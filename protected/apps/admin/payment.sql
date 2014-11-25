SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";



CREATE TABLE IF NOT EXISTS `payment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `orderid` int(11) NOT NULL,
  `orderno` varchar(50) NOT NULL,
  `price_rmb` decimal(15,4) NOT NULL,
  `price_dor` decimal(15,4) NOT NULL,
  `qa` decimal(15,4) NOT NULL COMMENT '汇率',
  `addtime` datetime NOT NULL,
  `create_by` int(11) NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='付款' AUTO_INCREMENT=1 ;
CREATE TABLE IF NOT EXISTS `receive` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `orderid` int(11) NOT NULL,
  `orderno` varchar(50) NOT NULL,
  `price_rmb` decimal(15,4) NOT NULL,
  `price_dor` decimal(15,4) NOT NULL,
  `qa` decimal(15,4) NOT NULL COMMENT '汇率',
  `addtime` datetime NOT NULL,
  `create_by` int(11) NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='收款' AUTO_INCREMENT=1 ;

