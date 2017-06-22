<?php

namespace Home\Controller;

use Think\Controller;
use DB;
use Think\Exception;

/*
For receiptno, D means Deposit when making an order

*/

class TestController extends Controller
{


    public function test(){
        $order_info_model = M('order_info');
        $condition = [];
        $condition['total_amount'] = 0;
        $condition['order_step'] = ['in',];

        $count = $order_info_model->where($condition)->count();
        for($i=0;$i<$count;$i=$i+1000){
            $list = $order_info_model->where($condition)->field('receiptno,corporate,order_id,balance,discount,order_sn,add_time,allow_balance,order_step')->limit($i,1000)->select();

            foreach ($list as $order_info){
                $receiptno = unserialize($order_info['receiptno']);
//                $type =  $receiptno['payment_method'];
                switch ($receiptno['payment_method']){
                    case  'insurance_0':
                    case  'insurance_1':
                        $type = 2;
                        break;
                    default:
                        $type = 1;
                        break;
                }
                if($order_info['corporate']){
                    $corporate = unserialize($order_info['corporate']);

                    if($corporate['corporate_status_set']=='proceed'){

                        $real_insurance = $receiptno['total_order_amount'];
                    }elseif($corporate['corporate_status_set']=='less approval'){

                        if($corporate['add_time']<1497196800){
                            $real_insurance = $receiptno['total_order_amount'] - $corporate['corporate_amount'];
                        }else{
                            $real_insurance =   $corporate['corporate_amount'];
                        }

                    }else{
                        $real_insurance = 0;
                    }

                }else{
                    $real_insurance = 0;
                }

                $paylist = M('cash')
                    ->where(['order_id' => $order_info['order_id'], 'pay_amount' => ['neq', 0]])
                    ->field('sum(pay_amount) as amount')

                    ->find();
                $payed_amount = $paylist['amount']?:0;

                $update = [];

                $update['order_id'] = $order_info['order_id'];
                $update['order_sn'] = $order_info['order_sn'];
                $update['total_amount'] = $receiptno['total_order_amount']?:0;
                $update['insurance'] = $corporate['corporate_amount']?:0;
                $update['admin_set_balance'] = $order_info['allow_balance'];
                $update['balance'] = $order_info['balance'];
                $update['paid_money'] = $payed_amount;
                $update['type'] = $type;
                $update['discount'] = $order_info['discount'];;
                $update['insurance_type'] = $corporate['corporate_status_set']?:'';
                $update['order_step'] = $order_info['order_step'];
                $update['add_time'] = $order_info['add_time'];



                M('order_new')->add($update);

                //
            }
        }


    }





}