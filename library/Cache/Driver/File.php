<?php
// +----------------------------------------------------------------------
// | yafwant
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2014
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: huangbin
// +----------------------------------------------------------------------

/**
 * Class FIle_session
 * 文件缓存驱动
 * @todo 添加错误处理
 */
class File_session extends Cache {
    /**
     * 构造函数
     * @param array $options 文件缓存配置参数
     */
    public function __construct($options = array()){
    	if(!empty($options)) {
            $this->options =  $options;
       	}
        $this->initPath();
    }

    /**
     * 初始化文件缓存目录文件夹
     */
    public function initPath(){
        // 创建应用缓存目录
        if (!is_dir($this->options['path'])) {
            mkdir($this->options['path']);
        }
    }

    /**
     * 写入缓存
     * @access public
     * @param string $name 缓存变量名
     * @param mixed $value  存储数据
     * @param int $expire  有效时间 0为永久
     * @return boolean
     */
    public function set($name,$value,$expire=null) {
        if(is_null($expire)) {
            $expire =  $this->options['expire'];
        }
        $filename = $this->getFileName($name);
        $data = serialize($value);
        //是否数据压缩
        if($this->options['compress'] && function_exists('gzcompress')) {
            //数据压缩
            $data = gzcompress($data,3);
        }
        //是否数据校验
        if($this->options['check']) {
            $check  =  md5($data);
        }else {
            $check  =  '';
        }
        $data = "<?php\n//".sprintf('%012d',$expire).$check.$data."\n?>";
        $result = file_put_contents($filename,$data);
        if($result) {
//            if($this->options['length']>0) {
//                // 记录缓存队列
//                $this->queue($name);
//            }
            clearstatcache();
            return true;
        }else {
            return false;
        }
    }

    /**
     * 读取缓存
     * @access public
     * @param string $name 缓存变量名
     * @return mixed
     */
    public function get($name) {
        $filename   =   $this->getFileName($name);
        if (!is_file($filename)) {
            return false;
        }
        $content = file_get_contents($filename);
        if(false !== $content) {
            //substr返回字符串的一部分substr(string,start,length)
            //string 规定要返回其中一部分的字符串 start 规定在字符串的何处开始 length 规定被返回字符串的长度。默认是直到字符串的结尾
            $expire  =  (int)substr($content,8,12);
            if($expire != 0 && time() > filemtime($filename) + $expire) {
                //缓存过期删除缓存文件
                unlink($filename);
                return false;
            }
            //开启数据校验
            if($this->options['check']) {
                $check = substr($content,20,32);
                $content = substr($content,52,-3);
                //校验错误
                if($check != md5($content)) {
                    return false;
                }
            }else {
                $content = substr($content,20,-3);
            }
            if($this->options['compress'] && function_exists('gzcompress')) {
                //启用数据压缩
                $content = gzuncompress($content);
            }
            $content = unserialize($content);
            return $content;
        }
        else {
            return false;
        }
    }

    /**
     * 删除文件缓存
     * @param $name 要删除的文件名
     * @return boolean
     */
    public function rm($name) {
        $filename   =   $this->getFileName($name);
        if (!is_file($filename)) {
            return false;
        }
        return unlink($filename);
    }

    /**
     * 取得变量的存储文件名
     * @access private
     * @param string $name 缓存变量名
     * @return string
     */
    private function getFileName($name) {
        $name	=	md5($name);
        //使用子目录
        if($this->options['subdir']) {
            $dir   ='';
            for($i = 0;$i < $this->options['level'];$i++) {
                $dir	.=	$name{$i}.'/';
            }
            if(!is_dir($this->options['path'].$dir)) {
                mkdir($this->options['path'].$dir,0755,true);
            }
            $filename =	$dir.$name.'.php';
        }else{
            $filename = $name.'.php';
        }
        return $this->options['path'].$filename;
    }
}