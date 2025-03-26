<?php
declare (strict_types = 1);

namespace app\middleware;
use app\model\User;
use think\facade\Cookie;
class CheckRememberMe
{
    /**
     * 处理请求
     *
     * @param \think\Request $request
     * @param \Closure       $next
     * @return Response
     */
    public function handle($request, \Closure $next)
    {
        if (!session('?user') ){
            if(Cookie::has('user_id')){
                $session_user = User::find(Cookie::get('user_id'));
                if($session_user){
                    session('user', $session_user);
                }
        }
        }
        return $next($request);
    }
}
