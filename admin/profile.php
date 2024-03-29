<?php
include 'common.php';
include 'header.php';
include 'menu.php';

$stat = Typecho_Widget::widget('Widget_Stat');
?>

<div class="main">
    <div class="body container">
        <?php include 'page-title.php'; ?>
        <div class="colgroup typecho-page-main">
            <div class="col-mb-12 col-tb-3">
                <h2><?php $user->screenName(); ?><br></h2>
                <p><?php _e('目前有 <em>%s</em> 篇 Blog,并有 <em>%s</em> 条关于你的评论在已设定的 <em>%s</em> 个分类中.', 
                $stat->myPublishedPostsNum, $stat->myPublishedCommentsNum, $stat->categoriesNum); ?></p>
            </div>
            <div class="col-mb-12 col-tb-6 col-tb-offset-1 typecho-content-panel" role="form">
                <section>
                    <h3><?php _e('个人资料'); ?></h3>
                    <?php Typecho_Widget::widget('Widget_Users_Profile')->profileForm()->render(); ?>
                </section>

                <?php if($user->pass('contributor', true)): ?>
                <br>
                <section id="writing-option">
                    <h3><?php _e('撰写设置'); ?></h3>
                    <?php Typecho_Widget::widget('Widget_Users_Profile')->optionsForm()->render(); ?>
                </section>
                <?php endif; ?>

                <br>

                <section id="change-password">
                    <h3><?php _e('密码修改'); ?></h3>
                    <?php Typecho_Widget::widget('Widget_Users_Profile')->personalFormList(); ?>
                    <?php Typecho_Widget::widget('Widget_Users_Profile')->passwordForm()->render(); ?>
                </section>
            </div>
        </div>
    </div>
</div>

<?php
include 'copyright.php';
include 'common-js.php';
include 'form-js.php';
Typecho_Plugin::factory('admin/profile.php')->bottom();
include 'footer.php';
?>
