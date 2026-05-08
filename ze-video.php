<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>

<?php
/* --- 1. 历史记录逻辑 --- */
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
    }
}

/* --- 2. 核心数据解析逻辑 --- */
$mp4Field = $this->fields->mp4 ? $this->fields->mp4 : '';
$mp4Field = str_replace(array("\r\n", "\r", "\n"), "\n", $mp4Field);
$rawLines = explode("\n", $mp4Field);

// 初始化变量
$albumData = [];   // 分组数据
$flatList = [];    // 扁平数据
$currentGroup = '曲目列表'; // 默认分组

// 开始解析
foreach($rawLines as $line){
    $line = trim($line);
    if(empty($line)) continue;

    // 处理分组
    if(strpos($line, '#') === 0){
        $currentGroup = substr($line, 1);
        continue;
    }

    // 解析歌曲
    $parts = explode('$', $line);
    if(count($parts) < 2 && strpos($line, '$') === false) continue; 

    $songInfo = [
        'title' => isset($parts[0]) ? trim($parts[0]) : '未知标题',
        'url'   => isset($parts[1]) ? trim($parts[1]) : '',
        'lrc'   => isset($parts[2]) ? trim($parts[2]) : ''
    ];

    $songInfo['global_index'] = count($flatList) + 1; 
    $albumData[$currentGroup][] = $songInfo;
    $flatList[] = $songInfo;
}

/* --- 3. 确定当前播放曲目及上一曲/下一曲 --- */
$currentP = 1; 
if(isset($_GET['p']) && is_numeric($_GET['p'])) {
    $currentP = intval($_GET['p']);
}

// 边界检查
$totalSongs = count($flatList);
if($currentP < 1) $currentP = 1;
if($totalSongs > 0 && $currentP > $totalSongs) $currentP = 1;

// 获取当前歌曲
$currentSong = null;
if(!empty($flatList)) {
    $currentSong = $flatList[$currentP - 1];
}

// 计算上一曲和下一曲
$prevP = ($currentP - 1 < 1) ? $totalSongs : $currentP - 1;
$nextP = ($currentP + 1 > $totalSongs) ? 1 : $currentP + 1;

$prevUrl = $this->permalink . '?action=get&p=' . $prevP;
$nextUrl = $this->permalink . '?action=get&p=' . $nextP;
?>

<div uk-grid="" class="uk-grid">
    <div class="uk-width-3-4@m uk-first-column">

    <?php if ($currentSong): ?>
        
        <div id="video-box" uk-sticky="top: 400 ;media : @s" class="uk-sticky" style="background:transparent;box-shadow:none;">
            <?php 
            // 权限判断
            if( !$this->user->hasLogin() && ( ($this->options->login>0 && $this->options->login<=$currentP) || $this->fields->isLogin == '1' ) ): 
            ?>
                <div class="uk-alert-danger" uk-alert>
                    <p>
                        <?php if($this->fields->isLogin == '1'): ?>
                            本专辑需要注册登录本站后才可收听！
                        <?php else: ?>
                            从本曲起，后续内容需要注册登录本站后才可收听！
                        <?php endif; ?>
                    </p>
                    <a href="<?php $this->options->loginUrl(); ?>" class="button small soft-primary">立即登录</a>
                </div>
            <?php else: ?>
                <?php if($this->hidden||$this->titleshow): ?>
                    <div class="uk-alert-danger" uk-alert><p>本内容已加密，请在下方输入正确密码！</p></div>
                <?php else: ?>
                    
                    <div id="native-player-box">
                        <?php
                        $audioUrl = $currentSong['url'];
                        $lrcUrl   = $currentSong['lrc'];
                        $songTitle = $currentSong['title'];
                        $coverField = $this->fields->thumb; 
                        $coverUrl = !empty($coverField) ? $coverField : '/cover.png';
                        $songArtistField = $this->fields->name; 
                        $songArtist = !empty($songArtistField) ? $songArtistField : 'Audio';
                        ?>

                        <style>
                            /* --- PC 端样式 --- */
                            #native-player-box {
                                position: relative; width: 100%; height: 420px; overflow: hidden;
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
                                background-image: url('<?php echo htmlspecialchars($coverUrl); ?>');
                                background-size: cover; background-position: center;
                                border: 5px solid rgba(255,255,255,0.1);
                                box-shadow: 0 10px 40px rgba(0,0,0,0.6);
                                animation: np-spin 25s linear infinite; animation-play-state: paused;
                            }
                            .np-cover.playing { animation-play-state: running; }
                            .np-info-wrap { flex: 1; height: 260px; display: flex; flex-direction: column; justify-content: center; min-width: 0; }
                            .np-title { font-size: 28px; font-weight: 700; margin-bottom: 5px; color:#fff; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
                            .np-artist { font-size: 16px; color: rgba(255,255,255,0.6); margin-bottom: 25px; }
                            .np-lrc-box {
                                height: 80px; overflow: hidden; margin-bottom: 30px; position: relative;
                                mask-image: linear-gradient(to bottom, transparent, black 15%, black 85%, transparent);
                                -webkit-mask-image: linear-gradient(to bottom, transparent, black 15%, black 85%, transparent);
                            }
                            .np-lrc-inner { transition: transform 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94); text-align: left; }
                            .np-lrc-line { 
                                font-size: 15px; 
                                color: rgba(255,255,255,0.4); 
                                line-height: 1.6; 
                                padding: 6px 0;
                                min-height: 24px; 
                                transition: all 0.3s; 
                            }
                            .np-lrc-line.active { 
                                color: #fff; 
                                font-size: 18px; 
                                font-weight: bold; 
                                transform: scale(1.02); 
                                transform-origin: left center;
                            }
                            
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
                                transition: all 0.3s ease;
                                cursor: pointer;
                                background-image: linear-gradient(to right, #fff 0%, #fff 0%, transparent 0%);
                                background-size: 100% 100%;
                                background-repeat: no-repeat;
                            }
                            .np-range:hover {
                                background: rgba(255,255,255,0.3);
                                height: 8px;
                            }
                            .np-range::-webkit-slider-thumb {
                                -webkit-appearance: none; width: 14px; height: 14px;
                                background: #fff; border-radius: 50%; cursor: pointer;
                                box-shadow: 0 0 10px rgba(0,0,0,0.3);
                                transition: all 0.3s ease;
                                opacity: 0.9;
                            }
                            .np-range:hover::-webkit-slider-thumb {
                                width: 18px; height: 18px;
                                opacity: 1;
                                box-shadow: 0 0 15px rgba(255,255,255,0.5);
                            }
                            @keyframes np-spin { 100% { transform: rotate(360deg); } }

                            @media (max-width: 768px) {
                                #native-player-box { height: auto; min-height: 580px; padding-bottom: 40px; }
                                .np-body { flex-direction: column; padding: 40px 25px; text-align: center; }
                                .np-cover-wrap { margin-right: 0; margin-bottom: 30px; flex: 0 0 auto; height: auto; }
                                .np-cover { width: 220px; height: 220px; border-width: 4px; }
                                .np-info-wrap { width: 100%; height: auto; display: block; }
                                .np-title { font-size: 26px; margin-bottom: 5px; }
                                .np-artist { font-size: 16px; margin-bottom: 25px; }
                                .np-lrc-inner { text-align: center; }
                                .np-lrc-line.active { transform-origin: center center; }
                                .np-controls { flex-wrap: wrap; justify-content: center; gap: 8px; margin-top: 10px; }
                                .np-progress-wrap { width: 100%; flex: 0 0 100%; order: 1; margin-bottom: 10px; }
                                .np-btn-play, .np-btn-nav { order: 2; }
                                .np-btn-play { width: 64px; height: 64px; }
                                .np-btn-play svg { width: 28px; height: 28px; }
                                .np-btn-nav { width: 48px; height: 48px; margin: 0 2px; }
                                .np-range::-webkit-slider-thumb {
                                width: 20px; height: 20px;
                                transition: all 0.3s ease;
                                opacity: 0.9;
                            }
                            .np-range:hover::-webkit-slider-thumb {
                                width: 24px; height: 24px;
                                opacity: 1;
                                box-shadow: 0 0 15px rgba(255,255,255,0.5);
                            }
                            }
                        </style>

                        <div class="np-bg"></div>
                        <div class="np-body">
                            <div class="np-cover-wrap">
                                <div class="np-cover" id="np-cover"></div>
                            </div>
                            <div class="np-info-wrap">
                                <div class="np-meta">
                                    <div class="np-title"><?php echo $songTitle; ?></div>
                                    <div class="np-artist"><?php echo $songArtist; ?></div>
                                </div>
                                <div class="np-lrc-box" id="np-lrc-box">
                                    <div class="np-lrc-inner" id="np-lrc-inner"><div class="np-lrc-line">Loading...</div></div>
                                </div>
                                
                                <div class="np-controls">
                                    <button class="np-btn-nav" id="np-mode-btn" title="顺序播放"><svg id="icon-mode-order" viewBox="0 0 24 24"><path d="M3 13h2v-2H3v2zm0 4h2v-2H3v2zm0-8h2V7H3v2zm4 4h14v-2H7v2zm0 4h14v-2H7v2zM7 7v2h14V7H7z"/></svg><svg id="icon-mode-repeat" style="display:none" viewBox="0 0 24 24"><path d="M7 7h10v3l4-4-4-4v3H5v6h2V7zm10 10H7v-3l-4 4 4 4v-3h12v-6h-2v4z"/></svg><svg id="icon-mode-shuffle" style="display:none" viewBox="0 0 24 24"><path d="M10.59 9.17L5.41 4 4 5.41l5.17 5.17 1.42-1.41zM14.5 4l2.04 2.04L4 18.59 5.41 20 17.96 7.46 20 9.5V4h-5.5zm.33 9.41l-1.41 1.41 3.13 3.13L14.5 20H20v-5.5l-2.04 2.04-3.13-3.13z"/></svg></button>
                                    <button class="np-btn-nav" id="np-prev-btn" title="上一曲"><svg viewBox="0 0 24 24"><path d="M6 6h2v12H6zm3.5 6l8.5 6V6z"/></svg></button>
                                    <button class="np-btn-play" id="np-play-btn">
                                        <svg id="icon-play" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                        <svg id="icon-pause" viewBox="0 0 24 24" style="display:none"><path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/></svg>
                                    </button>
                                    <button class="np-btn-nav" id="np-next-btn" title="下一曲"><svg viewBox="0 0 24 24"><path d="M6 18l8.5-6L6 6v12zM16 6v12h2V6h-2z"/></svg></button>
                                    <div class="np-progress-wrap">
                                        <span id="np-time-current">00:00</span>
                                        <input type="range" class="np-range" id="np-seek" value="0" min="0" max="100" step="0.1">
                                        <span id="np-time-total">00:00</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <script>
                        (function(){
                            var playlist = <?php echo json_encode($flatList); ?>;
                            var currentIndex = <?php echo $currentP - 1; ?>;
                            var audio = new Audio();
                            var btnPlay = document.getElementById('np-play-btn');
                            var btnPrev = document.getElementById('np-prev-btn');
                            var btnNext = document.getElementById('np-next-btn');
                            var btnMode = document.getElementById('np-mode-btn');
                            var iconPlay = document.getElementById('icon-play');
                            var iconPause = document.getElementById('icon-pause');
                            var iconModeOrder = document.getElementById('icon-mode-order');
                            var iconModeRepeat = document.getElementById('icon-mode-repeat');
                            var iconModeShuffle = document.getElementById('icon-mode-shuffle');
                            var cover = document.getElementById('np-cover');
                            var seek = document.getElementById('np-seek');
                            var timeCurr = document.getElementById('np-time-current');
                            var timeTotal = document.getElementById('np-time-total');
                            var lrcInner = document.getElementById('np-lrc-inner');
                            var titleEl = document.querySelector('.np-title');
                            var artistEl = document.querySelector('.np-artist');
                            var isPlaying = false;
                            var isSeeking = false;
                            var lrcData = [];
                            var songArtist = "<?php echo $songArtist; ?>";
                            var playMode = 'order';
                            var lastShuffledIndex = -1;

                            function fmtTime(s) {
                                var m = Math.floor(s / 60); var s = Math.floor(s % 60);
                                return (m<10?'0'+m:m) + ':' + (s<10?'0'+s:s);
                            }
                            function toggleUI(play) {
                                if(play) {
                                    iconPlay.style.display = 'none'; iconPause.style.display = 'block';
                                    cover.classList.add('playing'); btnPlay.classList.add('is-playing');
                                } else {
                                    iconPlay.style.display = 'block'; iconPause.style.display = 'none';
                                    cover.classList.remove('playing'); btnPlay.classList.remove('is-playing');
                                }
                            }
                            
                            function parseLrc(text) {
                                if(!text) return [];
                                var result = [];
                                var isVtt = text.trim().startsWith("WEBVTT");
                                var lines = text.split('\n');
                                
                                if(isVtt) {
                                    var vttTimeExp = /(\d{2}:)?(\d{2}:\d{2}\.\d{3})/;
                                    var currentStartTime = -1;
                                    for(var i=0; i<lines.length; i++) {
                                        var line = lines[i].trim();
                                        if(line === "WEBVTT" || line === "") continue;
                                        if(line.includes('-->')) {
                                            var match = vttTimeExp.exec(line);
                                            if(match) {
                                                var parts = match[0].split(':');
                                                var sec = 0;
                                                if(parts.length === 3) {
                                                    sec = parseInt(parts[0])*3600 + parseInt(parts[1])*60 + parseFloat(parts[2]);
                                                } else {
                                                    sec = parseInt(parts[0])*60 + parseFloat(parts[1]);
                                                }
                                                currentStartTime = sec;
                                            }
                                        } else if(currentStartTime >= 0) {
                                            var content = line.replace(/<[^>]+>/g, ''); 
                                            if(content.includes('->')) {
                                                content = content.split('->').map(function(s){return s.trim()}).join('\n');
                                            }
                                            result.push({time: currentStartTime, text: content});
                                            currentStartTime = -1;
                                        }
                                    }
                                } else {
                                    var timeExp = /\[(\d{2}):(\d{2})(\.\d{2,3})?\]/;
                                    for(var i=0; i<lines.length; i++) {
                                        var line = lines[i].trim();
                                        var match = timeExp.exec(line);
                                        if(match) {
                                            var t = parseInt(match[1])*60 + parseInt(match[2]) + (match[3]?parseFloat(match[3]):0);
                                            var content = line.replace(timeExp, '').trim();
                                            if(content.includes('->')) {
                                                content = content.split('->').map(function(s){return s.trim()}).join('\n');
                                            }
                                            if(content) result.push({time: t, text: content});
                                        }
                                    }
                                }

                                result.sort(function(a, b){ return a.time - b.time; });
                                var mergedResult = [];
                                if(result.length > 0) {
                                    var current = result[0];
                                    for(var j=1; j<result.length; j++) {
                                        var next = result[j];
                                        if(Math.abs(next.time - current.time) < 0.2) {
                                            current.text += '\n' + next.text;
                                        } else {
                                            mergedResult.push(current);
                                            current = next;
                                        }
                                    }
                                    mergedResult.push(current);
                                }
                                return mergedResult;
                            }

                            function renderLrc(data) {
                                if(!data.length) { lrcInner.innerHTML = '<div class="np-lrc-line">纯音乐 / 无歌词</div>'; return; }
                                var html = '';
                                for(var i=0; i<data.length; i++) {
                                    var textDisplay = data[i].text.replace(/\n/g, '<br>');
                                    html += '<div class="np-lrc-line">' + textDisplay + '</div>';
                                }
                                lrcInner.innerHTML = html;
                            }

                            function loadLrc(lrcUrl) {
                                if(lrcUrl) {
                                    fetch(lrcUrl).then(r=>r.text()).then(t=>{ 
                                        lrcData = parseLrc(t); 
                                        renderLrc(lrcData); 
                                    }).catch(()=>{ 
                                        lrcInner.innerHTML = '<div class="np-lrc-line">歌词加载失败</div>'; 
                                    });
                                } else { 
                                    lrcInner.innerHTML = '<div class="np-lrc-line">暂无歌词</div>'; 
                                }
                            }

                            function updatePlaylistUI() {
                                var items = document.querySelectorAll('.ze-playlist-item');
                                items.forEach(function(item, idx) {
                                    if(idx === currentIndex) {
                                        item.classList.add('active');
                                        var iconEl = item.querySelector('.item-icon');
                                        if(iconEl) iconEl.innerHTML = "<i class='uil-music'></i>";
                                    } else {
                                        item.classList.remove('active');
                                        var iconEl = item.querySelector('.item-icon');
                                        if(iconEl) iconEl.innerHTML = sprintf("%02d", idx + 1);
                                    }
                                });
                            }

                            function sprintf(format, number) {
                                return format.replace('%02d', number < 10 ? '0' + number : number);
                            }

                            function updateModeUI() {
                                iconModeOrder.style.display = 'none';
                                iconModeRepeat.style.display = 'none';
                                iconModeShuffle.style.display = 'none';
                                
                                if(playMode === 'order') {
                                    iconModeOrder.style.display = 'block';
                                    btnMode.title = '顺序播放';
                                } else if(playMode === 'repeat') {
                                    iconModeRepeat.style.display = 'block';
                                    btnMode.title = '单曲循环';
                                } else if(playMode === 'shuffle') {
                                    iconModeShuffle.style.display = 'block';
                                    btnMode.title = '随机播放';
                                }
                            }

                            function togglePlayMode() {
                                if(playMode === 'order') {
                                    playMode = 'repeat';
                                } else if(playMode === 'repeat') {
                                    playMode = 'shuffle';
                                } else {
                                    playMode = 'order';
                                }
                                updateModeUI();
                            }

                            function getNextIndex() {
                                if(playMode === 'repeat') {
                                    return currentIndex;
                                } else if(playMode === 'shuffle') {
                                    var newIndex;
                                    do {
                                        newIndex = Math.floor(Math.random() * playlist.length);
                                    } while(newIndex === currentIndex && playlist.length > 1);
                                    lastShuffledIndex = currentIndex;
                                    return newIndex;
                                } else {
                                    return (currentIndex + 1) % playlist.length;
                                }
                            }

                            function getPrevIndex() {
                                if(playMode === 'shuffle' && lastShuffledIndex !== -1) {
                                    var temp = lastShuffledIndex;
                                    lastShuffledIndex = currentIndex;
                                    return temp;
                                }
                                return (currentIndex - 1 + playlist.length) % playlist.length;
                            }

                            function loadTrack(index, autoPlay) {
                                if(index < 0) index = playlist.length - 1;
                                if(index >= playlist.length) index = 0;
                                
                                currentIndex = index;
                                var song = playlist[currentIndex];
                                
                                audio.src = song.url;
                                titleEl.textContent = song.title;
                                lrcData = [];
                                loadLrc(song.lrc);
                                updatePlaylistUI();
                                
                                if(autoPlay) {
                                    audio.play();
                                    isPlaying = true;
                                    toggleUI(true);
                                }
                            }

                            btnPlay.addEventListener('click', function() {
                                if(audio.paused) { 
                                    audio.play(); 
                                    isPlaying = true; 
                                } else { 
                                    audio.pause(); 
                                    isPlaying = false; 
                                }
                                toggleUI(isPlaying);
                            });

                            btnMode.addEventListener('click', function() {
                                togglePlayMode();
                            });

                            btnPrev.addEventListener('click', function() {
                                loadTrack(getPrevIndex(), true);
                            });

                            btnNext.addEventListener('click', function() {
                                loadTrack(getNextIndex(), true);
                            });

                            audio.addEventListener('loadedmetadata', function() {
                                var duration = audio.duration;
                                if(isNaN(duration) || duration === Infinity) {
                                    duration = 0;
                                }
                                timeTotal.innerText = fmtTime(duration);
                                seek.max = duration;
                            });
                            
                            audio.addEventListener('durationchange', function() {
                                var duration = audio.duration;
                                if(isNaN(duration) || duration === Infinity) {
                                    duration = 0;
                                }
                                timeTotal.innerText = fmtTime(duration);
                                seek.max = duration;
                                var progress = (audio.currentTime / duration) * 100;
                                seek.style.backgroundImage = `linear-gradient(to right, #fff 0%, #fff ${progress}%, transparent ${progress}%)`;
                            });

                            audio.addEventListener('timeupdate', function() {
                                if(isSeeking) return;
                                seek.value = audio.currentTime;
                                timeCurr.innerText = fmtTime(audio.currentTime);
                                
                                if(!isNaN(audio.duration) && audio.duration > 0) {
                                    var progress = (audio.currentTime / audio.duration) * 100;
                                    seek.style.backgroundImage = `linear-gradient(to right, #fff 0%, #fff ${progress}%, transparent ${progress}%)`;
                                }
                                
                                if(lrcData.length) {
                                    var idx = -1;
                                    for(var i=0; i<lrcData.length; i++) {
                                        if(audio.currentTime >= lrcData[i].time) idx = i; else break;
                                    }
                                    
                                    var lines = lrcInner.children;
                                    for(var j=0; j<lines.length; j++) lines[j].classList.remove('active');
                                    
                                    if(idx !== -1 && lines[idx]) {
                                        var activeLine = lines[idx];
                                        activeLine.classList.add('active');
                                        var containerHeight = 80; 
                                        var offset = activeLine.offsetTop - (containerHeight / 2) + (activeLine.offsetHeight / 2);
                                        if(offset < 0) offset = 0;
                                        lrcInner.style.transform = 'translateY(-' + offset + 'px)';
                                    }
                                }
                            });

                            seek.addEventListener('mousedown', function() { 
                                isSeeking = true;
                                this.style.opacity = '0.7';
                            });
                            seek.addEventListener('touchstart', function() { 
                                isSeeking = true;
                                this.style.opacity = '0.7';
                            });
                            seek.addEventListener('mouseup', function() { 
                                isSeeking = false;
                                this.style.opacity = '1';
                            });
                            seek.addEventListener('touchend', function() { 
                                isSeeking = false;
                                this.style.opacity = '1';
                            });
                            seek.addEventListener('input', function() { 
                                audio.currentTime = this.value; 
                                timeCurr.innerText = fmtTime(audio.currentTime);
                                if(!isNaN(audio.duration) && audio.duration > 0) {
                                    var progress = (this.value / audio.duration) * 100;
                                    this.style.backgroundImage = `linear-gradient(to right, #fff 0%, #fff ${progress}%, transparent ${progress}%)`;
                                }
                            });
                            seek.addEventListener('touchmove', function() { 
                                audio.currentTime = this.value; 
                                timeCurr.innerText = fmtTime(audio.currentTime);
                                if(!isNaN(audio.duration) && audio.duration > 0) {
                                    var progress = (this.value / audio.duration) * 100;
                                    this.style.backgroundImage = `linear-gradient(to right, #fff 0%, #fff ${progress}%, transparent ${progress}%)`;
                                }
                            });
                            seek.addEventListener('change', function() { 
                                isSeeking = false;
                                audio.currentTime = this.value; 
                                this.style.opacity = '1';
                                if(!isNaN(audio.duration) && audio.duration > 0) {
                                    var progress = (this.value / audio.duration) * 100;
                                    this.style.backgroundImage = `linear-gradient(to right, #fff 0%, #fff ${progress}%, transparent ${progress}%)`;
                                }
                            });

                            audio.addEventListener('ended', function() { 
                                loadTrack(getNextIndex(), true);
                            });

                            updateModeUI();
                            loadTrack(currentIndex, false);

                            document.addEventListener('click', function(e) {
                                var item = e.target.closest('.ze-playlist-item');
                                if(item && item.dataset.index !== undefined) {
                                    var idx = parseInt(item.dataset.index);
                                    loadTrack(idx, true);
                                }
                            });
                        })();
                        </script>
                    </div>
                <?php endif;?>
            <?php endif;?>
        </div>
    <?php endif;?> 


    <div class="video-info mt-3">
        <div class="video-info-title">
            <?php if ($_GET['action'] == 'get' && 'GET' == $_SERVER['REQUEST_METHOD']): ?>
            <a href="<?php $this->permalink(); ?>"><?php endif;?>
            <h1><font style="vertical-align: inherit;"><?php $this->title() ?></font></h1>
            <?php if ($_GET['action'] == 'get' && 'GET' == $_SERVER['REQUEST_METHOD']): ?></a><?php endif;?>
        </div>

        <?php if ($_GET['action'] == 'get' && 'GET' == $_SERVER['REQUEST_METHOD']): ?>
        <div class="uk-flex uk-flex-between">
            <div class="video-info-details">
                <!-- <span>播放量: <?php get_post_view($this); ?></span> -->
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

        <style>
            /* 详情按钮样式 */
            .ze-btn-details {
                background: rgba(0, 119, 255, 0.08);
                color: #0077ff;
                border: 1px solid rgba(0, 119, 255, 0.2);
                padding: 6px 16px;
                border-radius: 20px;
                cursor: pointer;
                font-size: 13px;
                font-weight: 500;
                transition: all 0.3s ease;
                display: inline-flex;
                align-items: center;
                gap: 5px;
                margin-top: 10px;
            }
            .ze-btn-details:hover {
                background: #0077ff;
                color: #fff;
                transform: translateY(-2px);
                box-shadow: 0 4px 10px rgba(0, 119, 255, 0.2);
            }

            /* 弹窗样式 */
            .ze-modal-wrap { display: none; position: fixed; z-index: 99999; inset: 0; }
            .ze-modal-wrap.show { display: block; }
            .ze-modal-bg { position: absolute; inset: 0; background: rgba(0,0,0,0.6); backdrop-filter: blur(5px); }
            .ze-modal-box {
                position: relative; width: 90%; max-width: 600px; margin: 10vh auto;
                background: #fff; border-radius: 16px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
                display: flex; flex-direction: column; max-height: 80vh;
                animation: modalSlideIn 0.3s ease-out;
            }
            @keyframes modalSlideIn { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
            .ze-modal-header { padding: 15px 20px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center; }
            .ze-modal-header h3 { margin: 0; font-size: 18px; font-weight: bold; color: #333; }
            .ze-modal-close { background: none; border: none; font-size: 20px; cursor: pointer; color: #999; transition: color 0.2s; }
            .ze-modal-close:hover { color: #ff4d4f; }
            .ze-modal-content { padding: 20px; overflow-y: auto; line-height: 1.7; color: #555; }

            /* 播放列表美化样式 */
            .ze-playlist-item {
                display: flex;
                align-items: center;
                padding: 12px 16px;
                border-radius: 8px;
                margin-bottom: 6px;
                text-decoration: none !important;
                transition: all 0.2s ease;
                background: transparent;
                border: 1px solid transparent;
            }
            .ze-playlist-item:hover {
                background: rgba(0,0,0,0.02);
                border-color: rgba(0,0,0,0.05);
                transform: translateX(4px);
            }
            .ze-playlist-item.active {
                background: rgba(0, 119, 255, 0.06);
                border-color: rgba(0, 119, 255, 0.15);
            }
            .ze-playlist-item .item-icon {
                width: 32px;
                color: #aaa;
                font-size: 14px;
                font-family: monospace;
                display: flex;
                align-items: center;
            }
            .ze-playlist-item.active .item-icon { color: #0077ff; font-size: 18px; }
            .ze-playlist-item .item-title {
                flex: 1;
                color: #444;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
                font-size: 14px;
            }
            .ze-playlist-item.active .item-title { color: #0077ff; font-weight: 600; }
            .ze-playlist-item .item-play-btn {
                opacity: 0;
                transform: scale(0.8);
                transition: all 0.2s ease;
                color: #0077ff;
                background: #fff;
                border-radius: 50%;
                width: 28px; height: 28px;
                display: flex; align-items: center; justify-content: center;
                box-shadow: 0 2px 6px rgba(0,0,0,0.1);
            }
            .ze-playlist-item:hover .item-play-btn, .ze-playlist-item.active .item-play-btn {
                opacity: 1;
                transform: scale(1);
            }

            /* 夜间模式自适应 (仅在包含 night-mode 类时生效) */
            .night-mode .ze-modal-box { background: #222; border: 1px solid #333; }
            .night-mode .ze-modal-header { border-bottom-color: #333; }
            .night-mode .ze-modal-header h3 { color: #eee; }
            .night-mode .ze-modal-content { color: #bbb; }
            .night-mode .ze-playlist-item .item-title { color: #ccc; }
            .night-mode .ze-playlist-item:hover { background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.08); }
            .night-mode .ze-playlist-item.active { background: rgba(0, 119, 255, 0.1); border-color: rgba(0, 119, 255, 0.2); }
            .night-mode .ze-playlist-item .item-play-btn { background: #333; box-shadow: 0 2px 6px rgba(0,0,0,0.5); }
        </style>

        <div>
            <?php if ($_GET['action'] == 'get' && 'GET' == $_SERVER['REQUEST_METHOD']): ?>
            <ul uk-tab="" class="uk-tab mt-0" uk-switcher="animation: uk-animation-slide-left-medium, uk-animation-slide-right-medium">
                <li class="uk-active"><a href="#" aria-expanded="true">专辑简介</a></li>
            </ul>
            <?php endif;?>

            <div style="display: flex; gap: 20px; margin-bottom: 20px; align-items: flex-start;">
                
                <div style="width: 100px; max-width: 30vw; flex-shrink: 0;">
                    <div class="media media-10x14">
                        <div class="media-content scrollLoading ojbk" style="background-image: url('<?php showThumbnail($this); ?>');"></div>
                    </div>
                </div>

                <div style="flex: 1;">
                    <?php $sc=0; if(collect($this->cid,Typecho_Widget::widget('Widget_User')->uid,1)=="ko"){$sc=1;} ?>
                    <a href="javascript:;" data-action="collect" data-id="<?php $this->cid(); ?>" class="uk-float-right btn-collect" style="margin-top: -5px;"><i class="uil-star"></i></a>

                    <p class="mt-0" style="line-height: 1.8;">
                        发行年份：<?php if($this->fields->niandai){ $this->fields->niandai();} ?><br>
                        曲风类型：<?php $this->tags(' / ', true, 'none'); ?><br>
                        专辑状态：<?php if($this->fields->zhuangtai>0){echo '连载中';}else{ if($this->fields->zhuangtai==-1){echo '待定';}else{echo '完整版';} } ?>
                    </p>

                    <button onclick="toggleDetailsModal(true)" class="ze-btn-details">
                        详情 <i class="uil-arrow-right"></i>
                    </button>
                </div>
            </div>

            <div id="ze-intro-modal" class="ze-modal-wrap">
                <div class="ze-modal-bg" onclick="toggleDetailsModal(false)"></div>
                <div class="ze-modal-box">
                    <div class="ze-modal-header">
                        <h3>专辑详情</h3>
                        <button class="ze-modal-close" onclick="toggleDetailsModal(false)"><i class="uil-multiply"></i></button>
                    </div>
                    <div class="ze-modal-content typecho-text">
                        <?php if($this->hidden||$this->titleshow): ?>
                        <form action="<?php echo Typecho_Widget::widget('Widget_Security')->getTokenUrl($this->permalink); ?>" method="post" class="protected">
                            <div><span class="uk-text-middle uk-text-danger">请输入密码访问</span></div>
                            <div class="uk-margin-small">
                                <div uk-form-custom="target: true" class="uk-form-custom uk-first-column">
                                    <input class="uk-input" name="protectPassword" type="password" placeholder="请输入密码">
                                </div>
                                <input type="hidden" name="protectCID" value="<?php $this->cid(); ?>" />
                                <button class="uk-button uk-button-default" type="submit">提交</button>
                            </div>
                        </form>
                        <?php else: ?>
                            <?php $this->content(); ?>
                        <?php endif;?>
                    </div>
                </div>
            </div>
            
            <div class="clear"></div>
        </div>

        <ul uk-tab="" class="uk-tab mt-0" uk-switcher="animation: uk-animation-slide-left-medium, uk-animation-slide-right-medium">
            <li class="uk-active"><a href="#" aria-expanded="true" class="uk-text-small">专辑曲目</a></li>
        </ul>

        <ul class="uk-switcher uk-margin uk-padding-small pt-0 pl-0">
            <li class="uk-active">
                <?php if(!empty($albumData)): ?>
                    <div style="max-height: 500px; overflow-y: auto; padding-right:5px;">
                    <?php foreach($albumData as $groupName => $songs): ?>
                        
                        <div class="uk-text-bold uk-margin-small-bottom uk-margin-small-top" style="color:#666; font-size:14px; border-left:3px solid #0077ff; padding-left:10px;">
                            <?php echo $groupName; ?>
                        </div>

                        <?php foreach($songs as $song): ?>
                            <?php 
                                $pIndex = $song['global_index']; 
                                $isActive = ($currentP == $pIndex);
                                $isActiveClass = $isActive ? 'active' : '';
                                
                                // 图标处理：当前播放显示动效图标，未播放显示数字序号
                                if($isActive) {
                                    $icon = "<i class='uil-music'></i>";
                                } else {
                                    $icon = sprintf("%02d", $pIndex); // 数字补零如 01, 02
                                }
                                
                                $link = $this->permalink . '?action=get&p=' . $pIndex;
                                
                                // 输出美化后的列表元素
                                echo "<div class='ze-playlist-item {$isActiveClass}' data-index='" . ($pIndex - 1) . "'>
                                        <div class='item-icon'>{$icon}</div>
                                        <div class='item-title'>{$song['title']}</div>
                                        <div class='item-play-btn'><i class='uil-play'></i></div>
                                      </div>";
                            ?>
                        <?php endforeach; ?>
                        
                        <div class="uk-margin"></div>

                    <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p>暂无曲目</p>
                <?php endif; ?>
            </li>
        </ul>
    </div>

    <script>
    function toggleDetailsModal(show) {
        const modal = document.getElementById('ze-intro-modal');
        if(show) {
            modal.classList.add('show');
            document.body.style.overflow = 'hidden'; // 防止背景滚动
        } else {
            modal.classList.remove('show');
            document.body.style.overflow = '';
        }
    }
    </script>

    <?php if(!$this->options->addie||!in_array($this->cid,explode(",", $this->options->addie))): ?>
        <?php if(!$this->request->isAjax()): ?><?php if($this->options->ad): ?>
            <?php $this->options->ad(); ?>
        <?php endif; ?><?php endif; ?>
    <?php endif; ?>

    <hr>
    <?php $this->need('comments.php'); ?>

    </div>
    <?php $this->need('post-sidebar.php'); ?>
</div>