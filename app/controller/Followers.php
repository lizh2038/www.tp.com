<?php
declare (strict_types = 1);

namespace app\controller;

class Followers
{
    protected $middleware = [
        'auth' => ['only' => ['save','delete']]
    ];
    public function save($id)
    {
        $user = loginUser();
        $user->follower($id);
        return redirect((string)url('User/read',['id'=>$user->id]))->with('success','恭喜你，关注成功~');

    }
    public function delete($id)
    {
        $user = loginUser();
        $user->unfollower($id);
        return redirect((string)url('User/read',['id'=>$user->id]))->with('success','恭喜你，取消关注成功~');

    }
}
