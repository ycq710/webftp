<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/7/7
 * Time: 15:31
 */
namespace app\controller;
use app\BaseController;

class Login extends BaseController{

    public function index()
    {
        $ftp = new \FtpClient\FtpClient();
        $ftp->connect($this->app->request->post('ip'),false,221);
        $ftp->login($this->app->request->post('username'),$this->app->request->post('password'));
    }
}