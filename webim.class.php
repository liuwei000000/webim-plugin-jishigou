<?php

defined('IN_JISHIGOU') or exit('invalid request');

class plugin_webim {

	function global_footer() {
		return '<script src="plugin/webim/index.php?action=boot" type="text/javascript"></script>';
	}

}

