DROP TABLE IF EXISTS Test1;
CREATE TABLE `Test1` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(50) NOT NULL,
  `somedate` DATE NOT NULL,
  `nameNullable` VARCHAR(50) NULL DEFAULT NULL,
  `somedateNullable` DATE NULL DEFAULT NULL,
  `nameWithDefault` VARCHAR(50) NULL DEFAULT ':3',
  PRIMARY KEY (`id`)
)
  COLLATE='utf8mb4_general_ci'
  ENGINE=InnoDB
;
