<?php
include_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Handler.php');

/**
 * Image Uploader
 *
 * @package ImageUploader
 * @author feeling
 * @version 0.1.3 alpha
 * @link http://feelingis.me
 */
class ImageUploader_Plugin implements Typecho_Plugin_Interface
{
    /**
     * 激活插件方法,如果激活失败,直接抛出异常
     *
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function activate()
    {
        Typecho_Plugin::factory('Widget_Upload')->upload = array('ImageUploader_Plugin', 'doHandler');
    }

    /**
     * 禁用插件方法,如果禁用失败,直接抛出异常
     *
     * @static
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function deactivate()
    {

    }

    /**
     * 获取插件配置面板
     *
     * @access public
     * @param Typecho_Widget_Helper_Form $form 配置面板
     * @return void
     */
    public static function config(Typecho_Widget_Helper_Form $form)
    {
        /*  处理引擎  */
        $avariable_engines = self::getAvariableEngines();
        $engines = new Typecho_Widget_Helper_Form_Element_Select('engine', $avariable_engines, key($avariable_engines), _t('处理引擎'));
        $form->addInput($engines);
        /** 是否生成缩略图 */
        $generate_thumb = new Typecho_Widget_Helper_Form_Element_Checkbox('thumb', array(1 => _t('是')), '1', _t('是否生成缩略图'));
        $form->addInput($generate_thumb);
        /* 缩略图最大宽度 */
        $thumb_width = new Typecho_Widget_Helper_Form_Element_Text('thumb_width', NULL, '150', _t('缩略图最大宽度'), _t('单位为像素'));
        $form->addInput($thumb_width);
        /* 缩略图最大高度 */
        $thumb_height = new Typecho_Widget_Helper_Form_Element_Text('thumb_height', NULL, '150', _t('缩略图最大高度'), _t('单位为像素'));
        $form->addInput($thumb_height);
        /*  是否自动增加水印  */
        $generate_watermark = new Typecho_Widget_Helper_Form_Element_Checkbox('watermark', array(1 => _t('是')), '1', _t('是否生成水印'));
        $form->addInput($generate_watermark);
        /*  水印类型  */
        $types = array(
            ImageUploaderHandler::WATERMARK_TYPE_STRING => _t('字符水印'),
            ImageUploaderHandler::WATERMARK_TYPE_IMAGE => _t('图像水印')
        );
        $watermark_types = new Typecho_Widget_Helper_Form_Element_Select(
            'watermark_type',
            $types,
            key($types), _t('水印类型'));
        $form->addInput($watermark_types);
        /*  水印位置  */
        $positions = array(
            ImageUploaderHandler::POSITION_LEFT << 2 | ImageUploaderHandler::POSITION_TOP   => '左上角',
            ImageUploaderHandler::POSITION_CENTER << 2 | ImageUploaderHandler::POSITION_TOP   => '顶部居中',
            ImageUploaderHandler::POSITION_RIGHT << 2 | ImageUploaderHandler::POSITION_TOP   => '顶部靠右',
            ImageUploaderHandler::POSITION_LEFT << 2 | ImageUploaderHandler::POSITION_CENTER   => '居中靠左',
            ImageUploaderHandler::POSITION_CENTER << 2 | ImageUploaderHandler::POSITION_CENTER   => '居中对齐',
            ImageUploaderHandler::POSITION_RIGHT << 2 | ImageUploaderHandler::POSITION_CENTER   => '居中靠右',
            ImageUploaderHandler::POSITION_LEFT << 2 | ImageUploaderHandler::POSITION_BOTTOM   => '底部靠左',
            ImageUploaderHandler::POSITION_CENTER << 2 | ImageUploaderHandler::POSITION_BOTTOM   => '底部居中',
            ImageUploaderHandler::POSITION_RIGHT << 2 | ImageUploaderHandler::POSITION_BOTTOM   => '底部靠右'
        );
        $watermark_positions = new Typecho_Widget_Helper_Form_Element_Select(
            'watermark_position',
            $positions,
            key($positions), _t('水印位置'));
        $form->addInput($watermark_positions);
        /*  字符串水印：字体文件路径  */
        $watermark_font = new Typecho_Widget_Helper_Form_Element_Text(
            'watermark_font',
            NULL,
            '1',
            _t('字体文件路径'),
            _t('仅当水印类型为字符水印时有效，允许为 1 - 5 的数字或一个已经存在的 TTF 字体路径')
        );
        $form->addInput($watermark_font);
        /*  字符串水印：字体大小  */
        $watermark_font_size = new Typecho_Widget_Helper_Form_Element_Text(
            'watermark_font_size',
            NULL,
            '9',
            _t('字体大小'),
            _t('仅当水印类型为字符水印并且字体为 TTF 字体时有效，单位为点')
        );
        $form->addInput($watermark_font_size);
        /*  字符串水印：内容  */
        $watermark_font_text = new Typecho_Widget_Helper_Form_Element_Text(
            'watermark_text', NULL, 'Uploaded on %LONG_TIME%', _t('水印内容'),
            _t('仅当水印类型为字符水印时有效，允许的宏定义包括 %LONG_TIME%（长格式时间：2010-07-01 12:00:00），%SHORT_TIME%（短格式时间：12:00:00）,%DATE%（日期：2010-07-01）')
        );
        $form->addInput($watermark_font_text);
        /*  字符串水印：颜色  */
        $watermark_font_color = new Typecho_Widget_Helper_Form_Element_Text(
            'watermark_color',
            NULL,
            '000000',
            _t('水印字体颜色'),
            _t('仅当水印类型为字符水印时有效，格式为RRGGBB，例如 FFCC00')
        );
        $form->addInput($watermark_font_color);
        /*  图像水印：图像文件路径  */
        $watermark_image = new Typecho_Widget_Helper_Form_Element_Text(
            'watermark_image',
            NULL,
            '',
            _t('图像文件路径'),
            _t('仅当水印类型为图像水印时有效')
        );
        $form->addInput($watermark_image);
    }

    /**
     * 获取所有可用的图像处理驱动列表
     * 
     * @return 数组
     */
    private static function getAvariableEngines()
    {
        $dir_path = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Engine';
        $dir_entries = scandir(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Engine');
        $engines = array();
        foreach ($dir_entries as $entry)
        {
            if ('.' != substr($entry, 0, 1) && 'abstract.php' != strtolower($entry) && TRUE == @is_file($dir_path . DIRECTORY_SEPARATOR . $entry))
            {
                include_once($dir_path . DIRECTORY_SEPARATOR . $entry);
                $engine_name = substr($entry, 0, strpos($entry, '.'));
                $class_name = 'ImageUploaderEngine' . $engine_name;
                if (
                    TRUE == class_exists($class_name)
                    && TRUE == call_user_func(array($class_name, 'isAvariable'))
                )
                    $engines[strtolower($engine_name)] = call_user_func(array($class_name, 'getName'));
            }
        }
        return $engines;
    }

    /**
     * 个人用户的配置面板
     *
     * @access public
     * @param Typecho_Widget_Helper_Form $form
     * @return void
     */
    public static function personalConfig(Typecho_Widget_Helper_Form $form){}

    /**
     * 实际处理方法
     * 
     * @param Widget_Upload $obj
     * @return 布尔值 处理成功则返回TRUE，否则返回FALSE
     */
    public static function doHandler($obj)
    {
        if (FALSE == $obj->attachment->isImage)
            return FALSE;
        $config_items = Typecho_Widget::widget('Widget_Options')->plugin('ImageUploader');
        if (TRUE == empty($config_items))
            return TRUE;

        $file_path = __TYPECHO_ROOT_DIR__ . $obj->attachment->path;
        $image_handler = new ImageUploaderHandler($file_path);
        $image_handler->setEngine($config_items->engine);

        // 水印处理
        if (TRUE == $config_items->watermark)
        {
            switch ($config_items->watermark_type)
            {
                case ImageUploaderHandler::WATERMARK_TYPE_STRING:
                    $water_params = array('watermark', array(
                        'type'  => $config_items->watermark_type,
                        'position'  => $config_items->watermark_position,
                        'font'  => $config_items->watermark_font,
                        'size'  => $config_items->watermark_font_size,
                        'text'  => $config_items->watermark_text,
                        'color' => $config_items->watermark_font_color
                    ));
                    break;
                case ImageUploaderHandler::WATERMARK_TYPE_IMAGE:
                     $water_params = array('watermark', array(
                        'type'  => $config_items->watermark_type,
                        'position'  => $config_items->watermark_position,
                        'file'  => $config_items->watermark_image
                    ));
                    break;
            }
        }

        // 对原图执行处理
        call_user_func_array(array($image_handler, 'addActions'), $water_params);
        $image_handler->handle($file_path);
        $image_handler->emptyActions();

        // 缩略图处理
        if (TRUE == $config_items->thumb)
           $image_handler->addActions('thumb', array('width' => $config_items->thumb_width, 'height' => $config_items->thumb_height));

        // 对缩略图执行处理
        $thumb_path = dirname($file_path) . DIRECTORY_SEPARATOR . 'thumbs' . DIRECTORY_SEPARATOR . basename($file_path);
        $image_handler->handle($thumb_path);
        $obj->attachment->url = Typecho_Common::url(
            substr($thumb_path, strlen(__TYPECHO_ROOT_DIR__)),
            Typecho_Widget::widget('Widget_Options')->siteUrl
        );
    }
}
