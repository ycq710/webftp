<?php
namespace app\controller;

use app\BaseController;
use think\Facade\View;

class Index extends BaseController
{
    public function index()
    {

       return \view();
    }

    public function demo()
    {
        return View::fetch('../public/static/demo/cn/index.html');
    }
}
