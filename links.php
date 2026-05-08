<?php 
/**
 * 友情链接模板
 * 
 * @package custom 
 * 
 */
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
$this->need('header.php');
$this->need('sidebar.php'); ?>
<div class="main_content_inner">
    
    
<h3> 友情链接 </h3>


<div class="uk-child-width-1-5@m uk-child-width-1-3 uk-grid" style="min-height: 75vh;" >

<?php Links_Plugin::output('<div>
<a href="{url}" target="_blank" title="{title}">
</a><div class="single-channal"><a href="{url}" target="_blank" title="{title}">
<div class="links">
<img src="{image}" alt="">
</div>
</a><div class="single-channal-body"><a href="{url}" target="_blank" title="{title}">
<h4>{name}</h4>
</a><a href="{url}" target="_blank" title="{title}" class="button soft-primary small circle"> <i class="uil-bell"></i> 访问 </a>
</div></div></div>'); ?>  





</div>
    
   
    
    
    
<?php $this->need('footer.php'); ?>
