<?php

class Meow_MWAI_Rest
{
	private $admin = null;
	private $core = null;
	private $namespace = 'ai-engine/v1';

	public function __construct( $core, $admin ) {
		if ( !current_user_can( 'administrator' ) ) {
			return;
		} 
		$this->core = $core;
		$this->admin = $admin;
		add_action( 'rest_api_init', array( $this, 'rest_api_init' ) );
	}

	function rest_api_init() {
		try {
			// SETTINGS
			register_rest_route( $this->namespace, '/update_option', array(
				'methods' => 'POST',
				'permission_callback' => array( $this->core, 'can_access_settings' ),
				'callback' => array( $this, 'rest_update_option' )
			) );
			register_rest_route( $this->namespace, '/all_settings', array(
				'methods' => 'GET',
				'permission_callback' => array( $this->core, 'can_access_settings' ),
				'callback' => array( $this, 'rest_all_settings' ),
			) );
			register_rest_route( $this->namespace, '/completions', array(
				'methods' => 'POST',
				'permission_callback' => array( $this->core, 'can_access_features' ),
				'callback' => array( $this, 'completions' ),
			) );
			register_rest_route( $this->namespace, '/generate_titles', array(
				'methods' => 'POST',
				'permission_callback' => array( $this->core, 'can_access_features' ),
				'callback' => array( $this, 'generate_titles' ),
			) );
			register_rest_route( $this->namespace, '/generate_excerpts', array(
				'methods' => 'POST',
				'permission_callback' => array( $this->core, 'can_access_features' ),
				'callback' => array( $this, 'generate_excerpts' ),
			) );
			register_rest_route( $this->namespace, '/update_post_title', array(
				'methods' => 'POST',
				'permission_callback' => array( $this->core, 'can_access_features' ),
				'callback' => array( $this, 'update_post_title' ),
			) );
			register_rest_route( $this->namespace, '/update_post_excerpt', array(
				'methods' => 'POST',
				'permission_callback' => array( $this->core, 'can_access_features' ),
				'callback' => array( $this, 'update_post_excerpt' ),
			) );
		}
		catch ( Exception $e ) {
			var_dump( $e );
		}
	}

	function rest_all_settings() {
		return new WP_REST_Response( [
			'success' => true,
			'data' => $this->admin->get_all_options()
		], 200 );
	}

	function rest_update_option( $request ) {
		try {
			$params = $request->get_json_params();
			$value = $params['options'];
			$options = $this->core->update_options( $value );
			$success = !!$options;
			$message = __( $success ? 'OK' : "Could not update options.", 'ai-engine' );
			return new WP_REST_Response([ 'success' => $success, 'message' => $message, 'options' => $options ], 200 );
		}
		catch ( Exception $e ) {
			return new WP_REST_Response([ 'success' => false, 'message' => $e->getMessage() ], 500 );
		}
	}

	function validate_updated_option( $option_name ) {
		$option_checkbox = get_option( 'mwai_option_checkbox', false );
		$option_text = get_option( 'mwai_option_text', 'Default' );
		if ( $option_checkbox === '' )
			update_option( 'mwai_option_checkbox', false );
		if ( $option_text === '' )
			update_option( 'mwai_option_text', 'Default' );
		return $this->createValidationResult();
	}

	function generate_titles( $request ) {
		try {
			$params = $request->get_json_params();
			$postId = intval( $params['postId'] );
			$titles = $this->core->generate_titles( $postId );
			$success = !!$titles;
			$message = __( $success ? 'OK' : "Could not fetch titles.", 'ai-engine' );
			return new WP_REST_Response([ 'success' => $success, 'message' => $message, 'titles' => $titles ], 200 );
		}
		catch ( Exception $e ) {
			return new WP_REST_Response([ 'success' => false, 'message' => $e->getMessage() ], 500 );
		}
	}

	function generate_excerpts( $request ) {
		try {
			$params = $request->get_json_params();
			$postId = intval( $params['postId'] );
			$excerpts = $this->core->generate_excerpts( $postId );
			$success = !!$excerpts;
			$message = __( $success ? 'OK' : "Could not fetch excerpts.", 'ai-engine' );
			return new WP_REST_Response([ 'success' => $success, 'message' => $message, 'excerpts' => $excerpts ], 200 );
		}
		catch ( Exception $e ) {
			return new WP_REST_Response([ 'success' => false, 'message' => $e->getMessage() ], 500 );
		}
	}

	function update_post_title( $request ) {
		try {
			$params = $request->get_json_params();
			$title = sanitize_text_field( $params['title'] );
			$this->core->update_post_title( $params['postId'], $title );
			return new WP_REST_Response([ 'success' => true, 'message' => "Title updated." ], 200 );
		}
		catch ( Exception $e ) {
			return new WP_REST_Response([ 'success' => false, 'message' => $e->getMessage() ], 500 );
		}
	}

	function update_post_excerpt( $request ) {
		try {
			$params = $request->get_json_params();
			$excerpt = sanitize_text_field( $params['excerpt'] );
			$this->core->update_post_excerpt( $params['postId'], $excerpt );
			return new WP_REST_Response([ 'success' => true, 'message' => "Excerpt updated." ], 200 );
		}
		catch ( Exception $e ) {
			return new WP_REST_Response([ 'success' => false, 'message' => $e->getMessage() ], 500 );
		}
	}

	function completions( $request ) {
		try {
			$params = $request->get_json_params();
			$res = $this->core->get_completions( $params['prompt'] );
			$success = !!$res;
			$message = __( $success ? 'OK' : "Could not fetch completions.", 'ai-engine' );
			return new WP_REST_Response([ 'success' => $success, 'message' => $message,
				'data' => $res['data'], 'usage' => $res['usage'] ], 200 );
		}
		catch ( Exception $e ) {
			return new WP_REST_Response([ 'success' => false, 'message' => $e->getMessage() ], 500 );
		}
	}

	function createValidationResult( $result = true, $message = null) {
		$message = $message ? $message : __( 'OK', 'ai-engine' );
		return ['result' => $result, 'message' => $message];
	}
}
