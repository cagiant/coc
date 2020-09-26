<?php


namespace coc\controller;


use coc\services\MiniProgramService;

class MiniProgramController
{
    public $service;

    public function __construct()
    {
        $this->service = new MiniProgramService();
    }

    public function getMsgCallback() {
        if ($this->service->checkSignature()) {
            echo $_GET["echostr"];
            exit();
        }
    }

    public function postMsgCallback() {
        echo 'success';
    }
}