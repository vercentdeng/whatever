<?php
include 'common.php';
include 'header.php';
include 'menu.php';

$stat = Typecho_Widget::widget('Widget_Stat');
?>
<div class="main">
    <div class="container typecho-dashboard">
        <?php include 'page-title.php'; ?>
        <div class="colgroup typecho-page-main">
            <div class="col-mb-12 welcome-board" role="main">
                <p><?php _e('目前有 <em>%s</em> 篇日志, 并有 <em>%s</em> 条关于你的评论在 <em>%s</em> 个分类中.', 
                $stat->myPublishedPostsNum, $stat->myPublishedCommentsNum, $stat->categoriesNum); ?>
                <br><?php _e('使用下面的链接开始你的故事吧:'); ?></p>
        
                <ul id="start-link" class="clearfix">
                    <?php if($user->pass('contributor', true)): ?>
                    <li><a href="<?php $options->adminUrl('write-post.php'); ?>"><?php _e('撰写新文章'); ?></a></li>
                    <?php if($user->pass('editor', true) && 'on' == $request->get('__typecho_all_comments') && $stat->waitingCommentsNum > 0): ?> 
                        <li><a href="<?php $options->adminUrl('manage-comments.php?status=waiting'); ?>"><?php _e('待审核的评论'); ?></a>
                        <span class="balloon"><?php $stat->waitingCommentsNum(); ?></span>
                        </li>
                    <?php elseif($stat->myWaitingCommentsNum > 0): ?>
                        <li><a href="<?php $options->adminUrl('manage-comments.php?status=waiting'); ?>"><?php _e('待审核评论'); ?></a>
                        <span class="balloon"><?php $stat->myWaitingCommentsNum(); ?></span>
                        </li>
                    <?php endif; ?>
                    <?php if($user->pass('editor', true) && 'on' == $request->get('__typecho_all_comments') && $stat->spamCommentsNum > 0): ?> 
                        <li><a href="<?php $options->adminUrl('manage-comments.php?status=spam'); ?>"><?php _e('垃圾评论'); ?></a>
                        <span class="balloon"><?php $stat->spamCommentsNum(); ?></span>
                        </li>
                    <?php elseif($stat->mySpamCommentsNum > 0): ?>
                        <li><a href="<?php $options->adminUrl('manage-comments.php?status=spam'); ?>"><?php _e('垃圾评论'); ?></a>
                        <span class="balloon"><?php $stat->mySpamCommentsNum(); ?></span>
                        </li>
                    <?php endif; ?>
                    <?php if($user->pass('administrator', true)): ?>
                    <li><a href="<?php $options->adminUrl('themes.php'); ?>"><?php _e('更换外观'); ?></a></li>
                    <li><a href="<?php $options->adminUrl('options-general.php'); ?>"><?php _e('系统设置'); ?></a></li>
                    <?php endif; ?>
                    <?php endif; ?>
                    <!--<li><a href="<?php $options->adminUrl('profile.php'); ?>"><?php _e('更新我的资料'); ?></a></li>-->
                </ul>
            </div>

            <div class="col-mb-12 col-tb-4" role="complementary">
                <section class="latest-link">
                    <h3><?php _e('最近发布的文章'); ?></h3>
                    <?php Typecho_Widget::widget('Widget_Contents_Post_Recent', 'pageSize=10')->to($posts); ?>
                    <ul>
                    <?php if($posts->have()): ?>
                    <?php while($posts->next()): ?>
                        <li>
                            <span><?php $posts->date('n.j'); ?></span>
                            <a href="<?php $posts->permalink(); ?>" class="title"><?php $posts->title(); ?></a>
                        </li>
                    <?php endwhile; ?>
                    <?php else: ?>
                        <li><em><?php _e('暂时没有文章'); ?></em></li>
                    <?php endif; ?>
                    </ul>
                </section>
            </div>

            <div class="col-mb-12 col-tb-4" role="complementary">
                <section class="latest-link">
                    <h3><?php _e('最近得到的回复'); ?></h3>
                    <ul>
                        <?php Typecho_Widget::widget('Widget_Comments_Recent', 'pageSize=10')->to($comments); ?>
                        <?php if($comments->have()): ?>
                        <?php while($comments->next()): ?>
                        <li>
                            <span><?php $comments->date('n.j'); ?></span>
                            <a href="<?php $comments->permalink(); ?>" class="title"><?php $comments->author(true); ?></a>:
                            <?php $comments->excerpt(35, '...'); ?>
                        </li>
                        <?php endwhile; ?>
                        <?php else: ?>
                        <li><?php _e('暂时没有回复'); ?></li>
                        <?php endif; ?>
                    </ul>
                </section>
            </div>
        </div>
    </div>
</div>

<?php
include 'copyright.php';
include 'common-js.php';
?>

<?php include 'footer.php'; ?>
