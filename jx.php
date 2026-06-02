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

// 核心改进：标准的申请常亮锁逻辑
async function requestWakeLock() {
    if (!isMobile) return;
    if (!('wakeLock' in navigator)) return;
    if (wakeLock !== null) return; 
    
    try {
        wakeLock = await navigator.wakeLock.request('screen');
        console.log('屏幕常亮锁申请成功');
        wakeLock.addEventListener('release', function() {
            if (!isManualRelease) {
                wakeLock = null; // 系统异常释放（如手机锁屏后再解锁），清空指针，等下次手势或特定事件自动恢复
            } else {
                wakeLock = null;
            }
        });
    } catch (e) {
        console.log('唤醒锁请求暂未获批（等待用户交互）', e.message);
    }
}

// 释放锁
async function releaseWakeLock() {
    if (wakeLock) {
        try {
            isManualRelease = true;
            await wakeLock.release();
            wakeLock = null;
            console.log('屏幕常亮锁已手动释放');
        } catch (e) {
            console.log('唤醒锁释放失败', e);
        } finally {
            isManualRelease = false;
        }
    }
}

// 监听切后台行为
document.addEventListener('visibilitychange', function() {
    if (document.visibilityState === 'visible') {
        if (dp && dp.video && !dp.video.ended && !dp.video.paused) {
            requestWakeLock();
        }
    } else {
        releaseWakeLock(); // 切后台时释放，节约手机电量
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
    
    // 【💡 关键改动】绝不销毁播放器实例，通过无缝切换视频源保持浏览器的 Context 上下文不丢失
    if (dp) {
        videoMetadataReady = false;
        
        var videoOptions = {
            url: vurl,
            type: 'auto',
            pic: '',
        };
        var subtitleOptions = zimu ? {
            url: zimu,
            type: 'webvtt',
            fontSize: '25px',
            bottom: '10%',
            color: '#b7daff',
        } : null;
        
        dp.switchVideo(videoOptions, subtitleOptions);
        
        if (shouldAutoPlay) {
            dp.play();
        }
        
        dp.seek(webdata.get('pay'+vurl));
        
        if (progressInterval) clearInterval(progressInterval);
        progressInterval = setInterval(function(){
            if(dp && dp.video) webdata.set('pay'+vurl, dp.video.currentTime);
        }, 1000);
        
        notifyParent();
        return;
    }
    
    // 首次初始化的逻辑
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
    
    // 覆盖所有播放状态变化事件来尝试申请锁
    var keepAwakeEvents = ['play', 'playing', 'waiting', 'seeking', 'seeked', 'loadstart'];
    keepAwakeEvents.forEach(function(evt) {
        dp.on(evt, function() {
            requestWakeLock();
        });
    });
    
    dp.on('play', function() {
        shouldAutoPlay = true;
        if (videoMetadataReady) {
            if (typeof handleAutoRotate === 'function') handleAutoRotate(dp.video);
        } else {
            dp.video.addEventListener('loadedmetadata', function() {
                videoMetadataReady = true;
                if (typeof handleAutoRotate === 'function') handleAutoRotate(dp.video);
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
    
    dp.seek(webdata.get('pay'+vurl));
    
    if (progressInterval) clearInterval(progressInterval);
    progressInterval = setInterval(function(){
        if(dp && dp.video) webdata.set('pay'+vurl, dp.video.currentTime);
        // 【💡 关键改动】移除了原来在此处定时器无条件的 requestWakeLock()，防止无交互下的异常报错死锁
    }, 1000);
    
    dp.on('ended', function() {
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
                if (typeof handleAutoRotate === 'function') handleAutoRotate(dp.video);
            } else {
                dp.video.addEventListener('loadedmetadata', function() {
                    videoMetadataReady = true;
                    if (typeof handleAutoRotate === 'function') handleAutoRotate(dp.video);
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
        
        dp.seek(webdata.get('pay'+vurl));
        
        if (progressInterval) clearInterval(progressInterval);
        progressInterval = setInterval(function(){
            if(dp && dp.video) webdata.set('pay'+vurl, dp.video.currentTime);
        }, 1000);

        dp.on('ended', function() {
            releaseWakeLock();
        });
    } else {
        initPlayer();
    }

    // 【💡 关键改动：终极补全方案】监听用户的全局手势触摸
    // 只要视频正在播放，用户一旦点击屏幕查看进度条、调节音量，就会立刻无感知补锁，实现绝对稳定的锁死状态
    ['touchstart', 'click'].forEach(function(evt) {
        document.addEventListener(evt, function() {
            if (dp && dp.video && !dp.video.paused && !dp.video.ended) {
                requestWakeLock();
            }
        }, { passive: true });
    });
});

setTimeout(function() {
    notifyParent();
}, 500);
</script>
</body>
</html>