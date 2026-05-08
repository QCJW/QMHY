                   <div class="uk-width-expand@m">


<?php $this->related(5)->to($relatedPosts); ?>
    <?php if ($relatedPosts->have()): ?>
<div class="uk-flex uk-flex-middle uk-flex-between px-1 pb-3">

<nav class="responsive-tab">
                    <ul>
                        <li class="uk-active"><a href="javascript:void(0);" class="uk-padding-remove-left"> 相关推荐 </a></li>
                    </ul>
                </nav>

<!--待定功能<label class="btn-switch">
<input type="checkbox">
<span class="btn-switch-slider" uk-toggle="target: #wrapper; cls: sidebar-out"></span>
</label>-->

</div>
<div class="uk-child-width-1-1@m uk-child-width-1-2 uk-grid uk-grid-stack" uk-grid="">

    <?php while ($relatedPosts->next()): ?>

<div class="uk-first-column">
<a href="<?php $relatedPosts->permalink(); ?><?php if($relatedPosts->fields->mp4&&strlen($relatedPosts->fields->mp4) > 10){ echo '?action=get&p=1';}?>" class="video-post">
<div class="media media-16x9">
<span class="video-post-time"><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">
<?php fanbiao($relatedPosts); ?></font></font></span>
<div class="media-content scrollLoading" data-xurl="<?php showThumbnail($relatedPosts); ?>"></div>
</div>
<div class="video-post-content">
<h3><font style="vertical-align: inherit;"><font style="vertical-align: inherit;"><?php $relatedPosts->title(); ?></font></font></h3>
</div></a></div>   
    <?php endwhile; ?>
 </div>
<?php endif; ?>
    

<!-- 横幅广告 -->
<?php if(!$this->options->addie||!in_array($this->cid,explode(",", $this->options->addie))): ?>
<?php if(!$this->request->isAjax()): ?><?php if($this->options->ads): ?>
 <div class="uk-flex uk-flex-middle uk-flex-between px-1 pb-3">
<nav class="responsive-tab">
<ul>
<li class="uk-active"><a href="javascript:void(0);" class="uk-padding-remove-left"> 广告赞助 </a></li>
</ul>
</nav></div>
 <div class="uk-flex uk-flex-middle uk-flex-between px-1 pb-3">
<div class="uk-first-column">
<?php $this->options->ads(); ?>
</div></div>
<?php endif; ?><?php endif; ?><?php endif; ?>
<!-- 横幅广告 -->



 <div class="uk-flex uk-flex-middle uk-flex-between px-1 pb-3">


<nav class="responsive-tab">
                    <ul>
                        <li class="uk-active"><a href="javascript:void(0);" class="uk-padding-remove-left"> 随机推荐 </a></li>
                    </ul>
                </nav>
<!--待定功能<label class="btn-switch">
<input type="checkbox">
<span class="btn-switch-slider" uk-toggle="target: #wrapper; cls: sidebar-out"></span>
</label>-->

                        </div>
                        <div class="uk-child-width-1-1@m uk-child-width-1-2 uk-grid uk-grid-stack" uk-grid="">

<?php 
$s=$this->categories[0]['mid'];$page=5;
$this->widget('Widget_Post_tongleisuiji', 'mid='.$s.'&pageSize='.$page.'&cid='.$this->cid)->to($to); ?>
<?php
if(!$to->have()){
$this->widget('Widget_Post_tongleisuiji@null', 'mid=0&pageSize='.$page.'&cid='.$this->cid)->to($to);
}

while($to->next()): ?>



<div class="uk-first-column">
<a href="<?php $to->permalink(); ?><?php if($to->fields->mp4&&strlen($to->fields->mp4) > 10){ echo '?action=get&p=1';}?>" class="video-post">
<div class="media media-16x9">
<span class="video-post-time"><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">
<?php fanbiao($to); ?></font></font></span>
<div class="media-content scrollLoading" data-xurl="<?php showThumbnail($to); ?>"></div>
</div>
<div class="video-post-content">
<h3><font style="vertical-align: inherit;"><font style="vertical-align: inherit;"><?php $to->title(); ?></font></font></h3>
</div></a></div>   




<?php endwhile; ?>






                        </div>
                        


                    </div>