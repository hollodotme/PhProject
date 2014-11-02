<?php
/**
 * CLI bootstrap script
 *
 * @author hollodotme
 */

namespace hollodotme\PhProject;

error_reporting( E_ALL );
ini_set( 'display_errors', 1 );

require_once __DIR__ . '/../../vendor/autoload.php';

use hollodotme\PhProject\Exceptions\NotCalledFromCli;
use hollodotme\PhProject\Options\Options;

if ( PHP_SAPI == 'cli' )
{
	$options   = Options::fromArgv( $_SERVER['argc'], $_SERVER['argv'] );
	$phproject = new PhProject( $options );

	$phproject->preCheck();
	$phproject->create();
	$phproject->postCheck();
}
else
{
	throw new NotCalledFromCli( PHP_SAPI );
}
