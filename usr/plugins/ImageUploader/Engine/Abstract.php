<?php
abstract class ImageUploaderEngineAbstract
{
    /**
     * 配置条目
     * 
     * @var 数组
     */
    protected $config = array();

    /**
     * 待处理操作列表
     * 
     * @var 数组
     */
    protected $actions = array();

    /**
     * 配置
     *
     * @param 数组|字符串 $name
     * @param 混合值 $value
     * @return 布尔值
     */
    public function set($name, $value = NULL)
    {
        if (TRUE == is_array($name))
        {
            foreach ($name as $key => $value)
                $this->set($key, $value);
        }
        elseif (TRUE == is_string($name))
            $this->set($name, $value);
        else
            return FALSE;
        return TRUE;
    }

    /**
     * 获取配置内容
     * 
     * @param 字符串|数组 $name
     * @return 混合值
     */
    public function get($name)
    {
        if (TRUE == is_array($name))
        {
            $values = array();
            foreach ($name as $key)
                $values[$key] = $this->get($key);
            return $values;
        }
        elseif (TRUE == is_string($name) && FALSE == array_key_exists($name, $this->config))
            return $this->config[$name];
        return NULL;
    }

    /**
     * 添加待处理操作
     * 
     * @param 字符串 $action 操作名称
     * @param 数组 $options 参数列表
     * @return ImageUploaderEngineAbstract
     */
    public function add($action, $options = NULL)
    {
        $action = ucwords(strtolower($action));
        if (TRUE == method_exists($this, 'action' . $action))
            $this->actions[] = array(
                'callback'    => $action,
                'params'    => $options
            );
        return $this;
    }

    /**
     * 清空待处理操作
     * @return ImageUploaderEngineAbstract
     */
    public function emptyActions()
    {
        $this->actions = array();
        return $this;
    }

    /**
     * 应用操作
     */
    public function parse()
    {
        foreach ($this->actions as $action)
            call_user_func(array($this, 'action' . $action['callback']), $action['params']);
    }

    /**
     * 魔法方法：获取配置
     * 
     * @param 字符串 $name
     * @return 混合值
     */
    public function __get($name)
    {
        return $this->get($name);
    }

    /**
     * 魔法方法：设置配置
     * 
     * @param 字符串 $name
     * @param 混合值 $value
     * @return 布尔值
     */
    public function __set($name, $value)
    {
        return $this->set($name, $value);
    }

    /**
     * 操作：添加水印
     * @param 数组 $params 要求只要包括键 type
     * @return 布尔值
     */
    public function actionWatermark($params)
    {
        if (FALSE == array_key_exists('type', $params))
            return TRUE;
        switch ($params['type'])
        {
            case ImageUploaderHandler::WATERMARK_TYPE_STRING:
                $type = 'string';
                break;
            case ImageUploaderHandler::WATERMARK_TYPE_IMAGE:
                $type = 'image';
                break;
        }
        $type = ucwords(strtolower($type));
        if (TRUE == method_exists($this, 'actionWatermark' . $type))
            return call_user_func(array($this, 'actionWatermark' . $type), $params);
        return TRUE;
    }

    /**
     * 根据文件扩展名
     * 
     * @param 字符串 $filename
     * @return 字符串
     */
    protected function getExtendName($filename)
    {
        return strtolower(substr($filename, strrpos($filename, '.') + 1));
    }

    /**
     * 转换RGB颜色
     * @param 字符串 $rgb
     * @return 数组
     */
    protected function rgb2Color($rgb)
    {
        if (6 < strlen($rgb))
            $rgb = str_pad($rgb, 6, '0');
        return array(
            base_convert(substr($rgb, 0, 2), 16, 10),
            base_convert(substr($rgb, 2, 2), 16, 10),
            base_convert(substr($rgb, 4, 2), 16, 10),
        );
    }

    /**
     * 打开指定的文件
     * 
     * @param 字符串 $filename
     * @return 布尔值
     */
    abstract public function open($filename);
    /**
     * 将操作结果另存
     *
     * @param 字符串 $filename
     * @return 布尔值
     */
    abstract public function saveTo($filename);

    /**
     * 操作：生成缩略图
     * 
     * @param 数组 $params
     * @return 布尔值
     */
    abstract public function actionThumb($params);

    /**
     * 操作：添加字符串水印
     *
     * @param 数组 $params
     * @return 布尔值
     */
    abstract public function actionWatermarkString($params);

    /**
     * 操作：添加图像水印
     *
     * @param 数组 $params
     * @return 布尔值
     */
    abstract public function actionWatermarkImage($params);

    /**
     * 检查引擎是否可用
     *
     * @return 布尔值
     */
    abstract public static function isAvariable();

    /**
     * 获取引擎名字
     *
     * @return 字符串
     */
    abstract public static function getName();
}