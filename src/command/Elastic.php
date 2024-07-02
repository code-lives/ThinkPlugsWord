<?php

declare(strict_types=1);

namespace app\word\command;

use app\word\service\ElasticService;
use think\admin\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\Output;

/**
 * Elastic管理指令
 * @class Elastic
 * @package app\word\command
 */
class Elastic extends Command
{
    /**
     * 配置指令
     */
    protected function configure()
    {
        $this->setName('elastic:reset')->setDescription('开始重置数据');
        $this->addArgument('uuid', Argument::OPTIONAL, '用户uid', '');
    }


    /**
     * @param Input $input
     * @param Output $output
     * @throws \think\admin\Exception
     */
    protected function execute(Input $input, Output $output)
    {
        $uuid = $input->getArgument('uuid');
        if (!$uuid) {
            $this->setQueueSuccess("非法操作！");
        }
        $service = ElasticService::make();
        $service->esCreateIk();
        $result = ElasticService::make()->fileList($uuid);
        $count = 0;
        $total = count($result);
        foreach ($result as $key => $val) {
            ++$count;
            $content = '';
            if ($val['xext'] === 'docx' || $val['xext'] === 'doc') {
                $content = $service->getDoc($val['xkey']);
            } else if ($val['xext'] == 'pdf') {
                $content = $service->getPdf($val['xkey']);
            } else if ($val['xext'] == 'txt') {
                $content = $service->getTxt($val['xkey']);
            } else if ($val['xext'] == 'xlsx') {
                $content = $service->getExecl($val['xkey']);
            } else if ($val['xext'] == 'xlss') {
                $content = $service->getExecl($val['xkey']);
            } else {
                $content = $val['name'];
            }
            $val['content'] = $content;
            $service->esInsert($val, $content);
            $this->setQueueMessage($total, $count, $val['name']);
        }
        $this->setQueueSuccess("重置成功");
    }
}
