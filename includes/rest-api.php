<?php namespace WSUWP\Plugin\Events_API;

Plugin::require_class( 'wsu-events-querier' );
Plugin::require_class( 'tribe-events-querier' );

class Rest_API {


	public static function init() {

		add_action( 'rest_api_init', array( __CLASS__, 'register_routes' ) );

	}


	public static function register_routes() {

		register_rest_route(
			'wsu-events/v1',
			'get-events',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( __CLASS__, 'get_events' ),
				'permission_callback' => '__return_true',
			)
		);

		register_rest_route(
			'wsu-events/v1',
			'debug',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( __CLASS__, 'debug' ),
				'permission_callback' => '__return_true',
			)
		);

		/*
		register_rest_route(
			'wsu-events/v1',
			'get-terms',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( __CLASS__, 'get_terms' ),
				'permission_callback' => '__return_true',
			)
		);
		*/
	}


	public static function debug( \WP_REST_Request $request ) {

		$args          = array(
			'posts_per_page' => 5,
			'offset'         => 0,
			'ends_after'     => 'now',
		);
		$args2         = array(
			'posts_per_page' => 5,
			'offset'         => 0,
			'start_date'     => 'now',
		);
		$tribe_events  = tribe_get_events( $args, false );
		$tribe_events2 = tribe_get_events( $args2, false );

		return array(

			'ends_after' => $tribe_events,
			'start_date' => $tribe_events2,
		);
	}


	public static function get_events( \WP_REST_Request $request ) {

		$params        = $request->get_params();
		$event_querier = self::get_event_querier( $params );

		if ( ! $event_querier ) {
			return new \WP_Error( 'error', 'Could not find a supported events calendar.', array( 'status' => 500 ) );
		}

		$events = $event_querier->get_events();

		return array_map(
			function( $e ) {
				return $e->serialize();
			},
			$events
		);

	}

	/*
	public static function get_terms( \WP_REST_Request $request ) {

		$params        = $request->get_params();
		$event_querier = self::get_event_querier( $params );

		if ( ! $event_querier ) {
			return new \WP_Error( 'error', 'Could not find a supported events calendar.', array( 'status' => 500 ) );
		}

		return $event_querier->get_terms();

	}
	*/

	private static function get_event_querier( $params ) {

		$active_plugins = get_option( 'active_plugins' );

		foreach ( $active_plugins as $plugin ) {
			$name = strtolower( explode( '/', $plugin )[0] );

			switch ( $name ) {
				case 'wp-event-calendar':
					return new WSU_Events_Querier( $params );

				case 'the-events-calendar':
					return new Tribe_Events_Querier( $params );
			}
		}

		return null;

	}


	private static function starts_with( $haystack, $needle ) {

		$length = strlen( $needle );

		return substr( $haystack, 0, $length ) === $needle;

	}


}

Rest_API::init();
