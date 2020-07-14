<?php
namespace app\controller;

use app\BaseController;
use think\App;
use think\facade\View;
use think\facade\Session;

class Ftp extends BaseController{

    /**
     *登录成功后，ftp列表信息
     */
    public function index()
    {
        $dir =  isset($_REQUEST['dir'])?$_REQUEST['dir']:'.';
        $ftp_info = (array)Session::get('ftp_info');
        if($ftp_info){
            $ftp =$this->ftp_return($ftp_info['ip'],$ftp_info['port'],$ftp_info['username'],$ftp_info['password']);
            if(!$ftp->isDir($dir)){
                $this->resError();
            }
            $files = $ftp->parseRawList($ftp->rawlist($dir));
        }else{
            $ip = isset($_REQUEST['ip'])?$_REQUEST['ip']:'';
            $port = isset($_REQUEST['port'])?$_REQUEST['port']:'';
            $username = isset($_REQUEST['username'])?$_REQUEST['username']:'';
            $password = isset($_REQUEST['password'])?$_REQUEST['password']:'';
            if(empty($ip) || empty($port) ||empty($username) ||empty($password) ){
                $this->resError();
            }
            $ftp = $this->ftp_return($ip,$port,$username,$password);
            $files = $ftp->parseRawList($ftp->rawlist($dir));
            Session::set('ftp_info', ['ip' => $ip, 'port' => $port, 'username' => $username, 'password' => $password]);
        }
        View::assign('files', $files);
        return view();

    }

    /**
     * 得到服务器文件信息
     */
    public function getFileInfo(){
        $file_name = isset($_REQUEST['file_name'])?$_REQUEST['file_name']:'';
        $dir = isset($_REQUEST['dir'])?$_REQUEST['dir']:'';
        if(empty($file_name)){
            $this->resError('json','文件名有误');
        }
        if(!empty($dir)){
            $file_name = $dir.'/'.$file_name;
        }
        $ftp = $this->ftp_session_login();
        $is_file = $ftp->ftp_size($file_name);
        if($is_file == '-1'){
            $this->resError('json','文件不存在');
        }
        if(checkIsCanEdit(getExt($file_name)) == '0'){
            $this->resError('json','该文件不支持编辑');
        }

        $file_info = $ftp->getContent($file_name);
        $file_info_to_utf8 = $this->characet($file_info);
        $this->resSuccess('json','文件信息获取成功',$file_info_to_utf8);
    }

    /**
     * 编辑文件信息、保存到ftp
     */
    public function actionSaveFile(){
        $save_info = isset($_REQUEST['save_info'])?$_REQUEST['save_info']:'';
        $file_info_to_utf8 = $this->characet($save_info);
        $ftp =$this->ftp_session_login();
        $res = $ftp->putFromString($this->getRealFilePath(),$file_info_to_utf8);
        if($res){
            $this->resSuccess('json','文件已保存!');
        }else{
            $this->resError('json','网络错误，请稍后重试!');
        }
    }

    /**
     * 删除文件 or 删除目录
     */
    public function actionDeleteFileOrDir(){
        $ftp =$this->ftp_session_login();
        $res = $ftp->remove($this->getRealFilePath());
        if($res){
            $this->resSuccess('json','删除成功!');
        }else{
            $this->resError('json','指定删除对象不存在');
        }
    }

    /**
     * 创建文件 or 目录
     */
    public function actionCreateFileOrDir()
    {
        $type = isset($_REQUEST['type'])?$_REQUEST['type']:'';
        $name = isset($_REQUEST['create_name'])?$_REQUEST['create_name']:'';

        $dir = isset($_REQUEST['dir'])?$_REQUEST['dir']:'';
        if($type== '' || $name==''){
            $this->resError('json','名称不能为空');
        }

        if(!empty($dir)){
            $name = $dir.'/'.$name;
        }

        $ftp = $this->ftp_session_login();
        if($type == 'dir'){
            $res =  $ftp->mkdir($name);
            if($res){
                $this->resSuccess('json','成功!');
            }else{
                $this->resError('json','失败');
            }
        }else{
            $ftp_info = (array)Session::get('ftp_info');
            $tmp_dir = "create_tmp_file/".$ftp_info['ip'].'/'.date('Y_m_d_H.i.s',time()).'/';
            $del_dir = 'create_tmp_file/'.$ftp_info['ip'].'/';
            if (!is_dir($tmp_dir)){
                mkdir($tmp_dir, 0777,true);
            };

            $name_file = isset($_REQUEST['create_name'])?$_REQUEST['create_name']:'';//新的文件名
            $is_can_create = checkIsCanCreate(getExt($name_file));//检查用户输入的信息，是否包含可新建文件后缀
            if(!$is_can_create){
                $this->resError('json','文件后缀不支持：'.getExt($name_file));
            }
            $file = $tmp_dir.$name_file;
            if(FALSE === strpos($name_file, '.')) {
                $local_path = $_SERVER['DOCUMENT_ROOT'].'/'.$file.'.txt';
                $server_path = $name.'.txt';
                $file = $tmp_dir.$name_file.'.txt';
            }else{
                $local_path = $_SERVER['DOCUMENT_ROOT'].'/'.$file;
                $server_path = $name;
            }
            $fp= fopen($file,'w+');
            fclose($fp); //关闭指针
            $ftp = $this->ftp_session_login();
            $ftp->ftp_put($local_path,$server_path);
            delDirAndFile($del_dir,true);
            $this->resSuccess('json','文件创建成功!');
        }
    }

    /**
     *上传文件
     */
    public function actionUploadFile(){
        $ip = (array)Session::get('ftp_info');
        $del_dir = 'upload_tmp/'.$ip['ip'].'/';
        $up_file = $this->up_file();
        $ftp = $this->ftp_session_login();
        $dir = isset($_REQUEST['dir'])?$_REQUEST['dir']:'';
        foreach ($up_file as $res){
            if(!empty($dir)){
                $server_file = $res['name'] = $dir.'/'.$res['name'];
            }else{
                $server_file = $res['name'];
            }
            $ftp->ftp_put($res['url'],$server_file);
        }
        delDirAndFile($del_dir,true);
        $this->resSuccess('json','文件上传成功!');
    }

    /**
     *下载文件
     */
    public function actionDownFile()
    {
        $ftp_info = (array)Session::get('ftp_info');
        $file_name = isset($_REQUEST['file_name'])?$_REQUEST['file_name']:'';
        if(empty($file_name)){
            $this->resError('json','文件名有误！');
        }

        $dir = isset($_REQUEST['dir'])?$_REQUEST['dir']:'';
        if(!empty($dir)){
            $file_name = '/'.$dir.'/'.$file_name;
        }else{
            $file_name = '/'.$file_name;
        }
        $d_ulr = 'ftp://'.$ftp_info['username'].':'.$ftp_info['password'].'@'.$ftp_info['ip'].':'.$ftp_info['port'].$file_name;
        $this->resSuccess('json','获取下载地址成功!',$d_ulr);
    }

    /**
     * 重命名
     */
    public function actionRename()
    {
        $new_name = $_REQUEST['new_name'];
        $dir = isset($_REQUEST['dir'])?$_REQUEST['dir']:'';
        if(!empty($dir)){
            $new_name = $dir.'/'.$new_name;
        }
        $ftp = $this->ftp_session_login();
        $res = $ftp->re_name($this->getRealFilePath(),$new_name);
        if($res){
            $this->resSuccess('json','重命名成功!');
        }else{
            $this->resError('json','重命名失败');
        }
    }

    /**
     * loginOut退出
     */
    public function loginOutFtp(){
        $this->ftp_session_login()->close();//关闭FTP
        Session(null);//清除session
        return redirect('/');
    }

    /**
     * Login FTP
     */
    protected function ftp_return($ip,$port,$username,$password){
        $ftp = new FtpClient();
        $ftp->connect($ip,false,$port);
        $ftp->login($username,$password);
        return $ftp;
    }

    /**
     *通过session信息，连接FTP
     */
    protected function ftp_session_login(){
        $ftp_info = (array)Session::get('ftp_info');
        if(!$ftp_info){
            $this->resError();
        }
        $ftp = new FtpClient();
        $ftp->connect($ftp_info['ip'],false,$ftp_info['port']);
        $ftp->login($ftp_info['username'],$ftp_info['password']);
        return $ftp;
    }

    /**
     * 错误返回
     * @param $type $info $data
     * @return string
     */
    public function resError($type = 'text',$info = '404 Bad',$data = ''){
        if($type == 'json'){
            exit(json_encode(array('code'=>2,'info'=>$info,'data'=>$data),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
        }
        exit('404 Bad');
    }

    /**
     * 成功返回
     * @param $type $info $data
     * @return string
     */
    public function resSuccess($type = 'text',$info = '200 OK',$data = ''){
        if($type == 'json'){
            exit(json_encode(array('code'=>1,'info'=>$info,'data'=>$data),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
        }
        exit('200 OK');
    }

    /**
     * 编码转UTF8
     * */
    public function characet($data){
        if( !empty($data) ){
            $fileType = mb_detect_encoding($data , array('UTF-8','GBK','ASCII','GB2312')) ;
//            if( $fileType != 'UTF-8'){
            $data = mb_convert_encoding($data ,'utf-8' , $fileType);
//            }
        }
        return $data;
    }

    /**
     * 得到真实文件信息（路径+名称）
     */
    protected function getRealFilePath(){
        $file_name = isset($_REQUEST['file_name'])?$_REQUEST['file_name']:'';
        if(empty($file_name)){
            $this->resError('json','文件名有误');
        }

        $dir = isset($_REQUEST['dir'])?$_REQUEST['dir']:'';
        if(!empty($dir)){
            $file_name = $dir.'/'.$file_name;
        }
        return $file_name;
    }

    /*在本地服务器创建临时文件*/
    protected function up_file(){
        foreach ($_FILES["up_file_name"]["name"] as $key => $tempName){
            $name =  $_FILES["up_file_name"]["name"][$key];
            $is_can_up = checkIsCanUp(getExt($name));
            if(!$is_can_up){
                $this->resError('json','包含不可上传文件类型：'.getExt($name).'');
            }
        }

        $ftp_info = (array)Session::get('ftp_info');
        $dir = "upload_tmp/".$ftp_info['ip'].'/'.date('Y_m_d_H.i.s',time()).'/';
        if (!is_dir($dir)){
            mkdir($dir, 0777,true);
        };
        $data = array();
        foreach ($_FILES["up_file_name"]["name"] as $key => $tempName){
            $name =  $_FILES["up_file_name"]["name"][$key];
            $tmp = $_FILES['up_file_name']['tmp_name'][$key];
            move_uploaded_file($tmp, $dir . $name);
            $data[]=array(
                'url'=>$_SERVER['DOCUMENT_ROOT'].'/'.$dir.$name,
                'name'=>$name
            );
        }
      return  $data;

    }

}
