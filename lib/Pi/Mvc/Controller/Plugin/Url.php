<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Mvc\Controller\Plugin;

use Zend\EventManager\EventInterface;
use Zend\Mvc\Exception;
use Zend\Mvc\InjectApplicationEventInterface;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\RouteStackInterface;
use Zend\Mvc\Controller\Plugin\Url as ZendUrl;

/**
 * URL plugin for controller
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Url extends ZendUrl
{
    /**
     * Generates a URL based on a route
     *
     * @param string        $route                  RouteInterface name
     * @param array         $params
     *      Parameters to use in url generation, if any
     * @param array|bool    $options
     *      RouteInterface-specific options to use in url generation, if any.
     *      If boolean, and no fourth argument, used as $reuseMatchedParams
     * @param bool          $reuseMatchedParams
     *      Whether to reuse matched parameters
     * @return string|$this
     */
    public function __invoke(
        $route = null,
        array $params = array(),
        $options = array(),
        $reuseMatchedParams = false
    ) {
        if (0 == func_num_args()) {
            return $this;
        }

        // Complete current module/controller
        if (!isset($params['module']) && isset($params['action'])) {
            $routeMatch = $this->getController()->getEvent()->getRouteMatch();
            $params['module'] = $routeMatch->getParam('module');
            if (!isset($params['controller'])) {
                $params['controller'] = $routeMatch->getParam('controller');
            }
        }

        $route = $route ?: null;
        $url = $this->fromRoute($route, $params,
                                $options, $reuseMatchedParams);

        return $url;
    }
}
