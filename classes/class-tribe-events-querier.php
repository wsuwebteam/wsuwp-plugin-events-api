<?php namespace WSUWP\Plugin\Events_API;

Plugin::require_class( 'events-querier' );

class Tribe_Events_Querier extends Events_Querier {

	private $taxonomies_map = array(
		'categories'    => 'tribe_events_cat',
		'tags'          => 'post_tag',
		'organizations' => 'wsuwp_university_org',
	);


	public function get_events() {

		$args = array();

		$type = isset( $this->request_params['search'] ) ? 'search' : $this->params['type'];

		switch ( $type ) {
			case 'search':
				return $this->get_events_by_search( $args );
			case 'select':
				return $this->get_selected_events( $args );
			case 'feed':
				return $this->get_events_feed( $args );
		}

	}


	// public function get_terms() {

	// var_dump( $this->params );

	// }


	private function get_events_by_search( $args ) {

		$args['s']              = $this->params['search'];
		$args['posts_per_page'] = empty( $this->params['count'] ) ? 10 : $this->params['count'];
		$args['ends_after']     = 'now';

		if ( ! empty( $this->params['search'] ) ) {
			$args['orderby'] = 'relevance';
			$args['order']   = 'DESC';
		}

		return $this->query_events( $args );

	}


	private function get_selected_events( $args ) {

		if ( empty( $this->params['post_ids'] ) ) {
			return array();
		}

		$args['post__in']       = $this->params['post_ids'];
		$args['posts_per_page'] = -1;

		return $this->query_events( $args );

	}


	private function get_events_feed( $args ) {

		$args['posts_per_page'] = $this->params['count'];
		$args['offset']         = $this->params['offset'];
		$args['ends_after']     = 'now';

		if ( ! empty( $this->params['exclude'] ) ) {
			$args['post__not_in'] = $this->params['exclude'];
		}

		if ( ! empty( $this->params['tags'] ) ) {
			$args['tax_query'][] = array(
				'taxonomy' => 'post_tag',
				'field'    => 'term_id',
				'terms'    => $this->params['tags'],
				'operator' => 'IN',
			);
		}

		if ( ! empty( $this->params['categories'] ) ) {
			$args['tax_query'][] = array(
				'taxonomy' => $this->taxonomies_map['categories'],
				'field'    => 'term_id',
				'terms'    => $this->params['categories'],
				'operator' => 'IN',
			);
		}

		if ( ! empty( $this->params['organizations'] ) ) {
			$args['tax_query'][] = array(
				'taxonomy' => $this->taxonomies_map['organizations'],
				'field'    => 'term_id',
				'terms'    => $this->params['organizations'],
				'operator' => 'IN',
			);
		}

		if ( ! empty( $args['tax_query'] ) ) {
			$args['tax_query']['relation'] = 'AND';
		}

		return $this->query_events( $args );

	}


	private function query_events( $args ) {

		// Ensure the global $post variable is in scope
		global $post;

		$events       = array();
		$tribe_events = tribe_get_events( $args, false );

		// print_r( $tribe_events );
		// die();

		foreach ( $tribe_events as $p ) {
			setup_postdata( $p );
			$id = $p->ID;

			$is_all_day = tribe_event_is_all_day( $id );

			$event = new Event(
				$id,
				get_the_title( $p ),
				wp_strip_all_tags( tribe_post_excerpt( $p ) ),
				tribe_get_the_content( null, false, $p ),
				tribe_get_event_link( $p ),
				tribe_get_start_date( $id, false, 'm/d/Y' ),
				tribe_get_end_date( $id, false, 'm/d/Y' ),
				! $is_all_day ? tribe_get_start_date( $id, true, 'h:i A' ) : '',
				! $is_all_day ? tribe_get_end_date( $id, true, 'h:i A' ) : '',
				$is_all_day,
				tribe_get_venue( $id ),
			);

			array_push( $events, $event );

		}

		wp_reset_query();

		return $events;

	}


}
