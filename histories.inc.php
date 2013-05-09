<?php
if(!defined('IN_JISHIGOU'))
{
    exit('invalid request');
}
require_once( dirname( __FILE__ ) . '/' . 'common.php' );

$notice = "";

if(jget('period')){

	switch (jget('period')) {
	case 'weekago':
		$ago = 7*24*60*60;break;
	case 'monthago':
		$ago = 30*24*60*60;break;
	case '3monthago':
		$ago = 3*30*24*60*60;break;
	default:
		$ago = 0;
	}
	$ago = ( time() - $ago ) * 1000;
	$imdb->query( $imdb->prepare( "DELETE FROM $imdb->webim_histories WHERE `timestamp` < %s", $ago ) );
	$this->Messager("记录清空成功", 'admin.php?mod=plugin&code=manage&id='.$pluginid.'&identifier=webim&pmod=histories');
}

$count = $imdb->get_var( $imdb->prepare( "SELECT count(*) FROM $imdb->webim_histories" ) );
$action = 'admin.php?mod=plugin&code=manage&id='.$pluginid.'&identifier=webim&pmod=histories';
?>

