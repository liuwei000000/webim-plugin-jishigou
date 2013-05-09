<?php
if(!defined('IN_JISHIGOU'))
{
    exit('invalid request');
}
function webim_scan_subdir( $dir ){
	$d = dir( $dir."/" );
	$dn = array();
	while ( false !== ( $f = $d->read() ) ) {
		if(is_dir($dir."/".$f) && $f!='.' && $f!='..') $dn[]=$f;
	}
	$d->close();
	return $dn;
}

if(jget('theme')){
	$theme = jget('theme');
	DB::query("UPDATE ".DB::table('pluginvar')." SET value='$theme' WHERE pluginid='$pluginid' AND variable='theme'");
	$this->Messager("主题更新成功，您可以更新JishiGou页面查看效果。", 'admin.php?mod=plugin&code=manage&id='.$pluginid.'&identifier=webim&pmod=themes');

}else{
	$res = DB::fetch_first("SELECT * FROM ".DB::table('pluginvar')." WHERE pluginid='$pluginid' AND variable='theme'");
	if($res){
		$theme = $res['value'];
	}else{
		$theme = 'base';
	}
}

$path = dirname(__FILE__).DIRECTORY_SEPARATOR."static".DIRECTORY_SEPARATOR."themes";
$files = webim_scan_subdir( $path );
$webmihtml = '<ul id="themes">';
foreach ($files as $k => $v){
	$t_path = $path.DIRECTORY_SEPARATOR.$v;
	if(is_dir($t_path) && is_file($t_path.DIRECTORY_SEPARATOR."jquery.ui.theme.css")){
		$cur = $v == $theme ? " class='current'" : "";
		$url = 'admin.php?mod=plugin&code=manage&id='.$pluginid.'&identifier=webim&pmod=themes&theme='.$v;
		$webmihtml .= "<li$cur><h4><a href='$url'>$v</a></h4><p><a href='$url'><img width=100 height=134 src='plugin/webim/static/themes/images/$v.png' alt='$v' title='$v'/></a></p></li>";
	}
}
$webmihtml .= '</ul>';
?>
