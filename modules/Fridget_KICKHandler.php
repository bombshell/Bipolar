<?php
global $khandler_autorejoin;

if ( @$init == true ) {
	$bot->modPreInit( 'KICKHandler' , 'Fridget KICK Handler 0.0.1' );
	$bot->bind( 'KICK' , 'KICKHandler' );
} else {
	$channel = $bot->data[ 'channel' ];
	$nick    = @$bot->data[ 'words' ][1];
	if ( $bot->nick == $nick ) {
		$bot->printf( 'We\'ve been kicked from ' . $channel );
		if ( $khandler_autorejoin ) {
			if ( $bot->debug > 1 ) {
				$bot->printf( 'Auto rejoin Enabled' );
			}
		
			sleep(3);
			if ( $bot->debug == 2 ) {
				$bot->printf( 'Joining ' . $channel );
			}
			$bot->send( 'JOIN ' . $channel );
			$timeout = 0;
			do {	
				$data = $bot->read();
				if ( $data[ 'command' ] == '474' ) {
					if ( $bot->debug > 1 ) {
						$bot->printf( 'Error: Unable to join: Banned from ' . $channel );
					}
					break;
				} elseif ( $data[ 'command' ] == '366' ) {
					break;
				} elseif ( $timeout > 5 ) {
					if ( $bot->debug == 2 ) {
						$bot->printf( 'Debug: Timeout trying to join ' . $channel );
					}
					break;
				} else {
					$timeout++;
				}
			} while(1);
		}
	}	
}