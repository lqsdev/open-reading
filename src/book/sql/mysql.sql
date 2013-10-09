CREATE TABLE `{article}` (
  `id`              int(10) UNSIGNED                NOT NULL AUTO_INCREMENT,
  `title`           varchar(255)                    NOT NULL DEFAULT '',     
  `content`         text                            NOT NULL DEFAULT '', 
  `catalogue_id`    int(10) UNSIGNED                NOT NULL DEFAULT 0, 
  PRIMARY KEY       (`id`));

CREATE TABLE `{book}` (
  `id`              int(10) UNSIGNED                NOT NULL AUTO_INCREMENT,
  `title`           varchar(255)                    NOT NULL DEFAULT '',     
  `cover`           varchar(255)                    NOT NULL DEFAULT '', 
  `introduction`    text                            NOT NULL DEFAULT '',
  PRIMARY KEY       (`id`) 
);

CREATE TABLE `{catalogue}` (
  `id`              int(10) UNSIGNED                NOT NULL AUTO_INCREMENT,
  `data`            text                            NULLABLE DEFAULT '',     
  `book_id`         int(10) UNSIGNED                NOT NULL AUTO_INCREMENT, 
 PRIMARY KEY        (`id`),
);