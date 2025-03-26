<?php
declare (strict_types = 1);

namespace app\model;

use think\Model;
use think\model\Pivot;

/**
 * @mixin \think\Model
 */
class Followers extends Pivot
{
    
    protected $autoWriteTimestamp = true;
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
}
