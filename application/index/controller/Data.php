<?php

namespace app\index\controller;
use think\Controller;
use think\View;
use think\Db;
use think\Session;
use think\Cookie;
use app\index\model\Data as DataModel;

class Data extends Controller
{
	public function ziliao(View $view)
	{	
		if(empty($this->userinfo)){
			return $view->fetch('index/gongyi');

		}

		// var_dump($this->userinfo);die;
		$introduce = $this->userinfo['introduce'] == null ? '请填写你的简介' : $this->userinfo['introduce'];
		
		$email = $this->userinfo['email'] == null ? '可用于登录和找回密码' : $this->userinfo['email'];

		$view->assign(['root'=>$this->root,
					  'title'=>'Bess音乐--个人资料页面',
						'introduce'=>$introduce,
						'email'=>$email,
					  'userinfo'=>$this->userinfo]);
		return $view->fetch();
	}

	//判断旧密码是否正确
	public function checkoldpass()
	{
		$oldpass = md5(input('post.oldpass'));
		if($oldpass == $_SESSION['think']['info']['password']){
			$arr = ['status'=>1];
		}else{
			$arr = ['status'=>0];
		}

		echo json_encode($arr);
	}
	//修改密码
	public function updatepass()
	{
		$newpass = md5(input('post.newpass'));
		$username = $_SESSION['think']['info']['username'];
		$data['password'] = $newpass;
		$arr = DataModel::updatepass('username',$username,$data);
		echo json_encode($arr);
	}


	

	//上传头像
	public function upload(){
		// var_dump($_FILES);die;
		if(!empty($_FILES)){
			if($_FILES['photo']['size'] !== 0){
				// 获取表单上传文件 例如上传了001.jpg
				$file = request()->file('photo');
				// var_dump($file);die;
				// 移动到框架应用根目录/public/uploads/ 目录下
				$info = $file->validate(['size'=>20971522,'ext'=>'jpg,png,gif'])->move(ROOT_PATH . 'public' . DS . 'upload');
				if($info){
				
				$data['pic'] = $this->root.'upload/'.str_replace('\\','/',$info->getSaveName());
				
				}else{
				// 上传失败获取错误信息
					var_dump($file->getError()) ;
				}
			}
				if(empty($_SESSION['think']['info']['username'])){
					$type = 'uniq';
					$num = $_SESSION['think']['info']['uniq'];
				}else{
					$type = 'username';
					$_SESSION['think']['info']['username'];
				}

			$res = DataModel::updateziliao($type,$data,$num);
		
		}
		
		header('Location:'.$this->root.'index/data/ziliao');
		exit;
	}
	//修改个人基础资料
	public function updateziliao()
	{
	

			
			$data['username'] = input('post.username');
			$data['sex'] = input('post.sex');
			$data['introduce'] = input('post.introduce');
		
			$username = $_SESSION['think']['info']['username'];

			$res = DataModel::updateziliao('username',$data,$username);
			if($res){
				//修改存入session的登录信息
				$_SESSION['think']['info']['username'] = $data['username'];
				$arr = ['status'=>1,'msg'=>'修改成功'];
			}else{
				$arr = ['status'=>0,'msg'=>'修改失败'];
			}
			
			echo json_encode($arr);
		
	}


	//修改安全邮箱和手机
	public function security()
	{
		$data['tel'] = input('post.tel');
		$data['email'] = input('post.email');

		$username = $_SESSION['think']['info']['username'];

		$res = DataModel::updateziliao($data,$username);
		if($res){
			$arr = ['status'=>1,'msg'=>'修改成功'];
		}else{
			$arr = ['status'=>0,'msg'=>'修改失败'];
		}
		echo json_encode($arr);
	}


}
