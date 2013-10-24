﻿angular.module('userProfileModule')
.config(['$routeProvider', 'piProvider', 'config',
  function ($routeProvider, piProvider, config) {
    //Get template url
    function tpl(name) {
      return config.assetRoot + name + '.html';
    }
    $routeProvider.when('/field', {
      templateUrl: tpl('profile-field'),
      controller: 'fieldCtrl'
    }).when('/dress', {
      templateUrl: tpl('profile-dress'),
      controller: 'dressCtrl',
      resolve: {
        data: ['$q', 'server',
          function($q, server) {
            var deferred = $q.defer();
            server.getDress().success(function(data) {
              deferred.resolve(data);
            });
            return deferred.promise;
          }
        ]
      }
    }).when('/privacy', {
      templateUrl: tpl('profile-privacy'),
      controller: 'privacyCtrl'
    }).otherwise({
      redirectTo: '/field'
    });
    piProvider.hashPrefix();
    piProvider.navTabs(config.navTabs);
    piProvider.translations(config.t);
    piProvider.ajaxSetup();
  }
])
.service('server', ['$http', '$cacheFactory', 'config',
  function ($http, $cacheFactory, config) {
    var urlRoot = config.urlRoot;

    this.getField = function() {
      return $http.get(urlRoot + 'field');
    }

    this.updateTitle = function(data) {
      return $http.post(urlRoot + 'updateField', data);
    }

    this.getPrivacy = function() {
      return $http.get(urlRoot + 'privacy');
    }

    this.setPrivacy = function(data) {
      return $http.post(urlRoot + 'setPrivacy', data);
    }

    this.getDress = function() {
      return $http.get(urlRoot + 'dressup');
    }

    this.saveDressUp = function(displays) {
      return $http.post(urlRoot + 'saveDressUp', {
        displays: displays
      });
    }
  }
])
.controller('fieldCtrl', ['$scope', 'server',
  function ($scope, server) {
    server.getField().success(function(data) {
      angular.extend($scope, data);
      angular.forEach($scope.compounds, function(compound) {
        angular.forEach(compound.fields, function(field) {
          field.compound = compound.name;
        });
      });
    });

    $scope.$on('piHoverInputSave', function(event, data) {
      server.updateTitle(data);
    });
  }
])
.controller('dressCtrl', ['$scope', '$route', '$timeout', 'config', 'server', 'data', 
  function ($scope, $route, $timeout, config, server, data) {
    angular.forEach(data.compounds, function(item) {
      item.$isEditing = 0;
    });
    angular.forEach(data.displays, function(item) {
      item.$isEditing = 0;
    });
    angular.extend($scope, data);

    var isSaved = 1;
    
    function sortGroupField() {
        //jQuery ui sortable
        $timeout(function() {
          $('.js-group-widgets').sortable({
          items: '.pi-widget-item',
          start: function (e, ui) {
            ui.item.data('start', ui.item.index());
          },
          update: function(e, ui) {
            var start = ui.item.data('start');
            var end = ui.item.index();
            var title = ui.item.parent().data('display');
            var list;
            angular.forEach($scope.displays, function(item) {
              if (item.title == title) {
                list = item;
                return false;
              }
            });
            var fields = list.fields;
            fields.splice(end, 0, fields.splice(start, 1)[0]);
            $scope.$apply();
          }
        });
      })
    }

    //jQuery ui sortable
    $('.user-profile-groups').sortable({
      items: '.pi-widget',
      handle: '.pi-widget-header',
      delay: 100,
      start: function (e, ui) {
        ui.item.data('start', ui.item.index());
      },
      update: function(e, ui) {
        var start = ui.item.data('start');
        var end = ui.item.index();
        var list = $scope.displays;
        list.splice(end, 0, list.splice(start, 1)[0]);
        $scope.$apply();
      }
    });
    sortGroupField();

    $scope.$watch('displays', function(newValue, oldValue) {
      if (newValue !== oldValue) {
        $scope.saveAlert = { message: config.t.SAVE_TIP };
        isSaved = 0;
      }
      var customGroup = [];
      angular.forEach(newValue, function(item) {
        if (!item.name) customGroup.push(item.title);
      });
      $scope.customGroup = customGroup;
    }, true);

    $scope.addDisplayGroup = function(idx) {
      var compound = $scope.compounds[idx];
      $scope.displays.push(compound);
      $scope.compounds.splice(idx, 1);
      sortGroupField();
    }

    $scope.AddCustomDisplay = function() {
      var title = $scope.entity;
      var unique = true;
      if (!title) return;
      angular.forEach($scope.displays, function(item) {
        if (item.title == title) return unique = false;
      });
      if (!unique) return;
      $scope.displays.push({
        title: title,
        $isEditing: 1,
        fields: []
      });
      $scope.entity = '';
      sortGroupField();
    }

    $scope.AddGroupField = function(title, idx) {
      var field = $scope.profile[idx];
      angular.forEach($scope.displays, function(item) {
        if (item.title == title) {
          item.$isEditing = 1;
          item.fields.push(field);
          $scope.profile.splice(idx, 1);
          return false;
        }
      });
    }

    $scope.removeGroupField = function(fields, idx) {
      var field = fields[idx];
      $scope.profile.push(field);
      fields.splice(idx, 1);
    }

    $scope.removeDisplay = function(idx) {
      var display = $scope.displays[idx];
      $scope.displays.splice(idx, 1);
      if (!display.name) return;
      $scope.compounds.push(display);
    }

    $scope.toggleGroup = function(display) {
      display.$isEditing = !display.$isEditing;
    }

    $scope.saveAction = function() {
      server.saveDressUp($scope.displays);
      $scope.saveAlert = '';
      isSaved = 1;
    }

    $scope.cancelAction = function() {
      $route.reload();
    }

    var leavingPageText = config.t.LEAVE_CONFIRM;

    window.onbeforeunload = function() {
      if (!isSaved) {
        return leavingPageText;
      }
    }

    $scope.$on('$locationChangeStart', function(event, next, current) {
        if (!isSaved) {
          if(!confirm(leavingPageText)) {
            event.preventDefault();
          }
        }
    });

  }
])
.controller('privacyCtrl', ['$scope', 'server',
  function($scope, server) {
    $scope.limits = [
      { text: 'public', value: 0 },
      { text: 'member', value: 1 },
      { text: 'follower', value: 2 },
      { text: 'following', value: 4 },
      { text: 'owner', value: 255 }
    ]

    server.getPrivacy().success(function(data) {
      $scope.fields = data;
    });

    $scope.setPrivacyAction = function(item) {
      server.setPrivacy(item);
    }

    $scope.forcePrivacyAction = function(item) {
      var old = item.is_forced;
      item.is_forced = old ? 0 : 1;
      server.setPrivacy(item).error(function() {
        item.is_forced = old;
      });
    }
  }
]);