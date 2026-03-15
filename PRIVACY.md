# Privacy Policy — All Sources Images

**Last updated:** March 2026

## Overview

All Sources Images is a WordPress plugin that connects to external image providers and AI services to search, retrieve, and generate images. This document describes what data is handled and how.

## Data Collection

The plugin itself does not collect, store, or transmit personal data to the plugin developer.

All plugin settings, including API keys, are stored locally in your WordPress database and are never sent to the plugin developer.

## External Services

When you use the plugin to search or generate images, the plugin sends requests to third-party services. Depending on the action, this may include:

- Search keywords
- Text prompts
- Post titles
- Video URLs or search terms

Requests are sent only to the service needed for the specific action.

### Developer Proxy (optional)

For some stock image sources, the plugin can optionally route requests through a developer-operated proxy hosted on Cloudflare Workers. When this proxy is used:

- Only search keywords are sent to the proxy
- The proxy forwards the request to the relevant image provider and returns the results
- No personal data is stored by the proxy
- No API keys are transmitted through the proxy

The proxy does not log, store, or process any personal information.

### Third-Party Providers

Each external service has its own privacy policy. When you use a provider through this plugin, that provider's privacy policy applies to the data sent to them. Links to each provider's privacy policy are listed in the plugin's README file.

## Cookies

The plugin does not set any cookies.

## Data Retention

The plugin does not retain any user data beyond what WordPress stores in its own database (plugin settings and generated media).

## Contact

For questions about this privacy policy, please use the [WordPress.org support forum](https://wordpress.org/support/plugin/all-sources-images/) or open an issue on [GitHub](https://github.com/estebanstifli/all-sources-images/issues).
