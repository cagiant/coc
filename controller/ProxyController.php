<?php


namespace coc\controller;


use coc\services\AuthService;
use coc\services\HttpClient;
use coc\services\ProxyParamters;
use coc\services\ProxyService;
use coc\utils\Response;

class ProxyController
{
    public function index()
    {
        return Response::jsonRes((new ProxyService(new HttpClient(), new AuthService(), new ProxyParamters($_POST)))->doProxy());
    }
}