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
    #dplayer{position:inherit;
width: 100%;
    height: 100%;
    margin: 0 auto;
}
.dplayer-controller .dplayer-icons .dplayer-full:hover .dplayer-full-in-icon{
display:none !important
}
.dplayer-menu>div:nth-last-child(-n+2) {
    display: none;
}
</style>


<body>
<?php
if($_GET['url']){
$spurl=@$_GET['url'];$zimu=@$_GET['zimu'];
?>
<div id="dplayer"></div>

<?php if(strpos($_GET['url'], '.flv')) {echo '<script src="./lib/dplayer/flv.min.js"></script>';}
if(strpos($_GET['url'], 'magnet:')) {echo '<script src="./lib/dplayer/webtorrent.min.js"></script>';} ?>
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
            window.sessionStorage.removeItem(key);
        },
        clear:function(key){
            window.sessionStorage.clear();
        }
    };
var vurl='<?php echo $spurl; ?>';
const dp = new DPlayer({
                container: document.getElementById('dplayer'),
                screenshot: false,lang: 'zh-cn',hotkey: true,preload: 'auto',
                //autoplay: true,
                video: {
                 url: vurl,type: 'auto',
                 pic: 'https://ae02.alicdn.com/kf/Hae3544136d6f4bf9aafc7a5993e2ece6C.jpg',
                },     
<?php if(!empty($zimu)): ?>
 subtitle: {
        url: '<?php echo $zimu; ?>',
        type: 'webvtt',
        fontSize: '25px',
        bottom: '10%',
        color: '#b7daff',
    },
<?php endif; ?>  
   contextmenu: [
        {
            text: '画中画模式',
            click: (player) => {
player.video.requestPictureInPicture();
            },
        },
    ],      
       });
   dp.seek(webdata.get('pay'+vurl));
    setInterval(function(){
        webdata.set('pay'+vurl,dp.video.currentTime);
    },1000);
</script>
<?php


    
}else{
    echo '参数未添加';
    
}



?>
</body>

</html>