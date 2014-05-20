<?php
if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}

$header = '<link rel="stylesheet" href="' . Typecho_Common::url('css/normalize.css?v=' . $suffixVersion, $options->adminUrl) . '"> 
<link rel="stylesheet" href="' . Typecho_Common::url('css/grid.css?v=' . $suffixVersion, $options->adminUrl) . '"> 
<link rel="stylesheet" href="' . Typecho_Common::url('css/style.css?v=' . $suffixVersion, $options->adminUrl) . '">
<!--[if lt IE 9]>
<script src="' . Typecho_Common::url('js/html5shiv.js?v=' . $suffixVersion, $options->adminUrl) . '"></script>
<script src="' . Typecho_Common::url('js/respond.js?v=' . $suffixVersion, $options->adminUrl) . '"></script>
<![endif]-->';

/** 注册一个初始化插件 */
$header = Typecho_Plugin::factory('admin/header.php')->header($header);

?><!DOCTYPE HTML>
<html class="no-js">
    <head>
        <meta charset="<?php $options->charset(); ?>">
        <meta http-equiv="X-UA-Compatible" content="IE=edge, chrome=1">
        <meta name="renderer" content="webkit">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
        <title><?php _e('%s - %s - Powered by Typecho', $menu->title, $options->title); ?></title>
        <meta name="robots" content="noindex, nofollow">
        <?php echo $header; ?>
    </head>
    <body<?php if (isset($bodyClass)) {echo ' class="' . $bodyClass . '"';} ?>>
    <!--[if lt IE 9]>
        <div class="message error browsehappy"><?php _e('当前网页 <strong>不支持</strong> 你正在使用的浏览器. 为了正常的访问, 请 <a href="http://browsehappy.com/">升级你的浏览器</a>'); ?>.</div>
    <![endif]-->
