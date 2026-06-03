<?php
if(@$_GET['action'] == 'get'){
if(@!$_COOKIE['history']){

setcookie('history', $this->cid,time()+3600*24*30,'/');

}else{

$list=explode(",",$_COOKIE['history']);
if(!in_array($this->cid,$list)){

if(count($list)>=60){
$c='';for($i=0;$i<49;++$i){$c=$c.','.$list[$i];}
setcookie('history', $c,time()+3600*24*30,'/');
}

setcookie('history', $this->cid.','.$_COOKIE['history'],time()+3600*24*30,'/');

}
}}

// 解析所有剧集
$allEpisodes = array();
if($this->fields->mp4){
    $spurl=$this->fields->mp4;
    if(strpos($spurl,'$') == false){
        $spurl='全集$'.$spurl;
    }
    if(strpos($spurl,'$') !== false){
        $string_arr = array_filter(explode("\r\n", $spurl));
        foreach($string_arr as $key => $line) {
            $parts = explode("$", $line);
            $allEpisodes[] = array(
                'title' => $parts[0],
                'url' => isset($parts[1]) ? $parts[1] : '',
                'zimu' => isset($parts[2]) ? $parts[2] : ''
            );
        }
    }
}
$currentEp = isset($_GET['p']) ? intval($_GET['p']) : 1;
$totalEps = count($allEpisodes);
?>

<style>
.episode-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(85px, 1fr)); 
    gap: 10px; 
    margin-bottom: 10px;
}
.episode-grid > a, .episode-grid > button {
    margin: 0 !important; 
    width: 100%;
    display: block; 
    text-align: center;
    white-space: nowrap; 
    overflow: hidden; 
    text-overflow: ellipsis; 
    box-sizing: border-box;
    text-decoration: none; 
}

/* --- 选中状态：默认（亮色模式）显示蓝色 --- */
.episode-grid .ep-active,
.episode-grid button.ep-active,
.episode-grid .button.ep-active {
    background-color: #1e87f0 !important;
    color: #ffffff !important;           
    opacity: 1 !important;               
    border: none !important;
    font-weight: bold !important;
    background: #1e87f0 !important;
}

/* --- 选中状态：night-mode 深色模式 --- */
.night-mode .episode-grid .ep-active,
.night-mode .episode-grid button.ep-active,
.night-mode .episode-grid .button.ep-active {
    background-color: #ff9800 !important;
    background: #ff9800 !important;
    color: #ffffff !important;
    box-shadow: 0 0 10px rgba(255, 152, 0, 0.6) !important;
    opacity: 1 !important;
    font-weight: bold !important;
    border: none !important;
}

/* --- 确保非选中按钮在深色模式下也正常 --- */
.night-mode .episode-grid .button,
.night-mode .episode-grid button {
    opacity: 1 !important;
}

@media (max-width: 640px) {
    .episode-grid {
        grid-template-columns: repeat(auto-fill, minmax(70px, 1fr));
        gap: 8px;
    }
}
</style>

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
$spurl=$text->fields->mp4;
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
$ep_name = explode("$",$string_arr[$i])[0]; 

if($j==$i&&($can==$xl||(!$_GET['xl']&&$c==1))){
$c_class="class=\"button small ep-active disabled\" data-ep=\"".$p."\"";
$list=$list."<button ".$c_class." title=\"".$ep_name."\">".$ep_name."</button>";
}else{
$c_class="class=\"button small soft-primary\" data-ep=\"".$p."\"";
$list=$list."<button ".$c_class." title=\"".$ep_name."\">".$ep_name."</button>";
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
$list= '<div class="episode-grid">'.$list.'</div>';
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
if($s==1){$xn="";}
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
<iframe width="100%" height="100%" src="<?php if(strpos($spurl,'player.bilibili.com')){echo $spurl.'&as_wide=1&high_quality=1&danmaku=0';}else{echo $spurl;} ?>" frameborder="0" border="0" marginwidth="0" marginheight="0" scrolling="no" allowfullscreen="allowfullscreen" mozallowfullscreen="mozallowfullscreen" msallowfullscreen="msallowfullscreen" oallowfullscreen="oallowfullscreen" webkitallowfullscreen="webkitallowfullscreen" sandbox="allow-top-navigation allow-same-origin allow-forms allow-scripts" allow="screen-wake-lock; fullscreen; picture-in-picture;"></iframe>
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

<iframe width="100%" height="100%" src="<?php echo $jx.$spurl; ?>" frameborder="0" border="0" marginwidth="0" marginheight="0" scrolling="no" allowfullscreen="allowfullscreen" mozallowfullscreen="mozallowfullscreen" msallowfullscreen="msallowfullscreen" oallowfullscreen="oallowfullscreen" webkitallowfullscreen="webkitallowfullscreen" allow="screen-wake-lock; fullscreen; picture-in-picture;"></iframe>
<?php else: ?>

<iframe id="video-iframe" width="100%" height="100%" src="<?php echo theurl.'jx.php?url='.$spurl.'&zimu='.$zimu.'&episodes='.urlencode(json_encode($allEpisodes)).'&currentIndex='.($currentEp-1); ?>" frameborder="0" border="0" marginwidth="0" marginheight="0" scrolling="no" allowfullscreen="allowfullscreen" mozallowfullscreen="mozallowfullscreen" msallowfullscreen="msallowfullscreen" oallowfullscreen="oallowfullscreen" webkitallowfullscreen="webkitallowfullscreen" allow="screen-wake-lock; fullscreen; picture-in-picture;"></iframe>

<?php endif; ?>  <?php endif; ?>  </div> <?php endif;?><?php endif;?>

</div>

<?php endif;?><?php endif;?>

                        <div class="video-info mt-3">

                            <div class="video-info-title">
<?php if ($_GET['action'] == 'get' && 'GET' == $_SERVER['REQUEST_METHOD']): ?>
<a href="<?php $this->permalink(); ?>"><?php endif;?>

<h1><font style="vertical-align: inherit;"><font style="vertical-align: inherit;"><?php $this->title() ?></font></font></h1>
<?php if ($_GET['action'] == 'get' && 'GET' == $_SERVER['REQUEST_METHOD']): ?></a><?php endif;?>
                            
</div>

<?php if ($_GET['action'] == 'get' && 'GET' == $_SERVER['REQUEST_METHOD']): ?>
                            <div class="uk-flex uk-flex-between">

                                <div class="video-info-details">
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
<li class="uk-active"><a href="#" aria-expanded="true">简介</a></li></ul><?php endif;?>

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
                                        <li class="uk-active">
<?php echo $list; ?></li>
                      
                                        
                                    </ul>

                        </div>

<?php if(!$this->options->addie||!in_array($this->cid,explode(",", $this->options->addie))): ?>
<?php if(!$this->request->isAjax()): ?><?php if($this->options->ad): ?>
<?php $this->options->ad(); ?>
<?php endif; ?><?php endif; ?><?php endif; ?>
<hr>

<?php $this->need('comments.php'); ?>

                    </div>
 
<?php $this->need('post-sidebar.php'); ?>
                </div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var episodes = <?php echo json_encode($allEpisodes); ?>;
    var currentIndex = <?php echo $currentEp - 1; ?>;
    var iframe = document.getElementById('video-iframe');
    var xlParam = <?php echo isset($_GET['xl']) ? json_encode('&xl='.$_GET['xl']) : "''" ?>;
    var isPlayingMode = <?php echo (isset($_GET['action']) && $_GET['action'] == 'get' && $_SERVER['REQUEST_METHOD'] == 'GET') ? 'true' : 'false' ?>;
    var baseUrl = '<?php echo $this->permalink(); ?>';
    
    if (episodes.length > 0) {
        if (isPlayingMode) {
            window.addEventListener('message', function(event) {
                if (event.data && event.data.action === 'episodeChange') {
                    currentIndex = event.data.index;
                    updateEpisodeList();
                    updateURL();
                }
            });
        }
        
        function updateEpisodeList() {
            var buttons = document.querySelectorAll('.episode-grid button');
            buttons.forEach(function(btn, idx) {
                // 彻底清除所有可能的类，避免残留样式
                btn.classList.remove('ep-active', 'disabled', 'soft-primary', 'button', 'small');
                
                // 重新添加基础类
                btn.classList.add('button', 'small', 'soft-primary');
                
                if (idx === currentIndex) {
                    // 选中状态
                    btn.classList.add('ep-active', 'disabled');
                    btn.classList.remove('soft-primary');
                }
            });
        }
        
        function updateURL() {
            var newURL = baseUrl + '?action=get&p=' + (currentIndex + 1) + xlParam + window.location.hash;
            window.history.pushState({path: newURL}, '', newURL);
        }
        
        function handleButtonClick(btn, idx) {
            var epNum = idx;
            currentIndex = epNum;
            
            if (isPlayingMode) {
                // 播放模式下，更新 UI 并通知 iframe
                updateEpisodeList();
                updateURL();
                if (iframe && iframe.contentWindow) {
                    iframe.contentWindow.postMessage({
                        action: 'playEpisode',
                        index: epNum
                    }, '*');
                }
            } else {
                // 非播放模式下，直接跳转到播放页面
                var newURL = baseUrl + '?action=get&p=' + (currentIndex + 1) + xlParam;
                window.location.href = newURL;
            }
        }
        
        function bindButtonEvents() {
            var buttons = document.querySelectorAll('.episode-grid button');
            buttons.forEach(function(btn, idx) {
                btn.onclick = function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    handleButtonClick(btn, idx);
                };
            });
        }
        
        // 确保初始状态正确
        updateEpisodeList();
        bindButtonEvents();
        
        setTimeout(bindButtonEvents, 100);
        setTimeout(bindButtonEvents, 500);
    }
});
</script>