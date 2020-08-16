<?php


namespace coc\services;


class AuthService implements iAuth
{
    public function checkSignature($params)
    {
        return true;
    }
}