<?php

namespace Home\Controller;

use Think\Controller;
use DB;
use Think\Exception;

/*
For receiptno, D means Deposit when making an order

*/

class RestoreController extends Controller
{

    public function cash()
    {
        $order_info_model = M('order_info');
        $condition = [];
        $condition['total_amount'] = 0;
        $condition['order_id'] = ['lt',9568];
        $condition['order_step'] = ['notin', [-1, 12, 13]];

        $count = $order_info_model->where($condition)->count();
        for ($i = 0; $i < $count; $i = $i + 1000) {
            $list = $order_info_model->where($condition)->field('receiptno,corporate,order_id,balance,discount,order_sn,add_time')->limit($i, 1000)->select();

            foreach ($list as $order_info) {
                $receiptno = unserialize($order_info['receiptno']);
//                $type =  $receiptno['payment_method'];
                switch ($receiptno['payment_method']) {
                    case  'insurance_0':
                    case  'insurance_1':
                    case  'insurance':
                        $type = 2;
                        break;
                    default:
                        $type = 1;
                        break;
                }
                if ($type == 2) {
                    continue;
                }
                $update = [];
                if ($order_info['corporate']) {
                    $update['cash_wrong']=1;
                    $update['corporate'] = '';
                }

                $paylist = M('cash')->where(['order_id' => $order_info['order_id'], 'pay_amount' => ['neq', 0]])->field('sum(pay_amount) as amount')->find();
                $payed_amount = $paylist['amount'];

                $update['type'] = 1;
                $update['total_amount'] = $receiptno['total_order_amount'];
                //$update['old_balance'] = $order_info['balance'];

                $update['balance'] = $receiptno['total_order_amount'] - $payed_amount - $order_info['discount'];

                $order_info_model->where(['order_id' => $order_info['order_id']])->save($update);

                //
            }
        }
    }


    public function insurance()
    {

        $order_info_model = M('order_info');
        $condition = [];
        $condition['total_amount'] = 0;
        $condition['real_insurance'] = 0;
        $condition['order_step'] = ['notin', [-1, 12, 13]];

        $count = $order_info_model->where($condition)->count();
        for ($i = 0; $i < $count; $i = $i + 1000) {
            $list = $order_info_model->where($condition)->field('receiptno,corporate,order_id,balance,discount,order_sn,add_time')->limit($i, 1000)->select();

            foreach ($list as $order_info) {
                $receiptno = unserialize($order_info['receiptno']);
//                $type =  $receiptno['payment_method'];
                switch ($receiptno['payment_method']) {
                    case  'insurance_0':
                    case  'insurance_1':
                    case  'insurance':
                        $type = 2;
                        break;
                    default:
                        $type = 1;
                        break;
                }
                if ($type == 1) {
                    continue;
                }


                if ($order_info['corporate']) {
                    $corporate = unserialize($order_info['corporate']);

                    if ($corporate['corporate_status_set'] == 'proceed') {

                        $real_insurance = $receiptno['total_order_amount'];
                    } elseif ($corporate['corporate_status_set'] == 'less approval') {

                        if ($corporate['add_time'] < 1497196800) {
                            $real_insurance = $receiptno['total_order_amount'] - $corporate['corporate_amount'];
                        } else {
                            $real_insurance = $corporate['corporate_amount'];
                        }

                    } else {
                        $real_insurance = 0;
                    }

                } else {
                    $real_insurance = 0;
                }

//                $paylist = M('cash')->where(['order_id' => $order_info['order_id'], 'pay_amount' => ['neq', 0]])->field('sum(pay_amount) as amount')->find();
//                $payed_amount = $paylist['amount'];

                $update = [];

                $update['total_amount'] = $receiptno['total_order_amount'];
                $update['real_insurance'] = $real_insurance;
                $update['type'] =2;

//                $update['balance'] = $receiptno['total_order_amount'] - $real_insurance - $payed_amount - $order_info['discount'];

                $order_info_model->where(['order_id' => $order_info['order_id']])->save($update);

                //
            }
        }


    }


    public function cooperate(){
//        echo 111;
//        die();
        $order_info_model = M('order_info');
        $condition = [];
        $condition['order_id'] =['lt',9163];
//        $condition['real_insurance'] =['gt',0];
        $condition['type'] =2;
       $condition['insurance_wrong'] =0;
        $condition['order_step'] = ['notin', [-1, 12, 13]];

        $count = $order_info_model->where($condition)->count();
        for ($i = 0; $i < $count; $i = $i + 1000) {
            $list = $order_info_model->where($condition)->field('receiptno,corporate,order_id,balance,discount,order_sn,add_time,real_insurance')->limit($i, 1000)->select();

            foreach ($list as $order_info) {
                $receiptno = unserialize($order_info['receiptno']);
//                $type =  $receiptno['payment_method'];
                switch ($receiptno['payment_method']) {
                    case  'insurance_0':
                    case  'insurance_1':
                    case  'insurance':
                        $type = 2;
                        break;
                    default:
                        $type = 1;
                        break;
                }
                if ($type == 1) {
                    continue;
                }



                $corporate = '';

                if ($order_info['corporate']) {
                    $corporate = unserialize($order_info['corporate']);

                    if($corporate['corporate_amount'] !=$order_info['real_insurance']){
                        $update = [];
                        $update['insurance_wrong'] = 1;
                        $corporate['corporate_amount'] = $order_info['real_insurance'];
                        $corporate = serialize($corporate);
                        $update['corporate'] = $corporate;
                        $order_info_model->where(['order_id' => $order_info['order_id']])->save($update);
                    }

                }

            }
        }
    }



    public function cal()    {

        $order_info_model = M('order_info');
        $condition = [];
        $condition['type'] = 2;
        $condition['total_amount'] = 0;
        $condition['order_step'] = ['notin', [-1, 12, 13]];

        $count = $order_info_model->where($condition)->count();
        for ($i = 0; $i < $count; $i = $i + 1000) {
            $list = $order_info_model->where($condition)->field('receiptno,corporate,order_id,balance,discount,order_sn,add_time,real_insurance')->limit($i, 1000)->select();

            foreach ($list as $order_info) {
                $receiptno = unserialize($order_info['receiptno']);
//

                $paylist = M('cash')->where(['order_id' => $order_info['order_id'], 'pay_amount' => ['neq', 0]])->field('sum(pay_amount) as amount')->find();
                $payed_amount = $paylist['amount'];

                $update = [];
                $update['total_amount'] = 1;


                $update['balance'] = $receiptno['total_order_amount'] - $order_info['real_insurance'] - $payed_amount - $order_info['discount'];

                $order_info_model->where(['order_id' => $order_info['order_id']])->save($update);

                //
            }
        }


    }



    public function test()
    {
        $order_info_model = M('order_info');
        $condition = [];
        $condition['total_amount'] = 0;

        $count = $order_info_model->where($condition)->count();
        for ($i = 0; $i < $count; $i = $i + 1000) {
            $list = $order_info_model->where($condition)->field('receiptno,corporate,order_id,balance,discount,order_sn,add_time')->limit($i, 1000)->select();

            foreach ($list as $order_info) {
                $receiptno = unserialize($order_info['receiptno']);
//                $type =  $receiptno['payment_method'];
                switch ($receiptno['payment_method']) {
                    case  'insurance_0':
                    case  'insurance_1':
                        $type = 2;
                        break;
                    default:
                        $type = 1;
                        break;
                }
                if ($order_info['corporate']) {
                    $corporate = unserialize($order_info['corporate']);

                    if ($corporate['corporate_status_set'] == 'proceed') {

                        $real_insurance = $receiptno['total_order_amount'];
                    } elseif ($corporate['corporate_status_set'] == 'less approval') {

                        if ($corporate['add_time'] < 1497196800) {
                            $real_insurance = $receiptno['total_order_amount'] - $corporate['corporate_amount'];
                        } else {
                            $real_insurance = $corporate['corporate_amount'];
                        }

                    } else {
                        $real_insurance = 0;
                    }

                } else {
                    $real_insurance = 0;
                }

                $paylist = M('cash')->where(['order_id' => $order_info['order_id'], 'pay_amount' => ['neq', 0]])->field('sum(pay_amount) as amount')->find();
                $payed_amount = $paylist['amount'];

                $update = [];
                $update['id'] = $order_info['id'];
                $update['order_sn'] = $order_info['order_sn'];
                $update['total_amount'] = $receiptno['total_order_amount'];
                $update['insurance'] = $corporate['corporate_amount'] ?: 0;
                $update['old_balance'] = $order_info['balance'];
                $corporate['corporate_amount'] = $real_insurance;
                $update['corporate'] = serialize($corporate);
                $update['balance'] = $receiptno['total_order_amount'] - $real_insurance - $payed_amount - $order_info['discount'];

                $order_info_model->where(['order_id' => $order_info['order_id']])->save($update);

                //
            }
        }


    }


}