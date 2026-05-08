# All Sources Images for Pixabay, Unsplash, Featured Images & AI

[![License: GPLv2+](https://img.shields.io/badge/License-GPLv2%2B-blue.svg)](https://www.gnu.org/licenses/gpl-2.0.html)
[![WordPress](https://img.shields.io/badge/WordPress-5.8%2B-blue.svg)](https://wordpress.org/)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B-purple.svg)](https://php.net/)

A WordPress plugin to search Pixabay and Unsplash images, assign automatic featured images, and generate AI images for WordPress posts.

If you are looking for a WordPress Pixabay plugin or a WordPress Unsplash plugin, All Sources Images is designed for that workflow. You can search Pixabay, browse Unsplash, download Pixabay photos or Unsplash photos, and use them as a featured image, inline image, or bulk image source without leaving WordPress.

Beyond Pixabay and Unsplash, the plugin also supports Pexels, Openverse, Flickr, GIPHY, YouTube thumbnails, and AI image generation with DALL·E, Stability AI, Gemini, Replicate, and Cloudflare Workers AI.

## Demo Video

[![Watch the demo](https://img.youtube.com/vi/XLHrwJsnGiY/hqdefault.jpg)](https://www.youtube.com/watch?v=XLHrwJsnGiY)


## Features

- Search Pixabay, Unsplash, Pexels, Flickr, Openverse, GIPHY, and more stock photo providers
- Generate images using AI providers when Pixabay or Unsplash are not enough for a specific post
- Set images as featured images or insert inside post content
- Set an automatic featured image on publish or in bulk workflows
- Bulk-generate featured images for multiple posts
- Gutenberg block for manual Pixabay and Unsplash image search and insertion
- Elementor widget for Pixabay and Unsplash image search and insertion
- Automatic featured image generation on post publish (optional)
- Keyword extraction from title, content, tags, and categories
- REST API support
- WordPress Abilities API support for MCP-compatible clients (WordPress 6.9+)

## Supported Image Sources

### AI Generation
| Provider | API Key Required |
|----------|:---:|
| OpenAI (DALL·E) | Yes |
| Stability AI | Yes |
| Google Gemini | Yes |
| Replicate | Yes |
| Cloudflare Workers AI | Yes |

### Stock / Search Sources
| Provider | API Key Required |
|----------|:---:|
| Pixabay | Optional (proxy available) |
| Unsplash | Optional (proxy available) |
| Pexels | Optional (proxy available) |
| Flickr | Optional (proxy available) |
| Openverse | No |
| GIPHY | Optional (proxy available) |
| YouTube thumbnails | Yes |

## How It Works

The plugin can work in two ways depending on the source:

1. **Direct API mode** — You add your own API keys for supported providers and the plugin connects directly to those services.

2. **Developer proxy mode** (optional, for some stock sources) — Some stock image searches can work without your own API key through an optional developer-operated proxy service.

AI image generation services require your own API keys.

For many publishers, the core use case is simple: search Pixabay, compare Unsplash results, and set the best featured image fast. The plugin brings Pixabay and Unsplash directly into the WordPress editorial workflow, which is why it is useful for blogs, magazines, affiliate sites, WooCommerce stores, and content teams.

That same workflow can also power automatic featured image generation, bulk featured image generation, and AI fallback when a Pixabay or Unsplash result is not the right match.

## Installation

### From WordPress.org

1. Go to **Plugins > Add New** in your WordPress admin
2. Search for **All Sources Images**
3. Click **Install Now** and activate

### Manual

1. Download the latest release ZIP
2. Go to **Plugins > Add New > Upload Plugin**
3. Upload the ZIP file and activate

## Usage

### Media Library
Open the **All Sources Images** tab in any media picker to search Pixabay, search Unsplash, and download images directly into WordPress.

### Gutenberg Block
Add the **ASI Images** block in the Block Editor to search Pixabay and Unsplash images and insert them into content.

### Elementor Widget
Add the **ASI Image** widget in Elementor to search Pixabay, browse Unsplash, and insert images into your page.

### Automatic Featured Images
Configure automatic featured image workflows to assign a Pixabay image, an Unsplash image, or an AI image when a post is published.

### Bulk Generation
Select multiple posts and run the bulk generation process to automatically generate featured images for all of them using Pixabay, Unsplash, or AI fallback.

### AI Agent Integration (WordPress 6.9+)
AI agents can interact with this plugin through the WordPress Abilities API:

- `allsi/search-image`
- `allsi/set-featured-image`
- `allsi/auto-generate-for-post`
- `allsi/insert-image-in-content`
- `allsi/generate-ai-image`

## Configuration

1. Go to **All Sources Images** in the WordPress admin
2. Open the **Image Banks** tab
3. Add your API keys for the providers you want to use
4. Save the settings

Stock image sources such as Pixabay and Unsplash can optionally work through the developer proxy without your own API keys.

If your main SEO goal is to publish faster with a relevant featured image, you can prioritize Pixabay and Unsplash in your source order and combine them with automatic featured image rules.

## External Services

This plugin connects to external third-party services to search, retrieve, or generate images. Please review the terms and privacy policies of each service before use.

### Developer Proxy (optional)

For some stock image sources, the plugin can use an optional proxy service operated by the plugin developer. This proxy only forwards search requests to the relevant image provider and returns results.

- **Data sent:** Search keywords only
- **Infrastructure:** Cloudflare Workers

### Third-Party Services Used

| Service | Purpose | Terms | Privacy |
|---------|---------|-------|---------|
| OpenAI | AI image generation | [Terms](https://openai.com/policies/terms-of-use) | [Privacy](https://openai.com/policies/privacy-policy) |
| Stability AI | AI image generation | [Terms](https://stability.ai/terms-of-service) | [Privacy](https://stability.ai/privacy-policy) |
| Google Gemini | AI image generation | [Terms](https://ai.google.dev/gemini-api/terms) | [Privacy](https://policies.google.com/privacy) |
| Cloudflare Workers AI | AI image generation | [Terms](https://www.cloudflare.com/terms/) | [Privacy](https://www.cloudflare.com/privacypolicy/) |
| Replicate | AI image generation | [Terms](https://replicate.com/terms) | [Privacy](https://replicate.com/privacy) |
| Pexels | Stock photos | [Terms](https://www.pexels.com/terms-of-service/) | [Privacy](https://www.pexels.com/privacy-policy/) |
| Unsplash | Stock photos | [Terms](https://unsplash.com/terms) | [Privacy](https://unsplash.com/privacy) |
| Pixabay | Stock photos | [Terms](https://pixabay.com/service/terms/) | [Privacy](https://pixabay.com/service/privacy/) |
| Flickr | Photo search | [Terms](https://www.flickr.com/help/terms) | [Privacy](https://www.flickr.com/help/privacy) |
| Openverse | Open-licensed media | [Terms](https://docs.openverse.org/terms_of_service.html) | [Privacy](https://automattic.com/privacy/) |
| GIPHY | Animated GIFs | [Terms](https://support.giphy.com/hc/en-us/articles/360020027752) | [Privacy](https://support.giphy.com/hc/en-us/articles/360032872931) |
| YouTube | Video thumbnails | [Terms](https://www.youtube.com/t/terms) | [Privacy](https://policies.google.com/privacy) |
| Google Custom Search | Web image search | [Terms](https://developers.google.com/custom-search/terms) | [Privacy](https://policies.google.com/privacy) |
| Google Translate | Text translation | [Terms](https://cloud.google.com/terms) | [Privacy](https://policies.google.com/privacy) |

## License

This plugin is licensed under the [GPLv2 or later](https://www.gnu.org/licenses/gpl-2.0.html).

## Privacy & Terms

- [Privacy Policy](PRIVACY.md)
- [Terms of Service](TERMS.md)

## Support

For support, please use the [WordPress.org support forum](https://wordpress.org/support/plugin/all-sources-images/).
