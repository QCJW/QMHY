<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php $this->need('header.php'); ?>
<?php $this->need('sidebar.php'); ?>
<div class="main_content_inner">
<?php if ($this->options->showtime=='text'): ?>
<h3 class="uk-heading-line text-left"><span> <?php $this->archiveTitle(array(
            'category'  =>  _t('%s'),
            'search'    =>  _t('检索到包含 %s 的文章'),
            'tag'       =>  _t('%s'),
        ), '', ''); ?> </span></h3>
<?php $this->need('ze-text.php'); ?>
<?php else: ?>






<div uk-grid="" class="uk-grid uk-grid-stack">

<div class="uk-width-expand uk-first-column">
<div class="section-header mb-lg-5 border-0 uk-flex-middle">
<div class="section-header-left">
<h3 class="uk-heading-line text-left"><span> <?php $this->archiveTitle(array(
            'category'  =>  _t('%s'),
            'search'    =>  _t('检索到包含 %s 的文章'),
            'tag'       =>  _t('%s'),
        ), '', ''); ?> </span></h3>
</div>

</div>
<?php if ($this->have()): ?>
<div class="uk-child-width-1-5@l uk-child-width-1-4@m uk-child-width-1-3@s uk-child-width-1-2" uk-grid >
<?php while($this->next()): ?>
<div tabindex="-1" class="uk-animation-slide-bottom-small">
<a href="<?php $this->permalink(); ?><?php if(strlen($this->fields->mp4) > 10){ echo '?action=get&p=1';}?>" class="video-post">
<div class="media media-16x9">
<span class="video-post-count"><font style="vertical-align: inherit;"><font style="vertical-align: inherit;"><?php get_post_view($this); ?></font></font></span>
<div class="media-content scrollLoading" data-xurl="<?php showThumbnail($this); ?>"></div>
</div>
<div class="video-post-content">
<h3><font style="vertical-align: inherit;"><font style="vertical-align: inherit;"><?php $this->title(); ?></font></font></h3>
</div></a></div> 


<?php endwhile; ?>
</div>
<?php else: ?>
<div class="uk-alert-danger" uk-alert>
<p>该栏目下暂无文章...</p>
</div>
<?php endif; ?>














<nav class="navigation pagination" role="navigation">
<?php $this->pageNav('<span class="uk-icon uk-pagination-next"><i class="uil uil-angle-left"></i></span>', '<span class="uk-icon uk-pagination-next"><i class="uil uil-angle-right"></i></span>', 3, '...', array('wrapTag' => 'div', 'wrapClass' => 'nav-links', 'itemTag' => '','itemClass' => '', 'aClass'=>'page-numbers','textTag' => 'li','textClass' => 'page-numbers', 'currentClass' => 'page-numbers current', 'prevClass' => 'page-numbers prev', 'nextClass' => 'page-numbers next',)); ?>
</nav>






</div>
</div>


<?php endif; ?>


<?php $this->need('footer.php'); ?>
