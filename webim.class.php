<?php
if(!defined('IN_JISHIGOU'))
{
    exit('invalid request');
}
class plugin_webim {

	function global_footer() {
		return '<script src="plugin/webim/custom.js.php" type="text/javascript"></script>';
	}
}

