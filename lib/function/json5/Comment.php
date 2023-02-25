<?php
namespace RedCat\JSON5;
class Comment {
  protected $comment;
  public function __construct($comment) {
    $this->comment = $comment;
  }
  public function __toString() {
    return $this->comment;
  }
}