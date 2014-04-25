<?php
$IMC = array();
$IMC["version"] = "@VERSION";//版本
$IMC["isopen"] = true;//开启webim
$IMC["domain"] = "";//网站注册域名
$IMC["apikey"] = "";//网站注册apikey
$IMC["host"] = "t.nextalk.im";//IM服务器
$IMC["port"] = 8000;//IM Port
$IMC["theme"] = "base";//界面主题，根据webim/static/themes/目录内容选择
$IMC["local"] = "zh-CN";//本地语言，扩展请修改webim/static/i18n/内容
$IMC["emot"] = "default";//表情主题
$IMC["opacity"] = 80;//toolbar背景透明度设置
$IMC["show_realname"] = false;//是否显示好友真实姓名
$IMC["enable_room"] = true;//禁止群组聊天
$IMC['discussion'] = true; 
$IMC["enable_chatlink"] = false;//禁止页面名字旁边的聊天链接
$IMC["enable_shortcut"] = false;//支持工具栏快捷方式
$IMC['enable_menu'] = false; //工具条
$IMC["enable_noti"] = true;//通知
$IMC['enable_login'] = false; //允许未登录时显示IM，并可从im登录
$IMC['visitor'] = false; //支持访客聊天(默认好友为站长),开启后通过im登录无效
$IMC['upload'] = false; //是否支持文件(图片)上传
$IMC['show_unavailable'] = false; //支持显示不在线用户
$IMC['admin_as_buddy'] = false; 

$query = DB::query("SELECT v.* FROM ".DB::table('pluginvar')." v, 
	".DB::table('plugin')." p 
	WHERE p.identifier='webim' AND v.pluginid = p.pluginid");
while($var = DB::fetch($query)){
	if(!empty($var['value'])){
		$IMC[$var['variable']] = $var['value'];
	}
}


