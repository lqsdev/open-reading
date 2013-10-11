<?php

/**
 * Class controller Class
 * @copyright       Copyright (c) lqsit
 * @license         All rights reserved
 * @author          lqsic
 * @package         Module\book
*/

namespace Module\Book\Controller\Front;

use Pi\Mvc\Controller\ActionController;

/**
 * Book controller
 */
class BookController extends ActionController
{
    public function listAction()
    {
        $books = array('楞严经', '法华经', '华严经', '心经');
        
        $this->view()->assign('books', $books);
        $this->view()->setTemplate('book-list');
    }

    public function viewAction()
    {   
        $book = array(
            'title' => '文殊菩萨心咒',
            'cover' => 'upload/article/media/2013/10/10/27.jpg',
            'introduction' => "咒语是佛菩萨的秘密藏中自然流露出的语言，
                当持诵者念诵真言时，就会得到佛菩萨的加持和相应，
                而感召不可思议的力量。\n
                念此咒可得聪明才智，得大智慧。可破除烦恼与障碍。能开发潜能，
                增长智慧，破除疑难，增强记忆、伶俐聪辩，了解诸法真实义。
                亦能消除语业、破愚痴，得诸佛菩萨之般若智慧。");

        $this->view()->assign('book', $book);
        $this->view()->setTemplate('book-view');
    }
}
