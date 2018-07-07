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


var_dump(parse_file_by_line('/var/spool/web-pbs/software/abaqus/version.conf'));

?>
