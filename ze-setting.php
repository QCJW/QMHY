<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php $this->need('header.php'); ?>
<?php $this->need('sidebar.php'); ?>
<div class="bg-gradient-primary uk-height-small uk-position-absolute uk-width-1-1"></div>

<div class="main_content_inner">

<div class="section-small uk-light">
<h1> 个人设置 </h1>
</div>
<?php Typecho_Widget::widget('Widget_Security')->to($security); ?>
<?php
$px='';$cx='';$mx='';$ux='';$sx='';
foreach ($_COOKIE as $key => $value) {
if(strpos($key,'typecho_form_message') !== false){
$notice=$_COOKIE[$key];
$notice=json_decode($notice, true);
$px=$notice['password'];//密码框下方提醒
$cx=$notice['confirm'];//重复密码框下方提醒
$mx=$notice['mail'];
$ux=$notice['url'];
$sx=$notice['screenName'];
setcookie($key, '');
break;
}}



?>


<div class="m-auto uk-position-relative uk-grid-stack">

<div class="mt-sm-3 pl-sm-0">
<div class="uk-card-default rounded">
<div class="p-3">
 <h5 class="mb-0"> 基本信息 </h5>
</div>
<hr class="m-0">

<form action="<?php $security->index('/action/users-profile'); ?>" method="post" enctype="application/x-www-form-urlencoded"><div class="uk-child-width-1-2@s uk-grid-small p-4 uk-grid">

<div class="uk-first-column">
<h5 class="uk-text-bold mb-2"> 昵称 </h5>
<input type="text" class="uk-input" value="<?php $this->user->screenName(); ?>" name="screenName">
</div>
<div class="uk-first-column">
<h5 class="uk-text-bold mb-2"> 邮箱 </h5>
<input type="text" class="uk-input" value="<?php $this->user->mail(); ?>" name="mail">
</div>

</div>
<div class="uk-flex uk-flex-right p-4">
<input name="do" type="hidden" value="profile">
<button type="submit" class="button primary">保存</button>
</div>
</form>

</div>


<div class="uk-card-default rounded mt-4">
<div class="p-3">
<h5 class="mb-0"> 密码修改 </h5>
</div>
<hr class="m-0">

<form action="<?php $security->index('/action/users-profile'); ?>" method="post" enctype="application/x-www-form-urlencoded" ><div class="uk-child-width-1-2@s uk-grid-small p-4 uk-grid">
<div class="uk-grid-margin">
<h5 class="uk-text-bold mb-2"> 用户密码 </h5>
<input class="uk-input" type="password" name="password">
<?php if(empty($px)){
echo '<span class="uk-text-meta">建议使用特殊字符与字母、数字的混编样式,以增加系统安全性.</span>';
}else{echo '<span class="uk-text-meta uk-text-danger">'.$px.'</span>';
} ?>



</div>
<div class="uk-grid-margin">
<h5 class="uk-text-bold mb-2"> 用户密码确认 </h5>
<input class="uk-input" type="password" name="confirm">
<?php if(empty($cx)){
echo '<span class="uk-text-meta">请确认你的密码, 与上面输入的密码保持一致.</span>';
}else{echo '<span class="uk-text-meta uk-text-danger">'.$cx.'</span>';
} ?>

</div></div>
<div class="uk-flex uk-flex-right p-4">
<input name="do" type="hidden" value="password">
<button type="submit" class="button primary">保存</button>
</div>

</form>

</div>
</div>
</div>
<?php $this->need('footer.php'); ?>
<?php if(!empty($mx)||!empty($ux)||!empty($sx)){
?>
<script>
ncPopup('small', "<?php echo $mx.$ux.$sx; ?>");
</script>
<?php
} ?>