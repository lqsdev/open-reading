<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt New BSD License
 */

namespace Module\Article\Registry;

use Pi\Application\Registry\AbstractRegistry;
use Pi;

/**
 * Route registry class
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
class Route extends AbstractRegistry
{
    protected $module = 'article';

    /**
     * Load data from database
     * 
     * @param array $options
     * @return array 
     */
    protected function loadDynamic($options = array())
    {
        $module = $options['module'];
        $route  = Pi::model('route')->find($module, 'module');

        return $route->name;
    }
    
    /**
     * Read data from cache or database
     * 
     * @return array 
     */
    public function read()
    {
        $module  = Pi::service('module')->current();
        $options = compact('module');
        
        return $this->loadData($options);
    }
    
    /**
     * Create a cache
     */
    public function create()
    {
        $module  = Pi::service('module')->current();
        $this->clear($module);
        $this->read();
    }
    
    /**
     * Clear cache
     * 
     * @param string $namespace
     * @return \Module\Article\Registry\Category 
     */
    public function clear($namespace = '')
    {
        parent::clear($namespace);
        return $this;
    }
    
    /**
     * Flush all cache
     * 
     * @return \Module\Article\Registry\Category 
     */
    public function flush()
    {
        $module = Pi::service('module')->current();
        $this->clear($module);
        return $this;
    }
}
