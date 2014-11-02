<?php
/**
 *
 * @author hollodotme
 */

namespace hollodotme\PhProject\Options;

/**
 * Class Option
 *
 * @package hollodotme\PhProject\Options
 */
class Option
{

	/** @var string */
	private $name;

	/** @var null|string */
	private $value;

	/**
	 * @param string      $name
	 * @param null|string $value
	 */
	public function __construct( $name, $value = null )
	{
		$this->name  = $name;
		$this->value = $value;
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @return null|string
	 */
	public function getValue()
	{
		return $this->value;
	}

	/**
	 * @param string $arg
	 *
	 * @return Option
	 */
	public static function fromArgvArg( $arg )
	{
		$opt_str = preg_replace( '#^\-+#', '', $arg );
		@list($name, $value) = explode( '=', $opt_str, 2 );

		return new static( $name, $value );
	}
}
