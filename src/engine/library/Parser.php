<?php

namespace Engine\Library;

class Parser {
  
  public static function getIncludes($plain) {
    $attrs = [];
    $attrRegexp = '{{INCLUDE:([a-zA-Z\-_:]+)}}';
    preg_match_all($attrRegexp, $plain, $attrs);
    return $attrs[1];
  }
}

