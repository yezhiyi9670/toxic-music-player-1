<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?><?php

checkROOT();
header('Content-Type: text/plain');
tpl("admin/query_comp");
