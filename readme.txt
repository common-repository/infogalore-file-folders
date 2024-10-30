=== InfoGalore Folders ===
Contributors: infogalore
Tags: filelist, shortcode, folders, organise, media
Requires at least: 4.7
Tested up to: 4.7
Stable tag: trunk
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Organize your file attachments into folders and subfolders. Use shortcodes to include folder contents and file links in your posts or pages.

== Description ==

InfoGalore Folders plugin allows you to:

* organize file attachments into logical folder hierarchy
* use shortcodes to easily publish any folder or subfolder
* use shortcodes to publish file links with file information (size, date etc.)
* include the same file in multiple folders
* collect information about file downloads

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/plugin-name` directory, or install the plugin through the WordPress plugins screen directly.
1. Activate the plugin through the 'Plugins' screen in WordPress.
1. Use Settings / InfoGalore Folders screen to configure the plugin.

== Usage ==

Go to plugin settings screen to review or change default shortcode values and shortcode prefix. You can set your own shortcode prefix or enter empty value to completely remove it. Examples below use default prefix `ig-`.

Go to Folders admin menu, create new folder and add one or more files. You can use existing files from media library or upload new files. Drag file icons in files list to change order. Don't forget to publish or update folder to save any changes, including file order.

**Warning!** InfoGalore Folders plugin is enhancement of media library, not a replacememnt. Any files included in folders will also be available in media library. If you delete file from media library, it will disappear from corresponding folder(s) as well.

Use Subfolders metabox to add subfolders and navigate folder hierarchy. You can drag subfolder icons to change subfolders order.

Use shortcodes in your post or page content to include folder or any individual file. You can click on shortcode text if folder screen to copy it to clipboard. `id` is required shortcode attribute. To change shortcode behavior, you can add additional additional attributes. See FAQ section below for details.

== Frequently Asked Questions ==

= What shortcodes I can use? =

Shortcodes you can use:

- `[ig-folder id='x']` : publish contents of folder with ID=x
- `[ig-file id='x']` : publish file link and description of attachment with ID=x

= What additional attributes I can use with [ig-folder] shortcode? =

You can use the following shortcode attributes to change default plugin behaviour:

* `depth` - number of subfolder levels to include in output (default value is 0)
* `layout` - output folder files and subfolders in 'block' or 'inline' layout
* `title` - 'show' or 'hide' folder title. Alternatively you can provide your own title text.
* `file_titles` - 'show' or 'hide' folder file titles (filename used if title hidden)
* `size` - 'show' or 'hide' folder file sizes
* `date` - 'show' or 'hide' folder file dates
* `description` - 'show' or 'hide' folder file descriptions (if any)

Examples:
	[ig-folder id='1' date='show']
	[ig-folder id='1' depth='1' title='My first folder with subfolders' description='show' size='hide']

= What additional attributes I can use with [ig-file] shortcode? =

* `layout` - output file information in 'block' or 'inline' layout
* `title` - 'show' or 'hide' file title (filename used if title hidden). Alternatively you can provide your own title text
* `size` - 'show' or 'hide' file size
* `date` - 'show' or 'hide' file date
* `description` - 'show' or 'hide' file description (if any)

Example:
	[ig-file id='1' title='First File' date='show']

== Screenshots ==

1. Folder editor screen
2. Shortcode usage in article editor
3. Rendered shortcodes in article content

== Changelog ==

= 1.0.0 =

* First version.
