<?php

namespace Home\Controller;

use Think\Controller;
use DB;
use Think\Exception;

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
        $condition['order_step'] =['in',[12,13]] ;
        $condition['is_insurance_topup'] =0 ;
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

        $order_info_lists = $order_info_model
            ->where($condition)
            ->order('order_id DESC')
            ->limit($Page->firstRow . ',' . $Page->listRows)
            ->field('*,FROM_UNIXTIME(add_time) AS addtime')
            ->select();
        $this->assign('order_info_lists', $order_info_lists);

        $this->assign('page', $show);
        $this->display();
    }

    public function insuranceedit(){

        $order_id =  I('get.order_id');
        $order_info = M('order_info')->where(['order_id' => $order_id])->find();

        $this->assign('order_info', $order_info);
        $this->display();

    }

    public function saveinsurance(){
        $order_id =  I('post.order_id');
        $amount = I('post.amount');
        $type = I('post.type');
        $remark = I('post.remark');

        $data = [];
        $data['order_id'] = $order_id;
        $data['amount'] = $amount;
        $data['type'] = $type;
        $data['remark'] = $remark;
        $model = M('insurance');

        try{
            $model->startTrans();
            $result = $model->add($data);

            //更改订单表状态
            $result2 = M('order_info')->where(['order_id'=>$order_id])->save([
                'is_insurance_topup'=>1
            ]);
            //增加日志
            insertlog($order_id,' insurance have get the money');

            $model->commit();
        }catch(\Exception $e) {
            $model->rollback();
        }


        if ($result) {
            $this->success('insurance top up success', U('Finance/insurance'));
        } else {
            $this->error('insurance top up fail!');
        }
    }


    //public function getGoodsList

}