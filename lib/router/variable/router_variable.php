<?php
namespace WMSDFCL\RouterFramework\router_variable;
?><?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?><?php

function NS() {
	return "WMSDFCL\\RouterFramework\\router_variable\\";
}

function check_url_variable($type, $cont) {
	if(function_exists(NS() . 'check_url_variable_' . $type)) {
		return (NS() . 'check_url_variable_' . $type)($cont);
	}
	return false;
}

function check_url_variable_integer($cont) {
	if(preg_match('/^(\d+)$/', $cont)) {
		return $cont;
	}
	return false;
}

function check_url_variable_word($cont) {
	if(preg_match('/^(\w+)$/', $cont)) {
		return $cont;
	}
	return false;
}

function checkExtPath($cont, $name) {
	if($cont == $name) {
		return $cont;
	}
	if(preg_match('/^' . $name . '\.(\w+)$/', $cont)) {
		return $cont;
	}
	return false;
}

function check_url_variable_audiopath($cont) {
	return checkExtPath($cont, 'audio');
}

function check_url_variable_backpath($cont) {
	return checkExtPath($cont, 'background');
}

function check_url_variable_avatarpath($cont) {
	return checkExtPath($cont, 'avatar');
}
