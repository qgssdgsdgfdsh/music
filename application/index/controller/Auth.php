<?php
 namespace app\index\controller;
 use think\Controller;
 use think\Request;
 use app\index\model\User;
 use think\Validate;
 class Auth extends Controller
 {
 	public function register()
 	{
 		return $this->fetch();
 	}

 	public function doRegister()
 	{

 		//定义验证规则
 		
 		//echo $_POST['username'];die;
 		//var_dump(input('?post.'));
 		
 		$validate = new Validate([
			'username' => 'require|max:25',
			'password' => 'require|max:6,12',
			'email' => 'email'
		]);

		$data = [
			'username' => input('post.username'),
			'password' => input('post.password'),
			'email' => input('post.email')
		];




		User::create($data);

 		
 	}
 }


