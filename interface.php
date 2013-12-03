<?php

/** 
 * Custom interface 
 *
 * Provide 
 *
 * array $_IMC
 * boolean $im_is_admin
 * boolean $im_is_login
 * object $imuser require when $im_is_login
 * function webim_get_buddies()
 * function webim_get_online_buddies()
 * function webim_get_rooms()
 * function webim_get_notifications()
 * function webim_login()
 *
 */

define( 'WEBIM_PRODUCT_NAME', 'jishigou' );

$_SERVER['REQUEST_URI'] = "";

if ( !defined('IN_JISHIGOU') ) {
	require_once '../../include/jishigou.php';
	$jishigou = new jishigou();
	$jishigou->init_user = true;
	$jishigou->init();
}

//Find and insert data with utf8 client.
@DB::query( "SET NAMES utf8" );


@include_once 'config.php';

/**
 *
 * Provide the webim database config.
 *
 * $_IMC['dbuser'] MySQL database user
 * $_IMC['dbpassword'] MySQL database password
 * $_IMC['dbname'] MySQL database name
 * $_IMC['dbhost'] MySQL database host
 * $_IMC['dbtable_prefix'] MySQL database table prefix
 * $_IMC['dbcharset'] MySQL database charset
 *
 */

$_IMC['dbuser'] = $GLOBALS['_J']['config']['db_user'];
$_IMC['dbpassword'] = $GLOBALS['_J']['config']['db_pass'];
$_IMC['dbname'] = $GLOBALS['_J']['config']['db_name'];
$_IMC['dbhost'] = $GLOBALS['_J']['config']['db_host'];
$_IMC['dbtable_prefix'] = $GLOBALS['_J']['config']['db_table_prefix'];

/**
 * Init im user.
 * 	-uid:
 * 	-id:
 * 	-nick:
 * 	-pic_url:
 * 	-show:
 *
 */

if( !defined('IN_JISHIGOU') || !$GLOBALS['_J']['uid'] ) {
	exit('"invalid request"');
}

if ( $GLOBALS['_J']['uid'] ) {
	webim_set_user();
	$im_is_login = true;

} else {
	$im_is_login = false;
}

function profile_url( $id ) {
	return jurl('index.php?mod='.$id);
}

function webim_get_menu () {
	$menu = array(
		array("title" => 'newtopic',"icon" =>$GLOBALS['_J']['site_url']."/images/lefticon/mygroup_icon.jpg","link" => jurl('index.php?mod=topic&code=new')),
        array("title" => 'newreply',"icon" =>$GLOBALS['_J']['site_url']."/images/lefticon/mychannelb_icon.jpg","link" => jurl('index.php?mod=topic&code=newreply')),
        array("title" => 'hotreply',"icon" =>$GLOBALS['_J']['site_url']."/images/lefticon/live_icon.jpg","link" => jurl('index.php?mod=topic&code=hotreply')),
        array("title" => 'hotforward',"icon" =>$GLOBALS['_J']['site_url']."/images/lefticon/left_define_icon.png","link" => jurl('index.php?mod=topic&code=hotforward'))
    );
	return $menu;
}

function webim_set_user( $is_utf8 = false ){
	global $imuser;
	$imuser->uid = $GLOBALS['_J']['uid'];
	$id = $is_utf8 ? $GLOBALS['_J']['nickname'] : to_utf8( $GLOBALS['_J']['nickname'] );
	$imuser->id = $id;
	$imuser->nick = $id;
	if( $_IMC['show_realname'] ) {
		$data = DB::fetch_first("SELECT validate_true_name FROM ".DB::table('memberfields')." WHERE uid = '$imuser->uid'");
		if( $data && $data['validate_true_name'] )
			$imuser->nick = $data['validate_true_name'];
	}
	$imuser->pic_url = face_get($imuser->uid);
	$imuser->default_pic_url = $GLOBALS['_J']['site_url'] . '/images/noavatar.gif';
	$imuser->show = webim_gp('show') ? webim_gp('show') : "available";
	$imuser->url = profile_url( $imuser->uid );
	complete_status( array( $imuser ) );
}

function webim_login( $username, $password, $question = "", $answer = "" ) {
	global $imuser, $im_is_login;
	$result = jsg_member_login($username, $password);
	if($result['uid'] > 0) {
		$im_is_login = true;
		webim_set_user( true );
		return true;
	}
	return false;
}

/**
 * Online buddy list.
 *
 */
function webim_get_online_buddies(){
	global $friend_groups, $imuser;
	$list = array();
	$query = DB::query("SELECT s.uid, m.username, m.nickname, f.validate_true_name name, b.touid gid FROM ".DB::table('sessions')." s
		LEFT JOIN ".DB::table('members')." m ON m.uid = s.uid 
		LEFT JOIN ".DB::table('memberfields')." f ON f.uid = m.uid 
		LEFT JOIN ".DB::table(jtable('buddy_follow')->table_name($imuser->uid))." b ON b.touid = s.uid AND b.uid = '$imuser->uid'
		WHERE s.uid <> '$imuser->uid'");
	while ($value = DB::fetch($query)){
		$list[] = (object)array(
			"uid" => $value['uid'],
			"id" => $value['nickname'],
			"nick" => nick($value),
			"group" => $value['gid'] ? '' : 'stranger',
			"url" => profile_url( $value['username'] ),
			"pic_url" => face_get($value['uid']),
			'default_pic_url' => $GLOBALS['_J']['site_url'] . '/images/noavatar.gif',
		);
	}
	complete_status( $list );
	return $list;
}

/**
 * Get buddy list from given ids
 * $ids:
 *
 * Example:
 * 	buddy('admin,webim,test');
 *
 */

function webim_get_buddies( $names, $uids = null ){
	global $friend_groups, $imuser;
	$where_name = "";
	$where_uid = "";
	if(!$names and !$uids)return array();
	if($names){
		$names = "'".implode("','", explode(",", $names))."'";
		$where_name = "m.nickname IN ($names)";
	}
	if($uids){
		//$uids = "'".implode("','", explode(",", $uids))."'";
		$where_uid = "m.uid IN ($uids)";
	}
	$where_sql = $where_name && $where_uid ? "($where_name OR $where_uid)" : ($where_name ? $where_name : $where_uid);

	$list = array();
	$query = DB::query("SELECT m.uid, m.username, m.nickname, f.validate_true_name name FROM ".DB::table('members')." m 
		LEFT JOIN ".DB::table('memberfields')." f ON f.uid = m.uid AND m.uid <> $imuser->uid 
		WHERE m.uid <> $imuser->uid AND $where_sql");
	while ( $value = DB::fetch( $query ) ){
		$list[] = (object)array(
			"uid" => $value['uid'],
			"id" => $value['nickname'],
			"nick" => nick($value),
			"group" => "stranger",
			"url" => profile_url($value['username']),
			"pic_url" => face_get($value['uid']),
			'default_pic_url' => $GLOBALS['_J']['site_url'] . '/images/noavatar.gif',
		);
	}
	complete_status( $list );
	return $list;
}

/**
 * Get room list
 * $ids: Get all imuser rooms if not given.
 *
 */

function webim_get_rooms($ids=null){
	global $imuser;
	if(!$ids){
		$query = DB::query("SELECT qid FROM ".DB::table("qun_user")." WHERE uid=$imuser->uid");
		while ($value = DB::fetch($query)){
			$ids[] = $value['qid'];
		}
		$ids = implode( ",", $ids );
	}
	$list = array();
	if(!$ids){
		return $list;
	}
	$query = DB::query("SELECT qid, name, icon, member_num, `desc` FROM ".DB::table('qun')." WHERE qid IN ($ids)");

	while ($value = DB::fetch($query)){
		$list[] = (object)array(
			"fid" => $value['qid'],
			"id" => $value['qid'],
			"nick" => $value['name'],
			"url" => jurl('index.php?mod=qun&qid='.$value['qid']),
			"pic_url" => $GLOBALS['_J']['site_url'] . ($value['icon'] ? $value['icon'] : "/images/qun_def_b.jpg"),
			'default_pic_url' => $GLOBALS['_J']['site_url'] . '/images/qun_def_b.jpg',
			"status" => $value['desc'],
			"count" => 0,
			"all_count" => $value['member_num'],
			"blocked" => false,
		);
	}
	return $list;
}

function webim_get_notifications(){
	global $imuser;
	$member = jsg_member_info($imuser->uid);
	$notice = array();
	if($member['newpm']>0){
		$notice[] = array('text'=>to_utf8($member['newpm'].'条新私信'),'link'=>jurl('index.php?mod=pm&code=list'));
	}
	if($member['comment_new']>0){
		$notice[] = array('text'=>to_utf8($member['comment_new'].'条新评论'),'link'=>jurl('index.php?mod=topic&code=mycomment'));
	}
	if($member['fans_new']>0){
		$notice[] = array('text'=>to_utf8($member['fans_new'].'人关注了我'),'link'=>jurl('index.php?mod='.$member['username'].'&code=fans'));
	}
	if($member['at_new']>0){
		$notice[] = array('text'=>to_utf8($member['at_new'].'人@提到我'),'link'=>jurl('index.php?mod=topic&code=myat'));
	}
	if($member['dig_new']>0){
		$notice[] = array('text'=>to_utf8('有'.$member['dig_new'].'人'.$GLOBALS['_J']['config']['changeword']['dig'].'了你'),'link'=>jurl('index.php?mod='.$member['username'].'&type=mydig'));
	}
	if($member['channel_new']>0){
		$notice[] = array('text'=>to_utf8('频道新增'.$member['channel_new'].'条内容'),'link'=>jurl('index.php?mod=topic&code=channel&orderby=post'));
	}
	if($member['vote_new']>0){
		$notice[] = array('text'=>to_utf8('投票新增'.$member['vote_new'].'人参与'),'link'=>jurl('index.php?mod=vote&view=me&filter=new_update&uid='.$member['uid']));
	}
	if($member['qun_new']>0){
		$notice[] = array('text'=>to_utf8('微群新增'.$member['qun_new'].'条内容'),'link'=>jurl('index.php?mod=topic&code=qun'));
	}		
	if($member['event_new']>0){
		$notice[] = array('text'=>to_utf8('活动新增'.$member['event_new'].'人报名'),'link'=>jurl('index.php?mod=event&code=myevent&type=new'));
	}		
	if($member['topic_new']>0){
		$notice[] = array('text'=>to_utf8('新增'.$member['topic_new'].'条话题内容'),'link'=>jurl('index.php?mod=topic&code=tag'));
	}        
	if($member['event_post_new']>0){
		$notice[] = array('text'=>to_utf8('新增'.$member['event_post_new'].'个关注的活动'),'link'=>jurl('index.php?mod=topic&code=other&view=event'));
	}           	
	if($member['fenlei_post_new']>0){
		$notice[] = array('text'=>to_utf8('新增'.$member['fenlei_post_new'].'条分类信息'),'link'=>jurl('index.php?mod=topic&code=other&view=fenlei'));
	}
	return $notice;
}

/**
 * Add status to member info.
 *
 * @param array $members the member list
 * @return 
 *
 */
function complete_status( $members ) {
	if(!empty($members)){
		$num = count($members);
		$ids = array();
		$ob = array();
		for($i = 0; $i < $num; $i++){
			$m = $members[$i];
			$id = $m->uid;
			if ( $id ) {
				$ids[] = $id;
				$ob[$id] = $m;
			}
		}
		$ids = implode(",", $ids);
		$query = DB::query("SELECT uid, fans_count, topic_count FROM ".DB::table('members')." WHERE uid IN ($ids)");
		while($res = DB::fetch($query)) {
			$ob[$res['uid']]->status = to_utf8('粉丝:'.$res['fans_count'].'&nbsp;微博:'.$res['topic_count']);
		}
	}
	return $members;
}

function nick( $sp ) {
	global $_IMC;
	return (!$_IMC['show_realname']||empty($sp['name'])) ? $sp['nickname'] : $sp['name'];
}

function to_utf8( $s ) {
	if( strtoupper( CHARSET ) == 'UTF-8' ) {
		return $s;
	} else {
		if ( function_exists( 'iconv' ) ) {
			return iconv( CHARSET, 'utf-8', $s );
		} else {
			require_once 'class_chinese.php';
			$chs = new Chinese( CHARSET, 'utf-8' );
			return $chs->Convert( $s );
		}
	}
}
function from_utf8( $s ) {
	if( strtoupper( CHARSET ) == 'UTF-8' ) {
		return $s;
	} else {
		if ( function_exists( 'iconv' ) ) {
			return iconv( 'utf-8', CHARSET, $s );
		} else {
			require_once 'class_chinese.php';
			$chs = new Chinese( 'utf-8', CHARSET );
			return $chs->Convert( $s );
		}
	}
}

?>
