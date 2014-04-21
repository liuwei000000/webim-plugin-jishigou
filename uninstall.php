<?php

defined('IN_JISHIGOU') or exit('invalid request');

$sql = <<<EOF

DROP TABLE IF EXISTS `{jishigou}webim_settings`;
DROP TABLE IF EXISTS `{jishigou}webim_histories`;
DROP TABLE IF EXISTS `{jishigou}webim_rooms`;
DROP TABLE IF EXISTS `{jishigou}webim_members`;
DROP TABLE IF EXISTS `{jishigou}webim_blocked`;
DROP TABLE IF EXISTS `{jishigou}webim_visitors`;

EOF;

?>
