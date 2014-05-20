<?php
class ImageUploaderHandler
{
    /**
     * 图像处理驱动
     * 
     * @var ImageUploaderEngineAbstract
     */
    private $engine;
    /**
     * 当前已经装载的文件
     *
     * @var 字符串
     */
    private $current_file = '';
    /**
     * 类基础目录
     * 
     * @var 字符串
     */
    private $base_path = '';

    const POSITION_LEFT = 0x00;
    const POSITION_CENTER = 0x01;
    const POSITION_RIGHT = 0x02;
    const POSITION_TOP = 0x00;
    const POSITION_BOTTOM = 0x02;
    const WATERMARK_TYPE_STRING = 0x01;
    const WATERMARK_TYPE_IMAGE = 0x02;

    public static function logToFile($mesg)
    {
        file_put_contents('./image_uploader.log', $mesg . "\n", FILE_APPEND);
    }

    /**
     * 构造器
     * 
     * @param 字符串 $filename
     * @param 字符串|ImageUploaderEngineAbstract $engine_name
     * @param 数组 $engine_options
     */
    public function __construct($filename, $engine_name = NULL, $engine_options = NULL)
    {
        $this->open($filename);
        $this->base_path = dirname(__FILE__);
    }

    /**
     * 设置图像处理驱动
     * 
     * @param 字符串|ImageUploaderHandler $name
     * @param 数组 $options
     * @return ImageUploaderHandler
     */
    public function setEngine($name, $options = NULL)
    {
        if (TRUE == ($name instanceof ImageUploaderEngineAbstract))
            $this->engine = $name;
        elseif (TRUE == is_string($name))
        {
            $uc_name = ucwords(strtolower($name));
            $class_name = 'ImageUploaderEngine' . $uc_name;
            @include_once($this->base_path . DIRECTORY_SEPARATOR . 'Engine' . DIRECTORY_SEPARATOR . $uc_name . '.php');
            if (TRUE == class_exists($class_name))
                $this->engine = new $class_name();
        }
        if (FALSE == empty($this->engine))
        {
            if (FALSE == empty($options))
                $this->engine->set($options);
            if (FALSE == empty($this->current_file))
                $this->engine->open($this->current_file);
        }
        return $this;
    }

    /**
     * 添加待处理操作
     * 
     * @param 字符串 $action
     * @param 数组 $options
     * @return ImageUploaderHandler
     */
    public function addActions($action, $options = NULL)
    {
        if (FALSE == empty($this->engine))
            $this->engine->add($action, $options);
        return $this;
    }

    /**
     * 清空待处理操作
     *
     * @return ImageUploaderHandler
     */
    public function emptyActions()
    {
        $this->engine->emptyActions();
        return $this;
    }

    /**
     * 处理图像
     * 
     * @param 字符串 $filename 需要另存的文件路径
     * @return ImageUploaderHandler
     */
    public function handle($filename = NULL)
    {
        if (TRUE == empty($this->engine))
            return $this;
        $this->engine->parse();
        // 保存文件
        if (FALSE == empty($filename))
            $this->saveTo($filename);
        return $this;
    }

    /**
     * 打开指定的文件
     * 
     * @param 字符串 $filename
     * @return ImageUploaderHandler
     */
    public function open($filename)
    {
        if (TRUE == is_string($filename) && TRUE == is_file($filename) && TRUE == is_readable($filename))
        {
            if (FALSE == empty($this->engine))
                $this->engine->open($filename);
            $this->current_file = $filename;
        }
        return $this;
    }

    /**
     * 将操作结果另存
     *
     * @param 字符串 $filename
     * @return 布尔值
     */
    public function saveTo($filename)
    {
        if (TRUE == empty($this->engine))
            return $this;
        else return $this->engine->saveTo($filename);
    }
}