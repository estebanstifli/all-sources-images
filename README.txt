=== All Sources Images ===
Contributors: estebandezafra
Donate link: https://github.com/estebanstifli/all-sources-images
Tags: image, pixabay, openverse, ai, auto
Requires at least: 5.8
Tested up to: 6.9
Stable tag: 1.0.5
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Generate stunning images for posts via AI (DALL·E, Stable Diffusion, etc) or image banks (Pexels, Unsplash, etc)

== Description ==

**Stop wasting hours searching for the perfect images!** All Sources Images automatically generates and retrieves high-quality images for your WordPress posts from multiple AI services and image banks.

= 🎨 Why All Sources Images? =

* **Save Time** - Automatically fetch images based on your post title, tags, or content analysis
* **Multiple Sources** - Connect to 10+ image providers with a single plugin
* **AI-Powered** - Generate unique images with DALL·E, Stable Diffusion, Gemini, and more
* **Flexible Placement** - Set as featured image or insert anywhere in your content
* **Bulk Processing** - Generate images for hundreds of posts at once

= 🎬 Demo Video =

Watch the plugin in action: https://youtu.be/XLHrwJsnGiY

= 🚀 Key Features =

**Image Sources Available:**

* 🤖 **AI Generation:** DALL·E (OpenAI), Stable Diffusion, Gemini, Replicate, Cloudflare Workers AI
* 📷 **Stock Photos:** Pexels, Unsplash, Pixabay, Flickr, Openverse
* 🎬 **Video Thumbnails:** YouTube
* 🎭 **Animated:** GIPHY

**Generation Options:**

* ✅ Generate featured thumbnails for Posts, Pages & Custom Post Types
* ✅ Insert images anywhere in your post content (choose position: first, second, third... up to 10th paragraph)
* ✅ **Gutenberg Block** for manual image search and insertion
* ✅ **Elementor Widget** - Full integration with Elementor page builder
* ✅ Bulk generation for multiple posts at once
* ✅ Automatic generation on post publish (optional)
* ✅ Smart keyword extraction from title, content, tags, or categories
* ✅ Randomized image selection for variety
* ✅ Custom ALT text and captions with attribution

**Advanced Features:**

* 🔄 Scheduled generation with WP-Cron
* 🔗 REST API support for headless WordPress
* 🌍 Multi-language search support
* 🖼️ Image post-processing (resize, crop)
* 📊 Detailed logging for troubleshooting

= 🎯 Perfect For =

* **Bloggers** - Automatically illustrate your articles
* **News Sites** - Keep up with high-volume content
* **E-commerce** - Generate product imagery
* **Content Aggregators** - Auto-add images to imported content
* **Agencies** - Streamline client site management
* **Elementor Users** - Native widget for seamless page building

= 📖 How to Use =

**This plugin works immediately after installation — no configuration required!** Stock photo sources (Pexels, Unsplash, Pixabay, Flickr, GIPHY) work out of the box thanks to our built-in proxy service.

There are three ways to search and add images:

**📚 1. Media Library**

1. Go to **Media > Add New** or open any media picker
2. Click the **"All Sources Images"** tab
3. Search for images across multiple banks simultaneously
4. Select an image to download it to your Media Library

**🧱 2. Gutenberg Block**

1. In the Block Editor, click **"+"** to add a new block
2. Search for **"ASI Images"** and insert it
3. Browse and search images from all configured sources
4. Select an image to insert it directly into your content

**🎨 3. Elementor Widget**

1. In Elementor, find the **"ASI Image"** widget in the General category
2. Drag it to your page
3. Click **"Choose Image"** to open the image explorer
4. Search and select from multiple image banks

**💡 Want more control?** You can configure your own API keys in **All Sources Images > Image Banks** for higher rate limits, direct connections, or to use AI generation services (DALL·E, Stable Diffusion, etc.).

== Installation ==

= Automatic Installation =

1. Go to **Plugins > Add New** in your WordPress admin
2. Search for "All Sources Images"
3. Click **Install Now** and then **Activate**

= Manual Installation =

1. Download the plugin ZIP file
2. Go to **Plugins > Add New > Upload Plugin**
3. Upload the ZIP file and click **Install Now**
4. Activate the plugin

= Configuration (Optional) =

The plugin works immediately with stock photo sources. For advanced users who want higher rate limits, direct API connections, or AI image generation:

1. Navigate to **All Sources Images** in your admin menu
2. Go to the **Image Banks** tab
3. Add your own API keys (optional):
   * [Get Pexels API Key](https://www.pexels.com/api/)
   * [Get Unsplash API Key](https://unsplash.com/developers)
   * [Get Pixabay API Key](https://pixabay.com/api/docs/)
   * [Get OpenAI API Key](https://platform.openai.com/api-keys) (required for DALL·E)
4. Configure your generation preferences in the **Settings** tab

== Frequently Asked Questions ==

= How do I generate images for my posts? =

There are multiple ways:

1. **Manual:** Click the "Generate Image" button in the post editor sidebar
2. **Automatic:** Enable auto-generation on post publish in settings
3. **Bulk:** Select multiple posts in the Posts list, choose "Generate featured images" from Bulk Actions
4. **Gutenberg Block:** Use the ASI Images block to search and insert images directly
5. **Elementor Widget:** Drag and drop the ASI Images widget in Elementor editor

= Which image sources are free to use? =

* **Pexels, Unsplash, Pixabay, Flickr, GIPHY** - Work immediately, no API key needed (uses built-in proxy)
* **Openverse** - Free, no API key required
* **AI Services (DALL·E, Stable Diffusion, etc.)** - Require your own API key (paid per generation)

= Can I use this with custom post types? =

Yes! Go to Settings and select which post types should have automatic image generation enabled. Works with any registered custom post type.

= What happens if an image source fails? =

The plugin automatically tries the next configured source in your priority list. You can configure multiple fallback sources.

= Is there a limit on how many images I can generate? =

The plugin itself has no limits. However, each image source has its own API rate limits:

* Pexels: 200 requests/hour (free)
* Unsplash: 50 requests/hour (free)
* Pixabay: 5000 requests/hour (free)
* AI services: Based on your subscription

= Does it work with the Classic Editor? =

Yes! The plugin works with both Gutenberg (Block Editor) and the Classic Editor.

= Does it work with Elementor? =

Absolutely! We provide a native Elementor widget that integrates seamlessly with the Elementor page builder. Simply drag and drop the "ASI Images" widget into your page and search for images from any configured source.

= Can I customize the search keywords? =

Absolutely! You can configure the plugin to extract keywords from:

* Post title
* Post content (text analysis)
* Categories
* Tags
* Custom fields

== Screenshots ==

1. Image search
2. Main settings dashboard with source configuration
3. Bulk generation interface
4. Bulk generation

== Changelog ==
= 1.0.5 =
* Fixed minor bugs.
* Added demo video of the plugin in action

= 1.0.4 - January 2026 =
* 🎉 Initial release
* ✨ Support for 10+ image sources (AI + Stock Photos)
* ✨ Gutenberg block for image search
* ✨ Elementor widget for page builder integration
* ✨ Bulk generation system with database-backed job queue
* ✨ Multi-language keyword search


== Additional Info ==

= Requirements =

* WordPress 6.0 or higher
* PHP 7.3 or higher

= Support =

For support, feature requests, or bug reports, please visit our [GitHub repository](https://github.com/developer-starter/all-sources-images) or contact us through WordPress.org support forums.



= Third Party Libraries =

This plugin includes the following third-party JavaScript libraries:

**MiniMasonry.js**
A lightweight dependency-free masonry layout library used for the image grid display in the Gutenberg block.
* Source code: https://github.com/Spope/MiniMasonry.js
* NPM package: https://www.npmjs.com/package/minimasonry
* License: MIT
* Version: 1.3.2

= External Services =

This plugin connects to external third-party APIs to fetch and generate images. When you use any of these services, your post titles, keywords, or prompts are sent to these services to search for or generate relevant images.

**Only the services you configure with API keys will be used. No data is sent to services you haven't enabled.**

== Plugin Developer Services ==

**All Sources Images Proxy (Optional)**
This plugin offers an optional proxy service hosted on Cloudflare Workers, provided by the plugin developer. This proxy allows users to access some image bank APIs without needing to obtain their own API keys.

When using the proxy option:
* Your search keywords are sent through our Cloudflare Worker proxy to the image bank APIs
* The proxy uses shared API keys to make requests on your behalf
* No personal data is stored by the proxy; it only forwards requests and returns results
* You can choose to use your own API keys instead for direct connections

This is an optional convenience feature. Users who prefer direct connections can configure their own API keys in the plugin settings.
* Service Provider: Plugin Developer (estebandezafra)
* Hosted on: Cloudflare Workers
* Cloudflare Terms of Service: https://www.cloudflare.com/terms/
* Cloudflare Privacy Policy: https://www.cloudflare.com/privacypolicy/

== Stock Photo Services ==

**Pexels API**
Free stock photo service. Sends search keywords to retrieve images.
* Service: https://www.pexels.com/
* Terms of Service: https://www.pexels.com/terms-of-service/
* Privacy Policy: https://www.pexels.com/privacy-policy/

**Unsplash API**
Free high-resolution photos. Sends search keywords to retrieve images.
* Service: https://unsplash.com/
* Terms of Service: https://unsplash.com/terms
* Privacy Policy: https://unsplash.com/privacy

**Pixabay API**
Free images and royalty-free stock. Sends search keywords to retrieve images.
* Service: https://pixabay.com/
* Terms of Service: https://pixabay.com/service/terms/
* Privacy Policy: https://pixabay.com/service/privacy/

**Flickr API**
Photo sharing platform. Sends search keywords to retrieve Creative Commons images.
* Service: https://www.flickr.com/
* Terms of Service: https://www.flickr.com/help/terms
* Privacy Policy: https://www.flickr.com/help/privacy

**Openverse API**
Open-licensed media search engine by WordPress. Sends search keywords to retrieve images.
* Service: https://openverse.org/
* Terms of Service: https://docs.openverse.org/terms_of_service.html
* Privacy Policy: https://automattic.com/privacy/

**GIPHY API**
Animated GIF search engine. Sends search keywords to retrieve GIFs.
* Service: https://giphy.com/
* Terms of Service: https://support.giphy.com/hc/en-us/articles/360020027752-GIPHY-Terms-of-Service
* Privacy Policy: https://support.giphy.com/hc/en-us/articles/360032872931-GIPHY-Privacy-Policy

== AI Image Generation Services ==

**OpenAI API (DALL·E)**
AI image generation service. Sends text prompts to generate unique images.
* Service: https://openai.com/
* Terms of Use: https://openai.com/policies/terms-of-use
* Privacy Policy: https://openai.com/policies/privacy-policy

**Stability AI API (Stable Diffusion)**
AI image generation service. Sends text prompts to generate images.
* Service: https://stability.ai/
* Terms of Service: https://stability.ai/terms-of-service
* Privacy Policy: https://stability.ai/privacy-policy

**Google Gemini API**
Google's multimodal AI for image generation. Sends text prompts to generate images.
* Service: https://ai.google.dev/
* Terms of Service: https://ai.google.dev/gemini-api/terms
* Privacy Policy: https://policies.google.com/privacy

**Cloudflare Workers AI**
Cloudflare's AI inference platform. Sends text prompts to generate images.
* Service: https://developers.cloudflare.com/workers-ai/
* Terms of Service: https://www.cloudflare.com/terms/
* Privacy Policy: https://www.cloudflare.com/privacypolicy/

**Replicate API**
Platform for running machine learning models. Sends text prompts to generate images.
* Service: https://replicate.com/
* Terms of Service: https://replicate.com/terms
* Privacy Policy: https://replicate.com/privacy

== Other Services ==

**YouTube Data API**
Retrieves video thumbnails from YouTube. Sends video URLs or search terms.
* Service: https://www.youtube.com/
* Terms of Service: https://www.youtube.com/t/terms
* Privacy Policy: https://policies.google.com/privacy

**Google Custom Search API**
Web image search via Google. Sends search keywords to retrieve images.
* Service: https://programmablesearchengine.google.com/
* Terms of Service: https://developers.google.com/custom-search/terms
* Privacy Policy: https://policies.google.com/privacy

**Google Cloud Translation API**
Optional translation service for search keywords. Sends text to translate.
* Service: https://cloud.google.com/translate
* Terms of Service: https://cloud.google.com/terms
* Privacy Policy: https://policies.google.com/privacy

**Envato Elements API**
Premium stock assets (requires Envato Elements subscription). Sends search keywords.
* Service: https://elements.envato.com/
* Terms of Service: https://elements.envato.com/user-terms
* Privacy Policy: https://www.envato.com/privacy/

