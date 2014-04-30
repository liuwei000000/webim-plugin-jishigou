<?php

/**
 * WebIM Plugin for JishiGou
 */
class webim_plugin_jishigou extends webim_plugin {

	/*
	 * constructor
	 */
    function __construct() {
        parent::__construct();
    }

	/*
	 * old constructor
	 */
    function webim_plugin_jishigou() {
        $this->__construct();
    }

    function user() {
        global $GLOBALS, $IMC;
        $uid = $GLOBALS['_J']['uid'];
        if( !$uid ) return null;

        $user = array();
        $user['id'] = $uid;
        $user['nick'] = webim_to_utf8(strtoupper(CHARSET), $GLOBALS['_J']['nickname'] );
        if( $IMC['show_realname'] ) {
            $data = DB::fetch_first("SELECT validate_true_name FROM ".DB::table('memberfields')." WHERE uid = '$uid'");
            if( $data && $data['validate_true_name'] )
                $user['nick'] = $data['validate_true_name'];
        }
        $user['pic_url'] = face_get($uid);
        $user['default_pic_url'] = $GLOBALS['_J']['site_url'] . '/images/noavatar.gif';
        $user['url'] = jurl('index.php?mod=' . $uid);
        $user = (object) $user;
        $this->complete_status( array( $user ) );
        return $user;
    }

    function buddies($uid) {
        global $GLOBALS;
        $buddies = array();
        $sql = "SELECT s.uid, m.username, m.nickname, f.validate_true_name name, b.touid gid FROM ".DB::table('sessions')." s
            LEFT JOIN ".DB::table('members')." m ON m.uid = s.uid 
            LEFT JOIN ".DB::table('memberfields')." f ON f.uid = m.uid 
            LEFT JOIN ".DB::table(jtable('buddy_follow')->table_name($uid))." b ON b.touid = s.uid AND b.uid = '$uid'
            WHERE s.uid <> '$uid' and s.uid <> 0";
        $query = DB::query($sql);
        while ($value = DB::fetch($query)){
            $buddies[] = (object)array(
                "id" => $value['uid'],
                "nick" => $this->nick($value),
                "group" => $value['gid'] ? '' : 'stranger',
                "url" => jurl('index.php?mod=' . $value['username']),
                "pic_url" => face_get($value['uid']),
                'default_pic_url' => $GLOBALS['_J']['site_url'] . '/images/noavatar.gif',
            );
        }
        $this->complete_status( $buddies);
        return $buddies;
    }

    /**
     * buddies from given ids
     * $ids:
     *
     * Example:
     * 	buddy_by_ids(array(1,2,3));
     *
     */
    function buddies_by_ids($uid, $ids){
        global $GLOBALS;
        if( count($ids) === 0 ) return array();
        $uids = array();
        foreach($ids as $id) {
            if( !webim_isvid($id) ) $uids[] = $id;
        }
        if( count($uids) === 0 ) return array();
        $buddies  = array();
        $ids = implode(',', $uids);
        $query = DB::query("SELECT m.uid, m.username, m.nickname, f.validate_true_name name FROM ".DB::table('members')." m 
		LEFT JOIN ".DB::table('memberfields')." f ON f.uid = m.uid AND m.uid <> $uid 
		WHERE m.uid <> $uid AND m.uid IN ({$ids})");
        while ( $value = DB::fetch( $query ) ){
            $buddies[] = (object) array(
                "id" => $value['uid'],
                "nick" => $this->nick($value),
                "group" => "friend",
                "url" => jurl('index.php?mod=' . $value['username']),
                "pic_url" => face_get($value['uid']),
                'default_pic_url' => $GLOBALS['_J']['site_url'] . '/images/noavatar.gif',
            );
        }
        $this->complete_status( $buddies );
        return $buddies;
    }

    function rooms($uid) {
        $ids = array();
		$query = DB::query("SELECT qid FROM ".DB::table("qun_user")." WHERE uid=$uid");
		while ($value = DB::fetch($query)){
			$ids[] = $value['qid'];
		}
        return $this->rooms_by_ids($uid, $ids);
    }

    /**
     * Get rooms by ids 
     * $ids: Get all imuser rooms if not given.
     *
     */
    function rooms_by_ids($uid, $ids){
        global $GLOBALS;
        $rooms = array();
        if( count($ids) === 0 ) return $rooms;

		$ids = implode( ",",  $ids);
        $query = DB::query("SELECT qid, name, icon, member_num, `desc` FROM ".DB::table('qun')." WHERE qid IN ($ids)");
        while ($value = DB::fetch($query)){
            $rooms[] = (object)array(
                "id" => $value['qid'],
                "nick" => webim_to_utf8(strtoupper(CHARSET), $value['name']),
                "url" => jurl('index.php?mod=qun&qid='.$value['qid']),
                "pic_url" => $GLOBALS['_J']['site_url'] . ($value['icon'] ? $value['icon'] : "/images/qun_def_b.jpg"),
                'default_pic_url' => $GLOBALS['_J']['site_url'] . '/images/qun_def_b.jpg',
                "status" => $value['desc'],
                "all_count" => $value['member_num'],
                "blocked" => false,
            );
        }
        return $rooms;
    }

    function members($room) {
        $query = DB::query("SELECT q.uid, m.nickname FROM " . DB::table('qun_user') . " q
            LEFT JOIN " . DB::table('members') . " m ON q.uid = m.uid 
            WHERE qid = $room");
        $members = array();
        while( $value = DB::fetch($query) ) {
            $members[] = (object)array(
                'id'   =>  $value['uid'],
                'nick' =>  webim_to_utf8(strtoupper(CHARSET), $value['nickname']),
            );
        }
        return $members;
    }

    function menu($uid) {
        global $GLOBALS;
        return array(
            array("title" => 'newtopic', "icon" =>$GLOBALS['_J']['site_url']."/images/lefticon/mygroup_icon.jpg","link" => jurl('index.php?mod=topic&code=new')),
            array("title" => 'newreply', "icon" =>$GLOBALS['_J']['site_url']."/images/lefticon/mychannelb_icon.jpg","link" => jurl('index.php?mod=topic&code=newreply')),
            array("title" => 'hotreply', "icon" =>$GLOBALS['_J']['site_url']."/images/lefticon/live_icon.jpg","link" => jurl('index.php?mod=topic&code=hotreply')),
            array("title" => 'hotforward', "icon" =>$GLOBALS['_J']['site_url']."/images/lefticon/left_define_icon.png","link" => jurl('index.php?mod=topic&code=hotforward'))
        );
    }

    /**
     * notifications of current user
     */
    function notifications($uid){
        $member = jsg_member_info($uid);
        $notice = array();
        if($member['newpm']>0){
            $notice[] = array('text'=>$member['newpm'].'条新私信','link'=>jurl('index.php?mod=pm&code=list'));
        }
        if($member['comment_new']>0){
            $notice[] = array('text'=>$member['comment_new'].'条新评论','link'=>jurl('index.php?mod=topic&code=mycomment'));
        }
        if($member['fans_new']>0){
            $notice[] = array('text'=>$member['fans_new'].'人关注了我','link'=>jurl('index.php?mod='.$member['username'].'&code=fans'));
        }
        if($member['at_new']>0){
            $notice[] = array('text'=>$member['at_new'].'人@提到我','link'=>jurl('index.php?mod=topic&code=myat'));
        }
        if($member['dig_new']>0){
            $notice[] = array('text'=>'有'.$member['dig_new'].'人'.$GLOBALS['_J']['config']['changeword']['dig'].'了你','link'=>jurl('index.php?mod='.$member['username'].'&type=mydig'));
        }
        if($member['channel_new']>0){
            $notice[] = array('text'=>'频道新增'.$member['channel_new'].'条内容','link'=>jurl('index.php?mod=topic&code=channel&orderby=post'));
        }
        if($member['vote_new']>0){
            $notice[] = array('text'=>'投票新增'.$member['vote_new'].'人参与','link'=>jurl('index.php?mod=vote&view=me&filter=new_update&uid='.$member['uid']));
        }
        if($member['qun_new']>0){
            $notice[] = array('text'=>'微群新增'.$member['qun_new'].'条内容','link'=>jurl('index.php?mod=topic&code=qun'));
        }		
        if($member['event_new']>0){
            $notice[] = array('text'=>'活动新增'.$member['event_new'].'人报名','link'=>jurl('index.php?mod=event&code=myevent&type=new'));
        }		
        if($member['topic_new']>0){
            $notice[] = array('text'=>'新增'.$member['topic_new'].'条话题内容','link'=>jurl('index.php?mod=topic&code=tag'));
        }        
        if($member['event_post_new']>0){
            $notice[] = array('text'=>'新增'.$member['event_post_new'].'个关注的活动','link'=>jurl('index.php?mod=topic&code=other&view=event'));
        }           	
        if($member['fenlei_post_new']>0){
            $notice[] = array('text'=>'新增'.$member['fenlei_post_new'].'条分类信息','link'=>jurl('index.php?mod=topic&code=other&view=fenlei'));
        }
        return $notice;
    }

    /**
     * Add status to member info.
     *
     * @param array $members the member
     * @return 
     *
     */
    function complete_status( $members ) {
        if( count($members) ){
            $ids = array();
            $cache = array();
            foreach($members as $m) {
                $ids[] = $m->id;
                $cache[$m->id] = $m;
            }
            $ids = implode(",", $ids);
			$query = DB::query("SELECT uid, fans_count, topic_count FROM ".DB::table('members')." WHERE uid IN ($ids)");
            while($row = DB::fetch($query)) {
                $cache[$row['uid']]->status = "粉丝:{$row['fans_count']}&nbsp;微博:{$row['topic_count']}";
            }
        }
        return $members;
    }

    function nick( $sp ) {
        global $IMC;
        return (!$IMC['show_realname']||empty($sp['name'])) ? webim_to_utf8(strtoupper(CHARSET), $sp['nickname']) : $sp['name'];
    }

}

?>
