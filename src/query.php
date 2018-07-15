<?php
require 'pbs.php';
$pbs=new pbs($global_conf);
if ($_SERVER["REQUEST_METHOD"]=="GET") {
  $args=array();
  if (sizeof($_GET)==0) {
    exit(0); // nothing would return;
  }

  if (array_key_exists('command',$_GET)) {
    $command=$_GET['command'];
    foreach($_GET as $k => $v) {
      $args[$k]=$v;
    }
    unset($args['command']);
  } else {
    exit(0);
  }

  if ( $command=="pbsnodes" )  {
    print json_encode($pbs->pbsnodes($args));
  } elseif ($command=="qstat" ) {
    print json_encode($pbs->qstat($args));
  } elseif ($command=="qselect") {
    print json_encode($pbs->qselect($args));
  } elseif ($command=="qdel") {
    $pbs->qdel($args);
    exit(0);
  }
  
 

};

if ($_SERVER["REQUEST_METHOD"]=="POST") {
  exit(0);
}




