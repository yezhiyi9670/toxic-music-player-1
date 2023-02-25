<?php

namespace RedCat\JSON5;
class Parser {
  protected $json5;
  protected $assoc;
  protected $keepComments;
  public function __construct($json5) {
    $this->json5 = $json5;
  }
  public function parse($assoc = false, $keepComments = false) {
    $this->assoc = $assoc;
    $this->keepComments = $keepComments;
    $json5 = $this->json5;
    return $this->parse_json5($json5);
  }
  protected function returnObject($obj) {
    if ($this->assoc) {
      return (array)$obj;
    }
    return (object)$obj;
  }
  protected function parse_json5(&$json5) {
    $this->parse_comment($json5, $comments);
    $json5 = trim($json5);
    $c = substr($json5, 0, 1);

    if ($c === "{") {
      return $this->parse_object($json5);
    }
    if ($c === "[") {
      return $this->parse_array($json5);
    }
    if ($c === '"' || $c === "'") {
      return $this->parse_string($json5);
    }
    if (strncasecmp($json5, "null", 4) === 0) {
      $json5 = substr($json5, 4);
      return null;
    }
    if (strncasecmp($json5, "true", 4) === 0) {
      $json5 = substr($json5, 4);
      return true;
    }
    if (strncasecmp($json5, "false", 5) == 0) {
      $json5 = substr($json5, 5);
      return false;
    }
    if (strncasecmp($json5, "infinity", 8) == 0) {
      $json5 = substr($json5, 8);
      return INF;
    }
    if (preg_match('/^(0x[a-zA-Z0-9]+)/', $json5, $m)) {
      $num = $m[1];
      $json5 = substr($json5, strlen($num));
      return intval($num, 16);
    }
    if (preg_match('/^((\+|\-)?\d*\.?\d*[eE]?(\+|\-)?\d*)/', $json5, $m)) {
      $num = $m[1];
      $json5 = substr($json5, strlen($num));
      return floatval($num);
    }
    $json5 = substr($json5, 1);
    return $this->returnObject($json5);
  }

  protected function parse_comment(&$json5, &$res = []) {
    while ($json5 !== "") {
      $c2 = substr(ltrim($json5), 0, 2);
      if ($c2 === "/*") {
        $comment = $this->commentToken($json5, "*/",["\n","\r","\t"," "]);
        if ($this->keepComments) {
          $res[] = new Comment(rtrim($comment,"\t\s"));
		}
        continue;
      }
      if ($c2 === "//") {
        $comment = $this->commentToken($json5, "\n", ["\n","\r","\t"," "], "\n");
        if ($this->keepComments) {
          $res[] = new Comment(rtrim($comment,"\t\s"));
		}
        continue;
      }
      break;
    }
    return $json5;
  }

  protected function parse_string(&$json5) {
    $str = "";
    $flag = substr($json5, 0, 1);
    $json5 = substr($json5, 1);
    while ($json5 !== "") {
      $c = mb_substr($json5, 0, 1);
      $json5 = substr($json5, strlen($c));
      if ($c === $flag) {
        break;
      }
      if ($c === "\\") {
        if (substr($json5, 0, 2) === "\r\n") {
          $json5 = substr($json5, 2);
          $str .= "\r\n";
          continue;
        }
        if (substr($json5, 0, 1) === "\n") {
          $json5 = substr($json5, 1);
          $str .= "\n";
          continue;
        }
      }
      $str .= $c;
    }
    $res = json_decode('"'.$str.'"');
    if (is_null($res)) {
      $json = json_decode(json_encode($str));
      $res = $json->str;
    }
    return $res;
  }

  protected  function parse_array(&$json5) {
    $json5 = substr($json5, 1);
    $res = [];
    while ($json5 !== "") {
      $this->parse_comment($json5, $res);
      $json5 = ltrim($json5);
      if (strncmp($json5, "]", 1) === 0) {
        $json5 = substr($json5, 1);
        break;
      }
      $res[] = $this->parse_json5($json5);
      $json5 = ltrim($json5);
      if (substr($json5, 0, 1) === ",") {
        $json5 = substr($json5, 1);
      }
    }
    return $this->returnObject($res);
  }

  protected function parse_object(&$json5) {
    $json5 = substr($json5, 1);
    $res = [];
    while ($json5 !== "") {
      $this->parse_comment($json5, $res);
      $ltJson5 = ltrim($json5);
      if (strncmp($ltJson5, "}", 1) === 0) {
        $json5 = substr($ltJson5, 1);
        break;
      }
      $c = substr($json5, 0, 1);
      if ($c === '"' || $c === "'") {
        $key = $this->parse_string($json5);
        $this->token($json5, ":");
      }
      else {
        $key = trim($this->token($json5, ":"));
      }
      $value = $this->parse_json5($json5);
      $res[trim($key,'"\'')] = $value;
      $json5 = ltrim($json5);
      if (strncmp($json5, ',', 1) === 0) {
        $json5 = substr($json5, 1);
      }
    }
    return $this->returnObject($res);
  }

  protected  function commentToken(&$str, $spl, $while = [], $preWhile = false) {
	  $result = '';
      if($preWhile){
        $i = 0;
        while(isset($str[$i])&&in_array($str[$i],$while)){
          $i++;
        }
        $result .= substr($str, 0, $i);
        $str = substr($str, $i);
      }
	  if($while){
		$result .= $this->token($str, $spl);
		$i = 0;
		while(isset($str[$i])&&in_array($str[$i],$while)){
		  $i++;
		}
      }
	  $result .= $spl.substr($str, 0, $i);
	  $str = substr($str, $i);
	  return $result;
  }
  
  protected  function token(&$str, $spl) {
    $i = strpos($str, $spl);
    if ($i === false) {
      $result = $str;
      $str = "";
      return $result;
    }
    $result = substr($str, 0, $i);
    $str = substr($str, $i+strlen($spl));
    return $result;
  }

}
