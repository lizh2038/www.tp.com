<?php
declare (strict_types = 1);

namespace app\middleware;

class Guest
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
        if(isLogin()){
            
            return redirect((string)url('home'))->with('warning','您已经登录');
        }
        return $next($request);
    }
}
