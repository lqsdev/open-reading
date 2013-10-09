<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

// Skip engine bootup
//define('PI_BOOT_SKIP', 1);
// Disable error_reporting
//define('APPLICATION_ENV', 'production');

// Pi boot with no engine bootup: current file is located in www/script/...
require __DIR__ . '/../boot.php';

// Disable debugger message
Pi::service('log')->mute();

// Load session resource which is required by CAPTCHA
Pi::engine()->bootResource('session');

// Retrieve id generated CAPTCHA
$id = htmlspecialchars($_GET['id'], ENT_QUOTES, 'UTF-8');
$image = null;
if (!empty($id)) {
    // Load CAPTCA adapter
    $captcha = Pi::service('captcha')->load();
    // Generate CAPTCHA image
    $image = $captcha->createImage($id);
    // Close session
    //session_write_close();
    //Pi::service('session')->manager()->writeClose();
}

// Send responding response if image is not created
if (empty($image)) {
    if (substr(PHP_SAPI, 0, 3) == 'cgi') {
        header('Status: 404 Not Found');
    } else {
        header('HTTP/1.1 404 Not Found');
    }

    return;
}

// Send image to browser
header('Content-type: image/png');
imagepng($image);
imagedestroy($image);
