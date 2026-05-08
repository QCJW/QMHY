<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php $this->need('header.php'); ?>
<?php $this->need('sidebar.php'); ?>



<?php if($this->fields->mp4): ?>
<div class="main_content_inner">
<?php $this->need('ze-video.php'); ?>
<?php else: ?>
<?php $this->need('ze-post.php'); ?><?php get_post_view($this,1); ?>
<?php endif; ?>




<?php $this->need('footer.php'); ?>
