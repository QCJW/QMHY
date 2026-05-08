<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php $this->need('header.php'); ?>
<?php $this->need('sidebar.php'); ?>
<div class="bg-gradient-primary">
                        <div class="main_content_inner uk-light pb-0">
        
                            <div class="p-3">
                                <h1 class="mt-4"><font style="vertical-align: inherit;"><font style="vertical-align: inherit;"><?php $this->title() ?></font></font></h1>
                            </div>
        

                        </div>
                    </div>

<div class="main_content_inner">
<div class="mt-4 uk-grid-large uk-grid pl-lg-3" id="zepost" style="min-height: 55vh;">

<div class="uk-width-4-4@s uk-first-column">   
<?php $this->content(); ?> 

<?php $this->need('comments.php'); ?>
 
</div>


                        </div>


                </ul>
<?php $this->need('footer.php'); ?>
