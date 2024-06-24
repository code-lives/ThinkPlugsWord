# ThinkPlugsWord

针对系统文件的上传后的文件，进行 ES 分词搜索。

> 目前支持搜索 doc、docx、pdf txt 的内容。

> execl 和其他目前只支持搜索名字。因为读取 execl 内容太大 容易内存溢出，正在其他方案。

# 通过 Composer 安装

```php
composer require code-lives/think-plugs-word
```

# 环境需要支持

- libreoffice
- zip
- imagemagick
- unoconv
- fonts-arphic-uming
- pandoc
- poppler-utils
- texlive
- texlive-xetex

# 包含功能说明

1. 文件转为 PDF
2. 如编号打印 A4 纸 1- N
3. ES 搜索
4. 下载文件（原系统有的不支持下载）
