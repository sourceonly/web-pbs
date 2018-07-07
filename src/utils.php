<?php
function remove_empty_value($a) {
	 $res_a=array();
	 foreach ($a as $k => $v ) {
	 	 if (!( $v == "")) {
		    $res_a[$k]=$v;
		 };
	 };
	 return $res_a;

}
function remove_empty_key($a) {
	 foreach ($a as $k => $v ) {
	 	 if (!( $k == "")) {
		    $res_a[$k]=$v;
		 };
	 };
	 return $res_a;
}

function parse_config_file() {
	 $newconf=array();
	 $config_path="/etc/web-pbs.conf";
	 $f=fopen($config_path,"r");
	 $c=explode("\n",fread($f,filesize($config_path)));
	 fclose($f);
	 foreach($c as $l) {
	 	    $v=explode("=",$l,2);
		    if (sizeof($v) == 2 ) {
		    		    $newconf[$v[0]]=$v[1];
		    }
	 };
	 $conf=remove_empty_key($newconf);
	 $GLOBALS['WEB_PBS_CONF']=$conf;
}

function parse_file_by_line($filename) {
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

parse_config_file();
parse_softwares();

?>