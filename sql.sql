
-- 增加状态
ALTER TABLE `oms_customer`
ADD COLUMN `state`  tinyint(1) NOT NULL DEFAULT 1 COMMENT '1  默认正常  0 删除' AFTER `c_gender`,
ADD COLUMN `c_amount`  float(10,2) NOT NULL DEFAULT 0 COMMENT '消费总金额' AFTER `state`,
ADD COLUMN `c_type`  tinyint(1) NOT NULL DEFAULT 1 COMMENT '消费类型 1 现金  2 保险公司' AFTER `c_amount`;

CREATE TABLE `oms_insurance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `amount` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '保险公司收取金额',
  `type` tinyint(1) NOT NULL COMMENT '1 Mpesa  2 cash 3ETF ',
  `remark` varchar(255) NOT NULL COMMENT '存储 mpesa、transit_id   等相关信息',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;


ALTER TABLE `oms_order_info`
ADD COLUMN `is_insurance_topup`  tinyint(1) NOT NULL COMMENT '是否保险公司核销过' AFTER `order_closed_date`;

ALTER TABLE `oms_order_info`
ADD COLUMN `is_insurance_checked`  tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否保险公司检查过' AFTER `order_closed_date`;

-- 数据库更改
ALTER TABLE `oms_customer`
MODIFY COLUMN `c_mobile`  varchar(15) NOT NULL AFTER `c_name`;

ALTER TABLE `oms_member_group`
ADD COLUMN `shop_arrive`  tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Waiting for shop arrive confirm 权限' AFTER `internal-print`


ALTER TABLE `oms_order_info`
ADD COLUMN `is_changed_goods`  tinyint(1) NOT NULL DEFAULT 0 COMMENT '买家是否更改过商品' AFTER `is_insurance_topup`,
ADD COLUMN `discount`  int(11) NOT NULL DEFAULT 0 COMMENT '打折' AFTER `is_insurance_topup`;
DEFAULT 0；

ALTER TABLE `oms_customer`
ADD COLUMN `birthday`  date NOT NULL AFTER `c_type`;

ALTER TABLE `oms_cash`
ADD COLUMN `type`  tinyint(1) NOT NULL DEFAULT 1 AFTER `shop`

CREATE TABLE `oms_admin_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_name` varchar(255) NOT NULL DEFAULT '',
  `order_id` int(11) NOT NULL DEFAULT '0',
  `operate` varchar(255) NOT NULL DEFAULT '',
  `add_time` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;

ALTER TABLE `oms_order_info`
ENGINE=InnoDB;

ALTER TABLE `oms_cash`
ENGINE=InnoDB;

ALTER TABLE `oms_order_goods`
ENGINE=InnoDB;



ALTER TABLE `oms_order_info`
ADD COLUMN `allow_balance`  float(11,0) NOT NULL DEFAULT 0 AFTER `corporate`;



