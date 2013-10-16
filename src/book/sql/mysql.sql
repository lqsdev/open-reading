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
  PRIMARY KEY       (`id`)
);
