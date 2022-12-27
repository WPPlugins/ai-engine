<?php

class Meow_MWAI_Core
{
	public $admin = null;
	public $is_rest = false;
	public $is_cli = false;
	public $site_url = null;
	private $option_name = 'mwai_options';

	public function __construct() {
		$this->site_url = get_site_url();
		$this->is_rest = MeowCommon_Helpers::is_rest();
		$this->is_cli = defined( 'WP_CLI' ) && WP_CLI;
		add_action( 'plugins_loaded', array( $this, 'init' ) );
	}

	function init() {
		// Part of the core, settings and stuff
		$this->admin = new Meow_MWAI_Admin( $this );

		// Only for REST
		if ( $this->is_rest ) {
			new Meow_MWAI_Rest( $this, $this->admin );
		}

		// Dashboard
		if ( is_admin() ) {
			new Meow_MWAI_UI( $this, $this->admin );
		}
	}

	#region Helpers
	function can_access_settings() {
		return apply_filters( 'mwai_allow_setup', current_user_can( 'manage_options' ) );
	}

	function can_access_features() {
		return apply_filters( 'mwai_allow_usage', current_user_can( 'administrator' ) );
	}

	function get_text_from_postId( $postId ) {
		$post = get_post( $postId );
		if ( !$post ) {
			return false;
		}
		$post->post_content = apply_filters( 'the_content', $post->post_content );
		$text = strip_tags( $post->post_content );
		$text = preg_replace( '/^\h*\v+/m', '', $text );
		$text = html_entity_decode( $text );
		return $text;
	}
	#endregion

	#region Options

	function get_option( $option, $default = null ) {
		$options = $this->get_all_options();
		return $options[$option] ?? $default;
	}

	function list_options() {
		return array(
			'module_titles' => true,
			'module_excerpts' => true,
			'module_blocks' => false,
			'openai_apikey' => false,
			'openai_usage' => []
		);
	}

	function get_all_options() {
		$options = get_option( $this->option_name, null );
		return $options;
	}

	function update_options( $options ) {
		if ( !update_option( $this->option_name, $options, false ) ) {
			return false;
		}
		$options = $this->sanitize_options();
		return $options;
	}

	function update_option( $option, $value ) {
		$options = $this->get_all_options();
		$options[$option] = $value;
		return $this->update_options( $options );
	}

	// Validate and keep the options clean and logical.
	function sanitize_options() {
		$options = $this->get_all_options();
		$needs_update = false;
		// We can sanitize our future options here, let's always remember it.

		if ( $needs_update ) {
			update_option( $this->option_name, $options, false );
		}
		return $options;
	}

	#endregion

	#region AI

	function generate_titles( $postId ) {
		$text = $this->get_text_from_postId( $postId );
		if ( empty( $text ) ) {
			throw new Exception( 'There is no content for this post.' );
		}
		$openai = new Meow_MWAI_OpenAI( $this );
		$recommendedTitles = $openai->getRecommendedTitles( $text );
		return $recommendedTitles;
	}

	function generate_excerpts( $postId ) {
		$text = $this->get_text_from_postId( $postId );
		if ( empty( $text ) ) {
			throw new Exception( 'There is no content for this post.' );
		}
		$openai = new Meow_MWAI_OpenAI( $this );
		$recommendedExcerpts = $openai->getRecommendedExcerpts( $text );
		return $recommendedExcerpts;
	}

	function get_completions( $text, $maxTokens = 100, $temperature = 0.5, $topP = 1,
		$frequencyPenalty = 0, $presencePenalty = 0, $bestOf = 1 ) {
		$openai = new Meow_MWAI_OpenAI( $this );
		$res = $openai->getCompletions( $text );
		return $res;
	}

	function update_post_title( $postId, $title ) {
		$post = get_post( $postId );
		if ( !$post ) {
			throw new Exception( 'There is no post with this ID.' );
		}
		$post->post_title = $title;
		//$post->post_name = sanitize_title( $title );
		wp_update_post( $post );
	}

	function update_post_excerpt( $postId, $excerpt ) {
		$post = get_post( $postId );
		if ( !$post ) {
			throw new Exception( 'There is no post with this ID.' );
		}
		$post->post_excerpt = $excerpt;
		wp_update_post( $post );
	}

	#endregion

}

?>