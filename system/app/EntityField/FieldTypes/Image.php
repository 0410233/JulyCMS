<?php

namespace App\EntityField\FieldTypes;

class Image extends File
{
    /**
     * 类型标志，由小写字符+数字+下划线组成
     *
     * @var string
     */
    protected $handle = 'image';

    /**
     * 字段类型标签
     *
     * @var string
     */
    protected $label = '图片';

    /**
     * 字段类型描述
     *
     * @var string|null
     */
    protected $description = '用于保存图片名（含路径），带文件浏览按钮';
}
