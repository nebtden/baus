<?php

namespace Home\Controller;

use Think\Controller;


/*
For receiptno, D means Deposit when making an order

*/

class OrderController extends Controller
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

    public function index()
    {
        $parent_cat_lists = M('category')->where(['parent_id' => 0])->order('sort_order asc')->select();
        $this->assign('parent_cat_lists', $parent_cat_lists);

        $brand_lists = M('brand')->select();
        $this->assign('brand_lists', $brand_lists);

        $brand_lists = M('goods')->field('goods_model_no')->select();
        $this->assign('goods_model_lists', $brand_lists);

        $this->display();
    }

    public function insurance(){
        $order_id = I('get.order_id');
        $order_info = M('order_info')->where(['order_id' => $order_id])->find();
        $receiptno = unserialize($order_info['receiptno']);


        $this->assign('order_info', $order_info);
        $this->assign('receiptno', $receiptno);

        $this->display();
    }



    public function editcorporate(){
        /* ====== Step 4 Corporate Manager Section ====== */

        $order_id = I('post.order_id');
        $order_info = M('order_info')->where(['order_id' => $order_id])->find();



        $data['corporate_status_set'] = I('post.corporate_status_set');
        $data['corporate_amount'] = I('post.corporate_amount');

        $data['add_time'] = time();

        $update_data['corporate'] = serialize($data);

        $receiptno = unserialize($order_info['receiptno']);

        if ($data['corporate_status_set'] == 'proceed') {
            if ($receiptno['payment_method'] == 'insurance_0') { //check if smart card selected in new order

                $update_data['order_step'] = 7; // Proceed with NO smart card to step7

            } else if ($receiptno['payment_method'] == 'insurance_1') {

                $update_data['order_step'] = 5;  // Proceed with smart card to step5
            }else {

                $update_data['order_step'] = 5;  // Proceed with smart card to step5
            }


        } elseif ($data['corporate_status_set'] == 'less approval') { // Less Approve option, so step5

            $update_data['order_step'] = 5;
        } else {
            $update_data['order_step'] = -1; //Cancelled Order
        }
        //
        $update_data['is_insurance_checked'] = 1;

        //M('order_info')->where(['order_id' => $order_id])->save($update_data);

        if (M('order_info')->where(['order_id' => $order_id])->save($update_data) !== FALSE) {
            //$this->redirect('/Index/index',array('order_step' => 4));
            $this->success('save success', U('Index/index', array('order_step' => 4)));
        } else {
            $this->error('save fail', U('Order/insurance', array('order_id' => $order_id)));
        }

        /* ====== Step 4, Corporate Manager Section END ====== */

    }

    public function confirm(){
        $order_id = I('get.order_id');
        $order_info = M('order_info')->where(['order_id' => $order_id])->find();
        $receiptno = unserialize($order_info['receiptno']);
        $customer_confirm = unserialize($order_info['customer_confirm']);

        $this->assign('order_info', $order_info);
        $this->assign('receiptno', $receiptno);
        $this->assign('customer_confirm', $customer_confirm);

        $this->display();
    }


    public function workshop(){
        $order_id = I('get.order_id');
        $order_info = M('order_info')->where(['order_id' => $order_id])->find();
        $receiptno = unserialize($order_info['receiptno']);
        $customer_confirm = unserialize($order_info['customer_confirm']);

        $this->assign('order_info', $order_info);
        $this->assign('receiptno', $receiptno);
        $this->assign('customer_confirm', $customer_confirm);

        $this->display();
    }

    public function workshopsave(){
        $order_id = I('post.order_id');

        $data['warehouse_state'] = I('post.warehouse_state');
        $data['stock_source'] = I('post.stock_source');
        $data['add_time'] = time();

        $update_data['order_step'] = 9;
        $update_data['warehouse_state'] = serialize($data);

        if (M('order_info')->where(['order_id' => $order_id])->save($update_data) !== FALSE) {
            $this->success('save success', U('Index/Index', array('order_step' => 8)));
        } else {
            $this->error('save fail', U('Order/workshop', array('order_id' => $order_id)));
        }

    }


    public function quality(){
        $order_id = I('get.order_id');
        $order_info = M('order_info')->where(['order_id' => $order_id])->find();
        $receiptno = unserialize($order_info['receiptno']);
        $customer_confirm = unserialize($order_info['customer_confirm']);

        $this->assign('order_info', $order_info);
        $this->assign('receiptno', $receiptno);
        $this->assign('customer_confirm', $customer_confirm);

        $this->display();
    }

    public function qualitysave(){
        $order_id = I('post.order_id');
        $data['products_shipped'] = I('post.products_shipped');
        $data['add_time'] = time();

        $update_data['order_step'] = 10;
        $update_data['products_shipped'] = serialize($data);


        if (M('order_info')->where(['order_id' => $order_id])->save($update_data) !== FALSE) {
            $this->success('save success', U('Index/Index', array('order_step' => $order_id)));
        } else {
            $this->error('save fail', U('Index/orderShow', array('order_id' => $order_id)));
        }

        if (M('order_info')->where(['order_id' => $order_id])->save($update_data) !== FALSE) {
            $this->success('save success', U('Index/Index', array('order_step' => 9)));
        } else {
            $this->error('save fail', U('Order/quality', array('order_id' => $order_id)));
        }

    }


    public function confirmsave(){
        $order_id = I('post.order_id');
        $order_info = M('order_info')->where(['order_id' => $order_id])->find();

        if (I('post.less_approve_action') == 'cancel') {
            $update_data['order_step'] = -1;

        } else {

            //Change goods for now is just to have a balance of 0
            if (I('post.less_approve_action') == 'top_up') {
                $data['balance_remaining'] = I('post.balance_remaining');
                $update_data['balance'] = I('post.balance_remaining');
                $update_data['topup'] = 1;

            } elseif (I('post.less_approve_action') == 'change_goods') {
                $data['balance_remaining'] = 0;
                $update_data['balance'] = 0;
                $update_data['topup'] = 0;
            } elseif (I('post.less_approve_action') == 'discount'){
                $data['balance_remaining'] = 0;
                $update_data['balance'] = 0;
                $update_data['topup'] = 1;
            }

            //if smart card or not
            $receiptno = unserialize($order_info['receiptno']);

            if ($receiptno['payment_method'] == 'insurance_0') { // NO SMART CARD

                $update_data['order_step'] = I('post.balance_remaining') == 0 ? 7 : 5;

            } elseif ($receiptno['payment_method'] == 'insurance_1') { // WITH SMART CARD

                $update_data['order_step'] = I('post.balance_remaining') == 0 ? 6 : 5;
            }


            $data['less_approve_action'] = I('post.less_approve_action');


            $data['remark'] = I('post.remark');


            $data['less_approve_pay_method'] = 'combine';


            //收取买家费用
            $payed_money = insertpaidmoney($order_info,'');
            $data['recevied_amount'] = $payed_money;
            //更新余额
            M('order_info')->where(['order_step'=>7,'order_id'=>$order_id])->save([
                'balance' => 0
            ]);

//                $data_cash['order_id'] = $order_info['order_id'];
//                $data_cash['order_sn'] = $order_info['order_sn'];
//                $data_cash['pay_amount'] = I('post.recevied_amount');
//                $data_cash['payment_method'] = I('post.less_approve_pay_method');
//                $data_cash['receipt_no'] = 'B' . $cash_count;
//                $data_cash['payment_remark'] = I('post.remark');
//                $data_cash['reverse_status'] = 0;
//                $data_cash['cancel_status'] = 0;
//                $data_cash['member_id'] = session('member_id');
//                $data_cash['add_time'] = time();
//
//                M('cash')->add($data_cash);

            $data['add_time'] = time();

            $update_data['customer_confirm'] = serialize($data);


            //M('order_info')->where(['order_id' => $order_id])->save($update_data); //update customer_confirm

        }

        if (M('order_info')->where(['order_id' => $order_id])->save($update_data) !== FALSE) {
            $this->success('save success', U('Index/Index', array('order_step' => 6)));
        } else {
            $this->error('save fail', U('Order/confirm', array('order_id' => $order_id)));
        }

    }


    public function cardconfirm(){
        $order_id = I('get.order_id');
        $order_info = M('order_info')->where(['order_id' => $order_id])->find();
        $receiptno = unserialize($order_info['receiptno']);

        $this->assign('order_info', $order_info);
        $this->assign('receiptno', $receiptno);

        $this->display();
    }

    public function cardconfirmsave(){
        $order_id = I('post.order_id');
        $order_info = M('order_info')->where(['order_id' => $order_id])->find();

        $data['customer_card_confirm'] = I('post.customer_card_confirm');
        $data['add_time'] = time();

        $corporate = unserialize($order_info['corporate']);

        /* Might be no need for this step ----------------------------------------------------*/
        if ($corporate['corporate_status_set'] == 'proceed') {
            $update_data['order_step'] = 7;
            $update_data['balance'] = 0;
        } else {
            $update_data['order_step'] = 6; //Fail Safe solution
        }
        /* Might be no need for this step ----------------------------------------------------*/

        $update_data['customer_card'] = serialize($data);

        // M('order_info')->where(['order_id' => $order_id])->save($update_data);

        if (M('order_info')->where(['order_id' => $order_id])->save($update_data) !== FALSE) {
            $this->success('save success', U('Index/Index', array('order_step' => 5)));
        } else {
            $this->error('save fail', U('Order/cardconfirm', array('order_id' => $order_id)));
        }

    }




    public function makeOrder()
    {

        //customer info
        $c_id = I('post.c_id');
        $data['user_id'] = $c_id;


        $c_name = I('post.c_name');
        $c_email = I('post.c_email');
        $c_mobile = I('post.c_mobile');
        $c_gender = I('post.c_gender');
        $c_age = I('post.c_age');
        //var_dump($c_id);


        $address = I('post.address');
        $customer['c_mobile'] = $c_mobile;
        $customer['c_name'] = $c_name;
        $customer['c_email'] = $c_email;
        $customer['c_address'] = $address;
        $customer['c_gender'] = $c_gender;
        $customer['c_age'] = $c_age;

        if ($c_id > 0) {
            $data['user_id'] = $c_id;
            $customer_model = M('customer');


            //更新信息
            $updatecustomer = $customer;
            unset($updatecustomer['c_mobile']);
            M('customer')->where(['c_id'=>$c_id])->save($updatecustomer);

            $customer = $customer_model->where(['c_id'=>$c_id])->find();
        } else {

            $res_id = M('customer')->add($customer);
            $data['user_id'] = $res_id;
        }


        $goods_id = I('post.goods_id');
        $goods_info = M('goods')->find($goods_id);

        $data['goods_amount'] = $goods_info['goods_price'];
        $data['order_amount'] = $goods_info['goods_price'];

        $data['order_sn'] = date('YmdHis', time()) . rand(0, 9);
        $data['goods_id'] = $goods_id;
        $data['consignee'] = I('post.c_name');
        $data['email'] = $customer['c_email'];
        $data['mobile'] = I('post.c_mobile');;
        $data['address'] = $customer['c_address'];
        $data['is_insurance_topup'] = 0;

        /* -------------------- */
        $data['pay_id'] = 0;
        $data['shipping_id'] = 0;
        $data['postscript'] = "test";
        /* -------------------- */
        $data['add_time'] = time();


        if (I('post.lensprice') == '' && I('post.lensname') == '') {

            $data_seals['lensname'] = '';
            $data_seals['lensprice'] = 0;

        } else {
            $data_seals['lensname'] = I('post.lensname');
            $data_seals['lensprice'] = I('post.lensprice');
        }
        $data['customer_care'] = '';

        $data_seals['discountseals'] = I('post.discountseals');
        $data_seals['special_marks'] = I('post.special_marks');

        $data_seals['add_time'] = time();

        $data['sealstxt'] = serialize($data_seals);

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

        $data['distance'] = serialize($data_distance);

        $orders_count = M('order_info')->field('count(order_id) as order_count')->select();
        $orders_count = $orders_count[0]['order_count'] + 1;

        $data_receiptno['payment_price'] = I('post.payment_price');
        $data_receiptno['nopayment_price'] = I('post.nopayment_price');
        $data_receiptno['receiptno'] = 'D' . $orders_count;
        $data_receiptno['cashier'] = I('post.cashier');
        $data_receiptno['sales_person'] = I('post.sales_person');
        $data_receiptno['optometrist'] = I('post.optometrist');
        if(I('post.payment_method')){
            $data_receiptno['payment_method'] = I('post.payment_method');
        }else{
            $data_receiptno['payment_method'] = 'combine';
        }

        $data_receiptno['insurancetext'] = I('post.insurancetext');
        $data_receiptno['add_time'] = time();

        $total_order_amount = ($goods_info['goods_price'] + $data_seals['lensprice']) - ($data_seals['discountseals']);

        $data_receiptno['total_order_amount'] = $total_order_amount;

        if ($data_receiptno['payment_method'] == 'insurance_1' || $data_receiptno['payment_method'] == "insurance_0") {
            $data['order_step'] = 4;// Insurance Order

        } else {
            $data['order_step'] = 7;// Cash Order
            $data['balance'] = $total_order_amount - $data_receiptno['payment_price'];
        }

        $data['receiptno'] = serialize($data_receiptno);

        //get shop
        $member_id = session('member_id');
        $member_info = M('member')->where(['member_id' => $member_id])->find();

        // if(($member_info['member_role'] == 2))
        // {
        $data['shop'] = $member_info['member_shop'];
        $data['member_id'] = $member_id;
        //}

        if ($order_id = M('order_info')->add($data)) {
            $data_order_goods['order_id'] = $order_id;
            $data_order_goods['goods_id'] = $goods_id;
            $data_order_goods['goods_model_no'] = $goods_info['goods_model_no'];
            $data_order_goods['goods_brand'] = $goods_info['goods_brand'];
            $data_order_goods['goods_cat'] = $goods_info['goods_cat'];
            $data_order_goods['goods_number'] = 1; // THIS MAY CHANGE
            $data_order_goods['goods_price'] = $goods_info['goods_price'];

            $data_order_goods['goods_attr'] = $goods_info['goods_attr'];
            $data_order_goods['goods_remark'] = I('post.goods_remark');
            $data_order_goods['goods_name'] = $goods_info['goods_name'];

            M('order_goods')->add($data_order_goods);

            $order_info = M('order_info')->find($order_id);

            //收取买家费用
            $payed_money = insertpaidmoney($order_info,$total_order_amount,$orders_count);

            //更新余额
            M('order_info')->where(['order_step'=>7,'order_id'=>$order_id])->save([
                'balance' => $total_order_amount-$payed_money
            ]);



            $this->success('add order success', U('Index/index'));
            //$this->success('add order success',U('Index/index',array('order_step' => 3)));
        } else {
            $this->error('add order fail!', U('Order/index'));
        }

    }

    public function getCustomerInfoAjax()
    {
        $c_mobile = I('get.c_mobile');
        $customer_info = M('customer')->where(['c_mobile' => $c_mobile])->find();
        if (empty($customer_info)) {
            echo json_encode(['info' => 'New Customer']);
        } else {
            echo json_encode($customer_info);
        }
    }

    public function getGoodsListAjax()
    {
        $cat_id = I('get.cat_id');
        $brand_id = I('get.brand');
        $model_no = I('get.model');

        //echo $cat_id." ".$brand_id." ".$model_no;die;

        $goods_lists = M('goods g')->query("select c.cat_name,b.brand_name,g.goods_id,g.goods_cat,g.goods_brand,g.goods_model_no,g.goods_attr,g.goods_price from oms_category c, oms_brand b, oms_goods g
                        where c.cat_id=g.goods_cat AND
                        b.brand_id = g.goods_brand AND
                        g.goods_model_no='" . $model_no . "' AND
                        c.cat_id = '" . $cat_id . "' AND
                        b.brand_id = '" . $brand_id . "'");

        // echo "<pre>";
        // print_r($goods_lists);
        // echo "</pre>";
        // die;

        foreach ($goods_lists as $goods_info) {

            // $str = getAttrNameById($goods_info['goods_attr']);
            $str .= "<tr>
                        <td>" . getCategoryNameById($goods_info['goods_cat']) . "</td>
                        <td>" . getBrandNameById($goods_info['goods_brand']) . "</td>
                        <td>" . $goods_info['goods_model_no'] . "</td>
                        <td>" . $goods_info['goods_price'] . "</td>";

            //if(getAttrNameById($goods_info['goods_attr'])==1 || getAttrNameById($goods_info['goods_attr'])==2){

            $str .= "<td>" . $goods_info['goods_attr'] . "</td>";
            // }else{
            //     $str.=" <td>".getAttrNameById($goods_info['goods_attr'])."</td>";
            // }
            /* This may change to allow multiple goods */
            $str .= "     <td><input type='radio' name='goods_id' value='" . $goods_info['goods_id'] . "'></td>
                    </tr>";
        }

        echo $str;
    }

    //public function getGoodsList

}