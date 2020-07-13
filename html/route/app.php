<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\facade\Route;

Route::get('think', function () {
    return 'hello,ThinkPHP6!';
});
Route::get('hello/:name', 'index/hello');
Route::rule('edit/file', 'edit/index');
Route::rule('action/getInfo', 'ftp/getFileInfo');
Route::rule('action/save', 'ftp/actionSaveFile');
Route::rule('action/delete', 'ftp/actionDeleteFileOrDir');
Route::rule('action/rename', 'ftp/actionRename');
Route::rule('action/login/out', 'ftp/loginOutFtp');
Route::rule('action/create', 'ftp/actionCreateFileOrDir');
Route::rule('action/up_file', 'ftp/actionUploadFile');
Route::rule('action/download', 'ftp/actionDownFile');

