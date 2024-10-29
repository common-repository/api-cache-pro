# API Cache Pro #
A simple plugin to cache WP Rest API Requests.

[![Build Status](https://travis-ci.com/bhubbard/api-cache-pro.svg?token=kkcazsQEFZQ5dR7MwDsz&branch=master)](https://travis-ci.com/bhubbard/api-cache-pro)

**Contributors:** [bhubbard](https://profiles.wordpress.org/bhubbard)  
**Tags:** wp rest api, rest api, wp api, api, json, json api  
**Requires at least:** 5.0  
**Tested up to:** 5.2.3  
**Stable tag:** 0.0.1  
**License:** GPLv3 or later  
**License URI:** http://www.gnu.org/licenses/gpl-3.0.html  

## Description

This plugin enables caching for the WordPress REST API to improve performance. Once enabled you can modify the basic settings via the Customizer. 

### Customizer Options:

- Disable Cache (Default: Disabled)
- Set Default Cache Timeout (Default: 300)

## Request Headers

Several Headers are added to all the API Requests. This plugin will also modify the Cache-Control header as well. Here is an example of the available custom headers.

```
X-API-CACHE-PRO: Cached
X-API-CACHE-PRO-EXPIRES: January 20, 2019, 12:39 AM UTC
X-API-CACHE-PRO-EXPIRES-DIFF: 5 mins
X-API-CACHE-PRO-KEY: api_cache_pro_78be25416f69cd3a885dcf14017a0691
```

* **X-API-CACHE-PRO** - Displays Cached, or Not Cached.
* **X-API-CACHE-PRO-EXPIRES** - Displays the date/time the cache is set to expire.
* **X-API-CACHE-PRO-EXPIRES-DIFF** - Displays the difference from current time to the time cache is set to expire. 
* **X-API-CACHE-PRO-KEY** - Displays the key used for the cache.


This plugin offers several filters so you can disable these headers:

| Filter    | Type | Default
|-----------|-----------|-------------|
| `api_cache_pro_header` | boolean | true
| `api_cache_pro_key_header` | boolean | true
| `api_cache_pro_expires_header` | boolean | true
| `api_cache_pro_expires_diff_header` | boolean | true
| `api_cache_pro_control_header` | boolean | true
| `api_cache_pro_max_age` | integer | Default Timeout or 300 (5 Minutes)
| `api_cache_pro_s_max_age` | integer | Default Timeout or 300 (5 Minutes)

You can use these filters to disable any of the headers. Here is an example to disable the Key Header.

```php
/**
 * Disable API Cache Pro Key Header.
 * 
 * @access public
 */
function disable_api_cache_pro_key_header() {
	return false;
}
add_action( 'api_cache_pro_key_header', 'disable_api_cache_pro_key_header' );

```
## Clearing Cache

The cache will automatically get cleared if you do any of the following:

* Disable the Cache
* Update the Default Cache Timeout Length
* Update any post, page or custom post type.
* Deactivate or Uninstall the plugin

You can skip that cache by adding the following param to any request:

```cache=disabled```

## WP-CLI Support

*API Cache Pro* offers wp-cli support to clear cache with the following command:

```
wp api-cache-pro delete
```

## Installation ##

1. Copy the `api-cache-pro` folder into your `wp-content/plugins` folder
2. Activate the `API Cache Pro` plugin via the plugin admin page

## Changelog ##

Please see [CHANGELOG.MD](CHANGELOG.md)
