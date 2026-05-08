<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
define("THEME_URL",str_replace('//usr','/usr',str_replace(Helper::options()->siteUrl,Helper::options()->rootUrl.'/',Helper::options()->themeUrl)));
$theurl = THEME_URL.'/';define("theurl",$theurl);
$str1 = explode('/themes/', $theurl);$str2 = explode('/', $str1[1]);define("thename",$str2[0]);
define("rooturl",Helper::options()->rootUrl.'/');
$jianrong=0;
$te=Helper::options()->version;
$tex=str_replace(".", "", $te);
$tex=str_replace("/", "", $tex);
$tex=substr($tex, 0, 3);
if($te=='1.2/18.10.23'){//伪1.2.0版本开启兼容模式
$jianrong=1;
}elseif($tex<120){//版本号低于1.2.0版本时开启兼容模式
$jianrong=1; 
}
define("jianrong",$jianrong);

if($jianrong==1){
include('lib/teold.php');
}else{
include('lib/tenew.php');
}

function themeConfig($form) {
Typecho_Widget::widget('Widget_Themes_Files')->to($files);
require_once("lib/backup.php");
require_once("lib/setting.php");
}
//自定义字段

function themeFields($layout) {
if(strpos($_SERVER['SCRIPT_NAME'], "write-post.php")){
    
    
    $okdizhi = new Typecho_Widget_Helper_Form_Element_Text('okdizhi', NULL, NULL, _t('豆瓣/Bangumi'), _t('在这里填入豆瓣或bangumi的栏目id然后点击右侧相应按钮即可获取影视信息<span class="right"><button type="button" id="douban" class="btn primary" style="margin-right: 10px;">豆瓣</button><button type="button" id="bangumi" class="btn primary">Bangumi</button></span>'));$okdizhi->setAttribute('id', 'okdizhi');
    $layout->addItem($okdizhi);

    $name = new Typecho_Widget_Helper_Form_Element_Text('name', NULL, NULL, _t('又名'), _t('填写作品其他名字或相关信息，该信息只会参与搜索不会进行前台展示，依赖soso高级版插件1.5.0版本<style>table.typecho-list-table.mono input {width: 100%;}table.typecho-list-table.mono textarea {width: 100%;height: 150px;}</style>'));
    $layout->addItem($name);


    $niandai = new Typecho_Widget_Helper_Form_Element_Text('niandai', NULL, NULL, _t('年代'), _t('填入时间'));
    $layout->addItem($niandai);

    $zhuangtai = new Typecho_Widget_Helper_Form_Element_Select('zhuangtai', array(0 => _t('完结'), 1 => _t('连载'), -1 => _t('预告')), 0, _t('状态'), _t('默认完结'));
    $layout->addItem($zhuangtai);

    $thumb = new Typecho_Widget_Helper_Form_Element_Text('thumb', NULL, NULL, _t('缩略图'), _t('图片地址'));
    $layout->addItem($thumb);

    $mp4 = new Typecho_Widget_Helper_Form_Element_Textarea('mp4', NULL, NULL, _t('视频地址'), _t('输入视频地址，格式如：第1集$第1集的视频链接 【多集视频请换行输入下一集，如果需要添加字幕请在后面追加“$字幕链接”】'));
    $layout->addItem($mp4);


    $duoji= new Typecho_Widget_Helper_Form_Element_Textarea('duoji', NULL, NULL, _t('多季'), _t('格式如：第一季$文章id'));
    $layout->addItem($duoji);

    $autoup= new Typecho_Widget_Helper_Form_Element_Text('autoup', NULL, NULL, _t('自动更新参数'), _t('格式如：123ku$123酷视频栏目id'));
    $layout->addItem($autoup);

}else{
	$icons = new Typecho_Widget_Helper_Form_Element_Text('icons',NULL, NULL, _t('图标名字'), _t('菜单导航文字左侧图标参数，网站支持图标请访问<a href="https://store.typecho.work/demo/unicons2.1.9/" target="_blank">https://store.typecho.work/demo/unicons2.1.9/</a>去除uil-就是图标的名字'));$icons->input->setAttribute('class', 'text w-100');
	$layout->addItem($icons);
}

}
//if(strpos($_SERVER['SCRIPT_NAME'], "write-post.php")):endif;
//缩略图
function showThumbnail($widget)
{ 
    $random = theurl.'img/slt/1.jpg';
    $pattern = '/\<img.*?src\=\"(.*?)\"[^>]*>/i';
    $attach = $widget->widget('Widget_Contents_Attachment_Related@' . $widget->cid . '-' . uniqid(), array(
            'parentId'  => $widget->cid,'limit'     => 1,'offset'    => 0))->attachment;
    $t=preg_match_all($pattern, $widget->content, $thumbUrl);
   $img=$random;
if($widget->fields->thumb){$img=$widget->fields->thumb;}//自定义字段设置封面
  elseif ($t && strpos($thumbUrl[1][0],'icon.png') == false && strpos($thumbUrl[1][0],'alipay') == false && strpos($thumbUrl[1][0],'wechat') == false) {$img = $thumbUrl[1][0];}//从文章中获取封面
  elseif (@$attach->isImage) {$img=$attach->url;}//从附件中获取封面
  if($img==$random){echo $img;}else{echo $img.Helper::options()->stxt;}//输出封面图
}

/**
 * 转换数字为简短形式
 * @param $n int 要转换的数字
 * @param $precision int 精度
 */
function shortenNumber($n, $precision = 0)
{
    if ($n < 1e+4) {
        $out = number_format($n);
    } else if ($n < 1e+9) {
        $out = number_format($n / 1e+4, $precision) . '万';
    } else if ($n < 1e+12) {
        $out = number_format($n / 1e+8, $precision) . '亿';
    }

    return $out;
}
//文章阅读数
function get_post_view($archive,$r=0)
{
    $cid    = $archive->cid;
    $db     = Typecho_Db::get();
    $prefix = $db->getPrefix();
    if (!array_key_exists('views', $db->fetchRow($db->select()->from('table.contents')->page(1,1)))) {
        $db->query('ALTER TABLE `' . $prefix . 'contents` ADD `views` INT(10) DEFAULT 0;');
    }
    $row = $db->fetchRow($db->select('views')->from('table.contents')->where('cid = ?', $cid));
    if ($archive->is('single')) {
 $views = Typecho_Cookie::get('extend_contents_views');
        if(empty($views)){
            $views = array();
        }else{
            $views = explode(',', $views);
        }
if(!in_array($cid,$views)){
       $db->query($db->update('table.contents')->rows(array('views' => (int) $row['views'] + 1))->where('cid = ?', $cid));
array_push($views, $cid);
            $views = implode(',', $views);
            Typecho_Cookie::set('extend_contents_views', $views); //记录查看cookie
        }
    }
if($r==0){
    echo shortenNumber($row['views']);
}
}
//头像
function tx($mail,$re=0,$id=0)
{
$a=Typecho_Widget::widget('Widget_Options')->gravatars;
$b='https://'.$a.'/';
$c=strtolower($mail);
$d=md5($c);
$f=str_replace('@qq.com','',$c);
if(strstr($c,"qq.com")&&is_numeric($f)&&strlen($f)<11&&strlen($f)>4){
$g='//q.qlogo.cn/g?b=qq&nk='.$f.'&s=100';
if($id>0){$g = Helper::options()->rootUrl.'/about.html?id='.$id.'" data-type="qqtx';}
}else{$g=$b.$d.'?d=mm';}
if($re==1){return $g;}else{echo $g;}
}


//强制设置
function themeInit($archive)
{
// 强奸用户，强制用户文章最新评论显示在文章首页
 Helper::options()->commentsPageDisplay = 'first';
// 强奸用户，将较新的评论显示在前面
 Helper::options()->commentsOrder= 'DESC';
// 强奸程序，突破评论回复楼层限制
 Helper::options()->commentsMaxNestingLevels = 999;

if($archive->parameter->pageSize==5&&!$archive->request->get('gaojijiansuo')){
$archive->parameter->pageSize = 20;
}

if ($archive->is('author')) {
$archive->parameter->pageSize = 20; // 自定义条数
}

if($archive->request->isPost() && $archive->request->likeup && $archive->request->do_action){
Typecho_Widget::widget('Widget_Security')->to($security);$security->protect();
likeup($archive->request->likeup,$archive->request->do_action);
exit;
}

if($archive->request->isPost() && $archive->request->collect){
Typecho_Widget::widget('Widget_Security')->to($security);$security->protect();
$user = Typecho_Widget::widget('Widget_User');
collect($archive->request->collect,$user->uid);
exit;
}


// 为文章或页面、post操作，且包含参数`themeAction=comment`(自定义)
if($archive->is('single') && $archive->request->isPost() && $archive->request->is('themeAction=comment')){
ajaxComment($archive);
}

}
function getSSLPage($url) {
    $ch = curl_init();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSLVERSION,30); 
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}
/**
 * ajaxComment
 * 实现Ajax评论的方法(实现feedback中的comment功能)
 * @param Widget_Archive $archive
 * @return void
 */
function ajaxComment($archive){
    $options = Helper::options();
    $user = Typecho_Widget::widget('Widget_User');
    $db = Typecho_Db::get();
    // Security 验证不通过时会直接跳转，所以需要自己进行判断
    // 需要开启反垃圾保护，此时将不验证来源
//if($archive->request->get('_') != Helper::security()->getToken($archive->request->getReferer())){
//   $archive->response->throwJson(array('status'=>0,'msg'=>_t('请求出现问题，请刷新重试！')));
//}
    /** 评论关闭 */
    if(!$archive->allow('comment')){
        $archive->response->throwJson(array('status'=>0,'msg'=>_t('评论已关闭')));
    }
    /** 检查ip评论间隔 */
    if (!$user->pass('editor', true) && $archive->authorId != $user->uid &&
    $options->commentsPostIntervalEnable){
        $latestComment = $db->fetchRow($db->select('created')->from('table.comments')
                    ->where('cid = ?', $archive->cid)
                    ->where('ip = ?', $archive->request->getIp())
                    ->order('created', Typecho_Db::SORT_DESC)
                    ->limit(1));

        if ($latestComment && ($options->gmtTime - $latestComment['created'] > 0 &&
        $options->gmtTime - $latestComment['created'] < $options->commentsPostInterval)) {
            $archive->response->throwJson(array('status'=>0,'msg'=>_t('对不起, 您的发言过于频繁, 请稍侯再次发布')));
        }        
    }

    $comment = array(
        'cid'       =>  $archive->cid,
        'created'   =>  $options->gmtTime,
        'agent'     =>  $archive->request->getAgent(),
        'ip'        =>  $archive->request->getIp(),
        'ownerId'   =>  $archive->author->uid,
        'type'      =>  'comment',
        'status'    =>  !$archive->allow('edit') && $options->commentsRequireModeration ? 'waiting' : 'approved'
    );

    /** 判断父节点 */
    if ($parentId = $archive->request->filter('int')->get('parent')) {
        if ($options->commentsThreaded && ($parent = $db->fetchRow($db->select('coid', 'cid')->from('table.comments')
        ->where('coid = ?', $parentId))) && $archive->cid == $parent['cid']) {
            $comment['parent'] = $parentId;
        } else {
            $archive->response->throwJson(array('status'=>0,'msg'=>_t('父级评论不存在')));
        }
    }
    $feedback = Typecho_Widget::widget('Widget_Feedback');
    //检验格式
    $validator = new Typecho_Validate();
    $validator->addRule('author', 'required', _t('必须填写用户名'));
    $validator->addRule('author', 'xssCheck', _t('请不要在用户名中使用特殊字符'));
    $validator->addRule('author', array($feedback, 'requireUserLogin'), _t('您所使用的用户名已经被注册,请登录后再次提交'));
    $validator->addRule('author', 'maxLength', _t('用户名最多包含200个字符'), 200);

    if ($options->commentsRequireMail && !$user->hasLogin()) {
        $validator->addRule('mail', 'required', _t('必须填写电子邮箱地址'));
    }

    $validator->addRule('mail', 'email', _t('邮箱地址不合法'));
    $validator->addRule('mail', 'maxLength', _t('电子邮箱最多包含200个字符'), 200);

    if ($options->commentsRequireUrl && !$user->hasLogin()) {
        $validator->addRule('url', 'required', _t('必须填写个人主页'));
    }
    $validator->addRule('url', 'url', _t('个人主页地址格式错误'));
    $validator->addRule('url', 'maxLength', _t('个人主页地址最多包含200个字符'), 200);

    $validator->addRule('text', 'required', _t('必须填写评论内容'));

    $comment['text'] = $archive->request->text;

    /** 对一般匿名访问者,将用户数据保存一个月 */
    if (!$user->hasLogin()) {
        /** Anti-XSS */
        $comment['author'] = $archive->request->filter('trim')->author;
        $comment['mail'] = $archive->request->filter('trim')->mail;
        $comment['url'] = $archive->request->filter('trim')->url;

        /** 修正用户提交的url */
        if (!empty($comment['url'])) {
            $urlParams = parse_url($comment['url']);
            if (!isset($urlParams['scheme'])) {
                $comment['url'] = 'http://' . $comment['url'];
            }
        }

        $expire = $options->gmtTime + $options->timezone + 30*24*3600;
        Typecho_Cookie::set('__typecho_remember_author', $comment['author'], $expire);
        Typecho_Cookie::set('__typecho_remember_mail', $comment['mail'], $expire);
        Typecho_Cookie::set('__typecho_remember_url', $comment['url'], $expire);
    } else {
        $comment['author'] = $user->screenName;
        $comment['mail'] = $user->mail;
        $comment['url'] = $user->url;

        /** 记录登录用户的id */
        $comment['authorId'] = $user->uid;
    }



    /** 评论者之前须有评论通过了审核 */
    if (!$options->commentsRequireModeration && $options->commentsWhitelist) {
        if ($feedback->size($feedback->select()->where('author = ? AND mail = ? AND status = ?', $comment['author'], $comment['mail'], 'approved'))) {
            $comment['status'] = 'approved';
        } else {
            $comment['status'] = 'waiting';
        }
    }

    if ($error = $validator->run($comment)) {
        $archive->response->throwJson(array('status'=>0,'msg'=> implode(';',$error)));
    }


if($archive->hidden){
        $archive->response->throwJson(array('status'=>0,'msg'=>_t('加密文章！输入正确密码后方可进行评论！')));
}

          /** 生成过滤器 */
        try {
            $comment = $feedback->pluginHandle()->comment($comment, $feedback->_content);
        } catch (Typecho_Exception $e) {
            Typecho_Cookie::set('__typecho_remember_text', $comment['text']);
          $archive->response->throwJson(array('status'=>0,'msg'=>_t($e->getMessage())));
            throw $e;
        }

 $status="";
if ('waiting' == $comment['status']) { $status='您的评论需管理员审核后才能显示！';} 

    /** 添加评论 */
    $commentId = $feedback->insert($comment);
    Typecho_Cookie::delete('__typecho_remember_text');
    $db->fetchRow($feedback->select()->where('coid = ?', $commentId)
    ->limit(1), array($feedback, 'push'));
$feedback->pluginHandle()->finishComment($feedback);


if($user->uid>0){if($user->uid == $archive->authorId){
 $sf='<svg viewBox="0 0 24 24" class="ml-1" width="14" height="14"><svg viewBox="0 0 24 24" x="-3" y="-3" fill="#FFFFFF" width="30" height="30"><path d="M3.56231227,13.8535307 C2.40051305,12.768677 2.41398885,11.0669203 3.59484487,9.99979213 L3.59222085,9.99654885 C4.26730143,9.45036719 4.79446755,8.21005186 4.7184197,7.34453784 L4.72305873,7.34412719 C4.66942824,5.75539997 5.8824188,4.56066914 7.47188965,4.64242381 L7.47229112,4.6386236 C8.33515314,4.72977993 9.58467253,4.22534048 10.1426329,3.55925173 L10.1462611,3.56228565 C11.2316055,2.40008701 12.9353108,2.41394456 14.0015072,3.59634088 L14.0047263,3.59374004 C14.5498229,4.26841874 15.7896857,4.79521622 16.6545744,4.71844347 L16.6549836,4.72304294 C18.245027,4.66894057 19.4396947,5.88213996 19.3575031,7.47241135 L19.3623099,7.47292747 C19.2704388,8.3358681 19.7742711,9.58421483 20.4407199,10.1424506 L20.437686,10.1460789 C21.5997217,11.2312209 21.5860695,12.9345218 20.4042441,14.0007396 L20.4072865,14.0045125 C19.7325967,14.5495925 19.2055209,15.7896954 19.2815865,16.6561959 L19.2770449,16.6565978 C19.3315454,18.2453037 18.1173775,19.4393568 16.5274188,19.3571512 L16.5269029,19.3619539 C15.6647098,19.270083 14.415408,19.7741709 13.8573671,20.4403558 L13.8537409,20.4373235 C12.76842,21.5995708 11.0650432,21.5864553 9.99899434,20.4039226 L9.99527367,20.406923 C9.45025436,19.7323399 8.21017638,19.2051872 7.34461983,19.2812352 L7.344304,19.2776405 C5.75448683,19.3312904 4.55977145,18.1170085 4.64254978,16.527117 L4.63769921,16.5265942 C4.72957031,15.6644394 4.22547659,14.4151814 3.55928015,13.8571569 L3.56231227,13.8535307 Z"></path></svg><path d="M2.63951518,13.3895441 C3.70763333,14.2842292 4.44777637,16.1226061 4.30075305,17.5023312 L4.32211542,17.3063047 C4.17509209,18.6910561 5.17786655,19.7063729 6.5613937,19.5844846 L6.364106,19.6008202 C7.75140298,19.4789319 9.57474349,20.2554985 10.4468305,21.3349009 L10.3224262,21.1803415 C11.1982831,22.2647703 12.6257916,22.2723098 13.5167278,21.2079863 L13.3898102,21.3600325 C14.2845162,20.2919393 16.1229361,19.5518136 17.5026934,19.6988334 L17.3054057,19.6774716 C18.6914461,19.8244915 19.7067866,18.8217404 19.5836389,17.4395022 L19.6012314,17.6367853 C19.4793403,16.2482641 20.255925,14.4249662 21.3353526,13.5528995 L21.1807897,13.677301 C22.2639871,12.8014646 22.2727834,11.3739894 21.2084351,10.483074 L21.3604848,10.6099886 C20.2923667,9.71530351 19.5522236,7.87818322 19.6992469,6.49720154 L19.6778846,6.69448464 C19.8249079,5.30847665 18.8221335,4.2944164 17.4386063,4.41630468 L17.635894,4.39871256 C16.248597,4.52185742 14.4252565,3.74529084 13.5531695,2.66588842 L13.6775738,2.81919121 C12.8017169,1.73601905 11.3742084,1.72722299 10.4832722,2.79154644 L10.6101898,2.63950024 C9.71548377,3.70759343 7.87706394,4.44771919 6.49730661,4.30195588 L6.69459432,4.32206116 C5.30855394,4.17504128 4.29447,5.17904888 4.41636114,6.56128713 L4.3987686,6.36400404 C4.52065973,7.75126861 3.74407501,9.57456653 2.66464737,10.4478898 L2.81921035,10.3222318 C1.73601288,11.1993248 1.72721662,12.6255433 2.79156494,13.5164587 L2.63951518,13.3895441 Z" fill="#0066FF"></path><svg class="Zi Zi--Check" fill="#fff" x="6" y="6" viewBox="0 0 24 24" width="12" height="12"><path d="M10.229 17.516c-.318.327-.75.484-1.199.484-.453 0-.884-.16-1.202-.488l-4.335-4.47a1.77 1.77 0 0 1 .007-2.459 1.663 1.663 0 0 1 2.397.01l3.137 3.246 9.072-9.329a1.662 1.662 0 0 1 2.397 0c.663.681.663 1.786 0 2.466L10.23 17.516z" fill-rule="evenodd"></path></svg></svg>';
}else{$sf="";}}else{$sf="";}

    // 返回评论数据
    $data = array(
        'cid' => $feedback->cid,
        'coid' => $feedback->coid,
        'parent' => $feedback->parent,
        'mail' => $feedback->mail,
        'url' => $feedback->url,
        'ip' => $feedback->ip,
        'agent' => $feedback->agent,
        'author' => $feedback->author,
        'authorId' => $feedback->authorId,
        'permalink' => $feedback->permalink,
        'created' => timesince($feedback->created),
        'datetime' => $feedback->date->format('Y-m-d H:i:s'),
        'status' => $status,
        'sf' => $sf,
    );
    // 评论内容
    ob_start();
    $feedback->content();
  
    $data['content'] = ob_get_clean();
    $data['content']=parseBiaoQing($data['content']);
    $data['avatar'] = tx($data['mail'],1,0);
    $archive->response->throwJson(array('status'=>1,'comment'=>$data));
}


//同分类随机文章
class Widget_Post_tongleisuiji extends Widget_Abstract_Contents
{
    public function __construct($request, $response, $params = NULL)
    {
        parent::__construct($request, $response, $params);
        $this->parameter->setDefault(array('pageSize' => $this->options->commentsListSize, 'parentId' => 0, 'ignoreAuthor' => false));
    }
    public function execute()
    {
$select  = $this->select()->from('table.contents')
->join('table.relationships', 'table.contents.cid = table.relationships.cid');
if($this->parameter->mid>0){
$select->where('table.relationships.mid = ?', $this->parameter->mid);
}

$select->where('table.contents.cid <> ?', $this->parameter->cid)
->where("table.contents.password IS NULL OR table.contents.password = ''")
->where('table.contents.type = ?', 'post')
->limit($this->parameter->pageSize)
->order('RAND()');
$this->db->fetchAll($select, array($this, 'push'));
    }
}

//获取文章内容图
function getPostHtmImg($obj,$num=0) {
	preg_match_all( "/<[img|IMG].*?src=[\'|\"](.*?)[\'|\"].*?alt=[\'|\"](.*?)[\'|\"].*?[\/]?>/", $obj->content, $matches);
	$atts = array();
	if(isset($matches[1][0])) {
		for($i = 0; $i < count($matches[1]); $i++) {
			$atts[] = array('name' => $obj->title.' ['.($i + 1).']', 'url' => $matches[1][$i],'title' => $matches[2][$i]);
		}
    }
if($num==0){
return  count($atts) ? $atts : NULL;
}else{
return  count($atts);
}
}


function parsePaopaoBiaoqingCallback($match)
    {
        return '<img class="biaoqing" src="'.theurl.'/assets/owo/paopao/'. str_replace('%', '', urlencode($match[1])) . '_2x.png">';
    }
function parseAruBiaoqingCallback($match)
    {
        return '<img class="biaoqing" src="'.theurl.'/assets/owo/aru/'. str_replace('%', '', urlencode($match[1])) . '_2x.png">';
    }
function parseBiaoQing($content)
    {
        $content = preg_replace_callback('/\:\:\(\s*(呵呵|哈哈|吐舌|太开心|笑眼|花心|小乖|乖|捂嘴笑|滑稽|你懂的|不高兴|怒|汗|黑线|泪|真棒|喷|惊哭|阴险|鄙视|酷|啊|狂汗|what|疑问|酸爽|呀咩爹|委屈|惊讶|睡觉|笑尿|挖鼻|吐|犀利|小红脸|懒得理|勉强|爱心|心碎|玫瑰|礼物|彩虹|太阳|星星月亮|钱币|茶杯|蛋糕|大拇指|胜利|haha|OK|沙发|手纸|香蕉|便便|药丸|红领巾|蜡烛|音乐|灯泡|开心|钱|咦|呼|冷|生气|弱|吐血)\s*\)/is',
'parsePaopaoBiaoqingCallback', $content);
        $content = preg_replace_callback('/\:\@\(\s*(高兴|小怒|脸红|内伤|装大款|赞一个|害羞|汗|吐血倒地|深思|不高兴|无语|亲亲|口水|尴尬|中指|想一想|哭泣|便便|献花|皱眉|傻笑|狂汗|吐|喷水|看不见|鼓掌|阴暗|长草|献黄瓜|邪恶|期待|得意|吐舌|喷血|无所谓|观察|暗地观察|肿包|中枪|大囧|呲牙|抠鼻|不说话|咽气|欢呼|锁眉|蜡烛|坐等|击掌|惊喜|喜极而泣|抽烟|不出所料|愤怒|无奈|黑线|投降|看热闹|扇耳光|小眼睛|中刀)\s*\)/is',
'parseAruBiaoqingCallback', $content);
        return $content;
    }


function get_comment_at($coid)
{
    $db   = Typecho_Db::get();
    $prow = $db->fetchRow($db->select('parent')->from('table.comments')
                                 ->where('coid = ?', $coid));
    $parent = $prow['parent'];
    if ($parent != "0") {
        $arow = $db->fetchRow($db->select('author')->from('table.comments')
                                     ->where('coid = ? AND status = ?', $parent, 'approved'));
if($arow['author']){ $author = $arow['author'];
        $href   = '<a href="#comment-' . $parent . '">@' . $author . '</a>';
        echo $href;
}else { echo '';}
    } else {
        echo '';
    }
}


function timesince($older_date,$comment_date = false) {
if($older_date=="no"){return;}
$chunks = array(
array(86400 , ' 天'),
array(3600 , ' 小时'),
array(60 , ' 分'),
array(1 , ' 秒'),
);
$newer_date = time();
$since = abs($newer_date - $older_date);

for ($i = 0, $j = count($chunks); $i < $j; $i++){
$seconds = $chunks[$i][0];
$name = $chunks[$i][1];
if (($count = floor($since / $seconds)) != 0) break;
}
$output = $count.$name.'前';

return $output;
}

function userok($id){
$db = Typecho_Db::get();
$userinfo=$db->fetchRow($db->select()->from ('table.users')->where ('table.users.uid=?',$id));
return $userinfo;
}

function collectzu($uid,$k=0){
$db = Typecho_Db::get(); 
$prefix = $db->getPrefix();
if (!array_key_exists('collect', $db->fetchRow($db->select()->from('table.users')->page(1,1)))) {
        $db->query('ALTER TABLE `' . $prefix . 'users` ADD `collect` TEXT DEFAULT NULL;');
    }
$shuzu = $db->fetchRow($db->select('collect')->from('table.users')->where('uid = ?', $uid))['collect'];
$shuzu=ltrim($shuzu, ",");
$shuzu=rtrim($shuzu, ",");

if(empty($shuzu)){$sc=0;}else{$sc=count(explode(",",$shuzu));}
if($k!=0){
echo $sc;
}
if($sc==0){return '-1';}else{
return $shuzu;}


}



function collect($ccid,$uid,$k=0) {
if($uid>0){
 $cid = $ccid;
 $db = Typecho_Db::get();
 $prefix = $db->getPrefix();
if (!array_key_exists('collect', $db->fetchRow($db->select()->from('table.users')->page(1,1)))) {
        $db->query('ALTER TABLE `' . $prefix . 'users` ADD `collect` TEXT DEFAULT NULL;');
    }
$row = $db->fetchRow($db->select('collect')->from('table.users')->where('uid = ?', $uid));
    


$sc=@explode(",",$row['collect']);

//print_r($sc);echo count($sc);
if(count($sc)<2){$pcid=','.$cid.',';}else{$pcid=$cid.',';}

if(count($sc)>=100){echo '收藏已达上限';$k=1;}

if(in_array($cid,$sc)){
//$pcid=','.$cid.',';$a=str_replace($pcid,",",$row['collect']);
//echo $a;
if($k==0){

if(count($sc)<4){
$db->query($db->update('table.users')->rows(array('collect' => NULL))->where('uid = ?', $uid));
    
}else{
$pcid=','.$cid.',';$pcid=str_replace($pcid,",",$row['collect']);

$db->query($db->update('table.users')->rows(array('collect' => $pcid))->where('uid = ?', $uid));
}echo 'ko';}
    
return 'ko';
}else{
if($k==0){
$db->query($db->update('table.users')->rows(array('collect' => $row['collect'].$pcid))->where('uid = ?', $uid));
echo 'ok';
}
return 'ok';


}
}else{if($k==0){echo '未登录账号不能进行收藏';}}

}



function likeup($ccid,$kg) {
 $cid = $ccid;
 $db = Typecho_Db::get();
    $prefix = $db->getPrefix();
    if (!array_key_exists('likes', $db->fetchRow($db->select()->from('table.contents')->page(1,1)))) {
        $db->query('ALTER TABLE `' . $prefix . 'contents` ADD `likes` INT(10) DEFAULT 0;');
echo '0';
        return;
    }
 $row = $db->fetchRow($db->select('likes')->from('table.contents')->where('cid = ?', $cid));
$num=$row['likes'];

if($kg=="do"){
 $db->query($db->update('table.contents')->rows(array('likes' => (int)$row['likes']+1))->where('cid = ?', $cid));
$num=$num+1;
}
if($kg=="undo"&&$num>0){
 $db->query($db->update('table.contents')->rows(array('likes' => (int)$row['likes']-1))->where('cid = ?', $cid));
$num=$num-1;
}


echo $num;
}

function fanbiao($new)
    {

$num=0;
$numx=$num;
$string_arr=[];
$spurl=$new->fields->mp4;
if(isset($spurl)&&strpos($spurl,'$') == false){$spurl='全集$'.$spurl;}

if(isset($spurl)){
$string_arr = array_filter(explode("\r\n", $spurl));
$num=count($string_arr);//视频参数段落数，可作为粗略的集数进行显示
$numx=$num;//全集时的集数显示
$jiend =array_filter(explode('$', $string_arr[$num-1]))[0];//获取视频参数最后一段的集数文字信息
preg_match_all("/[a-zA-Z0-9]+/", $jiend,$x); $end=join("", $x[0]);//提取集数信息中的字母与数字
if(!empty($end)){
if (preg_match('/[a-zA-Z]/',$end)){$num=$end;}else{$num=intval($end);}
    }//如果提取到了就将它作为最新集数进行显示

}


if($new->fields->zhuangtai>0){

echo '更新至'.$num.'集';}
elseif($new->fields->zhuangtai==-1){echo '预告';}
elseif(isset($spurl)&&strlen($spurl) < 10){
$new->category(',',false);
}else{
echo $numx.'集全';
}

} 


class Widget_Post_fanjubiao extends Widget_Abstract_Contents
{
    public function __construct($request, $response, $params = NULL)
    {
        parent::__construct($request, $response, $params);
        $this->parameter->setDefault(array('pageSize' => '999', 'parentId' => 0, 'ignoreAuthor' => false));
    }
    public function execute()
    {
        $select  = $this->select()->from('table.contents')
->where("table.contents.password IS NULL OR table.contents.password = ''")
->where('table.contents.type = ?', 'post')
->limit($this->parameter->pageSize)
->order('table.contents.modified', Typecho_Db::SORT_DESC);

if ($this->parameter->fanjubiao) {
$fanju=explode(",",$this->parameter->fanjubiao);
$select->where('table.contents.cid in ?', $fanju);
}
 $this->db->fetchAll($select, array($this, 'push'));
    }
}



class Widget_Post_cat extends Widget_Abstract_Metas
{
    public function __construct($request, $response, $params = NULL)
    {
        parent::__construct($request, $response, $params);
        $this->parameter->setDefault(array('pageSize' => $this->options->commentsListSize, 'parentId' => 0, 'ignoreAuthor' => false));
    }
    public function execute()
    {
$db= Typecho_Db::get();
$prefix = $db->getPrefix();
$select  = $this->select()->from($prefix.'metas')
->where('table.metas.parent = ?',$this->parameter->mid)//从所有分类中找到爸爸是这个mid的
->where('table.metas.type = ?','category')
->order('table.metas.order', Typecho_Db::SORT_ASC);
$this->db->fetchAll($select, array($this, 'push'));
    }
}

Typecho_Plugin::factory('admin/write-post.php')->bottom = array('plgl', 'san');
Typecho_Plugin::factory('Widget_Archive')->query = array('plgl', 'query');
Typecho_Plugin::factory('admin/footer.php')->end = array('plgl', 'mbupdate');

class plgl {
    public static function query($widget, $select)
    {

        if (isset($widget->parameter->orderBy)) {
            $select->order('table.contents.'.$widget->parameter->orderBy, Typecho_Db::SORT_DESC);
        }
        Typecho_Db::get()->fetchAll($select, array($widget, 'push'));
	}

   public static function mbupdate(){
?>
<script>$(document).ready(function(){
if($("#theme-yingshierhao").length>0){
var ystwonum=$("#theme-yingshierhao cite").text();
var ystwoupurl=$("#theme-yingshierhao cite a").attr("href");
ystwonum= ystwonum.replace(/[^0-9]/ig,"");
$.ajax({
           type:"get",//必须是get请求
           url: "https://store.typecho.work/sq",
           data: {site: 'update',name:'yingshierhao' },
           success: function (response) {
var ystwonewver=response;
var ystwonnum= ystwonewver.replace(/[^0-9]/ig,"");
if(ystwonnum>ystwonum){
$("#theme-yingshierhao cite").append('<span style="background: #ff3f3f;display: inherit;padding: 3px 5px;border-radius: 3px;"><a href="'+ystwoupurl+'" target="_blank" rel="noopener noreferrer" style="color:#fff;text-decoration: none;">发现新版本'+ystwonewver+'，点我前去更新</a></span>');
}
}
});
}
});
</script>
<?php
}

   public static function san()
    {
?>
<script> 
function getValue(str, key){
  let result = new RegExp(`(?:^|,)${key}:([^,]*)`).exec(str);
  return result && result[1]
}
function ysinfo(site) {
var _ok = $("input[name='fields\[okdizhi\]']").val();
  if (_ok != '') {
var k='https://ptgen.rhilip.info';
<?php if(Helper::options()->douban): ?>
k='<?php Helper::options()->douban(); ?>';
<?php endif; ?>      
 k=k+"/?site="+site+"&sid="+_ok;
$.get(k,function(result){
var json=result;

if(site=="douban"){


$('#title').val(json.chinese_title);
$('#text').val(json.introduction);
$('input[name="fields[name]"]').val(json.trans_title.join(","));
$('input[name="fields[niandai]"]').val(json.year.replace(/\s*/g,""));
$('input[name="fields[thumb]"]').val(json.poster);
arr=json.tags.join(",").split(',');
for(var i=0;i<arr.length;i++){
    $('#tags').tokenInput('add', {id: arr[i], tags: arr[i]});
}

}else{
var info=json.info.toString();
var name=getValue(info,'中文名');
var youname=getValue(info,'别名');
var nian=getValue(info,'放送开始');

if(name!=''&&name!=null){
name=name.replace(/\s*/g,"");}
if(youname!=''&&youname!=null){
youname=youname.replace(/\s*/g,"");}
if(nian!=''&&nian!=null){
nian=nian.replace(/\s*/g,"");
if(nian.search("年")!=-1){nian=nian.match(/(\S*)年/)[1];}
}

$('#title').val(name);
$('#text').val(json.story);
$('input[name="fields[name]"]').val(youname);
$('input[name="fields[niandai]"]').val(nian);
$('input[name="fields[thumb]"]').val(json.poster);
arr=json.tags.join(",").split(',');
for(var i=0;i<arr.length;i++){
    $('#tags').tokenInput('add', {id: arr[i], tags: arr[i]});
}



}









                        });
  }
  return false;
}


$(document).ready(function(){
  $("#douban").click(function(){
ysinfo("douban");
  });
  $("#bangumi").click(function(){
ysinfo("bangumi");
  });



});

</script> 
<?php
}
    
   
   
    
}