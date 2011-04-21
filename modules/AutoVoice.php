<?php
global $bshell, $LightJet, $profile;

if ( @$init == true ) {
	$bot->modPreInit( 'AutoVoice' , 'AutoVoice v0.0.1' );
	$bot->bind( 'JOIN' , 'AutoVoice' );
	
} else {
	//var_dump( $bot->data );
	//var_dump(  $bot->data[ 'words' ][0] );
	//var_dump( $bot->module );
	//MODE #bombshellnet-bots +v skill_pain
	$results = $bshell->db->query( 'bs_accounts' , 'bs_userid,bs_freenode_nick,bs_acct_status' , "WHERE bs_userid='{$bot->data[ 'nick' ][ 'name' ]}' OR bs_freenode_nick='{$bot->data[ 'nick' ][ 'name' ]}'" );
	if ( !empty( $results ) ) {
		if ( $results[0][ 'bs_acct_status' ] != 'Suspended' ) {
			$answer = $bot->setMode( $bot->data[ 'channel' ] . " +v " . $bot->data[ 'nick' ][ 'name' ] );
			if ( $answer[ 'command' ] == '482' ) {
				if ( $bot->debug == 2 ) {
					$bot->printf( 'Debug: Error: Unable to autovoice user: Need OP status: ' . $bot->data[ 'nick' ][ 'name'] );
				}
			}
		}
	}
}