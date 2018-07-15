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
    $keys=array("LOGFILE" => '/var/spool/web-pbs/logs/execute.log',
		"PBS_EXEC" => '/opt/pbs/default',
		"TMP_DIR" => '/var/spool/web-pbs/scripts',
		"SESSIONDIR" => "/var/spool/web-pbs/sessions",
		'PRESUBMIT_TYPE' => "/bin/bash",
		//		'PRESUBMIT_TEMPLATE' => "/var/spool/web-pbs/default/presubmit",
		'RUN_TYPE' => "/bin/bash",
		//		"RUN_TEMPLATE" => "/var/spool/web-pbs/default/run.sh"
		);
    foreach($keys as $k => $v) {
      $dict[$k]=$this->conf->get_value($k,$v);
    }
    $software_home=$this->tool->path_join($this->conf->get_value("SERVICE_HOME","/var/spool/web-pbs"),'software',$this->software);
    $dict["PRESUBMIT_TEMPLATE"]=$this->tool->path_join($software_home,'presubmit');
    $dict["RUN_TEMPLATE"]=$this->tool->path_join($software_home,'run.sh');
    $pbs_conf=new conf($dict);
    return $pbs_conf;
  }
  
};



$d=array("TEST"=>"LICENSE",
	 "FILES"=> array("/tmp/a",
			 "/tmp/b",
			 "/tmp/c"),
	 "LICENSE"=>"6200@pbs",
	 "USERNAME"=>"pbsadmin"
	 );

$conf=new conf();
$a=new software('abaqus',$conf);

print $a->pbs->qsub($d);

