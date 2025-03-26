<?php
declare (strict_types = 1);

namespace app\middleware;

use think\facade\Session;

class Auth
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
        if(!isLogin()){
            
            return redirect((string)url('login.create'))->with('danger','请先登录');
        }
        return $next($request);
    }
}
