<?php

declare(strict_types=1);

namespace app\word\service;

use PhpOffice\PhpSpreadsheet\IOFactory;
use Elastic\Elasticsearch\ClientBuilder;
use PhpOffice\PhpWord\IOFactory as WordIOFactory;
use Spatie\PdfToText\Pdf;
use think\admin\model\SystemFile;
use think\admin\service\AdminService;

class ElasticService
{
    public $client = null;
    public $type = ['txt', 'xlsx', 'doc', 'docx'];
    public static function make(...$parameters)
    {
        return new static(...$parameters);
    }
    public function __construct()
    {
        $this->client = ClientBuilder::create()
            ->setHosts(["http://elasticsearch:9200"])
            ->setBasicAuthentication('elastic', "123456")
            ->build();
    }
    public function infos()
    {
        return $this->client->info();
    }
    public function getPdf($path)
    {
        return Pdf::getText(app()->getRootPath() . '/public/upload/' . $path);
    }
    public function getTxt($path)
    {
        return file_get_contents(app()->getRootPath() . '/public/upload/' . $path);
    }
    public function getDoc($path)
    {
        $phpWord = \PhpOffice\PhpWord\IOFactory::load(app()->getRootPath() . '/public/upload/' . $path);
        $content = "";
        foreach ($phpWord->getSections() as $section) {
            $elements = $section->getElements();
            foreach ($elements as $element) {
                if (method_exists($element, 'getText')) {
                    $content .= $element->getText() . "\n";
                }
            }
        }

        return $content;
    }
    public function fileList($uuid)
    {
        $result = SystemFile::mk()->where(['uuid' => $uuid])->select()->toArray();
        return $result;
    }
    // ES文件内容存储限制文件【doc、docx、pdf，execl】
    public function esInsert($body, $content)
    {
        $body['content'] = $content;
        $data = [
            'index' => 'contents',
            'id' => $body['id'],
            'body' => $body
        ];
        return $this->client->index($data);
    }
    public function esSearch()
    {
        $page = request()->get('page', 1);
        $limit = request()->get('limit', 10);
        $searchParams = [
            'index' => 'contents',
            'body' => [
                // '_source' => ['content'], 
                'query' => [
                    'bool' => [
                        'must' => $this->spliceWhere()
                    ]
                ]
            ],
            'size' => $limit,
            'from' => ($page - 1) * $limit,
        ];
        $data = $this->client->search($searchParams)->asArray();
        if ($data['hits']['total']['value'] == 0) {
            $result = [];
        } else {
            foreach ($data['hits']['hits'] as $index => $item) {
                $result[$index] = $item['_source'];
            }
        }
        return json(['msg' => '', 'code' => 0, 'count' => $data['hits']['total']['value'], 'data' => $result]);
    }
    public function spliceWhere()
    {
        $where = [
            [
                'term' => [
                    'uuid' => AdminService::getUserId()
                ]
            ]
        ];
        $name = request()->get('name');
        if (!empty($name)) {
            $where[] = [
                'match' => [
                    'content' => $name
                ]
            ];
        }
        return $where;
    }
    // 创建 分词器
    public function esCreateIk()
    {
        if ($this->client->indices()->exists(
            ['index' => 'contents']
        )->asBool()) {
            return true;
        }
        $params = [
            'index' => 'contents',
            'body' => [
                'settings' => [
                    'analysis' => [
                        'analyzer' => [
                            'my_analyzer' => [
                                'type' => 'custom',
                                'tokenizer' => 'ik_max_word',
                                'filter' => ['lowercase']
                            ]
                        ]
                    ]
                ],
                'mappings' => [
                    'properties' => [
                        'uid' => [
                            'type' => 'keyword'
                        ],
                        'content' => [
                            'type' => 'text',
                            'analyzer' => 'my_analyzer'
                        ]
                    ]
                ]
            ]
        ];

        return $this->client->indices()->create($params);
    }
    public function getExecl($path)
    {
        $reader = IOFactory::createReader('Xlsx');
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load(app()->getRootPath() . '/public/upload/' . $path);
        // 获取所有工作表
        $sheetCount = $spreadsheet->getSheetCount();
        // 遍历每个工作表
        $string = '';
        for ($sheetIndex = 0; $sheetIndex < $sheetCount; $sheetIndex++) {
            $worksheet = $spreadsheet->getSheet($sheetIndex);
            // echo "Sheet " . ($sheetIndex + 1) . ": " . $worksheet->getTitle() . "\n";
            $string .= $worksheet->getTitle();
            // 获取工作表的行数和列数
            $highestRow = $worksheet->getHighestRow();
            $highestColumn = $worksheet->getHighestColumn();
            // 遍历行和列，直接获取单元格数据
            for ($row = 1; $row <= $highestRow; $row++) {
                for ($col = 'A'; $col <= $highestColumn; $col++) {
                    $cellValue = $worksheet->getCell($col . $row)->getValue();
                    // echo $cellValue . "\t";
                    $string .= $cellValue . "\t";
                }
                // echo "\n";
                $string .= "\n";
            }
            // echo "\n";
            $string .= "\n";
        }
        return $string;
    }
}
