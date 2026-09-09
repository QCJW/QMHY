<!doctype html>
<html lang="zh-CN">
<head>
<title>默认解析</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <meta charset="UTF-8">
</head>
<style>
    body,html{width:100%;height:100%;background:#000;padding:0;margin:0;overflow-x:hidden;overflow-y:hidden}
    *{margin:0;border:0;padding:0;text-decoration:none}
    #stats{position:fixed;top:5px;left:10px;font-size:12px;color:#fdfdfd;z-index:2147483647;text-shadow:1px 1px 1px #000, 1px 1px 1px #000}
    #artplayer{position:inherit;
width: 100%;
    height: 100%;
    margin: 0 auto;
}
</style>


<body>
<?php
if($_GET['url']){
$spurl=@$_GET['url'];$zimu=@$_GET['zimu'];
?>
<div id="artplayer"></div>

<script src="./lib/artplayer/hls.min.js"></script>
<script src="./lib/artplayer/artplayer.min.js"></script>

<script>
    var webdata = {
        set:function(key,val){
            window.sessionStorage.setItem(key,val);
        },
        get:function(key){
            return window.sessionStorage.getItem(key);
        },
        del:function(key){
            window.sessionStorage.removeItem(key);
        },
        clear:function(key){
            window.sessionStorage.clear();
        }
    };
var vurl='<?php echo $spurl; ?>';
var vtype = 'auto';
if(vurl.indexOf('.m3u8') !== -1) vtype = 'customHls';
else if(vurl.indexOf('.flv') !== -1) vtype = 'flv';

const art = new Artplayer({
    container: '#artplayer',
    url: vurl,
    type: vtype,
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
    autoPlayback: true,
    fullscreen: true,
<?php if(!empty($zimu)): ?>
    subtitle: {
        url: '<?php echo $zimu; ?>',
        type: 'webvtt',
        style: {
            color: '#b7daff',
            'font-size': '25px',
            bottom: '10%',
        },
    },
<?php endif; ?>
    contextmenu: [
        {
            name: 'pip',
            html: '画中画模式',
            click: function(art) {
                art.video.requestPictureInPicture();
            },
        },
    ],
    customType: {
        customHls: function(video, url) {
            if (Hls.isSupported()) {
                var hls = new Hls();
                hls.loadSource(url);
                hls.attachMedia(video);
                art.on('destroy', function() { hls.destroy(); });
            } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
                video.src = url;
            }
        },
    },
});

art.on('ready', function() {
    var saved = webdata.get('pay'+vurl);
    if(saved && parseFloat(saved) > 5) {
        art.seek(parseFloat(saved));
    }
});

setInterval(function(){
    if(art && art.video && !art.video.paused){
        webdata.set('pay'+vurl, art.video.currentTime);
    }
}, 1000);
</script>
<?php


    
}else{
    echo '参数未添加';
    
}



?>
</body>

</html>
