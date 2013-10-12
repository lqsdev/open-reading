<?php

namespace Module\Book\Form;

use Zend\InputFilter\InputFilter;

class ArticleFilter extends InputFilter
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
            'name'        => 'content',
            'filters'     => array(
                array(
                    'name' => 'StringTrim',
                ),
            ),
        ));
    }
}
