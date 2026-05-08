<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php if (!empty($this->options->tools) && in_array('qzlogin', $this->options->tools) && !$this->user->hasLogin()){define("pl",1);}else{define("pl",0);} ?>
<?php function threadedComments($comments, $options) {
    $commentClass = '';$sf='';
    if ($comments->authorId) {
        if ($comments->authorId == $comments->ownerId) {$sf='<svg viewBox="0 0 24 24" class="ml-1" width="14" height="14"><svg viewBox="0 0 24 24" x="-3" y="-3" fill="#FFFFFF" width="30" height="30"><path d="M3.56231227,13.8535307 C2.40051305,12.768677 2.41398885,11.0669203 3.59484487,9.99979213 L3.59222085,9.99654885 C4.26730143,9.45036719 4.79446755,8.21005186 4.7184197,7.34453784 L4.72305873,7.34412719 C4.66942824,5.75539997 5.8824188,4.56066914 7.47188965,4.64242381 L7.47229112,4.6386236 C8.33515314,4.72977993 9.58467253,4.22534048 10.1426329,3.55925173 L10.1462611,3.56228565 C11.2316055,2.40008701 12.9353108,2.41394456 14.0015072,3.59634088 L14.0047263,3.59374004 C14.5498229,4.26841874 15.7896857,4.79521622 16.6545744,4.71844347 L16.6549836,4.72304294 C18.245027,4.66894057 19.4396947,5.88213996 19.3575031,7.47241135 L19.3623099,7.47292747 C19.2704388,8.3358681 19.7742711,9.58421483 20.4407199,10.1424506 L20.437686,10.1460789 C21.5997217,11.2312209 21.5860695,12.9345218 20.4042441,14.0007396 L20.4072865,14.0045125 C19.7325967,14.5495925 19.2055209,15.7896954 19.2815865,16.6561959 L19.2770449,16.6565978 C19.3315454,18.2453037 18.1173775,19.4393568 16.5274188,19.3571512 L16.5269029,19.3619539 C15.6647098,19.270083 14.415408,19.7741709 13.8573671,20.4403558 L13.8537409,20.4373235 C12.76842,21.5995708 11.0650432,21.5864553 9.99899434,20.4039226 L9.99527367,20.406923 C9.45025436,19.7323399 8.21017638,19.2051872 7.34461983,19.2812352 L7.344304,19.2776405 C5.75448683,19.3312904 4.55977145,18.1170085 4.64254978,16.527117 L4.63769921,16.5265942 C4.72957031,15.6644394 4.22547659,14.4151814 3.55928015,13.8571569 L3.56231227,13.8535307 Z"></path></svg><path d="M2.63951518,13.3895441 C3.70763333,14.2842292 4.44777637,16.1226061 4.30075305,17.5023312 L4.32211542,17.3063047 C4.17509209,18.6910561 5.17786655,19.7063729 6.5613937,19.5844846 L6.364106,19.6008202 C7.75140298,19.4789319 9.57474349,20.2554985 10.4468305,21.3349009 L10.3224262,21.1803415 C11.1982831,22.2647703 12.6257916,22.2723098 13.5167278,21.2079863 L13.3898102,21.3600325 C14.2845162,20.2919393 16.1229361,19.5518136 17.5026934,19.6988334 L17.3054057,19.6774716 C18.6914461,19.8244915 19.7067866,18.8217404 19.5836389,17.4395022 L19.6012314,17.6367853 C19.4793403,16.2482641 20.255925,14.4249662 21.3353526,13.5528995 L21.1807897,13.677301 C22.2639871,12.8014646 22.2727834,11.3739894 21.2084351,10.483074 L21.3604848,10.6099886 C20.2923667,9.71530351 19.5522236,7.87818322 19.6992469,6.49720154 L19.6778846,6.69448464 C19.8249079,5.30847665 18.8221335,4.2944164 17.4386063,4.41630468 L17.635894,4.39871256 C16.248597,4.52185742 14.4252565,3.74529084 13.5531695,2.66588842 L13.6775738,2.81919121 C12.8017169,1.73601905 11.3742084,1.72722299 10.4832722,2.79154644 L10.6101898,2.63950024 C9.71548377,3.70759343 7.87706394,4.44771919 6.49730661,4.30195588 L6.69459432,4.32206116 C5.30855394,4.17504128 4.29447,5.17904888 4.41636114,6.56128713 L4.3987686,6.36400404 C4.52065973,7.75126861 3.74407501,9.57456653 2.66464737,10.4478898 L2.81921035,10.3222318 C1.73601288,11.1993248 1.72721662,12.6255433 2.79156494,13.5164587 L2.63951518,13.3895441 Z" fill="#0066FF"></path><svg class="Zi Zi--Check" fill="#fff" x="6" y="6" viewBox="0 0 24 24" width="12" height="12"><path d="M10.229 17.516c-.318.327-.75.484-1.199.484-.453 0-.884-.16-1.202-.488l-4.335-4.47a1.77 1.77 0 0 1 .007-2.459 1.663 1.663 0 0 1 2.397.01l3.137 3.246 9.072-9.329a1.662 1.662 0 0 1 2.397 0c.663.681.663 1.786 0 2.466L10.23 17.516z" fill-rule="evenodd"></path></svg></svg>';
            $commentClass .= ' comment-by-author';  //如果是文章作者的评论添加 .comment-by-author 样式
        } else {
            $commentClass .= ' comment-by-user';  //如果是评论作者的添加 .comment-by-user 样式
$sf='';
        }
    } 
    $commentLevelClass = $comments->_levels > 0 ? ' comment-child' : ' comment-parent';  //评论层数大于0为子级，否则是父级
?>

<li id="li-<?php $comments->theId(); ?>" class="comment-body<?php 
if ($comments->levels > 0) {
    echo ' comment-child children';
    $comments->levelsAlt(' comment-level-odd', ' comment-level-even');
} else {
    echo ' comment-parent hang';
}
$comments->alt(' comment-odd', ' comment-even');
    if ($comments->url) {
        $author = '<a href="' . $comments->url . '" target="_blank" rel="external nofollow">' . $comments->author . '</a>';
    } else {
                $author = '<span>' . $comments->author . '</span>';
    }
?>">
<div id="<?php $comments->theId(); ?>">
 <article id="div-<?php $comments->theId(); ?>" class="d-flex flex-fill comment-body my-4 py-md-2">
        <div class="comment-avatar flex-avatar w-48 mr-3">
            <img alt="" src="<?php tx($comments->mail); ?>" height="48" width="48" data-xurl="<?php tx($comments->mail,0,$comments->coid); ?>">        </div>
        <!-- .comment-author -->
<div class="flex-fill flex-column" style="max-width: calc(100% - 48px);margin-left: .5rem;">
            <div class="comment-author text-sm mb-1">
                <div>
                    <?php echo $author.$sf;; ?><span class="mx-1"></span><?php if ('waiting' == $comments->status) { ?><span class="text-muted">您的评论需管理员审核后才能显示！</span><?php } ?> </div>
            </div>
            <div class="comment-content text-sm">
                <?php 
$cos=parseBiaoQing($comments->content);
$cos = preg_replace('#<a(.*?) href="([^"]*/)?(([^"/]*)\.[^"]*)"(.*?)>#',
        '<a$1 href="$2$3"$5 target="_blank" rel="nofollow">', $cos);
echo get_comment_at($comments->coid).$cos;
 ?>
                            </div><!-- .comment-content -->
            <div class="comment-meta text-xs text-muted mt-1">
                <time class="mr-1"><?php echo timesince($comments->created); ?></time>
<?php if(pl==0): ?>
<span class="comment-reply cp-<?php $comments->theId(); ?> text-muted comment-reply-link"><?php $comments->reply('<span><i class="uil-comment-alt-message mr-1"></i></span>回复'); ?></span><span id="cancel-comment-reply" class="cancel-comment-reply cl-<?php $comments->theId(); ?> text-muted comment-reply-link" style="display:none" ><?php $comments->cancelReply('<span><i class="uil-comment-alt-upload mr-1"></i></span>取消'); ?></span>
<?php endif; ?>


            </div>
        </div><!-- .comment-text -->
    </article><!-- .comment-body -->
</div>



<?php if ($comments->children) { ?>
<?php $comments->threadedComments(); ?>
<?php } ?>
</li>
<?php } ?>




<div id="comments" class="post-comment border-hover mt-5">
<div class="h6 mb-4">
			<span class="d-inline-block border-title border-primary px-3">
				<span>评论</span> <span class="font-theme mx-1">(<?php $this->commentsNum(); ?>)</span>			</span>
		</div>
<?php if($this->allow('comment')): ?> 
 <div id="<?php $this->respondId(); ?>" class="comment-respond"  data-no-instant>









<?php if (pl==1): ?>

<div class="uk-alert-danger uk-alert" uk-alert="">
<p>登录后才能进行评论！</p>
</div>


<?php else: ?>

<form method="post" action="<?php $this->commentUrl() ?>" id="commentform" class="comment-form mb-3">
<div class="d-flex flex-fill">
<div class="comment-avatar flex-avatar w-48 bg-secondary mr-3">
<?php if($this->user->hasLogin()): ?>
<img src="<?php tx($this->user->mail); ?>"></div>
<?php else: ?>

<img src="<?php $this->options->themeUrl('assets/images/avatars/avatar-1.jpg'); ?>"></div>

<?php endif; ?>


<div class="flex-fill">
<div class="comment-form-body d-flex flex-fill align-items-center py-2 rounded">
<div class="form-group comment-form-textarea flex-fill m-0">
<textarea id="comment" name="text" class="form-control OwO-textarea" rows="3" required><?php $this->remember('text'); ?></textarea>
</div>
    
</div>
<?php if(!$this->user->hasLogin()): ?>
<div class="comment-form-info  mb-1">
<div class="uk-child-width-expand uk-grid-small uk-grid">
<div class="uk-width-1-3">							
<input class="uk-input" id="author" placeholder="昵称" name="author" type="text" value="<?php $this->remember('author'); ?>" required>
</div>
<div class="uk-width-1-3">
<input class="uk-input" id="mail" placeholder="Email" name="mail" type="email" value="<?php $this->remember('mail'); ?>"<?php if ($this->options->commentsRequireMail): ?> required<?php endif; ?>>
</div>
<div class="uk-width-1-3">
<input class="uk-input" id="url" placeholder="站点" name="url" type="url" value="<?php $this->remember('url'); ?>">
</div>
</div>
</div>     
<?php endif; ?>
 
<div class="form-text text-muted">
    
    
<li class="d-flex align-items-center"><button  class="button soft-primary" name="submit" id="submit" type="submit">提交评论</button></li>   
    
       
</div>   
   
</div></div>
</form>
<?php endif; ?>
















</div>
<?php else: ?>
<h3><?php _e('评论已关闭'); ?></h3>
<?php endif; ?>





<?php $this->comments()->to($comments); ?>
<?php if ($comments->have()): ?>
<?php $comments->listComments(); ?>

<nav class="navigation pagination" role="navigation">
<h2 class="screen-reader-text">Posts Navigation</h2>
<?php $comments->pageNav('<i class="uil uil-angle-left"></i>', '<i class="uil uil-angle-right"></i>', 2, '...', array('wrapTag' => 'div', 'wrapClass' => 'nav-links', 'itemTag' => '','aClass' => 'page-numbers','textClass' => 'page-numbers', 'currentClass' => 'page-numbers current', 'prevClass' => 'page-numbers prev', 'nextClass' => 'next page-numbers',)); ?>
</nav>


<?php endif; ?>








</div>

<script type="text/javascript">
function showhidediv(id){var sbtitle=document.getElementById(id);if(sbtitle){if(sbtitle.style.display=='flex'){sbtitle.style.display='none';}else{sbtitle.style.display='flex';}}}
(function(){window.TypechoComment={dom:function(id){return document.getElementById(id)},pom:function(id){return document.getElementsByClassName(id)[0]},iom:function(id,dis){var alist=document.getElementsByClassName(id);if(alist){for(var idx=0;idx<alist.length;idx++){var mya=alist[idx];mya.style.display=dis}}},create:function(tag,attr){var el=document.createElement(tag);for(var key in attr){el.setAttribute(key,attr[key])}return el},reply:function(cid,coid){var comment=this.dom(cid),parent=comment.parentNode,response=this.dom("<?php echo $this->respondId(); ?>"),input=this.dom("comment-parent"),form="form"==response.tagName?response:response.getElementsByTagName("form")[0],textarea=response.getElementsByTagName("textarea")[0];if(null==input){input=this.create("input",{"type":"hidden","name":"parent","id":"comment-parent"});form.appendChild(input)}input.setAttribute("value",coid);if(null==this.dom("comment-form-place-holder")){var holder=this.create("div",{"id":"comment-form-place-holder"});response.parentNode.insertBefore(holder,response)}comment.appendChild(response);this.iom("comment-reply","");this.pom("cp-"+cid).style.display="none";this.iom("cancel-comment-reply","none");this.pom("cl-"+cid).style.display="";if(null!=textarea&&"text"==textarea.name){textarea.focus()}return false},cancelReply:function(){var response=this.dom("<?php echo $this->respondId(); ?>"),holder=this.dom("comment-form-place-holder"),input=this.dom("comment-parent");if(null!=input){input.parentNode.removeChild(input)}if(null==holder){return true}this.iom("comment-reply","");this.iom("cancel-comment-reply","none");holder.parentNode.insertBefore(response,holder);return false}}})();
</script>