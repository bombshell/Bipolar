<?php
/*********************
 color codes 
 0 = black 1 = white 2 = blue 3 = green
 4 = red 5 = brown 6 = purple 7 = orange
 8 = yellow 9 = limegreen 10 = turquise
 11 = cyan 12 = lightblue 13 = pink
 14 = grey 15 = lightgrey
*********************/

/*** 
 * Written by higochoa
 * Heavely modified by: Bombshell
 * Property of http://www.bombshellz.net/
 * This bot can be freely redistributed as long the authors are credited 
 */
class Fridget
{
	public $version = 'Version 0.1.7';
	public $params;
	public $channels; /* Storage container for what channel the bot is in */
	public $data; /* Storage container for irc messages */
	public $nick; /* Our nick */
	public $module;
	public $tempStorage; /* Temporary storage of variables */
	public $bind;
	public $bindCmds = array(); /* Container for bot commands to protect from duplicate commands */
	public $sock;
	public $debug = 0;
	public $users; /* Container for bot users */
	public $daemon = false; /* Container if the bot is in daemon mode */
	public $pids; /* Container for bot pids */
	public $scripts; /* Container for scripts */
	public $connTimout = 0; /* Container to check if the connection is alive */
	public $connTimer = 0;
	public $rehash = false; /* If the bot is in rehash mode */ 
	public $tempDir = 'temp/'; /* Temp directory */
	public $ircFont = array(
							 '[c]'	=>	"\x03",
							 '[n]'	=>	"\x0f",
							 '[b]'	=>	"\x02",
							 '[u]'	=>	"\x1f",
							 '[r]'	=>	"\x16"
						   );
	
	
	public function __construct( $servers , $channels , $nicks , $botusers = null)
	{
		/*** Global Vars ***/
		global $daemon, $config, $debug;
		
		/*** Set output to log ***/
		if ( $daemon ) {
			$this->daemon = true;
		}
		
		/*** Check values ***/
		$run = true;
		if ( empty( $servers ) || !is_array( @$servers ) ) {
			$run = false;
		}
		if ( empty( $channels ) || !is_array( @$channels ) ) {
			$run = false;
		}
		if ( empty( $nicks ) || !is_array( @$nicks ) ) {
			$run = false;
		}
		if ( $run == false ) {
			$this->printf( 'Fridget: Error: Failed to initialize' );
			exit(1);
		}
		
		if ( !empty( $botusers ) ) {
			$this->users = $botusers;
		}
		$this->printf( "Fridget {$this->version} loaded and initializing" );
		$this->printf( "Using config: $config" );
		
		if ( !empty( $debug ) ) {
			$this->printf( "Setting debug level to: $debug" );
			$this->debug = $debug;
		}
		
		/* Store our paramaters */
		$this->params[ 'servers' ]  = $servers;
		$this->params[ 'channels' ] = $channels;
		$this->params[ 'nicks' ]    = $nicks;
	}
	
	public function connect( $reconn = false )
	{	
		//:kornbluth.freenode.net 433 * skill_pain :Nickname is already in use.
		$connected = false;
		foreach( $this->params[ 'servers' ] as $server => $values ) {
			$this->printf( 'Connecting to ' . $server );
			$this->sock = @fsockopen( $server , $values[ 'port' ] , $errno , $errstr , '30' );
			if ( $this->sock ) {
				/*** Lets wait for us to connect ***/
				sleep(5);
				$this->printf( "Connected to $server" );
				/*** Set connection keep alive time out ***/
				//stream_set_timeout( $this->sock , $values[ 'conn_nonalive' ] );
				
				$this->send("USER {$values[ 'ident' ]} 0 * :{$values[ 'realname' ]}" );
				
				/* Set bot nick */
				$nick_set = false;
				foreach( $this->params[ 'nicks' ] as $nick ) {
					$this->send( "NICK $nick" );
					while(1) {
						$answer = $this->read();
						if ( $answer[ 'command' ] == '001' ) {
							$this->nick = $nick;
							$nick_set = true;
							break;
						} elseif ( $answer[ 'command' ] == '433' ) {
							break;
						}
					}
					/*** Break out of foreach ***/
					if ( $nick_set ) {
						break;
					}
				}
				if ( $nick_set == false ) {
					$this->send( 'QUIT Bot Quit' );
					$this->printf( 'Error: Unable to set Nick' );
					$this->printf( 'Bot quit!' );
					exit(1); 
				} 
				
				/* Join channels */
				if ( $reconn ) {
					/*** Feature addded 03/08/2011 : Re join channels that we were in ***/
					foreach( $this->channels as $channel ) {
						$this->join( $channel );
					}
				} else {
					foreach( $this->params[ 'channels' ] as $channel ) {
                        $this->join( $channel );
					}
				}
				$connected = true;
			} else {
				$this->printf( "Error: Unable to connect to $server on port {$values[ 'port' ]}: $errno $errstr" );
			}
		}
		if ( $connected == false ) {
			$this->printf( 'Bot quit!' );
			exit(1);
		}
	}

	public function join( $channel )
	{
		$this->send( "JOIN $channel" );
		do {
			$data = $this->read();
			
			if ( $data[ 'command' ] == '353' ) {
				if ( $data[ 'words' ][1] == $channel ) {
					$nicks = array_splice( $data[ 'words' ] , 2 );
					foreach( $nicks as $nick ) {
						$nick = preg_replace( '`^:`' , '' , $nick );
						$this->channels[ $channel ][] = $nick; 
					}
				}
			} elseif ( $data[ 'command' ] == '366' ) {
				break;
			}
		} while(1);
	}
	
	public function module( $mod )
	{
		/*** Global Vars ***/
		global $php_bin;
		
		/* bot object */
		$bot = $this;
		$init = true;
		$mod_path = dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . $mod;
		if ( !is_file( $mod_path ) || !is_readable( $mod_path ) ) {
			if ( $this->debug >= 2 ) {
				$this->printf( 'Error: Warning: Module not loaded: Module Not found/Permission Denied: ' . $mod_path );
			}
			return false;
		}
		
		/*** Feature added 02/13/2011 2:13 : Syntax check ***/
		exec( $php_bin . ' --syntax-check ' . $mod_path , $output , $return_var );
		if ( $return_var != 0 ) {
			$this->printf( 'Error: Warning: Module not loaded: Syntax check failed: ' . $mod_path );
			return false;
		}
		
		$this->tempStorage[ 'modPreInit_modFile' ] = $mod_path;
		require( $mod_path );
		
		/* Load module into memory */
		$module = file_get_contents( $mod_path );
		$module = preg_replace( '`^(<\?php)`' , '' , $module );
		$this->module[ $this->tempStorage[ 'loaded_module' ][ $mod_path ][ 'mod_name' ] ][ 'mod_mod' ] = $mod;
		$this->module[ $this->tempStorage[ 'loaded_module' ][ $mod_path ][ 'mod_name' ] ][ 'mod_file' ] = $mod_path;
		$this->module[ $this->tempStorage[ 'loaded_module' ][ $mod_path ][ 'mod_name' ] ][ 'mod_desc' ] = $this->tempStorage[ 'loaded_module' ][ $mod_path ][ 'mod_desc' ];
		$this->module[ $this->tempStorage[ 'loaded_module' ][ $mod_path ][ 'mod_name' ] ][ 'mod_code' ] = $module;
		$this->printf( 'Loaded Module: ' . $this->module[ $this->tempStorage[ 'loaded_module' ][ $mod_path ][ 'mod_name' ] ][ 'mod_desc' ] );
		return true;
	}
	
	public function modPreInit( $modName , $modDesc )
	{
		$this->tempStorage[ 'loaded_module' ][ $this->tempStorage[ 'modPreInit_modFile' ] ][ 'mod_name' ] = $modName;
		$this->tempStorage[ 'loaded_module' ][ $this->tempStorage[ 'modPreInit_modFile' ] ][ 'mod_desc' ] = $modDesc;
	}
	
	public function readPrefix( $prefix )
	{
		$prefix = preg_replace( '`^(:)`' , '' , $prefix );
		if ( preg_match( '`!+`' , $prefix ) ) {
			$return[ 'prefix' ] = $prefix;
			$nick = explode( '!' , $prefix );
			$return[ 'name' ] = $nick[0];
			$ident = explode( '@' , $nick[1] );
			$return[ 'ident' ] = $ident[0];
			$return[ 'hostname' ] = $ident[1];
		} else {
			$return[ 'hostname' ] = $prefix;
		}
		return $return;		
	}
	
	public function read()
	{	
		$data = fgets( $this->sock , 512 );
		
		/*** Feature added : If we getting to many false data reads
		 * lets assume that the connection is no longer alive.
		 */
		if ( $data === false ) {
			if ( time() < $this->connTimer && $this->connTimer != 0 ) {
				$this->connTimout++;
				if ( $this->debug == 2 ) {
					$this->printf( 'Debug: Data read timeout: ' . $this->connTimout );
				}
			}
			$this->connTimer = time() + 5;	
		}
		if ( $this->connTimout > 5 ) {
			$this->printf( 'Reconnecting...' );
			$this->connTimout = 0;
			fclose( $this->sock );
			$this->connect(true);
		}
		if ( empty( $data ) ) {
			return null;
		}
		
		$arr = explode( ' ' , $data );
		/* Respond to pings */
		if ( $arr[0] == 'PING' ) {
			$pong = explode( ':' , $arr[1] );
			$pong = trim( $pong[1] );
			$pong = "PONG $pong";
			$this->send( $pong );
			return null; 
		}
		
		/* Get nick params */
		$return[ 'nick' ] = $this->readPrefix( $arr[0] );
		/* Get command */
		$return[ 'command' ] = trim( $arr[1] );
		if ( $return[ 'command' ] == 'PRIVMSG' ) {
			if ( $this->nick == $arr[2] ) {
				$return[ 'to' ]   = $return[ 'nick' ][ 'name' ];
				$return[ 'mode' ] = 'private';
			} else {
				$return[ 'to' ] = $arr[2];
				/*** Feature added 03/19/200 : Retrieve channel name ***/
				if ( preg_match( '`^#`' , $arr[2] ) ) {
					$return[ 'channel' ] = $arr[2];
					$return[ 'mode' ]    = 'public';
				}
			}
		}
		
		/* Get words */
		if ( $return[ 'command' ] == 'JOIN' || $return[ 'command' ] == 'PART' ) {
			$return[ 'channel' ] = preg_replace( '`^:`' , '' , $arr[2] );
		} elseif ( $return[ 'command' ] == 'KICK' ) {
			$return[ 'channel' ] = $arr[2];
			$return[ 'knick' ]   = $arr[3];
			$return[ 'words' ][0] = $arr[4];
		} elseif ( $return[ 'command' ] == 'MODE' ) {
			$return[ 'channel' ]  = $arr[2];
			$return[ 'param' ][0] = $arr[3];
			if ( !empty( $arr[4] ) ) {
				$return[ 'param' ][1] = $arr[4];
			}
		} else {
			$return[ 'words' ] = array_slice( $arr , 3 );
			$return[ 'words' ] = array_values( $return[ 'words' ] );
		}
		
		if ( !empty( $return[ 'words' ][0] ) ) {
			$return[ 'words' ] = $this->trimArrValues( $return[ 'words' ] );
			$return[ 'words' ][0] = preg_replace( '`^(:)`' , '' , $return[ 'words' ][0] );
		}
		
		/** Trim the channel if necessariy **/
		if ( !empty( $return[ 'channel' ] ) ) $return[ 'channel' ] = trim( $return[ 'channel' ] );
		
		return $return;
	}
	
	public function bindCmd( $module , $cmds , $desc , $level = 0 )
	{
		/*** Bug fixed 03/16/2011 : Prevent duplication commands ***/
		/*** Feature modified 03/19/2011 ***/
		$this->bindCmds[ $cmds ] = array( $module , $desc , $level );
	}
	
	public function isBindCmd( $command , $priv = null )
	{
		/* verify command exists */
		if ( !empty( $this->bindCmds[ $command ] ) ) {
			/* verify against command privalage */
			if ( !empty( $priv ) ) {
				if ( $this->bindCmds[ $command ][2] == $priv ) {
					return true;
				} else {
					return false;
				}
			} else {
				return true;
			}
		}
		return false;
	}
	
	public function bind( $command , $module )
	{
		/* Store the module in memory */
		$this->bind[ $command ][ $module ] = $module;
	}
	
	public function runScripts()
	{
		/*** Global Vars ***/
		global $scripts, $runtime;
		$temp_path = dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'temp' . DIRECTORY_SEPARATOR;
		if ( !empty( $scripts ) && is_writable( $temp_path ) ) {
			$bot = $this;
			$script_path = dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'scripts' . DIRECTORY_SEPARATOR;
			
			if ( !is_dir( $temp_path . 'scripts' ) ) {
				mkdir( $temp_path . 'scripts' , 0700 , true );
			}
			$temp_path .= 'scripts' . DIRECTORY_SEPARATOR;
		
			/*** Move scripts to temp location ***/
			foreach( $scripts as $script ) {
				if ( is_file( $script_path . $script ) ) {
					copy( $script_path . $script , $temp_path . $script );
					$this->scripts[] = $temp_path . $script;
				}	
			}
		
			$pid = pcntl_fork();
			if ($pid == -1) {
     			$this->printf( 'Error: Unable to fork: Unable to run scripts' );
			} elseif ( $pid ) {
     			// we are the parent
     			$this->pids[] = $pid;
     			$this->printf( 'Running scripts' );
			} else {
				while (1) {
					sleep($runtime);
					foreach( $this->scripts as $script ) {
						if ( $this->debug == 2 ) {
							$this->printf( 'Debug: Running script: ' . $script );
						}
						require $script;
					}
				}
			}
		} else {
			$this->printf( 'No scripts were loaded (Perhaps temp directory is not writable)' );
		}
	}
	
	public function runBindCmd( $cmd )
	{
		/* bot object */
		$bot = $this;
		$module = $this->bindCmds[ $cmd ][0];
		/*** Feature added 02/08/2011 4:29 PM : let's aviod calling code don't exists ***/
		if ( !empty( $this->module[ $module ] ) ) { 
			if ( $this->debug == 2 ) {
				$this->printf( "Debug: Running bind command: $cmd" );
			}
			eval( $this->module[ $module ][ 'mod_code' ] );
		}
	}
	
	public function runHooks()
	{
		/* bot object */
		$bot = $this;
		$command = $this->data[ 'command' ];
		
		if ( !empty( $this->bind[ $command ] ) ) {
			if ( $this->debug == 2 ) {
				$this->printf( "Debug: Running $command hooks" );
			}
			foreach( $this->bind[ $command ] as $module ) {
				//** Feature added 02/08/2011 4:29 PM : let's aviod calling code don't exists **//
				if ( !empty( $this->module[ $module ] ) ) { 
					eval( $this->module[ $module ][ 'mod_code' ] );
				}
			}	
		}
	}
	
	public function run()
	{
		while(1) {
			if ( is_resource( $this->sock ) ) {
				$this->data = $this->read();
				$command = @$this->data[ 'words' ][0];
				
				/* Check if we have module for the command */
				if ( $this->isBindCmd( $command , 1 ) ) {
					if ( $this->isUserAuth(1) ) {
						$this->runBindCmd( $command );
					}
				} elseif ( $this->isBindCmd( $command , 2 ) ) {
					if ( $this->isUserAuth(2) ) {
						$this->runBindCmd( $command );
					}
				} elseif ( $this->isBindCmd( $command ) ) {
					$this->runBindCmd( $command );
				} elseif ( !empty( $this->bind[ $this->data[ 'command' ] ] ) ) {
					$this->runHooks();
				} 
			} else {
				$this->connect(true);
			}
		}
	}
	
	public function privMsg( $str , $to = null , $color = false )
	{
		if ( empty( $to ) ) {
			if ( empty( $this->data[ 'to' ] ) ) {
				return false;
			}
			$to = $this->data[ 'to' ];
		}
		
		/** Include color in IRC text **/
		if ( $color ) {
			$str = strtr( $str , $this->ircFont );
		}
		
		//var_dump( "PRIVMSG {$this->data[ 'to' ]} :$str" );
		$this->send( "PRIVMSG $to :$str" );
	}
	
	public function notice( $str , $to = null )
	{
		if ( empty( $to ) ) {
			$to = $this->data[ 'to' ];
		}
		$this->send( "NOTICE $to :" . $str );
	}
	
	public function setMode( $str )
	{
		return $this->send( "MODE $str" , true );
	}
	
	public function send( $str , $read = false )
	{
		/* Let's see what we're sending */
		if ( $this->debug == 2 ) {
			$this->printf( 'Debug: send-> ' . $str );
		}
		fwrite( $this->sock , $str . "\n" );
		/* Read response */
		if ( $read == true ) {
			return $this->read();
		}
	}
	
	public function trimArrValues( $arr )
	{
		foreach( $arr as $key => $value ) {
			$arr[ $key ] = trim( $value );
		}
		return $arr;
	}
	
	public function printf( $str )
	{
		/*** Global Vars ***/
		global $log;
		
		$str = date( '[m/d/Y h:i:s A]' ) . ' ' . $str . "\r\n";
		if ( $this->daemon ) {
			file_put_contents( $log , $str , FILE_APPEND );
		} else {
			print $str;
		}
	}
	
	public function quit($status=0)
	{
		if ( !empty( $this->pids ) ) {
			foreach( $this->pids as $pid ) {
				posix_kill( $pid , SIGTERM );
			}
		}
		$this->printf( 'Bot Quit!' );
		exit($status);
	}
	
	public function isUserAuth( $priv )
	{
		if ( @$this->users[ $this->data[ 'nick' ][ 'prefix' ] ] <= $priv ) {
			return true;
		} 
		return false;
	}
}