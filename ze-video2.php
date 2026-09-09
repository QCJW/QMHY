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
    cursor: pointer;
}

/* 选中状态：亮色模式 */
.episode-grid .ep-active {
    background-color: #1e87f0 !important;
    color: #ffffff !important;           
    opacity: 1 !important;
    border: none !important;
    font-weight: bold;
}

/* 选中状态：暗色模式 */
@media (prefers-color-scheme: dark) {
    .episode-grid .ep-active {
        background-color: #ff9800 !important;
        color: #ffffff !important;
        box-shadow: 0 0 10px rgba(255, 152, 0, 0.6) !important;
    }
    .episode-grid .soft-primary {
        background-color: rgba(255,255,255,0.08) !important;
        color: #ccc !important;
    }
}
html.night-mode .episode-grid .ep-active,
html.dark .episode-grid .ep-active,
body.dark-mode .episode-grid .ep-active,
.uk-light .episode-grid .ep-active {
    background-color: #ff9800 !important;
    color: #ffffff !important;
    box-shadow: 0 0 10px rgba(255, 152, 0, 0.6) !important;
}
html.night-mode .episode-grid .soft-primary,
html.dark .episode-grid .soft-primary,
body.dark-mode .episode-grid .soft-primary,
.uk-light .episode-grid .soft-primary {
    background-color: rgba(255,255,255,0.08) !important;
    color: #ccc !important;
}

/* 手机端适配 */
@media (max-width: 640px) {
    .episode-grid {
        grid-template-columns: repeat(auto-fill, minmax(70px, 1fr));
        gap: 8px;
    }
}

/* ========== 视频播放器容器 ========== */
#ze-player-wrap {
    position: relative;
    width: 100%;
    background: #000;
    border-radius: 8px;
    overflow: hidden;
    margin-bottom: 15px;
}
#ze-player-wrap .artplayer {
    border-radius: 8px;
}
/* ArtPlayer 需要用绝对定位填满 embed-video 容器 */
#ze-artplayer {
    position: absolute;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
}

/* ========== 小电视加载动画（已移除旋转效果）========== */
.artplayer-plugin-loading-indicator {
    display: inline-block;
}


</style>

<div uk-grid="" class="uk-grid">
    <div class="uk-width-3-4@m uk-first-column">

<?php if ($this->fields->toolgo||$this->fields->mp4): ?>
<?php
/* ========== 辅助函数：解析剧集数据 ========== */
function jishulist($text, $type=0, $can=null, $c=0) {
    $spurl = '';
    if($text->fields->mp4){
        $spurl = $text->fields->mp4;
    }
    if($spurl && strpos($spurl, '$') === false){
        $spurl = '全集$'.$spurl;
    }

    $play_index = -1;
    if(isset($_GET['action']) && $_GET['action'] == 'get' && 'GET' == $_SERVER['REQUEST_METHOD']) {
        $play_index = isset($_GET['p']) ? (intval($_GET['p']) - 1) : 0;
    }
    $j = ($play_index >= 0) ? $play_index : 0;

    $txt = $spurl;
    $string_arr = array_values(array_filter(explode("\r\n", $txt)));
    $long = count($string_arr);
    if($long == 0) return '';
    if($j >= $long) $j = $long - 1;

    $list = "";
    $episodes_json = array();

    for($i=0; $i<$long; $i++){
        $xl = null;
        if(isset($_GET['xl'])){
            $xl = "&xl=".$_GET['xl'];
        }
        $p = $i+1;
        $parts = explode("$", $string_arr[$i]);
        $ep_name = $parts[0];
        $ep_url = isset($parts[1]) ? trim($parts[1]) : '';
        $ep_sub = isset($parts[2]) ? trim($parts[2]) : '';

        // 收集剧集数据用于JS
        $episodes_json[] = array(
            'name' => $ep_name,
            'url' => $ep_url,
            'sub' => $ep_sub,
            'index' => $i
        );

        if($play_index == $i && ($can==$xl || (!isset($_GET['xl']) && $c==1))){
            $c_class = "class=\"button small ep-active disabled\"";
            $list .= "<button ".$c_class." title=\"".$ep_name."\" data-ep=\"".$i."\">".$ep_name."</button>";
        }else{
            $c_class = "class=\"button small soft-primary\"";
            $xl_suffix = ($can && substr($can, 4)) ? '&amp;xl='.substr($can, 4) : '';
            $ep_href = $text->permalink.'?action=get&amp;p='.$p.$xl_suffix;
            $list .= "<a href=\"".$ep_href."\" ".$c_class." title=\"".$ep_name."\" data-ep=\"".$i."\" data-xl=\"".($can ? substr($can, 4) : '')."\">".$ep_name."</a>";
        }
    }

    if($type==0){
        $parts = explode("$", $string_arr[$j]);
        return isset($parts[1]) ? trim($parts[1]) : '';
    }elseif($type==2){
        $parts = explode("$", $string_arr[$j]);
        return isset($parts[2]) ? trim($parts[2]) : '';
    }else{
        // 自动转义 JSON，防御异常字符破坏 HTML
        $list = '<div class="episode-grid" data-episodes=\''.htmlspecialchars(json_encode($episodes_json, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8').'\'>'.$list.'</div>';
        return $list;
    }
}

/* ========== 解析duoji多剧集导航 ========== */
$duoji = "";
if($this->fields->duoji && strpos($this->fields->duoji, '$') !== false){
    $hang = array_filter(explode("\r\n", $this->fields->duoji));
    $shu = count($hang);
    for($i=0; $i<$shu; $i++){
        $cid = explode("$", $hang[$i])[1];
        $this->widget('Widget_Archive@duoji'.$cid, 'pageSize=1&type=post', 'cid='.$cid)->to($ji);
        if($ji->cid == $this->cid){
            $duoji .= "<span class=\"ml-1 uk-text-small p-1 uk-text-secondary\">".explode("$", $hang[$i])[0]."</span>";
        }else{
            $duoji .= "<a href=\"".$ji->permalink."\" class=\"ml-1 uk-text-small p-1\">".explode("$", $hang[$i])[0]."</a>";
        }
    }
}

/* ========== 获取当前视频URL和参数 ========== */
$spurl = jishulist($this, 0);
$zimu = jishulist($this, 2);

/* ========== 通用化：播放器模式识别 ========== */
$isExternal = (strpos($spurl, 'player.bilibili.com') !== false 
    || strpos($spurl, 'www.acfun.cn/player') !== false 
    || strpos($spurl, 'v.qq.com/txp/iframe') !== false 
    || strpos($spurl, 'open.iqiyi.com/developer/player_js') !== false 
    || $zimu == "iframe");

// 智能直链判定（无视域名）
$isDirectLink = false;
$zimuLower = strtolower(trim($zimu));
$directKeywords = array('artplayer', 'normal', 'mp4', 'hls', 'm3u8', 'direct');

// 1. 如果第三个参数明确指向直链，或 2. 链接包含常见的媒体后缀
if (in_array($zimuLower, $directKeywords) || preg_match('/(\.mp4|\.m3u8|\.flv|\.webm)(\?|$)/i', $spurl)) {
    $isDirectLink = true;
}

/* ========== 生成剧集列表HTML ========== */
$list = '';
$hasJx = false;
$jxList = array();

if($this->options->jxurl && !$isDirectLink && !$isExternal){
    $hasJx = true;
    $jxurl = $this->options->jxurl;
    $h = explode("\r\n", $jxurl);
    $s = count($h);
    for($i=0; $i<$s; $i++){
        $xn = explode("$", $h[$i])[0]."<br>";
        if($s==1){ $xn = ""; }
        $p = $i+1;
        $xl = "&xl=".$p;
        $list .= $xn . jishulist($this, 1, $xl, $p);
        $jxList[] = explode("$", $h[$i])[1];
    }
}else{
    $list = jishulist($this, 1);
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

<!-- ========== 视频播放器区域 ========== -->
<div class="embed-video" id="ze-player-wrap">

<?php if($isExternal): ?>
    <style>.embed-video{padding-bottom:68%;}</style>
    <iframe id="ze-video-iframe" width="100%" height="100%" 
        src="<?php if(strpos($spurl,'player.bilibili.com')){echo $spurl.'&as_wide=1&high_quality=1&danmaku=0';}else{echo $spurl;} ?>" 
        frameborder="0" border="0" marginwidth="0" marginheight="0" scrolling="no" 
        allowfullscreen="allowfullscreen" mozallowfullscreen="mozallowfullscreen" 
        msallowfullscreen="msallowfullscreen" oallowfullscreen="oallowfullscreen" 
        webkitallowfullscreen="webkitallowfullscreen"
        sandbox="allow-top-navigation allow-same-origin allow-forms allow-scripts"></iframe>

<?php elseif($hasJx): ?>
    <?php
    $jxurl = $this->options->jxurl;
    $h = explode("\r\n", $jxurl);
    if(isset($_GET['xl'])){
        $xl = $_GET['xl']-1;
    }else{
        $xl = 0;
    }
    $jx = explode("$", $h[$xl])[1];
    ?>
    <iframe id="ze-video-iframe" width="100%" height="100%" 
        src="<?php echo $jx.$spurl; ?>" 
        frameborder="0" border="0" marginwidth="0" marginheight="0" scrolling="no" 
        allowfullscreen="allowfullscreen" mozallowfullscreen="mozallowfullscreen" 
        msallowfullscreen="msallowfullscreen" oallowfullscreen="oallowfullscreen" 
        webkitallowfullscreen="webkitallowfullscreen"></iframe>

<?php else: ?>
    <div id="ze-artplayer"></div>
    <script src="<?php $this->options->themeUrl(); ?>lib/artplayer/hls.min.js"></script>
    <script src="<?php $this->options->themeUrl(); ?>lib/artplayer/artplayer.min.js"></script>
<?php endif; ?>
</div>

<?php endif;?><?php endif;?>
</div>
<?php endif;?><?php endif;?>

<!-- ========== 视频信息区域 ========== -->
<div class="video-info mt-3">
    <div class="video-info-title">
<?php if (isset($_GET['action']) && $_GET['action'] == 'get' && 'GET' == $_SERVER['REQUEST_METHOD']): ?>
<a href="<?php $this->permalink(); ?>?action=get&amp;p=<?php echo isset($_GET['p']) ? intval($_GET['p']) : 1; ?><?php echo isset($_GET['xl']) ? '&amp;xl='.intval($_GET['xl']) : ''; ?>"><?php endif;?>
<h1><font style="vertical-align: inherit;"><font style="vertical-align: inherit;"><?php $this->title() ?></font></font></h1>
<?php if (isset($_GET['action']) && $_GET['action'] == 'get' && 'GET' == $_SERVER['REQUEST_METHOD']): ?></a><?php endif;?>
</div>

<?php if (isset($_GET['action']) && $_GET['action'] == 'get' && 'GET' == $_SERVER['REQUEST_METHOD']): ?>
<div class="uk-flex uk-flex-between">
    <div class="video-info-details">
        <span><?php get_post_view($this, 1); ?></span>
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
<?php if (isset($_GET['action']) && $_GET['action'] == 'get' && 'GET' == $_SERVER['REQUEST_METHOD']): ?>
<ul uk-tab="" class="uk-tab mt-0" uk-switcher="animation: uk-animation-slide-left-medium, uk-animation-slide-right-medium">
<li class="uk-active"><a href="#" aria-expanded="true">简介</a></li></ul><?php endif;?>

<div class="mb-3 mr-3 uk-card uk-float-left">
<div class="media media-10x14" style="width: 100px;max-width: 30vw;">
<div class="media-content scrollLoading ojbk" style="background-image: url(&quot;<?php showThumbnail($this); ?>&quot;);"></div>
</div></div>

<div class="uk-card">
<?php 
$sc = 0;
if(collect($this->cid, Typecho_Widget::widget('Widget_User')->uid, 1) == "ko"){ $sc = 1; }
?>
<a href="javascript:;" data-action="collect" data-id="<?php $this->cid(); ?>" class="uk-float-right btn-collect mr-3<?php if($sc==1){echo " current";} ?>"><i class="uil-star"></i></a>

<p class="mt-0">年代：<?php if($this->fields->niandai){ $this->fields->niandai();} ?><br>
类型：<?php $this->tags(' / ', true, 'none'); ?><br>
状态：<?php if($this->fields->zhuangtai>0){echo '连载中';}else{if($this->fields->zhuangtai==-1){echo '待定';}else{echo '完结';}} ?>
</p>

<?php if($this->hidden||$this->titleshow): ?>
<form action="<?php echo Typecho_Widget::widget('Widget_Security')->getTokenUrl($this->permalink); ?>" method="post" class="protected">
<div><span class="uk-text-middle uk-text-danger">当前视频需要输入密码才能观看</span></div>
<div class="uk-margin-small">
<div uk-form-custom="target: true" class="uk-form-custom uk-first-column">
<input class="uk-input" name="protectPassword" type="password" placeholder="请输入密码">
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

<ul uk-tab="" class="uk-tab mt-0" uk-switcher="animation: uk-animation-slide-left-medium, uk-animation-slide-right-medium">
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

<!-- ========== 通用无缝联播 + 智能格式识别 JavaScript ========== -->
<?php if ((isset($_GET['action']) && $_GET['action'] == 'get' && 'GET' == $_SERVER['REQUEST_METHOD']) && !$this->hidden && !$this->titleshow): ?>
<script>
(function(){
    // 通用：全局插入防盗链伪装头
    if (!document.querySelector('meta[name="referrer"]')) {
        var meta = document.createElement('meta');
        meta.name = 'referrer';
        meta.content = 'no-referrer';
        document.head.appendChild(meta);
    }

    var isExternalPlayer = <?php echo $isExternal ? 'true' : 'false'; ?>;
    var hasJx = <?php echo $hasJx ? 'true' : 'false'; ?>;
    var currentP = <?php echo isset($_GET['p']) ? intval($_GET['p']) : 1; ?>;
    var currentXl = <?php echo isset($_GET['xl']) ? intval($_GET['xl']) : 0; ?>;
    var permalink = '<?php echo $this->permalink; ?>';
    var jxList = <?php echo json_encode($jxList); ?>;
    var totalEpisodes = 0;
    var episodes = [];
    var currentEpIndex = currentP - 1;
    var art = null;
    var autoPlayNext = true; 

    // ========== 清除所有剧集进度 ==========
    function clearAllProgress() {
        var keys = [];
        for (var i = 0; i < sessionStorage.length; i++) {
            var key = sessionStorage.key(i);
            if (key && key.indexOf('ze_vid_') === 0) {
                keys.push(key);
            }
        }
        keys.forEach(function(k) { sessionStorage.removeItem(k); });
    }

    // 获取剧集数据
    var episodeGrids = document.querySelectorAll('.episode-grid');
    var epMap = {};
    episodeGrids.forEach(function(grid){
        try {
            var data = JSON.parse(grid.getAttribute('data-episodes'));
            if(data && data.length > 0){
                data.forEach(function(ep){
                    if(!epMap[ep.index]){ epMap[ep.index] = ep; }
                });
            }
        } catch(e) {}
    });
    episodes = Object.values(epMap).sort(function(a,b){ return a.index - b.index; });
    totalEpisodes = episodes.length;

    var playerWrap = document.getElementById('ze-player-wrap');
    var artplayerBox = document.getElementById('ze-artplayer');
    var videoIframe = document.getElementById('ze-video-iframe');

    function updateActiveBtn(index){
        document.querySelectorAll('.episode-grid').forEach(function(grid){
            grid.querySelectorAll('a, button').forEach(function(btn){
                var ep = parseInt(btn.getAttribute('data-ep'));
                if(ep === index){
                    btn.classList.add('ep-active', 'disabled');
                    btn.classList.remove('soft-primary', 'active');
                    btn.removeAttribute('href');
                    btn.style.pointerEvents = 'none';
                } else {
                    btn.classList.remove('ep-active', 'disabled');
                    btn.classList.add('soft-primary');
                    btn.style.pointerEvents = 'auto';
                }
            });
        });
    }

    // ========== 无缝切集：切换视频源（不销毁播放器） ==========
    function switchEpisode(index, autoplay){
        if(index === currentEpIndex) return;
        clearAllProgress();
        if(index < 0 || index >= totalEpisodes) return;

        currentEpIndex = index;
        var ep = episodes[index];
        updateActiveBtn(index);

        if(isExternalPlayer){
            if(videoIframe){
                var url = ep.url;
                if(url.indexOf('player.bilibili.com') !== -1){
                    url += '&as_wide=1&high_quality=1&danmaku=0';
                }
                videoIframe.src = url;
            }
        } else if(hasJx){
            if(videoIframe){
                var jxIndex = currentXl > 0 ? currentXl - 1 : 0;
                var jxUrl = jxList[jxIndex] || '';
                videoIframe.src = jxUrl + ep.url;
            }
        } else {
            // 无缝切集：直接切换URL，不销毁播放器
            if(art && art.video){
                var newUrl = ep.url;
                var epSub = ep.sub ? ep.sub.toLowerCase().trim() : '';
                var isHls = (epSub === 'hls' || epSub === 'm3u8' || newUrl.toLowerCase().indexOf('.m3u8') !== -1);

                if(isHls && art.hls){
                    // HLS 源：销毁旧 hls 实例，重新加载
                    art.hls.destroy();
                    art.hls = null;
                    if(Hls.isSupported()){
                        art.hls = new Hls();
                        art.hls.loadSource(newUrl);
                        art.hls.attachMedia(art.video);
                    } else {
                        art.video.src = newUrl;
                    }
                } else if(isHls && art.video.canPlayType('application/vnd.apple.mpegurl')){
                    // Safari 原生 HLS
                    art.video.src = newUrl;
                } else {
                    // 普通 mp4/flv 等：直接 switchUrl
                    art.switchUrl(newUrl);
                }

                // 更新字幕
                var ignoredSubs = ['artplayer', 'normal', 'mp4', 'hls', 'm3u8', 'direct'];
                if(ep.sub && ignoredSubs.indexOf(epSub) === -1){
                    art.subtitle.url(ep.sub);
                }

                // 自动播放
                if(autoplay){
                    art.video.addEventListener('loadeddata', function(){
                        art.play();
                    }, { once: true });
                }
            } else {
                // 首次初始化
                initArtPlayer(ep, autoplay);
            }
        }
        updateUrl(index);
    }

    function initArtPlayer(ep, autoplay){
        if(!artplayerBox || !window.Artplayer) return;

        // 智能格式识别
        var vType = 'auto';
        var epSub = ep.sub ? ep.sub.toLowerCase().trim() : '';
        var epUrl = ep.url.toLowerCase();

        if (epSub === 'hls' || epSub === 'm3u8' || epUrl.indexOf('.m3u8') !== -1) {
            vType = 'customHls';
        } else if (epSub === 'flv' || epUrl.indexOf('.flv') !== -1) {
            vType = 'flv';
        }

        var options = {
            container: artplayerBox,
            url: ep.url,
            type: vType,
            autoSize: false,
            autoMini: true,
            loop: false,
            flip: true,
            playbackRate: true,
            aspectRatio: true,
            setting: true,
            hotkey: true,
            pip: true,
            mutex: true,
            theme: '#23ade5',
            lang: 'zh-cn',
            autoplay: autoplay === true,
            fullscreen: true,
            autoPlayback: true,
            lock: true,
            autoOrientation: true,
            icons: {
                loading: '<img src="<?php $this->options->themeUrl(); ?>lib/artplayer/ploading.gif">',
                state: '<svg width="80" height="80" viewBox="0 0 80 80" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><defs><path id="a" d="M0 0h80v80H0z"></path><path d="M52.546 8.014a3.998 3.998 0 014.222 3.077c.104.446.093.808.039 1.138a2.74 2.74 0 01-.312.881c-.073.132-.16.254-.246.376l-.257.366-.521.73c-.7.969-1.415 1.926-2.154 2.866l-.015.02a240.945 240.945 0 015.986.341l1.643.123.822.066.41.034.206.018.103.008.115.012c1.266.116 2.516.45 3.677.975a11.663 11.663 0 013.166 2.114c.931.87 1.719 1.895 2.321 3.022a11.595 11.595 0 011.224 3.613c.03.157.046.316.068.474l.015.119.013.112.022.206.085.822.159 1.646c.1 1.098.19 2.198.27 3.298.315 4.4.463 8.829.36 13.255a166.489 166.489 0 01-.843 13.213c-.012.127-.034.297-.053.454a7.589 7.589 0 01-.072.475l-.04.237-.05.236a11.762 11.762 0 01-.74 2.287 11.755 11.755 0 01-5.118 5.57 11.705 11.705 0 01-3.623 1.263c-.158.024-.316.052-.475.072l-.477.053-.821.071-1.644.134c-1.096.086-2.192.16-3.288.23a260.08 260.08 0 01-6.578.325c-8.772.324-17.546.22-26.313-.302a242.458 242.458 0 01-3.287-.22l-1.643-.129-.822-.069-.41-.035-.206-.018c-.068-.006-.133-.01-.218-.02a11.566 11.566 0 01-3.7-.992 11.732 11.732 0 01-5.497-5.178 11.73 11.73 0 01-1.215-3.627c-.024-.158-.051-.316-.067-.475l-.026-.238-.013-.119-.01-.103-.07-.823-.132-1.648a190.637 190.637 0 01-.22-3.298c-.256-4.399-.358-8.817-.258-13.233.099-4.412.372-8.811.788-13.197a11.65 11.65 0 013.039-6.835 11.585 11.585 0 016.572-3.563c.157-.023.312-.051.47-.07l.47-.05.82-.07 1.643-.13a228.493 228.493 0 016.647-.405l-.041-.05a88.145 88.145 0 01-2.154-2.867l-.52-.73-.258-.366c-.086-.122-.173-.244-.246-.376a2.74 2.74 0 01-.312-.881 2.808 2.808 0 01.04-1.138 3.998 3.998 0 014.22-3.077 2.8 2.8 0 011.093.313c.294.155.538.347.742.568.102.11.19.23.28.35l.27.359.532.72a88.059 88.059 0 012.06 2.936 73.036 73.036 0 011.929 3.03c.187.313.373.628.556.945 2.724-.047 5.447-.056 8.17-.038.748.006 1.496.015 2.244.026.18-.313.364-.624.549-.934a73.281 73.281 0 011.93-3.03 88.737 88.737 0 012.059-2.935l.533-.72.268-.359c.09-.12.179-.24.281-.35a2.8 2.8 0 011.834-.881zM30.13 34.631a4 4 0 00-.418 1.42 91.157 91.157 0 00-.446 9.128c0 2.828.121 5.656.364 8.483l.11 1.212a4 4 0 005.858 3.143c2.82-1.498 5.55-3.033 8.193-4.606a177.41 177.41 0 005.896-3.666l1.434-.942a4 4 0 00.047-6.632 137.703 137.703 0 00-7.377-4.708 146.88 146.88 0 00-6.879-3.849l-1.4-.725a4 4 0 00-5.382 1.742z" id="d"></path><filter x="-15.4%" y="-16.3%" width="130.9%" height="132.5%" filterUnits="objectBoundingBox" id="c"><feOffset dy="2" in="SourceAlpha" result="shadowOffsetOuter1"></feOffset><feGaussianBlur stdDeviation="1" in="shadowOffsetOuter1" result="shadowBlurOuter1"></feGaussianBlur><feColorMatrix values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.3 0" in="shadowBlurOuter1" result="shadowMatrixOuter1"></feColorMatrix><feOffset in="SourceAlpha" result="shadowOffsetOuter2"></feOffset><feGaussianBlur stdDeviation="3.5" in="shadowOffsetOuter2" result="shadowBlurOuter2"></feGaussianBlur><feColorMatrix values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.2 0" in="shadowBlurOuter2" result="shadowMatrixOuter2"></feColorMatrix><feMerge><feMergeNode in="shadowMatrixOuter1"></feMergeNode><feMergeNode in="shadowMatrixOuter2"></feMergeNode></feMerge></filter></defs><g fill="none" fill-rule="evenodd" opacity=".8"><mask id="b" fill="#fff"><use xlink:href="#a"></use></mask><g mask="url(#b)"><use fill="#000" filter="url(#c)" xlink:href="#d"></use><use fill="#FFF" xlink:href="#d"></use></g></g></svg>',
                indicator: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 22 22"><path d="M16.118 3.667h.382a3.667 3.667 0 013.667 3.667v7.333a3.667 3.667 0 01-3.667 3.667h-11a3.667 3.667 0 01-3.667-3.667V7.333A3.667 3.667 0 015.5 3.666h.382L4.95 2.053a1.1 1.1 0 011.906-1.1l1.567 2.714h5.156L15.146.953a1.101 1.101 0 011.906 1.1l-.934 1.614z" fill="#333"></path><path d="M5.561 5.194h10.878a2.2 2.2 0 012.2 2.2v7.211a2.2 2.2 0 01-2.2 2.2H5.561a2.2 2.2 0 01-2.2-2.2V7.394a2.2 2.2 0 012.2-2.2z" fill="#fff"></path><path d="M6.967 8.556a1.1 1.1 0 011.1 1.1v2.689a1.1 1.1 0 11-2.2 0V9.656a1.1 1.1 0 011.1-1.1zM15.033 8.556a1.1 1.1 0 011.1 1.1v2.689a1.1 1.1 0 11-2.2 0V9.656a1.1 1.1 0 011.1-1.1z" fill="#333"></path></svg>',
            },
            contextmenu: [
                {
                    name: 'pip',
                    html: '画中画模式',
                    click: function(art) { art.video.requestPictureInPicture(); }
                }
            ],
            customType: {
                customHls: function(video, url, art) {
                    if (Hls.isSupported()) {
                        var hls = new Hls();
                        hls.loadSource(url);
                        hls.attachMedia(video);
                        art.hls = hls; // 存储引用，便于无缝切集复用
                        art.on('destroy', function() { hls.destroy(); art.hls = null; });
                    } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
                        video.src = url;
                    }
                },
            },
        };

        var ignoredSubs = ['artplayer', 'normal', 'mp4', 'hls', 'm3u8', 'direct'];
        if(ep.sub && ignoredSubs.indexOf(epSub) === -1){
            options.subtitle = {
                url: ep.sub,
                type: 'webvtt',
                style: {
                    color: '#b7daff',
                    'font-size': '25px',
                    bottom: '10%',
                },
            };
        }

        art = new Artplayer(options);
        art.on('video:ended', onVideoEnded);



        // ========== Wake Lock API - 防止手机屏幕熄灭 ==========
        // Android Chrome/Edge: 使用 Wake Lock API
        // iOS Safari: 使用静音音频 trick（Safari 不支持 Wake Lock API）
        var wakeLockSentinel = null;
        var silentAudio = null;

        function createSilentAudio() {
            if (silentAudio) return;
            silentAudio = new Audio('data:audio/mp3;base64,//uQxAAAAAAAAAAAAAAAAAAAAAAAWGluZwAAAA8AAAACAAACcQCAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA//////////////////////////////////////////////////////////////////8AAAAATGF2ZjU2LjM1LjEwMAAAAAAAAAAAAAAAAQ==');
            silentAudio.loop = true;
            silentAudio.volume = 0.001;
            silentAudio.setAttribute('playsinline', '');
            silentAudio.setAttribute('webkit-playsinline', '');
        }

        async function requestWakeLock() {
            // 优先尝试 Wake Lock API (Chrome/Edge Android)
            try {
                if ('wakeLock' in navigator) {
                    wakeLockSentinel = await navigator.wakeLock.request('screen');
                    wakeLockSentinel.addEventListener('release', function() {
                        wakeLockSentinel = null;
                    });
                    return;
                }
            } catch (err) {
                console.log('Wake Lock API failed, trying silent audio fallback:', err);
            }
            // Fallback: 静音音频 (iOS Safari)
            try {
                createSilentAudio();
                if (silentAudio && silentAudio.paused) {
                    silentAudio.play().catch(function() {});
                }
            } catch (err) {}
        }

        function releaseWakeLock() {
            if (wakeLockSentinel !== null) {
                wakeLockSentinel.release();
                wakeLockSentinel = null;
            }
            if (silentAudio && !silentAudio.paused) {
                silentAudio.pause();
            }
        }

        // 页面可见性变化时重新获取 Wake Lock
        document.addEventListener('visibilitychange', function() {
            if (document.visibilityState === 'visible' && art && art.video && !art.video.paused) {
                requestWakeLock();
            }
        });
        // 播放时获取，暂停时释放
        art.on('play', function() { requestWakeLock(); });
        art.on('pause', function() { releaseWakeLock(); });
        art.on('video:ended', function() { releaseWakeLock(); });
        art.on('fullscreen', function(){
            playerWrap.classList.add('ze-fullscreen');
            onFullscreenEnter();
        });
        art.on('fullscreenCancel', function(){
            playerWrap.classList.remove('ze-fullscreen');
            onFullscreenExit();
        });

        // 保存 hls 实例引用，便于无缝切集时复用
        if(vType === 'customHls'){
            art.on('ready', function(){
                // hls 实例在 customType 回调中已创建，尝试获取
                // ArtPlayer 不直接暴露 hls，需自行管理
            });
        }

        var isFullscreen = document.fullscreenElement || document.webkitFullscreenElement;
        if(isFullscreen && art.video){
            playerWrap.classList.add('ze-fullscreen');
            art.video.addEventListener('loadedmetadata', onFullscreenEnter, { once: true });
        }



        // 进度存储 key（基于当前剧集 url）
        var storageKey = 'ze_vid_' + ep.url.replace(/[^a-zA-Z0-9]/g, '_').substring(0, 80);
        try {
            var savedTime = sessionStorage.getItem(storageKey);
            if(savedTime && parseFloat(savedTime) > 5){
                art.seek(parseFloat(savedTime));
            }
        } catch(e) {}

        // 定期保存当前集进度
        setInterval(function(){
            if(art && art.video && !art.video.paused){
                sessionStorage.setItem(storageKey, art.video.currentTime);
            }
        }, 2000);
    }

    function onVideoEnded(){
        if(!autoPlayNext) return;
        var nextIndex = currentEpIndex + 1;
        if(nextIndex >= totalEpisodes) return;
        switchEpisode(nextIndex, true);
    }

    function onFullscreenEnter(){
        if(!playerWrap || window.innerWidth > 768) return;
        var vw = (art && art.video) ? art.video.videoWidth : 0;
        var vh = (art && art.video) ? art.video.videoHeight : 0;
        if(vw > 0 && vh > 0 && vw > vh){
            try {
                if(screen.orientation && screen.orientation.lock) screen.orientation.lock('landscape').catch(function(){});
            } catch(e) {}
        }
    }

    function onFullscreenExit(){
        try {
            if(screen.orientation && screen.orientation.unlock) screen.orientation.unlock();
        } catch(e) {}
    }

    function updateUrl(index){
        var p = index + 1;
        var newUrl = permalink + '?action=get&p=' + p;
        if(currentXl > 0){ newUrl += '&xl=' + currentXl; }
        try {
            if(window.history && window.history.replaceState){
                window.history.replaceState({}, '', newUrl);
            }
        } catch(e) {}
    }



    // 点击剧集按钮切换
    document.addEventListener('click', function(e){
        var btn = e.target.closest('.episode-grid a, .episode-grid button');
        if(!btn) return;
        var epIndex = parseInt(btn.getAttribute('data-ep'));
        if(isNaN(epIndex)) return;

        var btnXl = btn.getAttribute('data-xl');
        if(btnXl && btnXl !== ''){ currentXl = parseInt(btnXl); }

        e.preventDefault();
        autoPlayNext = true; 
        switchEpisode(epIndex, true);
    });

    // 初始加载时，若有 ArtPlayer 剧集则初始化（不主动清除进度，保留当前集的进度）
    if(!isExternalPlayer && !hasJx && totalEpisodes > 0){
        var initEp = episodes[currentEpIndex];
        if(initEp){ initArtPlayer(initEp, false); }
    }
    updateActiveBtn(currentEpIndex);

})();
</script>
<?php endif; ?>