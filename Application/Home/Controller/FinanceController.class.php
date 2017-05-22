<?php

namespace Home\Controller;

use Think\Controller;


/*
For receiptno, D means Deposit when making an order

*/

class FinanceController extends Controller
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

    public function getCondition(){
        $condition = [];
        $condition['receiptno'] =['like','%insurance_%'] ;
        return $condition;
    }


    public function insurance(){
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

        $order_info_lists = $order_info_model->where($condition)->order('order_id DESC')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('order_info_lists', $order_info_lists);

        $this->assign('page', $show);
        $this->display();
    }


    //public function getGoodsList

}