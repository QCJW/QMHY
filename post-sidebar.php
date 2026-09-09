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
<div class="media media-3x4">
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
$db = Typecho_Db::get();
$to = $this->widget('Widget_Abstract_Contents');
$s = isset($this->categories[0]['mid']) ? $this->categories[0]['mid'] : 0;
$page = 5;

// 1. 优先尝试从当前同分类下随机获取文章
$select = $db->select()->from('table.contents')
    ->join('table.relationships', 'table.contents.cid = table.relationships.cid')
    ->where('table.relationships.mid = ?', $s)
    ->where('table.contents.type = ?', 'post')
    ->where('table.contents.status = ?', 'publish')
    ->where('table.contents.cid != ?', $this->cid)
    ->order('RAND()')
    ->limit($page);

$posts = $db->fetchAll($select);

// 2. 如果同分类下的文章不足 5 篇，直接切换为全站随机获取
if (count($posts) < $page) {
    $select = $db->select()->from('table.contents')
        ->where('table.contents.type = ?', 'post')
        ->where('table.contents.status = ?', 'publish')
        ->where('table.contents.cid != ?', $this->cid)
        ->order('RAND()')
        ->limit($page);
    $posts = $db->fetchAll($select);
}

// 3. 将结果压入 Widget 对象中，保证后续模板方法（permalink, fields等）正常调用
if ($posts) {
    foreach ($posts as $post) {
        $to->push($post);
    }
}

while($to->next()): ?>

<div class="uk-first-column">
<a href="<?php $to->permalink(); ?><?php if($to->fields->mp4&&strlen($to->fields->mp4) > 10){ echo '?action=get&p=1';}?>" class="video-post">
<div class="media media-3x4">
<div class="media-content scrollLoading" data-xurl="<?php showThumbnail($to); ?>"></div>
</div>
<div class="video-post-content">
<h3><font style="vertical-align: inherit;"><font style="vertical-align: inherit;"><?php $to->title(); ?></font></font></h3>
</div></a></div>   

<?php endwhile; ?>

                        </div>
                        
                    </div>