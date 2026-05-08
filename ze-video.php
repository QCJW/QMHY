<?php
if(@$_GET['action'] == 'get'){
if(@!$_COOKIE['history']){

setcookie('history', $this->cid,time()+3600*24*30,'/');

}else{

$list=explode(",",$_COOKIE['history']);
if(!in_array($this->cid,$list)){

if(count($list)>=60){//当历史记录存储到了60条时，清楚后10条
$c='';for($i=0;$i<49;++$i){$c=$c.','.$list[$i];}
setcookie('history', $c,time()+3600*24*30,'/');
}

setcookie('history', $this->cid.','.$_COOKIE['history'],time()+3600*24*30,'/');

}
}}
?>







<div uk-grid="" class="uk-grid">
                    <div class="uk-width-3-4@m uk-first-column">







<?php if ($this->fields->toolgo||$this->fields->mp4): ?>
<?php
$duoji="";
$list="";
if($this->fields->duoji && strpos($this->fields->duoji,'$') !== false){

$hang = array_filter(explode("\r\n", $this->fields->duoji));
$shu=count($hang);

for($i=0;$i<$shu;$i++){
$cid=explode("$",$hang[$i])[1];
$this->widget('Widget_Archive@duoji'.$cid, 'pageSize=1&type=post', 'cid='.$cid)->to($ji); 

if($ji->cid==$this->cid){
$duoji=$duoji."<span class=\"ml-1 uk-text-small p-1 uk-text-secondary\">".explode("$",$hang[$i])[0]."</span>";
}else{
$duoji=$duoji."<a href=\"".$ji->permalink."\" class=\"ml-1 uk-text-small p-1\">".explode("$",$hang[$i])[0]."</a>";
}
}

}





function jishulist($text,$type=0,$can=null,$c=0) {

if($text->fields->mp4){
$spurl=$text->fields->mp4;//后期可以从这里想办法拓展视频源2
}


if(strpos($spurl,'$') == false){
$spurl='全集$'.$spurl;
}

$sptitle=0;
$x=0;

if(strpos($spurl,'$') !== false){

$j=-1;
if(isset($_GET['action']) == 'get' && 'GET' == $_SERVER['REQUEST_METHOD'] ) {
$j=$_GET['p']-1;
}

$txt=$spurl;

$string_arr = array_filter(explode("\r\n", $txt));
$long=count($string_arr);
$list="";
for($i=0;$i<$long;$i++){
$xl=null;
if(@$_GET['xl']){
$xl="&xl=".$_GET['xl'];
}

$p=$i+1;
if($j==$i&&($can==$xl||(!$_GET['xl']&&$c==1))){
$c="class=\"button small soft-dark disabled mr-2 mb-2\"";
$list=$list."<button ".$c.">".explode("$",$string_arr[$i])[0]."</button>";
}else{

$c="class=\"button small soft-primary mr-2 mb-2\"";
$list=$list."<a href=\"".$text->permalink."?action=get&p=".$p.$can."\"><button ".$c.">".explode("$",$string_arr[$i])[0]."</button></a>";
}



}
if($type==0){
return @explode("$",$string_arr[$j])[1];
}elseif($type==2){

if(isset(explode("$",$string_arr[$j])[2])){
return explode("$",$string_arr[$j])[2];}else{
return '';
}

}
else{
$list= '<div class="uk-margin">'.$list.'</div>';
return $list;
}



}
}




$spurl=jishulist($this,0);

$zimu=jishulist($this,2);


$list='';





if($this->options->jxurl){
$jxurl=$this->options->jxurl;
$h = explode("\r\n", $jxurl);
$s=count($h);
for($i=0;$i<$s;$i++){
$xn=explode("$",$h[$i])[0]."<br>";
if($s==1){$xn="";}//如果只有一个自定义解析线路时隐藏线路名字
$p=$i+1;
$xl="&xl=".$p;
$list=$list.$xn.jishulist($this,1,$xl,$p); 

}
}else{
$list=jishulist($this,1);    
    
}
?>



<?php if (($_GET['action'] == 'get' && 'GET' == $_SERVER['REQUEST_METHOD'])): ?>
<div id="video-box" uk-sticky="top: 400 ;media : @s" cls-active="video-resized uk-animation-slide-right;" class="uk-sticky">
<span class="icon-feather-x btn-box-close" uk-toggle="target: #video-box ; cls: video-resized-hedden uk-animation-slide-left"></span>
<?php if(!$this->user->hasLogin() && $this->options->login>0 && $this->options->login<=$_GET['p']):?>
<div class="uk-alert-danger" uk-alert>
<p>从本集起，后续内容需要注册登录本站后才可观看！</p>
</div>
<?php else: ?>
<?php if($this->hidden||$this->titleshow): ?>
<div class="uk-alert-danger" uk-alert>
<p>本视频栏目已加密，请在下方输入正确密码观看！</p>
</div>
<?php else: ?>
<div class="embed-video">


<?php if(strpos($spurl,'player.bilibili.com') !== false||strpos($spurl,'www.acfun.cn/player') !== false||strpos($spurl,'v.qq.com/txp/iframe') !== false||strpos($spurl,'open.iqiyi.com/developer/player_js') !== false||$zimu=="iframe"): ?>
<style>
.embed-video{
padding-bottom:68%;
}
</style>
<iframe width="100%" height="100%" src="<?php if(strpos($spurl,'player.bilibili.com')){echo $spurl.'&as_wide=1&high_quality=1&danmaku=0';}else{echo $spurl;} ?>" frameborder="0" border="0" marginwidth="0" marginheight="0" scrolling="no" allowfullscreen="allowfullscreen" mozallowfullscreen="mozallowfullscreen" msallowfullscreen="msallowfullscreen" oallowfullscreen="oallowfullscreen" webkitallowfullscreen="webkitallowfullscreen" sandbox="allow-top-navigation allow-same-origin allow-forms allow-scripts"></iframe>
<?php else: ?>
<?php if ($this->options->jxurl): ?>

<?php
$jxurl=$this->options->jxurl;
$h = explode("\r\n", $jxurl);

if($_GET['xl']){
$xl=$_GET['xl']-1;
}else{
$xl=0;
}


$jx=explode("$",$h[$xl])[1];
?>


<iframe width="100%" height="100%" src="<?php echo $jx.$spurl; ?>" frameborder="0" border="0" marginwidth="0" marginheight="0" scrolling="no" allowfullscreen="allowfullscreen" mozallowfullscreen="mozallowfullscreen" msallowfullscreen="msallowfullscreen" oallowfullscreen="oallowfullscreen" webkitallowfullscreen="webkitallowfullscreen"></iframe>
<?php else: ?>

<iframe width="100%" height="100%" src="<?php echo theurl.'jx.php?url='.$spurl.'&zimu='.$zimu; ?>" frameborder="0" border="0" marginwidth="0" marginheight="0" scrolling="no" allowfullscreen="allowfullscreen" mozallowfullscreen="mozallowfullscreen" msallowfullscreen="msallowfullscreen" oallowfullscreen="oallowfullscreen" webkitallowfullscreen="webkitallowfullscreen"></iframe>



<?php endif; ?>  <?php endif; ?>  </div> <?php endif;?><?php endif;?>

</div>

<?php endif;?><?php endif;?>

  






                        <div class="video-info mt-3">

                            <!-- video title -->
                            <div class="video-info-title">
<?php if ($_GET['action'] == 'get' && 'GET' == $_SERVER['REQUEST_METHOD']): ?>
<a href="<?php $this->permalink(); ?>"><?php endif;?>

<h1><font style="vertical-align: inherit;"><font style="vertical-align: inherit;"><?php $this->title() ?></font></font></h1>
<?php if ($_GET['action'] == 'get' && 'GET' == $_SERVER['REQUEST_METHOD']): ?></a><?php endif;?>
                            
</div>


<?php if ($_GET['action'] == 'get' && 'GET' == $_SERVER['REQUEST_METHOD']): ?>
                            <div class="uk-flex uk-flex-between">

                                <div class="video-info-details">
                                    <span><font style="vertical-align: inherit;"><font style="vertical-align: inherit;"><?php get_post_view($this); ?> views</font></font></span>
                                </div>
                                <div class="video-likes">

                          
 <a href="javascript:;" data-action="like" data-id="<?php $this->cid(); ?>" class="btn-like">
 <div class="like-btn" aria-expanded="false">

                                        <i class="uil-thumbs-up"></i>
                                        <span class="likes"><font class="like-count"><?php likeup($this->cid,'kkb'); ?></font></span>
                                    </div>
</a>



                                </div>

                            </div>



<?php endif;?>




<div>
<?php if ($_GET['action'] == 'get' && 'GET' == $_SERVER['REQUEST_METHOD']): ?>
<ul uk-tab="" class="uk-tab mt-0"  uk-switcher="animation: uk-animation-slide-left-medium, uk-animation-slide-right-medium">
<li class="uk-active"><a href="#" aria-expanded="true">简介</a></li><!--<li><a href="#">其他</a></li>-->
</ul><?php endif;?>


<div class="mb-3 mr-3 uk-card uk-float-left">
<div class="media media-10x14" style="width: 100px;max-width: 30vw;">
<div class="media-content scrollLoading ojbk" style="background-image: url(&quot;<?php showThumbnail($this); ?>&quot;);"></div>
</div></div>

<div class="uk-card">

<?php 
$sc=0;
if(collect($this->cid,Typecho_Widget::widget('Widget_User')->uid,1)=="ko"){$sc=1; }
 ?>
<a href="javascript:;" data-action="collect" data-id="<?php $this->cid(); ?>" class="uk-float-right btn-collect mr-3<?php if($sc==1){echo " current";} ?>"><i class="uil-star"></i></a>


<p class="mt-0">年代：<?php if($this->fields->niandai){ $this->fields->niandai();} ?><br>
类型：<?php $this->tags(' / ', true, 'none'); ?><br>

状态：<?php if($this->fields->zhuangtai>0){echo '连载中';}else{
if($this->fields->zhuangtai==-1){echo '待定';}else{echo '完结';}
} ?>

</p>

<?php if($this->hidden||$this->titleshow): ?>
<form action="<?php echo Typecho_Widget::widget('Widget_Security')->getTokenUrl($this->permalink); ?>" method="post" class="protected">
<div>
<span class="uk-text-middle uk-text-danger">当前视频需要输入密码才能观看</span>
</div>
<div class="uk-margin-small">
<div uk-form-custom="target: true" class="uk-form-custom uk-first-column">
<input class="uk-input"  name="protectPassword" type="password" placeholder="请输入密码">
</div>
<input type="hidden" name="protectCID" value="<?php $this->cid(); ?>" />
<button class="uk-button uk-button-default" type="submit">提交</button>
</div>
</form>

<?php else: ?><?php $this->content(); ?>
<?php endif;?>

</div>
<div class="clear"></div>


</div>












<ul uk-tab="" class="uk-tab mt-0"  uk-switcher="animation: uk-animation-slide-left-medium, uk-animation-slide-right-medium">
                                    <li class="uk-active"><a href="#" aria-expanded="true" class="uk-text-small">剧集</a></li><?php echo $duoji; ?>

                                </ul>

<ul class="uk-switcher uk-margin uk-padding-small pt-0 pl-0">
                                        <!-- tab 1 -->
                                        <li class="uk-active">
<?php echo $list; ?></li>
                      
                                        
                                    </ul>








                        </div>


<!-- 横幅广告 -->
<?php if(!$this->options->addie||!in_array($this->cid,explode(",", $this->options->addie))): ?>
<?php if(!$this->request->isAjax()): ?><?php if($this->options->ad): ?>
<?php $this->options->ad(); ?>
<?php endif; ?><?php endif; ?><?php endif; ?>
<!-- 横幅广告 -->


                        <hr>

<?php $this->need('comments.php'); ?>





                    </div>
 
<?php $this->need('post-sidebar.php'); ?>
                </div>

