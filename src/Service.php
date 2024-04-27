<?php

// +----------------------------------------------------------------------
// | Wechat Plugin for ThinkAdmin
// +----------------------------------------------------------------------
// | 版权所有 2014~2024 Anyon <zoujingli@qq.com>
// +----------------------------------------------------------------------
// | 官方网站: https://thinkadmin.top
// +----------------------------------------------------------------------
// | 开源协议 ( https://mit-license.org )
// | 免责声明 ( https://thinkadmin.top/disclaimer )
// +----------------------------------------------------------------------
// | gitee 代码仓库：https://gitee.com/zoujingli/think-plugs-wechat
// | github 代码仓库：https://github.com/zoujingli/think-plugs-wechat
// +----------------------------------------------------------------------

declare(strict_types=1);

namespace app\word;


use think\admin\Plugin;

/**
 * 组件注册服务
 * @class Service
 * @package app\word
 */
class Service extends Plugin
{
    /**
     * 定义插件名称
     * @var string
     */
    protected $appName = '文件管理';

    /**
     * 定义安装包名
     * @var string
     */
    protected $package = 'code-lives/think-plugs-word';

    /**
     * 注册组件服务
     * @return void
     */
    public function register(): void
    {
    }

    /**
     * 增加微信配置
     * @return array[]
     */
    public static function menu(): array
    {

        return [
            [
                'name' => '文件列表',
                'subs' => [
                    ['name' => '文档列表', 'icon' => 'layui-icon layui-icon-folder', 'node' => 'word/word/index'],
                ],
            ],
            [
                'name' => 'EXECL管理',
                'subs' => [
                    ['name' => 'Execl导入Txt', 'icon' => 'iconfont iconfont-edit', 'node' => 'word/index/index'],
                ],
            ],
            [
                'name' => '编号打印',
                'subs' => [
                    ['name' => '编号打印', 'icon' => 'layui-icon layui-icon-print', 'node' => 'word/prints/index'],
                ],
            ],

        ];
    }
}
