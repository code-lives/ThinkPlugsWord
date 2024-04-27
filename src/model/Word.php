<?php

declare(strict_types=1);

namespace app\word\model;

use think\admin\Model;
use think\admin\model\SystemUser;
use think\model\relation\HasOne;

/**
 * @mixin \think\Model
 */
class Word extends Model
{
    protected $table = 'word_file';
    protected $createTime = 'create_at';
    protected $updateTime = 'update_at';
    /**
     * Word
     * @var string
     */
    protected $oplogName = 'Word管理';

    /**
     * 日志类型
     * @var string
     */
    protected $oplogType = 'Word管理';

    /**
     * 格式化创建时间
     * @param mixed $value
     * @return string
     */
    public function getCreateAtAttr($value): string
    {
        return format_datetime($value);
    }
    /**
     * 关联用户数据
     * @return \think\model\relation\HasOne
     */
    public function user(): HasOne
    {
        return $this->hasOne(SystemUser::class, 'id', 'uuid')->field('id,username,nickname');
    }
}
