<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php $this->need('header.php'); ?>
<div id="wrapper">



<div uk-height-viewport="expand: true" class="uk-flex uk-flex-middle">
<div class="uk-width-1-2@m uk-width-1-2@s m-auto text-center">
<img src="<?php echo theurl; ?>assets/images/maintenance.svg" alt="" class="my-3">
<h3>404</h3>
<p class="mb-0"> 你想查看的页面已被转移或删除了 </p>
<a href="<?php $this->options->rootUrl(); ?>" class="button primary transition-3d-hover my-4 small" uk-toggle>
<i class="icon-feather-clock mr-2"></i> 返回首页</a>
</div>
</div>



<?php $this->need('footer.php'); ?>
