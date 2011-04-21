<?php
$ver = 'v0.0.1';

if ( @$init == true ) {
	$bot->modPreInit( 'RemindUsers' , 'Remind users of the move ' . $ver );
	$bot->bind( 'JOIN' , 'RemindUsers' );
	$bot->tempStorage[ 'RemindUser' ] = null;
} else {
	$nick = $bot->data[ 'nick' ][ 'name' ];
	if ( empty( $bot->tempStorage[ 'RemindUser' ][ $nick ] ) ) {
		$bot->tempStorage[ 'RemindUser' ][ $nick ] = time() + 60;
	} 
	if ( $bot->tempStorage[ 'RemindUser' ][ $nick ] < time() ) {
		break;
	}
	var_dump( $bot->tempStorage[ 'RemindUser' ][ $nick ] );
	echo time();
	$bot->privMsg( $nick . ': We have move to irc.fossnet.info, please update your bookmarks' , '#bombshellnet' );
}