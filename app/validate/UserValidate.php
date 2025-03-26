<?php
declare (strict_types = 1);

namespace app\validate;

use think\Validate;

class UserValidate extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名' =>  ['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'name|用户名称' => 'require|max:25|unique:user|token',
        'email|邮箱' => 'require|email|max:100|unique:user',
        'password|密码' => 'require|min:6|confirm',
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名' =>  '错误信息'
     *
     * @var array
     */
    protected $message = [
        'name.require' => '名称必须填写',
        'name.max' => '名称最多不能超过25个字符',
        'name.unique' => '名称已存在',
        'email.require' => '邮箱必须填写',
        'email.email' => '邮箱格式错误',
        'email.max' => '邮箱最多不能超过100个字符',
        'email.unique' => '邮箱已存在',
        'password.require' => '密码必须填写',
        'password.min' => '密码最少不能少于6个字符',
        'password.confirm' => '两次密码不一致',
    ];
}
