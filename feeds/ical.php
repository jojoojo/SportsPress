<?php
/**
 * iCal Feed
 *
 * @author 		ThemeBoy
 * @category 	Feeds
 * @package 	SportsPress/Feeds
 * @version     1.4
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( 'sp_calendar' !== get_post_type( $post ) ) {
	wp_die( __( 'ERROR: This is not a valid feed template.', 'sportspress' ), '', array( 'response' => 404 ) );
}

// Get events in calendar
$calendar = new SP_Calendar( $post );
$events = $calendar->data();

// Get blog locale
$locale = substr( get_locale(), 0, 2 );

// Initialize output. Max line length is 75 chars.
$output =
"BEGIN:VCALENDAR\n" .
"METHOD:PUBLISH\n" .
"VERSION:2.0\n" .
"URL:" . add_query_arg( 'feed', 'sp-calendar-ical', get_post_permalink( $post ) ) . "\n" .
"NAME:" . $post->post_title . "\n" .
"X-WR-CALNAME:" . $post->post_title . "\n" .
"DESCRIPTION:" . $post->post_title . "\n" .
"DESCRIPTION:" . $post->post_title . "\n" .
"X-WR-CALDESC:" . $post->post_title . "\n" .
"REFRESH-INTERVAL;VALUE=DURATION:PT1H\n" .
"X-PUBLISHED-TTL:PT1H\n" .
"PRODID:-//ThemeBoy//SportsPress//" . strtoupper( $locale ) . "\n";

// Loop through each event
foreach ( $events as $event):

	// Define date format
	$date_format = 'Ymd\THis\Z';

	// Initialize end time	
	$end = new DateTime( $event->post_date_gmt );

	// Get full time minutes
	$minutes = get_post_meta( $event->post_id, 'sp_minutes', true );
	if ( false === $minutes ) $minutes = get_option( 'sportspress_event_minutes', 90 );

	// Add full time minutes to end time
	$end->add( new DateInterval( 'PT' . $minutes . 'M' ) );

	// Initialize location
	$location = '';

	// Get venue information
	$venues = get_the_terms( $event->ID, 'sp_venue' );
	if ( $venues ) {
		$venue = reset( $venues );
		$location .= $venue->name;

		// Get venue term meta
		$t_id = $venue->term_id;
		$meta = get_option( "taxonomy_$t_id" );

		// Add details to location
		$address = sp_array_value( $meta, 'sp_address', false );
		if ( false !== $address ) {
			$location = $address;
		}

		// Generate geo tag
		$latitude = sp_array_value( $meta, 'sp_latitude', false );
		$longitude = sp_array_value( $meta, 'sp_longitude', false );
		if ( false !== $latitude && false !== $longitude ) {
			$geo = $latitude . ';' . $longitude;
		} else {
			$geo = false;
		}
	}

	// Append to output string
	$output .=
	"BEGIN:VEVENT\n" .
	"SUMMARY:$event->post_title\n" .
	"UID:$event->ID\n" .
	"STATUS:CONFIRMED\n" .
	"DTSTART:" . mysql2date( $date_format, $event->post_date_gmt ) . "\n" .
	"DTEND:" . $end->format( $date_format ) . "\n" .
	"LAST-MODIFIED:" . mysql2date( $date_format, $event->post_modified_gmt ) . "\n" .
	"LOCATION:" . $location . "\n";

	if ( false !== $geo ) {
		$output .= "GEO:" . $geo . "\n";
	}

	$output .= "END:VEVENT\n";
endforeach;

// End output
$output .= "END:VCALENDAR";

echo $output;