<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php $this->need('header.php'); ?>
<?php $this->need('sidebar.php'); ?>


<?php 


if(!$this->author->uid > 0){
$str=$_SERVER["REQUEST_URI"];
    if(preg_match('/\d+/',$str,$arr)){
       $id=$arr[0];
$info=userok($id);
$this->author->uid=$id;
$this->author->screenName=$info['screenName'];
$this->author->mail=$info['mail'];
$this->author->group=$info['group'];
}}
//上代码是解决作者主页没有文章时无法直接获取作者信息的bug



$ta='';
if(!Typecho_Widget::widget('Widget_User')->uid>0||Typecho_Widget::widget('Widget_User')->uid!=$this->author->uid){$ta='Ta';
}


//判断访问作者页面的人是否为作者本人

?>





<div class="channal">
<div class="channal-cover bg-gradient-primary">
</div>
<div class="main_content_inner">
<div class="channal-details">
<div class="left-side">
<div class="channal-image">
<a href="#">
<img src="<?php tx($this->author->mail); ?>" alt="">
</a>
</div>
<div class="channal-details-info">
<h3> <?php $this->author->screenName(); ?> </h3>
<p> <?php $this->author->mail(); ?></p>
</div>
</div>

<div class="right-side">
<div class="btn-subscribe">
<a href="#" class="button primary"> <i class="uil-star"></i> 收藏
</a>
<span class="subs-amount"><?php collectzu($this->author->uid,1); ?></span>
</div>
</div>


</div>
<div class="nav-channal" k-sticky="offset:61;media : @s">
<nav class="responsive-tab">
<ul data-submenu-title="compounents" uk-switcher="connect: #components-nav ;animation: uk-animation-slide-bottom-medium, uk-animation-slide-bottom-medium">
<li class="uk-active"><a class="active" href="#0">稿件</a></li>
<li><a href="#0">收藏</a></li>
</ul>
</nav>
<!--<form class="nav-channal-saerchbox">
<i class="uil-search"></i>
<input class="uk-input" type="text" value="搜索他的内容...">
</form>-->
</div>
</div>
</div>







<div class="main_content_inner">
<ul class="uk-switcher" id="components-nav">
<li><!--稿件-->
<div class="section-small">

<div class="uk-child-width-1-5@l uk-child-width-1-4@m uk-child-width-1-3@s uk-child-width-1-2" uk-grid uk-scrollspy="target: > div; cls: uk-animation-slide-bottom-small; delay: 100">

<?php if ($this->have()): ?>
<?php while($this->next()): ?>
<?php 
$num=0;
if($this->fields->postType == 'video'){
$spurl=$this->fields->toolgo;
if($this->fields->toolgo && strpos($this->fields->toolgo,'$') == false){$spurl='全集$'.$spurl;}
$num=substr_count($spurl,'$');
}
elseif($this->fields->postType && strpos($this->fields->postType,'photo') != false){
$num=getPostHtmImg($this,1);
}
?>


<div>
<a href="<?php $this->permalink(); ?><?php if($this->fields->mp4&&strlen($this->fields->mp4) > 10){ echo '?action=get&p=1';}?>" class="video-post">

<div class="video-post-thumbnail">
<span class="video-post-count"><?php get_post_view($this); ?></span>
<?php if($num!=0): ?>
<span class="video-post-time"><?php echo $num; ?>P</span>
<?php endif; ?> 
<?php if($this->fields->postType == 'video'): ?><span class="play-btn-trigger"></span><?php endif; ?> 

<img src="<?php showThumbnail($this); ?>" alt="">
</div>

<div class="video-post-content">
<h3><?php $this->title() ?></h3>
</div>
</a>
</div>



<?php endwhile; ?>
<?php else: ?><div><?php echo $ta; ?>没有投递任何稿件</div><?php endif; ?>




</div>









<nav class="navigation pagination" role="navigation">
<?php $this->pageNav('<span class="uk-icon uk-pagination-next"><i class="uil uil-angle-left"></i></span>', '<span class="uk-icon uk-pagination-next"><i class="uil uil-angle-right"></i></span>', 3, '...', array('wrapTag' => 'div', 'wrapClass' => 'nav-links', 'itemTag' => '','itemClass' => '', 'aClass'=>'page-numbers','textTag' => 'li','textClass' => 'page-numbers', 'currentClass' => 'page-numbers current', 'prevClass' => 'page-numbers prev', 'nextClass' => 'page-numbers next',)); ?>
</nav>
</li>
<li><!--收藏-->
<div class="section-small">

<div class="uk-child-width-1-5@l uk-child-width-1-4@m uk-child-width-1-3@s uk-child-width-1-2" uk-grid uk-scrollspy="target: > div; cls: uk-animation-slide-bottom-small; delay: 100">

<?php 

$shoucang=collectzu($this->author->uid);
$this->widget('Widget_Post_fanjubiao@shoucang', 'fanjubiao='.$shoucang)->to($sc);
if ($sc->have()): ?>
<?php while($sc->next()): ?>
<?php 
$num=0;
if($sc->fields->postType == 'video'){
$spurl=$sc->fields->toolgo;
if($sc->fields->toolgo && strpos($sc->fields->toolgo,'$') == false){$spurl='全集$'.$spurl;}
$num=substr_count($spurl,'$');
}
elseif($sc->fields->postType && strpos($sc->fields->postType,'photo') != false){
$num=getPostHtmImg($sc,1);
}
?>


<div>
<a href="<?php $sc->permalink(); ?><?php if($sc->fields->mp4&&strlen($sc->fields->mp4) > 10){ echo '?action=get&p=1';}?>" class="video-post">

<div class="video-post-thumbnail">
<span class="video-post-count"><?php get_post_view($sc); ?></span>
<?php if($num!=0): ?>
<span class="video-post-time"><?php echo $num; ?>P</span>
<?php endif; ?> 
<?php if($sc->fields->postType == 'video'): ?><span class="play-btn-trigger"></span><?php endif; ?> 

<img src="<?php showThumbnail($sc); ?>" alt="">
</div>

<div class="video-post-content">
<h3><?php $sc->title() ?></h3>
</div>
</a>
</div>



<?php endwhile; ?>
<?php else: ?><div><?php echo $ta; ?>还没有收藏视频</div><?php endif; ?>




</div>








</li>
</ul>
</div>


<?php $this->need('footer.php'); ?>
