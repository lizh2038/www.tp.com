<?php
declare (strict_types = 1);

namespace app\controller;

use think\exception\ValidateException;
use think\facade\Session;
use think\Request;
use app\model\Topic as TopicMode;
class Topic
{
    protected $middleware = [
        'auth' => ['except' => [ 'save','delete']],
        
    ];
  
    public function save(Request $request)
    {
        try {
            validate([
                'content|博文内容' => 'require|max:2000',
                
            ])->batch()->check($request->post());
            $topic = loginUser();
            $topic->topics()->save([
                'content' => $request->post('content'),
            ]);
            return redirect ((string)url('home'))->with('success', '发布成功');
           
        } catch (ValidateException $e) {
            Session::flash('old_topic_content', $request->post('content'));
            if(!empty($errors = $e->getError())){
                session('errors', $errors); 
            }
            return redirect ((string)url('home'));
        }
    }

   
  
    public function delete($id)
    {
        $topic = TopicMode::find($id);
        if($topic && isMineopt($topic->user_id)){
            $topic->delete();
            return redirect ((string)url('home'))->with('success','你成功删除了一篇博文~');
        }else{
            abort(404,'非法操作');
        }
    }
}
