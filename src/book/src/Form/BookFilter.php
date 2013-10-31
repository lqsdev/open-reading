<?php

namespace Module\Book\Form;

use Zend\InputFilter\InputFilter;

class BookFilter extends InputFilter
{
    public function __construct()
    {
        $this->add(array(
            'name'        => 'title',
            'filters'     => array(
                array(
                    'name' => 'StringTrim',
                ),
            ),
        ));
        
        $this->add(array(
            'name'        => 'introduction',
            'filters'     => array(
                array(
                    'name' => 'StringTrim',
                ),
            ),
        ));
    }
}
