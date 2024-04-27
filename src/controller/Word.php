<?php

declare(strict_types=1);

namespace app\word\controller;
// php think make:service app\\word\\service\\Execl
// 

use app\word\model\Word as ModelWord;
use app\word\service\FileService;
use app\word\service\PdfService;
use think\admin\Controller;
use think\admin\helper\QueryHelper;
use think\admin\model\SystemOplog;
use think\Response;
use think\admin\model\SystemFile;
use think\admin\service\AdminService;
use think\admin\Storage;

/**
 * Execl管理
 * @class Index
 * @package app\word\controller
 */
class Word extends Controller
{
    protected $types;

    /**
     * 控制器初始化
     * @return void
     */
    protected function initialize()
    {
        $this->types = Storage::types();
    }
    /**
     * Word管理
     * @auth true
     * @menu true
     * @throws \think\admin\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * 
     */
    public function index()
    {

        SystemFile::mQuery()->layTable(function () {
            $this->title = '文件管理';
            $this->xexts = SystemFile::mk()->distinct()->column('xext');
        }, static function (QueryHelper $query) {
            $query->like('name,hash,xext')->equal('type')->dateBetween('create_at');
            $query->where(['issafe' => 0, 'status' => 2, 'uuid' => AdminService::getUserId()]);
        });
    }

    /**
     * 添加文件
     * @auth true
     * @menu true
     */
    public function add()
    {
        $this->_applyFormToken();
        ModelWord::mForm('form');
    }
    /**
     * 编辑word文件
     * @auth true
     * @menu true
     * @return void
     */
    public function edit()
    {
        SystemFile::mForm('form');
    }
    public function file()
    {
        return view();
    }
    /**
     * 删除系统文件
     * @auth true
     * @menu true
     * @return void
     */
    public function remove()
    {
        if (!AdminService::isSuper()) {
            $where = ['uuid' => AdminService::getUserId()];
        }
        SystemFile::mDelete('', $where ?? []);
    }
    /**
     * 下载文件
     * @auth true
     * @menu true
     */
    public function downloads()
    {
        $data = $this->request->get();
        $where = [
            'uuid' => AdminService::getUserId(),
            'id' => $data['id']
        ];
        $data = SystemFile::mk()->where($where)->field('xkey,name,xext')->findOrEmpty();
        if (!$data->isExists()) {
            $this->redirect('/');
        }
        $info = pathinfo($data->name);
        return download('/var/www/html/public/upload/' . $data->xkey, $info[''] . '.' . $data->xext)->expire(300);
    }
    /**
     * 文档转为PDF
     * @auth true
     * @menu true
     */
    public function pdf()
    {
        $data = $this->request->get();
        $where = [
            'uuid' => AdminService::getUserId(),
            'id' => $data['id']
        ];
        $data = SystemFile::mk()->where($where)->field('xkey,name')->findOrEmpty();
        if (!$data->isExists()) {
            $this->redirect('/');
        }
        $info = pathinfo($data->xkey);
        PdfService::make()->convertPdfToImages('/var/www/html/public/upload/' . $info['dirname'] . '/', $info['basename'], $info['extension'], "pdf");
        return download('/var/www/html/public/upload/' . $info['dirname'] . '/' . $info['filename'] . '.pdf', $data->name . '.pdf')->expire(300);
    }
    /**
     * 清理重复文件
     * @auth true
     * @menu true
     * @return void
     * @throws \think\db\exception\DbException
     */
    public function distinct()
    {
        $map = ['uuid' => AdminService::getUserId()];
        $db1 = SystemFile::mk()->fieldRaw('max(id) id')->where($map)->group('type,xkey');
        $db2 = $this->app->db->table($db1->buildSql())->alias('dt')->field('id');
        SystemFile::mk()->whereRaw("id not in {$db2->buildSql()}")->delete();
        SystemFile::mk()->where($map)->where(['status' => 1])->delete();
        $this->success('清理重复文件成功！');
    }
}
