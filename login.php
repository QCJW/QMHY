<?php if(!defined('__TYPECHO_ROOT_DIR__')) exit; 
 $this->need('header.php');
?>

<div uk-height-viewport="expand: true" class="uk-flex uk-flex-middle">
    <div class="uk-width-1-3@m uk-width-1-2@s m-auto">
        <div class="uk-card-default p-6 rounded">
            <div class="my-4 uk-text-center">
                <h2 class="mb-0"> Welcome </h2>
                <p class="my-2">请在下方登录您的账号</p>
            </div>

            <div class="uk-margin-bottom">
                <?php 
                // 获取插件传递回来的消息
                $notice = Typecho_Widget::widget('Widget_Notice'); 
                if ($notice->list): 
                ?>
                    <?php foreach ($notice->list as $msg): ?>
                    <div class="uk-alert-<?php echo $msg[1] == 'error' ? 'danger' : 'success'; ?>" uk-alert>
                        <a class="uk-alert-close" uk-close></a>
                        <p><?php echo $msg[0]; ?></p>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <form action="<?php $this->options->loginAction()?>" method="post" name="login" rold="form">
                <div class="uk-form-group">
                    <label class="uk-form-label"> 用户名</label>
                    <div class="uk-position-relative w-100">
                        <span class="uk-form-icon">
                            <i class="uil uil-user"></i>
                        </span>
                        <input class="uk-input" type="text" placeholder="用户名/邮箱" name="name">
                    </div>
                </div>

                <div class="uk-form-group">
                    <label class="uk-form-label"> 密码</label>
                    <div class="uk-position-relative w-100">
                        <span class="uk-form-icon">
                            <i class="uil uil-padlock"></i>
                        </span>
                        <input class="uk-input" type="password" placeholder="请输入密码" name="password">
                    </div>
                </div>

                <div class="mt-4 uk-flex-middle uk-grid-small" uk-grid>
                    <?php if($this->options->allowRegister): ?>
                    <div class="uk-width-expand@s">
                        <p> 没有账号请点击→ <a href="<?php $this->options->rootUrl(); ?>?register">注册</a></p>
                    </div><?php endif; ?>

                    <div class="uk-width-auto@s">
                        <input type="hidden" name="referer" value="<?php $this->options->siteUrl(); ?>">
                        <button type="submit" class="button primary">登录</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php 
 $this->need('footer.php');
?>