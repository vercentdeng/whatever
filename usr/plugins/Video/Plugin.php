<?php
/**
 * 云边轻博客的视频添加
 *
 * @package Video
 * @author yangweijie
 * @version 0.0.1
 * @link http://code-tech.diandian.com
 */
class Video_Plugin implements Typecho_Plugin_Interface{

    /**
     * 激活插件方法,如果激活失败,直接抛出异常
     *
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function activate(){
		Typecho_Plugin::factory('admin/write-post.php')->bottom = array('Video_Plugin', 'Insert');
		Typecho_Plugin::factory('admin/write-page.php')->bottom = array('Video_Plugin', 'Insert');
    }


	public static function Insert(){
		$options = Helper::options();
        $ajaxurl = Typecho_Common::url('Video/video.php?type=video' , $options->pluginUrl);
        include dirname(__FILE__).'/window.html';
	}

    public static function config(Typecho_Widget_Helper_Form $form){}
    /**
     * 个人用户的配置面板
     *
     * @access public
     * @param Typecho_Widget_Helper_Form $form
     * @return void
     */
    public static function personalConfig(Typecho_Widget_Helper_Form $form){}

    /**
     * 禁用插件方法,如果禁用失败,直接抛出异常
     *
     * @static
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function deactivate(){}



}

