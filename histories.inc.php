<?php

defined('IN_JISHIGOU') or exit('invalid request');

//require_once( dirname( __FILE__ ) . '/' . 'common.php' );
require 'env.php';
require 'lib/webim_db.class.php';

define(WEBIMDB_DEBUG, true);

$_dbcfg = $GLOBALS['_J']['config'];
$imdb = new webim_db($_dbcfg['db_user'], $_dbcfg['db_pass'], $_dbcfg['db_name'], $_dbcfg['db_host']);
$imdb->set_prefix($_dbcfg['db_table_prefix'] . 'webim_');
$imdb->add_tables( array('histories') );

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

