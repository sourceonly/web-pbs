<?php
require 'constant.php';
require 'parser.php';
class g_conf {
  var $d_conf;
  var $conf;
  function __construct() {
    $this->d_conf=new d_conf();
    $conf_file=$this->d_conf->get_default_key('G_CONF_FILE');
    $this->conf=parse_conf_file($conf_file);
  }
  function get_key($key) {
    if (array_key_exists($key,$this->conf)) {
      return $this->conf[$key];
    }
    $v=$this->d_conf->get_default_key($key);

    if ($v=="") {
      return $this->d_conf->get_default_key("G_" . $key);
    } else {
      return $v;

    }
  }
  function get_software_dir($software) {
    $service_home=$this->get_key("SERVICE_HOME");
    return path_join($service_home,"software",$software);
  }
  function get_software_presubmit($software) {
    $service_home=$this->get_key("SERVICE_HOME");
    return path_join($service_home,"software",$software,$this->get_key("PRESUBMIT_SCRIPT"));
  }
  function get_software_script($software) {
    $service_home=$this->get_key("SERVICE_HOME");
    return path_join($service_home,"software",$software,$this->get_key("RUN_SCRIPT"));
  }

  function get_temp_dir() {
    $service_home=$this->get_key("SERVICE_HOME");
    return path_join($service_home,"temp");
  }
  function get_submission_dir() {
    $service_home=$this->get_key("SERVICE_HOME");
      return path_join($service_home,"sessions");
  }


  function get_software() {
    $service_home=$this->get_key("SERVICE_HOME");
    $software_home=path_join($service_home,"software");
    $s=explode("\n",shell_exec("sudo ls " . $software_home . " 2>/dev/null"));
    $software=remove_empty_value($s);
    return $software;
  }
}

// $a=new g_conf();
// print $a->get_software_presubmit('abaqus');
// print "\n";
// print $a->get_software_script('abaqus');
//print $a->get_temp_dir();

?>
