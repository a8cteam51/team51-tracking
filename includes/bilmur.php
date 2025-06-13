<?php

defined( 'ABSPATH' ) || exit;

// Only load if the constant is defined and true.
if ( ! defined( 'WPCOMSP_BILMUR_TRACKING' ) || ! WPCOMSP_BILMUR_TRACKING ) {
	return;
}

// Returns a standardized timezone string.
//
// `wp_timezone_string()` sometimes returns offsets (e.g. "-07:00"), which are
// a non-standard representation of a UTC offset that only works in PHP.
// This function returns a standardized timezone string instead, of the form
// "Etc/GMT+7" for integer hour offsets, or a matching "<Area>/<City>" form for
// fractional hour offsets (used e.g. in India).
function wpcomsp_bilmur_timezone_string() {
	$wp_tz = wp_timezone_string();

	// Did we get back an offset?
	if ( preg_match( '/^([+-])?(\d{1,2}):(\d{2})$/', $wp_tz, $matches ) ) {
		$sign = $matches[1] === '-' ? -1 : 1;
		$hours = intval( $matches[2], 10 );
		$minutes = intval( $matches[3], 10 );

		// For fractional hour offsets, use `timezone_name_from_abbr` to get a
		// matching "<Area>/<City>" timezone.
		if ( $minutes > 0 ) {
			$offset = $sign * ( $hours * 3600 + $minutes * 60 );
			$city_tz = timezone_name_from_abbr( '', $offset, 0 );

			if ( ! empty( $city_tz ) ) {
				return $city_tz;
			}
		}

		// For integer hour offsets, use "Etc/GMT(+|-)<offset>".
		// The sign is flipped, to match how the `Etc` area is specced.
		//
		// This codepath is also followed if no city exists to match a
		// fractional offset, by simply discarding the fractional part.
		// This isn't ideal, but there's no standard way of describing
		// these offsets, and is likely to be an extreme edge case.
		return 'Etc/GMT' . ( $sign === -1 ? '+' : '-' ) . $hours;
	}

	// For anything that's not an offset, return the string we got from WP.
	return $wp_tz;
}

/**
 * Bilmur RUM data collector
 */
add_action(
	'wp_enqueue_scripts',
	static function() {
		// Request a new version of bilmur every week.
		// This keeps bilmur up-to-date independently of CDN caching times.
		$weekly_cachebust = 'm=' . gmdate( 'YW' );

		// Allow for manually forcing a new version of bilmur with a plugin update.
		// Useful in case bilmur needs to be updated outside of the weekly schedule.
		// Just increment this number if that's the case.
		$manual_version = '1';

		// Base URL for bilmur script.
		$bilmur_url = 'https://s0.wp.com/wp-content/js/bilmur.min.js';

		wp_enqueue_script( 'bilmur', $bilmur_url . '?' . $weekly_cachebust, array(), $manual_version, array( 'strategy' => 'defer', 'in_footer' => true ) );
	}
);

// Add bilmur config to page for the script to pick up when it runs.
add_action(
	'wp_footer',
	function() {
		$custom_properties = defined( 'WPCOMSP_BILMUR_CUSTOM_PROPERTIES' ) ? WPCOMSP_BILMUR_CUSTOM_PROPERTIES : array();

		// Is the WooCommerce plugin active?
		$woo_active = class_exists( 'WooCommerce' ) ? '1' : '0';
		$custom_properties['woo_active'] = $woo_active;

		?>
			<meta
				id="bilmur"
				property="bilmur:data"
				content=""
				data-provider="<?php echo esc_attr( WPCOMSP_BILMUR_PROVIDER ) ?>"
				data-service="<?php echo esc_attr( WPCOMSP_BILMUR_SERVICE ) ?>"
				data-custom-props="<?php echo esc_attr( wp_json_encode( $custom_properties ) ) ?>"
				data-site-tz="<?php echo esc_attr( wpcomsp_bilmur_timezone_string() ) ?>"
			>
		<?php
	}
);
