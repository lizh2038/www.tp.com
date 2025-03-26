<?php

use app\controller\User;
use app\model\User as ModelUser;

if(!function_exists('getSalt')) {
    function getSalt()
    {
        return 'a400406c1b';
    }
    
} 

if(!function_exists('isLogin')) {
    function isLogin()
    {
        return session('?user');
    }
    
} 
if (!function_exists('loginUser')) {
    function loginUser()
    {
        
        return session('user');
    }
}
if (!function_exists('isMineopt')) {
    function isMineopt($user_id)
    {
        $user = ModelUser::find($user_id);
        if($user && session('?user')){
            $session_user = session('user');
            return $session_user->id == $user->id;
        }else{
            return false;
        }
    }
}
if (!function_exists('isAdmin')) {
    function isAdmin()
    {
        if(session('?user')){
            $session_user = session('user');
            return $session_user->is_admin == 1;
        }else{
            return false;
        }
    }
}
