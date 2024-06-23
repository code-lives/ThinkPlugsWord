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
        $response = $this->client->info();
        echo "<pre>";
        print_R($response);
    }
    public function addData()
    {
        $this->esSearch();
        $path = "c6/3c31530edef5cedfec03bcb688d3c1.xlsx";
        echo $this->esCreateIk($path);
        die;
    }
    public function getPdf($path)
    {
        return Pdf::getText(app()->getRootPath() . '/public/upload/' . $path);
    }
    public function getDoc($path)
    {
        sleep(1);
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
    public function allInsert($data)
    {
        try {
            return $this->client->bulk($data);
        } catch (\Exception $e) {
            // 记录异常日志
            error_log('Error during bulk insert: ' . $e->getMessage());
        }
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
        // 查询文档并仅返回 content 字段
        $searchParams = [
            'index' => 'contents',
            'body' => [
                // '_source' => ['content'], 
                'query' => [
                    'bool' => [
                        'must' => $this->spliceWhere()
                    ]
                ], 'sort' => ['id' => ['order' => 'desc']]
            ],
            'size' => $limit,
            'from' => ($page - 1) * $limit,
        ];
        $data = $this->client->search($searchParams)->asArray();
        $count = 0;
        if ($data['hits']['total']['value'] == 0) {
            $result = [];
        } else {
            foreach ($data['hits']['hits'] as $index => $item) {
                $result[$index] = $item['_source'];
            }
            $count = $data['hits']['total']['value'];
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
        exit;
        // execl 读取太大，还在考虑中
        // $spreadsheet = IOFactory::load('../public/upload/' . $path);
        // // 获取所有工作表
        // $sheetCount = $spreadsheet->getSheetCount();

        // for ($sheetIndex = 0; $sheetIndex < $sheetCount; $sheetIndex++) {
        //     $worksheet = $spreadsheet->getSheet($sheetIndex);
        //     echo "Sheet " . ($sheetIndex + 1) . ": " . $worksheet->getTitle() . "\n";

        //     // 遍历行和列，输出每个单元格的内容
        //     foreach ($worksheet->getRowIterator() as $row) {
        //         $cellIterator = $row->getCellIterator();
        //         $cellIterator->setIterateOnlyExistingCells(false); // 遍历所有单元格，包括空单元格
        //         foreach ($cellIterator as $cell) {
        //             echo $cell->getValue() . "\t";
        //         }
        //         echo "\n";
        //     }
        //     echo "\n";
        // }
    }
}
