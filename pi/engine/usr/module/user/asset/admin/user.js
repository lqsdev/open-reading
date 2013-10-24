﻿angular.module('userListModule')
.config(['$routeProvider', 'piProvider', 'config',
  function ($routeProvider, piProvider, config) {
    //Get template url
    function tpl(name) {
      return config.assetRoot + name + '.html';
    }

    function resolve(action) {
      return {
        data: ['$q', '$route', '$rootScope', 'server',
          function($q, $route, $rootScope, server) {
            var deferred = $q.defer();
            var params = $route.current.params;
            $rootScope.alert = 2;
            server.get(action, params).success(function(data) {
              var users = data.users;
              for (var i = 0, l = users.length; i < l; i++) {
                var item = users[i];
                item.time_disabled *= 1000;
                item.time_created *= 1000;
                item.time_activated *= 1000;
                item.checked = 0;
                if (item.front_roles) {
                  item.front_roles = item.front_roles.join(',');
                }
                if (item.admin_roles) {
                  item.admin_roles = item.admin_roles.join(',');
                }
              }
              angular.extend(data, server.getRoles());
              data.filter = params;
              if (!users.length) {
                data.noneMessage = config.t.NONE_USER;
              }
              deferred.resolve(data);
              $rootScope.alert = '';
            });
            return deferred.promise;
          } 
        ]
      };
    }

    $routeProvider.when('/all', {
      templateUrl: tpl('index-all'),
      controller: 'ListCtrl',
      resolve: resolve('all')
    }).when('/activated', {
      templateUrl: tpl('index-activated'),
      controller: 'ListCtrl',
      resolve: resolve('activated')
    }).when('/pending', {
      templateUrl: tpl('index-pending'),
      controller: 'ListCtrl',
      resolve: resolve('pending')
    }).when('/new', {
      templateUrl: tpl('index-new'),
      controller: 'NewCtrl'
    }).when('/search', {
      templateUrl: tpl('advanced-search'),
      controller: 'SearchCtrl'
    }).when('/all/search', {
      templateUrl: tpl('advanced-search-result'),
      controller: 'ListCtrl',
      resolve: resolve('search')
    }).otherwise({
      redirectTo: '/all'
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

    this.get = function (action, params) {
      return $http.get(urlRoot + action, {
        params: params
      });
    }

    this.filterEmpty = function(obj) {
      var search = {};
      for (var i in obj) {
        if (obj[i]) {
          search[i] = obj[i];
        }
      }
      return search;
    }

    this.getRoles = function () {
      var frontRoles = [];
      var adminRoles = [];
      angular.forEach(config.roles, function(item) {
        if (item.type == 'front') {
          frontRoles.push(item);
        }
        if (item.type == 'admin') {
          adminRoles.push(item);
        }
      });
      return {
        'frontRoles': frontRoles,
        'adminRoles': adminRoles,
        'roles': config.roles
      };
    }

    this.roles = config.roles;

    this.disable = function (ids) {
      if (angular.isArray(ids)) {
        ids = ids.join(',');
      }
      return $http.post(urlRoot + 'disable', {
        ids: ids
      });
    }

    this.enable = function (ids) {
      if (angular.isArray(ids)) {
        ids = ids.join(',');
      }
      return $http.post(urlRoot + 'enable', {
        ids: ids
      });
    }

    this.active = function (ids) {
      if (angular.isArray(ids)) {
        ids = ids.join(',');
      }
      return $http.post(urlRoot + 'activateUser', {
        ids: ids
      });
    }

    this.remove = function (ids) {
      if (angular.isArray(ids)) {
        ids = ids.join(',');
      }
      return $http.post(urlRoot + 'deleteUser', {
        ids: ids
      });
    }

    this.add = function (params) {
      return $http.post(urlRoot + 'addUser', params);
    }

    this.assignRole = function(ids, role, op) {
      if (angular.isArray(ids)) {
        ids = ids.join(',');
      }
      return $http.post(urlRoot + 'assignRole', {
        ids: ids,
        role: role,
        type: op
      });
    }

    this.advanceSearch = function(params) {
      return $http.get(urlRoot + 'search', {
        params: params
      });
    }

    this.uniqueUrl = urlRoot + 'checkExist';
  }
])
.controller('ListCtrl', ['$scope', '$location', 'data', 'config', 'server', 
  function ($scope, $location, data, config, server) {
    angular.extend($scope, data);

    $scope.$watch('paginator.page', function (newValue, oldValue) {
      if(newValue === oldValue) return;
      $location.search('p', newValue);
    });

    function getCheckIds() {
      var ids = [];
      angular.forEach($scope.users, function (user) {
        if (user.checked) {
            ids.push(user.id);
        }
      });
      return ids;
    }

    $scope.markAll = function (checked) {
      angular.forEach(this.users, function (user) {
        user.checked = checked;
      });
    }

    $scope.disableBatchAction = function () {
      var users = $scope.users;
      server.disable(getCheckIds()).success(function (data) {
        if (data.status) {
          $scope.allChecked = 0;
          angular.forEach(users, function (user) {
            if (user.checked) {
              user.time_disabled = 1;
              user.active = 0;
              user.checked = 0;
            }
          });
        }
      });
    }

    $scope.enableAction = function(user) {
      if (user.time_disabled) {
        server.enable(user.id).success(function (data) {
          if (data.status) {
            user.time_disabled = 0;
            if (user.time_activated) user.active = 1;
          }
        });
      } else {
        server.disable(user.id).success(function (data) {
          if (data.status) {
            user.time_disabled = 1;
            user.active = 0;
          }
        });
      }
    }
   
    $scope.enableBatchAction = function () {
      var users = $scope.users;
      server.enable(getCheckIds()).success(function (data) {
        if (data.status) {
          $scope.allChecked = 0;
          angular.forEach(users, function (user) {
            if (user.checked) {
              user.time_disabled = 0;
              if (user.time_activated) user.active = 1;
              user.checked = 0;
            }
          });
        }
      });
    }

    $scope.activeAction = function (user) {
      if (user.time_activated) return;
      if (!confirm(config.t.CONFIRM_ACTIVATED)) return;
      server.active(user.id).success(function (data) {
        if (data.status) {
          user.time_activated = 1;
        }
      });
    }

    $scope.activeBatchAction = function () {
      server.active(getCheckIds()).success(function (data) {
        if (data.status) {
          $scope.allChecked = 0;
          angular.forEach($scope.users, function (user) {
            if (user.checked) {
              user.time_activated = 1;
              user.checked = 0;
            }
          });
        }
      });
    }

    $scope.deleteAction = function (idx) {
      if (!confirm(config.t.CONFIRM)) return;
      var users = this.users
      var user = users[idx];
      server.remove(user.id).success(function (data) {
        if (data.status) {
          users.splice(idx, 1);
        }
      });
    }

    $scope.deleteBatchAction = function () {
      if (!confirm(config.t.CONFIRMS)) return;
      server.remove(getCheckIds()).success(function (data) {
        var ret = [];
        if (data.status) {
          $scope.allChecked = 0;
          angular.forEach($scope.users, function (user) {
            !user.checked && ret.push(user);
          });
          $scope.users = ret;
        }
      });
    }

    $scope.assignRoleBacthAction = function() {
      var role = $scope.assignRole;
      if (!role) return;
      server.assignRole(getCheckIds(), role.name, 'add').success(function(data) {
        $scope.assignRole = '';
        if (!data.status) return;
        $scope.allChecked = 0;
        angular.forEach($scope.users, function (user) {
          if (user.checked) {
            if (role.type == 'front') {
              if (user.front_roles) {
                user.front_roles += ',' + role.name;
              } else {
                user.front_roles = role.name;
              }
            }
            if (role.type == 'admin') {
              if (user.admin_roles) {
                user.admin_roles += ',' + role.name;
              } else {
                user.admin_roles = role.name;
              } 
            }
            user.checked = 0;
          }
        });
      });
    }

    $scope.unassignRoleBacthAction = function() {
      var role = $scope.unassignRole;
      if (!role) return;
      server.assignRole(getCheckIds(), role, 'remove').success(function(data) {
        $scope.unassignRole = '';
        if (!data.status) return;
        $scope.allChecked = 0;
        angular.forEach($scope.users, function (user) {
          if (user.checked) {
            if (role.type == 'front' && user.front_roles) {
              user.front_roles = user.front_roles.replace(RegExp(',?' + role.name), '');
            }
            if (role.type == 'admin' && user.admin_roles) {
              user.admin_roles = user.admin_roles.replace(RegExp(',?' + role.name), '');
            }
            user.checked = 0;
          }
        });
      });
    }

    $scope.filterAction = function () {
      $location.search(server.filterEmpty($scope.filter));
      $location.search('p', null);
    }
  }
])
.controller('NewCtrl', ['$scope', 'server',
  function ($scope, server) {
    var entity = {
      activated: 1,
      enable: 1,
      roles: ['member']
    };
    
    $scope.entity = entity;
    $scope.uniqueUrl = server.uniqueUrl;
    $scope.roles = angular.copy(server.roles);
    angular.forEach($scope.roles, function (item) {
      if (entity.roles.indexOf(item.name) != -1) {
        item.checked = true;
      }
    });


    $scope.submit = function () {
      server.add(entity);
    }

    $scope.$watch('roles', function () {
      var roles = [];
      angular.forEach($scope.roles, function (item) {
        if (item.checked) {
          roles.push(item.name);
        }
      });
      entity.roles = roles;
    }, true);
  }
])
.controller('SearchCtrl', ['$scope', '$location', 'config', 'server',
  function($scope, $location, config, server) {
    $scope.roles = angular.copy(server.roles);
    $scope.today = config.today;
    $scope.filter = {};

    $scope.$watch('roles', function(newValue) {
      var front_role = [];
      var admin_role = [];
      var filter = $scope.filter;
      angular.forEach(newValue, function(item) {
        if (item.checked) {
          if (item.type == 'front') {
            front_role.push(item.name);
          } else {
            admin_role.push(item.name);
          }
        }
      });
      if (front_role.length) {
        filter.front_role = front_role.join(',');
      }
      if (admin_role.length) {
        filter.admin_role = admin_role.join(',');
      }
    }, true);

    $scope.submit = function() {
      var filter = angular.copy($scope.filter);
      var parse = function(time) {
        return parseInt((new Date(time)).getTime() / 1000, 10);
      }

      if (filter.time_created_from) {
        filter.time_created_from = parse(filter.time_created_from);
      }

      if (filter.time_created_to) {
        filter.time_created_to = parse(filter.time_created_to);
      }

      $location.path('/all/search').search(filter);
    }
  }
]);