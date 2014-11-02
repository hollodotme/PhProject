<?php
/**
 *
 * @author hollodotme
 */

namespace hollodotme\PhProject\Options;

/**
 * Class Opt
 *
 * @package hollodotme\PhProject
 */
abstract class Opt
{

	const TARGET_DIR                 = 'target-dir';

	const VENDOR_NAME                = 'vendor-name';

	const PROJECT_NAME               = 'project-name';

	const PROJECT_NAMESPACE          = 'project-namespace';

	const CREATE_COMPOSER_FILE       = 'create-composer-file';

	const COMPOSER_AUTOLOAD_TYPE     = 'composer-autoload-type';

	const AUTHOR_NAME                = 'author-name';

	const AUTHOR_EMAIL               = 'author-email';

	const AUTHOR_HOMEPAGE            = 'author-homepage';

	const CREATE_VAGRANT_ENV         = 'create-vagrant-env';

	const CREATE_PHPUNIT_ENV         = 'create-phpunit-env';

	const CREATE_GIT_ENV             = 'create-git-env';

	const SHOW_HELP                  = 'help';

	private static $description = array(
		self::TARGET_DIR                 => 'Path where the project shall be created (mandatory), defaults to the current working directory',
		self::VENDOR_NAME                => 'Name of the project vendor, defaults to the current user',
		self::PROJECT_NAME               => 'Name of the project (folder), defaults to "NewProject"',
		self::PROJECT_NAMESPACE          => 'PHP root namespace for the project, defaults to "[current user]\\NewProject"',
		self::CREATE_COMPOSER_FILE       => '[yes|no] Whether to create a composer.json file, defaults to "yes"',
		self::COMPOSER_AUTOLOAD_TYPE     => 'Autoload type for composer, defaults to "psr-4"',
		self::CREATE_PHPUNIT_ENV         => '[yes|no] Whether to create a phpunit environment, defaults to "yes"',
		self::CREATE_GIT_ENV             => '[yes|no] Whether to create a git environment, defaults to "yes"',
		self::CREATE_VAGRANT_ENV         => '[yes|no] Whether to create a vagrant environment, defaults to "yes"',
		self::SHOW_HELP                  => 'Shows this help',
	);

	/**
	 * @return array
	 */
	public static function getAll()
	{
		$ref_class = new \ReflectionClass( __CLASS__ );

		return $ref_class->getConstants();
	}

	/**
	 * @param null|string $option_name
	 *
	 * @return array|string
	 */
	public static function getHelpDescription( $option_name = null )
	{
		if ( is_null( $option_name ) )
		{
			return self::$description;
		}
		else
		{
			return self::$description[ $option_name ];
		}
	}

	/**
	 * @return array
	 */
	public static function getDefaults()
	{
		return array(
			self::TARGET_DIR                 => $_SERVER['PWD'],
			self::VENDOR_NAME                => $_SERVER['USER'],
			self::PROJECT_NAME               => 'NewProject',
			self::PROJECT_NAMESPACE          => $_SERVER['USER'] . '\\' . 'NewProject',
			self::AUTHOR_NAME                => $_SERVER['USER'],
			self::AUTHOR_EMAIL               => $_SERVER['USER'] . '@localhost',
			self::AUTHOR_HOMEPAGE            => 'http://localhost',
			self::CREATE_COMPOSER_FILE       => 'yes',
			self::CREATE_VAGRANT_ENV         => 'yes',
			self::CREATE_GIT_ENV             => 'yes',
			self::CREATE_PHPUNIT_ENV         => 'yes',
			self::COMPOSER_AUTOLOAD_TYPE     => 'psr-4',
		);
	}
}