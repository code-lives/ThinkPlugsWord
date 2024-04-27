<?php

declare(strict_types=1);

namespace app\word\service;

use think\admin\service\AdminService;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;


class FileService
{
    public static function make(...$parameters)
    {
        return new static(...$parameters);
    }
    //转化为Txt
    public function toTxt($param)
    {
        if (!$this->isFind($param['xkey'])) {
            return ['status' => false, 'msg' => '文件不存在'];
        }
        $data = ExeclService::make()->readExecl('../public/upload/' . $param['xkey']);
        $string = '';
        foreach ($data as $key => $val) {
            $filteredVal = array_filter($val, function ($item) {
                return $item !== null;
            });
            // 检查过滤后的数组是否为空  
            if (empty($filteredVal)) {
                // 如果为空（即原数组只包含null或为空数组），则直接添加换行符  
                $string .= "\n";
            } else {
                // 否则，使用implode连接数组元素并添加换行符  
                $string .= implode($param['symbol'] ? $param['symbol'] : "", $filteredVal) . "\n";
            }
        }
        $fileName = time() . '.txt';
        return $this->writeIn($fileName, $string);
    }
    //Execl写入Txt文件
    public function writeIn($fileName, $content)
    {
        // 要写入的文件路径
        $filePath = 'upload/txt/' . $fileName;
        // 检查目录是否存在，如果不存在则创建它
        $directory = dirname($filePath);
        if (!file_exists($directory)) {
            mkdir($directory, 0777, true);
        }
        // 检查文件是否可写
        if (!is_writable($directory)) {
            return ['status' => false, 'msg' => '文件不可写'];
        }
        // 要写入的内容
        try {
            // 打开文件以进行写入，如果文件不存在则创建它
            $file = fopen($filePath, 'w');
            if (!$file) {
                return ['status' => false, 'msg' => '文件有问题'];
            }
            // 将内容写入文件
            fwrite($file, $content);
            // 关闭文件
            fclose($file);
            return ['status' => true, 'file' => $filePath];
        } catch (\Exception $e) {
            return ['status' => false, 'msg' => '系统错误'];
        }
    }
    public function isFind($xkey)
    {
        $file = new \app\word\model\File();
        return $file->isFind(AdminService::getUserId(), $xkey);
    }
}
