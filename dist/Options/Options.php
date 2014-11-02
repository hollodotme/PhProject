<?php
/**
 *
 * @author hollodotme
 */

namespace hollodotme\PhProject\Options;

use hollodotme\PhProject\Exceptions\OptionIsNotSet;

/**
 * Class Options
 *
 * @package hollodotme\PhProject
 */
final class Options
{

	/** @var Option[] */
	private $options = array();

	/**
	 * @param Option $option
	 */
	public function set( Option $option )
	{
		$this->options[ $option->getName() ] = $option;
	}

	/**
	 * @param string $option_name
	 *
	 * @throws OptionIsNotSet
	 * @return mixed
	 */
	public function get( $option_name )
	{
		if ( $this->isOptionSet( $option_name ) )
		{
			return $this->options[ $option_name ]->getValue();
		}
		else
		{
			throw new OptionIsNotSet( $option_name );
		}
	}

	/**
	 * @param string $option_name
	 *
	 * @return array
	 */
	public function isOptionSet( $option_name )
	{
		return isset($this->options[ $option_name ]);
	}

	/**
	 * @return Option[]
	 */
	public function getInvalidOptionNames()
	{
		return array_filter(
			$this->options,
			function ( Option $option )
			{
				$validator = new OptionValidator( $option );

				return !$validator->isNameValid();
			}
		);
	}

	/**
	 * @return Option[]
	 */
	public function getInvalidOptionValues()
	{
		return array_filter(
			$this->options,
			function ( Option $option )
			{
				$validator = new OptionValidator( $option );

				return !$validator->isValueValid();
			}
		);
	}

	/**
	 * @param array $argv
	 *
	 * @return Options
	 */
	public static function fromArgv( $argc, array $argv )
	{
		$instance = self::fromDefaults();

		for ( $i = 1; $i < $argc; ++$i )
		{
			$option = Option::fromArgvArg( $argv[ $i ] );
			$instance->set( $option );
		}

		return $instance;
	}

	/**
	 * @return Options
	 */
	public static function fromDefaults()
	{
		$instance = new self();

		foreach ( Opt::getDefaults() as $opt_name => $opt_value )
		{
			$option = new Option( $opt_name, $opt_value );
			$instance->set( $option );
		}

		return $instance;
	}
}