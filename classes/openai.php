<?php

class Meow_MWAI_OpenAI {
  private $core = null;
  private $model = "text-davinci-003";

  public function __construct( $core ) {
    $this->core = $core;
    $this->apiKey = $this->core->get_option( 'openai_apikey' );
  }

  // Record usage of the API on a monthly basis
  public function record_usage( $model, $prompt_tokens, $completion_tokens ) {
    if ( !$model || !$prompt_tokens || !$completion_tokens ) {
      throw new Exception( 'Missing parameters for record_usage.' );
    }
    $usage = $this->core->get_option( 'openai_usage' );
    $month = date( 'Y-m' );
    if ( !isset( $usage[$month] ) ) {
      $usage[$month] = array();
    }
    if ( !isset( $usage[$month][$model] ) ) {
      $usage[$month][$model] = array(
        'prompt_tokens' => 0,
        'completion_tokens' => 0,
        'total_tokens' => 0
      );
    }
    $usage[$month][$model]['prompt_tokens'] += $prompt_tokens;
    $usage[$month][$model]['completion_tokens'] += $completion_tokens;
    $usage[$month][$model]['total_tokens'] += $prompt_tokens + $completion_tokens;
    $this->core->update_option( 'openai_usage', $usage );
    return [
      'prompt_tokens' => $prompt_tokens,
      'completion_tokens' => $completion_tokens,
      'total_tokens' => $prompt_tokens + $completion_tokens
    ];
  }

  public function getCompletions( $prompt, $maxResults = 1 ) {
    $url = 'https://api.openai.com/v1/completions';
    $options = array(
      "http" => array(
        "header" => "Content-Type: application/json\r\n" . "Authorization: Bearer " . $this->apiKey . "\r\n",
        "method" => "POST",
        "content" => json_encode( array(
          "model" => $this->model,
          "prompt" => $prompt,
          "n" => $maxResults,
          "max_tokens" => 280,
          "temperature" => 0.5
        ) ),
      ),
      "ssl" => array(
        "verify_peer" => false,
        "verify_peer_name" => false,
      )
    );
    $context = stream_context_create( $options );
    $response = file_get_contents( $url, false, $context );
    if ( $response !== false ) {
      $data = json_decode( $response, true );
      $usage = $this->record_usage( $data['model'], $data['usage']['prompt_tokens'], $data['usage']['completion_tokens'] );
      //error_log( print_r( $usage, 1 ) );
      $answer = "";
      $answers = array();
      foreach ( $data['choices'] as $choice ) {
        $text = trim( $choice['text'] );
        $answers[] = $text;
        $answer = $text;
      }
      return [
        "usage" => $usage,
        "data" => $answer,
      ];
    }
    return null;
  }

  public function getRecommendedTitles( $text, $maxResults = 3 ) {
    $url = 'https://api.openai.com/v1/completions';
    $options = array(
      "http" => array(
        "header" => "Content-Type: application/json\r\n" . "Authorization: Bearer " . $this->apiKey . "\r\n",
        "method" => "POST",
        "content" => json_encode( array(
          "model" => $this->model,
          "prompt" => 'Generate short SEO-friendly title. Text: ' . $text,
          "n" => $maxResults,
          "max_tokens" => 40,
          "temperature" => 0.5
        ) ),
      ),
      "ssl" => array(
        "verify_peer" => false,
        "verify_peer_name" => false,
      )
    );
    $context = stream_context_create( $options );
    $response = file_get_contents( $url, false, $context );
    if ( $response !== false ) {
      $data = json_decode( $response, true );
      $usage = $this->record_usage( $data['model'], $data['usage']['prompt_tokens'], $data['usage']['completion_tokens'] );
      //error_log( print_r( $usage, 1 ) );
      $titles = array();
      foreach ( $data['choices'] as $choice ) {
        $choice['text'] = str_replace( '"', '', $choice['text'] );
        $choice['text'] = preg_replace( '/\s+/', ' ', $choice['text'] );
        $choice['text'] = trim( $choice['text'] );
        $titles[] = $choice['text'];
      }
      return $titles;
    }
    return null;
  }

  public function getRecommendedExcerpts( $text, $maxResults = 3 ) {
    $url = 'https://api.openai.com/v1/completions';
    $options = array(
      "http" => array(
        "header" => "Content-Type: application/json\r\n" . "Authorization: Bearer " . $this->apiKey . "\r\n",
        "method" => "POST",
        "content" => json_encode( array(
          "model" => $this->model,
          "prompt" => 'Generate short SEO-friendly excerpts (between 120 and 170 chars). Text: ' . $text,
          "n" => $maxResults,
          "max_tokens" => 140,
          "temperature" => 0.5
        ) ),
      ),
      "ssl" => array(
        "verify_peer" => false,
        "verify_peer_name" => false,
      )
    );
    $context = stream_context_create( $options );
    $response = file_get_contents( $url, false, $context );
    if ( $response !== false ) {
      $data = json_decode( $response, true );
      $usage = $this->record_usage( $data['model'], $data['usage']['prompt_tokens'], $data['usage']['completion_tokens'] );
      //error_log( print_r( $usage, 1 ) );
      $excerpts = array();
      foreach ( $data['choices'] as $choice ) {
        $choice['text'] = str_replace( '"', '', $choice['text'] );
        $choice['text'] = preg_replace( '/\s+/', ' ', $choice['text'] );
        $choice['text'] = trim( $choice['text'] );
        $excerpts[] = $choice['text'];
      }
      return $excerpts;
    }
  }
}
