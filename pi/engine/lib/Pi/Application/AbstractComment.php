<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Application;

/**
 * Abstract class for module comment callback
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
abstract class AbstractComment extends AbstractModuleAwareness
{
    /**
     * Get target data of item(s)
     *
     * - Target data of an item:
     *   - title
     *   - url
     *   - time
     *   - uid
     *
     * @param int|string|int[]|string[] $item
     *
     * @return array|bool
     */
    abstract public function get($item);
}
