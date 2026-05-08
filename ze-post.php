<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>

<div class="bg-gradient-primary">
                        <div class="main_content_inner uk-light pb-0">
        
                            <div class="py-3">
                                <h1 class="mt-4"><font style="vertical-align: inherit;"><font style="vertical-align: inherit;"><?php $this->title() ?></font></font></h1>
                                <p><font style="vertical-align: inherit;"><font style="vertical-align: inherit;"> <?php $this->category(','); ?> </font></font></p>
                            </div>
        

                        </div>
                    </div>

<div class="main_content_inner">
<div class="mt-4 uk-grid-large uk-grid pl-lg-3" id="zepost" style="min-height: 55vh;">

<div class="uk-width-4-4@s uk-first-column post-content">   
<?php if($this->hidden||$this->titleshow): ?>
<form action="<?php echo Typecho_Widget::widget('Widget_Security')->getTokenUrl($this->permalink); ?>" method="post" class="protected">
<div>
<span class="uk-text-middle uk-text-danger">请输入密码访问</span>
</div>
<div class="uk-margin-small">
<div uk-form-custom="target: true" class="uk-form-custom uk-first-column">
<input class="uk-input"  name="protectPassword" type="password" placeholder="请输入密码">
</div>
<input type="hidden" name="protectCID" value="<?php $this->cid(); ?>" />
<button class="uk-button uk-button-default" type="submit">提交</button>
</div>
</form>

<?php else: ?><?php $this->content(); ?>

<!-- 横幅广告 -->
<?php if(empty($this->options->addie)||!in_array($this->cid,explode(",", $this->options->addie))): ?>
<?php if(!$this->request->isAjax()): ?><?php if($this->options->ad): ?>
<?php $this->options->ad(); ?>
<?php endif; ?><?php endif; ?><?php endif; ?>
<!-- 横幅广告 -->

<?php endif;?>

<?php $this->need('comments.php'); ?>
 
</div>


                        </div>


                </ul>

