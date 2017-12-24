<?php
namespace app\index\model;
use think\Model;
use think\Db;


class User extends Model
{	
	//通过手机或邮箱重置密码
	static public function findpass($num,$password,$type)
	{	
		$oldpass = Db::table('user')->where($type,$num)->value('password');
		if($oldpass == $password){
			return $arr = ['status'=>0,'msg'=>'密码不能和旧密码相同'];
		}
		$res = Db::name('user')->where("$type = '$num'")->update(['password'=>"$password"]);
		if($res){
			$username = Db::name('user')->where("$type = '$num'")->find()['username'];
          $arr = ['status' => 1,'msg'=>'找回密码成功','username'=>$username];
        }else{
          $arr = ['status'=>0,'msg'=>'找回密码失败'];
        }
		return $arr;
	}

	//检查手机或者邮箱是否注册的方法
	static public function checkexists($num,$type)
	{	
	  $res = Db::table('user')->where("$type = '$num'")->select();
      
      if(!$res){
          $arr = ['status'=>0,'msg'=>'尚未注册，请先注册'];
      }else{
          $arr = ['status'=>1,'msg'=>'已注册'];
      }

      return $arr;
	}

	//检查用户名是否被注册
	static public function checkuser($username)
	{	
	  $res = Db::table('user')->where("username = '$username' || tel = '$username' || email = '$username'")->select();
      
      if(!$res){
          $arr = ['status'=>0,'msg'=>'尚未注册，请先注册'];
      }else{
          $arr = ['status'=>1,'msg'=>'已注册'];
      }

      return $arr;
	}

	//注册一个用户
	static public function register($data)
	{
		   $result = Db::name('user')->insert($data);
		  if($result){
		  	$arr = ['status'=>1,'msg'=>'注册成功'];
		  }else{
		  	$arr = ['status'=>0,'msg'=>'注册失败'];
		  }

		  return $arr;
	}
	
}

