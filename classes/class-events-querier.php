<?php namespace WSUWP\Plugin\Events_API;

abstract class Events_Querier {

	protected $request_params = array();

	protected $params = array();

	public function __construct( $request_params ) {

		$this->request_params = $request_params;
		$this->params         = $this->process_params();

	}

	private function process_params() {

		return array(
			'search'        => $this->get_param( 'search' ),
			'type'          => $this->get_param( 'type', 'feed' ), // feed | select | search
			'count'         => (int) $this->get_param( 'count', 5 ),
			'post_ids'      => $this->convert_to_array_of_ids( $this->get_param( 'post-ids' ) ),
			'exclude'       => $this->convert_to_array_of_ids( $this->get_param( 'exclude' ) ),
			'types'         => $this->convert_to_array_of_ids( $this->get_param( 'types' ) ),
			'categories'    => $this->convert_to_array_of_ids( $this->get_param( 'categories' ) ),
			'tags'          => $this->convert_to_array_of_ids( $this->get_param( 'tags' ) ),
			'organizations' => $this->convert_to_array_of_ids( $this->get_param( 'organizations' ) ),
			'locations'     => $this->convert_to_array_of_ids( $this->get_param( 'locations' ) ),
			'offset'        => (int) $this->get_param( 'offset', 0 ),
		);

	}

	protected function get_param( $name, $default = '' ) {

		return isset( $this->request_params[ $name ] ) ? sanitize_text_field( $this->request_params[ $name ] ) : $default;

	}


	protected function convert_to_array_of_ids( $ids_string ) {

		if ( ! empty( $ids_string ) ) {
			return array_map( 'absint', explode( ',', $ids_string ) );
		}

		return array();

	}

	abstract public function get_events();

	// abstract public function get_terms();

}
