<?php
// 应用公共文件
function file_size($size) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    for ($i = 0; $size >= 1024 && $i < 4; $i++) $size /= 1024;
    return round($size, 2).$units[$i];
}

/**
 * 键查文件是否可以编辑，存在? not_edit : yes_edit
 * @param $file_type，文件类型
 * @return string
 */
function checkIsCanEdit($file_type){
    $arr = [
        "png",
        "jpg",
        "jpeg",
        "gif",
        "ttf",
        'zip',
        'oexe',
        'exe',
        'rar',
        '7z'
    ];
    $is_in = in_array($file_type,$arr);
    if($is_in){
        return '0';//存在，不可编辑
    }
    return '1';
}

/**
 * 自定义不可上传文件类型
 * @param $file_type，文件类型
 * @return int
 */
function checkIsCanUp($file_type){
    $arr = [
        "exe"
    ];
    $is_in = in_array($file_type,$arr);
    if($is_in){
        return false;//不可上传
    }
    return true;
}

/**
 * 自定义可新建文件类型
 * @param $file_type，文件类型
 * @return int
 */
function checkIsCanCreate($file_type){
    $arr = [
        "text",
        "txt",
        "php",
        'xml',
        'html',
        'css',
        'js',
        'ini'
    ];
    $is_in = in_array($file_type,$arr);
    if($is_in){
        return true;//可创建的
    }
    return false;
}

/**
 * 从文件名中获取后缀扩展
 *
 * @access  private
 * @param   string  目录标识
 * @return  string
 */
function getExt($filename) {
    if(FALSE === strpos($filename, '.')) {
        return 'txt';
    }
    $ext_arr = explode('.', $filename);
    return end($ext_arr);
}

/**
 * 删除目录及目录下所有文件或删除指定文件
 * @param str $path 待删除目录路径
 * @param int $delDir 是否删除目录，1或true删除目录，0或false则只删除文件保留目录（包含子目录）
 * @return bool 返回删除状态
 */
function delDirAndFile($path, $delDir = false)
{
    if (is_array($path)) {
        foreach ($path as $subPath) {
            delDirAndFile($subPath, $delDir);
        }
    }
    if (is_dir($path)) {
        $handle = opendir($path);
        if ($handle) {
            while (false !== ($item = readdir($handle))) {
                if ($item != "." && $item != "..") {
                    is_dir("$path/$item") ? delDirAndFile("$path/$item", $delDir) : unlink("$path/$item");
                }
            }
            closedir($handle);
            if ($delDir) {
                return rmdir($path);
            }
        }
    } else {
        if (file_exists($path)) {
            return unlink($path);
        } else {
            return false;
        }
    }
}