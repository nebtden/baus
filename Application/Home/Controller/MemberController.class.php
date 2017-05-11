<?php
namespace Home\Controller;
use Think\Controller;
class MemberController extends Controller {

    public function __construct()
    {
        parent::__construct();

        if(empty(session('member_id')))
        {
            $this->redirect('Login/index');
        }else{
            $member_group_id = session('member_group_id');
            $member_group_info = M('member_group')->where(['member_group_id'=>$member_group_id])->find();
            $this->assign('member_group_info',$member_group_info);
        }
    }    

    public function index()
    {
        $member_model = M('member');

        $member_id = session('member_id');
        $member_info = $member_model->where(['member_id'=>$member_id])->find();

        if($member_info['member_role'] == 2)
        {
            $condition['member_shop'] = $member_info['member_shop'];
        }

        $count = $member_model->join('LEFT JOIN oms_member_group ON oms_member.member_group_id = oms_member_group.member_group_id')->where($condition)->count();

        $Page = new \Think\Page($count,25);
        $Page->setConfig('header','<li class="rows"><b>[%NOW_PAGE%</b>/<b>%TOTAL_PAGE%]</b> Total <b>%TOTAL_ROW%</b> records </li>');
        $Page->setConfig('prev','prev page');
        $Page->setConfig('next','next page');
        $Page->setConfig('last','last page');
        $Page->setConfig('first','first page');
        $Page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE%  %HEADER%');
        $show = $Page->show();

        $member_lists = $member_model->join('LEFT JOIN oms_member_group ON oms_member.member_group_id = oms_member_group.member_group_id')->where($condition)->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->assign('member_lists',$member_lists);
        $this->assign('page',$show);
        $this->display();   
    }

    public function addMember()
    {
        $member_group_lists = M('member_group')->select();
        $shop_lists = M('shops')->select();
        $this->assign('member_group_lists',$member_group_lists);
        $this->assign('shop_lists',$shop_lists);
        $this->display();
    }

    public function saveMember()
    {
        $data['member_name'] = I('post.member_name');
        $data['member_role'] = I('post.member_role');

        if(I('post.member_role') == 1) //posted from the form
        {
            $data['member_role'] = 1;
            $data['member_shop'] = 26; // Hilton, Head office
        }else{

            $data['member_role'] = I('post.member_role');
            $data['member_shop'] = I('post.member_shop');
        }

            
        
        $data['member_pwd'] = md5(I('post.member_pwd'));
        $data['member_group_id'] = I('post.member_group_id');

       // echo "<pre>";
       // print_r($data);
       // echo "</pre>";

       // die;

        if(M('member')->add($data))
        {
            $this->success('add member success',U('Member/index'));
        }else{
            $this->error('add member fail!',U('Member/addMember'));
        }
    }

    public function editMember()
    {
        $member_id = I('get.member_id');
        $member_model = M('member');
        $member_info = $member_model->join('LEFT JOIN oms_member_group ON oms_member.member_group_id = oms_member_group.member_group_id')->where(['member_id'=>$member_id])->find();
       
        $member_group_lists = M('member_group')->select();
        $this->assign('member_group_lists',$member_group_lists);

        $shop_lists = M('shops')->select();
        $this->assign('shop_lists',$shop_lists);

        $this->assign('member_info',$member_info);
        $this->display();
    }

    public function updateMember()
    {
        $member_id = I('post.member_id');
        $data['member_role'] = I('post.member_role');

        if(I('post.member_role') == 1)
        {
            $data['member_role'] = 2;
            $data['member_shop'] = 26; // Hilton, Head office
        }else{
            $data['member_role'] = I('post.member_role');
            $data['member_shop'] = I('post.member_shop');
        }

        $data['member_name'] = I('post.member_name');
        if(I('post.member_pwd') != '')
        {
            $data['member_pwd'] = md5(I('post.member_pwd'));
        }
        $data['member_group_id'] = I('post.member_group_id');

        if(M('member')->where(['member_id'=>$member_id])->save($data) !== FALSE)
        {
            $this->success('update member success',U('Member/index'));
        }else{
            $this->error('update member fail!',U('Member/addMember'));
        }        
    }

    public function memberGroup()
    {
        $member_group_lists = M('member_group')->select();
        $this->assign('member_group_lists',$member_group_lists);
        $this->display();
    }

    public function addMemberGroup()
    {
        $this->display();
    }

    public function saveMemberGroup()
    {
        $data['group_name'] = I('post.group_name');
        $data['manager'] = (I('post.manager') != '')? I('post.manager'):0;
        $data['seller'] = (I('post.seller') != '')? I('post.seller'):0;
        $data['warehouse'] = (I('post.warehouse') != '')? I('post.warehouse'):0;
        $data['corporate'] = (I('post.corporate') != '')? I('post.corporate'):0;
        $data['quality'] = (I('post.quality') != '')? I('post.quality'):0;
        $data['balance'] = (I('post.balance') != '')? I('post.balance'):0;
        $data['cash_confirm'] = (I('post.cash_confirm') != '')? I('post.cash_confirm'):0;
        $data['customer_care'] = (I('post.customer_care') != '')? I('post.customer_care'):0;
        $data['goods_manager'] = (I('post.goods_manager') != '')? I('post.goods_manager'):0;

        if(M('member_group')->add($data))
        {
             $this->success('add member group success',U('Member/memberGroup'));
        }else{
            $this->error('add member group fail',U('Member/addMemberGroup'));
        }
    }

    public function editMemberGroup()
    {
        $member_group_id = I('get.member_group_id');
        $member_group_info = M('member_group')->where(['member_group_id'=>$member_group_id])->find();
        $this->assign('member_group_info',$member_group_info);
        $this->display();

    }

    public function updateMemberGroup()
    {
        $member_group_id = I('post.member_group_id');
        $data['group_name'] = I('post.group_name');
        $data['manager'] = (I('post.manager') != '')? I('post.manager'):0;
        $data['seller'] = (I('post.seller') != '')? I('post.seller'):0;
        $data['warehouse'] = (I('post.warehouse') != '')? I('post.warehouse'):0;
        $data['corporate'] = (I('post.corporate') != '')? I('post.corporate'):0;
        $data['quality'] = (I('post.quality') != '')? I('post.quality'):0;
        $data['balance'] = (I('post.balance') != '')? I('post.balance'):0;
        $data['cash_confirm'] = (I('post.cash_confirm') != '')? I('post.cash_confirm'):0;
        $data['customer_care'] = (I('post.customer_care') != '')? I('post.customer_care'):0;
        $data['goods_manager'] = (I('post.goods_manager') != '')? I('post.goods_manager'):0;

        if(M('member_group')->where(['member_group_id'=>$member_group_id])->save($data) !== FALSE)
        {
             $this->success('update member group success',U('Member/memberGroup'));
        }else{
            $this->error('update member group fail',U('Member/memberGroup'));
        }

    }
}
