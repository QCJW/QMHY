<?php
/**
 * ASMR.one 实时浏览/搜索/播放（文件夹树状设计优化版）
 */
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

// ========== 新增：获取真实客户端 IP（兼容 Cloudflare 等 CDN） ==========
function getRealIp() {
    // 1. Cloudflare 专用头
    if (isset($_SERVER['HTTP_CF_CONNECTING_IP'])) {
        return $_SERVER['HTTP_CF_CONNECTING_IP'];
    }
    // 2. 通用代理头（X-Forwarded-For 可能包含多个 IP，取第一个）
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $ip = trim($ips[0]);
        if (filter_var($ip, FILTER_VALIDATE_IP)) {
            return $ip;
        }
    }
    // 3. 直连 IP
    return $_SERVER['REMOTE_ADDR'] ?? '';
}

// ========== IP 黑名单检测（57.141.0.0/16） ==========
function isBlockedIp($ip) {
    if (strpos($ip, '57.141.') === 0) {
        return true;
    }
    return false;
}

$clientIp = getRealIp();
if ($clientIp && isBlockedIp($clientIp)) {
    http_response_code(403);
    header('Content-Type: text/plain; charset=utf-8');
    die('Access denied.');
}
// ========== 结束 IP 黑名单检测 ==========

/* ========== 0. 媒体/字幕反代入口（已废弃，保留为空） ========== */
if (isset($_GET['asmr_stream']) && !empty($_GET['k'])) {
    header('HTTP/1.1 410 Gone');
    echo 'Direct link only, proxy disabled.';
    exit;
}

/* ========== 1. 功能开关检查 ========== */
$asmrEnable = $this->options->asmr_enable;
$enabled = false;
if ($asmrEnable) {
    $val = is_array($asmrEnable) ? $asmrEnable : (is_string($asmrEnable) ? array($asmrEnable) : array());
    $enabled = in_array('enable', $val);
}
if (!$enabled) {
    header('HTTP/1.1 404 Not Found');
    $this->need('header.php');
    $this->need('sidebar.php');
    echo '<div class="main_content_inner"><div class="uk-alert-danger" uk-alert><p>ASMR 功能未开启。请在后台「主题设置 → ASMR」里开启。</p></div></div>';
    $this->need('footer.php');
    return;
}

/* ========== 2. 读取配置 ========== */
// 读取后台设置的模式并做兼容处理（防止老数据报错）
$rawMode = $this->options->asmr_default_age ? $this->options->asmr_default_age : 'safe';
$siteMode = ($rawMode === 'normal' || $rawMode === 'all') ? 'normal' : 'safe';

$asmrDefaultTag = $this->options->asmr_default_tag ? $this->options->asmr_default_tag : '';
$asmrPagesize  = intval($this->options->asmr_pagesize ? $this->options->asmr_pagesize : 20);
if ($asmrPagesize < 1) $asmrPagesize = 20;
$asmrTimeout   = intval($this->options->asmr_timeout ? $this->options->asmr_timeout : 10);
if ($asmrTimeout < 1) $asmrTimeout = 10;

$asmrBase = '?asmr=1';
$client   = new AsmrClient('', '', $asmrTimeout);

/* ========== 3. 路由分发 ========== */
$mode = 'list';
if (isset($_GET['w']) && preg_match('/^(RJ|VJ|BJ)\d+$/i', trim($_GET['w']))) {
    $mode = 'work';
} else {
    $hasQuery = isset($_GET['q']) && trim($_GET['q']) !== '';
    $hasVa    = isset($_GET['va']) && trim($_GET['va']) !== '';
    $hasTag   = isset($_GET['tag']) && trim($_GET['tag']) !== '';
    
    // 前端年龄验证
    $allowedAges = ($siteMode === 'safe') ? array('general', 'r15', 'all') : array('general', 'r15', 'adult', 'r18', 'all');
    $hasAge   = isset($_GET['age']) && in_array($_GET['age'], $allowedAges) && $_GET['age'] !== 'all';
    
    // 如果开启了全年龄安全模式，所有默认列表请求都需要转为 search 模式以便加上 $\-age:adult$ 过滤
    if ($hasQuery || $hasVa || $hasTag || $hasAge || $siteMode === 'safe') {
        $mode = 'search';
    }
}

/* ========== 4. 自包含页头 ========== */
$this->need('header.php');
$this->need('sidebar.php');

/* ========== 4.5 页面级 HTML 缓存（1天） ========== */
$_asmrPageCacheKey = 'ph_' . md5($_SERVER['REQUEST_URI']);
$_asmrCachedPage = _asmr_cache_read($_asmrPageCacheKey, 86400);
if ($_asmrCachedPage && is_string($_asmrCachedPage)) {
    echo $_asmrCachedPage;
    $this->need('footer.php');
    return;
}
ob_start();
?>
<div class="main_content_inner">

<?php if ($mode === 'work'): ?>
    <?php
    /* ==================== WORK 模式：作品详情 + 播放 ==================== */
    $workCode = strtoupper(trim($_GET['w']));
    $iid = isset($_GET['iid']) ? intval($_GET['iid']) : 0;
    if (!preg_match('/^(RJ|VJ|BJ)\d+$/i', $workCode)):
        echo '<div class="uk-alert-danger" uk-alert><p>无效的作品编号：' . htmlspecialchars($workCode) . '</p>'
            . '<a href="' . $asmrBase . '" class="button small soft-primary">返回列表</a></div>';
    else:
        list($workInfo, $tracks) = $client->getWorkBundle($workCode, $iid);

        if (!$workInfo):
            echo '<div class="uk-alert-danger" uk-alert><p>作品不存在或接口暂时不可用，请稍后再试。</p>'
                . '<a href="' . $asmrBase . '" class="button small soft-primary">返回列表</a></div>';
        else:
            $ageBadgeText = asmr_get_age_badge($workInfo);
            $isR18 = ($ageBadgeText === 'R18');
            $title = isset($workInfo['title']) ? $workInfo['title'] : (isset($workInfo['name']) ? $workInfo['name'] : $workCode);
            $internalId = isset($workInfo['id']) ? intval($workInfo['id']) : $iid;
            $coverUrl = asmr_cover_url($internalId);
            $coverThumb = asmr_cover_thumb_url($internalId);
            $vas = asmr_get_vas($workInfo);
            $songArtist = !empty($vas) ? implode(' / ', $vas) : (isset($workInfo['circle']['name']) ? $workInfo['circle']['name'] : 'ASMR');
            $circleName = isset($workInfo['circle']['name']) ? $workInfo['circle']['name'] : '';
            $workIntro = "作品编号：{$workCode}";
            if ($circleName) $workIntro .= " | 社团：{$circleName}";
            if (!empty($vas)) $workIntro .= " | 声优：" . implode('、', $vas);
            if ($ageBadgeText) $workIntro .= " | 分级：{$ageBadgeText}";

            if (!$tracks && $internalId) $tracks = $client->getWorkTracks($internalId);
            if (!$tracks):
                echo '<div class="uk-alert-danger" uk-alert><p>获取音轨失败，请稍后再试。</p>'
                    . '<a href="' . $asmrBase . '" class="button small soft-primary">返回列表</a></div>';
            else:
                list($audio, $video, $sub) = asmr_extract_tracks($tracks);
                $allMedia = array_merge($audio, $video);
                if (empty($allMedia)):
                    echo '<div class="uk-alert-warning" uk-alert><p>该作品暂无可播放的音视频文件。</p>'
                        . '<a href="' . $asmrBase . '" class="button small soft-primary">返回列表</a></div>';
                else:
                    $subMap = asmr_match_subtitles($allMedia, $sub);
                    $mp4Field = asmr_build_mp4_field($allMedia, $subMap, $workCode);
                    list($albumData, $flatList) = asmr_parse_mp4_field($mp4Field);

                    $currentP = 1;
                    if (isset($_GET['p']) && is_numeric($_GET['p'])) $currentP = intval($_GET['p']);
                    $totalSongs = count($flatList);
                    if ($currentP < 1) $currentP = 1;
                    if ($totalSongs > 0 && $currentP > $totalSongs) $currentP = 1;
                    $currentSong = !empty($flatList) ? $flatList[$currentP - 1] : null;

                    $prevP = ($currentP - 1 < 1) ? $totalSongs : $currentP - 1;
                    $nextP = ($currentP + 1 > $totalSongs) ? 1 : $currentP + 1;
                    $permalinkBase = $asmrBase . '&w=' . $workCode . ($internalId ? '&iid=' . $internalId : '') . '&p=';
                    $prevUrl = $permalinkBase . $prevP;
                    $nextUrl = $permalinkBase . $nextP;

                    $jsPlaylist = array();
                    foreach ($flatList as $idx => $item) {
                        $iTitle = isset($item['title']) ? $item['title'] : ('Track ' . ($idx + 1));
                        $iUrl   = isset($item['url']) ? $item['url'] : '';
                        $iLrc   = isset($item['lrc']) ? $item['lrc'] : '';

                        $jsPlaylist[] = array(
                            'p'        => $idx + 1,
                            'title'    => $iTitle,
                            'url'      => $iUrl,
                            'proxyUrl' => '',
                            'lrc'      => $iLrc,
                            'lrcProxy' => '',
                            'isVideo'  => !empty($item['isVideo']),
                            'hash'     => md5($workCode . '_' . ($idx + 1)),
                            'link'     => $permalinkBase . ($idx + 1)
                        );
                    }

                    $origAudioUrl = $currentSong ? $currentSong['url'] : '';
                    $songTitle = $currentSong ? $currentSong['title'] : '暂无曲目';
                    $isCurrentVideo = $currentSong && !empty($currentSong['isVideo']);
    ?>
<div uk-grid="" class="uk-grid">
    <div class="uk-width-3-4@m uk-first-column">

    <?php if ($currentSong): ?>
        <!-- ===== 统一播放器（视频+音频动态切换） ===== -->
        <div id="media-player-wrapper" uk-sticky="top: 400 ;media : @s" class="uk-sticky" style="background:transparent;box-shadow:none;">
            
            <!-- 视频 UI 容器 -->
            <div id="video-container" style="display: <?php echo $isCurrentVideo ? 'block' : 'none'; ?>;">
                <div style="position:relative;width:100%;background:#000;border-radius:12px;overflow:hidden;box-shadow:0 10px 30px rgba(0,0,0,0.5);margin-bottom:20px;">
                    <video id="asmr-video" controls preload="metadata" playsinline webkit-playsinline
                           poster="<?php echo htmlspecialchars($coverUrl); ?>"
                           src="<?php echo htmlspecialchars($isCurrentVideo ? $origAudioUrl : ''); ?>"
                           style="width:100%;max-height:520px;display:block;background:#000;">
                    </video>
                </div>
                <div style="display:flex;justify-content:space-between;align-items:center;background:#121212;color:#fff;padding:12px 20px;border-radius:10px;margin-bottom:15px;gap:10px;">
                    <div style="min-width:0;flex:1;">
                        <div id="vid-title" style="font-size:17px;font-weight:bold;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?php echo htmlspecialchars($songTitle); ?></div>
                        <div style="font-size:13px;opacity:0.6;margin-top:3px;"><?php echo htmlspecialchars($songArtist); ?></div>
                    </div>
                </div>
            </div>

            <!-- 音频 UI 容器 -->
            <div id="audio-container" style="display: <?php echo $isCurrentVideo ? 'none' : 'block'; ?>;">
                <div id="native-player-box">
                    <style>
                        #native-player-box {
                            position: relative; width: 100%; height: 500px; overflow: hidden;
                            background: #121212; border-radius: 12px;
                            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
                            font-family: -apple-system, BlinkMacSystemFont, sans-serif;
                            color: #fff; user-select: none; margin-bottom: 20px;
                        }
                        .np-bg {
                            position: absolute; top: 0; left: 0; width: 100%; height: 100%;
                            background-image: url('<?php echo htmlspecialchars($coverUrl); ?>');
                            background-size: cover; background-position: center;
                            filter: blur(50px) brightness(0.4); transform: scale(1.2); z-index: 1;
                        }
                        .np-body {
                            position: relative; z-index: 2; width: 100%; height: 100%;
                            display: flex; align-items: center; padding: 0 50px; box-sizing: border-box;
                        }
                        .np-cover-wrap {
                            flex: 0 0 260px; height: 260px; margin-right: 50px;
                            display: flex; justify-content: center; align-items: center;
                        }
                        .np-cover {
                            width: 240px; height: 240px; border-radius: 50%;
                            background-image: url('<?php echo htmlspecialchars($coverThumb); ?>');
                            background-size: cover; background-position: center;
                            border: 5px solid rgba(255,255,255,0.1);
                            box-shadow: 0 10px 40px rgba(0,0,0,0.6);
                            animation: np-spin 25s linear infinite; animation-play-state: paused;
                        }
                        .np-cover.playing { animation-play-state: running; }
                        .np-info-wrap { flex: 1; height: 340px; display: flex; flex-direction: column; justify-content: center; min-width: 0; }
                        .np-title { font-size: 28px; font-weight: 700; margin-bottom: 5px; color:#fff; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
                        .np-artist { font-size: 16px; color: rgba(255,255,255,0.6); margin-bottom: 25px; }
                        .np-lrc-box {
                            height: 160px; overflow: hidden; margin-bottom: 30px; position: relative;
                            mask-image: linear-gradient(to bottom, transparent, black 15%, black 85%, transparent);
                            -webkit-mask-image: linear-gradient(to bottom, transparent, black 15%, black 85%, transparent);
                        }
                        .np-lrc-inner { transition: transform 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94); text-align: left; }
                        .np-lrc-line { font-size: 15px; color: rgba(255,255,255,0.4); line-height: 1.6; padding: 6px 0; min-height: 24px; transition: all 0.3s; }
                        .np-lrc-line.active { color: #fff; font-size: 18px; font-weight: bold; transform: scale(1.02); transform-origin: left center; }
                        .np-controls { display: flex; align-items: center; gap: 15px; }
                        .np-btn-play {
                            width: 56px; height: 56px; border-radius: 50%;
                            background: #fff; border: none; cursor: pointer;
                            display: flex; align-items: center; justify-content: center;
                            box-shadow: 0 4px 15px rgba(255,255,255,0.3);
                            transition: transform 0.1s; flex-shrink: 0;
                        }
                        .np-btn-play:active { transform: scale(0.95); }
                        .np-btn-play svg { width: 24px; height: 24px; fill: #000; margin-left: 2px; }
                        .np-btn-play.is-playing svg { margin-left: 0; }
                        .np-btn-nav {
                            width: 40px; height: 40px; border-radius: 50%;
                            background: rgba(255,255,255,0.1); border: none; cursor: pointer;
                            display: flex; align-items: center; justify-content: center;
                            transition: background 0.2s; flex-shrink: 0; text-decoration: none;
                        }
                        .np-btn-nav:hover { background: rgba(255,255,255,0.2); }
                        .np-btn-nav svg { width: 20px; height: 20px; fill: #fff; }
                        .np-progress-wrap { flex: 1; display: flex; align-items: center; gap: 12px; font-size: 13px; color: rgba(255,255,255,0.8); }
                        .np-range {
                            flex: 1; -webkit-appearance: none; height: 6px;
                            background: rgba(255,255,255,0.2); border-radius: 3px; outline: none;
                            transition: all 0.3s ease; cursor: pointer;
                            background-image: linear-gradient(to right, #fff 0%, #fff 0%, transparent 0%);
                            background-size: 100% 100%; background-repeat: no-repeat;
                        }
                        .np-range:hover { background: rgba(255,255,255,0.3); height: 8px; }
                        .np-range::-webkit-slider-thumb {
                            -webkit-appearance: none; width: 14px; height: 14px;
                            background: #fff; border-radius: 50%; cursor: pointer;
                            box-shadow: 0 0 10px rgba(0,0,0,0.3); transition: all 0.3s ease; opacity: 0.9;
                        }
                        .np-range:hover::-webkit-slider-thumb { width: 18px; height: 18px; opacity: 1; box-shadow: 0 0 15px rgba(255,255,255,0.5); }
                        @keyframes np-spin { 100% { transform: rotate(360deg); } }
                        @media (max-width: 768px) {
                            #native-player-box { height: auto; min-height: 660px; padding-bottom: 40px; }
                            .np-body { flex-direction: column; padding: 40px 25px; text-align: center; }
                            .np-cover-wrap { margin-right: 0; margin-bottom: 30px; flex: 0 0 auto; height: auto; }
                            .np-cover { width: 220px; height: 220px; border-width: 4px; }
                            .np-info-wrap { width: 100%; height: auto; display: block; }
                            .np-title { font-size: 26px; margin-bottom: 5px; }
                            .np-artist { font-size: 16px; margin-bottom: 25px; }
                            .np-lrc-inner { text-align: center; }
                            .np-lrc-line.active { transform-origin: center center; }
                            .np-controls { flex-wrap: wrap; justify-content: center; gap: 20px; margin-top: 10px; }
                            .np-progress-wrap { width: 100%; flex: 0 0 100%; order: 1; margin-bottom: 10px; }
                            .np-btn-play, .np-btn-nav { order: 2; }
                            .np-btn-play { width: 64px; height: 64px; }
                            .np-btn-play svg { width: 28px; height: 28px; }
                            .np-btn-nav { width: 48px; height: 48px; margin: 0 15px; }
                        }
                    </style>
                    <div class="np-bg"></div>
                    <div class="np-body">
                        <div class="np-cover-wrap">
                            <div class="np-cover" id="np-cover"></div>
                        </div>
                        <div class="np-info-wrap">
                            <div class="np-meta">
                                <div class="np-title">
                                    <span id="aud-title-text"><?php echo htmlspecialchars($songTitle); ?></span>
                                </div>
                                <div class="np-artist"><?php echo htmlspecialchars($songArtist); ?></div>
                            </div>
                            <div class="np-lrc-box" id="np-lrc-box">
                                <div class="np-lrc-inner" id="np-lrc-inner"><div class="np-lrc-line">准备播放...（首次加载需点击播放按钮）</div></div>
                            </div>
                            <div class="np-controls">
                                <a href="<?php echo $prevUrl; ?>" class="np-btn-nav" id="np-btn-prev" title="上一曲"><svg viewBox="0 0 24 24"><path d="M6 6h2v12H6zm3.5 6l8.5 6V6z"/></svg></a>
                                <button class="np-btn-play" id="np-play-btn">
                                    <svg id="icon-play" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                    <svg id="icon-pause" viewBox="0 0 24 24" style="display:none"><path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/></svg>
                                </button>
                                <a href="<?php echo $nextUrl; ?>" class="np-btn-nav" id="np-btn-next" title="下一曲"><svg viewBox="0 0 24 24"><path d="M6 18l8.5-6L6 6v12zM16 6v12h2V6h-2z"/></svg></a>
                                <div class="np-progress-wrap">
                                    <span id="np-time-current">00:00</span>
                                    <input type="range" class="np-range" id="np-seek" value="0" min="0" max="100" step="0.1">
                                    <span id="np-time-total">00:00</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 统一播放控制脚本 -->
            <script>
            (function(){
                var playlist = <?php echo json_encode($jsPlaylist); ?>;
                var currentIndex = <?php echo $currentP - 1; ?>;
                if(!playlist || !playlist.length) return;

                var audio = new Audio();
                audio.preload = "metadata";
                var video = document.getElementById('asmr-video');
                var audioContainer = document.getElementById('audio-container');
                var videoContainer = document.getElementById('video-container');

                var btn = document.getElementById('np-play-btn');
                var iconPlay = document.getElementById('icon-play');
                var iconPause = document.getElementById('icon-pause');
                var cover = document.getElementById('np-cover');
                var seek = document.getElementById('np-seek');
                var timeCurr = document.getElementById('np-time-current');
                var timeTotal = document.getElementById('np-time-total');
                var lrcInner = document.getElementById('np-lrc-inner');
                var btnPrev = document.getElementById('np-btn-prev');
                var btnNext = document.getElementById('np-btn-next');
                var audTitleText = document.getElementById('aud-title-text');
                var vidTitle = document.getElementById('vid-title');

                var curTrack = playlist[currentIndex] || playlist[0];
                var directMedia = curTrack.url;
                var directLrc   = curTrack.lrc;
                var storageKey  = "music_pos_" + curTrack.hash;

                var isPlaying = false;
                var isSeeking = false;
                var lrcData = [];
                var metaLoaded = false;
                var stallTimer = null;
                var lrcLoaded = false;

                if (!curTrack.isVideo && directMedia) {
                    audio.src = directMedia;
                }

                function fmtTime(s) {
                    if (isNaN(s) || !isFinite(s)) s = 0;
                    var m = Math.floor(s / 60); var s0 = Math.floor(s % 60);
                    return (m<10?'0'+m:m) + ':' + (s0<10?'0'+s0:s0);
                }
                function toggleUI(play) {
                    if(play) {
                        iconPlay.style.display = 'none'; iconPause.style.display = 'block';
                        cover.classList.add('playing'); btn.classList.add('is-playing');
                    } else {
                        iconPlay.style.display = 'block'; iconPause.style.display = 'none';
                        cover.classList.remove('playing'); btn.classList.remove('is-playing');
                    }
                }

                function loadLrc(src) {
                    if(!src || lrcLoaded) return;
                    fetch(src, {cache:'force-cache'}).then(function(r){
                        if(!r.ok) throw new Error('lrc http ' + r.status);
                        return r.text();
                    }).then(function(t){
                        lrcLoaded = true;
                        lrcData = parseLrc(t);
                        renderLrc(lrcData);
                    }).catch(function(err){
                        console.warn('lrc load fail', err);
                        lrcInner.innerHTML = '<div class="np-lrc-line">歌词加载失败</div>';
                    });
                }
                function parseLrc(text) {
                    if(!text) return [];
                    var result = [];
                    var isVtt = /^WEBVTT/i.test(text.trim());
                    var lines = text.split(/\r?\n/);
                    if(isVtt) {
                        var vttTimeExp = /(\d{2}:)?(\d{2}:\d{2}\.\d{3})/;
                        var currentStartTime = -1;
                        for(var i=0; i<lines.length; i++) {
                            var line = lines[i].trim();
                            if(line === "" || /^WEBVTT/i.test(line)) continue;
                            if(line.indexOf('-->') >= 0) {
                                var match = vttTimeExp.exec(line);
                                if(match) {
                                    var parts = match[0].split(':');
                                    var sec = 0;
                                    if(parts.length === 3) { sec = parseInt(parts[0],10)*3600 + parseInt(parts[1],10)*60 + parseFloat(parts[2]); }
                                    else { sec = parseInt(parts[0],10)*60 + parseFloat(parts[1]); }
                                    currentStartTime = sec;
                                }
                            } else if(currentStartTime >= 0) {
                                var content = line.replace(/<[^>]+>/g, '');
                                if(content.indexOf('->')>=0) { content = content.split('->').map(function(s){return s.trim()}).join('\n'); }
                                result.push({time: currentStartTime, text: content});
                                currentStartTime = -1;
                            }
                        }
                    } else {
                        var timeExp = /\[(\d{2}):(\d{2})(?:\.(\d{2,3}))?\]/g;
                        for(var i=0; i<lines.length; i++) {
                            var line = lines[i].trim();
                            var matches; timeExp.lastIndex = 0;
                            var times = [];
                            while((matches = timeExp.exec(line)) !== null) {
                                var t = parseInt(matches[1],10)*60 + parseInt(matches[2],10) + (matches[3]?parseFloat('0.'+matches[3]):0);
                                times.push(t);
                            }
                            if(times.length) {
                                var content = line.replace(timeExp, '').trim();
                                if(!content) continue;
                                for(var j=0;j<times.length;j++) result.push({time: times[j], text: content});
                            }
                        }
                    }
                    result.sort(function(a,b){ return a.time-b.time; });
                    var merged = [];
                    for(var i=0;i<result.length;i++){
                        if(i>0 && Math.abs(result[i].time-merged[merged.length-1].time)<0.2) merged[merged.length-1].text += '\n'+result[i].text;
                        else merged.push(result[i]);
                    }
                    return merged;
                }
                function renderLrc(data) {
                    if(!data.length) { lrcInner.innerHTML = '<div class="np-lrc-line">纯音乐 / 无歌词</div>'; return; }
                    var html = '';
                    for(var i=0;i<data.length;i++) {
                        var display = String(data[i].text).replace(/\n/g,'<br>');
                        html += '<div class="np-lrc-line">' + display + '</div>';
                    }
                    lrcInner.innerHTML = html;
                }
                
                if(!curTrack.isVideo) {
                    if(directLrc) loadLrc(directLrc);
                    else lrcInner.innerHTML = '<div class="np-lrc-line">暂无歌词</div>';
                }

                function loadTrack(index, autoPlay) {
                    if (index < 0) index = playlist.length - 1;
                    if (index >= playlist.length) index = 0;
                    
                    var track = playlist[index];
                    currentIndex = index;

                    clearTimeout(stallTimer);
                    metaLoaded = false;
                    
                    directMedia = track.url;
                    directLrc   = track.lrc;
                    storageKey  = "music_pos_" + track.hash;

                    if (audTitleText) audTitleText.textContent = track.title;
                    if (vidTitle) vidTitle.textContent = track.title;

                    if (window.history && window.history.replaceState && track.link) {
                        window.history.replaceState(null, '', track.link);
                    }

                    var items = document.querySelectorAll('.ze-playlist-item');
                    items.forEach(function(el){
                        var pVal = parseInt(el.getAttribute('data-p') || '0', 10);
                        if (pVal === track.p) {
                            el.classList.add('active');
                            var iconBox = el.querySelector('.item-icon');
                            if (iconBox) iconBox.innerHTML = "<i class='uil-music'></i>";
                        } else {
                            el.classList.remove('active');
                            var iconBox = el.querySelector('.item-icon');
                            if (iconBox) {
                                var padP = (pVal < 10 ? '0' : '') + pVal;
                                iconBox.innerHTML = padP;
                            }
                        }
                    });

                    if (track.isVideo) {
                        audio.pause();
                        audioContainer.style.display = 'none';
                        videoContainer.style.display = 'block';

                        video.pause();
                        video.src = directMedia;
                        video.load();
                        if (autoPlay) {
                            video.play().catch(function(e){ console.warn('video play blocked', e); });
                        }
                    } else {
                        video.pause();
                        videoContainer.style.display = 'none';
                        audioContainer.style.display = 'block';

                        audio.pause();
                        audio.src = directMedia;
                        audio.load();

                        lrcLoaded = false;
                        lrcData = [];
                        lrcInner.style.transform = 'translateY(0px)';
                        if (directLrc) {
                            loadLrc(directLrc);
                        } else {
                            lrcInner.innerHTML = '<div class="np-lrc-line">暂无歌词</div>';
                        }

                        if (autoPlay) {
                            var p = audio.play();
                            if (p && typeof p.then === 'function') {
                                p.then(function(){ isPlaying = true; toggleUI(true); })
                                 .catch(function(err){ console.warn('autoplay blocked', err); });
                            } else { isPlaying = true; toggleUI(true); }
                        }
                    }
                }

                video.addEventListener('ended', function(){ loadTrack(currentIndex + 1, true); });
                video.addEventListener('error', function(){ console.warn('video error'); }, true);

                if (btnPrev) btnPrev.addEventListener('click', function(e){ e.preventDefault(); loadTrack(currentIndex - 1, true); });
                if (btnNext) btnNext.addEventListener('click', function(e){ e.preventDefault(); loadTrack(currentIndex + 1, true); });

                document.addEventListener('click', function(e){
                    var item = e.target.closest('.ze-playlist-item');
                    if (item) {
                        e.preventDefault();
                        var pVal = parseInt(item.getAttribute('data-p') || '0', 10);
                        if (!isNaN(pVal) && pVal > 0) {
                            loadTrack(pVal - 1, true);
                        }
                    }
                });

                btn.addEventListener('click', function() {
                    if(audio.paused) {
                        var p = audio.play();
                        if (p && typeof p.then === 'function') {
                            p.then(function(){ isPlaying = true; toggleUI(true); })
                             .catch(function(err){ isPlaying = false; toggleUI(false); });
                        } else { isPlaying = true; toggleUI(true); }
                    } else { audio.pause(); isPlaying = false; toggleUI(false); }
                });

                audio.addEventListener('loadstart', function(){
                    lrcInner.querySelectorAll('.np-lrc-line').forEach(function(l){ l.classList.remove('active'); });
                    clearTimeout(stallTimer);
                    stallTimer = setTimeout(function(){
                        if (!metaLoaded) console.warn('加载超时');
                    }, 12000);
                });
                audio.addEventListener('loadedmetadata', function() {
                    clearTimeout(stallTimer);
                    metaLoaded = true;
                    var dur = isNaN(audio.duration)||!isFinite(audio.duration) ? 0 : audio.duration;
                    timeTotal.innerText = fmtTime(dur);
                    seek.max = dur > 0 ? dur : 1;
                    var last = window.sessionStorage.getItem(storageKey);
                    if(last && !isNaN(parseFloat(last)) && parseFloat(last)>0 && dur>parseFloat(last)) {
                        try { audio.currentTime = parseFloat(last); } catch(e){}
                    }
                    var prog = dur>0 ? (audio.currentTime/dur)*100 : 0;
                    seek.style.backgroundImage = 'linear-gradient(to right, #fff 0%, #fff '+prog+'%, transparent '+prog+'%)';
                });
                audio.addEventListener('durationchange', function() {
                    var dur = isNaN(audio.duration)||!isFinite(audio.duration) ? 0 : audio.duration;
                    timeTotal.innerText = fmtTime(dur);
                    seek.max = dur > 0 ? dur : 1;
                });
                audio.addEventListener('timeupdate', function() {
                    if(isSeeking) return;
                    seek.value = audio.currentTime;
                    timeCurr.innerText = fmtTime(audio.currentTime);
                    try { window.sessionStorage.setItem(storageKey, audio.currentTime); } catch(e){}
                    var dur = audio.duration;
                    if(dur>0 && !isNaN(dur) && isFinite(dur)) {
                        var prog = (audio.currentTime/dur)*100;
                        seek.style.backgroundImage = 'linear-gradient(to right, #fff 0%, #fff '+prog+'%, transparent '+prog+'%)';
                    }
                    if(lrcData.length) {
                        var idx = -1;
                        for(var i=0;i<lrcData.length;i++){
                            if(audio.currentTime >= lrcData[i].time) idx = i; else break;
                        }
                        var lines = lrcInner.children;
                        for(var j=0;j<lines.length;j++) lines[j].classList.remove('active');
                        if(idx >= 0 && lines[idx]) {
                            lines[idx].classList.add('active');
                            var container = document.getElementById('np-lrc-box');
                            var cH = container ? container.offsetHeight : 160;
                            var off = lines[idx].offsetTop - cH/2 + lines[idx].offsetHeight/2;
                            if(off<0) off = 0;
                            lrcInner.style.transform = 'translateY(-' + off + 'px)';
                        }
                    }
                });
                audio.addEventListener('play',  function(){ isPlaying = true; toggleUI(true); });
                audio.addEventListener('pause', function(){ isPlaying = false; toggleUI(false); });
                audio.addEventListener('waiting', function(){
                    clearTimeout(stallTimer);
                    stallTimer = setTimeout(function(){
                        if (audio.readyState < 2) console.warn('缓冲卡住');
                    }, 8000);
                });
                audio.addEventListener('playing', function(){ clearTimeout(stallTimer); });
                audio.addEventListener('canplay', function(){ clearTimeout(stallTimer); });
                audio.addEventListener('error', function(e){
                    clearTimeout(stallTimer);
                    lrcInner.innerHTML = '<div class="np-lrc-line">播放失败，请稍后再试</div>';
                });

                audio.addEventListener('ended', function() {
                    isPlaying = false; toggleUI(false);
                    try { audio.currentTime = 0; } catch(e){}
                    loadTrack(currentIndex + 1, true);
                });

                seek.addEventListener('mousedown',  function(){ isSeeking = true; this.style.opacity='0.7'; });
                seek.addEventListener('touchstart', function(){ isSeeking = true; this.style.opacity='0.7'; });
                seek.addEventListener('mouseup',    function(){ isSeeking = false; this.style.opacity='1'; });
                seek.addEventListener('touchend',   function(){ isSeeking = false; this.style.opacity='1'; });
                seek.addEventListener('input', function() {
                    try { audio.currentTime = parseFloat(this.value); } catch(e){}
                    timeCurr.innerText = fmtTime(audio.currentTime);
                    var dur = audio.duration;
                    if(dur>0 && !isNaN(dur) && isFinite(dur)) {
                        var p = (audio.currentTime/dur)*100;
                        this.style.backgroundImage = 'linear-gradient(to right, #fff 0%, #fff '+p+'%, transparent '+p+'%)';
                    }
                });
                seek.addEventListener('change', function() { isSeeking = false; this.style.opacity='1'; });
            })();
            </script>
        </div>
    <?php endif; ?>

    <div class="video-info mt-3">
        <div class="video-info-title">
            <h1><font style="vertical-align: inherit;"><?php echo htmlspecialchars($title); ?></font></h1>
        </div>

        <div class="uk-flex uk-flex-between">
            <div class="video-info-details">
                <span><?php echo htmlspecialchars($workIntro); ?></span>
            </div>
            <!-- 作品详情页增加收藏按钮 -->
            <div class="video-likes" style="display: flex; gap: 10px;">
                <button id="btn-fav-toggle" class="button small soft-warning" onclick="toggleAsmrFavorite()">
                    <i class="uil-star"></i> <span id="fav-text">加载中...</span>
                </button>
                <a href="<?php echo $asmrBase; ?>" class="button small soft-primary">返回列表</a>
            </div>
        </div>

        <!-- 针对该作品详情页加入 JS -->
        <script>
        var currentWorkCode = '<?php echo $workCode; ?>';
        var currentWorkTitle = <?php echo json_encode($title); ?>;
        var currentWorkCover = '<?php echo $coverThumb; ?>';
        var currentWorkLink = '<?php echo $asmrBase . '&w=' . $workCode . ($internalId ? '&iid=' . $internalId : ''); ?>';

        document.addEventListener('DOMContentLoaded', function() {
            var favs = getAsmrFavorites();
            var isFav = favs.some(function(item) { return item.code === currentWorkCode; });
            var btnText = document.getElementById('fav-text');
            if (btnText) {
                btnText.innerText = isFav ? '已收藏 (取消)' : '加入收藏';
            }
        });

        function toggleAsmrFavorite() {
            var favs = getAsmrFavorites();
            var index = favs.findIndex(function(item) { return item.code === currentWorkCode; });
            
            if (index >= 0) {
                favs.splice(index, 1);
                document.getElementById('fav-text').innerText = '加入收藏';
                if(typeof UIkit !== 'undefined') UIkit.notification({message: '已取消收藏', status: 'warning', timeout: 2000});
            } else {
                favs.push({
                    code: currentWorkCode,
                    title: currentWorkTitle,
                    cover: currentWorkCover,
                    link: currentWorkLink
                });
                document.getElementById('fav-text').innerText = '已收藏 (取消)';
                if(typeof UIkit !== 'undefined') UIkit.notification({message: '已加入收藏', status: 'success', timeout: 2000});
            }
            saveAsmrFavorites(favs);
        }
        </script>

        <!-- ===== 单曲与文件夹树样式定义 ===== -->
        <style>
            .ze-playlist-item {
                display: flex; align-items: center; padding: 10px 14px;
                border-radius: 8px; margin-bottom: 4px; text-decoration: none !important;
                transition: all 0.2s ease; background: transparent; border: 1px solid transparent;
            }
            .ze-playlist-item:hover { background: rgba(0,0,0,0.02); border-color: rgba(0,0,0,0.05); transform: translateX(4px); }
            .ze-playlist-item.active { background: rgba(0, 119, 255, 0.06); border-color: rgba(0, 119, 255, 0.15); }
            .ze-playlist-item .item-icon { width: 32px; color: #aaa; font-size: 14px; font-family: monospace; display: flex; align-items: center; }
            .ze-playlist-item.active .item-icon { color: #0077ff; font-size: 18px; }
            .ze-playlist-item .item-title { flex: 1; color: #444; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-size: 14px; }
            .ze-playlist-item.active .item-title { color: #0077ff; font-weight: 600; }
            .ze-playlist-item .item-play-btn { opacity: 0; transform: scale(0.8); transition: all 0.2s ease; color: #0077ff; background: #fff; border-radius: 50%; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 6px rgba(0,0,0,0.1); }
            .ze-playlist-item:hover .item-play-btn, .ze-playlist-item.active .item-play-btn { opacity: 1; transform: scale(1); }
            
            /* ASMR.one 风格文件夹树样式 */
            .folder-tree { list-style: none; padding-left: 0; }
            .folder-node { margin-bottom: 8px; }
            .folder-header {
                display: flex; align-items: center; padding: 10px 14px;
                background: rgba(0, 119, 255, 0.03); border: 1px solid rgba(0, 119, 255, 0.08);
                border-radius: 8px; cursor: pointer; user-select: none; transition: all 0.2s ease;
                color: #444; font-weight: 600; font-size: 14px;
            }
            .folder-header:hover { background: rgba(0, 119, 255, 0.08); }
            .folder-icon { 
                margin-right: 10px; width: 18px; height: 18px; 
                fill: #0077ff; transition: transform 0.2s ease; flex-shrink: 0;
            }
            .folder-node.is-open .folder-icon { transform: rotate(90deg); }
            .folder-tracks {
                display: none; padding-left: 10px; margin-top: 6px;
                border-left: 2px dashed rgba(0, 119, 255, 0.2); margin-left: 18px;
            }
            .folder-node.is-open .folder-tracks { display: block; }

            /* 暗黑模式适配 */
            html.night-mode .ze-playlist-item .item-title,
            html.dark       .ze-playlist-item .item-title { color: #ccc; }
            html.night-mode .ze-playlist-item:hover,
            html.dark       .ze-playlist-item:hover { background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.08); }
            html.night-mode .ze-playlist-item.active,
            html.dark       .ze-playlist-item.active { background: rgba(0, 119, 255, 0.1); border-color: rgba(0, 119, 255, 0.2); }
            html.night-mode .ze-playlist-item .item-play-btn,
            html.dark       .ze-playlist-item .item-play-btn { background: #333; box-shadow: 0 2px 6px rgba(0,0,0,0.5); }
            
            html.night-mode .folder-header, html.dark .folder-header { 
                color: #ddd; background: rgba(255,255,255,0.04); border-color: rgba(255,255,255,0.08); 
            }
            html.night-mode .folder-tracks, html.dark .folder-tracks { border-left-color: rgba(255,255,255,0.15); }
            html.night-mode .folder-icon, html.dark .folder-icon { fill: #88c0d0; }
        </style>

        <div>
            <div style="display: flex; gap: 20px; margin-bottom: 20px; align-items: flex-start;">
                <div style="width: 100px; max-width: 30vw; flex-shrink: 0;">
                    <div class="media media-10x14">
                        <div class="media-content" style="background-image:url('<?php echo htmlspecialchars($coverThumb); ?>');background-size:cover;background-position:center;"></div>
                    </div>
                </div>
                <div style="flex: 1;">
                    <p class="mt-0" style="line-height: 1.8;">
                        作品编号：<?php echo htmlspecialchars($workCode); ?><br>
                        社团：<?php echo htmlspecialchars($circleName ? $circleName : '未知'); ?><br>
                        声优：<?php echo htmlspecialchars(!empty($vas) ? implode('、', $vas) : '未知'); ?><br>
                        分级：<?php echo $ageBadgeText ? $ageBadgeText : '全年龄'; ?>
                    </p>
                </div>
            </div>
            <div class="clear"></div>
        </div>

        <ul uk-tab="" class="uk-tab mt-0" uk-switcher="animation: uk-animation-slide-left-medium, uk-animation-slide-right-medium">
            <li class="uk-active"><a href="#" aria-expanded="true" class="uk-text-small">作品曲目（共 <?php echo $totalSongs; ?> 首）</a></li>
        </ul>

        <!-- ===== 文件夹折叠树状结构渲染区块 ===== -->
        <ul class="uk-switcher uk-margin uk-padding-small pt-0 pl-0">
            <li class="uk-active">
                <?php if(!empty($albumData)): ?>
                    <div class="folder-tree" style="max-height: 500px; overflow-y: auto; padding-right:5px;">
                    <?php foreach($albumData as $groupName => $songs): 
                        // 校验当前曲目是否在该分组中，若是则默认展开文件夹
                        $isOpen = false;
                        foreach($songs as $song) {
                            if($currentP == $song['global_index']) {
                                $isOpen = true;
                                break;
                            }
                        }
                    ?>
                        <div class="folder-node <?php echo $isOpen ? 'is-open' : ''; ?>">
                            <div class="folder-header" onclick="this.parentElement.classList.toggle('is-open')">
                                <svg class="folder-icon" viewBox="0 0 24 24">
                                    <path d="M10 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2h-8l-2-2z"/>
                                </svg>
                                <span><?php echo htmlspecialchars($groupName); ?></span>
                                <span style="margin-left:auto; font-size:12px; color:#999; font-weight:normal;">
                                    <?php echo count($songs); ?> 首
                                </span>
                            </div>
                            <div class="folder-tracks">
                                <?php foreach($songs as $song):
                                    $pIndex = $song['global_index'];
                                    $isActive = ($currentP == $pIndex);
                                    $icon = $isActive ? "<i class='uil-music'></i>" : sprintf("%02d", $pIndex);
                                    $link = $permalinkBase . $pIndex;
                                    $titleSafe = htmlspecialchars($song['title']);
                                    echo "<a href='$link' data-p='$pIndex' class='ze-playlist-item" . ($isActive?' active':'') . "'>
                                            <div class='item-icon'>{$icon}</div>
                                            <div class='item-title'>{$titleSafe}</div>
                                            <div class='item-play-btn'><i class='uil-play'></i></div>
                                          </a>";
                                endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p>暂无曲目</p>
                <?php endif; ?>
            </li>
        </ul>
    </div>

    </div>
</div>
    <?php
                        endif;
                endif;
        endif;
        endif;
    ?>

<?php else: ?>
    <?php
    /* ==================== LIST / SEARCH 模式 ==================== */
    // === 这里修改了 page 参数，避开 Typecho 全局拦截 ===
    $page = isset($_GET['asmr_p']) ? max(1, intval($_GET['asmr_p'])) : (isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1);

    $age = 'all';
    $ageExplicit = false;
    $allowedAges = ($siteMode === 'safe') ? array('general', 'r15', 'all') : array('general', 'r15', 'adult', 'r18', 'all');

    if (isset($_GET['age']) && in_array($_GET['age'], $allowedAges)) {
        $age = $_GET['age'];
        if ($age === 'r18') $age = 'adult';
    }

    if ($age !== 'all') $ageExplicit = true;

    $keyword = isset($_GET['q']) ? trim($_GET['q']) : '';
    $tag = isset($_GET['tag']) ? trim($_GET['tag']) : ($asmrDefaultTag ? $asmrDefaultTag : '');
    $va = isset($_GET['va']) ? trim($_GET['va']) : '';

    if ($mode === 'search') {
        $queryParts = array();
        if (!empty($va)) {
            $queryParts[] = '$va:' . $va . '$';
        }
        if (!empty($tag)) {
            $queryParts[] = '$tag:' . $tag . '$';
        }
        if ($ageExplicit && $age !== 'all') {
            $queryParts[] = '$age:' . $age . '$';
        } elseif ($siteMode === 'safe') {
            // 【核心】当处于安全模式，且没有指定只看 general/r15，默认追加过滤 R18 语法
            $queryParts[] = '$-age:adult$';
        }
        
        if (!empty($keyword)) {
            $queryParts[] = $keyword;
        }
        
        $query = implode(' ', $queryParts);
        if (empty($query)) {
            $query = '$all$';
        }

        $pageTitle = $this->options->asmr_title ? $this->options->asmr_title : 'ASMR';
        if ($keyword || $va || $tag || ($ageExplicit && $age !== 'all')) {
            $pageTitle = '搜索结果';
        }
        $result = $client->searchWorks($query, $page, $asmrPagesize);
    } else {
        $keyword = ''; $va = '';
        $pageTitle = $this->options->asmr_title ? $this->options->asmr_title : 'ASMR';
        // 只有【正常模式】的纯首页，才会走到原生的 listWorks 获取最新加入
        $result = $client->listWorks($page, $asmrPagesize, 'create_date', 'desc');
        if (!$result) {
            $result = $client->searchWorks('$all$', $page, $asmrPagesize);
        }
    }

    $works = $result && isset($result['works']) ? $result['works'] : array();
    $pagination = $result && isset($result['pagination']) ? $result['pagination'] : array();
    $totalCount = isset($pagination['totalCount']) ? intval($pagination['totalCount'])
                : (isset($pagination['total_records']) ? intval($pagination['total_records'])
                : (is_array($works) ? count($works) : 0));
    $totalPages = $asmrPagesize > 0 ? max(1, ceil($totalCount / $asmrPagesize)) : 1;
    if (is_array($works) && count($works) > 0 && $totalPages < 2 && $totalCount < count($works)) $totalPages = 1;

    $vaList   = asmr_extract_vas_from_works($works,  300);
    $tagList  = asmr_extract_tags_from_works($works, 200);
    ?>

    <div uk-grid="" class="uk-grid uk-grid-stack">
        <div class="uk-width-expand uk-first-column">
            <!-- 增加 uk-flex-between 将按钮排向右侧 -->
            <div class="section-header mb-lg-5 border-0 uk-flex-middle uk-flex uk-flex-between">
                <div class="section-header-left">
                    <h3 class="uk-heading-line text-left"><span><?php echo htmlspecialchars($pageTitle); ?>
                    <?php if ($keyword): ?> · 关键词「<?php echo htmlspecialchars($keyword); ?>」<?php endif; ?>
                    <?php if ($va): ?> · 声优「<?php echo htmlspecialchars($va); ?>」<?php endif; ?>
                    <?php if ($tag): ?> · 标签「<?php echo htmlspecialchars($tag); ?>」<?php endif; ?>
                    <?php if ($ageExplicit && $age !== 'all'): ?> · 分级「<?php
                        $ageLabel = array('general' => '全年龄', 'r15' => 'R15', 'adult' => 'R18');
                        echo isset($ageLabel[$age]) ? $ageLabel[$age] : strtoupper($age);
                    ?>」<?php endif; ?>
                    <?php if ($totalCount): ?> <small class="uk-text-muted">(<?php echo $totalCount; ?> 条)</small><?php endif; ?>
                    </span></h3>
                </div>
                <!-- 列表右上角的收藏按钮 -->
                <div class="section-header-right">
                    <button onclick="showAsmrFavorites()" class="button small soft-warning"><i class="uil-star"></i> 收藏列表</button>
                </div>
            </div>

            <form method="get" action="" class="uk-margin-small-bottom" id="asmr-filter-form">
                <input type="hidden" name="asmr" value="1">
                <div class="uk-flex uk-flex-middle" style="gap:8px; flex-wrap:wrap;">
                    <div class="uk-flex uk-flex-middle" style="gap:4px;">
                        <input type="text" name="q" value="<?php echo htmlspecialchars($keyword); ?>" placeholder="搜索作品/社团/编号..." class="uk-input" style="max-width:240px; flex:1;">
                        <button type="submit" class="button small primary">搜索</button>
                    </div>
                    <select name="va" class="uk-select" style="max-width:160px;" onchange="this.form.submit()">
                        <option value="">声优</option>
                        <?php foreach ($vaList as $v): ?>
                            <option value="<?php echo htmlspecialchars($v); ?>" <?php echo $va===$v?'selected':''; ?>><?php echo htmlspecialchars($v); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select name="tag" class="uk-select" style="max-width:200px;" onchange="this.form.submit()">
                        <option value="">标签</option>
                        <?php foreach ($tagList as $t): ?>
                            <option value="<?php echo htmlspecialchars($t); ?>" <?php echo $tag===$t?'selected':''; ?>><?php echo htmlspecialchars($t); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select name="age" class="uk-select" style="max-width:120px;" onchange="this.form.submit()">
                        <option value="all" <?php echo $age==='all'?'selected':''; ?>>全部</option>
                        <option value="general" <?php echo $age==='general'?'selected':''; ?>>全年龄</option>
                        <option value="r15" <?php echo $age==='r15'?'selected':''; ?>>R15</option>
                        <?php if ($siteMode !== 'safe'): ?>
                        <option value="adult" <?php echo $age==='adult'?'selected':''; ?>>R18</option>
                        <?php endif; ?>
                    </select>
                    <?php if ($mode === 'search' || $ageExplicit || $va || $tag || $keyword): ?>
                        <a href="<?php echo $asmrBase; ?>" class="button small soft-primary">重置</a>
                    <?php endif; ?>
                </div>
                <p class="uk-text-small uk-text-muted mt-1 mb-0">
                    请大家尽量挂梯子访问此页，筛选维度各自独立、可叠加
                </p>
            </form>

            <?php if (!empty($works)): ?>
            <div class="uk-child-width-1-8@l uk-child-width-1-6@m uk-child-width-1-3@s uk-child-width-1-3 uk-grid uk-grid-stack" uk-grid>
                <?php foreach($works as $work):
                    $wid = isset($work['id']) ? intval($work['id']) : 0;
                    $wcode = isset($work['source_id']) ? strtoupper($work['source_id']) : asmr_code_from_id($wid, (isset($work['source_id'])?$work['source_id']:''));
                    $wtitle = isset($work['title']) ? $work['title'] : (isset($work['name']) ? $work['name'] : $wcode);
                    $wcover = $wid ? asmr_cover_thumb_url($wid) : '';
                    $wlink = $asmrBase . '&w=' . strtoupper($wcode) . '&p=1' . ($wid ? '&iid=' . $wid : '');
                    $ageBadge = asmr_get_age_badge($work);
                    $badgeHtml = '';
                    if ($ageBadge === 'R18') {
                        $badgeHtml = '<span class="uk-badge" style="position:absolute;top:8px;left:8px;background:#ef4444;z-index:2">R18</span>';
                    } elseif ($ageBadge === 'R15') {
                        $badgeHtml = '<span class="uk-badge" style="position:absolute;top:8px;left:8px;background:#f59e0b;z-index:2">R15</span>';
                    }
                ?>
                <li tabindex="-1" class="uk-animation-slide-bottom-small">
                    <a href="<?php echo $wlink; ?>" class="video-post">
                        <div class="media" style="position:relative;">
                            <?php echo $badgeHtml; ?>
                            <?php if ($wcover): ?>
                                <div class="media-content scrollLoading" data-xurl="<?php echo htmlspecialchars($wcover); ?>"></div>
                            <?php endif; ?>
                        </div>
                        <div class="video-post-content">
                            <h3><font style="vertical-align: inherit;"><font style="vertical-align: inherit;"><?php echo htmlspecialchars($wtitle); ?></font></font></h3>
                        </div>
                    </a>
                </li>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="uk-alert-warning" uk-alert>
                <p>暂无 ASMR 作品数据。<?php if (!$result): ?>接口可能暂时不可用，请稍后再试。<?php else: ?>请尝试更换搜索条件。<?php endif; ?></p>
            </div>
            <?php endif; ?>

            <?php if ($totalPages > 1):
                $pageQuery = ($keyword?'&q='.urlencode($keyword):'') . ($tag?'&tag='.urlencode($tag):'') . ($va?'&va='.urlencode($va):'') . ($age?'&age='.urlencode($age):'');
                // 页码显示逻辑：显示当前页前后各 3 页，并始终显示首尾页（带省略号）
                $half = 3;
                $startP = max(1, $page - $half);
                $endP = min($totalPages, $page + $half);
                $showFirst = ($startP > 1);
                $showLast = ($endP < $totalPages);
                $showFirstDots = ($startP > 2);
                $showLastDots = ($endP < $totalPages - 1);
            ?>
            <style>
                .asmr-pagination { display:flex; align-items:center; justify-content:center; flex-wrap:wrap; gap:6px; padding:20px 0; }
                .asmr-pagination a, .asmr-pagination span { display:inline-flex; align-items:center; justify-content:center; min-width:36px; height:36px; padding:0 10px; border-radius:8px; font-size:14px; text-decoration:none !important; transition:all 0.2s; }
                .asmr-pagination a { background:rgba(0,119,255,0.06); color:#0077ff; border:1px solid rgba(0,119,255,0.12); }
                .asmr-pagination a:hover { background:rgba(0,119,255,0.15); transform:translateY(-1px); }
                .asmr-pagination .current { background:#0077ff; color:#fff; font-weight:600; border:1px solid #0077ff; }
                .asmr-pagination .dots { color:#999; border:none; background:none; min-width:24px; cursor:default; }
                .asmr-pagination .prev, .asmr-pagination .next { font-weight:500; padding:0 14px; }
                .asmr-pagination .page-jump { display:inline-flex; align-items:center; gap:6px; margin-left:10px; }
                .asmr-pagination .page-jump input { width:56px; height:36px; border:1px solid rgba(0,119,255,0.2); border-radius:8px; text-align:center; font-size:14px; outline:none; background:transparent; }
                .asmr-pagination .page-jump input:focus { border-color:#0077ff; box-shadow:0 0 0 2px rgba(0,119,255,0.1); }
                .asmr-pagination .page-jump button { height:36px; padding:0 12px; border:none; border-radius:8px; background:#0077ff; color:#fff; font-size:13px; cursor:pointer; transition:background 0.2s; }
                .asmr-pagination .page-jump button:hover { background:#0066dd; }
                .asmr-pagination .page-info { color:#999; font-size:13px; margin-left:8px; }
                html.night-mode .asmr-pagination a, html.dark .asmr-pagination a { background:rgba(255,255,255,0.06); color:#88c0d0; border-color:rgba(255,255,255,0.1); }
                html.night-mode .asmr-pagination a:hover, html.dark .asmr-pagination a:hover { background:rgba(255,255,255,0.12); }
                html.night-mode .asmr-pagination .current, html.dark .asmr-pagination .current { background:#0077ff; color:#fff; }
                html.night-mode .asmr-pagination .page-jump input, html.dark .asmr-pagination .page-jump input { border-color:rgba(255,255,255,0.15); color:#ddd; background:rgba(255,255,255,0.05); }
            </style>
            <nav class="asmr-pagination" role="navigation">
                <?php if ($page > 1): ?>
                    <a class="prev" href="<?php echo $asmrBase . '&asmr_p=' . ($page-1) . $pageQuery; ?>">‹ 上一页</a>
                <?php endif; ?>

                <?php if ($showFirst): ?>
                    <a href="<?php echo $asmrBase . '&asmr_p=1' . $pageQuery; ?>">1</a>
                <?php endif; ?>
                <?php if ($showFirstDots): ?><span class="dots">…</span><?php endif; ?>

                <?php for ($i = $startP; $i <= $endP; $i++): ?>
                    <?php if ($i == $page): ?>
                        <span class="current"><?php echo $i; ?></span>
                    <?php else: ?>
                        <a href="<?php echo $asmrBase . '&asmr_p=' . $i . $pageQuery; ?>"><?php echo $i; ?></a>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($showLastDots): ?><span class="dots">…</span><?php endif; ?>
                <?php if ($showLast): ?>
                    <a href="<?php echo $asmrBase . '&asmr_p=' . $totalPages . $pageQuery; ?>"><?php echo $totalPages; ?></a>
                <?php endif; ?>

                <?php if ($page < $totalPages): ?>
                    <a class="next" href="<?php echo $asmrBase . '&asmr_p=' . ($page+1) . $pageQuery; ?>">下一页 ›</a>
                <?php endif; ?>

                <div class="page-jump">
                    <input type="number" id="asmr-page-jump" min="1" max="<?php echo $totalPages; ?>" placeholder="页码" onkeydown="if(event.key==='Enter'){var v=parseInt(this.value);if(v>=1&&v<=<?php echo $totalPages; ?>)window.location.href='<?php echo $asmrBase . '&asmr_p='; ?>'+v+'<?php echo addslashes($pageQuery); ?>';}">
                    <button onclick="var v=parseInt(document.getElementById('asmr-page-jump').value);if(v>=1&&v<=<?php echo $totalPages; ?>)window.location.href='<?php echo $asmrBase . '&asmr_p='; ?>'+v+'<?php echo addslashes($pageQuery); ?>';">跳转</button>
                </div>
                <span class="page-info">共 <?php echo $totalPages; ?> 页</span>
            </nav>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<!-- ===== ASMR 收藏功能专用模态框及全局 JS ===== -->
<div id="asmr-favorites-modal" uk-modal>
    <div class="uk-modal-dialog uk-modal-body" style="max-width: 800px;">
        <button class="uk-modal-close-default" type="button" uk-close></button>
        <h2 class="uk-modal-title" style="font-size: 20px; font-weight: bold;">我的收藏</h2>
        <div class="uk-alert-warning" uk-alert>
            <p>提醒：收藏的数据仅保存在您当前的浏览器缓存中。如果您清理了浏览器痕迹，该收藏列表会自动消失哦！</p>
        </div>
        <div id="asmr-favorites-container" class="uk-child-width-1-4@m uk-child-width-1-3@s uk-child-width-1-2 uk-grid" uk-grid>
            <!-- 收藏列表会由 JS 动态注入在这里 -->
        </div>
    </div>
</div>

<script>
// 全局 ASMR 收藏管理逻辑 (借助 LocalStorage)
var ASMR_FAV_KEY = 'asmr_favorites_data';

function getAsmrFavorites() {
    try {
        var data = localStorage.getItem(ASMR_FAV_KEY);
        return data ? JSON.parse(data) : [];
    } catch(e) { return []; }
}

function saveAsmrFavorites(favs) {
    try {
        localStorage.setItem(ASMR_FAV_KEY, JSON.stringify(favs));
    } catch(e) { console.error('LocalStorage 读写出错', e); }
}

function showAsmrFavorites() {
    var favs = getAsmrFavorites();
    var container = document.getElementById('asmr-favorites-container');
    container.innerHTML = '';
    
    if (favs.length === 0) {
        container.innerHTML = '<div class="uk-width-1-1"><p class="uk-text-muted uk-text-center">暂无收藏内容，快去逛逛吧~</p></div>';
    } else {
        var html = '';
        favs.reverse().forEach(function(item) {
            html += '<div style="margin-bottom: 20px;">' +
                        '<a href="' + item.link + '" class="video-post" style="display: block;">' +
                            '<div class="media">' +
                                '<div class="media-content" style="background-image:url(' + item.cover + '); background-size: cover; background-position: center; border-radius: 8px;"></div>' +
                            '</div>' +
                            '<div class="video-post-content" style="padding-top: 8px;">' +
                                '<h3 style="font-size: 14px; margin: 0; text-overflow: ellipsis; white-space: nowrap; overflow: hidden;" title="' + item.title + '">' + item.title + '</h3>' +
                            '</div>' +
                        '</a>' +
                        '<button onclick="removeAsmrFavorite(\'' + item.code + '\')" class="button small soft-danger" style="width: 100%; margin-top: 8px; border-radius: 4px; padding: 4px 0;">取消收藏</button>' +
                    '</div>';
        });
        container.innerHTML = html;
    }
    if(typeof UIkit !== 'undefined') {
        UIkit.modal('#asmr-favorites-modal').show();
    } else {
        alert('暂不支持弹窗或框架加载异常');
    }
}

function removeAsmrFavorite(code) {
    var favs = getAsmrFavorites();
    var newFavs = favs.filter(function(item) { return item.code !== code; });
    saveAsmrFavorites(newFavs);
    showAsmrFavorites(); // 刷新 UI
    
    // 如果恰好当前就在被取消的详情页上，则同步更新一下主界面的按钮文本
    if (typeof currentWorkCode !== 'undefined' && currentWorkCode === code) {
        var btnText = document.getElementById('fav-text');
        if (btnText) btnText.innerText = '加入收藏';
    }
}
</script>

</div>
<?php
/* ========== 页面缓存写入 ========== */
$_asmrPageHtml = ob_get_clean();
_asmr_cache_write($_asmrPageCacheKey, $_asmrPageHtml);
echo $_asmrPageHtml;
?>
<?php $this->need('footer.php'); ?>