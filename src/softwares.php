<?php
require 'pbs.php';
class software {
  function __construct($software,$conf) {
    $this->tool=new tool();
    $this->software=$software;
    $this->conf=$conf;
    $pbs_conf=$this->create_pbs_conf();
    $this->pbs=new pbs($pbs_conf);
  }
  function create_pbs_conf() {
    $dict=array();
    $keys=array("LOGFILE" => '',
		"PBS_EXEC" => '',
		"TMP_DIR" => '',
		"SESSIONDIR" => '',
		'PRESUBMIT_TYPE' => "",
		'RUN_TYPE' => ""		);
    foreach($keys as $k => $v) {
      $dict[$k]=$this->conf->get_value($k,$v);
    }
    $software_home=$this->tool->path_join($this->conf->get_value("SERVICE_HOME","/var/spool/web-pbs"),'software',$this->software);
    $software_config_file=$this->tool->path_join($this->conf->get_value("SERVICE_HOME","/var/spool/web-pbs"),'software',$this->software,'config');
    $dict["PRESUBMIT_TEMPLATE"]=$this->tool->path_join($software_home,'presubmit');
    $dict["RUN_TEMPLATE"]=$this->tool->path_join($software_home,'run.sh');
    
    $b=$this->tool->parse_file_to_dict($software_config_file);
    foreach($b as $k => $v) {
      $dict[$k]=$v;
    }
    
    $pbs_conf=new conf($dict);
    return $pbs_conf;
  }
  function software_qsub($a=array()) {
    $a['SOFTWARE']=$this->software;
    $env_file=$this->tool->path_join($this->conf->get_value("SERVICE_HOME"),'software',$this->software,'env');
    $env=$this->tool->parse_file_to_dict($env_file);
    foreach ($env as $k => $v) {
      if (array_key_exists($k,$a)) {
	continue;
      }
      $a[$k]=$v;
    }
    return $this->pbs->qsub($a);
  }
};



$d=array("TEST"=>"LICENSE",
	 "FILES"=> array("/tmp/a",
			 "/tmp/b",
			 "/tmp/c"),
	 "LICENSE"=>"6200@pbs",
	 "USERNAME"=>"pbsadmin"
	 );


$a=new software('abaqus',$global_conf);

print $a->software_qsub($d);

