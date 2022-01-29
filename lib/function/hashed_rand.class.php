<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?><?php

class HashedRand {
	private $seed = "";
	private $readpos = 0;

	public function __construct($x = "") {
		$this->seed = strval($x);
		$this->readpos = 60;
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
		if($this->readpos == 60) {
			// 不能再读取了
			$this->pass_str();
			$this->readpos = 0;
		}
		$x = $this->seed;
		$r = 0;
		for($i = $this->readpos; $i < $this->readpos + 5; $i++) {
			$r = $r * 16 + strpos($hex,$x[$i]);
		}
		$this->readpos += 6;
		return $r * 1.0 / 1048576;
	}
}
