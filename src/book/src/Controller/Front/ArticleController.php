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
 * Article controller
 */
class ArticleController extends ActionController
{
    public function viewAction()
    {
        $id = $this->params('id');
        $row = $this->getModel('article')->find($id);
        if ($row != null) {
            $article = $row->toArray();
        }
        
        /*$article = array(
            'id'    => $id,
            'title' => '文殊心咒',
            'content' => "咒语是佛菩萨的秘密藏中自然流露出的语言，
                当持诵者念诵真言时，就会得到佛菩萨的加持和相应，
                而感召不可思议的力量。\n
                念此咒可得聪明才智，得大智慧。可破除烦恼与障碍。能开发潜能，
                增长智慧，破除疑难，增强记忆、伶俐聪辩，了解诸法真实义。
                亦能消除语业、破愚痴，得诸佛菩萨之般若智慧。");*/
        
        $this->view()->assign('article', $article);
        $this->view()->setTemplate('article-view');
    }
}
