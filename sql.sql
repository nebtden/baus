
-- 增加状态
ALTER TABLE `oms_customer`
ADD COLUMN `state`  tinyint(1) NOT NULL DEFAULT 1 COMMENT '1  默认正常  0 删除' AFTER `c_gender`，
ADD COLUMN `c_amount`  float(10,2) NOT NULL DEFAULT 0 COMMENT '消费总金额' AFTER `state`,
ADD COLUMN `c_type`  tinyint(1) NOT NULL DEFAULT 1 COMMENT '消费类型 1 现金  2 保险公司' AFTER `c_amount`;

-- 153 id  4000镜片  1000镜架



