<?php
namespace app\index\controller;

use think\Controller;
use think\Request;

class Tool extends Controller
{
    public function index()
    {
        return $this->fetch();
    }

    public function indexPost(Request $request)
    {
        if($request->isPost()) {
            if($request->file()) {
                $dir=WEB_ROOT.'uploads/';
                $file = $request->file('image');
                $url = cache($file->hash());

                if(!$url) {
//                    @unlink($dir.date('Ymd',time()-86400));
                    // 移动到框架应用根目录/public/uploads/ 目录下
                    $info = $file->validate(['size'=>20*1024*1024,'ext'=>'jpg'])->move($dir);
                    if($info){
                        $url = $dir.$info->getSaveName();
                        cache($file->hash(),$url);
                    }else{
                        // 上传失败获取错误信息
                        return $file->getError();
                    }
                }
                $proportion = $request->proportion ?? 10;
                $image_str = $this->img2str($url,$proportion);// 503*758 500*756
                echo "<pre>";
                echo $image_str;
                echo "</pre>";
                exit;
            } else {
                return '图片不能为空';
            }
        }
    }

    private function img2str($img_src,$proportion){
        $height_index = (int)ceil($proportion * 2.591330324);//最佳宽高比例 1:2.6
        if(exif_imagetype($img_src) == 3) {
            $resource = imagecreatefrompng($img_src);
        } elseif(exif_imagetype($img_src) == 2) {
            $resource = imagecreatefromjpeg($img_src);
        }
        $width = imagesx($resource);
        $height = imagesy($resource);
        imagefilter($resource, IMG_FILTER_GRAYSCALE);//转灰度图
        // 1.浮点算法：Gray=R*0.3+G*0.59+B*0.11
        //2.整数方法：Gray=(R*30+G*59+B*11)/100
        //3.移位方法：Gray =(R*76+G*151+B*28)>>8;
        //4.平均值法：Gray=（R+G+B）/3;
        //5.仅取绿色：Gray=G；
        //RPG(Gray,Gray,Gray)
        $image_str = '';
        for ($i=0; $i < $height; $i++) {
            if($i%$height_index==0){
                for ($j=0; $j < $width; $j++) {
                    if($j%$proportion==0){
                        $color_index = imagecolorat($resource, $j, $i);
                        $rgb = imagecolorsforindex($resource,$color_index);

                        $gray = $rgb['red'];//因为已经转为灰度图 所有 red green blue值相同
                        $str = '@Q#Om0dRoGhCsfyt+*/+-;::.,`. ';
                        $r = $gray/255;
                        $offset=(int)ceil($r*(strlen($str)-1));
                        $image_str .= $str[$offset];
                    }
                }
                $image_str .= " <br>";
            }
        }
        return $image_str;
    }
}
