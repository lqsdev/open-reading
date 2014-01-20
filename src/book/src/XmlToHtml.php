<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Module\Book;

use Pi;
use \DOMDocument;
/**
 * Description of XmlToHtml
 *
 * @author linliu
 */
class XmlToHtml
{
    public function parse($string)
    {
        $rawData = $this->getRawData($string);

        if (isset($rawData['article']))
        {
            return $this->parseArticle($rawData['article']);
        }
        
        if (isset($rawData['qandasets']))
        {
            return $this->parseQandA($rawData['qandasets']);
        }
        
        if (isset($rawData['chapter']))
        {
            return $this->parseChapter($rawData['chapter']);
        }
        
        return 'xml parse error: ' . $string;
    }
    
    private function parseChapter($array)
    {
        $article = array();
        $article['title'] = $array['title'];
        
        $content = false;
        foreach ($array['abstract'] as $para)
        {
            $content = '<p>' . $para;
        }
        
        $article['content'] = $content;
        return $article;    
    }
    
    private function parseArticle($array)
    {
        $article = array();
        $article['title'] = $array['title'];
        $content = '<center><h4>' . $array['subtitle'] . '</h4></center>';
        $content .= '<blockquote><strong>' . $array['abstract']['#text'] . '：</strong>';

        $i = 0;
        foreach ($array['para'] as $para)
        {
            if (++$i == 1)
            {
                $content .= $para . '</blockquote>';
            }
            else
            {
                $content .= '<p>' . $para;
            }
            
        }
        $article['content'] = $content;
        return $article;
    }
    
    private function parseQandA($array)
    {
        $article = array();
        $article['title'] = $array['title'];
        
        $content = '';
        foreach ($array['qandaentry'] as $qa)
        {
            $content .= $qa['question']['person'] . '&nbsp;发表于&nbsp;' . $qa['question']['time'] . '：&nbsp;' . $qa['question']['para'] . '<p>';
            $content .= $qa['answer']['person'] . '&nbsp;发表于&nbsp;' . $qa['answer']['time'] . '：&nbsp;' . $qa['answer']['para'] . '<p><p>';  
        }
        $article['content'] = $content;

        return $article;
    }

    private function getRawData($string)
    {
        $dom = new DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->loadXML($string);
        $dom->removeChild($dom->firstChild); // 去掉开始的元数据
        return $this->parseNode($dom);
    }
    
    private function parseNode($node)
    {
        static $textNode = array('para', 'person', 'time', 'title', 'subtitle');
        
        if (in_array($node->nodeName, $textNode) || !$node->hasChildNodes())
        {
            return $node->nodeValue;
        }

        $array = array();
        foreach ($node->childNodes as $childNode)
        {
            $array[$childNode->nodeName][] = $this->parseNode($childNode);
        }

        foreach($array as $key => $value)
        {
            if (count($value) == 1)
                $array[$key] = $value[0];
        }
        return $array;
    }
}
