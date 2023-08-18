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
	 * [<websiteid>]
	 * : The id (or ids, comma separated) of the child sites that need to be synced.
	 *
	 * [--all]
	 * : If set, all child sites will be synced.	 *
	 *
	 * ## EXAMPLES
	 *
	 *     wp mainwp delete_action 2,5
	 *     wp mainwp delete_action --all
	 *
	 * ## Synopsis [<websiteid>] [--all]
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
	public function delete_actions( $args, $assoc_args ) {
		
		//todo: examine commented code functionality
		// support new mainwp sites cli commands.
		/*		
		$handle = MainWP_WP_CLI_Handle::get_assoc_args_commands( 'delete_actions', $assoc_args );
		if ( ! empty( $handle ) ) {
			MainWP_WP_CLI_Handle::handle_cli_callback( 'delete_actions', $args, $assoc_args );
			return;
		}
		*/
		
		$sites = array();
		if ( count( $args ) > 0 ) {
			$args_exploded = explode( ',', $args[0] );
			foreach ( $args_exploded as $arg ) {
				if ( ! is_numeric( trim( $arg ) ) ) {
					\WP_CLI::error( 'Child site ids should be numeric.' );
				}

				$sites[] = trim( $arg );
			}
		}

		if ( ( count( $sites ) == 0 ) && ( ! isset( $assoc_args['all'] ) ) ) {
			\WP_CLI::error( 'Please specify one or more child sites, or use --all.' );
		}
		
		$websites = MainWP_DB::instance()->query( MainWP_DB::instance()->get_sql_websites_for_current_user( false, null, 'wp.url', false, false, null, true ) );
		
		$idLength      = strlen( 'i' );
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
			/*
			if ( $urlLength < strlen( $website->url ) ) {
				$urlLength = strlen( $website->url );
			}
			if ( $versionLength < strlen( $website->version ) ) {
				$versionLength = strlen( $website->version );
			}
			*/
		}
		MainWP_DB::data_seek( $websites, 0 );
						
		while ( $websites && ( $website = MainWP_DB::fetch_object( $websites ) ) ) {
			
			$success = false;
			$error   = '';
			
			if ( ( count( $sites ) == 0 && ! isset( $assoc_args['all'] ) ) || ( !in_array( $website->id, $sites ) && ! isset( $assoc_args['all'] ) ) )
				continue;
			
			try {
				$response = MainWP_Connect::fetch_url_authed( $website, 'delete_actions', array( 'del' => 'act' ) );
				if ( is_array( $response ) ) {
					if ( isset( $response['success'] ) ) {
						$success = true;
					} elseif ( isset( $response['error'] ) ) {
						$error = $response['error'];
					}
				}
			} catch ( \Exception $e ) {
				// ok!
			}
			if ( $success ) {
				MainWP_DB_Site_Actions::instance()->delete_action_by( 'wpid', $website->id );
				//wp_die( wp_json_encode( array( 'success' => 'ok' ) ) );
				//\WP_CLI::line( sprintf( '[%-' . $idLength . 's] %-' . $nameLength . 's - %-' . $urlLength . 's (%-' . $versionLength . 's) removed non-mainwp actions', $website->id, $website->name, $website->url, $website->version ) );
				\WP_CLI::line( sprintf( '[%-' . $idLength . 's] %-' . $nameLength . 's - removed non-mainwp actions', $website->id, $website->name ) );
				continue;
				
			}
			
			if ( empty( $error ) ) {
				\WP_CLI::line( sprintf( '%-' . $idLength . 's %-' . $nameLength . 's - undefined error, please try again', $website->id, $website->name ) );
			}							
		}
		
		MainWP_DB::free_result( $websites );
				
		//wp_die( wp_json_encode( array( 'error' => $error ) ) );
	}
}

MainWP_WP_CLI_Custom_Command::init();

?>
