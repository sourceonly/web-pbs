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
  
};
class conf {
  function __construct($dict=array()) {
    $this->conf=$dict;
  }
  function set_key($k,$v) {
    $this->conf[$k]=$v;
  }
  function get_value($k,$default="") {
    if (array_key_exists($k,$this->conf)) {
      return $this->conf[$k];
    }
    return $default;
  }
};



