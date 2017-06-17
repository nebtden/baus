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
        $condition['order_step'] = ['notin', [-1,12,13]];


        $count = $order_info_model->where($condition)->count();
        for($i=0;$i<$count;$i=$i+1000){
            $list = $order_info_model->where($condition)->field('receiptno,corporate,order_id,balance,discount')->limit($i,1000)->select();

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

                        $insurance = $receiptno['total_order_amount'];
                    }elseif($corporate['corporate_status_set']=='less approval'){
                        if($corporate['add_time']<1511366400){
                            $insurance = $receiptno['total_order_amount'] - $corporate['corporate_amount'];
                        }else{
                            $insurance =   $corporate['corporate_amount'];
                        }

                    }else{
                        $insurance = 0;
                    }

                }else{
                    $insurance = 0;
                }

                $paylist = M('cash')
                    ->where(['order_id' => $order_info['order_id'], 'pay_amount' => ['neq', 0]])
                    ->field('sum(pay_amount) as amount')

                    ->find();
                $payed_amount = $paylist['amount'];

                $update = [];
                $update['total_amount'] = $receiptno['total_order_amount'];
                $update['old_insurance'] = $corporate['corporate_amount']?:0;
                $update['real_insurance'] = $insurance;
                $update['old_balance'] = $order_info['balance'];
                $corporate['corporate_amount'] = $insurance;
                $update['corporate'] = $corporate;
                $update['balance'] =
                    $receiptno['total_order_amount']
                    - $insurance
                    - $payed_amount
                    - $order_info['discount'];

                $order_info_model
                    ->where(['order_id'=>$order_info['order_id']])
                    ->save($update);

                //
            }
        }


    }





}