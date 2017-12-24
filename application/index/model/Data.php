<?php
namespace app\index\model;
use think\Model;
use think\Db;


class Data extends Model
{	
	//修改资料
	static public function updateziliao($type,$data,$where)
	{	
		
		$res = Db::table('user')->where($type,$where)->update($data);
		return $res;
	}
	//修改密码
	static public function updatepass($fields,$num,$data)
	{	
		//判断新密码是否与旧密码相同
		if($data['password'] == $_SESSION['think']['info']['password']){
			return $arr = ['status'=>0,'msg'=>'新密码不能和旧密码相同'];
		}
		$res = Db::name('user')->where($fields,$num)->update($data);
		if($res){
			$_SESSION['think']['info']['password'] = $data['password'];
			$arr = ['status'=>1,'msg'=>'修改成功'];
		}else{
			$arr = ['status'=>0,'msg'=>'修改失败'];
		}
		return $arr;
	}
	
}

