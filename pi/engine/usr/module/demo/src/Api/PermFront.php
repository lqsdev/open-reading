<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\Demo\Api;

use Pi\Application\AbstractModuleAwareness;

class PermFront extends AbstractModuleAwareness
{
    protected $module = 'demo';

    public function getResources()
    {
        $resources = array();
        for ($i = 1; $i <= 10; $i++) {
            $name = $this->module . '-resource-' . $i;
            $title = ucwords($this->module . ' resource front ' . $i);
            $resources[$name] = $title;
        }

        //vd($resources);
        return $resources;
    }
}
