jQuery( function ($) {
	'use strict';
	// Bind behaviour to event
	jQuery('#nginxcacheoptimizer-purge').on('click.nginxcacheoptimizer', nginxcacheoptimizer_purge);
	jQuery('#nginxcacheoptimizer-dynamic-cache-toggle').on('click.nginxcacheoptimizer', function(event){event.preventDefault();nginxcacheoptimizer_toggle_option('dynamic-cache');});
	jQuery('#nginxcacheoptimizer-memcached-toggle').on('click.nginxcacheoptimizer', function(event){event.preventDefault();nginxcacheoptimizer_toggle_option('memcached');});
	jQuery('#nginxcacheoptimizer-autoflush-cache-toggle').on('click.nginxcacheoptimizer', function(event){event.preventDefault();nginxcacheoptimizer_toggle_option('autoflush-cache');});
	jQuery('#nginxcacheoptimizer-blacklist').on('click.nginxcacheoptimizer', nginxcacheoptimizer_save_blacklist);
	jQuery('#nginxcacheoptimizer-memcached').on('click.nginxcacheoptimizer', nginxcacheoptimizer_save_memcached);
	jQuery('#nginxcacheoptimizer-nginx').on('click.nginxcacheoptimizer', nginxcacheoptimizer_save_nginx);
});
var nginxcacheoptimizer_toggle_in_progress = false;
/**
 * Update a setting parameter
 *
 * @since 1.1.0
 *
 * @function
 *
 * @param {jQuery.event} event
 */
function nginxcacheoptimizer_toggle_option(optionName) {
	if (nginxcacheoptimizer_toggle_in_progress)
		return;
	
	nginxcacheoptimizer_toggle_in_progress = true;
	var $ajaxArgs;
	$ajaxArgs = {
		action:  'nginxcacheoptimizer-parameter-update',
		parameterName: optionName,
		objects: 'all'
	};
	jQuery.post(ajaxurl, $ajaxArgs).done(function(data){
		nginxcacheoptimizer_toggle_in_progress = false;
		jQuery('#nginxcacheoptimizer-'+optionName+'-text').show();
		jQuery('#nginxcacheoptimizer-'+optionName+'-error').hide();
		if (data == 1) 
		{
			jQuery('#nginxcacheoptimizer-'+optionName+'-toggle').removeClass('toggleoff').addClass('toggleon', 1000);
			return;
		}
		if (data == 0)
		{
			jQuery('#nginxcacheoptimizer-'+optionName+'-toggle').removeClass('toggleon').addClass('toggleoff', 1000);
			return;
		}
			
		jQuery('#nginxcacheoptimizer-'+optionName+'-text').hide();
		jQuery('#nginxcacheoptimizer-'+optionName+'-error').html(data).show();		
		});
}

/**
 * Update the blacklist
 *
 * @since 1.1.0
 *
 * @function
 *
 * @param {jQuery.event} event
 */
function nginxcacheoptimizer_save_blacklist(event) {
	event.preventDefault();
	var $ajaxArgs;
	$ajaxArgs = {
		action:  'nginxcacheoptimizer-blacklist-update',
		blacklist: jQuery('#nginxcacheoptimizer-blacklist-textarea').val(),
		objects: 'all'
	};
	jQuery(event.target).attr('disabled','disabled').attr('value', nginxcacheoptimizerL10n.updating);
	jQuery('#nginxcacheoptimizer-spinner-blacklist').show();
	jQuery.post(ajaxurl, $ajaxArgs).done(function(){
		jQuery('#nginxcacheoptimizer-spinner-blacklist').hide();
		jQuery('#nginxcacheoptimizer-blacklist').removeAttr('disabled').attr('value', nginxcacheoptimizerL10n.updated);
		});
}

/**
 * Update the memcached settings
 *
 * @since 1.1.0
 *
 * @function
 *
 * @param {jQuery.event} event
 */
function nginxcacheoptimizer_save_memcached(event) {
	event.preventDefault();
	jQuery('#nginxcacheoptimizer-memcached-error2').text('').hide();
	var $ajaxArgs;
	$ajaxArgs = {
		action:  'nginxcacheoptimizer-memcached-update',
		ip: jQuery('#nginxcacheoptimizer-memcached-ip').val(),
		port: jQuery('#nginxcacheoptimizer-memcached-port').val(),
		objects: 'all'
	};
	jQuery(event.target).attr('disabled','disabled').attr('value', nginxcacheoptimizerL10n.updating);
	jQuery('#nginxcacheoptimizer-spinner-memcached').show();
	jQuery.post(ajaxurl, $ajaxArgs).done(function(data){
		if (data != 1)
			jQuery('#nginxcacheoptimizer-memcached-error2').text(data).show();	
		jQuery('#nginxcacheoptimizer-spinner-memcached').hide();
		jQuery('#nginxcacheoptimizer-memcached').removeAttr('disabled').attr('value', nginxcacheoptimizerL10n.savechanges);
		});
}

/**
 * Update the nginx cache folder
 *
 * @since 1.1.0
 *
 * @function
 *
 * @param {jQuery.event} event
 */
function nginxcacheoptimizer_save_nginx(event) {
	event.preventDefault();
	jQuery('#nginxcacheoptimizer-nginx-error').text('').hide();
	var $ajaxArgs;
	$ajaxArgs = {
		action:  'nginxcacheoptimizer-nginx-update',
		dir: jQuery('#nginxcacheoptimizer-nginx-dir').val(),
		objects: 'all'
	};
	jQuery(event.target).attr('disabled','disabled').attr('value', nginxcacheoptimizerL10n.updating);
	jQuery('#nginxcacheoptimizer-spinner-nginx').show();
	jQuery.post(ajaxurl, $ajaxArgs).done(function(data){
		if (data != 1)
			jQuery('#nginxcacheoptimizer-nginx-error').text(data).show();
		jQuery('#nginxcacheoptimizer-spinner-nginx').hide();
		jQuery('#nginxcacheoptimizer-nginx').removeAttr('disabled').attr('value', nginxcacheoptimizerL10n.savechanges);
		});
}
/**
 * Start the purge procedure from a button click.
 *
 * @since 1.1.0
 *
 * @function
 *
 * @param {jQuery.event} event
 */
function nginxcacheoptimizer_purge(event) {
	jQuery('#nginxcacheoptimizer-purgesuccess').hide();
	jQuery('#nginxcacheoptimizer-purgefailure').hide();
	event.preventDefault();
	'use strict';
	var $ajaxArgs;
	$ajaxArgs = {
		action:  'nginxcacheoptimizer-purge',
		objects: 'all'
	};
	jQuery(event.target).attr('disabled','disabled').attr('value', nginxcacheoptimizerL10n.purging);
	jQuery('#nginxcacheoptimizer-spinner').css({'visibility': 'visible'});
	jQuery.post(ajaxurl, $ajaxArgs).done(nginxcacheoptimizer_purged);
}

/**
 * Tidy-up the UI after purge has successfully completed.
 *
 * @since 1.1.0
 *
 * @function
 *
 * @param {string} data
 */
function nginxcacheoptimizer_purged(data) {
	'use strict';
	jQuery('#nginxcacheoptimizer-purge').removeAttr('disabled').attr('value', nginxcacheoptimizerL10n.purge);
	jQuery('#nginxcacheoptimizer-spinner').css({'visibility':'hidden'});
	if ('1' == data){
		jQuery('#nginxcacheoptimizer-purgesuccess').fadeIn();
	} else {
		jQuery('#nginxcacheoptimizer-purgefailure').fadeIn();
	}
}