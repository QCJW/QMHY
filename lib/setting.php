<?php 

?>
<div id="tab-f" role="complementary"><ul class="typecho-option-tabs clearfix">
<li class="w-30" id="home" onclick="return Tabs.qie('home');" style="background:rgba(141,170,214,0.32);"><a>基础配置</a></li>
<li class="w-40" id="setc" onclick="return Tabs.qie('setc');"><a>功能设置</a></li>
<li class="w-30" id="helpme" onclick="return Tabs.qie('helpme');"><a style="color: red;">广告设置</a></li>
</ul></div>
<script>
(function(){
    window.Tabs = {
        dom: function(id) {
            return document.getElementById(id)
        },
        pom: function(id) {
            return document.getElementsByClassName(id)[0]
        },
        iom: function(id, dis) {
            var alist = document.getElementsByClassName(id);
            if (alist) {
                for (var idx = 0; idx < alist.length; idx++) {
                    var mya = alist[idx];
                    mya.style.display = dis
                }
            }
        },
        qie: function(c) {this.iom("home", "none");this.iom("setc", "none");this.iom("helpme", "none");this.dom("setc").style.background="";this.dom("home").style.background="";this.dom("helpme").style.background="";
//if(c=="helpme"){this.iom("typecho-option-submit", "none");}else{this.iom("typecho-option-submit", "block");}
            this.iom(c, "block");this.dom(c).style.background="rgba(141,170,214,0.32)";
            return false
        }
    }
})(); 
</script>
<div class="home">
<li><a href="<?php Typecho_Widget::widget('Widget_Options')->adminUrl('theme-editor.php?theme=' . $files->currentTheme() . '&file=config.php');?>">配置番剧表与首页布局</a></li>
logo，favicon等图片需要替换的话都在模板assets/images文件夹下</div>
<style><?php if(Typecho_Widget::widget('Widget_Options')->showtime!='cms'){echo '.cms{display:none}'; } ?></style>
<?php

$gg = new Typecho_Widget_Helper_Form_Element_Text('gg', NULL,NULL, _t('公告'), _t('需要需要公告的内容，不填则不显示公告！'));
$gg->setAttribute('class', 'col-mb-12 typecho-option home');
$form->addInput($gg);

$lunbo = new Typecho_Widget_Helper_Form_Element_Textarea('lunbo', NULL,NULL, _t('轮播图设置'), _t('填写格式是文章cid$图片地址然后换行输入下一个，推荐尺寸1300x350【这样电脑端显示会比较完美，因为是自适应设计所以这个尺寸手机端会差一些】'));
$lunbo->setAttribute('class', 'col-mb-12 typecho-option home');
$form->addInput($lunbo);

$douban = new Typecho_Widget_Helper_Form_Element_Text('douban', NULL,NULL, _t('API地址'), _t('该功能用于填写豆瓣或者bangumi的id获取影视信息，不填则默认一个随时可能失效的api，可根据该项目自行搭建api：https://github.com/Rhilip/pt-gen-cfworker，填写格式如：https://ptgen.rhilip.info 注意结尾没有/'));
$douban->setAttribute('class', 'col-mb-12 typecho-option home');
$form->addInput($douban);

    $tools = new Typecho_Widget_Helper_Form_Element_Checkbox('tools', 
    array(
'qzdenglu' => _t('强制登录（不登录不允许访问任何页面）'),
'qzlogin' => _t('禁止游客评论（评论需登录）'),
'ycnewadd' => _t('首页隐藏【最新加入】'),
'dark' => _t('勾选则默认黑色模式'),
'soso' => _t('勾选则关闭高级索引功能'),
'cache' => _t('勾选开启首页缓存功能，开启后需要进入首页点击更新缓存按钮来生成首页，每次首页内容有变动时都需要这样操作！'),
),
    array('qzlogin'), _t('<span onclick="bian()">拓展设置</span>'));
    $tools->setAttribute('class', 'col-mb-12 typecho-option setc');
    $form->addInput($tools->multiMode());


$d=array('不限制');
$n=1;
while ($n<11) {
$d[$n] = '从第'.$n.'集开始';$n++;
}

    $set1 = new Typecho_Widget_Helper_Form_Element_Select('login', $d, '0', _t('登录可看'), _t('视频登录可看设置'));
    $set1->setAttribute('class', 'col-mb-12 col-tb-6 typecho-option setc');
    $form->addInput($set1);


 $gravatars = new Typecho_Widget_Helper_Form_Element_Select('gravatars', array(
'cravatar.cn/avatar' => _t('Cravatar源'),'sdn.geekzu.org/avatar' => _t('极客族'),'gravatar.proxy.ustclug.org/avatar' => _t('中科大[不建议]'),'cdn.v2ex.com/gravatar' => _t('v2ex源'),'dn-qiniu-avatar.qbox.me/avatar' => _t('七牛源[不建议]'),'gravatar.helingqi.com/wavatar' => _t('禾令奇[建议]'),'gravatar.loli.net/avatar' => _t('loli.net源'),
    ), 'cravatar.cn/avatar',
    _t('gravatar头像源'), _t('默认cravatar.cn/avatar')); 
$gravatars->setAttribute('class', 'col-mb-12 col-tb-6 typecho-option setc');
$form->addInput($gravatars->multiMode());


$skin = new Typecho_Widget_Helper_Form_Element_Radio('skin', array(
'0' => _t('默认风格'), 
'1' => _t('毛玻璃风格'), 
),'0', _t('皮肤设置'),NULL);
$skin->setAttribute('class', 'col-mb-12 typecho-option home');
$form->addInput($skin);

	$menu = new Typecho_Widget_Helper_Form_Element_Radio('menu', array(
'0' => _t('全局展开'), 
'1' => _t('全局收缩'), 
'2' => _t('文章页收缩其他页展开'), 
),'2', _t('侧栏菜单设置'), _t('左侧导航条设置'));
$menu->setAttribute('class', 'col-mb-12 typecho-option home');
$form->addInput($menu);


$jxurl = new Typecho_Widget_Helper_Form_Element_Textarea('jxurl', NULL, NULL, _t('视频解析功能'), _t('填写视频解析地址，格式如：线路一$解析地址，多个解析请换行输入，不填写模板视频功能仅支持播放mp4与m3u8视频直链和qq quan的视频链接'));
$jxurl->setAttribute('class', 'col-mb-12 setc');
$form->addInput($jxurl);

$links = new Typecho_Widget_Helper_Form_Element_Textarea('links', NULL,NULL, _t('首页友链设置'), _t('填写格式例：&lt;a href="https://blog.zezeshe.com/" target="_blank"&gt;泽泽社长&lt;/a&gt;，不填则默认不显示友情链接'));$links->setAttribute('class', 'col-mb-12 home');
$form->addInput($links);

$header = new Typecho_Widget_Helper_Form_Element_Textarea('header', NULL,NULL, _t('头部信息'), _t('指html的<code>head</code>部分放置的内容，一般用来放置百度的统计代码'));$header->setAttribute('class', 'col-mb-12 home');
$form->addInput($header);

$footerwen = new Typecho_Widget_Helper_Form_Element_Textarea('footerwen', NULL,NULL, _t('自定义网站底部文字'), _t('默认为：©2020 <strong>YS2.</strong> All Rights Reserved.（支持html语法），此处可以填写备案信息或cnzz统计代码等'));$footerwen->setAttribute('class', 'col-mb-12 home');
$form->addInput($footerwen);

//广告位
$ad = new Typecho_Widget_Helper_Form_Element_Textarea('ad', NULL,NULL,'文章底部横幅广告', _t('可直接填入谷歌广告，或者按这个格式填入自定义广告&lt;a href="广告链接" target="_blank"&gt;&lt;img src="广告图片" style="width: 100%;" &gt;&lt;/a&gt;&gt;'));$ad->setAttribute('class', 'col-mb-6 helpme');
$form->addInput($ad);


//广告位
$ads = new Typecho_Widget_Helper_Form_Element_Textarea('ads', NULL,NULL,'相关文章底部广告', _t('可直接填入谷歌广告，或者按这个格式填入自定义广告&lt;a href="广告链接" target="_blank"&gt;&lt;img src="广告图片" style="width: 100%;" &gt;&lt;/a&gt;&gt;'));$ads->setAttribute('class', 'col-mb-6 helpme');
$form->addInput($ads);


$adh = new Typecho_Widget_Helper_Form_Element_Textarea('adh', NULL,NULL,'首页底部广告', _t('可直接填入谷歌广告，或者按这个格式填入自定义广告&lt;a href="广告链接" target="_blank"&gt;&lt;img src="广告图片" style="width: 100%;" &gt;&lt;/a&gt;&gt;'));$adh->setAttribute('class', 'col-mb-12 helpme');
$form->addInput($adh);


$addie = new Typecho_Widget_Helper_Form_Element_Textarea('addie', NULL,NULL,'针对文章关闭广告', _t('填写文章cid，多个cid之间用英文输入法下的逗号隔开'));$addie->setAttribute('class', 'col-mb-12 helpme');
$form->addInput($addie);

?>