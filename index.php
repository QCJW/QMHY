<?php
/**
 * 相遇是一种缘分,相识是一种幸运！<br>使用文档：<a href="https://www.yuque.com/qqdie/ys2/hu649o" target="_blank" rel="noopener noreferrer">https://www.yuque.com/qqdie/ys2/hu649o</a><br>
<style>.typecho-theme-list tbody #theme-yingshierhao.current td {background-color: #e8ecf3;}</style>
 * 
 * @package yingshierhao
 * @author 泽泽社长
 * @version 3.3.3
 * @link https://store.typecho.work/archives/typecho-film-theme.html
 */
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

if(isset($_GET['huancun'])&&$this->user->pass('administrator', true)){
$text=file_get_contents(rooturl.'?f5=1');
$str1 = explode('/themes/', Helper::options()->themeUrl);
$str2 = explode('/', $str1[1]);
$name=$str2[0];
//去除換行及空白字元（序列化內容才需使用）
$text=str_replace(array("\r","\n","\t","\s"), '', $text); 
 
//取出div标签且id為content的內容，並储存至阵列match
preg_match('/<!--cache-->(.*?)<!--cacheend-->/si',$text,$match);
$file='.'.__TYPECHO_THEME_DIR__.'/'.$name.'/cache-index.php';
file_put_contents($file, $match[0]);
echo '网页缓存生成完毕！<br><a href="'.rooturl.'">点此返回首页</a>';
exit;
}



if(isset($_GET['setting'])&&$this->user->hasLogin()){
 $this->need('ze-setting.php');exit;
}

if(isset($_GET['login'])&&!$this->user->hasLogin()){
 $this->need('login.php');exit;
}
if(isset($_GET['register'])&&!$this->user->hasLogin()&&$this->options->allowRegister){
 $this->need('register.php');exit;
}

 $this->need('header.php');
$this->need('sidebar.php');
 include 'config.php';
 ?>
<?php if(isset($_GET['f5']) || !(!empty($this->options->tools) && in_array('cache',$this->options->tools))){ ?>
<!--cache-->
<div class="main_content_inner">


<?php if($this->options->gg): ?>
<div class="uk-alert" uk-alert>
<!--<a class="uk-alert-close" uk-close></a>-->
<p><?php $this->options->gg(); ?></p>
</div>
<?php endif; ?>


<?php if($this->options->lunbo): ?>
<?php 
$txt=$this->options->lunbo;
$string_arr = explode("\r\n", $txt);
$long=count($string_arr);
?>



                <!-- Slideshow -->
                <div class="uk-position-relative uk-visible-toggle uk-light" tabindex="-1"
                    uk-slideshow="animation: push ;min-height: 200; max-height: 350 ;autoplay: t rue">

                    <ul class="uk-slideshow-items rounded">


<?php 
for($i=0;$i<$long;$i++){$av="";if($i==0){$av=' class="uk-active uk-transition-active"';}
$id=explode("$",$string_arr[$i])[0];
$tu=explode("$",$string_arr[$i])[1];
$this->widget('Widget_Archive@lunbo'.$i, 'pageSize=1&type=single', 'cid='.$id)->to($ji);
$zhuijia="";
if(strlen($ji->fields->mp4) > 10){ $zhuijia="?action=get&p=1";}

echo '<li><a href="'.$ji->permalink.$zhuijia.'">
                            <div class="uk-position-cover" uk-slideshow-parallax="scale: 1.2,1.2,1">
                                <img src="'.$tu.'" alt="" uk-cover>
                            </div>
                            <div class="uk-position-cover"
                                uk-slideshow-parallax="opacity: 0,0,0.2; backgroundColor: #000,#000"></div>
                            <div class="uk-position-bottom-left bg-gradient-4 uk-width-1-1 p-4">
                                <div uk-slideshow-parallax="scale: 1,1,0.8">
                                    <h3 uk-slideshow-parallax="x: 200,0,0"> '.$ji->title.'
                                    </h3>
                                </div>
                            </div></a>
                        </li>';
}
?>











                    </ul>

                    <a class="uk-position-center-left-out uk-position-small uk-hidden-hover slidenav-prev" href="#"
                        uk-slideshow-item="previous"></a>
                    <a class="uk-position-center-right-out uk-position-small uk-hidden-hover slidenav-next" href="#"
                        uk-slideshow-item="next"></a>



                </div>

<?php endif; ?>








<?php

if(!(empty($week0)&&empty($week1)&&empty($week2)&&empty($week3)&&empty($week4)&&empty($week5)&&empty($week6))){

?>

<div class="mt-5">
<ul  id="fanjubiao" uk-tab class="uk-tab mt-0" uk-switcher="animation: uk-animation-slide-left-medium, uk-animation-slide-right-medium" active="<?php echo date("w"); ?>">
<li><a href="#week-0"> <span>周日</span></a></li>
<li><a href="#week-1"> <span>周一</span></a></li>
<li><a href="#week-2"> <span>周二</span> </a> </li>
<li><a href="#week-3"> <span>周三</span></a></li>
<li><a href="#week-4"> <span>周四</span></a></li>
<li><a href="#week-5"> <span>周五</span></a></li>
<li><a href="#week-6"> <span>周六</span>
</a></li>
</ul>

<ul class="uk-switcher uk-margin uk-padding-small pt-0 pl-0" style="touch-action: pan-y pinch-zoom;">



<?php



for($i=0;$i<7;$i++){

?>

<li id="week-<?php echo $i; ?>">

<div class="uk-child-width-1-6@l uk-child-width-1-6@m uk-child-width-1-4@s uk-child-width-1-2 uk-grid uk-grid-stack" uk-grid">

<?php 
if(${'week'.$i}!=null||${'week'.$i}!=''){
$this->widget('Widget_Post_fanjubiao@week'.$i, 'fanjubiao='.${'week'.$i})->to($lianzai); ?>
<?php while($lianzai->next()): ?>

<div tabindex="-1" class="uk-animation-slide-bottom-small">
<a href="<?php $lianzai->permalink(); ?><?php if($lianzai->fields->mp4&&strlen($lianzai->fields->mp4) > 10){ echo '?action=get&p=1';}?>" class="video-post">
<div class="media media-16x9">
<span class="video-post-time"><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">
<?php fanbiao($lianzai); ?></font></font></span>
<div class="media-content" style="background-image: url(&quot;<?php showThumbnail($lianzai); ?>&quot;);"></div>
</div>
<div class="video-post-content">
<h3><font style="vertical-align: inherit;"><font style="vertical-align: inherit;"><?php $lianzai->title(); ?></font></font></h3>
</div></a></div>  

<?php endwhile;}else{echo '<div>暂无番剧或未配置番剧更新表</div>';} ?>

 </div>      
</li>
<?php } ?>
       
</ul></div>
<?php } ?>








<?php 
$n=count($flmid);if($n>0){
for($i=0;$i<$n;$i++){
$this->widget('Widget_Archive@fy'.$i, 'pageSize=12&type=category&orderBy=modified', 'mid='.$flmid[$i][0])->to($dy); ?>
<div class="grid-slider-header mt-5">
                    <div class="section-header-left">
                        <h3><font style="vertical-align: inherit;"><font style="vertical-align: inherit;"><?php echo $flmid[$i][1]; ?></font></font></h3>
                    </div>
                    <div class="section-header-right">
                        <a href="<?php echo $dy->categories[0]['permalink']; ?>" class="see-all"><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">更多</font></font></a>
                    </div>
                </div>


<div class="uk-child-width-1-6@l uk-child-width-1-6@m uk-child-width-1-4@s uk-child-width-1-2 uk-grid uk-grid-stack" uk-grid">
<?php while ($dy->next()): ?>
<li tabindex="-1" class="uk-animation-slide-bottom-small">
<a href="<?php $dy->permalink(); ?><?php if(strlen($dy->fields->mp4) > 10){ echo '?action=get&p=1';}?>" class="video-post">
<div class="media media-16x9">
<span class="video-post-time"><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">
<?php fanbiao($dy); ?></font></font></span>

<div class="media-content scrollLoading" data-xurl="<?php showThumbnail($dy); ?>"></div>
</div>
<div class="video-post-content">
<h3><font style="vertical-align: inherit;"><font style="vertical-align: inherit;"><?php $dy->title(); ?></font></font></h3>
</div></a></li>   
<?php endwhile; ?>
</div>
<?php }} ?>







<?php if (empty(Helper::options()->tools) || !in_array('ycnewadd',Helper::options()->tools)): ?>
<?php $this->widget('Widget_Contents_Post_Recent', 'pageSize=8')->to($new); ?>
<h4 class="mb-2">最新加入</h4>
<div class="uk-child-width-1-8@l uk-child-width-1-6@m uk-child-width-1-3@s uk-child-width-1-3 uk-grid uk-grid-stack" uk-grid">
<?php while ($new->next()): ?>

<li tabindex="-1" class="uk-animation-slide-bottom-small<?php if ($new->sequence >6): ?>  uk-visible@l<?php endif; ?>">
<a href="<?php $new->permalink(); ?><?php if($new->fields->mp4&&strlen($new->fields->mp4) > 10){ echo '?action=get&p=1';}?>" class="video-post">
                                    <!-- Blog Post Thumbnail -->
                                
    <div class="media media-3x4">
<span class="video-post-time"><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">
<?php fanbiao($new); ?></font></font></span>




<div class="media-content scrollLoading" data-xurl="<?php showThumbnail($new); ?>"></div>

    </div>

<div class="video-post-content">
                                        <h3><font style="vertical-align: inherit;"><font style="vertical-align: inherit;"><?php $new->title(); ?></font></font></h3>
                                    </div>
                                </a>
                            </li>   




<?php endwhile; ?>
</div>
<?php endif; ?>



<?php 
$n=count($dymid);if($n>0){
for($i=0;$i<$n;$i++){
$this->widget('Widget_Archive@dy'.$i, 'pageSize=12&type=tag&orderBy=modified', 'mid='.$dymid[$i][0])->to($dy); ?>
<div class="grid-slider-header mt-5">
                    <div class="section-header-left">
                        <h3><font style="vertical-align: inherit;"><font style="vertical-align: inherit;"><?php echo $dymid[$i][1]; ?></font></font></h3>
                    </div>
                    <div class="section-header-right">
                        <a href="<?php echo $dy->categories[0]['permalink']; ?>" class="see-all"><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">更多</font></font></a>
                    </div>
                </div>


<div class="uk-child-width-1-6@l uk-child-width-1-6@m uk-child-width-1-4@s uk-child-width-1-2 uk-grid uk-grid-stack" uk-grid">
<?php while ($dy->next()): ?>
<li tabindex="-1" class="uk-animation-slide-bottom-small">
<a href="<?php $dy->permalink(); ?><?php if(strlen($dy->fields->mp4) > 10){ echo '?action=get&p=1';}?>" class="video-post">
<div class="media media-16x9">
<span class="video-post-time"><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">
<?php fanbiao($dy); ?></font></font></span>

<div class="media-content scrollLoading" data-xurl="<?php showThumbnail($dy); ?>"></div>
</div>
<div class="video-post-content">
<h3><font style="vertical-align: inherit;"><font style="vertical-align: inherit;"><?php $dy->title(); ?></font></font></h3>
</div></a></li>   
<?php endwhile; ?>
</div>
<?php }} ?>



<!-- 横幅广告 -->
<?php if(empty($this->options->addie)||!in_array($this->cid,explode(",", $this->options->addie))): ?>
<?php if(!$this->request->isAjax()): ?><?php if($this->options->adh): ?>
<?php $this->options->adh(); ?>
<?php endif; ?><?php endif; ?><?php endif; ?>
<!-- 横幅广告 -->

<!--cacheend-->

<?php }else{
$this->need('cache-index.php');
} ?>



<?php $this->need('footer.php'); ?>