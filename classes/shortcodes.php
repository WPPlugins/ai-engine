<?php

class Meow_MWAI_Shortcodes {
  private $core = null;
  private $namespace = 'ai-engine/v1';

  public function __construct( $core ) {
    $this->core = $core;
    add_shortcode( 'mwai_chat', array( $this, 'chat' ) );
    add_action( 'rest_api_init', array( $this, 'rest_api_init' ) );
  }

  function rest_api_init() {
		try {
			register_rest_route( $this->namespace, '/chat', array(
				'methods' => 'POST',
				'callback' => array( $this, 'rest_chat' ),
        'permission_callback' => '__return_true'
			) );
		}
		catch ( Exception $e ) {
			var_dump( $e );
		}
	}

  function rest_chat( $request ) {
    try {
			$params = $request->get_json_params();
			$prompt = $params['prompt'];
      $model = $params['model'];
      $temperature = $params['temperature'];
			$query = new Meow_MWAI_Query( $prompt, 1024 );
      if ( $model ) {
        $query->setModel( $model );
      }
      if ( $temperature ) {
        $query->setTemperature( $temperature );
      }
			$answer = $this->core->ai->run( $query );
			return new WP_REST_Response([ 'success' => true, 'data' => $answer->result, 'usage' => $answer->usage ], 200 );
		}
		catch ( Exception $e ) {
			return new WP_REST_Response([ 'success' => false, 'message' => $e->getMessage() ], 500 );
		}
  }

  function chat( $atts ) {
    $defaults = apply_filters( 'mwai_chat_atts', [
      'id' => uniqid(),
      'context' => "Converse as if you were an AI assistant. Be friendly, creative.",
      'ai_prompt' => "AI: ",
      'user_prompt' => "You: ",
      'sys_prompt' => "System: ",
      'start_sentence' => "Hi! How can I help you?",
      'model' => 'text-davinci-003',
      'temperature' => 0.8
    ] );
    $atts = shortcode_atts( $defaults, $atts, 'meow_ai' );
    $id = $atts['id'];
    $apiUrl = get_rest_url( null, 'ai-engine/v1/chat' );
    $onSentClickFn = "mwai_{$id}_onSendClick";
    $addReplyFn = "mwai_{$id}_addReply";
    $convertToHtmlFn = "mwai_{$id}_convertToHtml";
    ob_start();
    ?>

      <div id="mwai-chat-<?= $id ?>" class="mwai-chat">
        <div class="mwai-conversation">
        </div>
        <div class="mwai-input">
          <input type="text" placeholder="Type your message here..." />
          <button>Send</button>
        </div>
      </div>

      <script>
        function <?= $convertToHtmlFn ?>(text) {
          // Split the input text into an array of lines
          const lines = text.split('\n');

          // Initialize an empty array to store the HTML
          const html = [];

          // Initialize a variable to keep track of the current list type (none, bullet, or numbered)
          let listType = 'none';

          // Iterate over the lines
          for (const line of lines) {
            // Check if the line is a bullet point
            if (line.startsWith('*')) {
              // If the current list type is not a bullet point, start a new bullet point list
              if (listType !== 'bullet') {
                html.push('<ul>');
                listType = 'bullet';
              }
              // Add the bullet point to the HTML
              html.push(`<li>${line.substring(1).trim()}</li>`);
            }
            // Check if the line is a numbered list item
            else if (line.match(/^\d+\./)) {
              // If the current list type is not a numbered list, start a new numbered list
              if (listType !== 'numbered') {
                html.push('<ol>');
                listType = 'numbered';
              }
              // Add the numbered list item to the HTML
              html.push(`<li>${line.substring(line.indexOf('.') + 1).trim()}</li>`);
            }
            // Otherwise, the line is not part of a list
            else {
              // If the current list type is not "none", close the current list
              if (listType !== 'none') {
                html.push(listType === 'bullet' ? '</ul>' : '</ol>');
                listType = 'none';
              }

              // If the line starts with the AI or user prompt, replace it with a span with the appropriate class
              if (line.startsWith('<?= $atts['ai_prompt'] ?>')) {
                html.push(`<p><span class="mwai-ai-prompt"><?= $atts['ai_prompt'] ?></span>${line.substring('<?= $atts['ai_prompt'] ?>'.length).trim()}</p>`);
              }
              else if (line.startsWith('<?= $atts['user_prompt'] ?>')) {
                html.push(`<p><span class="mwai-user-prompt"><?= $atts['user_prompt'] ?></span>${line.substring('<?= $atts['user_prompt'] ?>'.length).trim()}</p>`);
              }
              else {
                // Otherwise, add the line as a paragraph to the HTML
                html.push(`<p>${line.trim()}</p>`);
              }
            }
          }

          // If the current list type is not "none", close the current list
          if (listType !== 'none') {
            html.push(listType === 'bullet' ? '</ul>' : '</ol>');
          }

          // Join the HTML array into a single string and return it
          return html.join('');
        }

        // Function to add a reply in the conversation
        function <?= $addReplyFn ?>(text, type = 'user') {
          var conversation = document.querySelector('#mwai-chat-<?= $id ?> .mwai-conversation');
          text = <?= $convertToHtmlFn ?>(text);
          if (type === 'ai') {
            conversation.innerHTML += '<div class="mwai-ai">' + text + '</div>';
          }
          else if (type === 'system') {
            conversation.innerHTML += '<div class="mwai-system">' + text + '</div>';
          }
          else {
            conversation.innerHTML += '<div class="mwai-user">' + text + '</div>';
          }
        }

        // Function to request the completion
        function <?= $onSentClickFn ?>() {

          // Disable the button
          var button = document.querySelector('#mwai-chat-<?= $id ?> .mwai-input button');
          button.disabled = true;

          // Add the user reply
          var input = document.querySelector('#mwai-chat-<?= $id ?> .mwai-input input');
          <?= $addReplyFn ?>('<?= $atts['user_prompt'] ?>' + input.value, 'user');
          input.value = '';
          input.disabled = true;

          // Request the completion and add the reply
          var conversation = document.querySelector('#mwai-chat-<?= $id ?> .mwai-conversation');
          var promptToUse = '<?= $atts['context'] ?>' + conversation.innerText + '\n';
          fetch('<?= $apiUrl ?>', { method: 'POST', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ 
              prompt: promptToUse,
              model: '<?= $atts['model'] ?>',
              temperature: '<?= $atts['temperature'] ?>'
            })
          })
          .then(response => response.json())
          .then(data => {
            console.log(data);
            if (!data.success) {
              <?= $addReplyFn ?>('<?= $atts['sys_prompt'] ?>' + data.message, 'system');
            }
            else {
              <?= $addReplyFn ?>(data.data, 'ai');
            }
            button.disabled = false;
            input.disabled = false;
          })
          .catch(error => {
            console.error(error);
            button.disabled = false;
            input.disabled = false;
          });
        }

        var input = document.querySelector('#mwai-chat-<?= $id ?> .mwai-input input');
        input.addEventListener('keypress', (event) => {
          if (event.keyCode === 13) {
            <?= $onSentClickFn ?>(); 
          }
        });

        var button = document.querySelector('#mwai-chat-<?= $id ?> .mwai-input button');
        button.addEventListener('click', (event) => {
          <?= $onSentClickFn ?>(); 
        });

        <?= $addReplyFn ?>('<?= $atts['ai_prompt'] ?>' + '<?= $atts['start_sentence'] ?>', 'ai');
      </script>

    <?php
    $output = ob_get_contents();
    ob_end_clean();
    return $output;
  }
}