<?php
/*
 * This file is part of the `nicolab/php-ftp-client` package.
 *
 * (c) Nicolas Tallefourtane <dev@nicolab.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright Nicolas Tallefourtane http://nicolab.net
 */
namespace app\controller;

use \Countable;

/**
 * The FTP and SSL-FTP client for PHP.
 *
 * @method bool alloc(int $filesize, string &$result = null) 为要上传的文件分配空间
 * @method bool cdup() 父目录的更改
 * @method bool chdir(string $directory) 更改FTP服务器上的当前目录
 * @method int chmod(int $mode, string $filename) 通过FTP设置文件权限
 * @method bool delete(string $path) 删除FTP服务器上的文件
 * @method bool exec(string $command) 请求在FTP服务器上执行命令
 * @method bool fget(resource $handle, string $remote_file, int $mode, int $resumepos = 0) 从FTP服务器下载文件并保存到打开的文件
 * @method bool fput(string $remote_file, resource $handle, int $mode, int $startpos = 0) 从打开的文件上传到FTP服务器
 * @method mixed get_option(int $option) 检索当前FTP流的各种运行时行为
 * @method bool get(string $local_file, string $remote_file, int $mode, int $resumepos = 0) 从FTP服务器下载文件
 * @method int mdtm(string $remote_file) 返回给定文件的上次修改时间
 * @method array mlsd(string $remote_dir) 返回给定目录中的文件列表
 * @method int nb_continue() 继续检索/发送文件（非阻塞）
 * @method int nb_fget(resource $handle, string $remote_file, int $mode, int $resumepos = 0) 从FTP服务器检索文件并将其写入打开的文件（非阻塞）
 * @method int nb_fput(string $remote_file, resource $handle, int $mode, int $startpos = 0) 将文件从打开的文件存储到FTP服务器（非阻塞）
 * @method int nb_get(string $local_file, string $remote_file, int $mode, int $resumepos = 0) 从FTP服务器检索文件并将其写入本地文件（非阻塞）
 * @method int nb_put(string $remote_file, string $local_file, int $mode, int $startpos = 0)在FTP服务器上存储文件（非阻塞）
 * @method bool pasv(bool $pasv) 打开或关闭被动模式
 * @method bool put(string $remote_file, string $local_file, int $mode, int $startpos = 0) 将文件上传到FTP服务器
 * @method string pwd() 返回当前目录名
 * @method bool quit() 关闭FTP连接
 * @method array raw(string $command) 向FTP服务器发送任意命令
 * @method bool rename(string $oldname, string $newname) 重命名FTP服务器上的文件或目录
 * @method bool set_option(int $option, mixed $value)其他FTP运行时选项集
 * @method bool site(string $command) 向服务器发送站点命令
 * @method int size(string $remote_file) 返回给定文件的大小
 * @method string systype() 返回远程FTP服务器的系统类型标识符
 *
 * @author Nicolas Tallefourtane <dev@nicolab.net>
 */
class FtpClient implements Countable
{
    /**
     * 与服务器的连接。
     *
     * @var resource
     */
    protected $conn;

    /**
     * PHP FTP函数包装器。
     *
     * @var FtpWrapper
     */
    private $ftp;

    /**
     * Constructor.
     *
     * @param  resource|null $connection
     * @throws FtpException  如果未加载FTP扩展名。
     */
    public function __construct($connection = null)
    {
        if (!extension_loaded('ftp')) {
            throw new FtpException('FTP扩展未加载！');
        }

        if ($connection) {
            $this->conn = $connection;
        }

        $this->setWrapper(new FtpWrapper($this->conn));
    }


    /**
     * 当对象被破坏时关闭连接。
     */
    public function _close()
    {
        if ($this->conn) {
            $this->ftp->close();
        }
    }

    /**
     * 调用由包装器处理的内部方法或FTP方法。
     *
     * 将要调用的FTP PHP函数包装为FtpClient对象的方法。
     * 连接会自动传递给FTP PHP函数。
     *
     * @param  string       $method
     * @param  array        $arguments
     * @return mixed
     * @throws FtpException 当函数无效时
     */
    public function __call($method, array $arguments)
    {
        return $this->ftp->__call($method, $arguments);
    }

    /**
     * 覆盖PHP限制
     *
     * @param  string|null $memory           如果未修改null，则为内存限制
     * @param  int         $time_limit        最大执行时间，默认无限制
     * @param  bool        $ignore_user_abort 忽略用户中止，默认为true
     * @return FtpClient
     */
    public function setPhpLimit($memory = null, $time_limit = 0, $ignore_user_abort = true)
    {
        if (null !== $memory) {
            ini_set('memory_limit', $memory);
        }

        ignore_user_abort($ignore_user_abort);
        set_time_limit($time_limit);

        return $this;
    }

    /**
     * 获取远程FTP服务器的帮助信息。
     *
     * @return array
     */
    public function help()
    {
        return $this->ftp->raw('help');
    }

    /**
     * 打开FTP连接。
     *
     * @param string $host
     * @param bool   $ssl
     * @param int    $port
     * @param int    $timeout
     *
     * @return FtpClient
     * @throws FtpException If unable to connect
     */
    public function connect($host, $ssl = false, $port = 21, $timeout = 90)
    {
        if ($ssl) {
            $this->conn = $this->ftp->ssl_connect($host, $port, $timeout);
        } else {
            $this->conn = $this->ftp->connect($host, $port, $timeout);
        }

        if (!$this->conn) {
            throw new FtpException('无法连接');
        }

        return $this;
    }

    /**
     * 关闭当前FTP连接。
     */
    public function close()
    {
        if ($this->conn) {
            $this->ftp->close();
            $this->conn = null;
        }
    }

    /**
     * 获得与服务器的连接。
     *
     * @return resource
     */
    public function getConnection()
    {
        return $this->conn;
    }

    /**
     * Get the wrapper.
     *
     * @return FtpWrapper
     */
    public function getWrapper()
    {
        return $this->ftp;
    }

    /**
     * Logs in to an FTP connection.
     *
     * @param string $username
     * @param string $password
     *
     * @return FtpClient
     * @throws FtpException If the login is incorrect
     */
    public function login($username = 'anonymous', $password = '')
    {
        $result = $this->ftp->login($username, $password);

        if ($result === false) {
            throw new FtpException('Login incorrect');
        }



        return $this;
    }

    /**
     * Returns the last modified time of the given file.
     * Return -1 on error
     *
     * @param string $remoteFile
     * @param string|null $format
     *
     * @return int
     */
    public function modifiedTime($remoteFile, $format = null)
    {
        $time = $this->ftp->mdtm($remoteFile);

        if ($time !== -1 && $format !== null) {
            return date($format, $time);
        }

        return $time;
    }

    /**
     * Changes to the parent directory.
     *
     * @throws FtpException
     * @return FtpClient
     */
    public function up()
    {
        $result = $this->ftp->cdup();

        if ($result === false) {
            throw new FtpException('Unable to get parent folder');
        }

        return $this;
    }

    /**
     * 返回给定目录中的文件列表。
     *
     * @param string   $directory 默认为“.”当前目录
     * @param bool     $recursive 递归
     * @param callable $filter    一个可调用的过滤结果，默认为asort（）PHP函数。
     *                            结果在数组参数中传递，
     *                            必须引用这个论点！
     *                            callable应该继续引用数组
     *                            因为是几个PHP排序的行为
     *                           功能（通过引用直接确保兼容性所有PHP排序函数）。
     *
     * @return array
     * @throws FtpException 如果无法列出目录，则抛出ftpeexception
     */
    public function nlist($directory = '.', $recursive = false, $filter = 'sort')
    {
        if (!$this->isDir($directory)) {
            throw new FtpException('"'.$directory.'" 不是目录');
        }

        $files = $this->ftp->nlist($directory);

        if ($files === false) {
            throw new FtpException('无法列出目录');
        }

        $result  = array();
        $dir_len = strlen($directory);

        // if it's the current
        if (false !== ($kdot = array_search('.', $files))) {
            unset($files[$kdot]);
        }

        // if it's the parent
        if(false !== ($kdot = array_search('..', $files))) {
            unset($files[$kdot]);
        }

        if (!$recursive) {
            $result = $files;

            // working with the reference (behavior of several PHP sorting functions)
            $filter($result);

            return $result;
        }

        // utils for recursion
        $flatten = function (array $arr) use (&$flatten) {
            $flat = [];

            foreach ($arr as $k => $v) {
                if (is_array($v)) {
                    $flat = array_merge($flat, $flatten($v));
                } else {
                    $flat[] = $v;
                }
            }

            return $flat;
        };

        foreach ($files as $file) {
            $file = $directory.'/'.$file;

            // if contains the root path (behavior of the recursivity)
            if (0 === strpos($file, $directory, $dir_len)) {
                $file = substr($file, $dir_len);
            }

            if ($this->isDir($file)) {
                $result[] = $file;
                $items    = $flatten($this->nlist($file, true, $filter));

                foreach ($items as $item) {
                    $result[] = $item;
                }

            } else {
                $result[] = $file;
            }
        }

        $result = array_unique($result);
        $filter($result);

        return $result;
    }

    /**
     * 创建目录
     *
     * @see FtpClient::rmdir()
     * @see FtpClient::remove()
     * @see FtpClient::put()
     * @see FtpClient::putAll()
     *
     * @param  string $directory The directory
     * @param  bool   $recursive
     * @return array
     **/
    public function mkdir($directory, $recursive = false)
    {
        if (!$recursive or $this->isDir($directory)) {
            return $this->ftp->mkdir($directory);
        }

        $result = false;
        $pwd    = $this->ftp->pwd();
        $parts  = explode('/', $directory);

        foreach ($parts as $part) {
            if ($part == '') {
                continue;
            }

            if (!@$this->ftp->chdir($part)) {
                $result = $this->ftp->mkdir($part);
                $this->ftp->chdir($part);
            }
        }

        $this->ftp->chdir($pwd);

        return $result;
    }

    /**
     * Remove a directory.
     *
     * @see FtpClient::mkdir()
     * @see FtpClient::cleanDir()
     * @see FtpClient::remove()
     * @see FtpClient::delete()
     * @param  string       $directory
     * @param  bool         $recursive 如果目录不为空，则强制删除
     * @return bool
     * @throws FtpException 如果无法列出要删除的目录
     */
    public function rmdir($directory, $recursive = true)
    {
        if ($recursive) {
            $files = $this->nlist($directory, false, 'rsort');

            // remove children
            foreach ($files as $file) {
                $this->remove($file, true);
            }
        }

        // remove the directory
        return $this->ftp->rmdir($directory);
    }

    /**
     * 空目录。
     *
     * @see FtpClient::remove()
     * @see FtpClient::delete()
     * @see FtpClient::rmdir()
     *
     * @param  string $directory
     * @return bool
     */
    public function cleanDir($directory)
    {
        if (!$files = $this->nlist($directory)) {
            return $this->isEmpty($directory);
        }

        // remove children
        foreach ($files as $file) {
            $this->remove($file, true);
        }

        return $this->isEmpty($directory);
    }

    /**
     * 从文件名中获取后缀扩展
     *
     * @access  private
     * @param   string  目录标识
     * @return  string
     */
    public function _getext($filename) {
        if(FALSE === strpos($filename, '.')) {
            return 'txt';
        }

        $extarr = explode('.', $filename);
        return end($extarr);
    }

    /**
     * 删除文件或目录。
     *
     * @see FtpClient::rmdir()
     * @see FtpClient::cleanDir()
     * @see FtpClient::delete()
     * @param  string $path      要删除的文件或目录的路径
     * @param  bool   $recursive 仅当$path是目录时才有效，{@see FtpClient:：rmdir（）}
     * @return bool
     */
    public function remove($path, $recursive = false)
    {
        if ($path == '.' || $path == '..') {
            return false;
        }

        try {
            if (@$this->ftp->delete($path)
            or ($this->isDir($path) and $this->rmdir($path, $recursive))) {
                return true;
            }

            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 检查目录是否存在。
     *
     * @param string $directory
     * @return bool
     * @throws FtpException
     */
    public function isDir($directory)
    {
        $pwd = $this->ftp->pwd();

        if ($pwd === false) {
            throw new FtpException('Unable to resolve the current directory');
        }

        if (@$this->ftp->chdir($directory)) {
            $this->ftp->chdir($pwd);
            return true;
        }

        $this->ftp->chdir($pwd);

        return false;
    }

    /**
     * 检查目录是否为空。
     *
     * @param  string $directory
     * @return bool
     */
    public function isEmpty($directory)
    {
        return $this->count($directory, null, false) === 0 ? true : false;
    }

    /**
     * 扫描目录并返回每个项目的详细信息。
     *
     * @see FtpClient::nlist()
     * @see FtpClient::rawlist()
     * @see FtpClient::parseRawList()
     * @see FtpClient::dirSize()
     * @param  string $directory
     * @param  bool   $recursive
     * @return array
     */
    public function scanDir($directory = '.', $recursive = false)
    {
        return $this->parseRawList($this->rawlist($directory, $recursive));
    }

    /**
     * 返回给定目录的总大小（以字节为单位）。
     *
     * @param  string $directory 默认情况下，目录是当前目录。
     * @param  bool   $recursive 默认为true
     * @return int    以字节为单位的大小。
     */
    public function dirSize($directory = '.', $recursive = true)
    {
        $items = $this->scanDir($directory, $recursive);
        $size  = 0;

        foreach ($items as $item) {
            $size += (int) $item['size'];
        }

        return $size;
    }

    /**
     * 计数项目（文件、目录、链接、未知）。
     *
     * @param  string      $directory 默认情况下，目录是当前目录。
     * @param  string|null $type     要计数的项目类型（文件、目录、链接、未知）
     * @param  bool        $recursive 默认为true
     * @return int
     */
    public function count($directory = '.', $type = null, $recursive = true)
    {
        $items  = (null === $type ? $this->nlist($directory, $recursive)
            : $this->scanDir($directory, $recursive));

        $count = 0;
        foreach ($items as $item) {
            if (null === $type or $item['type'] == $type) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * 将文件从FTP服务器下载到字符串中
     *
     * @param  string $remote_file
     * @param  int    $mode
     * @param  int    $resumepos
     * @return string|null
     */
    public function getContent($remote_file, $mode = FTP_BINARY, $resumepos = 0)
    {
        $handle = fopen('php://temp', 'r+');

        if ($this->fget($handle, $remote_file, $mode, $resumepos)) {
            rewind($handle);
            return stream_get_contents($handle);
        }

        return null;
    }

    /**
     * 将文件从字符串上传到服务器。
     *
     * @param  string       $remote_file
     * @param  string       $content
     * @return FtpClient
     * @throws FtpException 当传输失败时
     */
    public function putFromString($remote_file, $content)
    {
        $handle = fopen('php://temp', 'w');

        fwrite($handle, $content);
        rewind($handle);

        if ($this->ftp->fput($remote_file, $handle, FTP_BINARY)) {
            return $this;
        }

        throw new FtpException('无法放置文件"'.$remote_file.'"');
    }

    /**
     * 将文件上传到服务器。
     *
     * @param  string       $local_file
     * @return FtpClient
     * @throws FtpException 当传输失败时
     */
    public function putFromPath($local_file)
    {
        $remote_file = basename($local_file);
        $handle      = fopen($local_file, 'r');

        if ($this->ftp->fput($remote_file, $handle, FTP_BINARY)) {
            rewind($handle);
            return $this;
        }

        throw new FtpException('无法从本地文件放置远程文件 "'.$local_file.'"');
    }

    /**
     * 上传文件。
     * @param $source_directory
     * @param $target_directory
     * @param int $mode
     * @return $this
     * @throws FtpException
     **/
    public function putAll($source_directory, $target_directory, $mode = FTP_BINARY)
    {
        $d = dir($source_directory);

        // do this for each file in the directory
        while ($file = $d->read()) {

            // to prevent an infinite loop
            if ($file != "." && $file != "..") {

                // do the following if it is a directory
                if (is_dir($source_directory.'/'.$file)) {

                    if (!$this->isDir($target_directory.'/'.$file)) {

                        // create directories that do not yet exist
                        $this->ftp->mkdir($target_directory.'/'.$file);
                    }

                    // recursive part
                    $this->putAll(
                        $source_directory.'/'.$file, $target_directory.'/'.$file,
                        $mode
                    );
                } else {

                    // put the files
                    $this->ftp->put(
                        $target_directory.'/'.$file, $source_directory.'/'.$file,
                        $mode
                    );
                }
            }
        }

	$d->close();

        return $this;
    }

    /**
     * 从远程FTP目录下载所有文件
     *
     * @param  string $source_directory The remote directory
     * @param  string $target_directory The local directory
     * @param  int    $mode
     * @return FtpClient
     * @return $this
     * @throws FtpException
     */
    public function getAll($source_directory, $target_directory, $mode = FTP_BINARY)
    {
        if ($source_directory != ".") { 
            if ($this->ftp->chdir($source_directory) == false) { 
                throw new FtpException("Unable to change directory: ".$source_directory);
            }

            if (!(is_dir($source_directory))) {
                mkdir($source_directory);
	    }

            chdir($source_directory); 
        } 

        $contents = $this->ftp->nlist(".");

        foreach ($contents as $file) { 
            if ($file == '.' || $file == '..') {
                continue;
	    }

            $this->ftp->get($target_directory."/".$file, $file, $mode);
        }

        $this->ftp->chdir(".."); 
        chdir(".."); 

        return $this;
    }

    /**
     * 返回给定目录中文件的详细列表。
     *
     * @see FtpClient::nlist()
     * @see FtpClient::scanDir()
     * @see FtpClient::dirSize()
     * @param  string       $directory The directory, by default is the current directory
     * @param  bool         $recursive
     * @return array
     * @throws FtpException
     */
    public function rawlist($directory = '.', $recursive = false)
    {
        if (!$this->isDir($directory)) {
            throw new FtpException('"'.$directory.'" is not a directory.');
        }
        
        if (strpos($directory, " ") > 0) {
            $ftproot = $this->ftp->pwd();
            $this->ftp->chdir($directory);
            $list  = $this->ftp->rawlist("");
            $this->ftp->chdir($ftproot);
        } else {
            $list  = $this->ftp->rawlist($directory);
        }
        
        $items = array();

        if (!$list) {
            return $items;
        }

        if (false == $recursive) {
            foreach ($list as $path => $item) {
                $chunks = preg_split("/\s+/", $item);

                // if not "name"
                if (empty($chunks[8]) || $chunks[8] == '.' || $chunks[8] == '..') {
                    continue;
                }

                $path = $directory.'/'.$chunks[8];

                if (isset($chunks[9])) {
                    $nbChunks = count($chunks);

                    for ($i = 9; $i < $nbChunks; $i++) {
                        $path .= ' '.$chunks[$i];
                    }
                }


                if (substr($path, 0, 2) == './') {
                    $path = substr($path, 2);
                }

                $items[ $this->rawToType($item).'#'.$path ] = $item;
            }

            return $items;
        }

        $path = '';

        foreach ($list as $item) {
            $len = strlen($item);

            if (!$len

            // "."
            || ($item[$len-1] == '.' && $item[$len-2] == ' '

            // ".."
            or $item[$len-1] == '.' && $item[$len-2] == '.' && $item[$len-3] == ' ')
            ) {

                continue;
            }

            $chunks = preg_split("/\s+/", $item);

            // if not "name"
            if (empty($chunks[8]) || $chunks[8] == '.' || $chunks[8] == '..') {
                continue;
            }

            $path = $directory.'/'.$chunks[8];

            if (isset($chunks[9])) {
                $nbChunks = count($chunks);

                for ($i = 9; $i < $nbChunks; $i++) {
                    $path .= ' '.$chunks[$i];
                }
            }

            if (substr($path, 0, 2) == './') {
                $path = substr($path, 2);
            }

            $items[$this->rawToType($item).'#'.$path] = $item;

            if ($item[0] == 'd') {
                $sublist = $this->rawlist($path, true);

                foreach ($sublist as $subpath => $subitem) {
                    $items[$subpath] = $subitem;
                }
            }
        }

        return $items;
    }

    /**
     * 分析原始列表。
     *
     * @see FtpClient::rawlist()
     * @see FtpClient::scanDir()
     * @see FtpClient::dirSize()
     * @param  array $rawlist
     * @return array
     */
    public function parseRawList(array $rawlist)
    {
        $items = array();
        $path  = '';

        foreach ($rawlist as $key => $child) {
            $chunks = preg_split("/\s+/", $child, 9);

            if (isset($chunks[8]) && ($chunks[8] == '.' or $chunks[8] == '..')) {
                continue;
            }

            if (count($chunks) === 1) {
                $len = strlen($chunks[0]);

                if ($len && $chunks[0][$len-1] == ':') {
                    $path = substr($chunks[0], 0, -1);
                }

                continue;
            }

            // Prepare for filename that has space
            $nameSlices = array_slice($chunks, 8, true);

            $item = [
                'permissions' => $chunks[0],
                'number'      => $chunks[1],
                'owner'       => $chunks[2],
                'group'       => $chunks[3],
                'size'        => file_size($chunks[4]),
                'month'       => $chunks[5],
                'day'         => $chunks[6],
                'time'        => $chunks[7],
                'name'        => implode(' ', $nameSlices),
                'file_type'   => substr(strrchr(implode(' ', $nameSlices), '.'), 1),
                'path'=>$this->getPath(),
                'type'        => $this->rawToType($chunks[0]),
            ];

            if ($item['type'] == 'link' && isset($chunks[10])) {
                $item['target'] = $chunks[10]; // 9 is "->"
            }

            // if the key is not the path, behavior of ftp_rawlist() PHP function
            if (is_int($key) || false === strpos($key, $item['name'])) {
                array_splice($chunks, 0, 8);

                $key = $item['type'].'#'
                    .($path ? $path.'/' : '')
                    .implode(' ', $chunks);

                if ($item['type'] == 'link') {
                    // get the first part of 'link#the-link.ext -> /path/of/the/source.ext'
                    $exp = explode(' ->', $key);
                    $key = rtrim($exp[0]);
                }

                $items[$key] = $item;
            } else {
                // the key is the path, behavior of FtpClient::rawlist() method()
                $items[$key] = $item;
            }
        }

        return $items;
    }

/**
     * 将原始信息（drwx---r-x…）转换为类型（文件、目录、链接、未知）。
     * 只有第一个字符用于解析。
     *
     * @param  string $permission Example : drwx---r-x
     *
     * @return string The file type (file, directory, link, unknown)
     * @throws FtpException
     */
    public function rawToType($permission)
    {
        if (!is_string($permission)) {
            throw new FtpException('The "$permission" argument must be a string, "'
            .gettype($permission).'" given.');
        }

        if (empty($permission[0])) {
            return 'unknown';
        }

        switch ($permission[0]) {
            case '-':
                return 'file';

            case 'd':
                return 'directory';

            case 'l':
                return 'link';

            default:
                return 'unknown';
        }
    }

    public function setPasvmode ($pasvmode)
    {
        //把FTP服务器部署在防火墙或者NAT服务器的背后，
        //则采用主动操作模式的客户端只能够建立命令连接而无法进行文件传输
        return ftp_pasv($this->conn, $pasvmode);
    }

    public function getPath(){
        return ftp_pwd($this->conn);
    }
    /**
     * 设置用于转发要在FtpClient实例中使用的PHP FTP函数的包装器。
     *
     * @param  FtpWrapper $wrapper
     * @return FtpClient
     */
    protected function setWrapper(FtpWrapper $wrapper)
    {
        $this->ftp = $wrapper;

        return $this;
    }

}
