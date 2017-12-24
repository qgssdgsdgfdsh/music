<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2017 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

namespace think;

use think\exception\ValidateException;
use traits\controller\Jump;

Loader::import('controller/Jump', TRAIT_PATH, EXT);

class Controller
{
    use Jump;

    /**
     * @var \think\View 视图类实例
     */
    protected $view;

    public $root = 'http://www.music.com/';
    public $css = 'http://www.music.com/static/css/';
    public $js = 'http://www.music.com/static/js/';
    public $picture = 'http://www.music.com/static/picture/';
    public $images = 'http://www.music.com/static/images/';

    //判断用户等级
        public function level($level)
        {
            
             switch($level){
                case 1:
                  $level = '普通会员';
                  break;
                case 2:
                  $level = '高级会员';
                  break;
             }

             return $level;
        }
       //检查是否是登录状态
    public function  checklogin()
    {   
        // session('info',null);
        // var_dump(session(''));die;
        if(empty(session('info')['username']) || empty(session('info')['password'])){

                if(empty($_SESSION['think']['info']['uniq'])){
                    $info = ['status'=>0,'msg'=>'未登录'];
                }else{
                   
                    $uniq= session('info')['uniq'];

                //查询第三方唯一id标识是否正确
                 $res = Db::table('user')->where('uniq',$uniq)->select();
                if($res){
                    $this->userinfo = $res[0];
                    $info = ['status'=>1,'msg'=>'已登录'];
                }else{
                     $this->userinfo = '';
                    $info = ['status'=>0,'msg'=>'未登录'];
                }
                }
            

        }else{

            $username = session('info')['username'];
            $password = session('info')['password'];

             //查询用户名和密码是否正确
          $res = Db::table('user')->where("(username = '$username' || tel = '$username' || email = '$username') && password = '$password'")->select();

            if($res){
                $this->userinfo = $res[0];
                // var_dump($this->userinfo);die;
                $info = ['status'=>1,'msg'=>'已登录'];
            }else{
                $info = ['status'=>0,'msg'=>'未登录'];
            }

        }

        echo  json_encode($info);
        
    }
    /**
     * @var \think\Request Request 实例
     */
    protected $request;

    /**
     * @var bool 验证失败是否抛出异常
     */
    protected $failException = false;

    /**
     * @var bool 是否批量验证
     */
    protected $batchValidate = false;

    /**
     * @var array 前置操作方法列表
     */
    protected $beforeActionList = [];

    /**
     * 构造方法
     * @access public
     * @param Request $request Request 对象
     */
    public function __construct(Request $request = null)
    {
        //检查是否是登录状态
        
      
        if(empty(session('info')['username']) || empty(session('info')['password'])){
           
                if(empty($_SESSION['think']['info']['uniq'])){
                    $info = ['status'=>0,'msg'=>'未登录'];
                }else{
                   
                    $uniq= session('info')['uniq'];

                //查询第三方唯一id标识是否正确
                 $res = Db::table('user')->where('uniq',$uniq)->select();
                if($res){
                    $this->userinfo = $res[0];
                    $info = ['status'=>1,'msg'=>'已登录'];
                }else{
                     $this->userinfo = '';
                    $info = ['status'=>0,'msg'=>'未登录'];
                }
                }

        }else{
         
            $username = session('info')['username'];
            $password = session('info')['password'];

             //查询用户名和密码是否正确
          $res = Db::table('user')->where("(username = '$username' || tel = '$username' || email = '$username') && password = '$password'")->select();
        
            if($res){
                 
                $this->userinfo = $res[0]; 

            }else{
                 $this->userinfo = [];
            }

        }

        //检查是否是登录状态结束
        
        $this->view = View::instance(Config::get('template'), Config::get('view_replace_str'));
        $this->request = is_null($request) ? Request::instance() : $request;

        // 控制器初始化
        $this->_initialize();

        // 前置操作方法
        if ($this->beforeActionList) {
            foreach ($this->beforeActionList as $method => $options) {
                is_numeric($method) ?
                $this->beforeAction($options) :
                $this->beforeAction($method, $options);
            }
        }
    }

    /**
     * 初始化操作
     * @access protected
     */
    protected function _initialize()
    {
    }

    /**
     * 前置操作
     * @access protected
     * @param  string $method  前置操作方法名
     * @param  array  $options 调用参数 ['only'=>[...]] 或者 ['except'=>[...]]
     * @return void
     */
    protected function beforeAction($method, $options = [])
    {
        if (isset($options['only'])) {
            if (is_string($options['only'])) {
                $options['only'] = explode(',', $options['only']);
            }

            if (!in_array($this->request->action(), $options['only'])) {
                return;
            }
        } elseif (isset($options['except'])) {
            if (is_string($options['except'])) {
                $options['except'] = explode(',', $options['except']);
            }

            if (in_array($this->request->action(), $options['except'])) {
                return;
            }
        }

        call_user_func([$this, $method]);
    }

    /**
     * 加载模板输出
     * @access protected
     * @param  string $template 模板文件名
     * @param  array  $vars     模板输出变量
     * @param  array  $replace  模板替换
     * @param  array  $config   模板参数
     * @return mixed
     */
    protected function fetch($template = '', $vars = [], $replace = [], $config = [])
    {
        return $this->view->fetch($template, $vars, $replace, $config);
    }

    /**
     * 渲染内容输出
     * @access protected
     * @param  string $content 模板内容
     * @param  array  $vars    模板输出变量
     * @param  array  $replace 替换内容
     * @param  array  $config  模板参数
     * @return mixed
     */
    protected function display($content = '', $vars = [], $replace = [], $config = [])
    {
        return $this->view->display($content, $vars, $replace, $config);
    }

    /**
     * 模板变量赋值
     * @access protected
     * @param  mixed $name  要显示的模板变量
     * @param  mixed $value 变量的值
     * @return $this
     */
    protected function assign($name, $value = '')
    {
        $this->view->assign($name, $value);

        return $this;
    }

    /**
     * 初始化模板引擎
     * @access protected
     * @param array|string $engine 引擎参数
     * @return $this
     */
    protected function engine($engine)
    {
        $this->view->engine($engine);

        return $this;
    }

    /**
     * 设置验证失败后是否抛出异常
     * @access protected
     * @param bool $fail 是否抛出异常
     * @return $this
     */
    protected function validateFailException($fail = true)
    {
        $this->failException = $fail;

        return $this;
    }

    /**
     * 验证数据
     * @access protected
     * @param  array        $data     数据
     * @param  string|array $validate 验证器名或者验证规则数组
     * @param  array        $message  提示信息
     * @param  bool         $batch    是否批量验证
     * @param  mixed        $callback 回调方法（闭包）
     * @return array|string|true
     * @throws ValidateException
     */
    protected function validate($data, $validate, $message = [], $batch = false, $callback = null)
    {
        if (is_array($validate)) {
            $v = Loader::validate();
            $v->rule($validate);
        } else {
            // 支持场景
            if (strpos($validate, '.')) {
                list($validate, $scene) = explode('.', $validate);
            }

            $v = Loader::validate($validate);

            !empty($scene) && $v->scene($scene);
        }

        // 批量验证
        if ($batch || $this->batchValidate) $v->batch(true);
        // 设置错误信息
        if (is_array($message)) $v->message($message);
        // 使用回调验证
        if ($callback && is_callable($callback)) {
            call_user_func_array($callback, [$v, &$data]);
        }

        if (!$v->check($data)) {
            if ($this->failException) {
                throw new ValidateException($v->getError());
            }

            return $v->getError();
        }

        return true;
    }
}
