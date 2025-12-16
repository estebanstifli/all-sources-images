=== All Sources Images ===
Contributors: estebandezafra
Donate link: https://github.com/estebanstifli/all-sources-images
Tags: image, pixabay, openverse, ai, auto
Requires at least: 5.8
Tested up to: 6.9
Stable tag: 1.0.0
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
* 🔌 Compatible with WPeMatico, FeedWordPress, WP All Import
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

= 📖 How It Works =

1. **Configure** - Add your API keys for your preferred image sources
2. **Select** - Choose which post types should get automatic images
3. **Publish** - Images are automatically fetched and attached to your posts

That's it! The plugin handles keyword extraction, API calls, image downloading, and media library management automatically.

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

= Configuration =

1. Navigate to **All Sources Images** in your admin menu
2. Go to the **Image Banks** tab
3. Add API keys for your preferred image sources:
   * [Get Pexels API Key](https://www.pexels.com/api/)
   * [Get Unsplash API Key](https://unsplash.com/developers)
   * [Get Pixabay API Key](https://pixabay.com/api/docs/)
   * [Get OpenAI API Key](https://platform.openai.com/api-keys) (for DALL·E)
4. Configure your generation preferences in the **Settings** tab
5. Start generating images!

== Frequently Asked Questions ==

= How do I generate images for my posts? =

There are multiple ways:

1. **Manual:** Click the "Generate Image" button in the post editor sidebar
2. **Automatic:** Enable auto-generation on post publish in settings
3. **Bulk:** Select multiple posts in the Posts list, choose "Generate featured images" from Bulk Actions
4. **Gutenberg Block:** Use the ASI Images block to search and insert images directly
5. **Elementor Widget:** Drag and drop the ASI Images widget in Elementor editor

= Which image sources are free to use? =

* **Pexels, Unsplash, Pixabay, Openverse** - Free with API key (generous limits)
* **Flickr** - Free with API key
* **GIPHY** - Free with API key
* **AI Services (DALL·E, Stable Diffusion, etc.)** - Paid per generation

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

1. Main settings dashboard with source configuration
2. Image bank API configuration
3. Gutenberg block for manual image search
4. Elementor widget integration
5. Bulk generation interface
6. Post editor with generation button

== Changelog ==

= 1.0.0 - December 2025 =
* 🎉 Initial release
* ✨ Support for 10+ image sources (AI + Stock Photos)
* ✨ Gutenberg block for image search
* ✨ Elementor widget for page builder integration
* ✨ Bulk generation system with database-backed job queue
* ✨ Automatic generation on post publish
* ✨ REST API support
* ✨ WPeMatico, FeedWordPress, WP All Import compatibility
* ✨ Multi-language keyword search
* ✨ Comprehensive logging system

== Upgrade Notice ==

= 1.0.0 =
Initial release. Welcome to All Sources Images!

== Additional Info ==

= Requirements =

* WordPress 6.0 or higher
* PHP 7.3 or higher
* At least one API key from supported image sources

= Support =

For support, feature requests, or bug reports, please visit our [GitHub repository](https://github.com/developer-starter/all-sources-images) or contact us through WordPress.org support forums.



= External Services =

This plugin connects to third-party APIs to fetch images. Your post titles/keywords are sent to these services to search for relevant images. Please review each service's privacy policy:


* **Pexels API** - https://www.pexels.com/api/
* **Unsplash API** - https://unsplash.com/developers
* **Pixabay API** - https://pixabay.com/api/docs/
* **OpenAI API** (DALL·E) - https://platform.openai.com/
* **Stability AI API** - https://stability.ai/
* **Google Gemini API** - https://ai.google.dev/
* **Cloudflare Workers AI** - https://developers.cloudflare.com/workers-ai/
* **Replicate API** - https://replicate.com/
* **Flickr API** - https://www.flickr.com/services/api/
* **GIPHY API** - https://developers.giphy.com/
* **Openverse API** - https://api.openverse.org/
* **YouTube Data API** - https://developers.google.com/youtube/v3
* **Google Custom Search API** - https://developers.google.com/custom-search/

