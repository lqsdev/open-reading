<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Application;

use Pi;

/**
 * Config handler
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Config
{
    /** @var string Default domain */
    const DEFAULT_DOMAIN = 'general';

    /**
     * Config file location
     *
     * @var string
     */
    protected $configLocation = '';

    /**
     * Custom file location
     *
     * @var string
     */
    protected $customLocation = '';

    /**
     * Container for config data:
     *
     * - Global general config
     * - Domain config
     *
     * @var array
     */
    protected $configs = array(
    );

    /**
     * Constructor
     *
     * @param  string $configLocation
     */
    public function __construct($configLocation = '', $customLocation = '')
    {
        $this->configLocation = $configLocation ?: Pi::path('config');
        $this->customLocation = $customLocation ?: Pi::path('custom');
    }

    /**
     * Get a config by name and its domain
     *
     * @param string    $name       Name of the config element
     * @param string    $domain     Configuration domain
     * @return mixed    configuration value
     */
    public function get($name, $domain = null)
    {
        $value = null;
        $domain = (null === $domain) ? static::DEFAULT_DOMAIN : $domain;

        if (!isset($this->configs[$domain])) {
            $this->loadDomain($domain);
        }

        if (isset($this->configs[$domain])
            && isset($this->configs[$domain][$name])
        ) {
            $value = $this->configs[$domain][$name];
        }

        return $value;
    }

    /**
     * Set a config
     *
     * @param string    $name       Name of the config element
     * @param string    $domain     Configuration domain
     * @return $this
     */
    public function set($name, $value, $domain = null)
    {
        $domain = (null === $domain) ? static::DEFAULT_DOMAIN : $domain;
        $this->configs[$domain][$name] = $value;

        return $this;
    }

    /**
     * Set configuration data
     *
     * @param array     $configs    Associative array of config data
     * @param string    $domain     Configuration domain
     * @return $this
     */
    public function setConfigs($configs, $domain = null)
    {
        $domain = (null === $domain) ? static::DEFAULT_DOMAIN : $domain;
        if (isset($this->configs[$domain])) {
            $this->configs[$domain] = array_merge(
                $this->configs[$domain],
                $configs
            );
        } else {
            $this->configs[$domain] = $configs;
        }

        return $this;
    }

    /**
     * Unset configuration data of a domain
     *
     * @param string    $domain     Configuration domain
     * @return $this
     */
    public function unsetDomain($domain = null)
    {
        $domain = (null === $domain) ? static::DEFAULT_DOMAIN : $domain;
        if (isset($this->configs[$domain])) {
            $this->configs[$domain] = null;
        }

        return $this;
    }

    /**
     * Load configuration data of a domain from database
     *
     * @param string    $domain     Configuration domain
     * @return $this
     */
    public function loadDomain($domain = null)
    {
        $domain = (null === $domain) ? static::DEFAULT_DOMAIN : $domain;
        // Load data from cache
        $this->setConfigs(
            (array) Pi::registry('config')->read('system', $domain),
            $domain
        );

        return $this;
    }

    /**
     * Load configuration data from custom or config directory
     *
     * @param string    $configFile
     *      Name for the config file located inside var/config and sub folders
     * @return array
     */
    public function load($configFile)
    {
        $configs = array();
        $file = $this->customLocation . '/' . $configFile;
        if (!file_exists($file)) {
            $file = $this->configLocation . '/' . $configFile;
            if (!file_exists($file)) {
                $file = false;
            }
        }
        if ($file) {
            $configs = include $file;
        }

        return $configs;
    }
}
