<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\facade\Route;

Route::get('/', 'StaticPages/home')->name('home');
Route::get('/help', 'StaticPages/help')->name('help');
Route::get('/about', 'StaticPages/about')->name('about');
Route::get('/test', 'StaticPages/test')->name('test');
//Route::get('/signup', 'User/create')->name('signup');

Route::resource('user', 'User');

//path
Route::get('/login', 'Auth/create')->name('login.create');
Route::post('/login', 'Auth/save')->name('login.save');
Route::delete('/logout', 'Auth/logout')->name('logout');

Route::resource('topic', 'Topic')->only(['save','delete']);

//某人关注列表的对应列表
Route::get('/user/:id/followings','User/followings')->name('user.followings');
//某人对应的粉丝列表
Route::get('/user/:id/followers','User/followers')->name('user.followers');

//关注
Route::post('/user/followers/:id','Followers/save')->name('followers.save')->token();
//取消关注
Route::delete('/user/followers/:id','Followers/delete')->name('followers.delete')->token();


Route::get('/signup/confirm/:token','User/confirm_email')->name('confirm_email');

//填写 Email 的表单
Route::get('/password/reset','Password/showLinkRequestForm')->name('password.request');
//处理表单提交，成功的话就发送邮件，附带 Token 的链接
Route::post('/password/email','Password/sendResetLinkEmail')->name('password.email')->token();
//显示更新密码的表单，包含 token
Route::get('/web/password/reset/:token','Password/showResetForm')->name('password.reset');
//对提交过来的 token 和 email 数据进行配对，正确的话更新密码
Route::post('/password/reset','Password/reset')->name('password.update')->token();
