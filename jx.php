<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
<meta content="telephone=no" name="format-detection">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black">
<link rel="stylesheet" href="./lib/dplayer/DPlayer.min.css">
<style>
*{margin:0;padding:0}
html,body{height:100%;width:100%;background-color:#000}
#dplayer{height:100%;width:100%}
</style>
</head>
<body>
<div id="dplayer"></div>
<?php
$url=trim($_GET['url']);
$zimu=trim($_GET['zimu']);
$spurl=str_replace('https://v.qq.com/x/cover/','https://v.qq.com/x/page/',$url);

$episodes = isset($_GET['episodes']) ? json_decode(urldecode($_GET['episodes']), true) : array();
$currentIndex = isset($_GET['currentIndex']) ? intval($_GET['currentIndex']) : 0;
?>
<?php if(strpos($spurl, '.flv')) {echo '<script src="./lib/dplayer/flv.min.js"></script>';}
if(strpos($spurl, 'magnet:')) {echo '<script src="./lib/dplayer/webtorrent.min.js"></script>';} ?>
<script src="./lib/dplayer/hls.min.js"></script>
<script src="./lib/dplayer/DPlayer.min.js"></script>
<script>
    var webdata = {
        set:function(key,val){
            window.sessionStorage.setItem(key,val);
        },
        get:function(key){
            return window.sessionStorage.getItem(key);
        },
        del:function(key){
            return window.sessionStorage.removeItem(key);
        },
        clear:function(key){
            window.sessionStorage.clear();
        }
    };
</script>
<script>
var episodes = <?php echo json_encode($episodes); ?>;
var currentIndex = <?php echo $currentIndex; ?>;
var dp = null;
var shouldAutoPlay = false;
var wakeLock = null;
var isManualRelease = false; 
var progressInterval = null; 
var videoMetadataReady = false;

var isMobile = /Mobi|Android|iPhone|iPad|iPod/i.test(navigator.userAgent) ||
               (navigator.maxTouchPoints > 0 && window.matchMedia('(max-width: 768px)').matches);

// 自动旋转处理函数 (修复火狐等浏览器的兼容问题)
function handleAutoRotate(videoEl) {
    if (!isMobile) return;
    if (!videoEl) return;
    var vw = videoEl.videoWidth;
    var vh = videoEl.videoHeight;
    
    // 判断是否是横屏视频
    if (vw && vh && vw > vh) {
        // 兼容获取当前全屏状态（火狐强制要求必须处于原生全屏下才能锁定方向）
        var isFullScreen = document.fullscreenElement || document.mozFullScreenElement || document.webkitFullscreenElement || document.msFullscreenElement;
        
        if (isFullScreen && screen.orientation && screen.orientation.lock) {
            screen.orientation.lock('landscape').catch(function(err) {
                console.log('自动锁定横屏失败:', err);
            });
        }
    }
}

async function requestWakeLock() {
    if (!isMobile) return;
    if (!('wakeLock' in navigator)) return;
    if (wakeLock !== null) return; // 如果当前已经持有锁，不重复申请
    
    try {
        wakeLock = await navigator.wakeLock.request('screen');
        wakeLock.addEventListener('release', function() {
            if (!isManualRelease) {
                wakeLock = null;
                // 若被系统因异常意外释放，且视频尚未彻底结束，1秒后自动尝试重新捕获
                if (dp && !dp.video.ended && document.visibilityState === 'visible') {
                    setTimeout(requestWakeLock, 1000);
                }
            } else {
                wakeLock = null;
            }
        });
    } catch (e) {
        console.log('唤醒锁请求失败', e);
    }
}

async function releaseWakeLock() {
    if (wakeLock) {
        try {
            isManualRelease = true;
            await wakeLock.release();
            wakeLock = null;
        } catch (e) {
            console.log('唤醒锁释放失败', e);
        } finally {
            isManualRelease = false;
        }
    }
}

document.addEventListener('visibilitychange', function() {
    // 只要切回前台，且视频没放完，立刻无条件恢复常亮
    if (document.visibilityState === 'visible') {
        if (dp && !dp.video.ended) {
            requestWakeLock();
        }
    } else {
        // 切到后台释放锁，节约手机电量
        releaseWakeLock();
    }
});

function bindVideoEvents(videoEl) {
    if (!videoEl) return;
    videoEl.addEventListener('loadedmetadata', function() {
        videoMetadataReady = true;
    });
}

function initPlayer() {
    if (episodes.length === 0) {
        return;
    }
    
    var ep = episodes[currentIndex];
    var vurl = ep.url || '';
    var zimu = ep.zimu || '';
    vurl = vurl.replace('https://v.qq.com/x/cover/', 'https://v.qq.com/x/page/');
    
    // 【核心修复】：如果播放器已存在，不要销毁 DOM，使用 switchVideo 无缝切换。
    // 这样火狐浏览器就不会因为节点消失而强制退出全屏，从而保留锁定横屏的权限。
    if (dp) {
        videoMetadataReady = false; // 关键：重置元数据状态，确保获取到新一集视频的真实宽高
        
        dp.switchVideo({
            url: vurl,
            type: 'auto',
            pic: ''
        });
        
        // 兼容切换字幕
        if (zimu) {
            if (!dp.options.subtitle) dp.options.subtitle = {};
            dp.options.subtitle.url = zimu;
            var track = dp.video.querySelector('track');
            if (track) track.src = zimu;
        }
        
        dp.seek(webdata.get('pay'+vurl) || 0);
        dp.play();
        
        notifyParent();
        return; // 提前结束，不再往下重新 new DPlayer
    }
    
    var container = document.getElementById('dplayer');
    if (container) {
        container.innerHTML = '';
    }
    
    var playerOptions = {
        container: container,
        screenshot: false,
        lang: 'zh-cn',
        hotkey: true,
        preload: 'auto',
        autoplay: shouldAutoPlay, 
        controls: true,
        video: {
            url: vurl,
            type: 'auto',
            pic: '',
        },
        contextmenu: [
            {
                text: '画中画模式',
                click: function(player) {
                    player.video.requestPictureInPicture();
                },
            },
        ],
    };
    
    if (zimu) {
        playerOptions.subtitle = {
            url: zimu,
            type: 'webvtt',
            fontSize: '25px',
            bottom: '10%',
            color: '#b7daff',
        };
    }
    
    dp = new DPlayer(playerOptions);
    bindVideoEvents(dp.video);
    
    // 全方位覆盖所有视频过渡状态，只要有动静就疯狂申请锁
    var keepAwakeEvents = ['play', 'playing', 'waiting', 'seeking', 'seeked', 'loadstart'];
    keepAwakeEvents.forEach(function(evt) {
        dp.on(evt, function() {
            requestWakeLock();
        });
    });
    
    dp.on('play', function() {
        shouldAutoPlay = true;
        if (videoMetadataReady) {
            handleAutoRotate(dp.video);
        } else {
            dp.video.addEventListener('loadedmetadata', function() {
                videoMetadataReady = true;
                handleAutoRotate(dp.video);
            }, { once: true });
        }
    });

    // 点击全屏时的旋转逻辑
    dp.on('fullscreen', function() {
        if (!isMobile) return;
        var videoEl = dp.video;
        if (!videoEl) return;
        var vw = videoEl.videoWidth;
        var vh = videoEl.videoHeight;
        if (vw && vh && vw > vh) {
            if (screen.orientation && screen.orientation.lock) {
                screen.orientation.lock('landscape').catch(function() {});
            }
        }
    });

    // 退出全屏解锁旋转
    dp.on('fullscreen_cancel', function() {
        if (!isMobile) return;
        if (screen.orientation && screen.orientation.unlock) {
            screen.orientation.unlock();
        }
    });
    
    dp.seek(webdata.get('pay'+vurl) || 0);
    
    // 清理旧定时器防止叠加泄露，每秒写盘同时做一次防掉锁强力扫描
    if (progressInterval) clearInterval(progressInterval);
    progressInterval = setInterval(function(){
        webdata.set('pay'+vurl, dp.video.currentTime);
        if (dp && !dp.video.ended && document.visibilityState === 'visible') {
            requestWakeLock();
        }
    }, 1000);
    
    dp.on('ended', function() {
        // 判断后面有没有新集数，如果有则不释放锁，无缝带入下一集加载
        if (episodes.length > 0 && currentIndex < episodes.length - 1) {
            playNext();
        } else {
            releaseWakeLock();
        }
    });
    
    notifyParent();
}

function notifyParent() {
    try {
        if (window.parent && window.parent !== window && window.parent.postMessage) {
            window.parent.postMessage({
                action: 'episodeChange',
                index: currentIndex
            }, '*');
        }
    } catch (e) {
        console.log('无法通知父页面');
    }
}

function playNext() {
    if (episodes.length === 0) return;
    currentIndex++;
    if (currentIndex >= episodes.length) {
        currentIndex = episodes.length - 1;
        releaseWakeLock();
        return;
    }
    initPlayer();
}

function playPrev() {
    if (episodes.length === 0) return;
    currentIndex--;
    if (currentIndex < 0) {
        currentIndex = episodes.length - 1;
    }
    initPlayer();
}

function playEpisode(index) {
    if (index >= 0 && index < episodes.length) {
        currentIndex = index;
        initPlayer();
    }
}

window.addEventListener('message', function(event) {
    try {
        if (event.data && event.data.action === 'playEpisode') {
            playEpisode(event.data.index);
        }
    } catch (e) {
        console.log('消息处理错误', e);
    }
});

document.addEventListener('DOMContentLoaded', function() {
    var container = document.getElementById('dplayer');
    if (container) {
        container.innerHTML = '';
    }
    
    if (episodes.length === 0) {
        var vurl = '<?php echo $spurl; ?>';
        var zimu = '<?php echo $zimu; ?>';
        
        var playerOptions = {
            container: container,
            screenshot: false,
            lang: 'zh-cn',
            hotkey: true,
            preload: 'auto',
            autoplay: false,
            controls: true,
            video: {
                url: vurl,
                type: 'auto',
                pic: '',
            },
            contextmenu: [
                {
                    text: '画中画模式',
                    click: function(player) {
                        player.video.requestPictureInPicture();
                    },
                },
            ],
        };
        
        if (zimu) {
            playerOptions.subtitle = {
                url: zimu,
                type: 'webvtt',
                fontSize: '25px',
                bottom: '10%',
                color: '#b7daff',
            };
        }
        
        dp = new DPlayer(playerOptions);
        bindVideoEvents(dp.video);
        
        var keepAwakeEvents = ['play', 'playing', 'waiting', 'seeking', 'seeked', 'loadstart'];
        keepAwakeEvents.forEach(function(evt) {
            dp.on(evt, function() {
                requestWakeLock();
            });
        });
        
        dp.on('play', function() {
            shouldAutoPlay = true;
            if (videoMetadataReady) {
                handleAutoRotate(dp.video);
            } else {
                dp.video.addEventListener('loadedmetadata', function() {
                    videoMetadataReady = true;
                    handleAutoRotate(dp.video);
                }, { once: true });
            }
        });

        dp.on('fullscreen', function() {
            if (!isMobile) return;
            var videoEl = dp.video;
            if (!videoEl) return;
            var vw = videoEl.videoWidth;
            var vh = videoEl.videoHeight;
            if (vw && vh && vw > vh) {
                if (screen.orientation && screen.orientation.lock) {
                    screen.orientation.lock('landscape').catch(function() {});
                }
            }
        });

        dp.on('fullscreen_cancel', function() {
            if (!isMobile) return;
            if (screen.orientation && screen.orientation.unlock) {
                screen.orientation.unlock();
            }
        });
        
        dp.seek(webdata.get('pay'+vurl) || 0);
        
        if (progressInterval) clearInterval(progressInterval);
        progressInterval = setInterval(function(){
            webdata.set('pay'+vurl, dp.video.currentTime);
            if (dp && !dp.video.ended && document.visibilityState === 'visible') {
                requestWakeLock();
            }
        }, 1000);

        dp.on('ended', function() {
            releaseWakeLock();
        });
    } else {
        initPlayer();
    }
});

setTimeout(function() {
    notifyParent();
}, 500);
</script>
</body>
</html>