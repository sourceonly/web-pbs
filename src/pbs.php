<?php
require 'conf.php';
class pbs {
  var $conf;
  function __construct() {
    $this->conf=new g_conf;
  }
  function get_pbs_exec() {
    return $this->conf->get_key("PBS_EXEC");
  }
  function make_pbs_cmd($pbs_cmd,$user="") {
    if (!( $user == "" )) {
      $user_cmd=" -u " . $user;
    } else {
      $user_cmd="";
    }
    return 'sudo ' . $user_cmd . " " . path_join($this->get_pbs_exec(),'bin',$pbs_cmd) . " ";
  }

  function make_arg_cmd($a=array()) {
    $cmd="";
    foreach ($a as $k => $v) {
      $cmd = $cmd . " " . $k . " " . $v;
    }
    return $cmd . " ";
  }
  function php_pbsnodes($a=array("-av" => "")) {
    $cmd=$this->make_pbs_cmd('pbsnodes');
    $cmd=$cmd . " " . $this->make_arg_cmd($a);
    $c=shell_exec($cmd);
    $n_out=array();

    $l=explode("\n",$c);
    foreach($l as $v) {
      $line_type=get_line_type($v);
      if ($line_type == LINE_IS_SINGLE) {
	  $current=$v;
	  $n_out[$current]=array();
      } 
      if ($line_type == LINE_IS_BLANK) {
	
      }

      if ($line_type == LINE_IS_PAIR) {
	$p=explode("=",$v,2);
	$n_out[$current][trim($p[0])]=trim($p[1]);
      }
    };
    return $n_out;
  }


  function php_qselect($a=array()) {
    $cmd=$this->make_pbs_cmd('qselect');
    $cmd = $cmd . " " . $this->make_arg_cmd($a);
    $jobs=shell_exec($cmd . " 2>/dev/null");
    return remove_empty_value(explode("\n",$jobs));
  }

  function php_qstat_one_job($jobid,$a=array()) {
    $cmd= $this->make_pbs_cmd('qstat');
    $cmd= $cmd . " " . $this->make_arg_cmd($a);
    $lines=explode("\n",str_replace("\n\t","",shell_exec($cmd . " ". $jobid . " 2>/dev/null")));
    $j=array();
    foreach ($lines as $l) {
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
    return remove_empty_key($j);
  }

  function php_qstat_jobarray($jobid=array(),$a=array()) {
    $job_array=array();
    foreach ($jobid as $job) {
      $j=$this->php_qstat_one_job($job,$a);
      $job_array[$j['JobID']]=$j;
    }
    return $job_array;
  }

  function php_qstat($job_array=array(),$a=array("-fx"=>"")) {
    if (sizeof($job_array) == 0 ) {
      $job_array=$this->php_qselect();
    };
    return $this->php_qstat_jobarray($job_array,$a);
  }

  function php_qdel_one($jobid) {
    $cmd=$this->make_pbs_cmd('qdel');
    shell_exec($cmd . " " . $jobid);
  }

  function php_qdel($jobarray) {
    foreach ($jobarray as $v) {
      $this->php_qdel_one($v);
    }
  }

  function php_generate_qsub_scripts($software,$env) {
    $random_name=uniqid("qsub_");
    $script_name=path_join($this->conf->get_temp_dir(),$random_name);
    $this->php_create_resource_head($script_name);
    $this->php_generate_presubmit_script($software,$env,$script_name);
    $this->php_generate_env($script_name,$env);
    $this->php_append_run_script($script_name,$software);
    shell_exec("sudo chmod +x " . $script_name);
    return $script_name;
  }
  function php_generate_env($qsub_script,$env) {
    
    $f=fopen($qsub_script,"a");
    foreach($env as $k=>$v) {
 	 fwrite($f,"#PBS -v " . $k . "=" . "\"" . $v . "\"" . "\n");
    }
    
    fclose($f);
  }
  function php_append_run_script($qsub_script,$software) {
    $run_script=$this->conf->get_software_script($software);
    $f=fopen($run_script,"r");
    $c=fread($f,filesize($run_script));
    fclose($f);
    $f=fopen($qsub_script,"a");
    fwrite($f,$c);
    fclose($f);
  }

  function php_generate_presubmit_script($software,$env,$qsub_script) {
    $random_name=uniqid("pre_");
    $script_name=path_join($this->conf->get_temp_dir(),$random_name);
    $f=fopen($script_name,"w");
    fwrite($f,"#!/bin/bash\n");
    foreach ($env as $k => $v) {
      fwrite($f, "export " . $k . "=\"".$v."\"\n");
    }
    fwrite($f, $this->conf->get_software_presubmit($software) . " 1>>" . $qsub_script . " 2>" . $script_name . ".log");
    fclose($f);
    shell_exec("sudo chmod +x " . $script_name);
    shell_exec($script_name);
    return $script_name;
  }
  function php_create_resource_head($script_name) {
    $f=fopen($script_name,"w");
    fwrite($f,"#!/usr/bin/env ". $this->conf->get_key("RUN_TYPE") . "\n");
    fclose($f);
  }

  function php_qsub($software,$env,$user) {
    $cmd=$this->make_pbs_cmd("qsub",$user);
    $script_name=$this->php_generate_qsub_scripts($software,$env);
    $submit_dir=$this->conf->get_submission_dir();
    $cwd=getcwd();
    chdir($submit_dir);
    $jobid=shell_exec($cmd . $script_name );
    chdir($cwd);
    return $jobid;
  }

  

}
$p=new pbs();

// $a=array("-f" => "", "-l" => "select=1:ncpus=8");
// print $p->get_pbs_exec();
// print $p->make_pbs_cmd("qstat");
// print "\n";
// print $p->make_pbs_cmd("qstat","pbsadmin");
// print "\n";
// var_dump( $p->php_pbsnodes());
// var_dump($p->php_qselect());
// var_dump($p->php_qstat(array("100")));
// var_dump($p->php_qstat());
// $p->php_qdel(array("120","121"));
$env=array("test"=>"a","LICENSE"=>"9200@localohst");
print $p->php_qsub("abaqus",$env,"pbsadmin");
?>
