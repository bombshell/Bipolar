<?php 

$servers[ 'chat.freenode.net' ][ 'ident' ] = 'bombbox';
$servers[ 'chat.freenode.net' ][ 'realname' ] = 'Bombshellnet.org';
$servers[ 'chat.freenode.net' ][ 'port' ] = 6667;
$servers[ 'chat.freenode.net' ][ 'conn_nonalive' ] = 1; /*** Seconds where a connection has declared disconnected ***/

//$channels[] = '#bombshellnet-bots';
$channels[] = '#bombshellnet';

$nicks[] = 'bombbot';
$nicks[] = 'bombbot_';

$modules[] = 'remindUsers.php';

/*** Scripts to run ever x seconds ***/
//$scripts[] = 'Fridget_Monitor.php';
$runtime = 2; /*** scripts run every $runtime ***/

$khandler_autorejoin = true;
$debug = 2;

$botusers = array( 'skill_pain!~whocares2@shellium/supporter/unofficialsuse' );

/*** Log output here if running in Deamon Mode ***/
$log = 'bot.2.log';

/*** DO NOT EDIT BELOW ***/
$php_bin = '/usr/local/bin/php';
/*** Default Modues for the bot ***/
$modules[] = 'system.php';
$modules[] = 'Fridget_KICKHandler.php';
$modules[] = 'Fridget_ChanNames.php';