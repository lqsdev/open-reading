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

class Image
{
    const OP_TYPE_RESIZE = 1;
    const OP_TYPE_SCALE  = 2;
    const OP_TYPE_CROP   = 3;
    const OP_TYPE_FIT    = 4;

    private $data;
    private $width;
    private $height;
    private $type;

    public function __construct($fileName)
    {
        $this->load($fileName);
    }

    public function getWidth()
    {
        return $this->width;
    }

    public function getHeight()
    {
        return $this->height;
    }

    public function getType()
    {
        return $this->type;
    }

    public function load($fileName)
    {
        if (!file_exists($fileName)) {
            return false;
        }

        $size = getimagesize($fileName);
        if (false === $size) {
            return false;
        }

        $this->type = $size[2];

        switch ($this->type) {
            case IMAGETYPE_JPEG:
                $this->data = imagecreatefromjpeg($fileName);
                break;
            case IMAGETYPE_GIF:
                $this->data = imagecreatefromgif($fileName);
                break;
            case IMAGETYPE_PNG:
                $this->data = imagecreatefrompng($fileName);

                if (function_exists('imagesavealpha')) {
                    imagesavealpha($this->data, true);
                }
                break;
            default:
                return false;
        }

        $this->width  = imagesx($this->data);
        $this->height = imagesy($this->data);

        return true;
    }

    public function isValid()
    {
        return !empty($this->data);
    }

    public function resize($width, $height)
    {
        if ($width == $this->width && $height == $this->height) {
            $realImage = $this->data;
        } else {
            if (IMAGETYPE_GIF != $this->type && function_exists('imagecreatetruecolor')) {
                $realImage = imagecreatetruecolor($width, $height);
            } else {
                $realImage = imagecreate($width, $height);
            }

            if (function_exists('imagealphablending') && function_exists('imagesavealpha')) {
                imagealphablending($realImage, false);
                imagesavealpha($realImage, true);
            }

            if (function_exists('imagecopyresampled')) {
                imagecopyresampled($realImage, $this->data, 0, 0, 0, 0, $width, $height, $this->width, $this->height);
            } else {
                imagecopyresized($realImage, $this->data, 0, 0, 0, 0, $width, $height, $this->width, $this->height);
            }
        }

        return $realImage;
    }

    public function crop($x, $y, $width, $height)
    {
        if (IMAGETYPE_GIF != $this->type && function_exists('imagecreatetruecolor')) {
            $realImage = imagecreatetruecolor($width, $height);
        } else {
            $realImage = imagecreate($width, $height);
        }

        if (function_exists('imagealphablending') && function_exists('imagesavealpha')) {
            imagealphablending($realImage, false);
            imagesavealpha($realImage, true);
        }

        if (function_exists('imagecopyresampled')) {
            imagecopyresampled($realImage, $this->data, 0, 0, $x, $y, $width, $height, $width, $height);
        } else {
            imagecopyresized($realImage, $this->data, 0, 0, $x, $y, $width, $height, $width, $height);
        }

        return $realImage;
    }

    public function scale($width, $height, $transparent = false)
    {
        if ($width == $this->width && $height == $this->height) {
            $realImage = $this->data;
        } else {
            $gapX  = $gapY = 0;
            $ratio = min($width / $this->width, $height / $this->height);

            if ($ratio >= 1) {
                $keptWidth  = $this->width;
                $keptHeight = $this->height;
            } else {
                $keptWidth  = (int) ($this->width * $ratio);
                $keptHeight = (int) ($this->height * $ratio);
            }
            $gapX = (int) (($width - $keptWidth) / 2);
            $gapY = (int) (($height - $keptHeight) / 2);

            if (IMAGETYPE_GIF != $this->type && function_exists('imagecreatetruecolor')) {
                $keptImage = imagecreatetruecolor($keptWidth, $keptHeight);
                $realImage = imagecreatetruecolor($width, $height);
            } else {
                $keptImage = imagecreate($keptWidth, $keptHeight);
                $realImage = imagecreate($width, $height);
            }
//var_dump(sprintf('ow:%d, oh:%d, w:%d, h:%d, r:%.3f, kw:%d, kh:%d, x:%d, y:%d', $this->width, $this->height, $width, $height, $ratio, $keptWidth, $keptHeight, $gapX, $gapY));
            if (function_exists('imagealphablending') && function_exists('imagesavealpha')) {
                imagealphablending($keptImage, false);
                imagesavealpha($keptImage, true);
            }

            // Fill default background
            $defaultBg = imagecolorallocate($realImage, 255, 255, 255);
            imagefill($realImage, 0, 0, $defaultBg);

            if ($transparent) {
                $background = imagecolorallocate($realImage, 0, 0, 0);
                imagecolortransparent($realImage, $background);
//                imagefill($realImage, 0, 0, $background);
//
//                $transparent = imagecolorallocatealpha($realImage, 0, 0, 0, 127);
//                imagefill($realImage, 0, 0, $transparent);
//                imagecolortransparent($realImage, $transparent);
            }

            if (function_exists('imagecopyresampled')) {
                imagecopyresampled($keptImage, $this->data, 0, 0, 0, 0, $keptWidth, $keptHeight, $this->width, $this->height);
            } else {
                imagecopyresized($keptImage, $this->data, 0, 0, 0, 0, $keptWidth, $keptHeight, $this->width, $this->height);
            }

            imagecopymerge($realImage, $keptImage, $gapX, $gapY, 0, 0, $keptWidth, $keptHeight, 100);

            imagedestroy($keptImage);
        }

        return $realImage;
    }

    public function fit($width, $height)
    {
        if (($width >= $this->width && $height >= $this->height) || $width == 0 || $height == 0) {
            $realImage = $this->data;
        } else {
            $ratioX = $width / $this->width;
            $ratioY = $height / $this->height;

            if ($ratioX <= $ratioY) {
                $height = (int) ($this->height * $ratioX);
            } else {
                $width  = (int) ($this->width * $ratioY);
            }

            $realImage = $this->resize($width, $height);
        }

        return $realImage;
    }

    public function output($image, $fileName, $type = IMAGETYPE_JPEG, $compression = 75, $permissions = null)
    {
        switch ($type) {
            case IMAGETYPE_JPEG:
                imagejpeg($image, $fileName, $compression);
                break;
            case IMAGETYPE_GIF:
                imagegif($image, $fileName);
                break;
            case IMAGETYPE_PNG:
                imagepng($image, $fileName);
                break;
            default:
                return false;
        }

        imagedestroy($image);

        if ($permissions) {
            chmod($fileName, $permissions);
        }

        return true;
    }

    public function save($fileName, $size = array(), $operation = self::OP_TYPE_RESIZE, $type = IMAGETYPE_JPEG,
                         $transparent = false, $compression = 75, $permissions = null)
    {
        $width  = intval($size['w']) ?: $this->width;
        $height = intval($size['h']) ?: $this->height;

        switch ($operation) {
            case self::OP_TYPE_RESIZE:
                $image = $this->resize($width, $height);
                break;
            case self::OP_TYPE_SCALE:
                $image = $this->scale($width, $height, $transparent);
                break;
            case self::OP_TYPE_CROP:
                $x = intval($size['x']) ?: 0;
                $y = intval($size['y']) ?: 0;
                $image = $this->crop($x, $y, $width, $height);
                break;
            case self::OP_TYPE_FIT:
                $image = $this->fit($width, $height);
                break;
            default:
                $image = $this->data;
        }

        return $this->output($image, $fileName, $type, $compression, $permissions);
    }
}
