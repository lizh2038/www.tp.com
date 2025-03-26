<?php
declare (strict_types = 1);

namespace app\controller;
use app\model\User;
class StaticPages
{
    public function home()
    {
        $topics = [];
        $count = 0;
        if(isLogin()){
            $user = loginUser();
            $topics = $user->feed()->paginate(3);
            $count = $topics->total();
        }
        return view('home',compact('topics','count'));
    }

    public function help()
    {
        return view('static_pages/help');
    }

    public function about()
    {
        return view('static_pages/about');
    }
    public function test()
    {
        $user = loginUser();
        $user->followings()->save(2);
    }
}
