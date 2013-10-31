<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\User\Api;

use Pi;
use Pi\Application\AbstractApi;

/**
 * User module quicklink api
 *
 * @author Liu Chuang <liuchuang@eefocus.com>
 */
class  Quicklink extends AbstractApi
{
    protected $module = 'user';

    /**
     * Get quicklink
     *
     * @param null $limit
     * @param null $offset
     * @return array
     */
    public function getList($limit = null, $offset = null)
    {
        $result = array();
        $model = Pi::model('quicklink', $this->module);
        $where = array(
            'active'  => 1,
            'display' => 1,
        );
        $columns = array(
            'id',
            'name',
            'title',
            'module',
            'link',
            'icon',
        );

        $select = $model->select()->where($where);
        if ($limit) {
            $select->limit($limit);
        }
        if ($offset) {
            $select->offset($offset);
        }

        $select->columns($columns);
        $rowset = $model->selectWith($select);

        foreach ($rowset as $row) {
            $result[] = $row->toArray();
        }

        return $result;

    }
}