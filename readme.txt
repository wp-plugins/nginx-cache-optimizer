=== NGINX Cache Optimizer ===
Contributors: getclouder, Hristo Sg
Tags: nginx, caching, speed, memcache, memcached, performance
Requires at least: 3.0.1
Tested up to: 3.9.2
Stable tag: 1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin allows you to make WordPress faster by using two caching mechanisms: the NGINX reverse proxy and the Memcached.

== Description ==

The main functionality of NGINX Cache Optimizer is to purge your NGINX cache whenever your content updates. For example, when you create a new post, someone comments your articles, etc. In addition to that, if you have a working Memcached service on your server, the plugin will allow you to easily configure and enable WordPress to use it.

=Requirements=

In order to work correctly, this plugin requires that your server meets the following criteria:

* NGINX configured to cache dynamic content
* Writable permissions for the user executing PHP scripts on the NGINX cache folder

== Installation ==

= Automatic Installation =

1. Go to Plugins -> Add New
1. Search for "NGINX Cache Optimizer"
1. Click on the Install button under the NGINX Cache Optimizer plugin
1. Once the plugin is installed, click on the Activate plugin link

= Manual Installation =

1. Login to the WordPress admin panel and go to Plugins -> Add New
1. Select the 'Upload' menu 
1. Click the 'Choose File' button and point your browser to the NGINXCacheOptimizers.zip file you've downloaded
1. Click the 'Install Now' button
1. Go to Plugins -> Installed Plugins and click the 'Activate' link under the WordPress NGINX Cache Optimizer listing


== Configuration ==

= Dynamic Cache Settings =
* nginx Cache Directory - 
* Dynamic Cache ON/OFF - 
* AutoFlush Cache ON/OFF - 
* Purge Cache - Manually purge all cached data from the NGINX cache

= Exclude URLs From Dynamic Caching = 

This field allows you to exclude URLs from the cache. This means that if you need certain parts of your site to be completely dynamic, you need to add them into this list. Type in the last part of the URL that you want to be excluded. For example, if you type in 'url', then '/path/to/url/' will be excluded but '/path/to/' and '/path/to/url/else/' won't.
		
= Memcached Settings =
* Memcached Instance IP Address - The IP address of the Memcached service. By default, it's set to 127.0.0.1.
* Memcached Instance Port - The port on which Memcached is running. By default, memcached uses port: 11211
* Enable Memcached - this is the main Memcached support swtich. Once you enter your Memcached server IP address and port, you need to actually enable it by setting this option to ON. 

== Changelog ==

= 1.0 =
* Plugin created.