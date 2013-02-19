CREATE TABLE IF NOT EXISTS `qu_pap_bannerscategories` (
  `categoryid` INT NOT NULL ,
  `description` VARCHAR(255) NULL ,
  `image` VARCHAR(255) NULL ,
  PRIMARY KEY ( `categoryid` ) );

CREATE TABLE IF NOT EXISTS `qu_pap_bannersincategory` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `categoryid` INT NOT NULL ,
  `bannerid` CHAR( 8 ) NOT NULL ,
  PRIMARY KEY (`id`) ); 