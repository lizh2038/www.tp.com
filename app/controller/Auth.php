<?php
declare (strict_types = 1);

namespace app\controller;

use app\model\User as UserModel;
use app\validate\UserValidate;
use think\exception\ValidateException;
use think\facade\Cookie;
use think\facade\Session;
use think\Request;

class Auth
{
    protected $middleware = [
        'guest' => ['only' => ['create']],
        'auth' => ['only' => ['logout']],
    ];
    public function create()
    {
        return view();
    }
      public function save(Request $request)
    {
        
        try {
            validate([
                'email|邮箱账号' => 'require|email|max:100|token',
                'password|密码' => 'require|min:6',
            ])->batch()->check($request->post());
            $user = UserModel::where('email', $request->post('email'))->find();
            if($user && $user->password == md5($request->post('password').getSalt())){
                if($user->actived){
                    Cookie::set('user_id', $user->id, 3600 * 24 * 30);
                    session('user', $user);
                    return redirect((string)url('user/read', ['id' => $user->id]))->with('success', '登录成功');
                }else{
                    session('user',null);
                    return redirect((string)url('home'))->with('danger', '账号未激活，请到注册邮箱点击激活~');
                }
                
            }else{ 
                // 记录旧表单数据
                Session::flash('old', $request->post());
                return redirect ((string)url('login.create'))->with('warning', '密码或邮箱账号错误');
            }
            //return redirect((string)url('user/read', ['id' => $user->id]))->with('success', '注册成功');


        } catch (ValidateException $e) {
            // 记录旧表单数据
            Session::flash('old', $request->post());

            if(!empty($errors = $e->getError())){
                session('errors', $errors); 
            }
            return redirect ((string)url('login.create'));
        }
    }
    public function logout()
    {
        session('user', null);
        Cookie::delete('user_id');
        return redirect ((string)url('login.create'))->with('success', '退出成功');
    }
}
