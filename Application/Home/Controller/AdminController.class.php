<?php

namespace Home\Controller;

use Think\Controller;

/*
For receiptno, D means Deposit when making an order

*/

class AdminController extends Controller
{

    public function __construct()
    {
        parent::__construct();

        if (empty(session('member_id'))) {
            $this->redirect('Login/index');
        } else {
            $member_group_id = session('member_group_id');
            $member_group_info = M('member_group')->where(['member_group_id' => $member_group_id])->find();
            $this->assign('member_group_info', $member_group_info);
        }
    }

    public function getCondition()
    {
        $condition = [];
        //$condition['receiptno'] =['like','%insurance_%'] ;
        //$condition['order_step'] =['in',[7,5,-1]] ;

        if (I('get.key_words')) {
            $key_words = I('get.key_words');
            $condition['order_sn'] = ['LIKE', "%$key_words%"];
            // $condition['consignee'] = ['LIKE',"%$key_words%"];
            // $condition['tel'] = ['LIKE',"%$key_words%"];
            // $condition['_logic'] = 'or';
        }
        if (I('get.member_shop')) {
            $member_shop = I('get.member_shop');
            $condition['shop'] = $member_shop;
            // $condition['consignee'] = ['LIKE',"%$key_words%"];
            // $condition['tel'] = ['LIKE',"%$key_words%"];
            // $condition['_logic'] = 'or';
        }

        if (I('get.sdate')) {
            $sdate = strtotime(I('get.sdate'));
            $condition['add_time'] = ['egt', $sdate];
        }
        if (I('get.edate')) {
            $edate = strtotime(I('get.edate')) + 60 * 60 * 24;
            $condition['add_time'] = ['elt', $edate];
        }
        if (I('get.sdate') && I('get.edate')) {
            $condition['add_time'] = array(array('egt', $sdate), array('elt', $edate));
        }

        if (I('get.mobile')) {
            $condition['mobile'] = I('get.mobile');
        }

        if (I('get.consignee')) {
            $condition['consignee'] = I('get.consignee');
        }


        if (I('get.is_print')) {
            if (I('get.is_print') == 1) {
                $condition['printdate'] = '';
            } else {
                $condition['printdate'] = ['neq', ''];
            }
        }
        return $condition;
    }


    public function order()
    {
        $order_info_model = M('order_info');

        $condition = $this->getCondition();

        $count = $order_info_model->where($condition)->count();

        $Page = new \Think\Page($count, 14);
        $Page->setConfig('header', '<li class="rows"><b>[%NOW_PAGE%</b>/<b>%TOTAL_PAGE%]</b> Total <b>%TOTAL_ROW%</b> records </li>');
        $Page->setConfig('prev', 'prev page');
        $Page->setConfig('next', 'next page');
        $Page->setConfig('last', 'last page');
        $Page->setConfig('first', 'first page');
        $Page->setConfig('theme', '%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE%  %HEADER%');
        $show = $Page->show();

        $order_info_lists = $order_info_model->where($condition)->order('order_id DESC')->limit($Page->firstRow . ',' . $Page->listRows)->field('*,FROM_UNIXTIME(add_time) AS addtime')->select();
        $this->assign('order_info_lists', $order_info_lists);

        $this->assign('page', $show);
        $this->display();
    }


    public function returnBack()
    {
        $order_id = I('get.order_id');
        $updatedata = [];
        $updatedata['order_step'] = 4;
        $updatedata['corporate'] = '';
        $updatedata['is_insurance_checked'] = 0;
        $result = M('order_info')->where(['order_id' => $order_id])->save($updatedata);
        if ($result) {
            $this->success('return to insurance success! ', U('Admin/order'));
        } else {
            $this->error('return to insurance  fail!');
        }
    }

    public function savegoods()
    {

        $order_id = I('post.order_id');
        $order_info = M('order_info')->where(['order_id' => $order_id])->find();
        if ($order_info['customer_confirm']) {
            $data = unserialize($order_info['customer_confirm']);
            $data['remark'] = $data['remark'] . I('post.remark');
        } else {
            $data = [];
            $data['remark'] = I('post.remark');
        }

        $data['less_approve_pay_method'] = 'combine';

        $update_data = [];
        $update_data['customer_confirm'] = serialize($data);
        $update_data['is_changed_goods'] = 1;
        $result = M('order_info')->where(['order_id' => $order_id])->save($update_data);
        if ($result) {
            $this->success('return to insurance success! ', U('Admin/order'));
        } else {
            $this->error('return to insurance  fail!');
        }

    }

    public function balance()
    {

        $order_id = I('get.order_id');
        $order_info = M('order_info')->where(['order_id' => $order_id])->find();
        $paylist = M('cash')->where(['order_id' => $order_id, 'pay_amount' => ['neq', 0]])->select();
        $balance = $order_info['balance'];
        $corporate = unserialize($order_info['corporate']);


        $this->assign('order_info', $order_info);
        $this->assign('paylist', $paylist);
        $this->assign('balance', $balance);
        $this->assign('corporate', $corporate);
        $this->display();
    }

    public function goods()
    {


        $order_id = I('get.order_id');
        $order_info = M('order_info')->where(['order_id' => $order_id])->find();
        $paylist = M('cash')->where(['order_id' => $order_id, 'pay_amount' => ['neq', 0]])->select();
        $balance = $order_info['balance'];
        $corporate = unserialize($order_info['corporate']);


        $this->assign('order_info', $order_info);
        $this->assign('paylist', $paylist);
        $this->assign('balance', $balance);
        $this->assign('corporate', $corporate);
        $this->display();

    }

    public function balancesave()
    {


        $order_id = I('post.order_id');
        $order_info = M('order_info')->where(['order_id' => $order_id])->find();
        $updatedata = [];
        $updatedata['order_step'] = 7;

        $orders_count = M('order_info')->field('count(order_id) as order_count')->select();
        $orders_count = $orders_count[0]['order_count'] + 1;

        //收取买家费用
        $payed_money = insertpaidmoney($order_info, $orders_count);


        //更新余额
        M('order_info')->where(['order_id' => $order_id])->save(['balance' => $order_info['balance'] - -$payed_money]);


        $result = M('order_info')->where(['order_id' => $order_id])->save($updatedata);
        if ($result) {
            $this->success('return to insurance success! ', U('Admin/order'));
        } else {
            $this->error('return to insurance  fail!');
        }
    }


    public function discount()
    {
        $order_id = I('get.order_id');
        $order_info = M('order_info')->where(['order_id' => $order_id])->find();

        $paylist = M('cash')->where(['order_id' => $order_id, 'pay_amount' => ['neq', 0]])->select();
        $balance = $order_info['balance'];
        $corporate = unserialize($order_info['corporate']);


        $this->assign('order_info', $order_info);
        $this->assign('paylist', $paylist);
        $this->assign('balance', $balance);
        $this->assign('corporate', $corporate);
        $this->display();
    }

    public function discountsave()
    {

        $order_id = I('post.order_id');
        $updatedata = [];
        $updatedata['balance'] = 0;

        $updatedata['is_insurance_checked'] = 0;
        $result = M('order_info')->where(['order_id' => $order_id])->save($updatedata);
        if ($result) {
            $this->success('return to insurance success! ', U('Admin/order'));
        } else {
            $this->error('return to insurance  fail!');
        }
    }


}