<?php
require 'constant.php';
class pbs {
  function __construct($conf) {
    $this->tool=new tool();
    $this->conf=$conf;
    $this->output="";

  }

  function get_user($a=array()) {
    $user="";
    if (array_key_exists('-u',$a)) {
      return $a['-u'];
    }
  }
  
  function make_pbs_cmd($pbs_cmd,$no_arg="",$a=array()) {
    $user=$this->get_user($a);
    if (!( $user == "" )) {
      $user_cmd=" -u " . $user;
    } else {
      $user_cmd="";
    }
    return 'sudo ' . $user_cmd . " " . $this->tool->path_join($this->conf->get_value('PBS_EXEC'),'bin',$pbs_cmd) . $this->make_arg_cmd($a) . " " . $no_arg;
  }


  function run_pbs_cmd($pbs_cmd,$no_args="",$a=array()) {
    return $this->run_cmd($this->make_pbs_cmd($pbs_cmd,$no_args,$a));
  }
  function make_arg_cmd($a=array()) {
    $cmd="";
    foreach ($a as $k => $v) {
      $cmd = $cmd . " " . $k . " " . $v;
    }
    return $cmd . " ";
  }

  function write_log($info) {
    $file=$this->conf->get_value('LOGFILE');
    $f=fopen($file,"a");
    fwrite($f,date(DATE_ATOM) . "\t" . $info . "\n");
    fclose($f);
  }
    
    

  
  function run_cmd($cmd) {
    $logfile=$this->conf->get_value('LOGFILE');
    $descriptorspec = array(
			    1 => array("pipe", "w"),  // stdout is a pipe that the child will write to
			    2 => array("file", $logfile, "a") // stderr is a file to write to
			    );

    $cwd = $this->conf->get_value("SESSIONDIR","/tmp");
    $env = array('some_option' => 'aeiou');
    $this->write_log("executing " . $cmd . " in " . $cwd);
    $process = proc_open($cmd, $descriptorspec, $pipes, $cwd, $env);
    if (is_resource($process)) {
      $this->output=stream_get_contents($pipes[1]);
      fclose($pipes[1]);
      
      // It is important that you close any pipes before calling
      // proc_close in order to avoid a deadlock
      $return_value = proc_close($process);
      $this->write_log("executed  " . $cmd . " code " . strval($return_value));
      return $return_value;
    }
    $this->write_log("executed  " . $cmd . " failed");
    Die();
  }

  function pbsnodes ($a=array("-av"=>"")) {
    $rc=$this->run_pbs_cmd("pbsnodes","",$a);
    $c=$this->output;
    $l=explode("\n",$c);
    $node=array();
    foreach ($l as $v) {
      if (trim($v)=="") {
	continue;
      }
      $lp=explode("=",$v,2);
      if (sizeof($lp)==1) {
	$node[trim($v)]=array();
	$current=trim($v);
	continue;
      }
      if (sizeof($lp)==2) {
	$node[$current][trim($lp[0])]=trim($lp[1]);
      }
    }
    return $node;
  }
  function qselect($a=array()) {
    $this->run_pbs_cmd('qselect',"",$a);
    $c=$this->output;
    $jobs=array();
    foreach(explode("\n",$c) as $v) {
      if (trim($v) == "") continue;
      $jobs[]=$v;
    }
    return $jobs;

  }
  function qstat_one_job($jobid,$a=array("-fx"=>"")) {
    $this->run_pbs_cmd('qstat',$jobid,$a);
    $c=$this->output;
    $lines=explode("\n",str_replace("\n\t","",$c));

    $j=array();
    foreach ($lines as $l) {
      if (trim($l)=="") continue;
      $t=explode("=",$l,2);
      if (sizeof($t)==1) {
	if (strlen($t[0]) > 0) {
	  $j_name=explode(":",$t[0]);
	  $j['JobID']=$j_name[1];
	};
      } else {
	$j[$t[0]]=$t[1];
      }
    };
    return $j;
  }
  function qstat_jobarray($jobid=array(),$a=array()) {
    $job_array=array();
    foreach ($jobid as $job) {
      $j=$this->qstat_one_job($job,$a);
      $job_array[$j['JobID']]=$j;
    }
    return $job_array;
  }
  function qstat($job_array=array(),$a=array("-fx"=>"")) {
    if (sizeof($job_array) == 0 ) {
      $job_array=$this->qselect();
    };
    return $this->qstat_jobarray($job_array,$a);
  }

  function qdel_one($jobid) {
    $this->run_pbs_cmd('qdel',$jobid,array());
  }

  function qdel($jobarray) {
    foreach ($jobarray as $v) {
      $this->qdel_one($v);
    }
  }

  function generate_qsub_scripts($dict) {
    $d=$this->generate_pair($dict);
    $random_name=uniqid("qsub_");
    $tmpdir=$this->conf->get_value("TMP_DIR","/tmp");
    $script_name=$this->tool->path_join($tmpdir,$random_name);
    
    $run_type=$this->conf->get_value("RUN_TYPE","/bin/bash");
    $f=fopen($script_name,"w");
    fwrite($f,"#!".trim($run_type)."\n");
    fclose($f);
    shell_exec("chmod +x " . $script_name );
    
    $this->generate_presubmit_script($d,$script_name);
    $this->generate_env($script_name,$d);
    $this->append_run_script($script_name);
    
    return $script_name;
  }

  function append_run_script($qsub_script) {
    $run_template=$this->conf->get_value("RUN_TEMPLATE");
    $f=fopen($run_template,"r");
    $c=fread($f,filesize($run_template));
    fclose($f);
    $f=fopen($qsub_script,"a");
    fwrite($f,$c);
    fclose($f);
  }

  function generate_pair($dict) {
    $d=array();
    foreach ($dict as $k => $v) {
      if ( gettype($v) == gettype(array())) {
	$d[$k]=implode(";",$v);
	continue;
      }
      if (gettype($v)==gettype("")) {
	$d[$k]=$v;
	continue;
      }

    }
    return $d;
  }
  function generate_env($qsub_script,$d) {
    $f=fopen($qsub_script,"a");
    fwrite($f,"#PBS -v ");
    foreach($d as $k => $v ) {
      fwrite($f, "\"" .  $k . "=" . "'" . $v . "'" . "\",");
    }
    fwrite($f,"\n");
    fclose($f);
  }

  function generate_presubmit_script($d,$qsub_script) {
    $random_name=uniqid("pre_");
    $tmpdir=$this->conf->get_value("TMP_DIR","/tmp");
    $script_name=$this->tool->path_join($tmpdir,$random_name);
    $presubmit_type=$this->conf->get_value("PRESUBMIT_TYPE");
    $presubmit_script=$this->conf->get_value("PRESUBMIT_TEMPLATE");
    
    $f=fopen($script_name,"w");
    fwrite($f,"#!/bin/bash\n"); 
    foreach ($d as $k => $v) {
      fwrite($f, "export " . $k . "=\"".$v."\"\n");
    }
    fwrite($f, $presubmit_type . " " . $presubmit_script . " "  .  " 1>>" . $qsub_script . " 2>" . $script_name . ".log");
    fclose($f);
    shell_exec("sudo chmod +x " . $script_name);
    shell_exec($script_name);
    return $script_name;
  }

  function qsub($d=array()) {

    $script_name=$this->generate_qsub_scripts($d);

    if (array_key_exists("USERNAME",$d)) {
      $a=array("-u" => $d["USERNAME"]);
    } else {
      $a=array();
    }
    $this->run_pbs_cmd('qsub',$script_name,$a);
    $jobid=$this->output;
    return $jobid;

  }

    

  

}

// $dict=array();
// $dict['LOGFILE']='/var/spool/web-pbs/logs/execute.log';
// $dict['PBS_EXEC']='/opt/pbs/default';
// $dict['TMP_DIR']="/var/spool/web-pbs/scripts";
// $dict['SESSIONDIR']="/var/spool/web-pbs/sessions";
// /* these should be setup while qsub */
// $dict['PRESUBMIT_TYPE']="/bin/bash";
// $dict['PRESUBMIT_TEMPLATE']="/var/spool/web-pbs/default/pre.sh";
// $dict['RUN_TYPE']="/bin/bash";
// $dict["RUN_TEMPLATE"]="/var/spool/web-pbs/default/run.sh";


//$conf=new conf($dict);
  
//$a=new pbs($conf);
//var_dump($a->pbsnodes(array("-av"  => "")));
//var_dump($a->qselect(array("-xu" => 'pbsadmin')));
//var_dump($a->qstat());
//$a->qdel(array("232"));

//$a->run_pbs_cmd('qstat',"", array("-fx"  => ""));
//$a->run_pbs_cmd('qselect',"", array("-x"  => ""));
//$a->run_pbs_cmd('qselect',"", array("-u"  => "pbsadmin","-x" => ""));
//print $a->output;
// $d=array("TEST"=>"LICENSE",
// 	 "FILES"=> array("/tmp/a",
// 			 "/tmp/b",
// 			 "/tmp/c"),
// 	 "LICENSE"=>"6200@pbs",
// 	 "USERNAME"=>"pbsadmin"
// 	 );

// print $a->qsub($d);

?>
