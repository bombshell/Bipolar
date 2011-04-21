<?php
/*** Settings ***/
$user_db_file = $bot->tempDir . 'user.db';
$cmnds_priv_mode = 'notice';
$ver = 'v0.0.6';

if ( @$init == true  ) {
	$bot->modPreInit( 'fridsystem' , 'Fridget System ' . $ver );
	$bot->bind( 'PRIVMSG' , 'fridsystem' );
	$bot->bindCmd( 'fridsystem' , '!rehash' , 'Rehash the bot' , 1 );
	$bot->bindCmd( 'fridsystem' , '!system' , 'Version of Fridget System Module' , 1 );
	$bot->bindCmd( 'fridsystem' , '!join' , 'Makes bot join a channel' , 1 );
	$bot->bindCmd( 'fridsystem' , '!die' , 'Kills the bot' , 1);
	$bot->bindCmd( 'fridsystem' , '!recog' , 'Recognize user' , 1 );
	$bot->bindCmd( 'fridsystem' , '!botusers' , 'List bot users' , 1 );
	$bot->bindCmd( 'fridsystem' , '!commands' , 'Display a list of bot commands. Note: Privalge commands will only be shown to bot users');
	$bot->bindCmd( 'fridsystem' , '!help' , 'Displays information about each command' );
	
	/*** Open Database ***/ 
	if ( is_file( $user_db_file ) ) {
		$user_db = file_get_contents( $user_db_file );
		$user_db = unserialize( $user_db );
		$bot_user_db = array_merge( $user_db , $bot->users );
	}
	
} else {
	
	if ( !is_array( $bot->users ) ) {
		return false;
	}
	$words = $bot->data[ 'words' ];
	switch( $words[0] ) {
		case '!commands':
			foreach ( $bot->bindCmds as $command => $cmnd_params ) {
				/*** If command is bot user privilage command, do not display it ***/
				if ( $cmnd_params[1] == 1 ) {
					if ( !$bot->isUserAuth() ) {
						break;
					}
				}
				if ( empty( $cmnd_list ) ) {
					$cmnd_list = "Bot command list: $command"; 
				} else {
					$cmnd_list .= ", $command";
				}
			} 
			if ( $cmnds_priv_mode == 'privmsg' ) {
				$bot->privMsg( $cmnd_list );
			} else {
				$bot->notice( $cmnd_list , $bot->data[ 'nick' ][ 'name' ] );
			}
			$cmnd_list = null;
		break;
		
		default:
			if ( $bot->isUserAuth(1) ) {
				
				switch( $words[0] ) {
					case '!rehash':
						$nick = $bot->data[ 'nick' ][ 'name' ];
						$bot->privMsg( 'Rehashing... =D' );
						$bot->rehash = true;
						$bot->notice( 'Reloading modules...' , $nick );
						foreach( $bot->module as $mod_name => $mod_params ) {
							//var_dump( $mod_params );
							if ( $bot->module( $mod_params[ 'mod_mod' ] ) ) {
								$mod_desc = $this->tempStorage[ 'loaded_module' ][ $mod_params[ 'mod_file' ] ][ 'mod_desc' ];
								$bot->notice( 'Reloaded Module: ' . $mod_desc , $bot->data[ 'nick' ][ 'name' ] );
							} else {
								$bot->notice( 'Error: Module not reloaded: Old Module still in use: ' . $mod_params[ 'mod_desc' ] , $bot->data[ 'nick' ][ 'name' ] );
							}
						}
						$bot->rehash = false;
					break;
					case '!join':
						$channel = str_replace( ' ' , '' , $words[1] );
						if ( empty( $channel ) ) {
							$bot->privMsg( 'Usage: !join #bombshellnet' );
						}
						if ( !preg_match( '`^#+`' , $channel ) ) {
							$bot->privMsg( 'Invalid Channel name. Example: #bombshellnet' );
						}
						$bot->privMsg( 'Ok joining ' . $channel );
						$bot->join( $channel );
					break;
		
					case '!die':
						$bot->privMsg( 'Wtf!!!' );
						$bot->send( 'QUIT' );
						$bot->quit();
					break;
					case '!system':
						$bot->privMsg( 'Fridget System ' . $ver );
					break;
					
					case '!recog':
						$prefix = @$words[2];
						var_dump( $prefix );
						if ( @$words[1] == 'add' ) {
							if ( !empty( $prefix ) ) {
								$bot->users[] = $prefix;
								$bot->privMsg( "Added $prefix to bot users" );
							} else {
								$bot->privMsg( 'Invalid Usage: !recog add <full hostmask>' );
							}
						} elseif ( @$words[1] == 'del' ) {
							$key = array_search( $prefix , $bot->users );
							if ( !empty( $key ) ) {
								unset( $bot->users[ $key ] );
								$bot->privMsg( "Removed $prefix from bot users" );
							} else {
								$bot->privMsg( "Unknown user: $prefix" );
							}
						} else {
							$bot->privMsg( 'Invalid Usage: !recog (add|del) <full hostmask>' ); 
						}
						
						/*** Write database **/
						file_put_contents( $user_db_file , serialize( $bot->users ) );
					break;
					
					case '!botusers':
						$nick = $bot->data[ 'nick' ][ 'name' ];
						foreach( $bot->users as $user ) {
							$bot->notice( "Bot user: $user" , $nick );
						}
					break;
				}
			}
		break;
	} 
}