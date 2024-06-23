<?php

declare(strict_types=1);

namespace app\word\controller;

use app\word\service\ElasticService;
use think\admin\Controller;
use think\admin\model\SystemFile;
use think\admin\service\AdminService;

/**
 * Elastic管理
 * @class Index
 * @package app\word\controller
 */
class Elastic extends Controller
{
    /**
     * Elastic管理
     * @auth true
     * @menu true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function index()
    {
        $this->title = 'Elastci列表';
        if (in_array(request()->get('output'), ['get.json', 'layui.table'])) {
            return  ElasticService::make()->esSearch();
        } else {
            SystemFile::mQuery()->layTable();
        }
    }
    /**
     * Elastic数据重置
     * @auth true
     * @menu true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function addQueue()
    {
        $this->_queue('重置数据', "elastic:reset " . AdminService::getUserId() . "");
    }
}
