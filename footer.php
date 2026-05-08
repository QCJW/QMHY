<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>


<?php if(!isset($_GET['login'])&&!isset($_GET['register'])){ ?>

<div class="footer">

<?php if($this->options->links && $this->is('index')): ?>
<div class="uk-grid-collapse uk-grid uk-grid-stack mb-2" uk-grid="">
                        <div class="uk-width-expand@s uk-first-column youlink">
                            <p>友情链接：<?php $this->options->links(); ?></p>
                        </div>
                    </div>
<?php endif; ?>


                    <div class="uk-grid-collapse uk-grid" uk-grid>
                        <div class="uk-width-expand@s uk-first-column">
                            <p><?php if ($this->options->footerwen): ?><?php $this->options->footerwen(); ?><?php else: ?>©2021 <strong>YS2.</strong> All Rights Reserved.<?php endif; ?></p>
                        </div>
                        <!--<div class="uk-width-auto@s">
                            <nav class="footer-nav-icon">
                                <ul>
                                    <li><a href="#"><i class="icon-brand-facebook"></i></a></li>
                                    <li><a href="#"><i class="icon-brand-dribbble"></i></a></li>
                                    <li><a href="#"><i class="icon-brand-youtube"></i></a></li>
                                    <li><a href="#"><i class="icon-brand-twitter"></i></a></li>
                                </ul>
                            </nav>
                        </div>-->
                    </div>
                </div>

<?php } ?>







        </div></div>





    </div>


  

    <!-- javaScripts================================================== -->

<script src="<?php $this->options->themeUrl('assets/js/uikit.min.js'); ?>"></script>
<script src="<?php $this->options->themeUrl('assets/js/jquery.min.js'); ?>?3.3.1"></script>
<script src="<?php $this->options->themeUrl('assets/js/main.js'); ?>?20201018"></script>
<script>
$(function () {
/*ajax评论*/
//监听评论表单提交
$('#commentform').submit(function(){
        var form = $(this), params = form.serialize();
        var buttonhtml=form.find('#submit').html();
        // 添加functions.php中定义的判断参数
        params += '&themeAction=comment';
        
        // 解析新评论并附加到评论列表
        var appendComment = function(comment){
            // 评论列表
            var el = $('#comments > .comment-list');
            var pl = " comment-parent";
            if(0 != comment.parent){
                pl = " children";
                // 子评论则重新定位评论列表
                var el = $('#li-comment-'+comment.parent);
                // 父评论不存在子评论时
                if(el.find('.comment-list').length < 1){
                    $('<ol class="comment-list"></ol>').appendTo(el);
                }else if(el.find('.comment-list').length <1){
                    $('<ol class="comment-list"></ol>').appendTo(el);
                }
                el = $('#li-comment-'+comment.parent).find('.comment-list');
            }
            if(0 == el.length){
                $('<ol class="comment-list"></ol>').appendTo($('#comments'));
                el = $('#comments > .comment-list');
            }
                        // 评论html模板，根据具体主题定制
            var html = '<div id="{coid}"><article id="div-{coid}" class="d-flex flex-fill comment-body my-4 py-md-2"><div class="comment-avatar flex-avatar w-48 mr-3"><img alt="" src="{avatar}" height="48" width="48"></div><div class="flex-fill flex-column" style="max-width: calc(100% - 48px);margin-left: .5rem;"><div class="comment-author text-sm mb-1"><div><a href="{permalink}" target="_blank" rel="external nofollow">{author}</a><span class="">{sf}</span></div></div><div class="comment-content text-sm">{content}</div><div class="comment-meta text-xs text-muted mt-1"><time class="mr-1">刚刚</time><span class="text-muted">{status}</span></div></div></article></div>';
            $.each(comment,function(k,v){
                regExp = new RegExp('{'+k+'}', 'g');
                html = html.replace(regExp, v);
            });

            $(html).prependTo(el);
        };
        // ajax提交评论
        $.ajax({
            url: '<?php $this->permalink();?>',
            type: 'POST',
            data: params,
            dataType: 'json',
            beforeSend: function() { form.find('#submit').attr('disabled','disabled').html('提交中...');},
            complete: function() { form.find('#submit').removeAttr('disabled').html(buttonhtml);},
            success: function(result){
                if(1 == result.status){
                    // 新评论附加到评论列表
                    appendComment(result.comment);
                    form.find('textarea').val('');
TypechoComment.cancelReply();
ncPopupTips(1, __.success)
                }else{

var tishi=undefined === result.msg ? '评论失败请重试' : result.msg;
ncPopupTips(0, tishi);
                }
            },
            error:function(xhr, ajaxOptions, thrownError){
if(xhr.responseJSON.status==0){
ncPopupTips(0, xhr.responseJSON.msg);
}else{
ncPopupTips(0, "评论提交失败请重试");
}
            }
        });
return false;
});
});
</script>









<!--<?php
$chuci='chuci';
if(!$_COOKIE[$chuci]){
?>
<script>
ncPopup('middle', '<h3 class="uk-heading-line"><span class="uk-text-danger uk-text-bolder">感谢您使用遇见主题</span></h3><h4 class="uk-text-emphasis m-0">遇见你是我的幸运，您可以在后台模板设置处设置这里的内容！</h4>');
</script>
<?php
setcookie('chuci', 'meile',time()+3600*24);
}
?>-->

<?php
$p=Typecho_Cookie::getPrefix();
$q=$p.'__typecho_notice';
$y=$p.'__typecho_notice_type';
$px='';$cx='';$mx='';$ux='';$sx='';
if (isset($_COOKIE[$y]) &&($_COOKIE[$y]=='success' || $_COOKIE[$y]=='notice' || $_COOKIE[$y]=='error')){
	if (isset($_COOKIE[$q])){
		?><script>ncPopup('small', "<?php echo preg_replace('#\[\"(.*?)\"\]#','$1', $_COOKIE[$q]); ?>");
		</script>
		<?php
Typecho_Cookie::delete('__typecho_notice');
Typecho_Cookie::delete('__typecho_notice_type');
	}
}
?>
<?php $this->footer(); ?>
</body>

</html>
