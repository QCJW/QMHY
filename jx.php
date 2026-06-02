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

var isMobile = /Mobi|Android|iPhone|iPad|iPod/i.test(navigator.userAgent) ||
               (navigator.maxTouchPoints > 0 && window.matchMedia('(max-width: 768px)').matches);

async function requestWakeLock() {
    if (!isMobile) return;
    try {
        if ('wakeLock' in navigator && !wakeLock) {
            wakeLock = await navigator.wakeLock.request('screen');
            wakeLock.addEventListener('release', function() {
                wakeLock = null;
            });
        }
    } catch (e) {}
}

async function releaseWakeLock() {
    if (wakeLock) {
        try {
            await wakeLock.release();
            wakeLock = null;
        } catch (e) {}
    }
}

document.addEventListener('visibilitychange', function() {
    // 只要页面可见且视频没有结束，即使暂停也保持常亮
    if (document.visibilityState === 'visible' && dp && !dp.video.ended) {
        requestWakeLock();
    } else {
        releaseWakeLock();
    }
});

function initPlayer() {
    if (episodes.length === 0) {
        return;
    }
    
    var ep = episodes[currentIndex];
    var vurl = ep.url || '';
    var zimu = ep.zimu || '';
    vurl = vurl.replace('https://v.qq.com/x/cover/', 'https://v.qq.com/x/page/');
    
    if (dp) {
        try {
            dp.destroy();
        } catch (e) {
            console.log('销毁旧播放器失败', e);
        }
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
    
    dp.on('play', function() {
        shouldAutoPlay = true;
        requestWakeLock();
    });
    
    dp.on('pause', function() {
        // 暂停时不做任何处理，保持常亮状态
    });

    // 进入全屏时的旋转逻辑
    dp.on('fullscreen', function() {
        if (!isMobile) return;
        var videoEl = dp.video;
        if (!videoEl) return;
        var vw = videoEl.videoWidth;
        var vh = videoEl.videoHeight;
        // 只有横屏视频（宽大于高）才触发手机旋转锁定
        if (vw && vh && vw > vh) {
            if (screen.orientation && screen.orientation.lock) {
                screen.orientation.lock('landscape').catch(function() {});
            }
        }
    });

    // 退出全屏时解除方向锁定
    dp.on('fullscreen_cancel', function() {
        if (!isMobile) return;
        if (screen.orientation && screen.orientation.unlock) {
            screen.orientation.unlock();
        }
    });
    
    dp.seek(webdata.get('pay'+vurl));
    setInterval(function(){
        webdata.set('pay'+vurl, dp.video.currentTime);
    }, 1000);
    
    dp.on('ended', function() {
        releaseWakeLock();
        playNext();
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
        
        dp.on('play', function() {
            shouldAutoPlay = true;
            requestWakeLock();
        });
        
        dp.on('pause', function() {
            // 暂停时保持常亮
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
        setInterval(function(){
            webdata.set('pay'+vurl, dp.video.currentTime);
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
