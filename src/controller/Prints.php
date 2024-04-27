<?php

declare(strict_types=1);

namespace app\word\controller;

use think\admin\Controller;
use think\admin\helper\QueryHelper;
use think\admin\model\SystemOplog;
use think\Response;

/**
 * 编号打印管理
 * @class Index
 * @package app\word\controller
 */
class Prints  extends Controller
{
    /**
     * 编号打印管理
     * @auth true
     * @menu true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function index()
    {

        SystemOplog::mQuery()->layTable(function () {
            $this->title = '编号打印管理';
            $columns = SystemOplog::mk()->column('action,username', 'id');
            $this->users = array_unique(array_column($columns, 'username'));
            $this->actions = array_unique(array_column($columns, 'action'));
        }, static function (QueryHelper $query) {
            $query->dateBetween('create_at')->equal('username,action')->like('content,geoip,node');
        });
    }
    /**
     * 普通编号打印
     * @auth true
     * @menu true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function numNormal()
    {
        // return $this->success("哈哈哈");
        $param = $this->request->post();
        $this->_vali([
            'num_min.require' => '最小范围',
            'num_max.require' => '最大范围',
            'num_min.min:1' => '最小为1',
        ]);
        $list['list'] = range($param['num_min'], $param['num_max']);
        $list['prefix'] = $param['symbol'];
        return $this->success("进行展示", $list, 1);
    }
    /**
     * 普通打印页面
     * @auth true
     * @menu true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function Prints()
    {
        // SystemOplog::mQuery()->layTable(function () {
        //     $this->title = 'Execl导入Txt';
        //     $columns = SystemOplog::mk()->column('action,username', 'id');
        //     $this->users = array_unique(array_column($columns, 'username'));
        //     $this->actions = array_unique(array_column($columns, 'action'));
        // }, static function (QueryHelper $query) {
        //     $query->dateBetween('create_at')->equal('username,action')->like('content,geoip,node');
        // });
        return view();
    }
}
