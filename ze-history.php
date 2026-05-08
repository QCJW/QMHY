<?php 
/**
 * 观看历史
 * 
 * @package custom 
 * 
 */
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
$this->need('header.php');
$this->need('sidebar.php'); 

$history='';


if($_COOKIE['history']){
$history=$_COOKIE['history'];
}

?>
<div class="main_content_inner">


<div uk-grid="" class="uk-grid uk-grid-stack">

<div class="uk-width-expand uk-first-column">
<div class="section-header mb-lg-5 border-0 uk-flex-middle">
<div class="section-header-left">
<h3 class="uk-heading-line text-left"><span> 观看历史 </span></h3>
</div>
<div class="section-header-right">
<a target="_self" href="javascript:void(0)" onclick="HistoryClear();">清空</a>
</div>
</div>

<div class="history uk-child-width-1-5@l uk-child-width-1-4@m uk-child-width-1-3@s uk-child-width-1-2" uk-grid>

<?php $this->widget('Widget_Post_fanjubiao@history', 'fanjubiao='.$history)->to($hot); ?>
<?php if($hot->have()&&$history!=null&&$history!=''): ?>
<?php while($hot->next()): ?>
<div tabindex="-1" class="uk-animation-slide-bottom-small">
<a href="<?php $hot->permalink(); ?><?php if($hot->fields->mp4&&strlen($hot->fields->mp4) > 10){ echo '?action=get&p=1';}?>" class="video-post">
<div class="media media-16x9">
<span class="video-post-count"><font style="vertical-align: inherit;"><font style="vertical-align: inherit;"><?php get_post_view($hot); ?></font></font></span>
<div class="media-content scrollLoading" data-xurl="<?php showThumbnail($hot); ?>"></div>
</div>
<div class="video-post-content">
<h3><font style="vertical-align: inherit;"><font style="vertical-align: inherit;"><?php $hot->title(); ?></font></font></h3>
</div></a></div> 

<?php endwhile; ?>
<?php else: ?><div>暂无历史记录</div> <?php endif; ?>




</div>







</div>
</div>


    
   
    
    
    
<?php $this->need('footer.php'); ?>
