<?php
declare (strict_types = 1);

namespace app\controller;

use app\model\User as UserModel;
use Exception;
use PHPMailer\PHPMailer\PHPMailer;
use think\exception\ValidateException;
use think\facade\Cookie;
use think\facade\Db;
use think\helper\Str;
use think\Request;
use think\facade\Request as RequestFacade;

class Password
{
    public function showLinkRequestForm(){
        return view();
    }
    public function sendResetLinkEmail(Request $request){
        try {
            validate([
                'email|邮箱账号' => 'require|email',
            ])->batch()->check($request->post());
            $email = $request->post('email');
            $user = UserModel::where('email',$email)->findOrEmpty();
            if(!$user->isEmpty()){
                $obj = Db::name('password_reset')->where('email', $email)->findOrEmpty();
                $token = sha1(Str::random(20).time());
                if(empty($obj)){
                    $data = [
                        'email'=>$email,
                        'token'=>$token,
                        'create_at'=>time()
                    ];
                    Db::name('password_reset')->insert($data);
                }else{
                    $data = [
                        'id'=>$obj['id'],
                        'email'=>$email,
                        'token'=>$token,
                        'create_at'=>time()
                    ];
                    Db::name('password_reset')->save($data);
                }
                $this->sendEmailResetPasswordTo($token,$email);
                return redirect((string)url('password.request'))->with('success', '邮箱发送成功~');
            }else{
                return redirect((string)url('password.request'))->with('danger', '邮箱未注册~');
            }


        } catch (ValidateException $e) {
            if (!empty($errors = $e->getError())) {
                session('errors', $errors);
            }
            return redirect((string)url('password.request'));
        }
    }
    public function showResetForm(Request $request){
        $token = $request->param('token');
        //dump($token);
        return view('password/show_reset_form',compact('token'));
    }
    public function reset(Request $request){
        $token = $request->post('token');
        try {
            validate([
                'token|令牌'=>'require',
                'email|邮箱' => 'require|email',
                'password|密码' => 'min:6|confirm'
            ])->batch()->check($request->post());
            //设置邮箱变量
            $email = $request->post('email');
            $user = UserModel::where('email',$email)->findOrEmpty();
            if($user->isEmpty()){
                return redirect((string)url('password.reset', ['token' => $token]))->with('danger','通过邮箱未找到对应用户信息~');
            }
            $obj = Db::name('password_reset')->where('email', $email)->findOrEmpty();
            if (!empty($obj)){
                //判断token是否过期
                if(time() > $obj['create_at'] + config('app.token_expires')){
                    return redirect((string)url('password.reset', ['token' => $token]))->with('danger','令牌已失效~');
                }
                //token是否合法
                if($token != $obj['token']){
                    return redirect((string)url('password.reset', ['token' => $token]))->with('danger','令牌无效~');
                }
                $user->password = md5($request->post('password') . getSalt());
                $user->save();
                return redirect((string)url('login.create'))->with('success','密码重置成功~');

            }

            return redirect((string)url('password.reset', ['token' => $token]))->with('danger','未找到对应记录~');
        } catch (ValidateException $e) {
            if (!empty($errors = $e->getError())) {
                session('errors', $errors);
            }
            return redirect((string)url('password.reset', ['token' => $token]));
        }
    }
    protected function sendEmailResetPasswordTo($token,$send_to_email){
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

            // 收件人
            $mail->setFrom(config('email.username'), 'Mailer');
            $mail->addAddress($send_to_email); // 添加收件人

            //拼装url
            $url = RequestFacade::domain().url('password.reset',['token'=>$token]);

            // 邮件内容
            $mail->isHTML(true);
            $mail->Subject = '找回您的密码';
            $mail->Body    = '请点击以下找回您的账户密码：<a href="'.$url.'">找回密码</a>';

            // 发送邮件
            $mail->send();

        } catch (Exception $e) {
            return "邮件发送失败: {$mail->ErrorInfo}";
        }
    }
}
