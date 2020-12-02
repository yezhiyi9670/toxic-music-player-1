<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?><?php

class HashedRand {
	private $seed = "";

	public function __construct($x = "") {
		$this->seed = strval($x);
	}

	public function set_seed($x) {
		$this->seed = strval($x);
	}

	public function pass_str() {
		$this->seed = strtoupper(hash("SHA256",$this->seed));
		return $this->seed;
	}

	public function rand_float() {
		$hex="0123456789ABCDEF";
		$x = $this->pass_str();
		$r = 0;
		for($i=0;$i<5;$i++) {
			$r = $r * 16 + strpos($hex,$x[$i]);
		}
		return $r * 1.0 / 1048576;
	}
}
