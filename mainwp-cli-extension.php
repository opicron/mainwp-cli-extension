<?php

namespace MainWP\Dashboard;

// Exit if access directly.
if ( ! defined( 'WP_CLI' ) ) {
	return;
}

class MainWP_WP_CLI_Custom_Command extends MainWP_WP_CLI_Command {

	//include_once __DIR__ . '/commands/class-mainwp-cli-main.php';

	/**
	 * Method init()
	 *
	 * Initiate the MainWP CLI after all Plugins have loaded.
	 */
	public static function init() {
		add_action( 'plugins_loaded', array( self::class, 'init_custom_wpcli_commands' ), 99999 );
	}
	
	/**
	 * Method init_wpcli_commands
	 *
	 * Adds the MainWP WP CLI Commands via WP_CLI::add_command
	 */
	public static function init_custom_wpcli_commands() {
		\WP_CLI::add_command( 'mainwp', self::class );
	}

	/**
	 * List information about added child sites.
	 *
	 * ## OPTIONS
	 *
	 * [--list]
	 *  : Get a list of all child sites
	 *
	 * [--count]
	 *  : If set, count child sites.
	 *
	 *
	 * ## EXAMPLES
	 *
	 *     wp mainwp sites --list
	 *     wp mainwp sites --all-sites-count
	 *
	 * ## Synopsis [--list] [--all-sites-count]
	 *
	 * @param array $args Function arguments.
	 * @param array $assoc_args Function associate arguments.
	 *
	 * @uses \MainWP\Dashboard\MainWP_DB::query()
	 * @uses \MainWP\Dashboard\MainWP_DB::get_sql_websites_for_current_user()
	 * @uses \MainWP\Dashboard\MainWP_DB::fetch_object()
	 * @uses \MainWP\Dashboard\MainWP_DB::free_result()
	 * @uses \MainWP\Dashboard\MainWP_DB::data_seek()
	 */
	public function test( $args, $assoc_args ) {
		
		// support new mainwp sites cli commands.
		
		$handle = MainWP_WP_CLI_Handle::get_assoc_args_commands( 'sites', $assoc_args );
		if ( ! empty( $handle ) ) {
			MainWP_WP_CLI_Handle::handle_cli_callback( 'sites', $args, $assoc_args );
			return;
		}

		$websites = MainWP_DB::instance()->query( MainWP_DB::instance()->get_sql_websites_for_current_user( false, null, 'wp.url', false, false, null, true ) );

		$idLength      = strlen( 'id' );
		$nameLength    = strlen( 'name' );
		$urlLength     = strlen( 'url' );
		$versionLength = strlen( 'version' );
		while ( $websites && ( $website      = MainWP_DB::fetch_object( $websites ) ) ) {
			if ( $idLength < strlen( $website->id ) ) {
				$idLength = strlen( $website->id );
			}
			if ( $nameLength < strlen( $website->name ) ) {
				$nameLength = strlen( $website->name );
			}
			if ( $urlLength < strlen( $website->url ) ) {
				$urlLength = strlen( $website->url );
			}
			if ( $versionLength < strlen( $website->version ) ) {
				$versionLength = strlen( $website->version );
			}
		}
		MainWP_DB::data_seek( $websites, 0 );

		\WP_CLI::line( sprintf( "+%'--" . ( $idLength + 2 ) . "s+%'--" . ( $nameLength + 2 ) . "s+%'--" . ( $urlLength + 2 ) . "s+%'--" . ( $versionLength + 2 ) . 's+', '', '', '', '' ) );
		\WP_CLI::line( sprintf( '| %-' . $idLength . 's | %-' . $nameLength . 's | %-' . $urlLength . 's | %-' . $versionLength . 's |', 'id', 'name', 'url', 'version' ) );
		\WP_CLI::line( sprintf( "+%'--" . ( $idLength + 2 ) . "s+%'--" . ( $nameLength + 2 ) . "s+%'--" . ( $urlLength + 2 ) . "s+%'--" . ( $versionLength + 2 ) . 's+', '', '', '', '' ) );

		while ( $websites && ( $website = MainWP_DB::fetch_object( $websites ) ) ) {
			\WP_CLI::line( sprintf( '| %-' . $idLength . 's | %-' . $nameLength . 's | %-' . $urlLength . 's | %-' . $versionLength . 's |', $website->id, $website->name, $website->url, $website->version ) );
		}

		\WP_CLI::line( sprintf( "+%'--" . ( $idLength + 2 ) . "s+%'--" . ( $nameLength + 2 ) . "s+%'--" . ( $urlLength + 2 ) . "s+%'--" . ( $versionLength + 2 ) . 's+', '', '', '', '' ) );
		MainWP_DB::free_result( $websites );
		
	}
}

MainWP_WP_CLI_Custom_Command::init();

?>
