CREATE TABLE `{book}` (
  `id`              int(10) unsigned                NOT NULL AUTO_INCREMENT,
  `title`           varchar(255)                    NOT NULL,     
  `cover_url`       varchar(255)                    NULL, 
  `introduction`    text                            NULL,
  `catalogue_id`    int(10) unsigned                NULL,
  PRIMARY KEY       (`id`) 
);

CREATE TABLE `{catalogue}` (
  `id`              int(10) unsigned                NOT NULL AUTO_INCREMENT,
  `data`            text                            NULL,
 PRIMARY KEY        (`id`)
);

CREATE TABLE `{catalogue_rel_article}` (
  `id`              int(10) unsigned               NOT NULL AUTO_INCREMENT,
  `book_id`         int(10) unsigned               NOT NULL,
  `cata_data_id`    int(10) unsigned               NOT NULL,
  `article_id`      int(10) unsigned               NOT NULL,
 PRIMARY KEY        (`id`)
);

CREATE TABLE `{article}` (
  `id`              int(10) unsigned                NOT NULL AUTO_INCREMENT,
  `title`           varchar(255)                    NOT NULL,     
  `content`         text                            NULL, 
  `meta_type`       varchar(32)                     NULL,
  `image`           varchar(255)                    NOT NULL DEFAULT '',
  PRIMARY KEY       (`id`)
);
CREATE TABLE `{media}` (
  `id`              int(10) UNSIGNED      NOT NULL AUTO_INCREMENT,
  `name`            varchar(64)           NOT NULL DEFAULT '',
  `title`           varchar(255)          NOT NULL DEFAULT '',
  `type`            varchar(64)           NOT NULL DEFAULT '',
  `description`     varchar(255)          NOT NULL DEFAULT '',
  `url`             varchar(255)          NOT NULL DEFAULT '',
  `size`            int(10) UNSIGNED      NOT NULL DEFAULT 0,
  `uid`             int(10) UNSIGNED      NOT NULL DEFAULT 0,
  `time_upload`     int(10) UNSIGNED      NOT NULL DEFAULT 0,
  `time_update`     int(10) UNSIGNED      NOT NULL DEFAULT 0,
  `meta`            text                  DEFAULT NULL,

  PRIMARY KEY           (`id`),
  UNIQUE KEY `name`     (`name`),
  KEY `type`            (`type`),
  KEY `uid`             (`uid`)
);

CREATE TABLE `{media_statistics}` (
  `id`              int(10) UNSIGNED      NOT NULL AUTO_INCREMENT,
  `media`           int(10) UNSIGNED      NOT NULL DEFAULT 0,
  `download`        int(10) UNSIGNED      NOT NULL DEFAULT 0,

  PRIMARY KEY           (`id`),
  UNIQUE KEY `media`    (`media`)
);
