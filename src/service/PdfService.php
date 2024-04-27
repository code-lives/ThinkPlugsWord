<?php

declare(strict_types=1);

namespace app\word\service;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Lukasss93\PdfToPpm\PdfToPpm;

class PdfService
{
    public static function make(...$parameters)
    {
        return new static(...$parameters);
    }
    /**
     * Unonconv 把docx、doc文档转为pdf
     * @param [type] $path 路径
     * @param [type] $fileNmae 接收文件名
     * @param [type] $extensionName 接收的文件后缀类型
     * @param string $type 转换类型
     * @return void
     */
    public function convertPdfToImages($path, $fileNmae, $extensionName, $type = "pdf")
    {
        // 构建将 PDF 转为图片的命令
        $command = "unoconv -f " . $type . ' ' . $path . $fileNmae;
        // 执行命令
        $process = new Process(explode(' ', $command));
        $process->run();
        // 检查是否执行成功
        if (!$process->isSuccessful()) {
            // return false;
            throw new ProcessFailedException($process);
        }
        return true;
    }
    /**
     * Pdf转为Png
     *
     * @param [type] $path
     * @param [type] $pathToWhereImageShouldBeStored
     * @param string $prefix
     * @return void
     */
    public function PdfToImages($path, $pathToWhereImageShouldBeStored, $prefix = '')
    {
        $pdf = PdfToPpm::create()->setPdf($path);
        $pdf->setOutputFormat('png')->saveAllPagesAsImages($pathToWhereImageShouldBeStored, $prefix);
        $page = $pdf->getNumberOfPages();
        return $page;
    }
}
