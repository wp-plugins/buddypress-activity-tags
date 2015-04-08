=== BuddyPress Activity Tags ===
Contributors: aliciagh
Tags: tag, activity, tags, tagging, buddypress, multisite, widget, multilingual, global, shortcode
Requires at least: 3.0
Tested up to: 3.8.1
Stable tag: 1.2

Adds a widget that displays a tag cloud with tags from new blog posts in BuddyPress Activity tab.
Tested up to BuddyPress 1.9.2

== Description ==

BuddyPress Activity tab is used to output a list of sitewide, member or member's friends activity. This plugin gets tags from each new blog posts activity and shows the most commonly used.
BuddyPress Activity Tags doesn't work with single WordPress installation or Multisite installation without BuddyPress. It must be activated for all sites using "network activate" in the Administration Panel.
The widget configuration and tags style is based on [Simple Tags plugin](http://wordpress.org/extend/plugins/simple-tags/) by [momo360modena](http://profiles.wordpress.org/users/momo360modena/).

**Translations**

The plugin comes with Spanish and English translations, please refer to the [WordPress Codex](http://codex.wordpress.org/Installing_WordPress_in_Your_Language "Installing WordPress in Your Language") for more information about activating the translation. If you want to help to translate the plugin to your language, please have a look at the bp-activity-tags.pot file which contains all definitions and may be used with a [gettext](http://www.gnu.org/software/gettext/) editor like [Poedit](http://www.poedit.net/).
Currently in the following languages:

* Italian
* English
* Serbian (sr_RS) by [Webhostinghub.com](http://www.webhostinghub.com)

**Features**

* Click tags.
* Customizable activity tags page URI.
* Recent activity posts with a selected tag are showed in a page which contents the shortcode: `[bp_activity_tags]`
* Customizable style sheet for widget and results page.
* Select how to display tags.
* The size and color of each tag is determined by how many times that particular tag has been assigned to posts.

[See the plugin page for more information](http://agora.grial.eu/pfcgrial/bp-activity-tags/).

== Installation ==

Installation is easy:

1. Upload `bp-activity-tags` folder to the `wp-content/plugins` directory in your installation.
2. Activate the plugin in your Administration Panel.
3. Create a new page in your blog with default activity tags URI: `activity-tags`.
4. Place `[bp_activity_tags]` in the post content area.
5. Activate widget `BP Activity Tags`.

== Frequently Asked Questions ==

If you have any further questions, please submit them.

== Screenshots ==

1. Widget configuration.
2. Widget.

== Changelog ==

= 1.2 =
* Added: Serbian language pack

= 1.1 =
* Added: Italian language pack
