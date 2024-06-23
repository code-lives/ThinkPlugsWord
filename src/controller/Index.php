<?php

declare(strict_types=1);

namespace app\word\controller;

use app\word\service\FileService;
use think\admin\Controller;
use think\admin\helper\QueryHelper;
use think\admin\model\SystemOplog;
use think\Response;

/**
 * Execl管理
 * @class Index
 * @package app\word\controller
 */
class Index extends Controller
{

    /**
     * Execl管理
     * @auth true
     * @menu true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function index()
    {
        SystemOplog::mQuery()->layTable(function () {
            $this->title = 'Execl导入Txt';
            $columns = SystemOplog::mk()->column('action,username', 'id');
            $this->users = array_unique(array_column($columns, 'username'));
            $this->actions = array_unique(array_column($columns, 'action'));
        }, static function (QueryHelper $query) {
            $query->dateBetween('create_at')->equal('username,action')->like('content,geoip,node');
        });
    }
    /**
     * 开始转化
     * @auth true
     * @menu true
     * @throws \think\admin\Exception
     */
    public function toTxt()
    {
        // 获取要下载的文件路径
        $param = $this->request->post();
        $this->_vali([
            'xkey.require' => '文件不存在,重新上传。',
        ]);
        $filePath = FileService::make()->toTxt($param);
        $this->success("开始转化", $filePath, 1);
    }
    /**
     * 下载Txt
     * @auth true
     * @menu true
     * @return void
     */
    public function downloadTxt()
    {
        $param = $this->request->get();
        return download($param['path'], 'code-lives.txt')->expire(300);
    }
}
