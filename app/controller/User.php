<?php
declare (strict_types = 1);

namespace app\controller;

use app\BaseController;
use think\exception\ValidateException;
use Exception;
use think\Request;
use PHPMailer\PHPMailer\PHPMailer;
use think\facade\Request as RequestFacade;
use app\model\User as UserModel;
use app\validate\UserValidate;
use think\facade\Cookie;
use think\facade\Session;

class User 
{
    protected $middleware = [
        'auth' => ['except' => ['create', 'save','index','read','confirm_email','sendEmailConfirmationTo']],
        'guest' => ['only' => ['create']],
    ];
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $users = UserModel::paginate(6);
        $count = $users->total();
        return view('user/index', compact('users', 'count'));
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        return view();
    }

    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        
        try {
            validate(UserValidate::class)->batch()->check($request->post());
        /*  $data = $request->post();
            $data['password'] = md5($data['password'].getSalt());
            $user = UserModel::create($data, ['name', 'email', 'password']);
             */
            $user = new UserModel();
            $user->name = $request->post('name');
            $user->email = $request->post('email');
            $user->password = md5($request->post('password').getSalt());
            $user->save();
            /* session('user', $user);
            return redirect((string)url('user/read', ['id' => $user->id]))->with('success', '注册成功');
 */         
            $this->sendEmailConfirmationTo($user->active_token,$user->email);
            return redirect((string)url('home'))->with('success', '恭喜你，注册成功，请前往邮箱激活账号！');
        

        } catch (ValidateException $e) {
            // 记录旧表单数据
            Session::flash('old', $request->post());
            if(!empty($errors = $e->getError())){
                session('errors', $errors); 
            }
            return redirect ((string)url('user/create'));
        }
    }

    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read($id)
    {
        $member = UserModel::find($id);
        $topics = $member->topics()->paginate(3);
        $count = $topics->total();
        return view('user/read', compact('member', 'topics','count'));
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function edit($id)
    {
        if(!isMineopt($id)){
            return redirect((string)url('home'))->with('danger', '无权操作');
        }
        $user = UserModel::find($id);
        return view('user/edit', compact('user'));
    }

    /**
     * 保存更新的资源
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {
        $user = UserModel::find($id);
        try {
            validate([
                'name|用户名称' => 'require|max:25|token|unique:user,name,'.$id,
                'password|密码' => 'min:6|confirm',
            ])->batch()->check($request->post());
            $user->name = $request->post('name');
            if($request->post('password')){
                $user->password = md5($request->post('password').getSalt());
            }
            $user->save();
            session('user', null);
            if(Cookie::has('user_id')){
                Cookie::delete('user_id');
            }
            return redirect((string)url('login.create'))->with('success', '更新成功');


        } catch (ValidateException $e) {
            
            if(!empty($errors = $e->getError())){
                session('errors', $errors); 
            }
            return redirect ((string)url('user/edit', ['id' =>$user->id]));
        }
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete(Request $request, $id)
    {
        $check = $request->checkToken('__token__');
        if(!$check){
            throw new ValidateException('invalid token');
        }
        $user = UserModel::find($id);
        $name = $user->name;
        $login_user = loginUser();
        if(isAdmin() &&($login_user->id !== $user->id) &&($user->is_admin!== 1))
        {
            $user->delete();
            return redirect((string)url('user/index'))->with('success', '成功删除用户-'.$name.'~');
        }else{
            abort(404, '无权操作');
        }
    }
    public function followings($id)
    {
        $user = UserModel::find($id);
        $users = $user->followings()->paginate(2);
        $count = $users->total();
        $title = $user->name.'关注的人';
        return view('user/followings',compact('users','title','count'));

    }
    public function followers($id)
    {
        $user = UserModel::find($id);
        $users = $user->followers()->paginate(2);
        $count = $users->total();
        $title = $user->name.'的粉丝';
        return view('user/followers',compact('users','title','count'));

    }
    public function confirm_email($token)
    {
        $user = UserModel::where('active_token',$token)->find();
        if(!$user->isEmpty()){
            $user->active_token = null;
            $user->actived = 1;
            $user->save();
            session('user',$user);
            return redirect((string)url('User/read',['id'=>$user->id]))->with('success','恭喜你，激活账号成功！');
        }else{
            return redirect((string)url('home'))->with('danger','激活失败！');
        }
    }
    protected function sendEmailConfirmationTo($token,$send_to_email)
    {
        $mail = new PHPMailer(true);
        try {
            // 服务器设置
            $mail->isSMTP();
            $mail->Host       = config('email.host'); // SMTP 服务器
            $mail->SMTPAuth   = true;
            $mail->Username   = config('email.username'); // SMTP 用户名
            $mail->Password   = config('email.password'); // SMTP 密码
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // 启用 TLS 加密
            $mail->Port       = 587; // TCP 端口
            // 启用调试输出
            $mail->SMTPDebug = 2; // 0 = off (for production use), 1 = client messages, 2 = client and server messages
            $mail->Debugoutput = function($str, $level) {
            file_put_contents('D:\phpstudy_pro\WWW\www.tp.com\logs\mail.log', date('Y-m-d H:i:s') . " [$level] $str\n", FILE_APPEND);
            };
            // 收件人
            $mail->setFrom(config('email.username'), 'Mailer');
            $mail->addAddress($send_to_email); // 添加收件人

            //拼装url
            $url = RequestFacade::domain().url('confirm_email',['token'=>$token]);

            // 邮件内容
            $mail->isHTML(true);
            $mail->Subject = '激活您的账户';
            $mail->Body    = '请点击以下激活您的账户：<a href="'.$url.'">激活账户</a>';

            // 发送邮件
            $mail->send();

        } catch (Exception $e) {
            return "邮件发送失败: {$mail->ErrorInfo}";
        }
    }
}
