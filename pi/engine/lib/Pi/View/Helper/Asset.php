<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 * @package         View
 */

namespace Pi\View\Helper;

use Pi;
use Zend\View\Helper\AbstractHelper;

/**
 * Helper for building asset URI
 *
 *
 * Usage inside a phtml template
 *
 * ```
 *  $cssUri = $this->asset('theme/default', 'css/style.css');
 *  $jsUri = $this->asset('module/demo', 'js/demo.js');
 * ```
 *
 * @see Pi\Application\Service\Asset
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Asset extends AbstractHelper
{
    /**
     * Get URI of an asset
     *
     * @param   string    $component
     * @param   string    $file
     * @param   bool      $isPublic
     * @param   bool|null $appendVersion
     *
     * @return  string
     */
    public function __invoke(
        $component,
        $file,
        $isPublic = false,
        $appendVersion = null
    ) {
        $type = $isPublic ? 'public' : 'asset';

        $result = Pi::service('asset')->getAssetUrl(
            $component,
            $file,
            $type,
            $appendVersion
        );

        return $result;
    }
}
