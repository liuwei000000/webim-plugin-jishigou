<?php

/**
 * WebIM-for-JishiGou插件入口文件
 *
 * @copyright   (C) 2014 NexTalk.IM
 * @license     http://nextalk.im/license
 * @lastmodify  2014-04-21
 */ 

// Die if PHP is not new enough
if (version_compare( PHP_VERSION, '4.3', '<' ) ) {
	die( sprintf( 'Your server is running PHP version %s but webim requires at least 4.3', PHP_VERSION ) );
}

require 'env.php';

require 'config.php';

if( !$IMC['isopen'] ) exit('WebIM Not Opened');

/**
 * -----------------------
 * integrated with jishgou
 * -----------------------
 */
define( 'DISABLEXSSCHECK', true );

$_SERVER['REQUEST_URI'] = "";

if ( !defined('IN_JISHIGOU') ) {
	require_once '../../include/jishigou.php';
	$jishigou = new jishigou();
	$jishigou->init_user = true;
	$jishigou->init();
}

$IMC['dbuser'] = $GLOBALS['_J']['config']['db_user'];
$IMC['dbpassword'] = $GLOBALS['_J']['config']['db_pass'];
$IMC['dbname'] = $GLOBALS['_J']['config']['db_name'];
$IMC['dbhost'] = $GLOBALS['_J']['config']['db_host'];
$IMC['dbprefix'] = $GLOBALS['_J']['config']['db_table_prefix'] . 'webim_';

//Find and insert data with utf8 client.
DB::query( "SET NAMES utf8" );

/**
 * -----------------------
 * end
 * -----------------------
 */

function WEBIM_PATH() {
	global $_SERVER;
    $name = htmlspecialchars($_SERVER['SCRIPT_NAME'] ? $_SERVER['SCRIPT_NAME'] : $_SERVER['PHP_SELF']); 
    return substr( $name, 0, strrpos( $name, '/' ) ) . "/";
}

function WEBIM_IMAGE($img) {
    return WEBIM_PATH() . "static/images/{$img}";
}

if($IMC['debug']) {
    define(WEBIM_DEBUG, true);
} else {
    define(WEBIM_DEBUG, false);
}

// Modify error reporting levels to exclude PHP notices
if( WEBIM_DEBUG ) {
	error_reporting( -1 );
} else {
	error_reporting( E_ALL & ~E_NOTICE & ~E_STRICT );
}

/**
 * load libraries
 */
require 'lib/http_client.php';
require 'lib/webim_client.class.php';
require 'lib/webim_common.func.php';
require 'lib/webim_db.class.php';
require 'lib/webim_model.class.php';
require 'lib/webim_plugin.class.php';
require 'lib/webim_router.class.php';
require 'lib/webim_app.class.php';

require 'webim_plugin_jishigou.class.php';

/**
 * webim route
 */
$app = new webim_app();

$app->plugin(new webim_plugin_jishigou());

$app->model(new webim_model());

$app->run();

?>

