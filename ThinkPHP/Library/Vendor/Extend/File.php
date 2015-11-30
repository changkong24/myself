<?php
namespace Extend;

/**
 * Class File
 * 文件上传处理类
 */
class File
{
    private $table,$file_type,$path,$name,$oldname,$newname,$guid,$type,$size=0,$suffix='jpg',$error=0,$message;
    private $img_suffixs = array('jpg','jpeg','png','gif');//pjpeg用于兼容IE7-8
    private $rar_suffixs = array('rar','zip');
    private static $sta_newname,$sta_message,$sta_error=0;
    private static $lang = array(
            'img' => array( 'null'=>"未选择上传图片", 'size'=>"图片大小不能超过2M", 'type'=>"图片类型错误" ),
            'file' => array( 'null'=>"未选择上传文件", 'size'=>"文件大小不能超过2M", 'type'=>"文件类型错误" ),
        );

    static function name( $file_name )
    {
        $obj = new File();
        self::$sta_error = 0;//初始化error以免再次调用时上次残留的error
        if(strpos($file_name,':')){
            list($obj->file_type,$file) = explode(':',$file_name);
            list($path,$file_name) = explode('|',$file);
            $obj->path = "/uploadfile/$path/".date('Ym').'/';
        }
        $obj->name = $file_name;

        return $obj;
    }

    //原文件名
    public function oldname( $oldname )
    {
        $this->oldname = $oldname;
        return $this;
    }

    //取得创建的文件名
    static function filename(){
        return self::$sta_newname;
    }

    /**
     * 取得缩略图
     * @param $filename 已上传图片
     * @param $size 缩略图长宽
     * @return string
     */
    static function thumb( $filename, $size )
    {
        $pos = strrpos($filename,'/');
        $path = substr($filename,0,$pos+1);
        $filename = substr($filename,$pos+1);

        $filename = $path . "$size/$filename";

        return $filename;
    }

    //取得64位image data (经测试，chrome qq浏览器 firfox 360浏览器 ie7-11都支持这种方式显示图片) $size图片长宽
    static function dataimage( $size=null )
    {
        if( $size ){
            return "data:image/jpeg;base64,".base64_encode(file_get_contents( GMLPATH . self::thumb(self::$sta_newname,$size) ));
        }
        return "data:image/jpeg;base64,".base64_encode(file_get_contents( GMLPATH . self::$sta_newname ));
    }

    //生成文件
    public function create()
    {
        if( $this->_validate()==false ){
            return false;
        }

        if( !$this->createpath( GMLPATH.$this->path ) ){
            $this->message = self::$sta_message ='create dir fail.';
            return false;
        }
        $this->guid = guid();
        $newname = $this->path . $this->guid . '.jpg';

        move_uploaded_file( $_FILES[$this->name]['tmp_name'], GMLPATH.$newname );
        $this->newname = self::$sta_newname = $newname;

        return true;
    }

    //生成图片缩略图 $int_falg：1 生成原生图，-1不生成原生图
    public function createThumb( $sizes, $int_falg=1 )
    {
        if( $this->_validate()==false ){
            return false;
        }
        if( $int_falg===1 ){//生成原生图
            $this->create();
            self::$sta_newname = $this->newname;
            $new_file = GMLPATH . $this->newname;
        }elseif( $int_falg===-1 ){
            $this->guid = guid();
            self::$sta_newname = $this->path . $this->guid . '.jpg';
            $new_file = $_FILES[$this->name]['tmp_name'];
        }else{
            return false;
        }

        //生个缩略图
        foreach( $sizes as $size )
        {
            $size = explode('x',$size);
            $size[0] = intval($size[0]);
            $size[1] = intval($size[1]);

            $path = GMLPATH . $this->path ."{$size[0]}x{$size[1]}/";
            $this->createpath($path);//建立目录
            $this->img2thumb( $new_file, $path . $this->guid . '.jpg', $size[0], $size[1], 1 );
        }

        return true;
    }

    //多文件生成 一般情况下不会用到暂时不写
    public function creates(){}

    //多张图片生成缩略图 一般情况下不会用到暂时不写
    public function createThumbs(){}

    //创建多级目录
    private function createpath( $dir )
    {
        if( !is_dir( $dir ) )//目录不存在则创建
        {
            if( !mkdir($dir, 0777, true) ){//创建多级目录
                return false;
            }

            //目录创建成功返回true
            return true;
        }else{
            //目录已存在返回true
            return true;
        }
    }

    //删除文件
    public function delete()
    {
        if( is_array($this->name) ){//多个文件删除
            foreach( $this->name as $childname ){
                if(file_exists($childname))    unlink($childname);
            }
        }else{//单个文件删除
            if(file_exists($this->name))    unlink($this->name);
        }
    }

    //更换单个文件
    public function update()
    {
        $name = $this->name;
        //删除老文件
        $this->name = $this->oldname;
        $this->delete();
        //生成新文件
        $this->name = $name;
        $new_name = $this->create();

        return $new_name;
    }

    /**
     * 生成缩略图
     * @param string     源图绝对完整地址{带文件名及后缀名}
     * @param string     目标图绝对完整地址{带文件名及后缀名}
     * @param int        缩略图宽{0:此时目标高度不能为0，目标宽度为源图宽*(目标高度/源图高)}
     * @param int        缩略图高{0:此时目标宽度不能为0，目标高度为源图高*(目标宽度/源图宽)}
     * @param int        是否裁切{宽,高必须非0}
     * @param int/float  缩放{0:不缩放, 0<this<1:缩放到相应比例(此时宽高限制和裁切均失效)}
     * @return boolean
     */
    private function img2thumb($src_img, $dst_img, $width = 75, $height = 75, $cut = 0, $proportion = 0)
    {
        if(!is_file($src_img))
        {
            return false;
        }
        $ot = pathinfo($dst_img, PATHINFO_EXTENSION);//取得文件后缀
        $otfunc = 'image' . ($ot == 'jpg' ? 'jpeg' : $ot);
        $srcinfo = getimagesize($src_img);
        $src_w = $srcinfo[0];
        $src_h = $srcinfo[1];
        $type  = strtolower(substr(image_type_to_extension($srcinfo[2]), 1));
        $createfun = 'imagecreatefrom' . ($type == 'jpg' ? 'jpeg' : $type);

        $dst_h = $height;
        $dst_w = $width;
        $x = $y = 0;

        /**
         * 缩略图不超过源图尺寸（前提是宽或高只有一个）
         */
        if(($width> $src_w && $height> $src_h) || ($height> $src_h && $width == 0) || ($width> $src_w && $height == 0))
        {
            $proportion = 1;
        }
        if($width> $src_w)
        {
            $dst_w = $width = $src_w;
        }
        if($height> $src_h)
        {
            $dst_h = $height = $src_h;
        }

        if(!$width && !$height && !$proportion)
        {
            return false;
        }
        if(!$proportion)
        {
            if($cut == 0)
            {
                if($dst_w && $dst_h)
                {
                    if($dst_w/$src_w> $dst_h/$src_h)
                    {
                        $dst_w = $src_w * ($dst_h / $src_h);
                        $x = 0 - ($dst_w - $width) / 2;
                    }
                    else
                    {
                        $dst_h = $src_h * ($dst_w / $src_w);
                        $y = 0 - ($dst_h - $height) / 2;
                    }
                }
                else if($dst_w xor $dst_h)
                {
                    if($dst_w && !$dst_h)  //有宽无高
                    {
                        $propor = $dst_w / $src_w;
                        $height = $dst_h  = $src_h * $propor;
                    }
                    else if(!$dst_w && $dst_h)  //有高无宽
                    {
                        $propor = $dst_h / $src_h;
                        $width  = $dst_w = $src_w * $propor;
                    }
                }
            }
            else
            {
                if(!$dst_h)  //裁剪时无高
                {
                    $height = $dst_h = $dst_w;
                }
                if(!$dst_w)  //裁剪时无宽
                {
                    $width = $dst_w = $dst_h;
                }
                $propor = min(max($dst_w / $src_w, $dst_h / $src_h), 1);
                $dst_w = (int)round($src_w * $propor);
                $dst_h = (int)round($src_h * $propor);
                $x = ($width - $dst_w) / 2;
                $y = ($height - $dst_h) / 2;
            }
        }
        else
        {
            $proportion = min($proportion, 1);
            $height = $dst_h = $src_h * $proportion;
            $width  = $dst_w = $src_w * $proportion;
        }

        $src = $createfun($src_img);
        $dst = imagecreatetruecolor($width ? $width : $dst_w, $height ? $height : $dst_h);
        $white = imagecolorallocate($dst, 255, 255, 255);
        imagefill($dst, 0, 0, $white);

        if(function_exists('imagecopyresampled'))
        {
            imagecopyresampled($dst, $src, $x, $y, 0, 0, $dst_w, $dst_h, $src_w, $src_h);
        }
        else
        {
            imagecopyresized($dst, $src, $x, $y, 0, 0, $dst_w, $dst_h, $src_w, $src_h);
        }
        $otfunc($dst, $dst_img);
        imagedestroy($dst);
        imagedestroy($src);
        return true;
    }

    //上传文件类型
    public function type( $type )
    {
        $this->type = explode(',',$type);

        return $this;
    }

    //文件大小
    public function size( $size )
    {
        $this->size = $size;
        return $this;
    }

    //后缀
    public function suffix( $suffix )
    {
        $this->suffix = $suffix;
        return $this;
    }

    static function lang( $lang )
    {
        self::$lang = $lang;
    }

    //验证文件
    private function _validate()
    {
        if( !in_array($this->file_type,array('img','file')) ){
            $this->error = self::$sta_error = -1;
            $this->message = self::$sta_message ='args file_type value error';
            return false;
        }elseif( empty($_FILES[$this->name]) || $_FILES[$this->name]['error']==4 ){
            $this->error = self::$sta_error = 4;//保持与$_FILES中的error一致
            $this->message = self::$sta_message = self::$lang[$this->file_type]['null'];
            return false;
        }elseif( $_FILES[$this->name]['error']==1 ){
            $this->error = self::$sta_error = 1;//保持与$_FILES中的error一致
            $this->message = self::$sta_message = "上传的文件的大小超过了web服务器的限制";//php.ini 中 upload_max_filesize的值
            return false;
        }elseif( $_FILES[$this->name]['error']==2 ){
            $this->error = self::$sta_error = 2;//保持与$_FILES中的error一致
            $this->message = self::$sta_message = "上传文件的大小超过了HTML表单中的限制";//HTML 表单中 MAX_FILE_SIZE 选项指定的值。
            return false;
        }elseif( $_FILES[$this->name]['error']==3 ){
            $this->error = self::$sta_error = 3;//保持与$_FILES中的error一致
            $this->message = self::$sta_message = "文件只有部分被上传";
            return false;
        }

        $file = &$_FILES[$this->name];
        preg_match('/jpeg|png|gif|rar|zip/',$file['type'],$file_type_match);//explode('/',$file['type']);
        $file['type'] = @$file_type_match[0];
        $this->size==0 && $this->size = 1024*1024*2;//2M

        if( $file['size']>$this->size ){
            $this->error = self::$sta_error = -2;
            $this->message = self::$sta_message = self::$lang[$this->file_type]['size'];
            return false;
        }elseif( !in_array($file['type'],$this->type) ){//这里选用后缀作判断，日后有时间再改善
            $this->error = self::$sta_error = -3;
            $this->message = self::$sta_message = self::$lang[$this->file_type]['type'];
            return false;
        }

        return true;
    }

    //取得错误信息
    static function message()
    {
        return self::$sta_message;
    }

    //取得error
    static function error()
    {
        return self::$sta_error;
    }
}

//生成原生图片并生成缩略图
//说明：File::name('filetype:path|filename')
//if( !File::name('img:news|pic')->type('jpg,png,gif')->create(1)->createThumb( array('180x180','100x100') ) ){
//    return File::message();
//}

//删除多个文件 结合DB->event()使用更佳
//File::name( array('/upload/img/news/201504/sdfsdfsd.jpg','/upload/img/news/201504/sdfsdfsd32434.jpg') )->delete();

//更新单张原生图片
//File::name('img:news|pic')->oldname('sfdfsdf.jpg')->update();
