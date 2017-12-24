<?php
namespace app\index\model;
use think\Model;

class Article extends Model
{

	public function post($data)
	{	
		$this->create($data);
	}

}