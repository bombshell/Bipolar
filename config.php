<?php 
require '/root/app/BBackend/Classes/System-Users.php';
//$users = new System_Users( '' , $bsUserId)

$servers[ 'irc.fossnet.info' ][ 'ident' ] = 'bombbox';
$servers[ 'irc.fossnet.info' ][ 'realname' ] = 'Bombshellnet.org';
$servers[ 'irc.fossnet.info' ][ 'port' ] = 6667;
$servers[ 'irc.fossnet.info' ][ 'conn_nonalive' ] = 1; /*** Seconds where a connection has declared disconnected ***/

$channels[] = '#bombshellnet-bots';
//$channels[] = '#bombshellnet';

$nicks[] = 'bombbot';
$nicks[] = 'bombbot_';

$modules[] = 'bombshellnet.php';
$modules[] = 'system.php';
$modules[] = 'status.php';
$modules[] = 'AutoVoice.php';
$modules[] = 'Fridget_KICKHandler.php';
$modules[] = 'Fridget_ChanNames.php';

/*** Scripts to run ever x seconds ***/
//$scripts[] = 'Fridget_Monitor.php';
$runtime = 2; /*** scripts run every $runtime ***/

$khandler_autorejoin = true;
$debug = 0;

$botusers = array( 'skill_pain!~whocares2@bombshellnet/owner/bombshell' => 1 , 'bombshell!bombshell@bombshellz/Owner/bombshell' => 1 );

/*** Log output here if running in Deamon Mode ***/
$log = 'bot.log';

/*** DO NOT EDIT BELOW ***/
$php_bin = '/usr/local/bin/php';