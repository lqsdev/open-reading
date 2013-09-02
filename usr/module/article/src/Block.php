<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt New BSD License
 */

namespace Module\Article;

use Pi;
use Module\Article\Service;
use Module\Article\Topic;
use Module\Article\Statistics;
use Module\Article\Entity;

/**
 * Block class for providing article blocks
 * 
 * @author Zongshu Lin <lin40553024@163.com> 
 */
class Block
{
    /**
     * List all categories and its children
     * 
     * @param array   $options  Block parameters
     * @param string  $module   Module name
     * @return boolean 
     */
    public static function allCategories($options = array(), $module = null)
    {
        if (empty($module)) {
            return false;
        }
        
        $count       = $options['top-category'];
        $maxTopCount = ($count > 8 or $count <= 0) ? 6 : $count;
        $maxSubCount = $options['sub-category'] > 5 ? 5 : $options['sub-category'];
        $maxSubCount = $maxSubCount <= 0 ? 3 : $maxSubCount;
        $route       = $module . '-' . Service::getRouteName();
        $defaultUrl  = Pi::engine()->application()
            ->getRouter()
            ->assemble(
                array(
                    'list'  => 'all',
                ), 
                array(
                    'name' => $route
                )
            );
        
        $categories  = Service::getCategoryList(array('is-tree' => true));
        
        $allItems    = array();
        $topCount    = 0;
        foreach ($categories['child'] as $category) {
            if ($topCount > $maxTopCount) {
                break;
            }
            $id = $category['id'];
            $allItems[$id] = array(
                'title'     => $category['title'],
                'url'       => Pi::engine()->application()
                    ->getRouter()
                    ->assemble(
                        array(
                            'category'  => $category['slug'] ?: $category['id'],
                        ), 
                        array(
                            'name' => $route
                        )
                    ),
            );
            $topCount++;
            
            // Fetching sub-category
            $subCount    = 0;
            $child = isset($category['child']) ? $category['child'] : array();
            foreach ($child as $item) {
                if ($subCount > $maxSubCount) {
                    break;
                }
                $allItems[$id]['child'][$item['id']] = array(
                    'title'    => $item['title'],
                    'url'      => Pi::engine()->application()
                        ->getRouter()
                        ->assemble(
                            array(
                                'category'  => $item['slug'] ?: $item['id'],
                            ), 
                            array(
                                'name' => $route
                            )
                        ),
                );
                $subCount++;
            }
            for ($i = $subCount; $i < $maxSubCount; $i++) {
                $allItems[$id]['child'][] = array(
                    'title' => $options['default-category'] ?: __('None'),
                    'url'   => $defaultUrl,
                );
            }
        }
        for ($j = count($allItems); $j < $maxTopCount; $j++) {
            $child = array();
            for ($i = 0; $i < $maxSubCount; $i++) {
                $child[] = array(
                    'title' => $options['default-category'] ?: __('None'),
                    'url'   => $defaultUrl,
                );
            }
            $allItems[] = array(
                'title' => $options['default-category'] ?: __('None'),
                'url'   => $defaultUrl,
                'child' => $child,
            );
            
        }
        
        return $allItems;
    }
    
    /**
     * List newest published articles
     * 
     * @param array   $options
     * @param string  $module
     * @return boolean 
     */
    public static function newestPublishedArticles(
        $options = array(), 
        $module = null
    ) {
        if (empty($module)) {
            return false;
        }
        
        $params   = Pi::engine()->application()->getRouteMatch()->getParams();
        
        $config   = Pi::service('module')->config('', $module);
        $image    = $config['default_feature_thumb'];
        $image    = Pi::service('asset')->getModuleAsset($image, $module);
        $length   = isset($options['max_subject_length']) 
            ? intval($options['max_subject_length']) : 15;
        
        $postCategory = isset($params['category']) ? $params['category'] : 0;
        $postTopic    = isset($params['topic']) ? $params['topic'] : 0;
        
        $category = $options['category'] ? $options['category'] : $postCategory;
        $topic    = $options['topic'] ? $options['topic'] : $postTopic;
        if (!is_numeric($topic)) {
            $topic = Pi::model('topic', $module)->slugToId($topic);
        }
        $limit    = ($options['list-count'] <= 0) ? 10 : $options['list-count'];
        $page     = 1;
        $order    = 'time_publish DESC';
        $columns  = array('subject', 'summary', 'time_publish', 'image');
        $where    = array();
        if (!empty($category)) {
            $where['category'] = $category;
        }
        if (!empty($options['is-topic'])) {
            if (!empty($topic)) {
                $where['topic'] = $topic;
            }
            $articles = Topic::getTopicArticles(
                $where, 
                $page, 
                $limit, 
                $columns, 
                $order, 
                $module
            );
        } else {
            $articles = Entity::getAvailableArticlePage(
                $where, 
                $page, 
                $limit, 
                $columns, 
                $order, 
                $module
            );
        }
        
        foreach ($articles as &$article) {
            $article['subject'] = mb_substr($article['subject'], 0, $length, 'UTF-8');
            $article['image']   = $article['image'] ?: $image;
        }
        
        return array(
            'articles'  => $articles,
            'target'    => $options['target'],
            'style'     => $options['block-style'],
            'config'    => $config,
        );
    }
    
    /**
     * List articles defined by user.
     * 
     * @param array   $options
     * @param string  $module
     * @return boolean 
     */
    public static function customArticleList($options = array(), $module = null)
    {
        if (!$module) {
            return false;
        }
        
        $config   = Pi::service('module')->config('', $module);
        $image    = $config['default_feature_thumb'];
        $image    = Pi::service('asset')->getModuleAsset($image, $module);
        $length   = isset($options['max_subject_length']) 
            ? intval($options['max_subject_length']) : 15;
        
        $columns  = array('subject', 'summary', 'time_publish', 'image');
        $ids      = explode(',', $options['articles']);
        foreach ($ids as &$id) {
            $id = trim($id);
        }
        $where    = array('id' => $ids);
        $articles = Entity::getAvailableArticlePage(
            $where, 
            1, 
            10, 
            $columns, 
            null, 
            $module
        );
        
        foreach ($articles as &$article) {
            $article['subject'] = mb_substr($article['subject'], 0, $length, 'UTF-8');
            $article['image']   = $article['image'] ?: $image;
        }
        
        return array(
            'articles'  => $articles,
            'target'    => $options['target'],
            'style'     => $options['block-style'],
            'config'    => $config,
        );
    }
    
    /**
     * Export a search form.
     * 
     * @param array   $options
     * @param string  $module
     * @return boolean 
     */
    public static function simpleSearch($options = array(), $module = null)
    {
        if (!$module) {
            return false;
        }
        
        return array(
            'url' => Pi::engine()->application()->getRouter()->assemble(
                array(
                    'module'     => $module,
                    'controller' => 'search',
                    'action'     => 'simple',
                ),
                array(
                    'name'       => 'default',
                )
            ),
        );
    }

    /**
     * Count all article according to submitter
     * 
     * @param array   $options
     * @param string  $module
     * @return boolean 
     */
    public static function submitterStatistics($options = array(), $module = null)
    {
        if (!$module) {
            return false;
        }
        
        $limit    = ($options['list-count'] <= 0) ? 10 : $options['list-count'];
        $time      = time();
        $today     = strtotime(date('Y-m-d', $time));
        $tomorrow  = $today + 24 * 3600;
        $week      = $tomorrow - 24 * 3600 * 7;
        $month     = $tomorrow - 24 * 3600 * 30;
        $daySets   = Statistics::getSubmittersInPeriod($today, $tomorrow, $limit, $module);
        $weekSets  = Statistics::getSubmittersInPeriod($week, $tomorrow, $limit, $module);
        $monthSets = Statistics::getSubmittersInPeriod($month, $tomorrow, $limit, $module);
        $historySets = Statistics::getSubmittersInPeriod(0, $tomorrow, $limit, $module);
        
        return array(
            'day'     => $daySets,
            'week'    => $weekSets,
            'month'   => $monthSets,
            'history' => $historySets,
        );
    }
    
    /**
     * List newest topics.
     * 
     * @param array   $options
     * @param string  $module
     * @return boolean 
     */
    public static function newestTopic($options = array(), $module = null)
    {
        if (!$module) {
            return false;
        }
        
        $limit  = ($options['list-count'] <= 0) ? 10 : $options['list-count'];
        $order  = 'id DESC';
        $topics = Topic::getTopics(array(), 1, $limit, null, $order, $module);
        
        return $topics;
    }

    /**
     * List hot articles by visit count
     * 
     * @param array   $options
     * @param string  $module
     * @return boolean 
     */
    public static function hotArticles($options = array(), $module = null)
    {
        if (!$module) {
            return false;
        }
        
        $limit  = isset($options['list-count']) ? (int) $options['list-count'] : 10;
        $target = isset($options['target']) ?: '_blank';
        $config = Pi::service('module')->config('', $module);
        $image  = $config['default_feature_thumb'];
        $image  = Pi::service('asset')->getModuleAsset($image, $module);
        $length = isset($options['max_subject_length']) 
            ? intval($options['max_subject_length']) : 15;
        $day    = $options['day-range'] ? intval($options['day-range']) : 7;

        if ($options['is-topic']) {
            $params = Pi::engine()->application()->getRouteMatch()->getParams();
            if (is_string($params)) {
                $params['topic'] = Pi::model('topic', $module)
                    ->slugToId($params['topic']);
            }
            $articles = Topic::getVisitsRecently($day, $limit, null, $params['topic'], $module);
        } else {
            $articles = Entity::getVisitsRecently($day, $limit, null, $module);
        }
        
        foreach ($articles as &$article) {
            $article['subject'] = mb_substr($article['subject'], 0, $length, 'UTF-8');
            $article['image']   = $article['image'] ?: $image;
        }

        return array(
            'articles'  => $articles,
            'target'    => $target,
            'style'     => $options['block-style'],
            'config'    => $config,
        );
    }
    
    /**
     * List custom articles and with a slideshow besides article list
     * 
     * @param array   $options
     * @param string  $module
     * @return boolean 
     */
    public static function recommendedSlideshow($options = array(), $module = null)
    {
        if (!$module) {
            return false;
        }
        
        // Getting custom article list
        $columns  = array('subject', 'summary', 'time_publish', 'image');
        $ids      = explode(',', $options['articles']);
        foreach ($ids as &$id) {
            $id = trim($id);
        }
        $where    = array('id' => $ids);
        $articles = Entity::getAvailableArticlePage($where, 1, 10, $columns, null, $module);
        
        $config   = Pi::service('module')->config('', $module);
        $image    = $config['default_feature_thumb'];
        $image    = Pi::service('asset')->getModuleAsset($image, $module);
        $length   = isset($options['max_subject_length']) 
            ? intval($options['max_subject_length']) : 15;
        foreach ($articles as &$article) {
            $article['subject'] = mb_substr($article['subject'], 0, $length, 'UTF-8');
            $article['image']   = $article['image'] ?: $image;
        }
        
        // Getting image link url
        $urlRows    = explode('\n', $options['image-link']);
        $imageLinks = array();
        foreach ($urlRows as $row) {
            list($id, $url) = explode(':', trim($row), 2);
            $imageLinks[trim($id)] = trim($url);
        }
        
        // Fetching image ID
        $images   = explode(',', $options['images']);
        $imageIds = array();
        foreach ($images as $key => &$image) {
            $image = trim($image);
            if (is_numeric($image)) {
                $imageIds[] = $image;
            } else {
                $url   = $image ?: 'image/default-recommended.png';
                $image = array(
                    'url'         => Pi::service('asset')->getModuleAsset($url, $module),
                    'link'        => $imageLinks[$key + 1],
                    'title'       => __('This is default recommended image'),
                    'description' => __('You should to add your own images and its title and description!'),
                );
            }
        }
        
        if (!empty($imageIds)) {
            $images = array();
            $rowset = Pi::model('media', $module)->select(array('id' => $imageIds));
            foreach ($rowset as $row) {
                $id       = $row['id'];
                $link     = isset($imageLinks[$id]) ? $imageLinks[$id] : '';
                $images[] = array(
                    'url'         => Pi::url($row['url']),
                    'link'        => $link,
                    'title'       => $row['title'],
                    'description' => $row['description'],
                );
            }
        }
        
        return array(
            'articles'  => $articles,
            'target'    => $options['target'],
            'style'     => $options['block-style'],
            'images'    => $images,
            'config'    => Pi::service('module')->config('', $module),
        );
    }
}
