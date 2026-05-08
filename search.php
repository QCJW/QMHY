<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
 $this->need('header.php');
 $this->need('sidebar.php');

if($this->options->rewrite==0){
$soso="/index.php/search/sy/";
}else{
$soso="/search/sy/";
}

$sousou=$this->options->rootUrl.$soso;


$gj="";$can="";
if($this->request->gaojijiansuo){
$gj="&gaojijiansuo=1";
}
$cat=intval($this->request->cat);
$tag=intval($this->request->tag);
$niandai=intval($this->request->niandai);
$zhuangtai=intval($this->request->zhuangtai);
$site=intval($this->request->site);
if(!$site){$site=0;}
if(!$cat){$cat=0;}
if(!$tag){$tag=0;}
if(!$niandai){$niandai=0;}
if(!$zhuangtai){$zhuangtai=-2;}
 ?>

<div class="main_content_inner">











<?php if(!$this->request->gaojijiansuo): ?>
<h3>
<?php $this->archiveTitle(array(
'search'    =>  _t('检索到包含 %s 的文章'),
        ), '', ''); ?></h3>
<div class="row archive mt-2">
<?php else: ?>
<?php 

$can='?cat='.$cat.'&site='.$site.'&tag='.$tag.'&niandai='.$niandai.'&zhuangtai='.$zhuangtai.$gj;


 ?>
<div class="uk-card uk-card-default uk-card-body mt-2 mb-4 p-3">
<h4 class="uk-card-title">高级索引</h4>

<div class="mb-1"><span class="button white px-0">类型：</span><a href="<?php echo $sousou; ?>?niandai=<?php echo $niandai; ?>&cat=0&site=0&tag=0<?php echo $gj; ?>&zhuangtai=<?php echo $zhuangtai; ?>" class="button white<?php if($cat==0){echo " uk-text-danger";} ?>">全部</a>                    
<?php $this->widget('Widget_Metas_Category_List')->to($categorys); ?>
<?php while($categorys->next()): ?><?php if ($categorys->levels === 0): ?>
<a href="<?php echo $sousou; ?>?niandai=<?php echo $niandai; ?>&cat=<?php $categorys->mid(); ?>&site=0&tag=0<?php echo $gj; ?>&zhuangtai=<?php echo $zhuangtai; ?>" title="<?php $categorys->name(); ?>" class="button white<?php if($cat==$categorys->mid){echo " uk-text-danger";} ?>"><?php $categorys->name(); ?></a>
<?php endif; ?><?php endwhile; ?>

</div>





<?php if ($cat != 0): ?>
<?php $this->widget('Widget_Post_cat@cat', 'mid='.$cat)->to($categorys); ?>
<?php if ($categorys->have()): ?>

<div class="mb-1"><span class="button white px-0">子类：</span><a href="<?php echo $sousou; ?>?niandai=<?php echo $niandai; ?>&cat=<?php echo $cat.$gj; ?>&site=0&tag=<?php echo $tag.$gj; ?>&zhuangtai=<?php echo $zhuangtai; ?>" class="button white<?php if($site==0){echo " uk-text-danger";} ?>">全部</a>                    

<?php while($categorys->next()): ?>
<a href="<?php echo $sousou; ?>?niandai=<?php echo $niandai; ?>&cat=<?php echo $cat.$gj; ?>&site=<?php $categorys->mid(); ?>&tag=<?php echo $tag.$gj; ?>&zhuangtai=<?php echo $zhuangtai; ?>" title="<?php $categorys->name(); ?>" class="button white<?php if($site==$categorys->mid){echo " uk-text-danger";} ?>"><?php $categorys->name(); ?></a>
<?php endwhile; ?></div>

<?php endif; ?>


<?php endif; ?>


<?php if ($cat != 0): ?>
<div class="mb-1"><span class="button white px-0">标签：</span><a href="<?php echo $sousou; ?>?niandai=<?php echo $niandai; ?>&tag=0&cat=<?php echo $cat; ?>&site=<?php echo $site; ?>&zhuangtai=<?php echo $zhuangtai.$gj; ?>" class="button white<?php if($tag==0){echo " uk-text-danger";} ?>">全部</a>

<?php 
// 获取该分类下的所有文章
$posts = $this->db->fetchAll($this->db->select('table.contents.cid')->from('table.contents')
    ->join('table.relationships', 'table.contents.cid = table.relationships.cid')
    ->where('table.relationships.mid = ?', $cat)
    ->where('table.contents.status = ?', 'publish')
    ->where('table.contents.type = ?', 'post'));

// 获取这些文章的所有标签
$tagIds = [];
foreach ($posts as $post) {
    $tagsForPost = $this->db->fetchAll($this->db->select('table.relationships.mid')->from('table.relationships')
        ->where('table.relationships.cid = ?', $post['cid'])
        ->join('table.metas', 'table.relationships.mid = table.metas.mid')
        ->where('table.metas.type = ?', 'tag'));
    foreach ($tagsForPost as $tagForPost) {
        $tagIds[] = $tagForPost['mid'];
    }
}

// 去重
$tagIds = array_unique($tagIds);

// 获取标签信息
$tags = [];
if (!empty($tagIds)) {
    $tags = $this->db->fetchAll($this->db->select()->from('table.metas')
        ->where('table.metas.mid IN (' . implode(',', $tagIds) . ')')
        ->where('table.metas.type = ?', 'tag')
        ->order('table.metas.count', Typecho_Db::SORT_DESC));
}

// 输出标签
foreach ($tags as $singleTag): ?>
<a rel="tag" href="<?php echo $sousou; ?>?niandai=<?php echo $niandai; ?>&tag=<?php echo $singleTag['mid']; ?>&cat=<?php echo $cat; ?>&site=<?php echo $site; ?>&zhuangtai=<?php echo $zhuangtai.$gj; ?>" class="button white<?php if($tag == $singleTag['mid']){echo " uk-text-danger";} ?>"><?php echo $singleTag['name']; ?></a>
<?php endforeach; ?>

</div>
<?php endif; ?>


<div class="mb-1"><span class="button white px-0">状态：</span><a href="<?php echo $sousou; ?>?niandai=<?php echo $niandai; ?>&cat=<?php echo $cat; ?>&site=<?php echo $site; ?>&tag=<?php echo $tag.$gj; ?>&zhuangtai=-2" class="button white<?php if($zhuangtai==-2){echo " uk-text-danger";} ?>">全部</a>    
<a href="<?php echo $sousou; ?>?niandai=<?php echo $niandai; ?>&cat=<?php echo $cat; ?>&site=<?php echo $site; ?>&tag=<?php echo $tag.$gj; ?>&zhuangtai=2" class="button white<?php if($zhuangtai==2){echo " uk-text-danger";} ?>">完结</a>
<a href="<?php echo $sousou; ?>?niandai=<?php echo $niandai; ?>&cat=<?php echo $cat; ?>&site=<?php echo $site; ?>&tag=<?php echo $tag.$gj; ?>&zhuangtai=1" class="button white<?php if($zhuangtai==1){echo " uk-text-danger";} ?>">连载</a>

<a href="<?php echo $sousou; ?>?niandai=<?php echo $niandai; ?>&cat=<?php echo $cat; ?>&site=<?php echo $site; ?>&tag=<?php echo $tag.$gj; ?>&zhuangtai=-1" class="button white<?php if($zhuangtai==-1){echo " uk-text-danger";} ?>">预告</a>
</div>



<div class="mb-1"><span class="button white px-0">年代：</span>
    <a href="<?php echo $sousou; ?>?niandai=0&cat=<?php echo $cat; ?>&site=<?php echo $site; ?>&tag=<?php echo $tag.$gj; ?>&zhuangtai=<?php echo $zhuangtai; ?>" class="button white<?php if($niandai==0){echo " uk-text-danger";} ?>">全部</a>     
    <a href="<?php echo $sousou; ?>?niandai=2025&cat=<?php echo $cat; ?>&site=<?php echo $site; ?>&tag=<?php echo $tag.$gj; ?>&zhuangtai=<?php echo $zhuangtai; ?>" class="button white<?php if($niandai==2025){echo " uk-text-danger";} ?>">2025</a>
</div>
                </div>

<?php endif; ?>











<?php if ($this->have()): ?>
<div class="uk-child-width-1-5@l uk-child-width-1-4@m uk-child-width-1-3@s uk-child-width-1-2" uk-grid >

<?php while($this->next()): ?>

<div tabindex="-1" class="uk-animation-slide-bottom-small">
<a href="<?php $this->permalink(); ?><?php if($this->fields->mp4&&strlen($this->fields->mp4) > 10){ echo '?action=get&p=1';}?>" class="video-post">
<div class="media media-3x4">



<div class="media-content scrollLoading" data-xurl="<?php showThumbnail($this); ?>"></div>
</div>
<div class="video-post-content">
<h3><font style="vertical-align: inherit;"><font style="vertical-align: inherit;"><?php $this->title(); ?></font></font></h3>
</div></a></div> 

<?php endwhile; ?>
</div>






<nav class="navigation pagination" role="navigation">
<?php $this->pageNav('<span class="uk-icon uk-pagination-next"><i class="uil uil-angle-left"></i></span>', '<span class="uk-icon uk-pagination-next"><i class="uil uil-angle-right"></i></span>', 3, '...', array('wrapTag' => 'div', 'wrapClass' => 'nav-links', 'itemTag' => '','itemClass' => '', 'aClass'=>'page-numbers','textTag' => 'li','textClass' => 'page-numbers', 'currentClass' => 'page-numbers current', 'prevClass' => 'page-numbers prev', 'nextClass' => 'page-numbers next','can'=>$can)); ?>
</nav>

<?php else: ?>


<div class="uk-alert-danger" uk-alert>
<p>未找到相关内容</p>
</div>
<?php endif; ?>






<?php $this->need('footer.php'); ?>