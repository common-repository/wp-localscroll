=== WP LocalScroll ===
Contributors: philiprabbett
Tags: jquery, localscroll, scroll, smooth scroll, anchor, links, anchor text, link, navigation, jump links
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=4GP3USPU8MPL4
Requires at least: 3.5
Tested up to: 3.8
Stable tag: 1.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin will animate a regular anchor navigation with a smooth scrolling effect.

== Description ==
This plugin will animate a regular anchor navigation with a smooth scrolling effect.

Each time a link is clicked, the whole screen will gradually scroll to the targeted element, instead of "jumping" as it'd normally do. 

jQuery.ScrollTo is used for the animation.

== Installation ==
1. Upload the `wp-localscroll` folder and all contents to `/wp-content/plugins`
2. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==
= How to create jump links in WordPress? =

What you need:

* An element with an id that acts as the anchor
* A link that uses the format #anchor_id

1. In the Visual Editor click on the HTML Editor
2. Locate the end point of the jump in HTML view
3. Add an id attribute to your end point
4. Using the built-in WordPress link dialog box, add your link in the usual way at the origin point
5. You should be good to go

== Changelog ==
= 1.1 =
* Fixed an issue that was preventing the JS from loading on the front-end.

= 1.0 =
* Initial release.

== Upgrade Notice ==
= 1.1 =
* Fixed an issue that was preventing the JS from loading on the front-end.

= 1.0 =
* Initial release.