<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 * @package         View
 */

namespace Pi\View\Helper\Navigation;

use Pi;
use RecursiveIteratorIterator;
use Zend\Navigation\AbstractContainer;
use Zend\View\Helper\Navigation\Menu as ZendMenu;
use Zend\Cache\Storage\Adapter\AbstractAdapter as CacheAdapter;

/**
 * Navigation menu helper
 *
 * {@inheritDoc}
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Menu extends ZendMenu
{
    /**
     * Cache container
     * @var \StdClass
     */
    protected $cache;

    /**
     * Set cache container
     *
     * @param \StdClass $cache
     */
    public function setCache($cache)
    {
        $this->cache = $cache;
    }

    /**
     * Renders a pair menu: normal menu and its active sub
     *
     *
     * Available $options:
     *
     *
     * @param  AbstractContainer $container
     *      [optional] container to create menu from.
     *      Default is to use the container retrieved
     *      from {@link getContainer()}.
     * @param  array     $options
     *      [optional] options for controlling rendering:
     *          ulClass - CSS class for first UL for parent menu;
     *          indent - initial indentation for parent menu;
     *          minDepth - minimum depth for parent menu;
     *          maxDepth - maximum depth for parent menu;
     *          escapeLabels - to escape labels for parent menu;
     *          sub:
     *           ulClass - CSS class for first UL for sub menu;
     *           indent - initial indentation for sub menu;
     *           maxDepth - maximum depth for sub menu;
     *           escapeLabels - to escape labels for sub menu;
     *
     * @return array()  array of parent menu and sub menu
     */
    public function renderPair($container = null, array $options = array())
    {
        $this->parseContainer($container);
        if (null === $container) {
            $container = $this->getContainer();
        }
        list($parent, $sub) = array('', '');
        if (!isset($options['onlyActiveBranch'])) {
            $options['onlyActiveBranch'] = true;
        }

        // find deepest active
        list($minDepth, $maxDepth) = array(0, null);
        $found = $this->findActive($container, $minDepth, $maxDepth);
        if ($found) {
            $foundPage  = $found['page'];
            $foundDepth = $found['depth'];
        } else {
            $foundPage = null;
        }

        $_this = $this;
        $_eol = static::EOL;
        $render = function (
            $container,
            $options = array(),
            $limitDepth = null,
            &$return = array()
        ) use ($_this, $_eol, $foundPage) {
            extract($options);

            $html = '';
            // create iterator
            $iterator = new RecursiveIteratorIterator(
                $container,
                RecursiveIteratorIterator::SELF_FIRST
            );
            if (is_int($limitDepth)) {
                $iterator->setMaxDepth($limitDepth);
            }

            // iterate container
            $prevDepth = -1;
            foreach ($iterator as $page) {
                $depth = $iterator->getDepth();
                if ($maxDepth && $depth > $maxDepth) {
                    continue;
                }
                $isActive = $page->isActive(true);
                if ($depth < $minDepth || !$_this->accept($page)) {
                    // page is below minDepth or not accepted by acl/visibility
                    continue;
                } elseif ($maxDepth && $depth == $maxDepth) {
                    // page is in the deepest branch
                    $accept = true;
                } elseif ($depth > $minDepth && $onlyActiveBranch
                    && !$isActive
                ) {
                    // page is not active itself,
                    // but might be in the active branch
                    $accept = false;
                    if ($foundPage) {
                        if ($foundPage->hasPage($page)) {
                            // accept if page is a direct child of
                            // the active page
                            $accept = true;
                        } elseif ($foundPage->getParent()->hasPage($page)) {
                            // page is a sibling of the active page...
                            if (!$foundPage->hasPages()
                                || is_int($maxDepth)
                                && $foundDepth + 1 > $maxDepth
                            ) {
                                // accept if active page has no children,
                                // or the children are too deep to be rendered
                                $accept = true;
                            }
                        }
                    }

                    if (!$accept) {
                        continue;
                    }
                }

                // make sure indentation is correct
                $depth -= $minDepth;
                $myIndent = $indent . str_repeat('        ', $depth);

                if ($depth > $prevDepth) {
                    // start new ul tag
                    if ($ulClass && $depth ==  0) {
                        $ulClass = ' class="' . $ulClass . '"';
                    } else {
                        $ulClass = '';
                    }
                    $html .= $myIndent . '<ul' . $ulClass . '>' . $_eol;
                } elseif ($prevDepth > $depth) {
                    // close li/ul tags until we're at current depth
                    for ($i = $prevDepth; $i > $depth; $i--) {
                        $ind = $indent . str_repeat('        ', $i);
                        $html .= $ind . '    </li>' . $_eol;
                        $html .= $ind . '</ul>' . $_eol;
                    }
                    // close previous li tag
                    $html .= $myIndent . '    </li>' . $_eol;
                } else {
                    // close previous li tag
                    $html .= $myIndent . '    </li>' . $_eol;
                }

                // render li tag and page
                $liClass = $isActive ? ' class="active"' : '';
                $html .= $myIndent . '    <li' . $liClass . '>' . $_eol
                       . $myIndent . '        '
                       . $_this->htmlify($page, $escapeLabels) . $_eol;

                // store as previous depth for next iteration
                $prevDepth = $depth;

                // Get sub menu
                if (is_int($maxDepth) && $depth == $maxDepth && $isActive) {
                    $return = $page;
                }
            }

            if ($html) {
                // done iterating container; close open ul/li tags
                for ($i = $prevDepth + 1; $i > 0; $i--) {
                    $myIndent = $indent . str_repeat('        ', $i-1);
                    $html .= $myIndent . '    </li>' . $_eol
                           . $myIndent . '</ul>' . $_eol;
                }
                $html = rtrim($html, $_eol);
            }

            return $html;
        };

        $options = $this->normalizeOptions($options);
        $optionsSub = isset($options['sub']) ? $options['sub'] : array();


        $limitDepth = isset($optionsSub['maxDepth'])
            ? $optionsSub['maxDepth'] : null;
        $subPages = array();
        $parent = $render($container, $options, $limitDepth, $subPages);
        if ($subPages) {
            $optionsSub = $this->normalizeOptions($optionsSub);
            $sub = $render($subPages, $optionsSub, $limitDepth);
        }

        return array($parent, $sub);
    }

    /**
     * {@inheritDoc}
     */
    protected function renderDeepestMenu(
        AbstractContainer $container,
        $ulClass,
        $indent,
        $minDepth,
        $maxDepth,
        $escapeLabels,
        $addClassToListItem
    ) {
        if (!$active =
            $this->findActive($container, $minDepth - 1, $maxDepth)) {
            return '';
        }

        // special case if active page is one below minDepth
        if ($active['depth'] < $minDepth) {
            if (!$active['page']->hasPages()) {
                return '';
            }
        } elseif (!$active['page']->hasPages()) {
            // found pages has no children; render siblings
            $active['page'] = $active['page']->getParent();
        } elseif (is_int($maxDepth) && $active['depth'] +1 > $maxDepth) {
            // children are below max depth; render siblings
            $active['page'] = $active['page']->getParent();
        }

        $ulClass = $ulClass ? ' class="' . $ulClass . '"' : '';
        $html = $indent . '<ul' . $ulClass . '>' . PHP_EOL;

        foreach ($active['page'] as $subPage) {
            if (!$this->accept($subPage)) {
                continue;
            }
            /**#@+
             * Added by Taiwen Jiang
             */
            if (!$subPage->getLabel()) {
                $liClass = $subPage->getClass() ?: 'divider';
                $html .= $indent . '    <li class="' . $liClass . '" />'
                       . PHP_EOL;
                continue;
            }
            /**#@-*/

            // render li tag and page
            $liClasses = array();
            // Is page active?
            if ($subPage->isActive(true)) {
                $liClasses[] = 'active';
            }
            // Add CSS class from page to <li>
            if ($addClassToListItem && $subPage->getClass()) {
                $liClasses[] = $subPage->getClass();
            }
            $liClass = empty($liClasses)
                ? '' : ' class="' . implode(' ', $liClasses) . '"';

            $html .= $indent . '    <li' . $liClass . '>' . PHP_EOL;
            $html .= $indent . '        '
                   . $this->htmlify($subPage, $escapeLabels, $addClassToListItem)
                   . PHP_EOL;
            $html .= $indent . '    </li>' . PHP_EOL;
        }

        $html .= $indent . '</ul>';

        return $html;
    }

    /**
     * {@inheritDoc}
     */
    protected function renderNormalMenu(
        AbstractContainer $container,
        $ulClass,
        $indent,
        $minDepth,
        $maxDepth,
        $onlyActive,
        $escapeLabels,
        $addClassToListItem
    ) {
        $html = '';

        // find deepest active
        $found = $this->findActive($container, $minDepth, $maxDepth);
        if ($found) {
            $foundPage  = $found['page'];
            $foundDepth = $found['depth'];
        } else {
            $foundPage = null;
        }

        // create iterator
        $iterator = new RecursiveIteratorIterator(
            $container,
            RecursiveIteratorIterator::SELF_FIRST
        );
        if (is_int($maxDepth)) {
            $iterator->setMaxDepth($maxDepth);
        }

        // iterate container
        $prevDepth = -1;
        foreach ($iterator as $page) {
            $depth = $iterator->getDepth();
            $isActive = $page->isActive(true);
            if ($depth < $minDepth || !$this->accept($page)) {
                // page is below minDepth or not accepted by acl/visibility
                continue;
            } elseif ($onlyActive && !$isActive) {
                // page is not active itself, but might be in the active branch
                $accept = false;
                if ($foundPage) {
                    if ($foundPage->hasPage($page)) {
                        // accept if page is a direct child of the active page
                        $accept = true;
                    } elseif ($foundPage->getParent()->hasPage($page)) {
                        // page is a sibling of the active page...
                        if (!$foundPage->hasPages()
                            || is_int($maxDepth)
                            && $foundDepth + 1 > $maxDepth
                        ) {
                            // accept if active page has no children, or the
                            // children are too deep to be rendered
                            $accept = true;
                        }
                    }
                }

                if (!$accept) {
                    continue;
                }
            }

            // make sure indentation is correct
            $depth -= $minDepth;
            $myIndent = $indent . str_repeat('        ', $depth);

            if ($depth > $prevDepth) {
                // start new ul tag
                if ($ulClass && $depth ==  0) {
                    $ulClass = ' class="' . $ulClass . '"';
                } else {
                    $ulClass = '';
                }
                $html .= $myIndent . '<ul' . $ulClass . '>' . PHP_EOL;
            } elseif ($prevDepth > $depth) {
                // close li/ul tags until we're at current depth
                for ($i = $prevDepth; $i > $depth; $i--) {
                    $ind = $indent . str_repeat('        ', $i);
                    $html .= $ind . '    </li>' . PHP_EOL;
                    $html .= $ind . '</ul>' . PHP_EOL;
                }
                // close previous li tag
                $html .= $myIndent . '    </li>' . PHP_EOL;
            } else {
                // close previous li tag
                $html .= $myIndent . '    </li>' . PHP_EOL;
            }

            /**#@+
             * Added by Taiwen Jiang
             */
            if (!$page->getLabel()) {
                $liClass = $page->getClass() ?: 'divider';
                $html .= $myIndent . '    <li class="' . $liClass . '" />'
                       . PHP_EOL;
                $prevDepth = $depth;
                continue;
            }
            /**#@-*/

            // render li tag and page
            $liClasses = array();
            // Is page active?
            if ($isActive) {
                $liClasses[] = 'active';
            }
            // Add CSS class from page to <li>
            if ($addClassToListItem && $page->getClass()) {
                $liClasses[] = $page->getClass();
            }
            $liClass = empty($liClasses)
                ? '' : ' class="' . implode(' ', $liClasses) . '"';

            $html .= $myIndent . '    <li' . $liClass . '>' . PHP_EOL
                   . $myIndent . '        '
                   . $this->htmlify($page, $escapeLabels, $addClassToListItem)
                   . PHP_EOL;

            // store as previous depth for next iteration
            $prevDepth = $depth;
        }

        if ($html) {
            // done iterating container; close open ul/li tags
            for ($i = $prevDepth+1; $i > 0; $i--) {
                $myIndent = $indent . str_repeat('        ', $i-1);
                $html .= $myIndent . '    </li>' . PHP_EOL
                       . $myIndent . '</ul>' . PHP_EOL;
            }
            $html = rtrim($html, PHP_EOL);
        }

        return $html;
    }

    /**
     * {@inheritDoc}
     */
    public function render($container = null)
    {
        Pi::service('log')->start('menu');

        // Try to load from cache
        if ($this->cache) {
            $cacheKey = $this->cache->key . '-mu';
            $content = $this->cache->storage->getItem($cacheKey);
            if (null !== $content) {
                if (Pi::service()->hasService('log')) {
                    Pi::service('log')->info('Menu is loaded from cache.');
                }
                return $content;
            }
        }

        // Generate if no cache available
        $content = parent::render($container);

        // Save to cache
        if ($this->cache) {
            $this->cache->storage->setItem($cacheKey, $content);
        }

        Pi::service('log')->end('menu');

        return $content;
    }
}
