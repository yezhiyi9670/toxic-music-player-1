<?php
namespace RedCat\JSON5;
use RedCat\JSON5\RuntimeException;
use SplFileObject;
class JSON5 {
  public static function decode($json5, $assoc = false, $keepComments = false) {
    $parser = new Parser($json5);
    return $parser->parse($assoc, $keepComments);
  }
  public static function encode($json5, $options = 0, $keepComments = false) {
    $formatter = new Formatter($json5);
    return $formatter->format($options, $keepComments);
  }
  public static function decodeFile($file, $assoc = false, $keepComments = false) {
    if (!$file instanceof SplFileObject) {
      if (!file_exists($file)) {
        throw new RuntimeException("File does not exist.");
      }
      return self::decodeFile(new SplFileObject($file, "r"), $assoc, $keepComments);
    }
    $json5 = $file->fread($file->getSize());
    return self::decode($json5, $assoc, $keepComments);
  }
}
