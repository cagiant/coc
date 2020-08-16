<?php


namespace coc\services;


use coc\utils\CocException;

class ProxyParamters
{
    public $option;

    public $tag;

    public function __construct($postData)
    {
        $tag = $postData['tag'] ?? '';
        $option = $postData['option'] ?? '';
        if (empty($tag) || empty($option)) {
            throw new CocException("tag 和option为必填");
        }
        $this->tag = $tag;
        $this->option = $option;
    }
}