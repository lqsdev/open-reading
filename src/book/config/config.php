<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt New BSD License
 */

/**
 * Module config config
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
return array(
    'category' => array(
        array(
            'name'  => 'media',
            'title' => _t('Media'),
        ),
    ),

    'item' => array(
        
        // Media
        'path_media'      => array(
            'category'    => 'media',
            'title'       => _t('Media Path'),
            'description' => _t('Path to save media file.'),
            'value'       => 'upload/article/media',
        ),
        'media_extension' => array(
            'category'    => 'media',
            'title'       => _t('Media Extension'),
            'description' => _t('Media types which can be uploaded.'),
            'value'       => 'pdf,rar,zip,doc,txt,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,gif',
        ),
        'image_format'    => array(
            'category'    => 'media',
            'title'       => _t('Image Format'),
            'description' => _t('Decide which extension belong to image'),
            'value'       => 'jpg,jpeg,png,gif,bmp,tiff,exif',
        ),
        'doc_format'      => array(
            'category'    => 'media',
            'title'       => _t('Documentation Format'),
            'description' => _t('Decide which extension belong to doc'),
            'value'       => 'pdf,doc,docx,xls,xlsx,ppt,pptx,txt,csv',
        ),
        'video_format'    => array(
            'category'    => 'media',
            'title'       => _t('Vedio Format'),
            'description' => _t('Decide which extension belong to vedio'),
            'value'       => 'avi,rm,rmvb,flv,swf,wmv,mp4',
        ),
        'zip_format'      => array(
            'category'    => 'media',
            'title'       => _t('Compression Format'),
            'description' => _t('Decide which extension belong to compression'),
            'value'       => 'zip,rar',
        ),
        'max_media_size'  => array(
            'category'    => 'media',
            'title'       => _t('Max Media Size'),
            'description' => _t('Max media size'),
            'value'       => '2MB',
        ),
        'default_media_image' => array(
            'category'    => 'media',
            'title'       => _t('Default Media Image'),
            'description' => _t('Path to default media image of article.'),
            'value'       => 'image/default-media.png',
        ),
        'default_media_thumb' => array(
            'category'    => 'media',
            'title'       => _t('Default media thumb'),
            'description' => _t('Path to default media thumb of article.'),
            'value'       => 'image/default-media-thumb.png',
        ),
        'image_width'     => array(
            'category'    => 'media',
            'title'       => _t('Image Width'),
            'description' => _t('Max allowed image width'),
            'value'       => 540,
        ),
        'image_height'    => array(
            'category'    => 'media',
            'title'       => _t('Image Height'),
            'description' => _t('Max allowed image height'),
            'value'       => 460,
        ),
        'image_extension' => array(
            'category'    => 'media',
            'title'       => _t('Image Extension'),
            'description' => _t('Images types which can be uploaded.'),
            'value'       => 'jpg,png,gif',
        ),
        'max_image_size' => array(
            'category'    => 'media',
            'title'       => _t('Max Image Size'),
            'description' => _t('Max image size allowed'),
            'value'       => '2MB',
        ),
        'path_author'  => array(
            'category'    => 'media',
            'title'       => _t('Author Path'),
            'description' => _t('Path to upload photo of author.'),
            'value'       => 'upload/article/author',
        ),
        'path_category' => array(
            'category'    => 'media',
            'title'       => _t('Category Path'),
            'description' => _t('Path to upload image of category.'),
            'value'       => 'upload/article/category',
        ),
        'path_feature'  => array(
            'category'    => 'media',
            'title'       => _t('Feature Path'),
            'description' => _t('Path to upload feature image of article.'),
            'value'       => 'upload/article/feature',
        ),
        'path_topic'    => array(
            'category'    => 'media',
            'title'       => _t('Topic Path'),
            'description' => _t('Path to upload image of topic.'),
            'value'       => 'upload/article/topic',
        ),
        'sub_dir_pattern' => array(
            'category'    => 'media',
            'title'       => _t('Pattern'),
            'description' => _t('Use datetime as pattern of sub directory.'),
            'value'       => _t('Y/m/d'),
            'edit'        => array(
                'type'    => 'select',
                'options' => array(
                    'options' => array(
                        'Y/m/d' => 'Y/m/d',
                        'Y/m'   => 'Y/m',
                        'Ym'    => 'Ym',
                    ),
                ),
            ),
        ),
        'author_size'     => array(
            'category'    => 'media',
            'title'       => _t('Author Photo Size'),
            'description' => _t('Author photo width and height'),
            'value'       => 110,
            'filter'      => 'number_int',
        ),
        'default_author_photo' => array(
            'category'    => 'media',
            'title'       => _t('Default Author Photo'),
            'description' => _t('Path to default photo of author.'),
            'value'       => 'image/default-author.png',
        ),
        'category_width'  => array(
            'category'    => 'media',
            'title'       => _t('Category Image Width'),
            'description' => _t('Category image width'),
            'value'       => 40,
            'filter'      => 'number_int',
        ),
        'category_height' => array(
            'category'    => 'media',
            'title'       => _t('Category Image Height'),
            'description' => _t('Category image height'),
            'value'       => 40,
            'filter'      => 'number_int',
        ),
        'default_category_image' => array(
            'category'    => 'media',
            'title'       => _t('Default Category Image'),
            'description' => _t('Path to default image of category.'),
            'value'       => 'image/default-category.png',
        ),
        'topic_width'     => array(
            'category'    => 'media',
            'title'       => _t('Topic Image Width'),
            'description' => _t('Topic image width'),
            'value'       => 320,
            'filter'      => 'number_int',
        ),
        'topic_height'    => array(
            'category'    => 'media',
            'title'       => _t('Topic Image Height'),
            'description' => _t('Topic image height'),
            'value'       => 240,
            'filter'      => 'number_int',
        ),
        'topic_thumb_width' => array(
            'category'    => 'media',
            'title'       => _t('Topic thumb width'),
            'description' => '',
            'value'       => 80,
            'filter'      => 'number_int',
        ),
        'topic_thumb_height' => array(
            'category'    => 'media',
            'title'       => _t('Topic thumb height'),
            'description' => '',
            'value'       => 60,
            'filter'      => 'number_int',
        ),
        'default_topic_thumb' => array(
            'category'    => 'media',
            'title'       => _t('Default topic thumb'),
            'description' => _t('Path to default topic thumb.'),
            'value'       => 'image/default-topic-thumb.png',
        ),
        'default_topic_image' => array(
            'category'    => 'media',
            'title'       => _t('Default Topic Image'),
            'description' => _t('Path to default image of topic.'),
            'value'       => 'image/default-topic.png',
        ),
        'feature_width'   => array(
            'category'    => 'media',
            'title'       => _t('Feature Image Width'),
            'description' => _t('Feature image width'),
            'value'       => 440,
            'filter'      => 'number_int',
        ),
        'feature_height'  => array(
            'category'    => 'media',
            'title'       => _t('Feature Image Height'),
            'description' => _t('Feature image height'),
            'value'       => 300,
            'filter'      => 'number_int',
        ),
        'default_feature_image' => array(
            'category'    => 'media',
            'title'       => _t('Default Feature Image'),
            'description' => _t('Path to default feature image of article.'),
            'value'       => 'image/default-feature.png',
        ),
        'feature_thumb_width' => array(
            'category'    => 'media',
            'title'       => _t('Feature thumb width'),
            'description' => '',
            'value'       => 80,
            'filter'      => 'number_int',
        ),
        'feature_thumb_height' => array(
            'category'    => 'media',
            'title'       => _t('Feature thumb height'),
            'description' => '',
            'value'       => 60,
            'filter'      => 'number_int',
        ),
        'default_feature_thumb' => array(
            'category'    => 'media',
            'title'       => _t('Default feature thumb'),
            'description' => _t('Path to default feature thumb of article.'),
            'value'       => 'image/default-feature-thumb.png',
        ),
        'content_thumb_width' => array(
            'category'    => 'media',
            'title'       => _t('Content thumb width'),
            'description' => '',
            'value'       => 640,
            'filter'      => 'number_int',
        ),
        'content_thumb_height' => array(
            'category'    => 'media',
            'title'       => _t('Content thumb height'),
            'description' => '',
            'value'       => 360,
            'filter'      => 'number_int',
        ),

    ),
);
