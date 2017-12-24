<?php
namespace app\index\controller;

use think\Controller;
use app\index\model\User;
use think\Model;
use think\View;
use think\Db;


class Index extends Controller
{
    public function index(View $view)
    { 
      
      //如果为自动登录则则输入框显示存放的账号信息，否则为空
      $cookie_username = cookie('username') ? cookie('username') : '';
      $cookie_password = cookie('password') ? cookie('password') : '';
     	
      //如果已登录，则显示用户信息，否则显示空
      if(empty($this->userinfo)){
          $nickname = null;
          $pic = null;
          $level = null;
          $tel = null;
      }else{
         $nickname = $this->userinfo['username'];
         $pic = $this->userinfo['pic'];
         $level = $this->level($this->userinfo['level']);
         $tel = $this->userinfo['tel'];
      }
      // var_dump($this->userinfo);die;
      $view->assign(['root'=>$this->root,'picture'=>$this->picture,
              'images'=>$this->images,
       				'title'=>'Bess音乐--首页',
              //输入框记住的账号与密码
       				'username'=>$cookie_username,
       				'password'=>$cookie_password,
              'nickname'=>$nickname,
              'pic'=>$pic,
              'level'=>$level,
              'tel'=>$tel]);
       return $view->fetch();
    }

    public function gongyi(View $view)
    {
      return $view->fetch();
    }
  
}
