#!/usr/local/bin/php
<?php
/* Default vars */
$daemon = false;
$config = 'config.php';

/* Process command line arguements */
if ( $argc > 1 ) {
	foreach ( $argv as $pos => $arg ) {
		if ( $arg == '-d' || $arg == '--daemon' ) {
			$daemon = true;
		} elseif ( $arg == '--conf' ) {
			$file = $argv[ $pos + 1 ];
			if ( is_file( $file ) ) {
				$config = $file;
			} else {
				echo 'Error: Invalid configuration file, defaulting to config.php';
			}
			
		} elseif ( preg_match( '`^(-|--)`' , $arg ) ) {
			echo "Invalid Usage: $arg\n";
			echo "Usage: \n";
			echo "  --daemon\n";
			echo "  -d\n";
			echo "     Run bot in daemon mode\n";
			echo "  --conf\n";
			echo "     Provide alternate configuration file\n";
			exit(1);
		}
	}
}

if ( $daemon == true ) {
	$pid = pcntl_fork();
	if ($pid == -1) {
     	die( "could not fork\r\n" );
	} elseif ( $pid ) {
     	// we are the parent
     	print "Forking bot to the background [$pid]\n";
     	exit;
     	//pcntl_wait($status); //Protect against Zombie children
	} else {
		require 'Fridgetv2.Core.php';
	}
} else {
	require 'Fridgetv2.Core.php';
}