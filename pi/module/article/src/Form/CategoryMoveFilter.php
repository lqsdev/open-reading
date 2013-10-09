<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt New BSD License
 */

namespace Module\Article\Form;

use Pi;
use Zend\InputFilter\InputFilter;

/**
 * Filter and validator of category move form
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
class CategoryMoveFilter extends InputFilter
{
    /**
     * Initializing validator and filter 
     */
    public function __construct()
    {
        $this->add(array(
            'name'     => 'from',
            'required' => true,
        ));

        $this->add(array(
            'name'     => 'to',
            'required' => false,
        ));
    }
}
