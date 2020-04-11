<?php


namespace coc\controller;


use coc\utils\Response;

class WelcomeController
{
    public function index()
    {
        Response::displayPage(ROOT . DIRECTORY_SEPARATOR . "frontend" . DIRECTORY_SEPARATOR . "index.html");
    }
}