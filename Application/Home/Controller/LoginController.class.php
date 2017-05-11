<?php
namespace Home\Controller;
use Think\Controller;
class LoginController extends Controller {

    public function index()
    {
    	$this->display();
    }

    public function checkLogin()
    {
    	$memmber_name = I('post.username');
    	$password = I('post.password');

    	$member_model = M('member');

       	$user_info = $member_model->where(['member_name'=>$memmber_name])->find();

    	if(empty($user_info))
    	{
    		$this->error('account error',U('Login/index'));
    	}else{

    		$password = md5($password); // NOT very secure
    		if($password == $user_info['member_pwd'])
    		{

                session('member_id',$user_info['member_id']);
                session('member_name',$user_info['member_name']);
                session('member_shop',$user_info['member_shop']);
                session('member_group_id',$user_info['member_group_id']);
                session('member_role',$user_info['member_role']);

    			$this->success('login in success',U('Index/index'));
    		}else{
    			$this->error('account error',U('Login/index'));
    		}
    		
    	}
    }

    public function Logout()
    {
        session(null);
        $this->redirect('Login/index');
    }
}