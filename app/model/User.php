<?php
declare (strict_types = 1);

namespace app\model;

use think\helper\Str;
use think\Model;

/**
 * @mixin \think\Model
 */
class User extends Model
{
    protected $autoWriteTimestamp = true;
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

    public function getAvatarAttr($value)
    {
        return '/images/faces/'.($this->id%7).'.png' . $value;
    }
    public static function onBeforeInsert($user)
    {
        $user->active_token = Str::random(10);
    }
    
    public static function onBeforeDelete($user)
    {
        $user->topics()->delete();
    }
    public function feed()
    {
        
          $user_ids = [];
        $users = $this->followings()->where('follower_id',$this->id)->select()->toArray();
        foreach($users as $v){
            $user_ids[] = $v['id'];
        }
        array_push($user_ids,$this->id);
        //halt($user_ids);
        return Topic::whereIn('user_id',$user_ids)->order('id','desc');
         
        //return $this->topics()->order('id','desc');
    }
    public function topics()
    {
        return $this->hasMany(Topic::class, 'user_id', 'id');
    }
    public function followers()
    {

        return $this->belongsToMany(User::class,Followers::class,'follower_id','user_id');
    }
    public function followings()
    {

        return $this->belongsToMany(User::class,Followers::class,'user_id','follower_id');
    }
    public function follower($user_id)
    {
        $this->followings()->save($user_id);
    }
    public function unfollower($user_id)
    {
        $this->followings()->detach($user_id);
    }
    public function isFollowing($user_id)
    {
        $users =  $this->followings()->where('user_id',$user_id)->select();
        return $users->isEmpty();
    }

}
