<?php

declare(strict_types=1);

namespace app\word\service;

use PhpOffice\PhpSpreadsheet\IOFactory;

class ExeclService
{
    public static function make(...$parameters)
    {
        return new static(...$parameters);
    }

    public function readExecl($pathfile)
    {
        $spreadsheet = IOFactory::load($pathfile);
        $worksheet = $spreadsheet->getActiveSheet();
        // 读取单元格内容，并存储到数组中  
        $data = [];
        foreach ($worksheet->getRowIterator() as $row) {
            $rowData = [];
            foreach ($row->getCellIterator() as $cell) {
                $rowData[] = $cell->getValue();
            }
            $data[] = $rowData;
        }
        return $data;
    }
}
