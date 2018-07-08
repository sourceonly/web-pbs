<?php
require 'utils.php';

function get_pbs_exec() {
	 $pbs_exec=get_global_conf("PBS_EXEC");
	 if ($pbs_exec == "") {
	    return '/opt/pbs/default';
	 }
	 return $pbs_exec;
	   
}

function make_pbs_cmd($pbs_cmd,$user="") {
	 if (!( $user == "" )) {
	    $user_cmd=" -u " . $user;
	 } else {
	    $user_cmd="";
	 }
	 return 'sudo ' . $user_cmd . " " . path_join(get_pbs_exec(),'bin',$pbs_cmd) . " ";
}

function make_arg_cmd($a=array()) {
	$cmd="";
	foreach ($a as $k => $v) {
		$cmd = $cmd . " " . $k . " " . $v;
	}
	return $cmd . " ";
}


function php_pbsnodes($a=array("-av" => "")) {
	 $cmd=make_pbs_cmd('pbsnodes');
	 $cmd=$cmd . " " . make_arg_cmd($a);
	 $c=shell_exec($cmd);
	 $n_out=array();

	 $l=explode("\n",$c);
	 foreach($l as $v) {
	 	    if (strlen($v) >0 ) {
		       		$p=explode("=",$v,2);
				if (sizeof($p) == 1) {
				   $current=$v;
				   $n_out[$current]=array();
				} else {
				   $n_out[$current][trim($p[0])]=trim($p[1]);
				}
		    };

	 };
	 return $n_out;
}


function php_qselect($a=array()) {
	 $cmd=make_pbs_cmd('qselect');
	 $cmd = $cmd . " " . make_arg_cmd($a);
	 $jobs=shell_exec($cmd . " 2>/dev/null");
	 return	remove_empty_value(explode("\n",$jobs));
};
function php_qstat_jobarray($jobid=array(),$a=array()) {
	$job_array=array();
	foreach ($jobid as $job) {
		$j=php_qstat_one_job($job,$a);
		$job_array[$j['JobID']]=$j;
	}
	return $job_array;
}
function php_qstat_one_job($jobid,$a=array()) {
	 $cmd= make_pbs_cmd('qstat');
	 $cmd= $cmd . " " . make_arg_cmd($a);
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
function php_qstat($job_array=array(),$a=array("-fx"=>"")) {
	 if (sizeof($job_array) == 0 ) {
	    $job_array=php_qselect();
	 };
	 return php_qstat_jobarray($job_array,$a);
}

function php_qdel_one($jobid) {
	 $cmd=make_pbs_cmd('qdel');
	 shell_exec($cmd . " " . $jobid);
};

function php_qdel($jobarray) {
	 foreach ($jobarray as $v) {
	 	 php_qdel_one($v);
	 }
}
function php_get_softwares() {
	 return $GLOBALS["SOFTWARE"];
}


function php_generate_qsub_scripts($software,$env) {
	 $random_name=uniqid("qsub_");
	 $script_name=path_join(get_global_conf("CONFIG_PATH"),'scripts',$random_name);
	 php_create_resource_head($script_name,$env);
	 php_create_env($script_name,$env);
	 php_append_script($script_name);
	 return $script_name;
}

function php_generate_presubmit_script($env) {
	 $random_name=uniqid("submit_");	     
	 $script_name=path_join(get_global_conf("CONFIG_PATH"),'scripts',$random_name);
	 $f=fopen($script_name,"w");
	 foreach($env as $k => $v) {
	 	      fwrite($f,"export " . $k. "=\"". $v . "\"\n");
	 }
	 

	 fclose($f);
	 return $script_name;
}
function  php_create_resource_head($script_name,$env) {
	  $f=fopen($script_name,"a");
	  fwrite($f,"#!/bin/bash\n");
	  fclose($f);
};

function php_create_env($script_name,$env) {
	 $f=fopen($script_name,"a");
	 foreach ($env as $k => $v ) {
	 	 fwrite($f,"#PBS -v " . $k . "=" . "\"" . $v . "\"" . "\n");
	 };
	 fclose($f);
}

function php_append_script($script_name) {
	 $f=fopen($script_name,"a");
	 fwrite($f,"/bin/sleep 100\n");
	 fclose($f);
}



function php_qsub($software,$env,$user) {
	 $cmd=make_pbs_cmd("qsub",$user);
	 $script_name=php_generate_qsub_scripts($software,$env);
	 $submit_dir=path_join(get_global_conf('CONFIG_PATH'),"sessions");
	 $cwd=getcwd();
	 chdir($submit_dir);
	 $jobid=shell_exec($cmd . $script_name );
	 chdir($cwd);
	 return $jobid;
}
$env=array();
$env['LICENSE']='6200@12345';
$env['TEST']='a b c';
//print php_qsub("abaqus",$env,"pbsadmin");
//var_dump(php_qstat());
//var_dump(php_pbsnodes());
//var_dump(php_qselect());
//var_dump(php_qstat());
//var_dump(php_pbsnodes());
print php_generate_presubmit_script($env);
?>
