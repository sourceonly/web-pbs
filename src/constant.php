<?php
require 'tool.php';
$global_conf=new conf(array());
$t=new tool();
$global_conf->set_key_value("SERVICE_HOME","/var/spool/web-pbs");
$global_conf->set_key_value("PBS_EXEC","/opt/pbs/default");
$global_conf->set_key_value("LOGFILE",$t->path_join($global_conf->get_value("SERVICE_HOME"),"logs","execute.log"));
$global_conf->set_key_value("TMP_DIR",$t->path_join($global_conf->get_value("SERVICE_HOME"),"scripts"));
$global_conf->set_key_value("SESSIONDIR",$t->path_join($global_conf->get_value("SERVICE_HOME"),"sessions"));
$global_conf->set_key_value("PRESUBMIT_TYPE","/bin/bash");
$global_conf->set_key_value("RUN_TYPE","/bin/bash");




