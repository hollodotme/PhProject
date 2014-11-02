<?php
/**
 *
 * @author hollodotme
 */

namespace hollodotme\PhProject\Options;

use hollodotme\Utilities\ClassName;

/**
 * Class OptionValidator
 *
 * @package hollodotme\PhProject\Options
 */
class OptionValidator
{

	/** @var Option */
	private $option;

	/**
	 * @param Option $option
	 */
	public function __construct( Option $option )
	{
		$this->option = $option;
	}

	/**
	 * @return bool
	 */
	public function isNameValid()
	{
		return in_array( $this->option->getName(), Opt::getAll() );
	}

	/**
	 * @return bool
	 */
	public function isValueValid()
	{
		$check_name   = preg_replace_callback(
			"#[^a-z0-9]([a-z0-9])#i",
			function ( array $matches )
			{
				return strtoupper( $matches[1] );
			},
			$this->option->getName()
		);
		$check_method = 'check' . ucfirst( $check_name );

		if ( is_callable( array($this, $check_method ) ) )
		{
			return $this->{$check_method}();
		}
		else
		{
			return false;
		}
	}

	private function checkProjectName()
	{
		return $this->isNameUsefulAsDirectory();
	}

	private function isNameUsefulAsDirectory()
	{
		return (bool)preg_match( "#^[a-z_\-0-9\.]+$#i", $this->option->getValue() );
	}

	private function checkProjectNamespace()
	{
		return ClassName::isValid( $this->option->getValue() );
	}

	private function checkCreateGitEnv()
	{
		return $this->isBoolean();
	}

	private function checkCreateComposerFile()
	{
		return $this->isBoolean();
	}

	private function checkCreateVagrantEnv()
	{
		return $this->isBoolean();
	}

	private function checkCreatePhpunitEnv()
	{
		return $this->isBoolean();
	}

	private function isBoolean()
	{
		return in_array( $this->option->getValue(), array( 'yes', 'no' ) );
	}

	private function checkTargetDir()
	{
		$value = $this->option->getValue();
		if ( $value[0] == '/' )
		{
			return (is_dir( $value ) && is_writable( $value ));
		}
		else
		{
			$path = realpath( $_SERVER['PWD'] . DIRECTORY_SEPARATOR . $value );

			return (!empty($path) && is_dir( $path ) && is_writable( $path ));
		}
	}

	private function checkVendorName()
	{
		return $this->isNameUsefulAsDirectory();
	}

	private function checkAuthorName()
	{
		return $this->isNotEmptyString();
	}

	private function isNotEmptyString()
	{
		return ($this->option->getValue() != '');
	}

	private function checkAuthorEmail()
	{
		return $this->isNotEmptyString();
	}

	private function checkAuthorHomepage()
	{
		return $this->isNotEmptyString();
	}

	private function checkComposerAutoloadType()
	{
		return in_array( strtolower( $this->option->getValue() ), array( 'psr-0', 'psr-4', 'classmap', 'files' ) );
	}

	private function checkHelp()
	{
		return true;
	}
}