=== WP Gif Resizer ===
Contributors: kappuccino
Tags: image, animated gif, animated-gif
Requires at least: 4.4
Tested up to: 4.4.1
Stable tag: 4.4
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

== Description ==

This plugin allow WordPress to generate animated thumbnail from GIF files, instead of static files.

This plugin uses image magick `convert` to generate smaller gif, your server needs to have it. A check
is done during activation, so if you do not have acces to `convert` this plugin will do nothing.

== Installation ==

Install this plugin as any other plugins

1. Upload the plugin files to the `/wp-content/plugins/wp-gif-resizer` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Next GIF uploaded will use this plugin

== Frequently Asked Questions ==

= Why should i install this plugin ? =

By default Wordpress generate thumbnails of GIF files, but the generated files are not animated.
This plugins generate animated thumbnail

== Screenshots ==

This plugin has no interface, nothing to show. It is working in the background.

== Upgrade Notice ==

= 1.0.0 =
* First release

== Changelog ==

= 1.0.0 =
* First release
