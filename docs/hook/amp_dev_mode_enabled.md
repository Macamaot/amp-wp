## Filter `amp_dev_mode_enabled`

```php
apply_filters( 'amp_dev_mode_enabled', $is_dev_mode_enabled );
```

Filters whether AMP mode is enabled.

When enabled, the data-ampdevmode attribute will be added to the document element and it will allow the attributes to be added to the admin bar. It will also add the attribute to all elements which match the queries for the expressions returned by the &#039;amp_dev_mode_element_xpaths&#039; filter.

### Arguments

* `bool $is_dev_mode_enabled` - Whether AMP dev mode is enabled.

### Source

:link: [includes/amp-helper-functions.php:1374](/includes/amp-helper-functions.php#L1374-L1388)

<details>
<summary>Show Code</summary>

```php
return apply_filters(
	'amp_dev_mode_enabled',
	(
		// For the few sites that forcibly show the admin bar even when the user is logged out, only enable dev
		// mode if the user is actually logged in. This prevents the dev mode from being served to crawlers
		// when they index the AMP version. The theme support check disables dev mode in Reader mode.
		( is_admin_bar_showing() && is_user_logged_in() )
		||
		is_customize_preview()
		||
		// Force dev mode for Bento since it currently requires the Bento experiment opt-in script.
		// @todo Remove this once Bento no longer requires an experiment to opt-in. See <https://amp.dev/documentation/guides-and-tutorials/start/bento_guide/?format=websites#enable-bento-experiment>.
		amp_is_bento_enabled()
	)
);
```

</details>
