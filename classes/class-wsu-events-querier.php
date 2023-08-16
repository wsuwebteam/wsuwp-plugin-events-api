<?php namespace WSUWP\Plugin\Events_API;

Plugin::require_class( 'event' );
Plugin::require_class( 'events-querier' );

class WSU_Events_Querier extends Events_Querier {


	private $taxonomies_map = array(
		'types'         => 'event-type',
		'categories'    => 'wsuwp_university_category',
		'tags'          => 'post_tag',
		'organizations' => 'wsuwp_university_org',
	);


	public function get_events() {

		$args = array(
			'post_type'   => 'event',
			'post_status' => array( 'publish', 'passed' ),
			'meta_key'    => 'wp_event_calendar_date_time',
			'meta_type'   => 'DATETIME',
		);

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

	// $terms = array();

	// return $terms;

	// }


	private function get_events_by_search( $args ) {

		$args['s']              = $this->params['search'];
		$args['posts_per_page'] = empty( $this->params['count'] ) ? 10 : $this->params['count'];
		$args['meta_query']     = $this->get_future_event_args();

		if ( ! empty( $this->params['search'] ) ) {
			$args['orderby'] = 'relevance';
			$args['order']   = 'DESC';
		} else {
			$args['orderby'] = 'meta_value';
			$args['order']   = 'ASC';
		}

		return $this->query_events( $args );

	}


	private function get_selected_events( $args ) {

		if ( empty( $this->params['post_ids'] ) ) {
			return array();
		}

		$args['post__in']       = $this->params['post_ids'];
		$args['posts_per_page'] = -1;
		$args['orderby']        = 'meta_value';
		$args['order']          = 'ASC';

		return $this->query_events( $args );

	}


	private function get_events_feed( $args ) {

		$args['posts_per_page'] = $this->params['count'];
		$args['offset']         = $this->params['offset'];
		$args['orderby']        = 'meta_value';
		$args['order']          = 'ASC';

		if ( ! empty( $this->params['exclude'] ) ) {
			$args['post__not_in'] = $this->params['exclude'];
		}

		if ( ! empty( $this->params['types'] ) ) {
			$args['tax_query'][] = array(
				'taxonomy' => $this->taxonomies_map['types'],
				'field'    => 'term_id',
				'terms'    => $this->params['types'],
				'operator' => 'IN',
			);
		}

		if ( ! empty( $this->params['tags'] ) ) {
			$args['tag__in'] = $this->params['tags'];
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

		$args['meta_query'] = $this->get_future_event_args();

		return $this->query_events( $args );

	}


	private function query_events( $args ) {

		$events = array();
		$query  = new \WP_Query( $args );

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$id   = get_the_ID();
				$meta = $this->get_event_meta( $id );

				$venue      = get_the_terms( $id, 'venue-tax' );
				$venue      = ! empty( $venue ) ? $venue[0]->name : '';
				$start_dt   = new \DateTime( $meta['wp_event_calendar_date_time'] );
				$end_dt     = new \DateTime( $meta['wp_event_calendar_end_date_time'] );
				$is_all_day = isset( $meta['wp_event_calendar_all_day'] ) && true === boolval( $meta['wp_event_calendar_all_day'] ) ? true : false;

				$event = new Event(
					$id,
					get_the_title(),
					wp_strip_all_tags( get_the_excerpt() ),
					get_the_content(),
					get_the_permalink(),
					$start_dt->format( 'm/d/Y' ),
					$end_dt->format( 'm/d/Y' ),
					! $is_all_day ? $start_dt->format( 'h:i A' ) : '',
					! $is_all_day ? $end_dt->format( 'h:i A' ) : '',
					$is_all_day,
					$venue,
				);

				array_push( $events, $event );

			}
		}

		wp_reset_postdata();

		return $events;

	}


	private function get_event_meta( $post_id ) {

		$meta = get_post_meta( $post_id );

		return array_map(
			function( $n ) {
				return $n[0];
			},
			$meta
		);

	}


	private function get_future_event_args() {

		$current_time = current_time( 'mysql' );

		return array(
			'wsuwp_event_end_date' => array(
				'key'     => 'wp_event_calendar_end_date_time',
				'value'   => $current_time,
				'compare' => '>',
				'type'    => 'DATETIME',
			),
		);

	}


}
