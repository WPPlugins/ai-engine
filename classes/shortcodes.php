<?php

class Meow_MWAI_Shortcodes {
  private $core = null;
  private $namespace = 'ai-engine/v1';

  public function __construct( $core ) {
    $this->core = $core;
    add_shortcode( 'mwai_chat', array( $this, 'chat' ) );
    add_action( 'rest_api_init', array( $this, 'rest_api_init' ) );

    // If we apply ChatGPT styles
    add_filter( 'mwai_chat_html', array( $this, 'chatgpt_style' ), 10, 2 );
    add_filter( 'mwai_chat_html', array( $this, 'spinner_style' ), 10, 2 );
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

  function spinner_style( $html, $atts ) {
    // If needed, we can use the $id to apply styles
    $id = $atts['id']; // This could be replace by the ID of a specific chatbot
    return "
      <style>
        #mwai-chat-$id button {
          position: relative;
        }

        #mwai-chat-$id button[disabled] span {
          display: none;
        }

        #mwai-chat-$id button[disabled]::after {
          content: '';
          position: absolute;
          width: 18px;
          height: 18px;
          top: 0;
          left: 0;
          right: 0;
          bottom: 0;
          margin: auto;
          border: 3px solid transparent;
          border-top-color: #ffffff;
          border-radius: 50%;
          animation: mwai-button-spinner 1s ease infinite;
        }

        @keyframes mwai-button-spinner {
          from {
             transform: rotate(0turn);
          }
      
          to {
            transform: rotate(1turn);
          }
        }
      </style>
    " . $html;
  }

  function chatgpt_style( $html, $atts ) {
    // If needed, we can use the $id to apply styles
    $id = $atts['id']; // This could be replace by the ID of a specific chatbot
    return "
      <style>
        #mwai-chat-$id {
          background: #343541;
          color: white;
          font-size: 15px;
        }

        #mwai-chat-$id  ol {
          padding: 0;
          margin: 0 0 0 20px;
        }
        
        #mwai-chat-$id .mwai-reply {
          display: flex;
        }

        #mwai-chat-$id .mwai-user, #mwai-chat-$id .mwai-ai {
          padding: 20px;
        }
        
        #mwai-chat-$id .mwai-ai {
          background: #454654;
        }

        #mwai-chat-$id .mwai-user-name, #mwai-chat-$id .mwai-ai-name {
          color: #a0a0a0;
          margin-right: 5px;
          min-width: 80px;
        }

        #mwai-chat-$id .mwai-input {
          display: flex;
          padding: 15px;
        }

        #mwai-chat-$id .mwai-input input {
          background: #40414f;
          color: white;
          flex: auto;
          padding: 15px;
          border: none;
          border-radius: 5px;
          font-size: 15px;
        }

        #mwai-chat-$id .mwai-input button {
          background: none;
          color: white;
          border: 1px solid #40414f;
          margin-left: 15px;
          width: 80px;
          border-radius: 5px;
          cursor: pointer;
        }
      </style>
    " . $html;
  }

  function rest_chat( $request ) {
    try {
			$params = $request->get_json_params();
			$prompt = $params['prompt'];
      $model = $params['model'];
      $temperature = $params['temperature'];
      $apiKey = $params['apiKey'];
			$query = new Meow_MWAI_Query( $prompt, 1024 );
      if ( $model ) {
        $query->setModel( $model );
      }
      if ( $temperature ) {
        $query->setTemperature( $temperature );
      }
      if ( $apiKey ) {
        $query->setApiKey( $apiKey );
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
      'ai_name' => "AI: ",
      'user_name' => "You: ",
      'sys_name' => "System: ",
      'start_sentence' => "Hi! How can I help you?",
      'model' => 'text-davinci-003',
      'temperature' => 0.8,
      'api_key' => ''
    ] );
    $atts = shortcode_atts( $defaults, $atts, 'meow_ai' );
    $id = $atts['id'];
    $apiUrl = get_rest_url( null, 'ai-engine/v1/chat' );
    $onSentClickFn = "mwai_{$id}_onSendClick";
    $addReplyFn = "mwai_{$id}_addReply";
    $convertToHtmlFn = "mwai_{$id}_convertToHtml";
    $aiName = trim($atts['ai_name']);
    $userName = trim($atts['user_name']);
    ob_start();
    ?>

      <div id="mwai-chat-<?= $id ?>" class="mwai-chat">
        <div class="mwai-conversation">
        </div>
        <div class="mwai-input">
          <input type="text" placeholder="Type your message here..." />
          <button><span>Send</span></button>
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

              // console.log('AI PROMPT: ', '|<?= $aiName ?>|');
              // console.log('USER PROMPT: ', '|<?= $userName ?>|');
              // console.log(line);

              if (line.trim() === '') {
                continue;
              }
              // If the line starts with the AI or user prompt, replace it with a span with the appropriate class
              else if (line.startsWith('<?= $aiName ?>')) {
                html.push(`<span class="mwai-ai-name"><?= $aiName ?> </span><span>${line.substring('<?= $aiName ?>'.length).trim()}</span>`);
              }
              else if (line.startsWith('<?= $userName ?>')) {
                html.push(`<span class="mwai-user-name"><?= $userName ?> </span><span>${line.substring('<?= $userName ?>'.length).trim()}</span>`);
              }
              else {
                // Otherwise, add the line as a paragraph to the HTML
                console.log("We have something here...");
                html.push(`<span>${line.trim()}</span>`);
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
            conversation.innerHTML += '<div class="mwai-reply mwai-ai">' + text + '</div>';
          }
          else if (type === 'system') {
            conversation.innerHTML += '<div class="mwai-reply mwai-system">' + text + '</div>';
          }
          else {
            conversation.innerHTML += '<div class="mwai-reply mwai-user">' + text + '</div>';
          }
        }

        // Function to request the completion
        function <?= $onSentClickFn ?>() {
          let input = document.querySelector('#mwai-chat-<?= $id ?> .mwai-input input');
          let inputText = input.value.trim();

          if (inputText === '') {
            return;
          }

          // Disable the button
          var button = document.querySelector('#mwai-chat-<?= $id ?> .mwai-input button');
          button.disabled = true;

          // Add the user reply
          <?= $addReplyFn ?>('<?= $userName ?>' + inputText, 'user');
          input.value = '';
          input.disabled = true;

          // Request the completion and add the reply
          var conversation = document.querySelector('#mwai-chat-<?= $id ?> .mwai-conversation');
          var promptToUse = '<?= $atts['context'] ?>' + '\n\n' + conversation.innerText + '\n';
          const data = { 
            prompt: promptToUse,
            model: '<?= $atts['model'] ?>',
            temperature: '<?= $atts['temperature'] ?>',
            apiKey: '<?= $atts['api_key'] ?>',
          };
          console.log('Sent: ', data);
          fetch('<?= $apiUrl ?>', { method: 'POST', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
          })
          .then(response => response.json())
          .then(data => {
            console.log('Received: ', data);
            if (!data.success) {
              <?= $addReplyFn ?>('<?= $atts['sys_name'] ?>' + data.message, 'system');
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

        <?= $addReplyFn ?>('<?= $aiName ?>' + '<?= $atts['start_sentence'] ?>', 'ai');
      </script>

    <?php
    $output = ob_get_contents();
    ob_end_clean();
    $output = apply_filters( 'mwai_chat_html', $output, $atts );
    return $output;
  }
}