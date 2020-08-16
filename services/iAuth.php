<?php


namespace coc\services;


interface iAuth
{
    public function checkSignature($params);
}