<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Setup\Controller;

use Pi;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

/**
 * Finish page controller
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Finish extends AbstractController
{
    protected $hasBootstrap = true;

    public function init()
    {
        //$this->wizard->destroyPersist();
    }

    public function indexAction()
    {
        $wizard = $this->wizard;
        $vars = $wizard->getPersist('paths');
        $configs = array();

        /**#@+
         * htdocs/boot.php
         */
        $file = $vars['www']['path'] . '/boot.php';
        $file_dist = $wizard->getRoot() . '/dist/boot.php.dist';
        $content = file_get_contents($file_dist);
        foreach ($vars as $var => $val) {
            if (!empty($val['path'])) {
                $content = str_replace(
                    '%' . $var . '%',
                    $val['path'],
                    $content
                );
            }
        }
        $content = str_replace(
            '%host%',
            $vars['config']['path'] . '/host.php',
            $content
        );
        $configs[] = array('file' => $file, 'content' => $content);
        /**#@-*/

        /**#@+
         * htdocs/.htaccess
         */
        $file = $vars['www']['path'] . '/.htaccess';
        $file_dist = $wizard->getRoot() . '/dist/.htaccess.dist';
        $content = file_get_contents($file_dist);
        $configs[] = array('file' => $file, 'content' => $content);
        /**#@-*/

        // Write content to files and record errors in case occured
        foreach ($configs as $config) {
            $error = false;
            if (!$file = fopen($config['file'], 'w')) {
                $error = true;
            } else {
                if (fwrite($file, $config['content']) == -1) {
                    $error = true;
                }
                fclose($file);
            }
        }

        $readPaths = "<ul>";
        $readonly = $this->wizard->getConfig('readonly');
        foreach ($readonly as $section => $list) {
            foreach ($list as $item) {
                $file = Pi::path($section . '/' . $item);
                @chmod($file, 0644);
                $readPaths .= '<li class="files">' . $section . '/' . $item . '</li>';
                if (is_dir($file)) {
                    $objects = new RecursiveIteratorIterator(
                        new RecursiveDirectoryIterator($file),
                        RecursiveIteratorIterator::CHILD_FIRST
                    );
                    foreach ($objects as $object) {
                        @chmod($file, 0644);
                    }
                }
            }
        }
        $readPaths .= '</ul>';

        $message = _s('
<div class="well">
<h3>Congratulations!</h3>
<p>The system is set up successfully. <a href="../index.php?redirect=0">Click to visit your website!</a></p>
</div>
<div class="well">
<h3>Security advisory</h3>
<p>For security considerations please make sure the following operations are done:</p>
<ol>
    <li>Remove the installation folder <strong>{www}/setup/</strong> from your server manually.</li>
    <li>Set configuration directories and files to readonly: %s</li>
</ol>
</div>
<div class="well">
<h3>Support</h3>
<p>Visit <a href="http://pialog.org/" rel="external">Pi Engine Development Site</a> in case you need any help.</p>
</div>
');
        $this->content = sprintf($message, $readPaths);

        $path = Pi::path('cache');
        $objects = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path),
            RecursiveIteratorIterator::SELF_FIRST
        );
        foreach ($objects as $object) {
            if ($object->isFile() && 'index.html' != $object->getFilename()) {
                unlink($object->getPathname());
            }
        }

        Pi::persist()->flush();
    }
}
