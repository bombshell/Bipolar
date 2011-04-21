<?php
global $bshell, $LightJet, $profile;

/*** Settings ***/
$admin_db_file = $bot->tempDir . 'admin.db';
$ver = 'v0.0.8';

if ( @$init == true ) {
	$bot->modPreInit( 'bombshellnet' , 'Bombshellnet Module: ' . $ver . ' Bot: ' . $bot->version );
	$bot->bindCmd( 'bombshellnet' , '!bombshellnet' , 'Displays the Fridget bot version' );
	$bot->bindCmd( 'bombshellnet' , '!pending' , 'Display a list of the top 15 pending users' );
	$bot->bindCmd( 'bombshellnet' , '!admins' , 'Display\'s the list of Chan OPs and Server Administrators' );
	$bot->bindCmd( 'bombshellnet' , '!admin' , '!admins Alias' );
	$bot->bindCmd( 'bombshellnet' , '!rules' , 'Server Rules URL' );
	$bot->bindCmd( 'bombshellnet' , '!chanrules' , 'Channel Rules URL' );
	$bot->bindCmd( 'bombshellnet' , '!usermin' , 'Usermin URL' );
	$bot->bindCmd( 'bombshellnet' , '!activate' , 'Activate HowTo' );
	$bot->bindCmd( 'bombshellnet' , '!trial' , 'Trial Explained' );
	$bot->bindCmd( 'bombshellnet' , '!accounts' , 'Accounts URL' );
	$bot->bindCmd( 'bombshellnet' , '!stats' , 'Database stats' );
	$bot->bindCmd( 'bombshellnet' , '!uptime' , 'Server uptime' );
	$bot->bindCmd( 'bombshellnet' , '!webmail' , 'Webmail Link' );
	$bot->bindCmd( 'bombshellnet' , '!znc' , 'ZNC Wiki Page' );
	$bot->bindCmd( 'bombshellnet' , '!gcc' , 'GCC Access' );
	$bot->bindCmd( 'bombshellnet' , '!pastebin' , 'Pastebin URL' );
	$bot->bindCmd( 'bombshellnet' , '!manadmin' , 'Add user to admin list' , 1 );
	
	/*** Open Database ***/ 
	if ( !is_file( $admin_db_file ) ) {
		file_put_contents( $admin_db_file , array() );
	}
	$admin_db = file_get_contents( $admin_db_file );
	$admin_db = unserialize( $admin_db );
	$bot->tempStorage[ 'admin_db' ] = $admin_db;
	
} else {
	//var_dump( $bot->data );
	//var_dump(  $bot->data[ 'words' ][0] );
	//var_dump( $bot->module );
	/*** Default Vars ***/
	$words = $bot->data[ 'words' ];
	$admin_db = $bot->tempStorage[ 'admin_db' ];
	$msgs = array();
	
	switch( $words[0] )
	{
		case '!bombshellnet';
			$msgs[] = 'Bombshellnet Module: ' . $ver;
		break;
		
		case '!pending':
			$results = $bshell->db->query( 'bs_accounts' , 'bs_userid' , 'WHERE bs_acct_status=\'Pending\' LIMIT 15' );
			if ( !empty( $results ) ) {
				foreach( $results as $value ) {
					if ( empty( $users ) ) {
						$users = $value[ 'bs_userid' ];
					} else {
						$users .= ", {$value[ 'bs_userid' ]}";
					}
				}
			} else {
				$users = 'None';
			}
			$msgs[] = "Pending users: $users";
		break;
		case '!stats':
			$sql          = "SELECT COUNT(*) FROM bs_accounts";
			$res          = $bshell->db->dbObj->query( $sql );
			$totalusers   = $res->fetchColumn();
			$sql          = "SELECT COUNT(*) FROM bs_accounts WHERE bs_acct_status = 'Pending'";
			$res          = $bshell->db->dbObj->query( $sql );
			$pendingusers = $res->fetchColumn();
			$sql          = "SELECT COUNT(*) FROM bs_accounts WHERE bs_pkg_level = 'trial' and bs_acct_status = 'Active'";
			$res          = $bshell->db->dbObj->query( $sql );
			$trialusers   = $res->fetchColumn();
			$sql          = "SELECT COUNT(*) FROM bs_accounts WHERE bs_pkg_level = 'standard' OR bs_pkg_level = 'contrib'";
			$res          = $bshell->db->dbObj->query( $sql );
			$activeusers  = $res->fetchColumn();
			
			$msgs[] = "Bombshellnet Database Stats: Total: $totalusers Pending: $pendingusers Ontrial: $trialusers Active: $activeusers";
		break;
		case '!admins':
		case '!admin':
			$server_admins = 'None';
			$chan_ops      = 'None';
			
			if ( !empty( $admin_db ) ) {
				if ( !empty( $admin_db[ 'admins' ] ) ) {
					foreach( $admin_db[ 'admins' ] as $admin ) {
						if ( $server_admins == 'None' ) {
							$server_admins = $admin;
						} else {
							$server_admins .= ", $admin";
						}
					}		
				}
				if ( !empty( $admin_db[ 'ops' ] ) && is_array( $admin_db[ 'ops' ] ) ) {
					foreach( $admin_db[ 'ops' ] as $op ) {
						if ( $chan_ops == 'None' ) {
							$chan_ops = $op;
						} else {
							$chan_ops .= ", $op";
						}
					}
				}
			}
			$msgs[] = "Server Admins: $server_admins";
			$msgs[] = "Channel Ops: $chan_ops";
			//$bot->privMsg( 'Channel OPS: @Gryllida, @castirphoni' );
		break;
		
		case '!manadmin':
			if ( $bot->isUserAuth(2) ) {
				$nick = $bot->data[ 'nick' ][ 'name' ];
				if ( @$words[1] == 'add' ) {
					if ( @$words[2] == 'op' ) {
						$admin_db[ 'ops' ][] = $words[3];
						$bot->privMsg( "Op {$words[3]} added" );
					} elseif ( @$words[2] == 'admin' ) {
						$admin_db[ 'admins' ][] = $words[3];
						$bot->privMsg( "Admin {$words[3]} added" );
					} else {
						$bot->privMsg( $nick . ': Invalid usage: !manadmin add (op|admin) <user>' );
					}
				} elseif( @$words[1] == 'del' ) {
					if ( @$words[2] == 'op' ) {
						$key = array_search( $words[3] , $admin_db[ 'ops' ] );
						if ( !empty( $key ) ) {
							unset( $admin_db[ 'ops' ][ $key ] );
							$bot->privMsg( "Op {$words[3]} deleted" );
						} else {
							$bot->privMsg( "Unknown op: {$words[3]}" );
						}
					} elseif ( @$words[2] == 'admin' ) {
						$key = array_search( $words[3] , $admin_db[ 'admins' ] );
						if ( !empty( $key ) ) {
							unset( $admin_db[ 'admins' ][ $key ] );
							$bot->privMsg( "Admin {$words[3]} deleted" );
						} else {
							$bot->privMsg( "Unknown admin: {$words[3]}" );
						}
					} else {
						$bot->privMsg( $nick . ': Invalid usage: !manadmin del (op|admin) <user>' );
					}
				} else {
					$bot->privMsg( $nick . ': Invalid usage: !manadmin (add|del) (op|admin) <user>' );
				} 
				
				/*** Write admin database out ***/
				file_put_contents( $admin_db_file , serialize( $admin_db ) );
				$bot->tempStorage[ 'admin_db' ] = $admin_db;
			}
		break;
		
		case '!uptime':
			$data = shell_exec('uptime');
  			$uptime = explode(' up ', $data);
  			$uptime = explode(',', $uptime[1]);
  			$hours = explode( ':' , $uptime[1] );
  			$hours = trim( $hours[0] . ' hours, and ' . $hours[1] . ' minutes' );
  			$uptime = $uptime[0].', '. $hours;
  			$bot->privMsg( "Current server uptime: $uptime" );
		break;
		
		case '!rules':
			$msgs[] = 'Read the rules: http://wiki.bombshellz.net/wiki/Rules';
		break;
		case '!chanrules':
			$msgs[] = 'Read the rules: http://wiki.bombshellz.net/wiki/Chanrules';
		break;
		case '!usermin':
			$msgs[] = 'Visit usermin to change your password at https://ssh.bshellnet.org:1002';
		break;
		case '!activate':
			$msgs[] = 'If you haven\'t done so, please visit our webpage and register an account @ http://bombshellz.net/. Once done,' .
				      'ask an admin from the !admin list to begin your trial period. ' .
			          'Note: You account will begin on trial level, which means no SSH access during this period. ';
				   
		break;
		case '!trial':
			$msgs[] = 'You must spend a minimum of 5 days on trial before you can be vouch by channel operator. During this period, ' .
			          'your channel activity is monitored and any contributions you may have made. ' .
			          'This is time we get to know you better and you serious about our services, thank you. ' .
                      'Please note: There is no SSH access! You can start by saying Hello to everyone';
		break;
		
		case '!accounts':
			$msgs[] = 'Read the accounts page: http://wiki.bombshellz.net/wiki/Accounts';
		break;
		
		case '!webmail':
			$msgs[] = 'To access webmail, visit: https://webmail.bombshellz.net';
		break;
		
		case '!znc':
			$msgs[] = 'Read our znc page: http://wiki.bombshellz.net/wiki/Znc';
			$msgs[] = 'Personal ZNC\'s are no longer supported';
		break;
		
		case '!gcc':
			$msgs[] = 'We\'re not handing out GCC access at this time, until further notice';
		break;
		
		case '!pastebin':
			$msgs[] = 'Do not paste more then 3 lines on this channel! Use a http://pastebin.bombshellz.net/ instead.';
		break;
		
	}
	
	if ( is_array( $msgs ) ) {
		$un = null;
		if ( @$words[1] == '@' ) {
			$un = $words[2] . ': ';
		}
		foreach( $msgs as $msg ) {
			$bot->privMsg( $un . $msg );
		}
	}
}