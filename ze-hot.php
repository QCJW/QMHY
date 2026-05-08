<?php 
/**
 * 热门文章
 * 
 * @package custom 
 * 
 */
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
$this->need('header.php');
$this->need('sidebar.php'); 

$db     = Typecho_Db::get();
$prefix = $db->getPrefix();
if (!array_key_exists('views', $db->fetchRow($db->select()->from('table.contents')))) {
        $db->query('ALTER TABLE `' . $prefix . 'contents` ADD `views` INT(10) DEFAULT 0;');
    }

//热门文章
class Widget_Post_hot extends Widget_Abstract_Contents
{
    public function __construct($request, $response, $params = NULL)
    {
        parent::__construct($request, $response, $params);
        $this->parameter->setDefault(array('pageSize' => $this->options->commentsListSize, 'parentId' => 0, 'ignoreAuthor' => false));
    }
    public function execute()
    {
        $select  = $this->select()->from('table.contents')
->where("table.contents.password IS NULL OR table.contents.password = ''")
->where('table.contents.status = ?','publish')
->where('table.contents.created <= ?', time())
->where('table.contents.type = ?', 'post')
->limit($this->parameter->pageSize)
->order('table.contents.views', Typecho_Db::SORT_DESC);
 $this->db->fetchAll($select, array($this, 'push'));
    }
}


?>
<div class="main_content_inner">







<div uk-grid="" class="uk-grid uk-grid-stack">

<div class="uk-width-expand uk-first-column">
<div class="section-header mb-lg-5 border-0 uk-flex-middle">
<div class="section-header-left">
<h3 class="uk-heading-line text-left"><span> 热门文章 </span></h3>
</div>

</div>

<div class="uk-child-width-1-5@l uk-child-width-1-4@m uk-child-width-1-3@s uk-child-width-1-2" uk-grid>

<?php $this->widget('Widget_Post_hot@hot', 'pageSize=25')->to($hot); ?>
<?php if($hot->have()): ?>
<?php while($hot->next()): ?>
<div tabindex="-1" class="uk-animation-slide-bottom-small">
<a href="<?php $hot->permalink(); ?><?php if($hot->fields->mp4&&strlen($hot->fields->mp4) > 10){ echo '?action=get&p=1';}?>" class="video-post">
<div class="media media-3x4">
<!-- <span class="video-post-count"><font style="vertical-align: inherit;"><font style="vertical-align: inherit;"><?php get_post_view($hot); ?></font></font></span> -->
<div class="media-content scrollLoading" data-xurl="<?php showThumbnail($hot); ?>"></div>
</div>
<div class="video-post-content">
<h3><font style="vertical-align: inherit;"><font style="vertical-align: inherit;"><?php $hot->title(); ?></font></font></h3>
</div></a></div> 

<?php endwhile; ?>
<?php else: ?>暂无文章<?php endif; ?>




</div>







</div>
</div>


    
   
    
    
    
<?php $this->need('footer.php'); ?>
