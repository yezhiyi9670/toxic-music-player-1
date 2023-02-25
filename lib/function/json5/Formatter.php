<?php
namespace RedCat\JSON5;

class Formatter {
  protected $json;
  protected $indent;
  protected $linebreak;
  public function __construct($json, $indent = "\t", $linebreak = "\n") {
    $this->json = $json;
    $this->indent = $indent;
    $this->linebreak = $linebreak;
  }
  public function format($options = 0, $keepComments = false) {
    return $this->json_encode($this->json, $options, $keepComments, $this->indent, $this->linebreak);
  }

  public static function json_encode(&$value, $options = 0, $keepComments = false, $indent = "\t", $linebreak = "\n", $nested = 1) {
	    $pretty = $options&JSON_PRETTY_PRINT;
		$indent = $pretty?$indent:'';
		$linebreak = $pretty?$linebreak:'';
		if (is_null($value)) {
            return 'null';
        }
        elseif (is_scalar($value)) {
            return json_encode($value,$options);
        }
        elseif (is_array($value)) {
            $with_keys = false;
            $n = count($value);
            for ($i = 0, reset($value); $i < $n; $i++, next($value)) {
              if (key($value) !== $i) {
			    $with_keys = true;
			    break;
              }
            }
        }
        elseif (is_object($value)) {
            $with_keys = true;
			if($value instanceof \JsonSerializable){
				$value = $value->jsonSerialize();
			}
        }
        else {
            return '';
        }
        $result = [];
		$c = 0;
		foreach ($value as $key => $v) {
			if(!$v instanceof Comment){
				$c++;
			}
		}
		$i = 1;
		$lastchr = null;
		foreach ($value as $key => $v) {
			if($v instanceof Comment){
				if(!$keepComments)
					continue;
				$r = $v;
			}
			else{
				$r = '';
				if($lastchr!=$linebreak){ //don't duplicate linebreak from comments
					$r .= $linebreak;
				}
				$r .= str_repeat($indent,$nested);
				if ($with_keys) {
					if(!preg_match('/^(\w+)$/',$key)) {
						$r .= json_encode($key, $options) . ':';
					} else {
						$r .= $key . ':';
					}
					if($pretty) {
						$r .= ' ';
					}
				}
				$r .= self::json_encode($v, $options, $keepComments, $indent, $linebreak, $nested+1);
				if($i<$c){
					$r .= ',';
				}
				if($pretty&&(is_object($v)||is_array($v))){
					$r .= $linebreak;
				}
				$i++;
			}
			$result[] = $r;
			$lastchr = substr($r,-1);
		}
		$r = $with_keys?'{':'[';
		$r .= implode('', $result);
		if($lastchr!=$linebreak){ //don't duplicate linebreak from comments
			$r .= $linebreak;
		}
		if($pretty&&$nested>1){
			$r .= str_repeat($indent, $nested-1);
		}
		$r .= $with_keys?'}':']';
		return $r;
    }
}
