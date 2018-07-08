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

function parse_global_config_file() {
	 $newconf=array();
	 $config_path="/etc/web-pbs.conf";
	 $f=fopen($config_path,"r");
	 $c=explode("\n",fread($f,filesize($config_path)));
	 fclose($f);
	 foreach($c as $l) {
	 	    if (!(trim($l) == "")) {
		    	$v=explode("=",$l,2);
			if (sizeof($v) == 2 ) {
		    	   $newconf[trim($v[0])]=trim($v[1]);
			}
		    }
	 };
	 $conf=remove_empty_key($newconf);
	 $GLOBALS['WEB_PBS_CONF']=$conf;
}

function parse_conf_file($filename) {
	 $newconf=array();
	 $f=fopen($filename,"r");
	 $c=explode("\n",fread($f,filesize($filename)));
	 fclose($f);
	 foreach($c as $l) {
	 	 $v=explode("=",$l,2);
		 if (sizeof($v) == 2 ) {
		 	 $newconf[$v[0]]=$v[1];
		 }
	 };
	 $conf=remove_empty_key($newconf);
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

function parse_softwares() {
	 $config_path=$GLOBALS['WEB_PBS_CONF']['CONFIG_PATH'];
	 $software_path=path_join($config_path,"software");
	 $s=explode("\n",shell_exec("sudo ls " . $software_path . " 2>/dev/null"));
	 $software=remove_empty_value($s);
	 $GLOBALS['SOFTWARE']=$software;
}
function get_global_conf($key) {
	 $c=$GLOBALS['WEB_PBS_CONF'];
	 if (array_key_exists($key,$c)) {
	    return $c[$key];
	 }
	 return "";
}
function get_conf_from_file($k,$f) {
	 $conf=parse_conf_file($f);
	 if (array_key_exists($k,$conf)) {
	    return $conf[$k];
	 }
	 return "";
}

function get_software_home($software) {
	 return	 path.join(get_global_conf("CONFIG_PATH"),"software",$software);
}
parse_global_config_file();
parse_softwares();



?>