<?php
/*
Plugin Name: AI Engine: ChatGPT, GPT-3 Content Generation, AI Tools & Playground
Plugin URI: https://wordpress.org/plugins/ai-engine/
Description: Boost your WordPress site with AI magic using AI Engine! It generates content, suggests new titles and excerpts, adds a chatbot, and allows access to the AI Playground. It also tracks API usage statistics.
Version: 0.1.0
Author: Jordy Meow
Author URI: https://jordymeow.com
Text Domain: ai-engine

Dual licensed under the MIT and GPL licenses:
http://www.opensource.org/licenses/mit-license.php
http://www.gnu.org/licenses/gpl.html
*/

define( 'MWAI_VERSION', '0.1.0' );
define( 'MWAI_PREFIX', 'mwai' );
define( 'MWAI_DOMAIN', 'ai-engine' );
define( 'MWAI_ENTRY', __FILE__ );
define( 'MWAI_PATH', dirname( __FILE__ ) );
define( 'MWAI_URL', plugin_dir_url( __FILE__ ) );

require_once( 'classes/init.php' );

?>
