<?php
define("LINE_IS_PAIR",1);
define("LINE_IS_SINGLE",2);
define("LINE_IS_BLANK",3);

class d_conf {
  var $conf;
  function __construct() {
    $this->conf['G_CONF_FILE']='/etc/web-pbs.conf';
    $this->conf['G_SERVICE_HOME']='/var/spool/web-pbs';
    $this->conf['G_PRESUBMIT_SCRIPT']='presubmit';
    $this->conf['G_PRESUBMIT_TYPE']='bash';
    $this->conf['G_RUN_SCRIPT']='run.sh';
    $this->conf['G_RUN_TYPE']='bash';
    $this->conf['G_VERSION_CONF']='executable';
  }

  function get_default_key($key) {
    if (array_key_exists($key,$this->conf)) {
      return $this->conf[$key];
    }
    return "";
  }
}
?>
