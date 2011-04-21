<?php

$ver = 'v0.0.2';

if ( @$init == true ) {
	$bot->modPreInit( 'NamesHandler' , 'Fridget Channel Names Maintainer ' . $ver );
	$bot->bind( 'PART' , 'NamesHandler' );
	$bot->bind( 'JOIN' , 'NamesHandler' );
	$bot->bind( 'KICK' , 'NamesHandler' );
	$bot->bind( 'MODE' , 'NamesHandler' );
} else {
	$channel = $bot->data[ 'channel' ];
	$data    = $bot->data;

	if ( $bot->data[ 'command' ] == 'JOIN' ) {
		/*** Add nick to bots Global Channel list ***/
		if ( !array_search( $bot->data[ 'nick' ][ 'name' ] , $bot->channels[ $channel ] ) ) {
			$bot->channels[ $channel ][] = $bot->data[ 'nick' ][ 'name' ];
		}
	} elseif ( $bot->data[ 'command' ] == 'MODE' ) {
		$channel;
		if ( $data[ 'param' ][0] == '+o' && !array_search( '@' . $data[ 'param' ][1] , $bot->channels[ $channel ] ) ) {
			/*** Remove nick from bots Global Channel list ***/
			$key = array_search( $data[ 'param' ][1] , $bot->channels[ $channel ] );
			unset( $bot->channels[ $channel ][ $key ] );
			/*** Reset index ***/
			$bot->channels[ $channel ] = array_values( $bot->channels[ $channel ] );
			/*** Add nick to bots Global Channel list ***/
			$bot->channels[ $channel ][] = '@' . $data[ 'param' ][1];
		} elseif ( $data[ 'param' ][0] == '-o' ) {
			/*** Remove nick from bots Global Channel list ***/
			$key = array_search( '@' . $data[ 'param' ][1] , $bot->channels[ $channel ] );
			unset( $bot->channels[ $channel ][ $key ] );
			/*** Reset index ***/
			$bot->channels[ $channel ] = array_values( $bot->channels[ $channel ] );
			/*** Add nick to bots Global Channel list ***/
			$bot->channels[ $channel ][] = $data[ 'param' ][1];
		}
	} else {
		/*** Remove nick from bots Global Channel list ***/
		$key = array_search( $bot->data[ 'nick' ][ 'name' ] , $bot->channels[ $channel ] );
		unset( $bot->channels[ $channel ][ $key ] );
		/*** Reset index ***/
		$bot->channels[ $channel ] = array_values( $bot->channels[ $channel ] );
	}
	//var_dump( $bot->channels[ $channel ] );
}