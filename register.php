<?php if(!defined('__TYPECHO_ROOT_DIR__')) exit; 
 $this->need('header.php');
?>

<div uk-height-viewport="expand: true" class="uk-flex uk-flex-middle">
    <div class="uk-width-1-3@m uk-width-1-2@s m-auto">
        <div class="uk-card-default p-6 rounded">
            <div class="my-4 uk-text-center">
                <h2 class="mb-0">注册一个新账号</h2>
                <p class="my-2"></p>
                
                <?php 
                // 提示用户需要验证邮箱
                $allPlugins = Typecho_Plugin::export();
                if(is_array($allPlugins['activated']) && array_key_exists('MailVerify', $allPlugins['activated'])): 
                ?>
                <div class="uk-alert-warning" uk-alert>
                    <p class="uk-text-small" style="color:#d32f2f;">
                        <i class="uil uil-envelope-exclamation"></i> 
                        <b>注意：</b>注册时系统将进行邮箱验证。请务必填写<b>真实有效的邮箱</b>，否则将无法登录。
                    </p>
                </div>
                <?php endif; ?>
            </div>

            <form class="uk-child-width-1-1 uk-grid-small" action="<?php $this->options->registerAction();?>" method="post" name="register" role="form" uk-grid>
                <input type="hidden" name="_" value="<?php echo $this->security->getToken($this->request->getRequestUrl());?>">
                
                <div>
                    <div class="uk-form-group">
                        <label class="uk-form-label"> 用户名</label>
                        <div class="uk-position-relative w-100">
                            <span class="uk-form-icon">
                                <i class="uil uil-user"></i>
                            </span>
                            <input class="uk-input" type="text" placeholder="设置您的用户名" name="name" required>
                        </div>
                    </div>
                </div>
                
                <div>
                    <div class="uk-form-group">
                        <label class="uk-form-label"> 邮箱</label>
                        <div class="uk-position-relative w-100">
                            <span class="uk-form-icon">
                                <i class="uil uil-at"></i>
                            </span>
                            <input class="uk-input" type="email" name="mail" placeholder="用于接收验证邮件" required>
                        </div>
                    </div>
                </div>

                <?php if(array_key_exists('Rdog', $allPlugins['activated'])): ?>
                <div class="uk-width-1-2@s">
                    <div class="uk-form-group">
                        <label class="uk-form-label"> 密码</label>
                        <div class="uk-position-relative w-100">
                            <span class="uk-form-icon">
                                <i class="uil uil-padlock"></i>
                            </span>
                            <input class="uk-input" type="password" name="password" placeholder="********" required>
                        </div>
                    </div>
                </div>
                <div class="uk-width-1-2@s">
                    <div class="uk-form-group">
                        <label class="uk-form-label"> 再次确认密码</label>
                        <div class="uk-position-relative w-100">
                            <span class="uk-form-icon">
                                <i class="uil uil-padlock"></i>
                            </span>
                            <input class="uk-input" type="password" name="confirm" placeholder="********" required>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <?php if(array_key_exists('InvitationCode', $allPlugins['activated'])): ?>
                <div class="uk-form-group">
                    <label class="uk-form-label"> 邀请码</label>
                    <div class="uk-position-relative w-100">
                        <span class="uk-form-icon">
                            <i class="uil uil-exclamation-triangle"></i>
                        </span>
                        <input class="uk-input" type="text" id="code_cxa" name="code_cxa" placeholder="请输入邀请码">
                    </div>
                </div>
                <?php endif; ?>

                <div>
                    <div class="mt-4 uk-flex-middle uk-grid-small" uk-grid>
                        <div class="uk-width-expand@s">
                            <p> 如果您有账号，请→ <a href="<?php $this->options->rootUrl(); ?>?login">登录</a></p>
                        </div>
                        <div class="uk-width-auto@s">
                            <input type="hidden" name="referer" value="<?php $this->options->siteUrl(); ?>?setting">
                            <button type="submit" class="button primary">注册</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php 
 $this->need('footer.php');
?>