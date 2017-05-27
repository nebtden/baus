<?php

namespace Home\Controller;

use Think\Controller;

/*
For receiptno. B means Balance.

*/

class IndexController extends Controller
{
    private $customer_type =[
        1=>'Cash',
        2=>'Insurance'
    ];

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

    private function getCondition()
    {

        //echo I('get.order_step')."<br />";
        if (I('get.order_step')) {
            $order_step = I('get.order_step');
            switch (I('get.order_step')) {
                case '3':
                    $condition['order_step'] = ['between', '0,3'];
                    break;
                case '4':
                    $condition['order_step'] = 4;
                    break;
                case '5':
                    $condition['order_step'] = 5;
                    break;
                case '6':
                    $condition['order_step'] = 6;
                    break;
                case '7':
                    $condition['order_step'] = 7;
                    break;
                case '8':
                    $condition['order_step'] = 8;
                    break;
                case '9':
                    $condition['order_step'] = 9;
                    break;
                case '10':
                    $condition['order_step'] = 10;
                    break;
                case '11':
                    $condition['order_step'] = 11;
                    break;
                case '12':
                    $condition['order_step'] = 12;
                    break;
                default:
                    $condition['order_step'] = ['between', '-2,13'];

            }

        } else {
            $condition['order_step'] = ['between', '-2,13'];
        }


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
        $member_id = session('member_id');
        $member_info = M('member')->where(['member_id' => $member_id])->find();

        if (($member_info['member_role'] == 2)) //Head office is 1 show all orders, if 2, show orders in other branches
        {
            $condition['shop'] = session('member_shop');
        }
        return $condition;
    }

    public function index()
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

        $order_info_lists = $order_info_model->where($condition)->order('order_id DESC')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('order_info_lists', $order_info_lists);
        $shop_lists = M('shops')->select();
        $this->assign('shop_lists', $shop_lists);
        $this->assign('page', $show);
        session('group', 'customer');
        $this->display();
    }


    public function export()
    {
        $order_info_model = M('order_info');


        $condition = $this->getCondition();
        $order_info_lists = $order_info_model->where($condition)
            ->field('order_sn,order_id,mobile,consignee,shop,add_time,receiptno,order_step')->order('order_id DESC')->select();
        $file = 'Order' . date('YmdHis', time());
        $downloads = dirname(dirname(dirname(dirname(__FILE__)))). DIRECTORY_SEPARATOR.'public' . DIRECTORY_SEPARATOR . 'downs' .DIRECTORY_SEPARATOR. $file . '.csv';
        $fp = fopen($downloads, 'a');
        $title = ['Order No', 'mobile', 'consignee', 'Shop','Time','Total Price','Status','goods_brand','goods_model_no','goods_number','goods_price','goods_attr'];
        fputcsv($fp, $title);

        $model_goods = M('order_goods');
//        `goods_name` varchar(120) NOT NULL,
//  `goods_cat` int(10) NOT NULL,
//  `goods_brand` varchar(45) NOT NULL,
//  `goods_model_no` varchar(45) NOT NULL,
//  `goods_number` int(2) NOT NULL,
//  `goods_price` decimal(10,2) NOT NULL,
//  `goods_attr` varchar(255) NOT NULL,

        foreach ($order_info_lists as $order_info) {

            $goods_info = $model_goods->where(['order_id'=>$order_info['order_id']])->select();
            foreach ($goods_info as $goods){
                $column = [];
                $column['Order No'] = $order_info['order_sn'];
                $column['mobile'] = $order_info['mobile'];
                $column['consignee'] = $order_info['consignee'];
                $column['Shop'] = getShopNameById($order_info['shop']);
                $column['Time'] = date('j F Y, H:i:s',$order_info['add_time']);
                $column['Total Price'] = unserialize($order_info['receiptno'])['total_order_amount'];
                $column['Status'] = getOrderStepNameById($order_info['order_step'],$order_info['order_id']);

                $column['goods_brand'] =  $goods['goods_brand'];
                $column['goods_model_no'] =  $goods['goods_model_no'];
                $column['goods_number'] =  $goods['goods_number'];
                $column['goods_price'] =  $goods['goods_price'];
                $column['goods_attr'] =  $goods['goods_attr'];


            }


            fputcsv($fp, $column);

        }

        fclose($fp);
        $downloadurl = 'http://' . $_SERVER['HTTP_HOST'].'/public/downs/' . $file . '.csv';
        $this->success('export success',$downloadurl);
//        $downloadurl = 'http://' . $_SERVER['HTTP_HOST'].'/public/downs/' . $file . '.csv';
//        $this->assign('downloadurl',$downloadurl);
//        $this->display();

    }

    public function orderShow()
    {

        $order_id = I('get.order_id');
        $order_info = M('order_info')->where(['order_id' => $order_id])->find();

        $order_goods_lists = M('order_goods')->where(['order_id' => $order_id])->select();

        $sealstxt_info = unserialize($order_info['sealstxt']);
        $distance_info = unserialize($order_info['distance']);

        $receiptno = unserialize($order_info['receiptno']);

        $customer_card = unserialize($order_info['customer_card']);
        $customer_confirm = unserialize($order_info['customer_confirm']);
        $print_set = unserialize($order_info['print_set']);
        $warehouse_state = unserialize($order_info['warehouse_state']);
        $corporate = unserialize($order_info['corporate']);
        $products_shipped = unserialize($order_info['products_shipped']);
        $shop_arrive = unserialize($order_info['shop_arrive']);
        $receiptno2 = unserialize($order_info['receiptno2']);
        $balance = 0;

        if ($order_info['order_step'] == 11) { // If last step, calculate any remaining balance

            if ($receiptno['payment_method'] == 'insurance_1' || $receiptno['payment_method'] == "insurance_0") {
                if ($customer_confirm['less_approve_action']) {
                    $balance = $customer_confirm['balance_remaining'];
                }
            } else {
                $balance = $order_info['balance'];
            }
        }

        if ($balance < 0) {
            $change = abs($balance);
            $this->assign('change', $change);
        }

        $paylist = M('cash')->where([
            'order_id'=>$order_id,
             'pay_amount'=>['neq',0]
        ])->select();

        $this->assign('order_goods_lists', $order_goods_lists);
        $this->assign('order_info', $order_info);
        $this->assign('sealstxt_info', $sealstxt_info);
        $this->assign('distance_info', $distance_info);
        $this->assign('receiptno', $receiptno);
        $this->assign('customer_card', $customer_card);
        $this->assign('customer_confirm', $customer_confirm);
        $this->assign('print_set', $print_set);
        $this->assign('warehouse_state', $warehouse_state);
        $this->assign('corporate', $corporate);
        $this->assign('products_shipped', $products_shipped);
        $this->assign('shop_arrive', $shop_arrive);
        $this->assign('receiptno2', $receiptno2);
        $this->assign('balance_remaining', $balance);
        $this->assign('paylist', $paylist);
        $this->display();
    }

    public function orderProcess()
    {
        $step_name = I('post.step_name');
        $order_id = I('post.order_id');
        $order_info = M('order_info')->where(['order_id' => $order_id])->find();

        if ($step_name == 'editprice') {
            unset($update_data);
            unset($data);

            $data_seals['lensname'] = I('post.lensname');
            $data_seals['lensprice'] = I('post.lensprice');
            $data_seals['discountseals'] = I('post.discountseals');
            $data_seals['special_marks'] = I('post.special_marks');
            $data_seals['add_time'] = time();

            $update_data['sealstxt'] = serialize($data_seals);


            $data_distance['sph_right'] = I('post.sph_right');
            $data_distance['cyl_right'] = I('post.cyl_right');
            $data_distance['axis_right'] = I('post.axis_right');
            $data_distance['prism_right'] = I('post.prism_right');
            $data_distance['base_right'] = I('post.base_right');
            $data_distance['ovision_right'] = I('post.ovision_right');
            $data_distance['vision_right'] = I('post.vision_right');
            $data_distance['add_right'] = I('post.add_right');
            $data_distance['pd_right'] = I('post.pd_right');
            $data_distance['sph_left'] = I('post.sph_left');
            $data_distance['cyl_left'] = I('post.cyl_left');
            $data_distance['axis_left'] = I('post.axis_left');
            $data_distance['prism_left'] = I('post.prism_left');
            $data_distance['base_left'] = I('post.base_left');
            $data_distance['ovision_left'] = I('post.ovision_left');
            $data_distance['vision_left'] = I('post.vision_left');
            $data_distance['add_left'] = I('post.add_left');
            $data_distance['pd_left'] = I('post.pd_left');
            $data_distance['cardno'] = I('post.cardno');
            $data_distance['add_time'] = time();

            $update_data['distance'] = serialize($data_distance);

            $data_receiptno['payment_price'] = I('post.payment_price');
            $data_receiptno['nopayment_price'] = I('post.nopayment_price');
            $data_receiptno['receiptno'] = I('post.receiptno');
            $data_receiptno['cashier'] = I('post.cashier');
            $data_receiptno['sales_person'] = I('post.sales_person');
            $data_receiptno['optometrist'] = I('post.optometrist');
            $data_receiptno['payment_method'] = I('post.payment_method');
            $data_receiptno['insurancetext'] = I('post.insurancetext');
            $data_receiptno['add_time'] = time();

            if ($data_receiptno['payment_method'] == 'insurance') {
                $update_data['order_step'] = 4;
            } else {
                $update_data['order_step'] = 7;
            }

            $update_data['receiptno'] = serialize($data_receiptno);

            $data_cash['order_id'] = $order_info['order_id'];
            $data_cash['order_sn'] = $order_info['order_sn'];
            $data_cash['shop'] = $order_info['shop'];
            $data_cash['pay_amount'] = I('post.payment_price');
            $data_cash['payment_method'] = I('post.payment_method');
            $data_cash['receipt_no'] = I('post.receipt_no');
            $data_cash['reverse_status'] = 0;
            $data_cash['member_id'] = session('member_id');
            $data_cash['add_time'] = time();

            M('cash')->add($data_cash);

            M('order_info')->where(['order_id' => $order_id])->save($update_data);

            if (M('order_info')->where(['order_id' => $order_id])->save($update_data) !== FALSE) {
                $this->success('save success', U('Index/orderShow', array('order_id' => $order_id)));
            } else {
                $this->error('save fail', U('Index/orderShow', array('order_id' => $order_id)));
            }
        }




        /* ====== Step 6 Check if smart card was swiped. ONLY FOR INSURANCE WITH SMART CARD, LESS APPROVAL ====== */
        if ($step_name == 'edit_customer_card_less') {

            unset($update_data);
            unset($data);

            $data['insurance_smart_card_remark'] = I('post.insurance_smart_card_remark');

            if (I('post.customer_card_confirm') == 1) {
                $data['customer_card_confirm'] = I('post.customer_card_confirm');
            } else {
                $data['customer_card_confirm'] = 0;
            }

            $update_data['order_step'] = 7;

            $data['add_time'] = $data['add_time'];

            $update_data['customer_card'] = serialize($data);

            M('order_info')->where(['order_id' => $order_id])->save($update_data); //update customer_card done as step6

            if (M('order_info')->where(['order_id' => $order_id])->save($update_data) !== FALSE) {
                $this->success('save success', U('Index/orderShow', array('order_id' => $order_id)));
            } else {
                $this->error('save fail', U('Index/orderShow', array('order_id' => $order_id)));
            }

        }
        /* ====== Step 6 Check if smart card was swiped. ONLY FOR INSURANCE WITH SMART CARD, LESS APPROVAL END ====== */

        if ($step_name == 'editprint') {
            unset($update_data);
            unset($data);

            $data['print_confirm'] = I('post.print_confirm');
            $data['add_time'] = time();


            if (I('post.print_confirm') == 1) {
                $update_data['order_step'] = 8;
            }

            $update_data['print_set'] = serialize($data);

            M('order_info')->where(['order_id' => $order_id])->save($update_data);

            if (M('order_info')->where(['order_id' => $order_id])->save($update_data) !== FALSE) {
                $this->success('save success', U('Index/orderShow', array('order_id' => $order_id)));
            } else {
                $this->error('save fail', U('Index/orderShow', array('order_id' => $order_id)));
            }
        }



        if ($step_name == 'edit_shop_arrive') {
            unset($update_data);
            unset($data);

            $data['shop_arrive_set'] = I('post.shop_arrive_set');
            $data['add_time'] = time();

            $update_data['order_step'] = 11;
            $update_data['shop_arrive'] = serialize($data);

            M('order_info')->where(['order_id' => $order_id])->save($update_data);

            if (M('order_info')->where(['order_id' => $order_id])->save($update_data) !== FALSE) {
                $this->success('save success', U('Index/orderShow', array('order_id' => $order_id)));
            } else {
                $this->error('save fail', U('Index/orderShow', array('order_id' => $order_id)));
            }
        }

        if ($step_name == 'editorderstatus') {
            unset($update_data);
            unset($data);

            $data['receiptno2'] = I('post.receiptno2');
            $data['add_time'] = time();

            if (I('post.final_bal') - I('post.balance_amount') == 0) {
                $update_data['order_step'] = 13;
                $update_data['order_status_set'] = I('post.order_status_set');
                $update_data['balance'] = 0;
            } else {
                //$update_data['order_step'] = 11;
                $update_data['order_status_set'] = "balance";
                $update_data['balance'] = I('post.final_bal') - I('post.balance_amount');
            }

            $update_data['receiptno2'] = serialize($data);

            $cash_count = M('cash')->field('count(cash_id) as cash_count')->select();
            $cash_count = $cash_count[0]['cash_count'] + 1;

            $data_cash['order_id'] = $order_info['order_id'];
            $data_cash['order_sn'] = $order_info['order_sn'];
            $data_cash['pay_amount'] = I('post.balance_pay_method') ? I('post.balance_amount') : 0;
            $data_cash['payment_method'] = I('post.balance_pay_method') ? 'balance-' . I('post.balance_pay_method') : 'no-balance-close';
            $data_cash['receipt_no'] = 'B' . $cash_count;
            $data_cash['payment_remark'] = I('post.balance_remark');
            $data_cash['reverse_status'] = 0;
            $data_cash['cancel_status'] = 0;
            $data_cash['member_id'] = session('member_id');
            $data_cash['add_time'] = time();

            M('cash')->add($data_cash);

            M('order_info')->where(['order_id' => $order_id])->save($update_data);

            if (M('order_info')->where(['order_id' => $order_id])->save($update_data) !== FALSE) {
                $this->success('save success', U('Index/orderShow', array('order_id' => $order_id)));
            } else {
                $this->error('save fail', U('Index/orderShow', array('order_id' => $order_id)));
            }
        }

    }

    public function moveToNext()
    {

        $order_id = I('get.order_id');

        $order_info = M('order_info')->where(['order_id' => $order_id])->find();

        $print_set = unserialize($order_info['print_set']);

        $this->assign('step7_info', $print_set);
        $this->assign('order_id', $order_id);

        $this->display();

    }

    public function move_next()
    {

        $order_id = I('post.order_id');

        $order_info = M('order_info')->where(['order_id' => $order_id])->find();

        $print_set = unserialize($order_info['print_set']);

        $print_set['print_confirm'] = 1;
        $print_set['step7_remark'] = I('post.step7_remark');

        $update_data['print_set'] = serialize($print_set);
        $update_data['order_step'] = 8;

        M('order_info')->where(['order_id' => $order_id])->save($update_data);

        if (M('order_info')->where(['order_id' => $order_id])->save($update_data) !== FALSE) {
            $this->success('Update success', U('Index/orderShow', array('order_id' => $order_id)));
        } else {
            $this->error('Update fail', U('Index/orderShow', array('order_id' => $order_id)));
        }


    }

    /* ====== prints customer order plus their prescription ====== */
    public function printInternalOrder()
    {
        $order_id = I('get.order_id');
        $order_info = M('order_info')->where(['order_id' => $order_id])->find();

        $latest_cash_id = M('cash')->field('min(cash_id)')->where(['order_id' => $order_id])->select();

        $cash_info = M('cash')->where(['cash_id' => $latest_cash_id[0]['min(cash_id)'], ['order_id' => $order_id]])->select();

        $order_goods_lists = M('order_goods')->where(['order_id' => $order_id])->select();

        $sealstxt_info = unserialize($order_info['sealstxt']);
        $receiptno = unserialize($order_info['receiptno']);
        $distance_info = unserialize($order_info['distance']);

        //Check if insurance to calculate insurance cover
        if ($receiptno['payment_method'] == 'insurance_1' || $receiptno['payment_method'] == "insurance_0") {
            $insurance_detail = unserialize($order_info['corporate']);
            $insurance_detail2 = unserialize($order_info['customer_confirm']);

            $this->assign('insurance_detail', $insurance_detail);
            $this->assign('insurance_detail2', $insurance_detail2);

        }

        $shop_info = M('shops')->where(['shop_id' => $order_info['shop']])->find();

        $user_info = M('customer')->where(['c_id' => $order_info['user_id']])->find();
        $sex = $user_info['c_gender'];

        if ($sex == 0) {
            $Gender = "Secrecy";
        }
        if ($sex == 1) {
            $Gender = "Male";
        }
        if ($sex == 2) {
            $Gender = "Female";
        }

        $age = $user_info['c_age'];
        $user_info['gender'] = $Gender;
        $user_info['age'] = $age;

        if ($sealstxt_info['discountseals'] == "") {
            $discountseals = 0;
        } else {
            $discountseals = $sealstxt_info['discountseals'];
        }

        if ($sealstxt_info['lensprice'] == "") {
            $lensprice = 0;
        } else {
            $lensprice = $sealstxt_info['lensprice'];
        }

        foreach ($order_goods_lists as $goods_info) {
            $total_goods += $goods_info['goods_number'] * $goods_info['goods_price'];
        }

        $total_price = $lensprice + $total_goods - $discountseals;
        $initial_balance = $total_price - $receiptno['payment_price'];

        $this->assign('order_goods_lists', $order_goods_lists);
        $this->assign('order_info', $order_info);
        $this->assign('sealstxt_info', $sealstxt_info);
        $this->assign('receiptno', $receiptno);
        $this->assign('distance_info', $distance_info);
        $this->assign('cash_info', $cash_info[0]);
        $this->assign('shop_info', $shop_info);
        $this->assign('user_info', $user_info);
        $this->assign('discountseals', $discountseals);
        $this->assign('lensprice', $lensprice);
        $this->assign('total_price', $total_price);
        $this->assign('initial_balance', $initial_balance);
        $this->display();

        M('order_info')->where(['order_id' => $order_id])->save([
            'printdate' => time(),
            'order_step'=>8
        ]);
    }

    /* ====== prints customer order plus their prescription END ====== */

    public function printOrder()
    {
        $order_id = I('get.order_id');
        $order_info = M('order_info')->where(['order_id' => $order_id])->find();

        $latest_cash_id = M('cash')->field('min(cash_id)')->where(['order_id' => $order_id])->select();

        $cash_info = M('cash')->where(['cash_id' => $latest_cash_id[0]['min(cash_id)'], ['order_id' => $order_id]])->select();

        $order_goods_lists = M('order_goods')->where(['order_id' => $order_id])->select();

        $sealstxt_info = unserialize($order_info['sealstxt']);
        $receiptno = unserialize($order_info['receiptno']);

        //Check if insurance to calculate insurance cover
        if ($receiptno['payment_method'] == 'insurance_1' || $receiptno['payment_method'] == "insurance_0") {
            $insurance_detail = unserialize($order_info['corporate']);
            $insurance_detail2 = unserialize($order_info['customer_confirm']);

            $this->assign('insurance_detail', $insurance_detail);
            $this->assign('insurance_detail2', $insurance_detail2);

        }

        $shop_info = M('shops')->where(['shop_id' => $order_info['shop']])->find();

        $user_info = M('customer')->where(['c_id' => $order_info['user_id']])->find();
        $sex = $user_info['c_gender'];

        if ($sex == 0) {
            $Gender = "Secrecy";
        }
        if ($sex == 1) {
            $Gender = "Male";
        }
        if ($sex == 2) {
            $Gender = "Female";
        }

        $age = $user_info['c_age'];
        $user_info['gender'] = $Gender;
        $user_info['age'] = $age;

        if ($sealstxt_info['discountseals'] == "") {
            $discountseals = 0;
        } else {
            $discountseals = $sealstxt_info['discountseals'];
        }

        if ($sealstxt_info['lensprice'] == "") {
            $lensprice = 0;
        } else {
            $lensprice = $sealstxt_info['lensprice'];
        }

        foreach ($order_goods_lists as $goods_info) {
            $total_goods += $goods_info['goods_number'] * $goods_info['goods_price'];
        }

        $total_price = $lensprice + $total_goods - $discountseals;
        $initial_balance = $total_price - $receiptno['payment_price'];

        $this->assign('order_goods_lists', $order_goods_lists);
        $this->assign('order_info', $order_info);
        $this->assign('sealstxt_info', $sealstxt_info);
        $this->assign('receiptno', $receiptno);
        $this->assign('cash_info', $cash_info[0]);
        $this->assign('shop_info', $shop_info);
        $this->assign('user_info', $user_info);
        $this->assign('discountseals', $discountseals);
        $this->assign('lensprice', $lensprice);
        $this->assign('total_price', $total_price);
        $this->assign('initial_balance', $initial_balance);
        $this->display();

        M('order_info')->where(['order_id' => $order_id])->save(['printdate' => time()]);
    }

    /* ====== print cash confirm receipt ====== */
    public function printReceipt()
    {

        $cash_id = I('get.cash_id');

        $cash_info = M('cash')->where(['cash_id' => $cash_id])->find();

        $order_info = M('order_info')->where(['order_id' => $cash_info['order_id']])->find();

        $shop_info = M('shops')->where(['shop_id' => $order_info['shop']])->find();

        $user_info = M('customer')->where(['c_id' => $order_info['user_id']])->find();

        $order_goods_lists = M('order_goods')->where(['order_id' => $order_info['order_id']])->select();

        $sealstxt_info = unserialize($order_info['sealstxt']);
        $receiptno = unserialize($order_info['receiptno']);

        $sex = $user_info['c_gender'];

        if ($sex == 0) {
            $Gender = "Secrecy";
        }
        if ($sex == 1) {
            $Gender = "Male";
        }
        if ($sex == 2) {
            $Gender = "Female";
        }

        $age = $user_info['c_age'];
        $user_info['gender'] = $Gender;
        $user_info['age'] = $age;

        if ($sealstxt_info['discountseals'] == "") {
            $discountseals = 0;
        } else {
            $discountseals = $sealstxt_info['discountseals'];
        }

        if ($sealstxt_info['lensprice'] == "") {
            $lensprice = 0;
        } else {
            $lensprice = $sealstxt_info['lensprice'];
        }

        foreach ($order_goods_lists as $goods_info) {
            $total_goods += $goods_info['goods_number'] * $goods_info['goods_price'];
        }

        //Check if insurance to calculate insurance cover
        if ($receiptno['payment_method'] == 'insurance_1' || $receiptno['payment_method'] == "insurance_0") {
            $insurance_detail = unserialize($order_info['corporate']);

            $insurance_detail2 = unserialize($order_info['customer_confirm']);

            $total_price = $lensprice + $total_goods - $discountseals;
            $initial_balance = $order_info['balance'];

            // /echo $initial_balance; die;

            $this->assign('insurance_detail', $insurance_detail);
            $this->assign('insurance_detail2', $insurance_detail2);

        } else { //Cash Order

            $total_price = $lensprice + $total_goods - $discountseals;
            $initial_balance = $total_price - $receiptno['payment_price'];
        }

        $this->assign('cash_receipt', $cash_info['receipt_no'][0]);
        $this->assign('cash_info', $cash_info);
        $this->assign('order_goods_lists', $order_goods_lists);
        $this->assign('order_info', $order_info);
        $this->assign('sealstxt_info', $sealstxt_info);
        $this->assign('receiptno', $receiptno);

        $this->assign('shop_info', $shop_info);
        $this->assign('user_info', $user_info);
        $this->assign('discountseals', $discountseals);
        $this->assign('lensprice', $lensprice);
        $this->assign('total_price', $total_price);
        $this->assign('initial_balance', $initial_balance);
        $this->display();
    }
    /* ====== print cash confirm receipt END ====== */

    /* ====== print Balance step11 ====== */
    public function printBalance()
    {

        $order_id = I('get.order_id');

        $latest_cash_id = M('cash')->field('max(cash_id)')->where(['order_id' => $order_id])->select();

        $order_info = M('order_info')->where(['order_id' => $order_id])->find();

        $cash_info = M('cash')->where(['cash_id' => $latest_cash_id[0]['max(cash_id)'], ['order_id' => $order_id]])->select();

        //print_r($cash_info[0]['receipt_no']);die;

        $order_goods_lists = M('order_goods')->where(['order_id' => $order_id])->select();

        $sealstxt_info = unserialize($order_info['sealstxt']);
        $receiptno = unserialize($order_info['receiptno']);

        $shop_info = M('shops')->where(['shop_id' => $order_info['shop']])->find();

        $user_info = M('customer')->where(['c_id' => $order_info['user_id']])->find();
        $sex = $user_info['c_gender'];

        if ($sex == 0) {
            $Gender = "Secrecy";
        }
        if ($sex == 1) {
            $Gender = "Male";
        }
        if ($sex == 2) {
            $Gender = "Female";
        }

        $age = $user_info['c_age'];
        $user_info['gender'] = $Gender;
        $user_info['age'] = $age;

        if ($sealstxt_info['discountseals'] == "") {
            $discountseals = 0;
        } else {
            $discountseals = $sealstxt_info['discountseals'];
        }

        if ($sealstxt_info['lensprice'] == "") {
            $lensprice = 0;
        } else {
            $lensprice = $sealstxt_info['lensprice'];
        }

        foreach ($order_goods_lists as $goods_info) {
            $total_goods += $goods_info['goods_number'] * $goods_info['goods_price'];
        }

        //Check if insurance to calculate insurance cover
        if ($receiptno['payment_method'] == 'insurance_1' || $receiptno['payment_method'] == "insurance_0") {
            $insurance_detail = unserialize($order_info['corporate']);

            $insurance_detail2 = unserialize($order_info['customer_confirm']);

            $total_price = $lensprice + $total_goods - $discountseals;
            $initial_balance = $order_info['balance'];

            // /echo $initial_balance; die;

            $this->assign('insurance_detail', $insurance_detail);
            $this->assign('insurance_detail2', $insurance_detail2);

        } else { //Cash Order

            $total_price = $lensprice + $total_goods - $discountseals;
            $initial_balance = $order_info['balance'];
        }

        $this->assign('cash_receipt', $cash_info[0]['receipt_no'][0]);
        $this->assign('cash_info', $cash_info[0]);
        $this->assign('order_goods_lists', $order_goods_lists);
        $this->assign('order_info', $order_info);
        $this->assign('sealstxt_info', $sealstxt_info);
        $this->assign('receiptno', $receiptno);

        $this->assign('shop_info', $shop_info);
        $this->assign('user_info', $user_info);
        $this->assign('discountseals', $discountseals);
        $this->assign('lensprice', $lensprice);
        $this->assign('total_price', $total_price);
        $this->assign('initial_balance', $initial_balance);
        $this->display();

        //M('order_info')->where(['order_id'=>$order_id])->save(['printdate'=>time()]);
    }
    /* ====== print Balance step11 END====== */

    /* ====== Top Up Section ====== */
    public function printTopUpReceipt()
    {
        $order_id = I('get.order_id');

        $latest_cash_id = M('cash')->field('max(cash_id)')->where(['order_id' => $order_id])->select();

        $order_info = M('order_info')->where(['order_id' => $order_id])->find();

        $cash_info = M('cash')->where(['cash_id' => $latest_cash_id[0]['max(cash_id)'], ['order_id' => $order_id]])->select();

        //print_r($cash_info[0]['receipt_no']);die;

        $order_goods_lists = M('order_goods')->where(['order_id' => $order_id])->select();

        $sealstxt_info = unserialize($order_info['sealstxt']);
        $receiptno = unserialize($order_info['receiptno']);

        $shop_info = M('shops')->where(['shop_id' => $order_info['shop']])->find();

        $user_info = M('customer')->where(['c_id' => $order_info['user_id']])->find();
        $sex = $user_info['c_gender'];

        if ($sex == 0) {
            $Gender = "Secrecy";
        }
        if ($sex == 1) {
            $Gender = "Male";
        }
        if ($sex == 2) {
            $Gender = "Female";
        }

        $age = $user_info['c_age'];
        $user_info['gender'] = $Gender;
        $user_info['age'] = $age;

        if ($sealstxt_info['discountseals'] == "") {
            $discountseals = 0;
        } else {
            $discountseals = $sealstxt_info['discountseals'];
        }

        if ($sealstxt_info['lensprice'] == "") {
            $lensprice = 0;
        } else {
            $lensprice = $sealstxt_info['lensprice'];
        }

        foreach ($order_goods_lists as $goods_info) {
            $total_goods += $goods_info['goods_number'] * $goods_info['goods_price'];
        }

        //Check if insurance to calculate insurance cover
        // if($receiptno['payment_method'] == 'insurance_1' || $receiptno['payment_method'] == "insurance_0")
        // {
        $insurance_detail = unserialize($order_info['corporate']);

        $insurance_detail2 = unserialize($order_info['customer_confirm']);

        $total_price = $lensprice + $total_goods - $discountseals;
        $initial_balance = $order_info['balance'];

        // /echo $initial_balance; die;

        $this->assign('insurance_detail', $insurance_detail);
        $this->assign('insurance_detail2', $insurance_detail2);

        $this->assign('cash_receipt', $cash_info[0]['receipt_no'][0]);
        $this->assign('cash_info', $cash_info[0]);
        $this->assign('order_goods_lists', $order_goods_lists);
        $this->assign('order_info', $order_info);
        $this->assign('sealstxt_info', $sealstxt_info);
        $this->assign('receiptno', $receiptno);

        $this->assign('shop_info', $shop_info);
        $this->assign('user_info', $user_info);
        $this->assign('discountseals', $discountseals);
        $this->assign('lensprice', $lensprice);
        $this->assign('total_price', $total_price);
        $this->assign('initial_balance', $initial_balance);
        $this->display();
    }

    public function viewprintTopUp()
    {

        $order_info = M('order_info')->where('customer_confirm IS NOT NULL AND topup=1')->select();

        $this->assign('order_info_lists', $order_info);

        $this->display();
    }

    public function detailsTopUp()
    {

        $order_id = I('get.order_id');
        $order_info = M('order_info')->where(['order_id' => $order_id])->find();

        $balance = $order_info['balance'];
        $this->assign('balance', $balance);
        $this->assign('order_info', $order_info);

        $this->display();

    }

    public function clearTopUp()
    {

        unset($update_data);
        unset($data);

        $order_id = I('post.order_id');

        $recevied_amount = I('post.recevied_amount');
        $remark = I('post.remark');

        $order_info = M('order_info')->where(['order_id' => $order_id])->find();

        $customer_confirm = unserialize($order_info['customer_confirm']);

        $customer_confirm['balance_remaining'] = $customer_confirm['balance_remaining'] - $recevied_amount;
        $customer_confirm['recevied_amount'] = $recevied_amount;

        $update_data['balance'] = $order_info['balance'] - $recevied_amount;


        // if($customer_confirm['balance_remaining']==0 && $update_data['balance']==0){

        //     $receiptno = unserialize($order_info['receiptno']);
        //     $corporate = unserialize($order_info['corporate']);

        //     // if($receiptno['payment_method'] == 'insurance_1' && $corporate['corporate_status_set']=='less approval'){
        //     //     $update_data['order_step'] = 6; //Go to this step to check if the customer swiped their card
        //     // }else{
        //     //     $update_data['order_step'] = 7; // Can print the order
        //     // }


        // }
        $data['add_time'] = time();

        $update_data['customer_confirm'] = serialize($customer_confirm);

        $cash_count = M('cash')->field('count(cash_id) as cash_count')->select();
        $cash_count = $cash_count[0]['cash_count'] + 1;

        $data_cash['order_id'] = $order_info['order_id'];
        $data_cash['order_sn'] = $order_info['order_sn'];
        $data_cash['pay_amount'] = I('post.recevied_amount');
        $data_cash['payment_method'] = I('post.top_up_pay_method');
        $data_cash['receipt_no'] = 'B' . $cash_count;
        $data_cash['payment_remark'] = I('post.remark');
        $data_cash['reverse_status'] = 0;
        $data_cash['cancel_status'] = 0;
        $data_cash['member_id'] = session('member_id');
        $data_cash['add_time'] = time();

        M('cash')->add($data_cash);

        M('order_info')->where(['order_id' => $order_id])->save($update_data); //update customer_confirm

        if (M('order_info')->where(['order_id' => $order_id])->save($update_data) !== FALSE) {
            $this->success('save success', U('Index/viewprintTopUp'));
        } else {
            $this->error('save fail', U('Index/viewprintTopUp'));
        }

    }

    public function clearClientFromList()
    { // Remove order No from Top Up list

        unset($update_data);
        unset($data);

        $order_id = I('get.order_id');

        $update_data['topup'] = 0;

        $order_info = M('order_info')->where(['order_id' => $order_id])->find();

        $customer_confirm = unserialize($order_info['customer_confirm']);

        if ($customer_confirm['balance_remaining'] == 0 && $order_info['balance'] == 0) {

            $receiptno = unserialize($order_info['receiptno']);
            $corporate = unserialize($order_info['corporate']);

            if ($receiptno['payment_method'] == 'insurance_1' && $corporate['corporate_status_set'] == 'less approval') {
                $update_data['order_step'] = 6; //Go to this step to check if the customer swiped their card
            } else {
                $update_data['order_step'] = 7; // Can print the order
            }

        } elseif ($customer_confirm['balance_remaining'] < 0 && $order_info['balance'] < 0) {
            $receiptno = unserialize($order_info['receiptno']);
            $corporate = unserialize($order_info['corporate']);

            if ($receiptno['payment_method'] == 'insurance_1' && $corporate['corporate_status_set'] == 'less approval') {
                $update_data['order_step'] = 6; //Go to this step to check if the customer swiped their card
            } else {
                $update_data['order_step'] = 7; // Can print the order
            }
            $update_data['balance'] = 0;
            $update_data['customer_confirm'] = $customer_confirm['balance_remaining'] = 0;
        }

        M('order_info')->where(['order_id' => $order_id])->save($update_data); //update customer_confirm

        if (M('order_info')->where(['order_id' => $order_id])->save($update_data) !== FALSE) {
            $this->success('Save success', U('Index/viewprintTopUp'));
        } else {
            $this->error('Save fail', U('Index/viewprintTopUp'));
        }

    }
    /* ====== Top Up Section END ====== */

    /* ====== Cancel Order ====== */
    public function cancelOrder()
    {
        $order_id = I('post.order_id');
        $order_info = M('order_info')->where(['order_id' => $order_id])->find();

        $cancel_remark['account_with_order'] = $order_info['member_id'];
        $cancel_remark['shop_with_order'] = $order_info['shop'];

        $cancel_remark['cancelled_by'] = session('member_name');
        $cancel_remark['shop_that_cancelled'] = session('member_shop');
        $cancel_remark['cancel_time'] = time();
        $cancel_remark['cancel_remark'] = I('post.cancel_remark');
        $cancel_remark['old_order_step'] = $order_info['order_step'];

        // echo $order_id."<br />";
        // echo $order_step."<br />";
        // echo "<pre>";
        // print_r($cancel_remark);
        // echo "</pre>";
        // die;

        $update_data['cancel_remark'] = serialize($cancel_remark);
        $update_data['order_step'] = -1;
        $update_data2['cancel_status'] = 1;

        M('order_info')->where(['order_id' => $order_id])->save($update_data); //update order_info
        M('cash')->where(['order_id' => $order_id])->save($update_data2); //update cash

        if (M('order_info')->where(['order_id' => $order_id])->save($update_data) !== FALSE) {
            $this->success('Order Cancelled, Success', U('Index/index'));
        } else {
            $this->error('Order NOT cancelled, Fail', U('Index/index'));
        }

    }
    /* ====== Cancel Order END====== */

    /* ====== Reverse Cancelled Order ===== */

    public function reverseCancelledOrder()
    {
        $order_id = I('get.order_id');
        $order_info = M('order_info')->where(['order_id' => $order_id])->find();

        $update_data['cancel_remark'] = '';
        $update_data2['cancel_status'] = 0;

        $cancel_remark = unserialize($order_info['cancel_remark']);

        $update_data['order_step'] = $cancel_remark['old_order_step'];

        M('order_info')->where(['order_id' => $order_id])->save($update_data); //update order_info
        M('cash')->where(['order_id' => $order_id])->save($update_data2); //update cash

        if (M('order_info')->where(['order_id' => $order_id])->save($update_data) !== FALSE) {
            $this->success('Order Status Reversed, Success', U('Index/index'));
        } else {
            $this->error('Order NOT Reversed, Fail', U('Index/viewCancelledOrders'));
        }

    }

    /* ====== Reverse Cancelled Order END ===== */

    /* ====== view cancelled Orders ======*/

    public function viewCancelledOrders()
    {

        if (session('member_role') == 2) {
            $order_info = M('order_info')->where('order_step = -1 AND shop=' . session('member_shop') . '')->select();
        } else {
            $order_info = M('order_info')->where('order_step = -1 AND cancel_remark IS NOT NULL')->select();
        }

        $this->assign('cancelled_orders', $order_info);

        $this->display();
    }

    public function changePassword()
    {
        if (IS_POST) {
            $oldpassword = I('post.oldpassword');
            $newpassword = I('post.newpassword');
            $newpassword2 = I('post.newpassword2');

            $member_id = session('member_id');
            $member_model = M('member');

            $member_info = $member_model->where(['member_id' => $member_id])->find();

            $password = md5($oldpassword);
            if ($password == $member_info['member_pwd']) {
                $member_model->where(['member_id' => $member_id])->save(['member_pwd' => md5($newpassword)]);
                $this->success('change password success', U('Index/changePassword'));
            } else {
                $this->error('old password incorrect!', U('Index/changePassword'));
            }

        } else {
            $this->display();
        }
    }

    public function customer()
    {
        $customer_model = M('customer');

        $condition = array();
        if (I('get.name')) {
            $condition['c_name'] = I('get.name');
        }
        if (I('get.c_mobile')) {
            $condition['c_mobile'] = I('get.mobile');
        }

        $condition['status'] = 1;

        $count = $customer_model->where($condition)->count();

        $Page = new \Think\Page($count, 25);
        $Page->setConfig('header', '<li class="rows"><b>[%NOW_PAGE%</b>/<b>%TOTAL_PAGE%]</b> Total <b>%TOTAL_ROW%</b> records </li>');
        $Page->setConfig('prev', 'prev page');
        $Page->setConfig('next', 'next page');
        $Page->setConfig('last', 'last page');
        $Page->setConfig('first', 'first page');
        $Page->setConfig('theme', '%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE%  %HEADER%');
        $show = $Page->show();

        $customer_lists = $customer_model->where($condition)->order('c_id DESC')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach ($customer_lists as &$customer){
            $customer['type']=$this->customer_type[$customer['c_type']];
        }
        $this->assign('customer_lists', $customer_lists);
        $this->assign('page', $show);
        $this->display();
    }

    public function deleteCustomer(){
        $goods_id = I('get.customer_id');
        //$data['customer_id'] = 0;

        if(M('customer')->where(['c_id'=>$goods_id])->save(['state'=>0]))
        {
            $this->success('Delete customer success',U('Index/customer'));
        }else{
            $this->error('Delete customer fail!',U('Index/customer'));
        }
    }

    public function addCustomer()
    {
        //$customer_model = M('customer');
        $this->display();
    }

    public function saveCustomer()
    {
        $data = [];
        $data['c_name'] = I('post.name');
        $data['c_mobile'] = I('post.mobile');
        $data['c_email'] = I('post.email');
        $data['c_address'] = I('post.address');
        $data['c_type'] = I('post.type');
        $data['c_amount'] = I('post.amount');
        $customer_model = M('customer');

        if(I('post.customer_id')){

            $result = $customer_model->where(['c_id'=>I('post.customer_id')])->save($data);
            if ($result) {
                $this->success('update member success', U('index/customer'));
            } else {
                $this->error('update member fail!');
            }

        }else{
            $result = $customer_model->add($data);
            if ($result) {
                $this->success('add member success', U('index/customer'));
            } else {
                $this->error('add member fail!');
            }
        }

    }


    public function editCustomer(){
        $member_id = I('get.customer_id');
        $customer_model = M('customer');

        $customer = $customer_model->where(['c_id'=>$member_id])->find();
        $this->assign('customer',$customer);
        $this->display('addCustomer');
    }



    public function exportExcel()
    {
        import('ORG.Util.Excel');
        $excel_obj = new \Org\Util\Excel();
        $excel_data = array();
        //设置样式
        $excel_obj->setStyle(array('id' => 's_title', 'Font' => array('FontName' => '宋体', 'Size' => '12', 'Bold' => '1')));
        //header
        $excel_data[0][] = array('styleid' => 's_title', 'data' => 'id');
        $excel_data[0][] = array('styleid' => 's_title', 'data' => 'name');
        $excel_data[0][] = array('styleid' => 's_title', 'data' => 'address');
        $excel_data[0][] = array('styleid' => 's_title', 'data' => 'mobile');
        $excel_data[0][] = array('styleid' => 's_title', 'data' => 'email');

        $customer_model = M('customer');

        $condition = array();

        $data = $customer_model->where($condition)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach ((array)$data as $k => $v) {
            $tmp = array();
            $tmp[] = array('data' => $v['c_id']);
            $tmp[] = array('data' => $v['c_name']);
            $tmp[] = array('data' => $v['c_address']);
            $tmp[] = array('data' => $v['c_mobile']);
            $tmp[] = array('data' => $v['c_email']);
            $excel_data[] = $tmp;
        }
        $excel_data = $excel_obj->charset($excel_data, 'utf8');
        $excel_obj->addArray($excel_data);
        $excel_obj->addWorksheet($excel_obj->charset('customer', 'utf8'));
        $excel_obj->generateXML($excel_obj->charset('customer', 'utf8') . '-' . date('Y-m-d-H', time()));
    }

    public function customerCare()
    {
        $order_info_model = M('order_info');

        $e_time = time() - 60 * 60 * 24 * 30;
        $condition['add_time'] = ['lt', $e_time];
        $condition['customer_care'] = 0;
        $condition['customer_care'] = 0;
        $condition['order_closed'] = 1;
        if (I('get.key_words')) {
            $key_words = I('post.key_words');
            $condition['order_sn'] = ['LIKE', "%$key_words%"];
            // $condition['consignee'] = ['LIKE',"%$key_words%"];
            // $condition['tel'] = ['LIKE',"%$key_words%"];
            // $condition['_logic'] = 'or';
        }

        $member_id = session('member_id');
        $member_info = M('member')->where(['member_id' => $member_id])->find();

        // if(($member_info['member_role'] == 2))
        // {
        //     $condition['shop'] = session('member_shop');
        // }

        $count = $order_info_model->where($condition)->count();

        $Page = new \Think\Page($count, 25);
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

    public function customerCareProcess()
    {
        $order_id = I('post.order_id');
        $customer_care_remark = I('post.customer_care_remark');

        $data_update['customer_care'] = 1;
        $data_update['customer_care_remark'] = $customer_care_remark;

        if (M('order_info')->where(['order_id' => $order_id])->find()) {
            if (M('order_info')->where(['order_id' => $order_id])->save($data_update) !== FALSE) {
                $this->success('save success', U('Index/customerCare'));
            } else {
                $this->error('save failed', U('Index/customerCare'));
            }
        } else {
            $this->error('save failed', U('Index/customerCare'));
        }
    }

    public function cashConfirm()
    {
        $cash_model = M('cash');

        if (I('get.reverse_status') < 3) {
            $condition['reverse_status'] = I('get.reverse_status');
            $condition['cancel_status'] = 0;
        } elseif (I('get.reverse_status') == 3) {

            $condition['cancel_status'] = 1;
        }

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
        $condition['pay_amount'] = ['gt',0];

        $count = $cash_model->where($condition)->count();

        $Page = new \Think\Page($count, 14);
        $Page->setConfig('header', '<li class="rows"><b>[%NOW_PAGE%</b>/<b>%TOTAL_PAGE%]</b> Total <b>%TOTAL_ROW%</b> records </li>');
        $Page->setConfig('prev', 'prev page');
        $Page->setConfig('next', 'next page');
        $Page->setConfig('last', 'last page');
        $Page->setConfig('first', 'first page');
        $Page->setConfig('theme', '%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE%  %HEADER%');
        $show = $Page->show();

        $cash_lists = $cash_model->where($condition)->order('cash_id DESC')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('cash_lists', $cash_lists);
        $shop_lists = M('shops')->select();
        $this->assign('shop_lists', $shop_lists);
        $this->assign('page', $show);
        $this->display();
    }

    public function cashProcess()
    {
        $cash_id = I('post.cash_id');
        $cash_remark = I('post.cash_remark');
        $reverse_status = I('post.reverse_status');

        $data_update['reverse_status'] = $reverse_status;
        $data_update['cash_remark'] = $cash_remark;

        if (M('cash')->where(['cash_id' => $cash_id])->find()) {
            if (M('cash')->where(['cash_id' => $cash_id])->save($data_update) !== FALSE) {
                $this->success('save success', U('Index/cashConfirm'));
            } else {
                $this->error('save failed', U('Index/cashConfirm'));
            }
        } else {
            $this->error('save failed', U('Index/cashConfirm'));
        }
    }


}