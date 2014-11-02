<?php
/**
 *
 * @author hollodotme
 */

namespace hollodotme\PhProject;

use hollodotme\PhProject\Options\Opt;
use hollodotme\PhProject\Options\Options;

/**
 * Class PhProject
 *
 * @package hollodotme\PhProject
 */
class PhProject
{

	const COLOR_RED     = 'red';

	const COLOR_GREEN   = 'green';

	const COLOR_DEFAULT = 'default';

	/** @var Options */
	private $options;

	/**
	 * @param Options $options
	 */
	public function __construct( Options $options )
	{
		$this->options = $options;
	}

	public function preCheck()
	{
		$this->printInvalidOptionNamesIfNeeded();
		$this->printInvalidOptionValuesIfNeeded();
		$this->printHelpIfNeeded();
	}

	private function printInvalidOptionNamesIfNeeded()
	{
		$invalid_options = $this->options->getInvalidOptionNames();
		if ( !empty($invalid_options) )
		{
			$this->printLine( 'Invalid options detected', self::COLOR_RED );
			foreach ( $invalid_options as $option )
			{
				$this->printLine( sprintf( "\t%-25s", "--{$option->getName()}" ), self::COLOR_RED );
			}

			$this->printLine();
			$this->printHelp();
		}
	}

	private function printLine( $line = '', $color = self::COLOR_DEFAULT )
	{
		switch ( $color )
		{
			case 'red':
				echo "\e[1;31m{$line}\e[0m\n";
				break;

			case 'green':
				echo "\e[1;32m{$line}\e[0m\n";
				break;

			default:
				echo "\e[0m{$line}\e[0m\n";
		}
	}

	private function printInvalidOptionValuesIfNeeded()
	{
		$invalid_options = $this->options->getInvalidOptionValues();
		if ( !empty($invalid_options) )
		{
			$this->printLine( 'Invalid option values detected:', self::COLOR_RED );
			foreach ( $invalid_options as $option )
			{
				$this->printLine(
					sprintf( "\t%-25s\t%s", "--{$option->getName()}", "={$option->getValue()}" ),
					self::COLOR_RED
				);
			}

			$this->printLine();
			$this->printHelp();
		}
	}

	private function printHelpIfNeeded()
	{
		if ( $this->options->isOptionSet( Opt::SHOW_HELP ) )
		{
			$this->printHelp();
		}
	}

	private function printHelp()
	{
		$this->printLine( '[PhProject help]' );
		$this->printLine( '--' );
		$this->printLine( 'General usage:' );
		$this->printLine( 'phproject [options]' );
		$this->printLine();
		$this->printLine( 'Options:' );

		foreach ( Opt::getHelpDescription() as $opt_name => $help_text )
		{
			$this->printLine( sprintf( "\t%-25s\t%s\n", "--{$opt_name}", $help_text ) );
		}

		$this->printLine();

		exit();
	}

	public function create()
	{
		$this->createFolderStructure();

		$this->createFiles();
	}

	private function createFolderStructure()
	{
		$this->printLine( 'Creating directories...' );

		$project_folder = $this->getProjectDir();

		if ( $this->createFolder( $project_folder ) )
		{
			$this->createFoldersFromSection( 'dist', $project_folder );
			$this->createFoldersFromSection( 'doc', $project_folder );
			$this->createFoldersFromSection( 'env', $project_folder );
			$this->createFoldersFromSection( 'test', $project_folder );

			if ( $this->options->get( Opt::CREATE_COMPOSER_FILE ) == 'yes' )
			{
				$this->printLine( 'Composer directories:' );
				$this->createFoldersFromSection( 'composer', $project_folder );
			}

			if ( $this->options->get( Opt::CREATE_VAGRANT_ENV ) == 'yes' )
			{
				$this->printLine( 'Vagrant directories:' );
				$this->createFoldersFromSection( 'vagrant', $project_folder );
			}

			if ( $this->options->get( Opt::CREATE_PHPUNIT_ENV ) == 'yes' )
			{
				$this->printLine( 'PhpUnit directories:' );
				$this->createFoldersFromSection( 'phpunit', $project_folder );
			}
		}
	}

	/**
	 * @param string $section
	 * @param string $root_folder
	 *
	 * @throws Exceptions\OptionIsNotSet
	 */
	private function createFoldersFromSection( $section, $root_folder )
	{
		$project_name     = $this->options->get( Opt::PROJECT_NAME );
		$folder_structure = $this->getFolderStructure( $section );
		foreach ( $folder_structure['folders'] as $folder )
		{
			$path = $root_folder . DIRECTORY_SEPARATOR . $this->replaceInString(
					$folder, 'project_name', $project_name
				);

			$this->createFolder( $path );
		}
	}

	private function replaceInString( $string, $name, $value )
	{
		return str_replace( "{%{$name}%}", $value, $string );
	}

	/**
	 * @param string $section
	 *
	 * @return array
	 */
	private function getFolderStructure( $section )
	{
		$folder_structure = parse_ini_file( __DIR__ . '/Skeleton/folder_structure.ini', true );

		return $folder_structure[ $section ];
	}

	/**
	 * @param string $path
	 *
	 * @return bool
	 */
	private function createFolder( $path )
	{
		if ( file_exists( $path ) )
		{
			$this->printLine( $path . ' : OK (EXISTS)', self::COLOR_GREEN );

			return true;
		}
		else
		{
			if ( mkdir( $path, 0755, true ) )
			{
				$this->printLine( $path . ' : OK', self::COLOR_GREEN );

				return true;
			}
			else
			{
				$this->printLine( $path . ' : FAILED', self::COLOR_RED );

				return false;
			}
		}
	}

	/**
	 * @return string
	 * @throws Exceptions\OptionIsNotSet
	 */
	private function getProjectDir()
	{
		$target_dir   = $this->getTargetDir();
		$project_name = $this->options->get( Opt::PROJECT_NAME );

		return $target_dir . DIRECTORY_SEPARATOR . $project_name;
	}

	/**
	 * @return string
	 * @throws Exceptions\OptionIsNotSet
	 */
	private function getTargetDir()
	{
		$path = $this->options->get( Opt::TARGET_DIR );
		if ( $path[0] != '/' )
		{
			$path = realpath( $_SERVER['PWD'] . DIRECTORY_SEPARATOR . $path );
		}

		return $path;
	}

	private function createFiles()
	{
		$this->printLine( 'Creating files...' );
		$this->createReadmeFile();
		$this->createLicenseFile();
		$this->createComposerJsonFile();
		$this->createPhpUnitFiles();
		$this->createVagrantFiles();
	}

	private function createReadmeFile()
	{
		$content = $this->getSkeletonFileContent(
			'README.md.skel',
			array(
				'project_name' => $this->options->get( Opt::PROJECT_NAME ),
			)
		);

		$file_path = $this->getTargetPathInProject( 'README.md' );
		$this->putFileContents( $file_path, $content );
	}

	private function createLicenseFile()
	{
		$content = $this->getSkeletonFileContent(
			'LICENSE.skel',
			array(
				'author_name' => $this->options->get( Opt::AUTHOR_NAME ),
				'year'        => date( 'Y' ),
			)
		);

		$file_path = $this->getTargetPathInProject( 'LICENSE' );
		$this->putFileContents( $file_path, $content );
	}

	private function createComposerJsonFile()
	{
		if ( $this->options->get( Opt::CREATE_COMPOSER_FILE ) == 'yes' )
		{
			$content = $this->getSkeletonFileContent(
				'Composer/composer.json.skel',
				array(
					'vendor_name'             => $this->options->get( Opt::VENDOR_NAME ),
					'project_name'            => $this->options->get( Opt::PROJECT_NAME ),
					'author_name'             => $this->options->get( Opt::AUTHOR_NAME ),
					'author_email'            => $this->options->get( Opt::AUTHOR_EMAIL ),
					'author_homepage'         => $this->options->get( Opt::AUTHOR_HOMEPAGE ),
					'composer_autoload_block' => $this->getComposerAutoloadBlock(),
				)
			);

			$file_path = $this->getTargetPathInProject( 'composer.json' );
			$this->putFileContents( $file_path, $content );
		}
	}

	/**
	 * @return string
	 * @throws Exceptions\OptionIsNotSet
	 */
	private function getComposerAutoloadBlock()
	{
		$autoload_type = $this->options->get( Opt::COMPOSER_AUTOLOAD_TYPE );

		switch ( $autoload_type )
		{
			case 'psr-0':
				$block = '"autoload": { "psr-0": { "{%project_namespace%}": "dist/" } }';
				break;

			case 'psr-4':
				$block = '"autoload": { "psr-4": { "{%project_namespace%}": "dist/" } }';
				break;

			case 'classmap':
				$block = '"autoload": { "classmap": ["dist/"] }';
				break;

			case 'files':
				$block = '"autoload": { "files": [] }';
				break;

			default:
				$block = '"autoload": {}';
				break;
		}

		$project_namespace = rtrim( $this->options->get( Opt::PROJECT_NAMESPACE ), '\\' ) . '\\';
		$project_namespace = preg_replace( "#\\\+#", '\\\\\\', $project_namespace );
		$block             = $this->replaceInString( $block, 'project_namespace', $project_namespace );

		return $block;
	}

	private function createPhpUnitFiles()
	{
		if ( $this->options->get( Opt::CREATE_PHPUNIT_ENV ) )
		{
			$this->createPhpUnitBootstrapFile();
			$this->createPhpUnitConfigFile();
		}
	}

	private function createPhpUnitBootstrapFile()
	{
		$content   = $this->getSkeletonFileContent( 'PhpUnit/bootstrap.php.skel' );
		$file_path = $this->getTargetPathInProject( 'test/Unit/bootstrap.php' );
		$this->putFileContents( $file_path, $content );
	}

	private function createPhpUnitConfigFile()
	{
		$content   = $this->getSkeletonFileContent( 'PhpUnit/phpunit.xml.skel' );
		$file_path = $this->getTargetPathInProject( 'test/Unit/phpunit.xml' );
		$this->putFileContents( $file_path, $content );
	}

	/**
	 * @param string $skeleton_file
	 * @param array  $replacements
	 *
	 * @return string
	 */
	private function getSkeletonFileContent( $skeleton_file, array $replacements = array() )
	{
		$content = file_get_contents( __DIR__ . '/Skeleton/' . $skeleton_file );

		foreach ( $replacements as $name => $value )
		{
			$content = $this->replaceInString( $content, $name, $value );
		}

		return $content;
	}

	/**
	 * @param string $file_path
	 *
	 * @return string
	 */
	private function getTargetPathInProject( $file_path )
	{
		return $this->getProjectDir() . DIRECTORY_SEPARATOR . $file_path;
	}

	/**
	 * @param string $file_path
	 * @param string $content
	 *
	 * @return bool
	 */
	private function putFileContents( $file_path, $content )
	{
		if ( file_put_contents( $file_path, $content ) !== false )
		{
			$this->printLine( $file_path . ' OK', self::COLOR_GREEN );

			return true;
		}
		else
		{
			$this->printLine( $file_path . ' FAILED', self::COLOR_RED );

			return false;
		}
	}

	private function createVagrantFiles()
	{
		if ( $this->options->get( Opt::CREATE_VAGRANT_ENV ) == 'yes' )
		{
			$this->createVagrantFile();
			$this->createVagrantBootstrapShellFile();
			$this->createVagrantSshKeyFile();
			$this->createVagrantSshConfigFile();
			$this->createVagrantNginxConfigFiles();
		}
	}

	private function createVagrantFile()
	{
		$content   = $this->getSkeletonFileContent(
			'Vagrant/Vagrantfile.skel',
			array(
				'project_name'   => $this->options->get( Opt::PROJECT_NAME ),
				'project_domain' => $this->getProjectDomain(),
			)
		);
		$file_path = $this->getTargetPathInProject( 'Vagrantfile' );
		$this->putFileContents( $file_path, $content );
	}

	/**
	 * @throws Exceptions\OptionIsNotSet
	 * @return string
	 */
	private function getProjectDomain()
	{
		$project_name = $this->options->get( Opt::PROJECT_NAME );
		$project_name = preg_replace( "#[^a-z\-0-9]#i", '-', $project_name );

		return strtolower( preg_replace( "#\-+#", '-', $project_name ) );
	}

	private function createVagrantBootstrapShellFile()
	{
		$content   = $this->getSkeletonFileContent(
			'Vagrant/bootstrap.sh.skel',
			array(
				'project_domain' => $this->getProjectDomain(),
			)
		);
		$file_path = $this->getTargetPathInProject( 'env/vagrant/bootstrap.sh' );
		$this->putFileContents( $file_path, $content );
	}

	private function createVagrantSshKeyFile()
	{
		$content   = $this->getSkeletonFileContent( 'Vagrant/id_rsa.skel' );
		$file_path = $this->getTargetPathInProject( 'env/vagrant/id_rsa' );
		$this->putFileContents( $file_path, $content );
	}

	private function createVagrantSshConfigFile()
	{
		$content   = $this->getSkeletonFileContent( 'Vagrant/ssh_config.skel' );
		$file_path = $this->getTargetPathInProject( 'env/vagrant/ssh_config' );
		$this->putFileContents( $file_path, $content );
	}

	private function createVagrantNginxConfigFiles()
	{
		foreach ( array( 'dist', 'doc', 'test' ) as $config_name )
		{
			$content   = $this->getSkeletonFileContent(
				"Vagrant/nginx/{$config_name}.conf.skel",
				array(
					'project_domain' => $this->getProjectDomain(),
				)
			);
			$file_path = $this->getTargetPathInProject( "env/nginx/{$config_name}.conf" );
			$this->putFileContents( $file_path, $content );
		}
	}

	public function postCheck()
	{
	}
}