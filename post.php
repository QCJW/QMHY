<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php $this->need('header.php'); ?>
<?php $this->need('sidebar.php'); ?>



<?php 
// 检查是否有 mp4 字段（旧数据兼容）
if($this->fields->mp4):
    // 使用新函数自动判断媒体类型
    if(function_exists('getPostMediaType')):
        $mediaType = getPostMediaType($this);
    else:
        // 如果函数不存在，尝试简单判断
        $mediaType = 'audio'; // 默认为音频
    endif;
    
    if($mediaType == 'video'):
?>
<div class="main_content_inner">
<?php $this->need('ze-video2.php'); ?>
<?php 
    else: // audio
?>
<div class="main_content_inner">
<?php $this->need('ze-video.php'); ?>
<?php 
    endif;
else: // 没有 mp4 字段，使用普通文章模板
?>
<?php $this->need('ze-post.php'); ?><?php get_post_view($this,1); ?>
<?php 
endif; 
?>




<?php $this->need('footer.php'); ?>
