
-- 增加状态
ALTER TABLE `oms_customer`
ADD COLUMN `state`  tinyint(1) NOT NULL DEFAULT 1 COMMENT '1  默认正常  0 删除' AFTER `c_gender`，
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
DEFAULT 0
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


--  保险公司不能有尾款 ，需要管理员操作
--  保险公司不全款通过， 打折需要管理员操作
--  保险公司进行更改  @龙魁

-- 153 id  4000镜片  1000镜架
UPDATE `db_baus`.`oms_order_info` SET `order_id`='153', `order_sn`='201705211201059', `user_id`='15', `consignee`='James Konan', `address`='mba way', `mobile`='727800700', `email`='jk@gmail.com', `member_id`='2', `postscript`='test', `shipping_id`='0', `pay_id`='0', `goods_amount`='4000.00', `shipping_fee`='0.00', `order_amount`='4000.00', `add_time`='1495378865', `confirm_time`='0', `pay_time`='0', `shipping_time`='0', `shop`='26', `distance`='a:20:{s:9:\"sph_right\";s:1:\"0\";s:9:\"cyl_right\";s:0:\"\";s:10:\"axis_right\";s:0:\"\";s:11:\"prism_right\";s:0:\"\";s:10:\"base_right\";s:0:\"\";s:13:\"ovision_right\";s:0:\"\";s:12:\"vision_right\";s:0:\"\";s:9:\"add_right\";s:0:\"\";s:8:\"pd_right\";s:0:\"\";s:8:\"sph_left\";s:1:\"0\";s:8:\"cyl_left\";s:0:\"\";s:9:\"axis_left\";s:0:\"\";s:10:\"prism_left\";s:0:\"\";s:9:\"base_left\";s:0:\"\";s:12:\"ovision_left\";s:0:\"\";s:11:\"vision_left\";s:0:\"\";s:8:\"add_left\";s:0:\"\";s:7:\"pd_left\";s:0:\"\";s:6:\"cardno\";s:24:\"111111111111111111111111\";s:8:\"add_time\";i:1495378865;}', `payment_price`='0.00', `nopayment_price`='0.00', `warehouse_state`=NULL, `order_status_set`=NULL, `products_shipped`=NULL, `receiptno`='a:10:{s:13:\"payment_price\";s:0:\"\";s:15:\"nopayment_price\";s:0:\"\";s:9:\"receiptno\";s:4:\"D148\";s:7:\"cashier\";s:9:\"727800700\";s:12:\"sales_person\";s:9:\"727800700\";s:11:\"optometrist\";s:9:\"727800700\";s:14:\"payment_method\";s:11:\"insurance_1\";s:13:\"insurancetext\";s:7:\"1111111\";s:8:\"add_time\";i:1495378865;s:18:\"total_order_amount\";d:5000;}', `customer_card`=NULL, `customer_confirm`=NULL, `print_set`=NULL, `shop_arrive`=NULL, `receiptno2`=NULL, `stock_source`=NULL, `lensname`=NULL, `lensprice`='0', `discountseals`='0', `sealstxt`='a:5:{s:8:\"lensname\";s:7:\"simon\'s\";s:9:\"lensprice\";s:4:\"1000\";s:13:\"discountseals\";s:1:\"0\";s:13:\"special_marks\";s:0:\"\";s:8:\"add_time\";i:1495378865;}', `topup`='0', `corporate`=NULL, `balance`='0', `printdate`='0', `order_step`='4', `customer_care`='0', `customer_care_remark`=NULL, `cancel_remark`=NULL, `order_closed`='0', `order_closed_date`=NULL WHERE (`order_id`='153');




