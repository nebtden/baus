
-- 增加状态
ALTER TABLE `oms_customer`
ADD COLUMN `state`  tinyint(1) NOT NULL DEFAULT 1 COMMENT '1  默认正常  0 删除' AFTER `c_gender`，
ADD COLUMN `c_amount`  float(10,2) NOT NULL DEFAULT 0 COMMENT '消费总金额' AFTER `state`,
ADD COLUMN `c_type`  tinyint(1) NOT NULL DEFAULT 1 COMMENT '消费类型 1 现金  2 保险公司' AFTER `c_amount`;

-- 153 id  4000镜片  1000镜架
UPDATE `db_baus`.`oms_order_info` SET `order_id`='153', `order_sn`='201705211201059', `user_id`='15', `consignee`='James Konan', `address`='mba way', `mobile`='727800700', `email`='jk@gmail.com', `member_id`='2', `postscript`='test', `shipping_id`='0', `pay_id`='0', `goods_amount`='4000.00', `shipping_fee`='0.00', `order_amount`='4000.00', `add_time`='1495378865', `confirm_time`='0', `pay_time`='0', `shipping_time`='0', `shop`='26', `distance`='a:20:{s:9:\"sph_right\";s:1:\"0\";s:9:\"cyl_right\";s:0:\"\";s:10:\"axis_right\";s:0:\"\";s:11:\"prism_right\";s:0:\"\";s:10:\"base_right\";s:0:\"\";s:13:\"ovision_right\";s:0:\"\";s:12:\"vision_right\";s:0:\"\";s:9:\"add_right\";s:0:\"\";s:8:\"pd_right\";s:0:\"\";s:8:\"sph_left\";s:1:\"0\";s:8:\"cyl_left\";s:0:\"\";s:9:\"axis_left\";s:0:\"\";s:10:\"prism_left\";s:0:\"\";s:9:\"base_left\";s:0:\"\";s:12:\"ovision_left\";s:0:\"\";s:11:\"vision_left\";s:0:\"\";s:8:\"add_left\";s:0:\"\";s:7:\"pd_left\";s:0:\"\";s:6:\"cardno\";s:24:\"111111111111111111111111\";s:8:\"add_time\";i:1495378865;}', `payment_price`='0.00', `nopayment_price`='0.00', `warehouse_state`=NULL, `order_status_set`=NULL, `products_shipped`=NULL, `receiptno`='a:10:{s:13:\"payment_price\";s:0:\"\";s:15:\"nopayment_price\";s:0:\"\";s:9:\"receiptno\";s:4:\"D148\";s:7:\"cashier\";s:9:\"727800700\";s:12:\"sales_person\";s:9:\"727800700\";s:11:\"optometrist\";s:9:\"727800700\";s:14:\"payment_method\";s:11:\"insurance_1\";s:13:\"insurancetext\";s:7:\"1111111\";s:8:\"add_time\";i:1495378865;s:18:\"total_order_amount\";d:5000;}', `customer_card`=NULL, `customer_confirm`=NULL, `print_set`=NULL, `shop_arrive`=NULL, `receiptno2`=NULL, `stock_source`=NULL, `lensname`=NULL, `lensprice`='0', `discountseals`='0', `sealstxt`='a:5:{s:8:\"lensname\";s:7:\"simon\'s\";s:9:\"lensprice\";s:4:\"1000\";s:13:\"discountseals\";s:1:\"0\";s:13:\"special_marks\";s:0:\"\";s:8:\"add_time\";i:1495378865;}', `topup`='0', `corporate`=NULL, `balance`='0', `printdate`='0', `order_step`='4', `customer_care`='0', `customer_care_remark`=NULL, `cancel_remark`=NULL, `order_closed`='0', `order_closed_date`=NULL WHERE (`order_id`='153');




