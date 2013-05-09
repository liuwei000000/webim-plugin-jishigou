<?php
if(!defined('IN_JISHIGOU'))
{
    exit('invalid request');
}
$sql = <<<EOF
DROP TABLE IF EXISTS {jishigou}webim_settings;
DROP TABLE IF EXISTS {jishigou}webim_histories;
EOF;
?>