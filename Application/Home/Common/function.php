<?php

function getShopNameById($id)
{

	$shops_info = M('shops')->where(['shop_id'=>$id])->find();
	return $shops_info['title'];
}

function getShopNameByOrderId($order_id)
{
	$order_info = M('order_info')->where(['order_id'=>$order_id])->find();
	return getShopNameById($order_info['shop']);
}

function getMemberNameById($member_id)
{
	$member_info = M('member')->where(['member_id'=>$member_id])->find();
	return $member_info['member_name'];
}

//Top Up Section
function getBalance($order_id){ 
	$order_info = M('order_info')->where(['order_id'=>$order_id],['topup'=>1])->find();

	if($order_info['topup']==1){
		$balance = unserialize($order_info['customer_confirm']);
		return $balance['balance_remaining'];
	}
}

function getOrderStepNameById($id,$order_id)
{
	switch ($id) {

		case '-1':
			return 'Order Cancelled';
			break;

		case '3':
			return 'Waiting Shop Process';
			break;
		case '4':
			return 'Waiting for Insurance Approval';
			break;
		case '5':
			
			$order_info = M('order_info')->where(['order_id'=>$order_id])->find();

			$customer_confirm = unserialize($order_info['customer_confirm']);
			$payment_info = unserialize($order_info['receiptno']);
			$corporate_info = unserialize($order_info['corporate']);

			if($payment_info['payment_method']=="insurance_1" && $corporate_info['corporate_status_set']=='proceed'){ //if smart card and proceed
				return 'Waiting for Smart Card Report';
			}else{
				if($customer_confirm['less_approve_action']=='top_up' && $order_info['balance']>0){
					return 'Waiting Top Up';
				}else{
					return 'Waiting for Less Approve action';
				}
			}

			break;
		case '6':
			return 'Waiting for Customer Confirm';
			break;
		case '7':

			$order_info = M('order_info')->where(['order_id'=>$order_id])->find();

			$print_set = unserialize($order_info['print_set']);

			if($print_set['print_confirm']==2){
				return 'Waiting, Personal Frame';
			}elseif ($print_set['print_confirm']==3) {
				return 'Waiting, Personal Lens';
			}elseif ($print_set['print_confirm']==4) {
				return 'On Hold';
			}else{
				return 'Waiting for print order';
			}

			break;
		case '8':
			return 'Waiting for warehouse & workshop process';
			break;
		case '9':
				$order_info = M('order_info')->where(['order_id'=>$order_id])->find();

				$order_status = unserialize($order_info['warehouse_state']);
				return $order_status['warehouse_state'];

			break;
		case '10':
			return 'Waiting for shop arrive confirm';
			break;
		case '11':
			return 'Waiting for balance';
			break;
		case '12':
			return 'Closed';
			break;

		default:
			return 'Waiting shop Process';
			break;
	}
}

function getCategoryNameById($cate_id)
{
	if($cate_id == 0)
	{
		return 'TOP';
	}else{
		$cate_info = M('category')->find($cate_id);
		return $cate_info['cat_name'];		
	}
}

function getBrandNameById($brand_id)
{
	$brand_info = M('brand')->find($brand_id);
	return $brand_info['brand_name'];
}

function getAttrNameById($attr_lists)
{
	$attr_arr = explode(",", $attr_lists);

	$custom = 0;
	foreach ($attr_arr as $attr_id) 
	{
		$attr_info = M('attribute')->find($attr_id);

		if($attr_info){
			$attr_values[] = $attr_info['attr_values'];
		 }else{
		 	$custom++;
		 }

	}
	//print_r($attr_info);

	if($custom>0){
		return $custom;
	}else{
		return implode(",", $attr_values);
	}
		
}

?>