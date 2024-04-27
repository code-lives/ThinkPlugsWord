<?php

declare(strict_types=1);

namespace app\word\model;

use think\Model;

/**
 * @mixin \think\Model
 */
class File extends Model
{
    protected $table = 'system_file';

    //查询文件是否存在
    public function isFind($uid, $xkey)
    {
        return $this->where([
            ['uuid', '=', $uid],
            ['xkey', '=', $xkey]
        ])->find();
    }
}
