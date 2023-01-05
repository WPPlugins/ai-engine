=== AI Engine: ChatGPT, GPT-3 Content Generation, AI Tools & Playground ===
Contributors: TigrouMeow
Tags: gpt-3, openai, ai, chatgpt, chatbot, content generation, automation, translation, title, excerpt, toolbox
Donate link: https://meowapps.com/donation/
Requires at least: 5.0
Tested up to: 6.1
Requires PHP: 7.3
Stable tag: 0.1.3
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Boost your WordPress site with AI magic using AI Engine. It generates content, suggests new titles and excerpts, adds a chatbot, and allows access to the AI Playground. It also tracks API usage statistics.

== Description ==

Ready to take your WordPress site to the next level with the power of AI? The AI Engine plugin is here to help!

Currently, it generates content for your articles, suggests titles and excerpts for your posts and keeps track of your OpenAI usage stats. There is also a shortcode that will create a ChatGPT-like discussion on your website. And finally, you'll find an AI Playground in which you will be able to do a bit of everything (translation, correction, SEO-related tasks, discussions, etc). But that's just the beginning!

We'll be continually adding new tools and features to the AI Engine based on your feedback, and even offering an API so other plugins can make use of it. Our goal is to create a toolbox full of useful AI tools and a framework that other plugins can build upon. And don't forget, our other Meow Apps plugins will soon be able to tap into the AI Engine too.

There's no doubt that we'll have a lot to discuss and options to consider as we work to improve and adapt the AI Engine to meet the needs of all our users. So let's get started and make the most of this amazing technology!

== Features ==

* Generate new content.
* Quickly create new titles for your posts.
* Quickly create new excerpts for your posts.
* Keep track of your OpenAI usage stats.
* AI Playground: Translation, correction, ChatGPT-like discussions, etc.
* ChatGPT-like chatbot for your website (shortcode).
* Coming next: translation to any language.
* Coming next: improve a specific paragraph.
* And more features coming soon!

== Content Generator ==

Visit the Content Generator under Tools or Posts, it's called either Generate New or Content Generator. This feature is beta, so let me know how you use it and how I could enhance it for you! You can play with the parameters and even modify the prompts.

== Shortcode: A chatbot like ChatGPT ==

The current chatbot uses vanilla JS on your frontend, so it's extremely lightweight. There is no CSS, so you can style it as you wish. Enable it in the settings, and you can use the shortcode this way:

[mwai_chat context="Converse as if you were Michael Jackson, talking from the afterlife." ai_name="Michael: " user_name="You: " start_sentence="Hi, my friend." ]

== Open AI ==

The AI Engine utilizes the API from [OpenAI](https://beta.openai.com). This plugin does not gather any information from your OpenAI account except for the number of tokens utilized. The data transmitted to the OpenAI servers primarily consists of the content of your article and the context you specify. The usage shown in the plugin's settings is just for reference. It is important to check your usage on the [OpenAI website](https://beta.openai.com/account/usage) for accurate information. Please also review their [Privacy Policy](https://openai.com/privacy/) and [Terms of Service](https://openai.com/terms/) for further information.

== Usage ==

1. Create an account at OpenAI.
2. Create an API key and insert in the plugin settings (Meow Apps -> AI Engine).
3. Enjoy the features of AI Engine!
5. ... and always keep an eye on [your OpenAI usage](https://beta.openai.com/account/usage)!

Languages: English.

== Changelog ==

= 0.1.3 =
* Update: Sorry, the name of the parameters in the chatbot were confusing. I've changed them to make it more clear.
* Add: New filter, and the possibility to add some CSS to the chatbot, directly through coding. Have a look on https://meowapps.com/ai-engine/.
* Info: Please let me know how you use the Content Generator, and how I could enhance it for you! Any other feedback you might have as well. And for the motivation, don't hesitate to leave me a little review. Thanks a lot and Happy New Year 2023 🥳

= 0.1.2 =
* Add: New filter for the chatbot. More info here: https://meowapps.com/ai-engine/.

= 0.1.0 =
* Add: Content Generator (available under Tools and Posts).

= 0.0.8 =
* Fix: A few fixes in the playground.

= 0.0.7 =
* Fix: Little issue in the playground.
* Add: Model and temperature in the playground.

= 0.0.6 =
* Updated: Improved the chatbot, with more parameters (temperature, model), and a better layout (HTML only).

= 0.0.3 =
* Add: Lightweight chatbot (beta).
* Fix: Missing icon.

= 0.0.1 =
* First release.
