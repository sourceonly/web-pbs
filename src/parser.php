<?php
function remove_empty_value($a) {
  $res_a=array();
  foreach ($a as $k => $v ) {
    if (!( trim($v) == "")) {
      $res_a[trim($k)]=trim($v);
    };
  };
  return $res_a;

  }
function remove_empty_key($a) {
  foreach ($a as $k => $v ) {
    if (!( trim($k) == "")) {
      $res_a[trim($k)]=trim($v);
    };
  };
  return $res_a;
}

function get_line_type($line) {
  if (trim($line)=="") {
    return LINE_IS_BLANK;
  }
  $a=explode("=",$line,2);
  if (sizeof($a)==1) {
    return LINE_IS_SINGLE;
  }
  if (sizeof($a)==2) {
    return LINE_IS_PAIR;
  }
}

function parse_conf_file($file) {
  $conf=array();
  $f=fopen($file,"r");
  $c=fread($f,filesize($file));
  fclose($f);
  $lines=explode("\n",$c);
  foreach ($lines as $l) {
    if (get_line_type($l)==LINE_IS_PAIR) {
      $p=explode("=",$l,2);
      $conf[trim($p[0])]=trim($p[1]);
    }
  }
  return $conf;
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

?>
