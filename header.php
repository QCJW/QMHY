<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<!doctype html>
<html lang="zh-CN" <?php if((!empty(Helper::options()->tools) && in_array('dark',Helper::options()->tools) && @$_COOKIE['night'] != '0') || @$_COOKIE['night'] == '1'){echo 'class="night-mode"';} ?>>
<head>
<title><?php if($this->request->gaojijiansuo){echo "高级检索 - ";
}else{if($this->_currentPage>1) echo '第 '.$this->_currentPage.' 页 - '; ?><?php $this->archiveTitle(array(
            'category'  =>  _t('分类 %s 下的文章'),
            'search'    =>  _t('包含关键字 %s 的文章'),
            'tag'       =>  _t('标签 %s 下的文章'),
            'author'    =>  _t('%s 发布的文章')
        ), '', ' - '); } ?>
<?php 
if(isset($this->fields->mp4) && strpos($this->fields->mp4,'$') !== false &&isset($_GET['action'])&& $_GET['action'] == 'get' && 'GET' == $_SERVER['REQUEST_METHOD']){
$txt=$this->fields->mp4;
$string_arr = explode("\r\n", $txt);
$j=$_GET['p']-1;
$sptitle=explode("$",$string_arr[$j])[0];echo $sptitle.' - ';
} ?><?php $this->options->title(); ?></title>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=1,user-scalable=no">
<meta itemprop="name" content="<?php if ($this->is('index')) : ?><?php $this->options->title(); ?><?php else: ?><?php $this->archiveTitle(array('category'  =>  _t('分类 %s 下的文章'),'search'    =>  _t('包含关键字 %s 的文章'),'tag'       =>  _t('标签 %s 下的文章'),'author'    =>  _t('%s 发布的文章')), '', ''); ?><?php endif; ?>"/>
<meta itemprop="image" content="<?php if ($this->is('post')) : ?><?php showThumbnail($this); ?><?php else: ?><?php if ($this->options->logoUrl): ?><?php $this->options->logoUrl() ?><?php else: ?><?php echo theurl; ?>assets/images/logo.png<?php endif; ?><?php endif; ?>" />
<link rel="dns-prefetch" href="//unicons.iconscout.com" /><link rel="dns-prefetch" href="//cdn.staticfile.org" />
<meta itemprop="description" name="description" content="<?php $d=$this->fields->description;if(empty($d) || !$this->is('single')){if($this->getDescription()){echo $this->getDescription();}}else{ echo $d;};?>" />
    <meta name="keywords" content="<?php $k=$this->fields->keyword;if(empty($k) || !$this->is('single')){echo $this->keywords();}else{ echo $k;};?>" />   
    <link rel="icon" href="<?php $this->options->themeUrl('assets/images/favicon.png'); ?>">
    <!-- CSS ================================================== -->
    <link rel="stylesheet" href="<?php $this->options->themeUrl('assets/css/style.css'); ?>?20210322">
    <link href="<?php $this->options->themeUrl('assets/css/uikit.min.css'); ?>" type="text/css" rel="stylesheet" />
    <link rel="stylesheet" href="<?php $this->options->themeUrl('assets/css/night-mode.css?202103'); ?>">
    <link rel="stylesheet" href="<?php $this->options->themeUrl('style.css'); ?>?202103">
    <!-- icons================================================== -->
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v2.1.9/css/unicons.css">

    <!-- 通过自有函数输出HTML头部信息================================================== -->
    <?php $this->header('generator=&template=&keywords=&description=&commentReply='); ?>
<script type='text/javascript'>
/* <![CDATA[ */
var globals = {"ajax_url":"<?php Typecho_Widget::widget('Widget_Security')->to($security); $security->index('?'); ?>","post_id":"<?php if ($this->is('post')){$this->cid();}else{echo '0';} ?>","theme_url":"<?php $this->options->themeUrl(); ?>"};
var __ = {"load_more":"\u52a0\u8f7d\u66f4\u591a","reached_the_end":"- \u6ca1\u6709\u66f4\u591a\u5185\u5bb9 -","thank_you":"\u8c22\u8c22\u70b9\u8d5e","success":"\u64cd\u4f5c\u6210\u529f","cancelled":"\u53d6\u6d88\u70b9\u8d5e"};
/* ]]> */
</script>
<?php $this->options->header(); ?>
</head>

<body<?php if ($this->options->skin=='1'): ?> class="maoboli"<?php endif; ?>>

<?php if (!empty($this->options->tools) && in_array('qzdenglu', $this->options->tools) && !$this->user->hasLogin() && !isset($_GET['login'])&& !isset($_GET['register'])){
header("location:".$this->options->rootUrl."?login");
} ?>