<?php
global $bshell, $LightJet, $profile;

$ver = 'v0.0.7';
if ( @$init == true ) {
	$bot->modPreInit( 'accnt' , 'BSAccountInfo ' . $ver );
	$bot->bindCmd( 'accnt' , '!accnt' , 'Retrieves User account information' );
	$bot->bindCmd( 'accnt' , '!vouch' , 'Vouch a user. OP Only command' );
	$bot->bindCmd( 'accnt' , '!delvouch' , 'Delete users vouch' );
} else {
	//var_dump( $bot->data );
	//var_dump(  $bot->data[ 'words' ][0] );
	//var_dump( $bot->module );

	$words = $bot->data[ 'words' ];
	switch( $words[0] )
	{
		case '!accnt';
			$un = trim( @$words[1] );
			/*** Example ***/
			/*** check if anything was inserted***/
			if (empty($un)) {
				$bot->privMsg('Usage !accnt <username>');
				break;
			}
			/*** gather information***/
			$user = new System_Users( 'bots' , false ); 
			if ( $user->openUser( $un ) ) {
				$username   = $profile->currentProfile[ 'bs_userid' ];
				$pkgLevel   = $profile->currentProfile[ 'bs_pkg_level' ];
				$trialDate  = $profile->currentProfile[ 'bs_activated_time' ];
				if ( empty( $trialDate) ) $trialDate = 'Pending';
				
				
				if ( !empty( $profile->currentProfile[ 'bs_admin_vouched' ] ) ) {
					$vouched = explode( '=' , $profile->currentProfile[ 'bs_admin_vouched' ] );
					$vouched = "[b][c]3Vouched by:[n] [c]0" . $vouched[0] . ' ' . $vouched[1];
				} else {
					$vouched = 'No vouch';
				}
				$msg = "Account: [c]0{$username}[n] [[b][c]3Trial Start:[n][c]0 {$trialDate}[n] [b][c]3End:[n][c]0 Not yet implemented[n]] [[b][c]3Level:[n][c]0 {$pkgLevel}[n]] [[b][c]3Status[n]: [c]0" . $user->accountStatus() . "[n]] [[c]0{$vouched}[n]]";
			} else {
				$msg = "$un is not in our database ";
			}
			$bot->privMsg($msg , null , true);
		break;

		case '!vouch':
			$channel = $bot->channels[ $bot->data[ 'channel' ] ];
			$nick    = $bot->data[ 'nick' ][ 'name' ];
			$user    = $profile->getProfile( @$words[1] );
			$to      = $bot->data[ 'nick' ][ 'name' ];
			$activated_timestamp = strtotime( $account[ 'bs_activated_time' ] );
            $trial_timestamp     = $activated_timestamp + 60*60*24*5;
            $current_timestamp   = time();
            if ( $trial_timestamp > $current_timestamp ) {
            	$bot->notice( 'Error: User needs to be atleast 5 days on trial' , $to );
            } else {
				if ( !empty( $user ) ) {
					if ( array_search( '@' . $nick , $channel ) ) {
						if ( empty( $profile->currentProfile[ 'bs_admin_vouched' ] ) ) {
							$vouch = $nick . '=' . $bshell->_date();
							$profile->updateProfile( array( 'bs_admin_vouched' => $vouch ) );
							$profile->destroyProfile();
							$bot->notice( 'Vouch Recorded' , $to );
						} else {
							$bot->notice( 'User already vouched' , $to  );
						}
					} 
				} else {
					$bot->notice( 'Invalid User' , $to  );
				}
            }
		break;
		
		case '!delvouch':
			if ( $bot->isUserAuth() ) {
				$user = $profile->getProfile( @$words[1] );
				$to   = $bot->data[ 'nick' ][ 'name' ];
				if ( !empty( $user ) ) {
					$profile->updateProfile( array( 'bs_admin_vouched' => null ) );
					$profile->updateModifiedTime( 'bots' , 'Vouch deleted by ' . $to );
					$bot->notice( 'Vouch deleted' , $to );
				} else {
					$bot->notice( 'Invalid User' , $to  );
				}
			}
		break;
	}
}
