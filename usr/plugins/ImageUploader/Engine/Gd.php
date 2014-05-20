<?php
include_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Abstract.php');
class ImageUploaderEngineGd extends ImageUploaderEngineAbstract
{
    const ENGINE_NAME = 'GD 2.x';

    /**
     * 图像处理句柄
     * 
     * @var 资源
     */
    private $image_id;

    /**
     * 图像信息
     * 
     * @var 数组
     */
    private $image_info;

    /**
     * 打开指定的文件
     *
     * @param 字符串 $filename
     * @return 布尔值
     */
    public function open($filename)
    {
        if (FALSE == empty($this->image_id))
            @imagedestroy($this->image_id);
        $file_extend = $this->getExtendName($filename);
        switch ($file_extend)
        {
            case 'gif':
                $this->image_id = @imagecreatefromgif($filename);
                break;
            case 'png':
                $this->image_id = @imagecreatefrompng($filename);
                break;
            case 'jpg':
            case 'jpeg':
                $this->image_id = @imagecreatefromjpeg($filename);
                break;
        }
        if (FALSE == empty($this->image_id))
        {
            $this->image_info = getimagesize($filename);
            return TRUE;
        }
        else return FALSE;
    }

    public function  __destruct()
    {
        if (FALSE == empty($this->image_id))
            @imagedestroy($this->image_id);
    }

    /**
     * 将操作结果另存
     *
     * @param 字符串 $filename
     * @return 布尔值
     */
    public function saveTo($filename)
    {
        $file_extend = $this->getExtendName($filename);
        $parent_dir = dirname($filename);
        if (
            (
                FALSE == file_exists($parent_dir)
                && FALSE == @mkdir($parent_dir, 0777, TRUE)
            )
            || FALSE == @is_dir($parent_dir)
            || FALSE == @is_writable($parent_dir)
        )
            return FALSE;
        switch ($file_extend)
        {
            case 'gif':
                return @imagegif($this->image_id, $filename);
                break;
            case 'png':
                return @imagepng($this->image_id, $filename);
                break;
            case 'jpg':
            case 'jpeg':
                return @imagejpeg($this->image_id, $filename);
                break;
        }
        return FALSE;
    }

    /**
     * 操作：生成缩略图
     *
     * @param 数组 $params
     * @return 布尔值
     */
    public function actionThumb($params)
    {
        if (TRUE == empty($this->image_id))
            return FALSE;
        if (FALSE == array_key_exists('rate', $params))
        {
            if (FALSE == array_key_exists('width', $params) || FALSE == is_numeric($params['width']))
                $params['width'] = 0;
            else $params['width'] = intval($params['width']);
            if (FALSE == array_key_exists('height', $params) || FALSE == is_numeric($params['height']))
                $params['height'] = 0;
            else $params['height'] = intval($params['height']);
            $width_rate = ceil($params['width'] / $this->image_info[0] * 100);
            $height_rate = ceil($params['height'] / $this->image_info[1] * 100);
            if (0 == $width_rate)
                $width_rate = 100;
            if (0 == $height_rate)
                $height_rate = 100;
            $params['rate'] = min($width_rate, $height_rate);
        }
        else $params['rate'] = intval($params['rate']);
        // 缩放后的尺寸
        $resize_size = array(
            ceil($this->image_info[0] * $params['rate'] / 100),
            ceil($this->image_info[1] * $params['rate'] / 100)
        );
        $dst_image_id = imagecreatetruecolor($resize_size[0], $resize_size[1]);
        if (FALSE == $dst_image_id)
            return FALSE;
        $result = @imagecopyresampled(
            $dst_image_id, $this->image_id,
            0, 0,
            0, 0,
            $resize_size[0], $resize_size[1],
            $this->image_info[0], $this->image_info[1]
        );
        $this->image_id = $dst_image_id;
        $this->image_info[0] = imagesx($this->image_id);
        $this->image_info[1] = imagesy($this->image_id);
        return $result;
    }

    /**
     * 操作：添加字符串水印
     *
     * @param 数组 $params 参数包括 type, position，font，text，color 和 size
     * @return 布尔值
     */
    public function actionWatermarkString($params)
    {
        $params['text'] = str_replace(
                array('%LONG_TIME%', '%SHORT_TIME%', '%DATE%'),
                array(strftime('%Y-%m-%d %H:%M:%S'), strftime('%H:%M:%S'), strftime('%Y-%m-%d')),
                $params['text']
        );
        if (TRUE == is_numeric($params['font']))
        {
            $font_rec = array(
                'width' => imagefontwidth($params['font']) * strlen($params['text']),
                'height'    => imagefontheight($params['font'])
            );
        }
        elseif (
            TRUE == is_string($params['font'])
            && TRUE == is_file($params['font'])
            && TRUE == is_readable($params['font'])
        )
        {
            $tmp = imagettfbbox($params['size'], 0, $params['font'], $params['text']);
            if (TRUE == $tmp)
            {
                $font_rec = array(
                    'width' => max($tmp[2] - $tmp[0], $tmp[4] - $tmp[6]),
                    'height'    => max($tmp[1] - $tmp[7], $tmp[3] - $tmp[5])
                );
            }
        }
        if (FALSE == isset($font_rec))
            return FALSE;
        $dist_position = array(
            'x' => 0,
            'y' => 0,
            'width' => $font_rec['width'],
            'height'    => $font_rec['height']
        );
        switch ($params['position'] >> 2)
        {
            case ImageUploaderHandler::POSITION_LEFT: break;
            case ImageUploaderHandler::POSITION_CENTER:
                $dist_position['x'] = ceil(($this->image_info[0] - $font_rec['width']) / 2);
                break;
            case ImageUploaderHandler::POSITION_RIGHT:
                $dist_position['x'] = $this->image_info[0] - $font_rec['width'];
                break;
        }
        switch ($params['position'] & 3)
        {
            case ImageUploaderHandler::POSITION_TOP:
                break;
            case ImageUploaderHandler::POSITION_CENTER:
                $dist_position['y'] = ceil(($this->image_info[1] - $font_rec['height']) / 2);
                break;
            case ImageUploaderHandler::POSITION_BOTTOM:
                $dist_position['y'] = $this->image_info[1] - $font_rec['height'];
                break;
        }
        // 白色背景
        $back_color = imagecolorallocate($this->image_id, 255, 255, 255);
        $tmp = $this->rgb2Color($params['color']);
        $color = imagecolorallocate($this->image_id, $tmp[0], $tmp[1], $tmp[2]);
        if (TRUE == is_numeric($params['font']))
            return imagestring($this->image_id, intval($params['font']), $dist_position['x'], $dist_position['y'], $params['text'], $color);
        else
            return imagettftext($this->image_id, $params['size'], 0, $dist_position['x'], $dist_position['y'] + $font_rec['height'], $color, $params['font'], $params['text']);
    }

    /**
     * 操作：添加图像水印
     *
     * @param 数组 $params 参数包括 type, position 和 file
     * @return 布尔值
     */
    public function actionWatermarkImage($params)
    {
        if (FALSE == file_exists($params['file']) || FALSE == is_readable($params['file']) || FALSE == is_file($params['file']))
            return FALSE;
        $file_size = getimagesize($params['file']);
        switch (strtolower($file_size['mime']))
        {
            case 'image/gif':
                $water_img = imagecreatefromgif($params['file']);
                break;
            case 'image/jpeg':
                $water_img = imagecreatefromjpeg($params['file']);
                break;
            case 'image/png':
                $water_img = imagecreatefrompng($params['file']);
                break;
            default:
                return FALSE;
        }
        $dist_position = array(
            'x' => 0,
            'y' => 0,
            'width' => $file_size[0],
            'height'    => $file_size[1]
        );
        switch ($params['position'] >> 2)
        {
            case ImageUploaderHandler::POSITION_LEFT: break;
            case ImageUploaderHandler::POSITION_CENTER:
                $dist_position['x'] = ceil(($this->image_info[0] -  $file_size[0]) / 2);
                break;
            case ImageUploaderHandler::POSITION_RIGHT:
                $dist_position['x'] = $this->image_info[0] -  $file_size[0];
                break;
        }
        switch ($params['position'] & 3)
        {
            case ImageUploaderHandler::POSITION_TOP:
                break;
            case ImageUploaderHandler::POSITION_CENTER:
                $dist_position['y'] = ceil(($this->image_info[1] -  $file_size[1]) / 2);
                break;
            case ImageUploaderHandler::POSITION_BOTTOM:
                $dist_position['y'] = $this->image_info[1] - $file_size[1];
                break;
        }
        $result = imagecopyresampled(
            $this->image_id, $water_img,
            $dist_position['x'], $dist_position['y'],
            0, 0,
            $dist_position['width'], $dist_position['height'],
            $dist_position['width'], $dist_position['height']
        );
        imagedestroy($water_img);
        return $result;
    }

    /**
     * 检查引擎是否可用
     *
     * @return 布尔值
     */
    public static function isAvariable()
    {
        if (TRUE == extension_loaded('gd'))
            return TRUE;
        return FALSE;
    }

    /**
     * 获取引擎名字
     *
     * @return 字符串
     */
    public static function getName()
    {
        return self::ENGINE_NAME;
    }
}