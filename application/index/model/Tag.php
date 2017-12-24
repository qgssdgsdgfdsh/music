<?php
namespace app\index\model;
use think\Model;

class Tag extends Model
{
	public function user()
	{
		return $this->belongsToMany('User' , 'user_tag' , 'user_id' , 'tag_id');
	}
}