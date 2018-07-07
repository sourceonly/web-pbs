<?php
require 'utils.php';
function php_qselect($a=array()) {
	 $cmd='sudo /opt/pbs/default/bin/qselect';
	 foreach ($a as $k=> $v) {
	 	 $cmd = $cmd . " ". $k ." ". $v ;
	 };
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
	 $cmd='sudo /opt/pbs/default/bin/qstat';
	 foreach ($a as $k=> $v) {
	 	 $cmd = $cmd . " " . $k . " " . $v ; 
	 };
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
	 return $j;
}
function php_qstat($job_array=array(),$a=array("-fx"=>"")) {
	 if (sizeof($job_array) == 0 ) {
	    $job_array=php_qselect();
	 };
	 return php_qstat_jobarray($job_array,$a);
}

function php_qdel_one($jobid) {
	 $cmd='sudo /opt/pbs/default/bin/qdel';
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
	 $script_name=path_join($GLOBALS['WEB_PBS_CONF']['CONFIG_PATH'],'scripts',$random_name);
	 php_create_resource_head($script_name,$env);
	 php_create_env($script_name,$env);
	 php_append_script($script_name);
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
 	 $cmd="sudo -u "  . $user . " /opt/pbs/default/bin/qsub ";
	 $script_name=php_generate_qsub_scripts($software,$env);
	 $submit_dir=path_join($GLOBALS["WEB_PBS_CONF"]["CONFIG_PATH"],"sessions");
	 $cwd=getcwd();
	 chdir($submit_dir);
	 $jobid=shell_exec($cmd . $script_name );
	 chdir($cwd);
	 return $jobid;
}
$env=array();
$env['LICENSE']='6200@12345';
$env['TEST']='a b c';
print php_qsub("abaqus",$env,"pbsadmin");


?>
