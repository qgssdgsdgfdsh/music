<?php
namespace app\index\controller;
use think\Controller;
use think\View;
use think\Db;
use think\Session;
use think\Cookie;;

use app\index\model\User as UserModel;
// use app\index\validate\User as UserVali;

class User extends Controller
{
	public function register(View $view)
    {	
    	$view->assign(['root'=>$this->root,
                    'images'=>$this->images]);
    	$view->assign('title','Bess用户中心门户-手机号注册');
    

    	return $view->fetch();
    
    }

    //第三方登录
    public function otherthree()
    {
        include ('../vendor/framework/lx/src/other/open51094.class.php');
        $uniq = $otherinfo['uniq'];
        //判断是否用该第三方账号登录过
        $arr = UserModel::checkexists($uniq,'uniq');
        if($arr['status'] == 0){
          //如果没登录过,就注册一个账号
           $data['username'] = $otherinfo['name'];
           $data['pic'] = $otherinfo['img'];
           $data['uniq'] = $uniq;
           $data['sex'] = $otherinfo['sex'];
           $data['from'] = $otherinfo['from'];
           $data['rtime'] = time();
           $data['rip'] = ip2long($_SERVER['REMOTE_ADDR']);
           $arr = UserModel::register($data);
        }
        //把登录信息存入session
        $info['uniq'] = $uniq;
        session('info',$info);
      header('location:'.$this->root);
      exit;
    } 

    //找回密码
    public function findpass(View $view)
    {
        $view->assign('root',$this->root);
        $view->assign('title','找回密码');
        return $view->fetch();
    }
    //通过邮箱找回密码页面
     public function findemail(View $view)
    {
        $view->assign(['root'=>$this->root,
                       'images'=>$this->images]);
        $view->assign('title','通过邮箱找回密码');
        return $view->fetch();
    }
    //通过手机找回密码页面
    public function findtel(View $view)
    {
        $view->assign(['root'=>$this->root,
                       'images'=>$this->images]);
        $view->assign('title','通过手机找回密码');
        return $view->fetch();
    }

    //执行通过手机号码找回密码
    public function dofindtel()
    {
        $tel = input("post.tel");
        $password = md5(input('post.password'));
        
        $arr = UserModel::findpass($tel,$password,'tel');
       echo json_encode($arr);

    }

     //执行通过安全邮箱找回密码
    public function dofindemail()
    {
        $email = input('post.email');
        $password = md5(input('post.password'));
      
        $arr = UserModel::findpass($email,$password,'email');
       echo json_encode($arr);

    }

    //检查手机号码是否注册
    public function checktel()
    {
      $tel = input('post.tel');
      
      $arr = UserModel::checkexists($tel,'tel');

      echo json_encode($arr);
    }

    //检查用户名是否已被注册
    public function checkuser()
    {
        $username = input('post.username');
        // $username = 17674514010;
        $arr = UserModel::checkuser($username);
        echo json_encode($arr);
    }

     //检查安全邮箱是否注册
    public function checkemail()
    {
      $email = input('post.email');
      
      $arr = UserModel::checkexists($email,'email');

      echo json_encode($arr);
    }
    //注册方法 
    public function doRegister()
    {	
        $data['username']  = trim(input('post.username'));
        $data['password'] = md5(trim(input('post.password')));
    	
        $data['tel'] = input('post.tel');
        $data['rip'] = ip2long($_SERVER['REMOTE_ADDR']);
        $data['rtime'] = time();

        //查询数据库是否有该用户
        $res = Db::name('user')->where(['username'=>$data['username']])->select();
        // var_dump($data);die;
        if($res){
            $arr = ['status'=>0,'msg'=>'用户名已存在'];
        }else{
         $result = Db::name('user')->insert($data);
            if($result == 1){
                 $arr = ['status'=>1,'tel'=>$data['tel'],'msg'=>'注册成功'];
                  $info = ['username'=>$data['username'],'password'=>$data['password']];
                  session('info',$info);
            }else{
                $arr = ['status'=>0,'tel'=>$data['tel'],'msg'=>'注册失败'];
            }
        }

        echo json_encode($arr);


    }

    //ajax检查图形验证码是否正确
    public function check(){
    	$graphCaptcha = $_POST['graphCaptcha'];
    	if(!captcha_check($graphCaptcha)){
		 $arr = ['status'=>0,'msg'=>'图片验证码输入错误'];
		}else{
		 $arr = ['status'=>1,'msg'=>'图片验证通过'];
		}
		echo json_encode($arr);
    }


      //ajax检查短信或邮件验证码是否正确
    public function checkmsg(){

        $captcha = strtolower(input('post.captcha'));
        $type = input('post.type');
        if($type == 'email'){
            $check = $_SESSION['checkemail'];
        }else{
           $check = $_SESSION['checkmobile'];
        }
       
        // var_dump($checkmobile,$captcha);die;
        if($check !== $captcha){
         $arr = ['status'=>0,'msg'=>'验证码输入错误'];
        }else{
         $arr = ['status'=>1,'msg'=>'验证通过'];
        }
        echo json_encode($arr);
    }
    

    //调用短信接口
    public function message()
    {
        return  include('../vendor/framework/lx/src/message.php');

    }

    //申请发送邮箱验证码
    public function sendemail()
    { 
       include('../vendor/framework/lx/src/email/functions.php');
       $to = input('post.email');
      
       $title = 'Bess音乐---重置密码';
       $captcha = substr(str_shuffle('123456789abcdefghijkmnpqrstuvwxyz'),0,6);
       $content = '尊敬的Bess音乐用户您好,您正在申请重置密码，验证码为'.$captcha.',如非您本人操作请忽略【Bess音乐】';
       $res = sendmail($to,$title,$content);
       if($res){
          $_SESSION['checkemail'] = $captcha;
          $arr = ['status'=>1,'msg'=>'发送成功'];
       }else{
           $arr = ['status'=>1,'msg'=>'发送失败'];
       }

       echo json_encode($arr);
    }
   
   //用手机短信登录
   public function tellogin()
   {
      $tel = input('post.tel');
     
      $res = Db::table('user')->where("tel = '$tel'")->select();
      
      if(!$res){
          $arr = ['status'=>0,'msg'=>'该手机号尚未注册，请先注册'];
      }else{

          $info['username'] = $res[0]['username'];
          $info['password'] = $res[0]['password'];
          session('info',$info);
          //修改最后一次登陆时间为当前时间
            $uid = $res[0]['uid'];
                $result = Db::table('user')->where('uid',$uid)->update(['logintime'=>time()]);
           $userinfo = ['username'=>$res[0]['username'],
                             'pic'=>$res[0]['pic'],
                             'tel'=>$res[0]['tel'],
                             'level'=>$this->level($res[0]['level'])];
                $arr = ['userinfo'=>$userinfo,'status'=>1,'msg'=>'登录成功'];
      }
      echo json_encode($arr);
   }

   //登录
   public function login()
    {   
       // Cookie::delete('username');
       // Cookie::delete('password');
        // var_dump($_COOKIE);die;
       $username = input('post.username');
       $password = md5(input('post.password'));
       $captcha = input('post.captcha');
       $autologin = input('post.autologin');
        
      
       //检查验证码是否正确
        if(!captcha_check($captcha)){
                //验证码错误直接返回错误数据
         $arr = ['status'=>0,'msg'=>'图片验证码输入错误'];
        }else{
            //查询用户名和密码是否正确
         $res = Db::table('user')->where("(username = '$username' || tel = '$username' || email = '$username') && password = '$password'")->select();

            if($res){
                //登录成功,
                $uid = $res[0]['uid'];
                $username = $res[0]['username'];
                //修改最后一次登陆时间为当前时间
                $result = Db::table('user')->where('uid',$uid)->update(['logintime'=>time()]);
                //把用户信息存入session
                $info = ['username'=>$username,'password'=>$password];
                session('info',$info);
                //判断是否记住密码
                if($autologin == 'true'){
                    cookie('username', input('post.username'),3600*24);
                    cookie('password', md5(input('post.password')),3600*24);
                }else{
                  cookie('username', null);
                  cookie('password', null);
                }
                $userinfo = ['username'=>$res[0]['username'],
                             'pic'=>$res[0]['pic'],
                             'tel'=>$res[0]['tel'],
                             'level'=>$this->level($res[0]['level'])];
                $arr = ['userinfo'=>$userinfo,'status'=>1,'msg'=>'登录成功'];
            }else{
                //登录失败
                 $arr = ['status'=>2,'msg'=>'账号或密码错误'];
            }
        }
        echo json_encode($arr);

   }

    public function loginout()
    {
        $_SESSION = [];
       if(empty(session(''))){
          $arr = ['status'=>1,'msg'=>'退出成功'];
       }else{
          $arr = ['status'=>0,'msg'=>'退出失败'];
       }

       echo json_encode($arr);

    }

}

