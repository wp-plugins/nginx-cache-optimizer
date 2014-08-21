<div class="wrap">
	
	<div class="box">
		<h2><img src="../wp-content/plugins/nginx-cache-optimizer/css/cacher-black-1.svg" width="25" style="float: left; margin: 2px 5px 0 0;" / >NGINX Cache Optimizer</h2>
		<p>The NGINX Cache Optimizer allows you to use two caching mechanisms very efficiently to make your WordPress faster: the NGINX reverse proxy and the Memcached.</p>
		<p>The main functionality of the NGINX Cache Optimizer is to purge your NGINX cache automatically each time you add a new post, someone comments, or there is another change on your website that needs reverse proxy cache purged in order to become visible for all. You can also use it to purge the cache manually, whenever you want. Additionally, the plugin allows you to easily use Memcached with your WordPress site too.</p>
	</div>

	<div class="box clear">
		<h2>Dynamic Cache Settings</h2>

		<div class="greybox clear">
			<div>
				<input type="text" class="regular-text code" value="<?php  echo esc_attr($this->options_handler->get_option('nginx_cache')); ?>" id="nginxcacheoptimizer-nginx-dir" name="nginxcacheoptimizer-nginx-dir">
				
				<p>nginx Cache Directory</p>
			</div>
			<div>
				<?php submit_button( __( 'Save Changes', 'nginxcacheoptimizer' ), 'primary', 'nginxcacheoptimizer-nginx', false ); ?>
				<p class="error" id="nginxcacheoptimizer-nginx-error"></p>
			</div>
		</div>
		
		<div class="three clear">
			<div class="greybox">
				<h3>Dynamic Cache</h3>
				<a href="" id="nginxcacheoptimizer-dynamic-cache-toggle" class="<?php  if ( $this->options_handler->get_option('enable_cache') ==1 ) echo 'toggleon'; else echo 'toggleoff'; ?>"></a>
				<p id="nginxcacheoptimizer-dynamic-cache-text">Enable the NGINX-powered Dynamic caching system.</p>
				<p id="nginxcacheoptimizer-dynamic-cache-error" class="error"></p>
			</div>

			<div class="greybox">
				<h3>AutoFlush Cache</h3>
				<a href="" id="nginxcacheoptimizer-autoflush-cache-toggle" class="<?php  if ( $this->options_handler->get_option('autoflush_cache') ==1 ) echo 'toggleon'; else echo 'toggleoff'; ?>"></a>
				<p id="nginxcacheoptimizer-autoflush-cache-text">Automatically flush the Dynamic cache when you edit your content.</p>
				<p id="nginxcacheoptimizer-autoflush-cache-error" class="error"></p>
			</div>

			<div class="greybox">
				<h3>Purge Cache</h3>
				<form class="purgebtn" method="post" action="<?php menu_page_url( 'nginxcacheoptimizer-purge' ); ?>">
					<?php submit_button( __( 'Purge', 'nginxcacheoptimizer' ), 'primary', 'nginxcacheoptimizer-purge', false );?>
				</form>
				<p>Purge all the data cached by the Dynamic cache.</p>
			</div>
		</div>
		
		<div class="greybox">
			<h3>Exclude URLs From Dynamic Caching</h3>
			<p>Provide a list of your website's URLs you would like to exclude from the cache. Type in the last part of the URL that you want to be excluded. For example, if you type in <strong>'url'</strong>, then <strong>'/path/to/url/</strong>' will be excluded but <strong>'/path/to/'</strong> and <strong>'/path/to/url/else/'</strong> won't.</p>
			</p>
			<form method="post" action="<?php menu_page_url( 'nginxcacheoptimizer-purge' ); ?>">
				<textarea id="nginxcacheoptimizer-blacklist-textarea"><?php  echo esc_textarea($this->options_handler->get_blacklist()); ?></textarea>
				<?php submit_button( __( 'Update The Exclude List', 'nginxcacheoptimizer' ), 'primary', 'nginxcacheoptimizer-blacklist', false ); ?>
			</form>

		</div>
	</div>
	
	<div class="box">
		<h2>Memcached Settings</h2>
		<div class="greybox">
			
			<a href="" id="nginxcacheoptimizer-memcached-toggle" class="<?php  if ( $this->options_handler->get_option('enable_memcached') ==1 ) echo 'toggleon'; else echo 'toggleoff'; ?>"></a>
			<p id="nginxcacheoptimizer-memcached-text">Enable Memcached</p>
			<p class="error" id="nginxcacheoptimizer-memcached-error"></p>
			
			<div class="clr"></div>
			
			<p>Store in the server's memory frequently executed queries to the database for a faster access on a later use.</p>
	
			<form method="post" action="<?php menu_page_url( 'nginxcacheoptimizer-purge' ); ?>">
				<div>
					<input type="text" class="regular-text code" value="<?php  echo esc_attr($this->options_handler->get_option('memcached_ip')); ?>" id="nginxcacheoptimizer-memcached-ip" name="nginxcacheoptimizer-memcached-ip">
					<p>Memcached Instance IP Address</p>
				</div>
				<div>
					<input type="text" class="regular-text code" value="<?php  echo esc_attr($this->options_handler->get_option('memcached_port')); ?>" id="nginxcacheoptimizer-memcached-port" name="nginxcacheoptimizer-memcached-port">
					<p>Memcached Instance Port</p>
				</div>

				
				<div class="clr"></div>
				<p class="error" id="nginxcacheoptimizer-memcached-error2"></p>
				<?php submit_button( __( 'Save Changes', 'nginxcacheoptimizer' ), 'primary', 'nginxcacheoptimizer-memcached', false ); ?>
			</form>
		</div>
	</div>
	
	<div class="box">
		<h2>Requirements</h2>
		
		<p>In order to work correctly, this plugin requires that your server meets the following criteria:</p>
		
		<ul>
			<li>NGINX configured to cache dynamic content (<a href="http://download.getclouder.com/wordpress/conf.d" target="_blank">Download sample NGINX config</a>)</li>
			<li>Writable permissions for the user executing PHP scripts on the NGINX cache folder</li>			
		</ul>
		
	</div>
	
	<div class="box">
		<h2>Developed by <img src="../wp-content/plugins/nginx-cache-optimizer/css/getclouder-logo-black.svg" height="20" style="margin: 0 0 -2px 0; display: inline-block;" / ></h2>
		
		<p>The NGINX Cache Optimizer plugin was developed by the experts at GetClouder.com. If you want to spin off a new server in 5 seconds, that comes with NGINX configured as a reverse proxy, Memcached up and running, and working perfectly with this plugin - GetClouder.com is the right choice for you!</p>
		
		<a style="font-size: 20px; margin: 0 auto; display: block;" class="button button-primary" href="https://www.getclouder.com/?utm_source=plugin&utm_medium=banner&utm_campaign=NGINX%20Cache%20Optimizer" target="_blank"><strong>Visit</strong> <img src="../wp-content/plugins/nginx-cache-optimizer/css/getclouder-logo-white.svg" height="20" style="margin: 0 0 -2px 0; display: inline-block;" / ></a>
	</div>
</div>