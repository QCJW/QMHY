(function(a){
    a.fn.scrollLoading=function(b){
        var c={attr:"data-xurl",type:"data-type",container:a(window),callback:a.noop};
        var d=a.extend({},c,b||{});d.cache=[];
        a(this).each(function(){
            var h=this.nodeName.toLowerCase(),g=a(this).attr(d.attr),t=a(this).attr(d.type);
            var i={obj:a(this),tag:h,type:t,url:g};d.cache.push(i)});
        var f=function(g){
            if(a.isFunction(d.callback)){d.callback.call(g.get(0))}
        };
        var e=function(){
            var g=d.container.height();
            if(d.container.get(0)===window){contop=a(window).scrollTop()
                                           }else{
                                               contop=d.container.offset().top}a.each(d.cache,function(m,n){
      var p=n.obj,j=n.tag,k=n.url,t=n.type,l,h;
      if(p){l=p.offset().top-contop,h=l+p.height();if((l>=0&&l<g)||(h>0&&h<=g)){if(k){
    if(j==="img"){
    if(t=="qqtx"){$.get(k,function(result){var k=jQuery.parseJSON(result).url;f(p.attr("src",k))})
}
    else{var oI = new Image();oI.src = k;oI.onload = function() {f(p.attr("src",oI.src));};}}
else{          
    if (j === "a"||j === "div") {
var temp = new Image();
temp.src =k;//只会请求一次
// onload判断图片加载完毕，真是图片加载完毕，再赋值给dom节点
temp.onload = function(){
//图片，改变src
p.css("background-image","url("+k+")");
f(p);
}


}
else{p.load(k,{},function(){f(p)})}
}
}else{f(p)}n.obj=null}}})};e();d.container.bind("scroll",e)}})(jQuery);
/* ---------------------------------------------- /*
	* POPUP 
/* ---------------------------------------------- */
if (typeof jQuery != 'undefined') {
	var $ = jQuery.noConflict();
}

function HistoryClear() {
document.cookie = "history=;path=/";
$(".history").html("<div>已清空观看记录</div>");
}

function SwitchDarkMode(e){ e.preventDefault();
var storage = window.localStorage;
    $('<div class="ze_DarkSky"><div class="ze_DarkPlanet"><svg class="yueliang" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0" y="0" viewBox="0 0 1024 1024"><path d="M483.555556 964.266667c-164.977778 0-315.733333-85.333333-398.222223-224.711111 19.911111 2.844444 39.822222 2.844444 56.888889 2.844444 275.911111 0 500.622222-224.711111 500.622222-500.622222 0-68.266667-14.222222-133.688889-39.822222-193.422222 201.955556 54.044444 347.022222 238.933333 347.022222 449.422222 0 256-210.488889 466.488889-466.488888 466.488889z" fill="#FFE35E"></path><path d="M631.466667 73.955556c179.2 62.577778 301.511111 230.4 301.511111 423.822222 0 247.466667-201.955556 449.422222-449.422222 449.422222-147.911111 0-281.6-71.111111-364.088889-187.733333H142.222222c284.444444 0 517.688889-233.244444 517.688889-517.688889 0-56.888889-8.533333-113.777778-28.444444-167.822222M571.733333 22.755556C605.866667 88.177778 625.777778 162.133333 625.777778 241.777778c0 267.377778-216.177778 483.555556-483.555556 483.555555-31.288889 0-59.733333-2.844444-88.177778-8.533333 79.644444 156.444444 241.777778 264.533333 429.511112 264.533333 267.377778 0 483.555556-216.177778 483.555555-483.555555C967.111111 261.688889 796.444444 65.422222 571.733333 22.755556z" fill="#303133"></path><path d="M787.911111 455.111111c-5.688889-2.844444-8.533333-8.533333-5.688889-14.222222 5.688889-17.066667-2.844444-42.666667-19.911111-48.355556-17.066667-5.688889-39.822222 8.533333-45.511111 22.755556-2.844444 5.688889-8.533333 8.533333-14.222222 5.688889-5.688889-2.844444-8.533333-8.533333-5.688889-14.222222 8.533333-25.6 42.666667-45.511111 73.955555-34.133334 28.444444 11.377778 39.822222 48.355556 31.288889 73.955556-2.844444 5.688889-8.533333 8.533333-14.222222 8.533333" fill="#303133"></path><path d="M608.711111 620.088889c-14.222222 0-28.444444-2.844444-39.822222-11.377778-31.288889-22.755556-31.288889-65.422222-31.288889-68.266667 0-8.533333 8.533333-17.066667 17.066667-17.066666s17.066667 8.533333 17.066666 17.066666 2.844444 31.288889 17.066667 39.822223c11.377778 8.533333 25.6 8.533333 45.511111 0 8.533333-2.844444 19.911111 2.844444 22.755556 11.377777 2.844444 8.533333-2.844444 19.911111-11.377778 22.755556-14.222222 2.844444-25.6 5.688889-36.977778 5.688889zM571.733333 540.444444z" fill="#303133"></path><path d="M810.666667 588.8c-5.688889 19.911111-36.977778 28.444444-68.266667 19.911111-31.288889-8.533333-54.044444-34.133333-48.355556-54.044444 5.688889-19.911111 36.977778-28.444444 68.266667-19.911111 34.133333 11.377778 54.044444 34.133333 48.355556 54.044444" fill="#FFA450"></path><path d="M864.711111 270.222222c14.222222 42.666667 19.911111 91.022222 19.911111 136.533334 0 258.844444-213.333333 466.488889-477.866666 466.488888-96.711111 0-187.733333-28.444444-264.533334-76.8 82.488889 93.866667 204.8 156.444444 344.177778 156.444445C736.711111 952.888889 938.666667 756.622222 938.666667 512c0-88.177778-28.444444-173.511111-73.955556-241.777778z" fill="#FFC93F"></path></svg><svg class="taiyang" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0" y="0" viewBox="0 0 1118 1024"><path d="M228.57614222 516.12330667C228.57614222 714.42659555 389.63996445 875.09105778 588.20949333 875.09105778c198.56952889 0 359.63335111-160.79758222 359.63335112-358.96888889S786.77902222 157.15441778 588.20949333 157.15441778c-198.70264889 0-359.63335111 160.66446222-359.63335111 358.96888889Zm0 0" fill="#f9d706"></path><path d="M346.68088889 251.31918222c103.47440355-94.03141689 252.30222222-119.43480889 381.17944889-65.03719822C856.73756445 240.67868445 942.25635555 365.03438222 946.91214222 504.81834667s-72.21930667 269.59189333-197.10634667 332.63388444c132.86741333-120.76487111 156.14293333-321.19580445 54.39715556-469.09326222-101.74543645-147.89632-297.25582222-197.90506667-457.52206222-117.04092444Zm0 0" fill="#febd34"></path><path d="M393.23079111 215.54176c112.51848533-73.28324267 256.55751111-77.67233422 373.33219556-11.30503965 116.77468445 66.36726045 186.73322667 192.31857778 181.27985778 326.64917334C942.38947555 665.08344889 862.58915555 785.0496 740.76046222 841.84177778c157.87121778-102.94237867 209.34314667-310.29020445 117.83850667-475.07797333-91.37129245-164.78776889-294.59569778-230.75612445-465.36931556-151.22204445Zm0 0" fill="#fa9f45"></path><path d="M418.76707555 471.43480889c0 9.5760384 5.05402027 18.48706845 13.43305956 23.27506489 8.24603307 4.7880192 18.48706845 4.7880192 26.86611911 0 8.24603307-4.7880192 13.43305955-13.699072 13.43305956-23.27506489 0-9.5760384-5.05402027-18.48706845-13.43305956-23.27506489-8.24603307-4.7880192-18.48706845-4.7880192-26.86611911 0-8.24603307 4.7880192-13.43305955 13.699072-13.43305956 23.27506489Zm0 0M639.41404445 471.43480889c0 9.5760384 5.05402027 18.48706845 13.43305955 23.27506489 8.24603307 4.7880192 18.48706845 4.7880192 26.86611911 0 8.24603307-4.7880192 13.43305955-13.699072 13.43305956-23.27506489 0-9.5760384-5.05402027-18.48706845-13.43305956-23.27506489-8.24603307-4.7880192-18.48706845-4.7880192-26.86611911 0-8.24603307 4.7880192-13.43305955 13.699072-13.43305955 23.27506489Zm0 0" fill=""></path><path d="M495.77415111 577.56899555h137.12384c0 37.90518045-30.723072 68.62825245-68.62825244 68.62825245-37.77217422 0-68.49524622-30.723072-68.49524622-68.62825245Zm0 0" fill="#f48373"></path><path d="M564.26951111 655.10855111c-43.09219555 0-78.603264-33.91510755-80.46535111-76.87429689-0.13300053-3.32500765 1.06400427-6.51702045 3.32500765-8.7780352 2.26101475-2.3940096 5.32002133-3.72401493 8.64502897-3.72401493h137.12384c3.32500765 0 6.3840256 1.33000533 8.64502898 3.72401493 2.26101475 2.3940096 3.45801387 5.5860224 3.32500765 8.7780352-1.99501369 42.95918933-37.50616178 76.87429689-80.59835734 76.87429689Zm-54.79617422-65.56922311c6.3840256 25.00414578 28.99410489 42.56017067 54.92918044 42.56017067 25.80206933 0 48.41221689-17.55602489 54.79617422-42.56017067h-109.72546844Zm0 0" fill=""></path><path d="M705.64864 582.88924445c0 11.03903858 5.85202347 21.28008533 15.56104533 26.8661191 9.5760384 5.5860224 21.41309155 5.5860224 30.98908445 0 9.5760384-5.5860224 15.56104533-15.82705778 15.56104533-26.8661191 0-11.03903858-5.85202347-21.28008533-15.56104533-26.86611912-9.5760384-5.5860224-21.41309155-5.5860224-30.98908445 0-9.5760384 5.5860224-15.56104533 15.82705778-15.56104533 26.86611912Zm0 0" fill="#f79121"></path><path d="M561.14289778 914.45930667c-211.60391111 0-383.17397333-171.30496-383.44021333-382.90887112-0.13300053-55.46120533 11.83709867-110.12437333 35.24516977-160.39822222 5.5860224-11.96999111 19.81713067-17.29001245 31.920128-11.57108622 11.96999111 5.5860224 17.29001245 19.81713067 11.57108623 31.920128-20.482048 43.89011911-30.98908445 91.77031111-30.98908445 140.18218667 0.26600107 136.8576 83.65727289 259.88323555 210.67320889 310.95466667 127.01582222 50.93922133 272.38513778 19.81713067 367.21436444-79.00228267 9.17703111-8.91103005 23.80708978-9.04403627 32.98417778-0.13300053 9.31003733 8.7780352 9.97503431 23.40807111 1.5960064 32.98417777-72.08630045 75.67724089-172.23566222 118.37098667-276.77354666 117.97162667ZM863.45386667 748.74083555c-8.7780352 0.13300053-17.02411378-4.65501298-21.28008534-12.36900977-4.25601707-7.71403093-3.85700978-17.15712 0.93100374-24.60512711 15.29503289-23.80708978 27.53115022-49.47615289 36.442112-76.342272 4.12301085-12.502016 17.55602489-19.28510578 30.05815466-15.16202667 12.502016 4.12301085 19.28510578 17.55602489 15.16202667 30.05815467-9.97503431 30.59006578-23.80708978 59.85018311-41.09710222 87.11532088-4.25601707 7.04902258-11.83709867 11.30503965-20.21603556 11.30503965Zm56.65826133-193.78176c-13.16704711 0-23.80708978-10.64004267-23.80708978-23.80708977-0.66500267-184.73756445-150.42332445-334.23018667-335.16088889-334.49642667l-32.18614044 1.72900124c-8.51203413 0.93100373-16.89110755-2.79300551-21.94511644-9.84203946-5.05402027-6.91602773-5.98501831-16.09307022-2.3940096-23.940096s11.03903858-13.16704711 19.55111822-13.96508444l36.97413688-1.72900125c211.60391111 0 383.17397333 171.30496 383.44021334 382.90887111 0 6.3840256-2.66001067 12.63502222-7.1820288 17.02411378-4.65501298 4.52201813-10.77303751 6.91602773-17.29001244 6.78302151v-0.66500267ZM286.89635555 322.87288889c-9.31003733-0.13300053-17.68903111-5.5860224-21.54609777-13.96508444-3.85700978-8.37902791-2.3940096-18.35406222 3.59100871-25.40305067 20.34904178-24.07310222 43.62422045-45.48619378 69.16027733-63.840256 10.77303751-7.71403093 25.66906311-5.32002133 33.38308267 5.32002133 7.71403093 10.77303751 5.32002133 25.66906311-5.32002134 33.38308267-22.47714133 16.09307022-42.95918933 34.97915733-60.78122666 55.99323022-4.65501298 5.5860224-11.43808 8.64502898-18.48706844 8.51203413Zm128.74410667-95.89339022c-11.43808 0.26600107-21.54609778-7.71403093-23.80708977-19.01909334s3.72401493-22.61003378 14.3641031-26.86611911l32.85117156-11.96999111c12.502016-4.25601707 26.06808178 2.3940096 30.45705956 14.89601422 4.25601707 12.502016-2.3940096 26.06808178-14.89601422 30.45705956l-29.26011734 10.77303751c-3.1920128 1.06400427-6.3840256 1.72900125-9.70903324 1.72900125Zm0 0" fill="#fb7419"></path><path d="M310.90346667 582.88924445c0 11.03903858 5.85202347 21.28008533 15.56104533 26.8661191 9.5760384 5.5860224 21.41309155 5.5860224 30.98908445 0 9.5760384-5.5860224 15.56104533-15.82705778 15.56104533-26.8661191 0-11.03903858-5.85202347-21.28008533-15.56104533-26.86611912-9.5760384-5.5860224-21.41309155-5.5860224-30.98908445 0-9.70903325 5.5860224-15.56104533 15.82705778-15.56104533 26.86611912Zm0 0" fill="#f79121"></path><path d="M611.48387555 1067.80444445c-8.7780352 0.13300053-16.35908267-6.25101938-17.95504355-14.89601423l-9.5760384-53.73223822c-0.93100373-9.31003733 5.5860224-17.82203733 14.763008-19.28510578 9.17703111-1.5960064 18.08804978 4.25601707 20.34904178 13.43305956l9.5760384 53.73223822c0.93100373 4.65501298-0.13300053 9.44303218-2.79300551 13.43305955-2.66001067 3.85700978-6.78302151 6.65002667-11.43808 7.44802987h-2.92601174Zm-356.04252444-88.844288c-6.3840256 0-12.23600355-3.45801387-15.56104533-8.91103005-3.1920128-5.5860224-3.1920128-12.36900978 0-17.95504355L267.27879111 904.35128889c3.1920128-5.5860224 9.04403627-9.17703111 15.56104534-9.17703111 6.51702045 0 12.502016 3.45801387 15.69405155 9.04403627 3.1920128 5.5860224 3.1920128 12.502016-0.13300053 18.08804977l-27.398144 47.74718578c-3.32500765 5.45301618-9.17703111 8.91103005-15.56104534 8.91103005Zm685.75118222-71.55427556c-4.12301085 0-8.11302685-1.46301155-11.30503964-4.12301084l-41.76213334-35.24516978c-4.92101405-4.12301085-7.1820288-10.64004267-6.11802453-16.89110756 1.19701049-6.3840256 5.5860224-11.57108622 11.57108623-13.699072 5.98501831-2.12800853 12.76802845-0.93100373 17.6890311 3.1920128l41.76213334 35.24516978c5.71901725 4.7880192 7.84702578 12.63502222 5.32002133 19.81713066-2.52700445 7.04902258-9.17703111 11.83709867-16.62509511 11.83709867h-0.53200213ZM40.114176 681.97489778c-8.51203413-0.26600107-15.69405155-6.3840256-17.15712-14.89601423-1.46301155-8.37902791 3.1920128-16.62509511 11.1720448-19.68412444l51.33824-18.48706844c8.91103005-2.52700445 18.35406222 2.12800853 21.679104 10.90604373s-0.66500267 18.48706845-9.17703111 22.47714133l-51.87015111 18.48706845-5.98501831 1.19701049ZM1036.02176 572.24874667c-9.84203947 0-17.95504355-7.980032-17.95504355-17.82203734s7.980032-17.95504355 17.95504355-17.95504355h54.79617422c9.84203947 0 17.95504355 7.980032 17.95504356 17.95504355 0 9.84203947-7.980032 17.82203733-17.95504356 17.82203734H1036.02176ZM117.65304889 334.31096889l-5.98501831-1.19701049-51.87015111-18.48706845c-7.980032-4.12301085-11.70409245-13.43305955-8.64502898-21.94511644 3.05900658-8.37902791 11.83709867-13.30005333 20.61505422-11.43808l51.87015111 18.48706844c7.980032 3.05900658 12.63502222 11.30503965 11.1720448 19.68412445-1.46301155 8.37902791-8.64502898 14.63000178-17.15712 14.89601422Zm830.05667556-79.93332622c-7.44802987 0-14.09809067-4.7880192-16.62509512-11.83709867-2.52700445-7.04902258-0.3990016-14.89601422 5.32002134-19.81713067l41.76213333-35.24516978c7.58102471-6.3840256 18.88608711-5.45301618 25.40305067 2.12800854 6.3840256 7.58102471 5.45301618 18.88608711-2.12800854 25.40305066l-41.76213333 35.24516978c-3.32500765 2.66001067-7.58102471 4.25601707-11.96999111 4.12301085ZM348.40917333 99.29955555c-6.3840256 0-12.36900978-3.45801387-15.56104533-8.91103004l-27.398144-47.08215466c-3.32500765-5.5860224-3.32500765-12.502016-0.13300053-18.08804978 3.1920128-5.5860224 9.17703111-9.04403627 15.69405155-9.04403627s12.36900978 3.59100871 15.56104533 9.17703111l27.398144 47.08215467c3.1920128 5.5860224 3.1920128 12.36900978 0 17.95504355-3.1920128 5.45301618-9.17703111 8.91103005-15.56104533 8.91103005Zm327.97923556-31.65411555h-2.92601174c-4.65501298-0.93100373-8.7780352-3.59100871-11.43808-7.44802987-2.66001067-3.85700978-3.72401493-8.7780352-2.7930055-13.43305955l9.5760384-53.73223823c2.26101475-9.04403627 11.1720448-14.89601422 20.34904177-13.43305955 9.17703111 1.5960064 15.69405155 9.97503431 14.763008 19.28510578l-9.5760384 53.73223822c-1.46301155 8.91103005-9.04403627 15.16202667-17.95504355 15.02902045Zm0 0" fill="#fb7419"></path></svg></div></div>').appendTo($("body"));
    if($('html').hasClass("night-mode")){
        $('.yueliang').css('display', 'block'), $('.taiyang').css('display', 'none');
        storage.removeItem('gmtNightMode'); document.cookie = "night=0;path=/";
    }else{
        $('.taiyang').css('display', 'block'), $('.yueliang').css('display', 'none');
        storage.setItem('gmtNightMode', true);document.cookie = "night=1;path=/";
    }
setTimeout(function () {
    if(storage.getItem('gmtNightMode')){
     document.documentElement.classList.toggle('night-mode');
        $('.yueliang').css('display', 'block'), $('.taiyang').css('display', 'none');
    }else{
     document.documentElement.classList.toggle('night-mode');
       $('.taiyang').css('display', 'block'), $('.yueliang').css('display', 'none'); 
    }
			}, 900)
setTimeout(function () {
				$(".ze_DarkSky").fadeOut(1e3, function () {
					$(this).remove()
				})
			}, 2e3)
}



function ncPopupTips(type, msg) {
	var ico = type ? '<span class="d-block h1 text-success mb-2"><?xml version="1.0" standalone="no"?><!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd"><svg t="1553065772988" class="icon w-56" style="" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="2922" xmlns:xlink="http://www.w3.org/1999/xlink" width="200" height="200"><defs><style type="text/css"></style></defs><path d="M666.272 472.288l-175.616 192a31.904 31.904 0 0 1-23.616 10.4h-0.192a32 32 0 0 1-23.68-10.688l-85.728-96a32 32 0 1 1 47.744-42.624l62.144 69.6 151.712-165.888a32 32 0 1 1 47.232 43.2m-154.24-344.32C300.224 128 128 300.32 128 512c0 211.776 172.224 384 384 384 211.68 0 384-172.224 384-384 0-211.68-172.32-384-384-384" p-id="2923"></path></svg></span>' : '<span class="d-block h1 text-danger mb-2"><?xml version="1.0" standalone="no"?><!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd"><svg t="1553065784656" class="icon w-56" style="" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="3053" xmlns:xlink="http://www.w3.org/1999/xlink" width="200" height="200"><defs><style type="text/css"></style></defs><path d="M544 576a32 32 0 0 1-64 0v-256a32 32 0 0 1 64 0v256z m-32 160a32 32 0 1 1 0-64 32 32 0 0 1 0 64z m0-608C300.256 128 128 300.256 128 512s172.256 384 384 384 384-172.256 384-384S723.744 128 512 128z" p-id="3054"></path></svg></span>';
	var c = type ? 'tips-success' : 'tips-error';
	var html = '<section class="nice-tips '+c+' nice-tips-sm nice-tips-open">'+
					'<div class="nice-tips-overlay"></div>'+
                    '<div class="nice-tips-body text-center">'+
	                    '<div class="nice-tips-content px-5">'+ico+
	                        '<div class="text-sm text-muted">'+msg+'</div>'+
	                    '</div>'+
                    '</div>'+
                '</section>';
    var tips = $(html);
	$('body').append(tips);
	$('body').addClass('modal-open');
	if (typeof lazyLoadInstance !== "undefined") {
		lazyLoadInstance.update();
	}

	setTimeout(function(){
		$('body').removeClass('modal-open');
		tips.removeClass('nice-tips-open');
		tips.addClass('nice-tips-close');

		setTimeout(function(){
			tips.removeClass('nice-tips-close');
			setTimeout(function(){
				tips.remove();
			}, 200);
		},400);
	},1200);
}

function ncPopup(type, html, maskStyle, btnCallBack) {

	var maskStyle = maskStyle ? 'style="' + maskStyle + '"' : '';

	var size = '';

	if( type == 'big' ){
		size = 'nice-tips-lg';
	}else if( type == 'no-padding' ){
		size = 'nice-tips-nopd';
	}else if( type == 'cover' ){
		size = 'nice-tips-cover nice-tips-nopd';
	}else if( type == 'full' ){
		size = 'nice-tips-xl';
	}else if( type == 'scroll' ){
		size = 'nice-tips-scroll';
	}else if( type == 'middle' ){
		size = 'nice-tips-md';
	}else if( type == 'small' ){
		size = 'nice-tips-sm';
	}

	var template = '\
	<div class="nice-tips ' + size + ' nice-tips-open">\
		<div class="nice-tips-overlay" ' + maskStyle + '></div>\
		<div class="nice-tips-body">\
			<div class="nice-tips-content">\
				'+html+'\
			</div>\
			<div class="btn-close-tips">\
				<?xml version="1.0" standalone="no"?><!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd"><svg t="1553064665406" class="icon w-32" style="" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="3368" xmlns:xlink="http://www.w3.org/1999/xlink" width="200" height="200"><defs><style type="text/css"></style></defs><path d="M512 12C235.9 12 12 235.9 12 512s223.9 500 500 500 500-223.9 500-500S788.1 12 512 12z m166.3 609.7c15.6 15.6 15.6 40.9 0 56.6-7.8 7.8-18 11.7-28.3 11.7s-20.5-3.9-28.3-11.7L512 568.6 402.3 678.3c-7.8 7.8-18 11.7-28.3 11.7s-20.5-3.9-28.3-11.7c-15.6-15.6-15.6-40.9 0-56.6L455.4 512 345.7 402.3c-15.6-15.6-15.6-40.9 0-56.6 15.6-15.6 40.9-15.6 56.6 0L512 455.4l109.7-109.7c15.6-15.6 40.9-15.6 56.6 0 15.6 15.6 15.6 40.9 0 56.6L568.6 512l109.7 109.7z" p-id="3369"></path></svg>\
			</div>\
		</div>\
	</div>\
	';

	var popup = $(template);
	$('body').append(popup);
	$('body').addClass('modal-open');
	if (typeof lazyLoadInstance !== "undefined") {
		lazyLoadInstance.update();
	}

	var close = function(){
		$('body').removeClass('modal-open');
		$(popup).removeClass('nice-tips-open');
		$(popup).addClass('nice-tips-close');
		setTimeout(function(){
			$(popup).removeClass('nice-tips-close');
			setTimeout(function(){
				popup.remove();
			}, 200);
		},600);
	}

	$(popup).on('click touchstart', '.btn-close-tips, .nice-tips-overlay', function(event) {
		event.preventDefault();
		close();
	});


	if( typeof btnCallBack == 'object' ){
		Object.keys(btnCallBack).forEach(function(key){

			$(popup).on('click touchstart', key, function(event) {

				var ret = btnCallBack[key](event, close);

			});

		});
	}
	return popup;
}








/* ----------------- Start Document ----------------- */
(function ($) {
	"use strict";
	$(document).ready(function () {

var storage = window.localStorage;
 if (storage.getItem('gmtNightMode')){
document.documentElement.className += ' night-mode';document.cookie = "night=1;path=/";
 }
var storage = window.localStorage;
if(globals.post_id!=0){
if(storage.getItem('like_'+globals.post_id)){
 $('.btn-like[data-action="like"]').addClass('current');
 $('.btn-like[data-action="like"]').attr('data-action', 'unlike');
}
}	    
	    
$(".scrollLoading").scrollLoading({callback: function() {$(this).removeAttr("data-type");$(this).removeAttr("data-xurl");$(this).addClass("ojbk");}
});


$(".protected").submit(function() {
var sxurl=$(".protected").attr("action");
$.ajax({
                type: "POST",
                url:sxurl,
                data:$('.protected').serialize(),// 你的formid
                async:true,
                error: function(request) {
ncPopupTips(0, "密码提交失败，请刷新页面重试！");
                },
                success: function(data) {
if(data.indexOf("密码错误") >= 0 && (data.indexOf("<title>Error</title>") >= 0 || data.indexOf("Typecho_Widget_Exception") >= 0)) {
ncPopupTips(0, "密码错误，请重试！");
}else{
location.reload();//密码正确刷新页面
}
                }
});
return false;
});
   



$(document).on('click', '#night-mode', function (event) {
SwitchDarkMode(event);
});


$(document).on('click', '.btn-search-toggler', function (event) {
    event.preventDefault();
    ncPopup('middle', $('#single-search-template').html());
});


$(document).on("click", '.btn-collect[data-action="collect"]', function () {
    event.preventDefault();
    var $this = $('.btn-collect');
    var id = $(this).data("id");
    var html = $this.html();

    if ($this.hasClass('requesting')) {
        return false;
    }

    $this.addClass('requesting');
    $.ajax({
        url: globals.ajax_url,
        type: 'post',
        dataType: 'html',
        data: { collect: id, },
    })
        .done(function (data) {
            if(data=="ok"){$this.addClass('current').html(html);ncPopupTips(1, '收藏成功');}
            else if(data=="ko"){$this.removeClass('current').html(html);ncPopupTips(0, '已取消收藏');}
            else{ncPopupTips(0, data);}
        })
        .always(function () {
            $this.removeClass('requesting');
        });
    return false;
});


$(document).on("click", '.btn-like[data-action="like"]', function () {
if(storage.getItem('like_'+globals.post_id)){return false;}
    event.preventDefault();
    var $this = $('.btn-like');
    var id = $(this).data("id");
    var html = $this.html();

    if ($this.hasClass('requesting')) {
        return false;
    }

    $this.addClass('requesting');
    $.ajax({
        url: globals.ajax_url,
        type: 'post',
        dataType: 'html',
        data: { likeup: id, do_action: 'do' },
    })
        .done(function (data) {storage.setItem('like_'+id, true);
            $this.addClass('current').html(html);
            $this.attr('data-action', 'unlike');
            ncPopupTips(1, '谢谢点赞')
            $('.like-count').html(data.trim());
        })
        .always(function () {
            $this.removeClass('requesting');
        });
    return false;
});

$(document).on("click", '.btn-like[data-action="unlike"]', function () {
    if(!storage.getItem('like_'+globals.post_id)){return false;}
    event.preventDefault();
    var $this = $('.btn-like');
    var id = $(this).data("id");
    var html = $this.html();

    if ($this.hasClass('requesting')) {
        return false;
    }

    $this.addClass('requesting');

    $this.removeClass('current');

    $.ajax({
        url: globals.ajax_url,
        type: 'post',
        dataType: 'html',
        data: {likeup: id, do_action: 'undo' },
    })
        .done(function (data) {storage.removeItem('like_'+id); 
            $this.removeClass('current').html(html);
            $this.attr('data-action', 'like');
            ncPopupTips(0, '取消点赞')
            $('.like-count').html(data.trim());
        })
        .always(function () {
            $this.removeClass('requesting');
        });
    return false;
});


		/*----------------------------------------------------*/
		/*  Sidebar Nav Submenus
		/*----------------------------------------------------*/

		$('.sidebar_innr ul li a').on('click', function (e) {
			if ($(this).closest("li").children("ul").length) {
				if ($(this).closest("li").is(".active-submenu")) {
					$('.sidebar_innr ul li').removeClass('active-submenu');
				} else {
					$('.sidebar_innr ul li').removeClass('active-submenu');
					$(this).parent('li').addClass('active-submenu');
				}
				e.preventDefault();
			}
		});


		/*----------------------------------------------------*/
		/*  Back to Top
		/*----------------------------------------------------*/

		// Button
		function backToTop() {
			$('body').append('<div id="backtotop"><a href="#"></a></div>');
		}
		backToTop();

        // Scroll to Top
        $(window).scroll(function () {
            if ($(this).scrollTop() >= 50) {
             	$("#backtotop").addClass('visible uk-animation-slide-bottom');
            } else {
				$("#backtotop").removeClass('visible uk-animation-slide-bottom');
            }
        });
        $('#backtotop a').click(function () {
            $('body,html').animate({
                scrollTop: 0
            }, 500);
        });

		/*--------------------------------------------------*/
		/*  NProgress
		/*-------------------------------------------------- */
		NProgress.start(); // start    
		NProgress.set(0.4); // To set a progress percentage, call .set(n), where n is a number between 0..1
		NProgress.inc(); // To increment the progress bar, just use .inc(). This increments it with a random amount. This will never get to 100%: use it for every image load (or similar).If you want to increment by a specific value, you can pass that as a parameter
		NProgress.configure({
			ease: 'ease',
			speed: 1000
		}); // Adjust animation settings using easing (a CSS easing string) and speed (in ms). (default: ease and 200)
		NProgress.configure({
			trickleSpeed: 800
		}); //Adjust how often to trickle/increment, in ms.
		NProgress.configure({
			showSpinner: true
		}); //Turn off loading spinner by setting it to false. (default: true)
		NProgress.configure({
			parent: '#wrapper'
		}); //specify this to change the parent container. (default: body)
		NProgress.done(); // end 


		// ------------------ End Document ------------------ //
	});

})(this.jQuery);





/*! NProgress (c) 2013, Rico Sta. Cruz
 *  http://ricostacruz.com/nprogress */

;
(function (factory) {

	if (typeof module === 'function') {
		module.exports = factory(this.jQuery || require('jquery'));
	} else {
		this.NProgress = factory(this.jQuery);
	}

})(function ($) {
	var NProgress = {};

	NProgress.version = '0.1.2';

	var Settings = NProgress.settings = {
		minimum: 0.08,
		easing: 'ease',
		positionUsing: '',
		speed: 200,
		trickle: true,
		trickleRate: 0.02,
		trickleSpeed: 800,
		showSpinner: true,
		template: '<div class="bar" role="bar"><div class="peg"></div></div><div class="spinner" role="spinner"><div class="spinner-icon"></div></div>'
	};

	/**
	 * Updates configuration.
	 *
	 *     NProgress.configure({
	 *       minimum: 0.1
	 *     });
	 */
	NProgress.configure = function (options) {
		$.extend(Settings, options);
		return this;
	};

	/**
	 * Last number.
	 */

	NProgress.status = null;

	/**
	 * Sets the progress bar status, where `n` is a number from `0.0` to `1.0`.
	 *
	 *     NProgress.set(0.4);
	 *     NProgress.set(1.0);
	 */

	NProgress.set = function (n) {
		var started = NProgress.isStarted();

		n = clamp(n, Settings.minimum, 1);
		NProgress.status = (n === 1 ? null : n);

		var $progress = NProgress.render(!started),
			$bar = $progress.find('[role="bar"]'),
			speed = Settings.speed,
			ease = Settings.easing;

		$progress[0].offsetWidth; /* Repaint */

		$progress.queue(function (next) {
			// Set positionUsing if it hasn't already been set
			if (Settings.positionUsing === '') Settings.positionUsing = NProgress.getPositioningCSS();

			// Add transition
			$bar.css(barPositionCSS(n, speed, ease));

			if (n === 1) {
				// Fade out
				$progress.css({
					transition: 'none',
					opacity: 1
				});
				$progress[0].offsetWidth; /* Repaint */

				setTimeout(function () {
					$progress.css({
						transition: 'all ' + speed + 'ms linear',
						opacity: 0
					});
					setTimeout(function () {
						NProgress.remove();
						next();
					}, speed);
				}, speed);
			} else {
				setTimeout(next, speed);
			}
		});

		return this;
	};

	NProgress.isStarted = function () {
		return typeof NProgress.status === 'number';
	};

	/**
	 * Shows the progress bar.
	 * This is the same as setting the status to 0%, except that it doesn't go backwards.
	 *
	 *     NProgress.start();
	 *
	 */
	NProgress.start = function () {
		if (!NProgress.status) NProgress.set(0);

		var work = function () {
			setTimeout(function () {
				if (!NProgress.status) return;
				NProgress.trickle();
				work();
			}, Settings.trickleSpeed);
		};

		if (Settings.trickle) work();

		return this;
	};

	/**
	 * Hides the progress bar.
	 * This is the *sort of* the same as setting the status to 100%, with the
	 * difference being `done()` makes some placebo effect of some realistic motion.
	 *
	 *     NProgress.done();
	 *
	 * If `true` is passed, it will show the progress bar even if its hidden.
	 *
	 *     NProgress.done(true);
	 */

	NProgress.done = function (force) {
		if (!force && !NProgress.status) return this;

		return NProgress.inc(0.3 + 0.5 * Math.random()).set(1);
	};

	/**
	 * Increments by a random amount.
	 */

	NProgress.inc = function (amount) {
		var n = NProgress.status;

		if (!n) {
			return NProgress.start();
		} else {
			if (typeof amount !== 'number') {
				amount = (1 - n) * clamp(Math.random() * n, 0.1, 0.95);
			}

			n = clamp(n + amount, 0, 0.994);
			return NProgress.set(n);
		}
	};

	NProgress.trickle = function () {
		return NProgress.inc(Math.random() * Settings.trickleRate);
	};

	/**
	 * (Internal) renders the progress bar markup based on the `template`
	 * setting.
	 */

	NProgress.render = function (fromStart) {
		if (NProgress.isRendered()) return $("#nprogress");
		$('html').addClass('nprogress-busy');

		var $el = $("<div id='nprogress'>")
			.html(Settings.template);

		var perc = fromStart ? '-100' : toBarPerc(NProgress.status || 0);

		$el.find('[role="bar"]').css({
			transition: 'all 0 linear',
			transform: 'translate3d(' + perc + '%,0,0)'
		});

		if (!Settings.showSpinner)
			$el.find('[role="spinner"]').remove();

		$el.appendTo(document.body);

		return $el;
	};

	/**
	 * Removes the element. Opposite of render().
	 */

	NProgress.remove = function () {
		$('html').removeClass('nprogress-busy');
		$('#nprogress').remove();
	};

	/**
	 * Checks if the progress bar is rendered.
	 */

	NProgress.isRendered = function () {
		return ($("#nprogress").length > 0);
	};

	/**
	 * Determine which positioning CSS rule to use.
	 */

	NProgress.getPositioningCSS = function () {
		// Sniff on document.body.style
		var bodyStyle = document.body.style;

		// Sniff prefixes
		var vendorPrefix = ('WebkitTransform' in bodyStyle) ? 'Webkit' :
			('MozTransform' in bodyStyle) ? 'Moz' :
			('msTransform' in bodyStyle) ? 'ms' :
			('OTransform' in bodyStyle) ? 'O' : '';

		if (vendorPrefix + 'Perspective' in bodyStyle) {
			// Modern browsers with 3D support, e.g. Webkit, IE10
			return 'translate3d';
		} else if (vendorPrefix + 'Transform' in bodyStyle) {
			// Browsers without 3D support, e.g. IE9
			return 'translate';
		} else {
			// Browsers without translate() support, e.g. IE7-8
			return 'margin';
		}
	};

	/**
	 * Helpers
	 */

	function clamp(n, min, max) {
		if (n < min) return min;
		if (n > max) return max;
		return n;
	}

	/**
	 * (Internal) converts a percentage (`0..1`) to a bar translateX
	 * percentage (`-100%..0%`).
	 */

	function toBarPerc(n) {
		return (-1 + n) * 100;
	}


	/**
	 * (Internal) returns the correct CSS for changing the bar's
	 * position given an n percentage, and speed and ease from Settings
	 */

	function barPositionCSS(n, speed, ease) {
		var barCSS;

		if (Settings.positionUsing === 'translate3d') {
			barCSS = {
				transform: 'translate3d(' + toBarPerc(n) + '%,0,0)'
			};
		} else if (Settings.positionUsing === 'translate') {
			barCSS = {
				transform: 'translate(' + toBarPerc(n) + '%,0)'
			};
		} else {
			barCSS = {
				'margin-left': toBarPerc(n) + '%'
			};
		}

		barCSS.transition = 'all ' + speed + 'ms ' + ease;

		return barCSS;
	}

	return NProgress;
});


	    
