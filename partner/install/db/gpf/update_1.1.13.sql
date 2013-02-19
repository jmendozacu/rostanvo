CREATE  TABLE IF NOT EXISTS `qu_g_currencyrates` (
  `rateid` INT NOT NULL AUTO_INCREMENT,
  `valid_from` DATETIME NOT NULL ,
  `valid_to` DATETIME NOT NULL ,
  `source_currency` VARCHAR(10) NOT NULL ,
  `target_currency` VARCHAR(10) NOT NULL ,
  `rate` DOUBLE NOT NULL ,
  `type` CHAR(1) NOT NULL ,
  PRIMARY KEY (`rateid`) );