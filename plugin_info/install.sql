CREATE TABLE IF NOT EXISTS `calendar_event` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `eqLogic_id` int(11) NOT NULL,
  `cmd_param` TEXT DEFAULT NULL,
  `value` varchar(127),
  `startDate` datetime DEFAULT NULL,
  `endDate` datetime DEFAULT NULL,
  `until` datetime DEFAULT NULL,
  `repeat` TEXT DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
