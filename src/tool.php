<?php
class tool {
  function __construct() {
    
  }
  function path_join() {
    $args = func_get_args();
    $paths = array();

    foreach($args as $arg) {
      $paths = array_merge($paths, (array)$arg);
    }

    foreach($paths as &$path) {
      $path = trim($path, '/');
    }

    if (substr($args[0], 0, 1) == '/') {
      $paths[0] = '/' . $paths[0];
    }
    return join('/', $paths);
  }
  function parse_file_to_dict($file_name) {
    if (!file_exists($file_name))  {
      return array();
    }
    $a=array();
    $c=file_get_contents($file_name) ;
    $lines=explode("\n",$c);
    foreach ($lines as $l) {
      if (trim($l) == "") {
	continue;
      }
      $p=explode("=",$l,2);
      if (sizeof($p) == 2) {
	$a[trim($p[0])]=trim($p[1]);
      }
    }
    return $a;
  }
};
class conf {
  function __construct($dict=array()) {
    $this->conf=$dict;
  }
  function set_key_value($k,$v) {
    $this->conf[$k]=$v;
  }
  function get_value($k,$default="") {
    if (array_key_exists($k,$this->conf)) {
      return $this->conf[$k];
    }
    return $default;
  }
};



