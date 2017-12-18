<?php
namespace app\index\controller;
use think\View;

class Index
{
    public function index()
    {
    	$view=new View();
    	$view->assign('data','wwwwwwwwwwwww');
    	return $view->fetch('index/index');
    }
}
