<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php 
if ($this->options->rewrite==0){
$sideurl=$this->options->rootUrl."/index.php/";
}else{
$sideurl=$this->options->rootUrl;
}
$newurl=$sideurl.$_SERVER['REQUEST_URI'];
?>

    <!-- Wrapper -->
    <div id="wrapper"<?php if ($this->options->menu=='1'): ?> class="sidebar-out"<?php endif; ?><?php if ($this->options->menu=='2'): ?><?php if ($this->is('post')) : ?> class="sidebar-out"<?php endif; ?><?php endif; ?>>

        <!-- sidebar -->
        <div class="main_sidebar">


<div class="side-overlay" uk-toggle="target: #wrapper ; cls: collapse-sidebar mobile-visible"></div>

            <!-- sidebar header -->
            <div class="sidebar-header">
                <h4> 导航</h4>
<span class="btn-close" uk-toggle="target: #wrapper ; cls: collapse-sidebar mobile-visible"></span>
            </div>

            <!-- sidebar Menu -->
            <div class="sidebar">
                <div class="sidebar_innr" data-simplebar>

                    <div class="sections">
                        <h3> 导航 </h3>
                        <ul>
                            <li<?php if ($this->is('index')) : ?> class="active"<?php endif; ?>> <a href="<?php $this->options->rootUrl(); ?>/"> <i class="uil-home-alt"></i> <span> 首页 </span> </a></li>




    <!--循环显示页面-->
    <?php $this->widget('Widget_Contents_Page_List')->to($pages); ?>
<?php if (empty(Helper::options()->tools) || !in_array('soso',Helper::options()->tools)): ?>
<li<?php if($this->request->gaojijiansuo){echo ' class="active"';}?>>
<a class="side-nav-link" href="<?php $this->options->rootUrl(); ?><?php if($this->options->rewrite==0){echo "/index.php";} ?>/search/sy/?gaojijiansuo=1"><i class="uil-search"></i><span>高级索引</span></a>
</li>
<?php endif; ?>
    <?php while($pages->next()): ?>
<li<?php if($this->is('page', $pages->slug)||$pages->fields->url==$newurl): ?>  class="active"<?php endif; ?>> <a href="<?php if($pages->fields->url){$pages->fields->url();}else{$pages->permalink();} ?>"> <i class="uil-<?php if($pages->fields->icons){$pages->fields->icons();}else{echo 'location-arrow';} ?>"></i> <span>  <?php $pages->title(); ?> </span> </a></li>
    <?php endwhile; ?>
    <!--结束显示页面-->


</ul>
                    </div>

<?php if($this->user->pass('administrator', true)&&(!empty($this->options->tools) && in_array('cache',$this->options->tools))): ?>
<div id="foot">
<div class="uk-flex uk-flex-center mb-3">
                            <a href="<?php echo rooturl.'?huancun'; ?>" class="button default circle px-5">
                                <i class="uil-cloud-upload mr-1"></i>更新首页缓存</a>
</div>
</div>
<?php endif; ?>

                </div>
<div class="header__blur"></div>

            </div>

        </div>

        <!-- header -->
        <div id="main_header">
            <header>

                <!-- Logo-->
<i class="header-traiger uil-bars" uk-toggle="target: #wrapper ; cls: collapse-sidebar mobile-visible"></i>


                <!-- Logo-->
                <div id="logo">
                    <a href="<?php $this->options->rootUrl(); ?>/"> <img src="<?php $this->options->themeUrl('assets/images/logo.png'); ?>" alt=""></a>
                    <a href="<?php $this->options->rootUrl(); ?>/"> <img src="<?php $this->options->themeUrl('assets/images/logo-light.png'); ?>" class="logo-inverse" alt=""></a>
                </div>

                <!-- form search-->
                <div class="head_search">
                   <form method="post" action="<?php $this->options->siteUrl(); ?>" role="search">
                        <div class="head_search_cont">
                            <input value="" type="text" name="s" class="form-control"
                                placeholder="搜索视频.." autocomplete="off">
                            <i class="s_icon uil-search-alt"></i>
                        </div>




                    </form>
                </div>


<template id="single-search-template">
    <div class="py-3 py-md-4">
        <div class="font-theme text-xl text-center mb-3">- SEARCH ANY THING -</div>
        <div class="list-share text-center">
                <!-- form search-->
                <div class="head_search">
                   <form method="post" action="<?php $this->options->siteUrl(); ?>" role="search">
                        <div class="head_search_cont">
                            <input value="" type="text" name="s" class="form-control"
                                placeholder="搜索视频.." autocomplete="off" style="min-width: 100%;">
                            <i class="s_icon uil-search-alt"></i>
                        </div>




                    </form>
                </div>
        </div>
    </div>
</template>


                <!-- user icons -->
                <div class="head_user">

 <a href="javascript:;" class="btn-search-toggler opts_icon uk-hidden@l"> 
<i class="uil-search"></i>  
</a>


<a href="#" id="night-mode" class="opts_icon"> 
<i class="uil-sun"></i>  
<i class="uil-wind-moon"></i>
</a>


<?php if(!$this->user->hasLogin()): ?> 
<?php if($this->options->allowRegister): ?>
<a href="<?php $this->options->rootUrl(); ?>?register" class="btn-upgrade uk-visible@s"> <i class="uil-registered"></i> 注册</a>
<?php endif; ?>

<a href="<?php $this->options->rootUrl(); ?>?login" class="btn-upgrade"> <i class="uil uil-signin"></i> 登录</a>

<?php else: ?>    

                    <!-- profile -image -->
                    <a class="opts_account"> <img src="<?php tx($this->user->mail); ?>" alt=""></a>

                    <!-- profile dropdown-->
                    <div uk-dropdown="pos: top-right;mode:click ; animation: uk-animation-slide-bottom-small"
                        class="dropdown-notifications small">

                        <!-- User Name / Avatar -->
                        <a href="#">

                            <div class="dropdown-user-details">
                                <div class="dropdown-user-avatar">
                                    <img src="<?php tx($this->user->mail); ?>" alt="">
                                </div>
                                <div class="dropdown-user-name">
                                    <?php $this->user->screenName(); ?> <span> <?php $this->user->mail(); ?> <i class="uil-check"></i> </span>
                                </div>
                            </div>

                        </a>

                        <!-- User menu -->

                        <ul class="dropdown-user-menu">
<li><a href="<?php echo $sideurl.'/author/'.$this->user->uid; ?>"> <i class="uil-user"></i> 我的主页</a> </li>

<li><a href="<?php $this->options->rootUrl(); ?>?setting"> <i class="uil-cog"></i> 个人设置</a></li>


<?php if($this->user->group=='administrator'||$this->user->group=='editor'||$this->user->group=='contributor'): ?>
<li><a target="_blank" href="<?php $this->options->adminUrl(); ?>"> <i class="uil-sliders-v-alt"></i> 进入后台</a></li>
<?php endif; ?>

                            <div class="menu-divider">
                                <!--<li><a href="#"> <i class="icon-feather-help-circle"></i> 帮助</a>
                                </li>-->
                                <li><a href="<?php $this->options->logoutUrl(); ?>"> <i class="uil-sign-out-alt"></i> 登出账号</a>
                                </li>
                        </ul>


                    </div>
<?php endif; ?> 
                </div>

            </header>
<div class="header__blur"></div>
        </div>

<div class="main_content">