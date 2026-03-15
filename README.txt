=== All Sources Images ===
Contributors: estebandezafra
Donate link: https://github.com/estebanstifli/all-sources-images
Tags: image, pixabay, openverse, ai, mcp
Requires at least: 5.8
Tested up to: 6.9
Stable tag: 1.0.7
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Generate stunning images for posts via AI (DALL·E, Stable Diffusion, etc) or image banks (Pexels, Unsplash, etc)

== Description ==
**All Sources Images** helps you search, download, generate, and insert images into WordPress posts using multiple external image providers and AI services.


= Main Features =

* Search images from multiple stock photo providers
* Generate images using supported AI providers
* Set images as featured images
* Insert images inside post content
* Bulk-generate images for multiple posts
* Gutenberg block for manual image search and insertion
* Elementor widget for image search and insertion
* Automatic generation on post publish (optional)
* Keyword extraction from title, content, tags, and categories
* Logging for troubleshooting
* REST API support
* WordPress Abilities API support for MCP-compatible clients (WordPress 6.9+)


= 🎬 Demo Video =

[youtube https://www.youtube.com/watch?v=XLHrwJsnGiY]

= Supported Image Sources =

**AI Generation**
* OpenAI (DALL·E)
* Stability AI
* Google Gemini
* Replicate
* Cloudflare Workers AI

**Stock / Search Sources**
* Pexels
* Unsplash
* Pixabay
* Flickr
* Openverse
* GIPHY
* YouTube thumbnails

= How It Works =

The plugin can work in two ways depending on the source:

1. **Direct API mode**  
   You add your own API keys for supported providers and the plugin connects directly to those services.

2. **Developer proxy mode (optional, for some stock sources)**  
   Some stock image searches can work without your own API key through an optional developer-operated proxy service. In that case, the search keywords are sent to the developer proxy, which forwards the request to the relevant image provider and returns the results.

AI image generation services generally require your own API keys.

= Typical Use Cases =

* Automatically find a featured image for a blog post
* Insert an image after a selected paragraph
* Search stock images directly from the Media Library
* Use the Gutenberg block to manually insert images in content
* Use the Elementor widget to search and display images
* Bulk-process many posts

= AI Agent Integration =

With WordPress 6.9+, AI agents can interact with this plugin through the WordPress Abilities API and MCP-compatible tools.

Available abilities include:

* `allsi/search-image`
* `allsi/set-featured-image`
* `allsi/auto-generate-for-post`
* `allsi/insert-image-in-content`
* `allsi/generate-ai-image`

This allows compatible assistants to help find, generate, and assign images to posts.

== Installation ==

= Automatic Installation =

1. Go to **Plugins > Add New** in your WordPress admin
2. Search for **All Sources Images**
3. Click **Install Now**
4. Activate the plugin

= Manual Installation =

1. Download the plugin ZIP file
2. Go to **Plugins > Add New > Upload Plugin**
3. Upload the ZIP file
4. Activate the plugin

== Usage ==

There are several ways to use the plugin:

= 1. Media Library =

1. Go to **Media > Add New** or open a media picker
2. Open the **All Sources Images** tab
3. Search for images
4. Download the selected image into your Media Library

= 2. Gutenberg Block =

1. In the Block Editor, add the **ASI Images** block
2. Search images from supported sources
3. Select and insert the image into the post

= 3. Elementor Widget =

1. In Elementor, add the **ASI Image** widget
2. Open the image explorer
3. Search and select an image source
4. Insert the image into the page

= 4. Post Image Generation =

1. Open a post or page
2. Use the image generation tools from the plugin interface
3. Generate or retrieve an image
4. Set it as featured image or insert it into content

= 5. Bulk Generation =

1. Go to the bulk generation interface
2. Select one or more posts
3. Run the generation process
4. The plugin processes the configured image blocks for each selected post

== Configuration ==

Configuration depends on the providers you want to use.

= Stock image sources =

Some stock image providers can work either:

* through the optional developer proxy, or
* through your own API keys, where supported

Using your own API keys may provide direct connections and provider-specific rate limits.

= AI image generation sources =

AI services generally require your own API keys.

To configure providers:

1. Go to **All Sources Images**
2. Open the **Image Banks** tab
3. Add your API keys for the providers you want to use
4. Save the settings

== Frequently Asked Questions ==

= Is the plugin fully functional in the WordPress.org version? =

Yes. The plugin distributed on WordPress.org is fully functional and does not contain built-in features locked behind a license key, payment, trial period, quota, or similar restriction.

= Do I need API keys? =

Not always.

Some stock image searches can work through the optional developer proxy service without your own API key.

Other providers, especially AI image generation services, require your own API keys.

= Which services can work without my own API key? =

Some stock photo sources may work through the optional developer proxy service, depending on the plugin configuration.

If you prefer, you can use your own API keys for supported services instead of relying on the proxy.

= What data is sent to external services? =

Depending on the feature you use, the plugin may send:

* search keywords
* post titles
* prompts
* selected text used to build image prompts
* video URLs or search terms for thumbnail retrieval

Only the service needed for the requested action is contacted.

If you use the optional developer proxy path for supported stock sources, search keywords are sent to the developer proxy and then forwarded to the relevant image provider.

If you use your own API keys, requests are sent directly to the configured provider.

= Does the plugin store remote service credentials on the developer server? =

No. Your own API keys are stored in your WordPress site settings and are used by your site when configured. The optional developer proxy is a forwarding service for supported stock-source requests and is not used to store your own API keys.

= Can I use this with custom post types? =

Yes. You can enable image generation for registered custom post types.

= What happens if an image source fails? =

The plugin can try the next configured source in your source order, depending on your settings.

= Does it work with Elementor? =

Yes. The plugin includes an Elementor widget for image search and insertion.

= Does it work with the Classic Editor? =

Yes. The plugin works with Classic Editor as well as with Gutenberg where applicable.

== Screenshots ==

1. Image search
2. Main settings dashboard with source configuration
3. Bulk generation interface
4. Bulk generation process

== External Services ==

This plugin connects to external third-party services to search, retrieve, or generate images.

Depending on the provider and feature used, the plugin may send search keywords, prompts, post titles, selected text, or video URLs.

= Important =

* The plugin can connect either directly to third-party providers or, for some supported stock sources, through an optional developer-operated proxy service.
* The optional proxy is used only for supported stock-source requests.
* AI image generation services generally require your own API keys.
* Please review the terms and privacy policies of each external service before use.

== Plugin Developer Service ==

= All Sources Images Proxy (optional) =

For some supported stock image sources, the plugin can use an optional proxy service operated by the plugin developer.

When this proxy path is used:

* search keywords are sent to the developer proxy
* the developer proxy forwards the request to the relevant image provider
* the proxy returns the search results to your site

This proxy exists only to perform the remote request to the external provider on behalf of the site.

* Service provider: Plugin developer
* Service purpose: Forward supported stock image search requests
* Data sent: Search keywords
* Terms of Service: https://github.com/estebanstifli/all-sources-images/blob/main/TERMS.md
* Privacy Policy: https://github.com/estebanstifli/all-sources-images/blob/main/PRIVACY.md
* Infrastructure provider: Cloudflare Workers
* Cloudflare Terms of Service: https://www.cloudflare.com/terms/
* Cloudflare Privacy Policy: https://www.cloudflare.com/privacypolicy/

== Stock Photo Services ==

= Pexels API =

Used to search stock photos.

* Service: https://www.pexels.com/
* Data sent: Search keywords
* Terms of Service: https://www.pexels.com/terms-of-service/
* Privacy Policy: https://www.pexels.com/privacy-policy/

= Unsplash API =

Used to search stock photos.

* Service: https://unsplash.com/
* Data sent: Search keywords
* Terms of Service: https://unsplash.com/terms
* Privacy Policy: https://unsplash.com/privacy

= Pixabay API =

Used to search stock photos.

* Service: https://pixabay.com/
* Data sent: Search keywords
* Terms of Service: https://pixabay.com/service/terms/
* Privacy Policy: https://pixabay.com/service/privacy/

= Flickr API =

Used to search photos.

* Service: https://www.flickr.com/
* Data sent: Search keywords
* Terms of Service: https://www.flickr.com/help/terms
* Privacy Policy: https://www.flickr.com/help/privacy

= Openverse API =

Used to search open-licensed media.

* Service: https://openverse.org/
* Data sent: Search keywords
* Terms of Service: https://docs.openverse.org/terms_of_service.html
* Privacy Policy: https://automattic.com/privacy/

= GIPHY API =

Used to search animated GIFs.

* Service: https://giphy.com/
* Data sent: Search keywords
* Terms of Service: https://support.giphy.com/hc/en-us/articles/360020027752-GIPHY-Terms-of-Service
* Privacy Policy: https://support.giphy.com/hc/en-us/articles/360032872931-GIPHY-Privacy-Policy

== AI Image Generation Services ==

= OpenAI API (DALL·E) =

Used to generate AI images.

* Service: https://openai.com/
* Data sent: Text prompts
* Terms of Use: https://openai.com/policies/terms-of-use
* Privacy Policy: https://openai.com/policies/privacy-policy

= Stability AI API =

Used to generate AI images.

* Service: https://stability.ai/
* Data sent: Text prompts
* Terms of Service: https://stability.ai/terms-of-service
* Privacy Policy: https://stability.ai/privacy-policy

= Google Gemini API =

Used to generate AI images.

* Service: https://ai.google.dev/
* Data sent: Text prompts
* Terms of Service: https://ai.google.dev/gemini-api/terms
* Privacy Policy: https://policies.google.com/privacy

= Cloudflare Workers AI =

Used to generate AI images.

* Service: https://developers.cloudflare.com/workers-ai/
* Data sent: Text prompts
* Terms of Service: https://www.cloudflare.com/terms/
* Privacy Policy: https://www.cloudflare.com/privacypolicy/

= Replicate API =

Used to generate AI images.

* Service: https://replicate.com/
* Data sent: Text prompts
* Terms of Service: https://replicate.com/terms
* Privacy Policy: https://replicate.com/privacy

== Other Services ==

= YouTube Data API =

Used to retrieve video thumbnails.

* Service: https://www.youtube.com/
* Data sent: Video URLs or search terms
* Terms of Service: https://www.youtube.com/t/terms
* Privacy Policy: https://policies.google.com/privacy

= Google Custom Search API =

Used for web image search where configured.

* Service: https://programmablesearchengine.google.com/
* Data sent: Search keywords
* Terms of Service: https://developers.google.com/custom-search/terms
* Privacy Policy: https://policies.google.com/privacy

= Google Cloud Translation API =

Optional service used to translate search text where configured.

* Service: https://cloud.google.com/translate
* Data sent: Text to translate
* Terms of Service: https://cloud.google.com/terms
* Privacy Policy: https://policies.google.com/privacy

== Third Party Libraries ==

= MiniMasonry.js =

A lightweight dependency-free masonry layout library used for the image grid display.

* Source code: https://github.com/Spope/MiniMasonry.js
* Package: https://www.npmjs.com/package/minimasonry
* License: MIT
* Version: 1.3.2

== Changelog ==

= 1.0.7 - March 2026 =
* Compliance updates
* Removed wording that could be interpreted as feature restriction
* Clarified external service and proxy documentation
* Confirmed bulk generation processes configured image blocks without artificial limitation

= 1.0.6 - January 2026 =
* Added WordPress Abilities API integration for MCP-compatible clients
* Added image search and image assignment abilities
* Added AI image generation ability
* Requires WordPress 6.9+ and an MCP adapter where applicable

= 1.0.5 =
* Fixed minor bugs
* Added demo video

= 1.0.4 - January 2026 =
* Initial release
* Support for multiple image sources
* Gutenberg block
* Elementor widget
* Bulk generation system
* Multi-language keyword search

== Support ==

For support, please use the WordPress.org support forum for this plugin.