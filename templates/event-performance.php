<?php
/**
 * Event Performance
 *
 * @author 		ThemeBoy
 * @package 	SportsPress/Templates
 * @version     1.9.6
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$show_players = get_option( 'sportspress_event_show_players', 'yes' ) === 'yes' ? true : false;
$show_staff = get_option( 'sportspress_event_show_staff', 'yes' ) === 'yes' ? true : false;
$show_total = get_option( 'sportspress_event_show_total', 'yes' ) === 'yes' ? true : false;
$show_numbers = get_option( 'sportspress_event_show_player_numbers', 'yes' ) === 'yes' ? true : false;
$sections = get_option( 'sportspress_event_performance_sections', -1 );
$reverse_teams = get_option( 'sportspress_event_performance_reverse_teams', 'no' ) === 'yes' ? true : false;
$primary = sp_get_main_performance_option();
$total = get_option( 'sportspress_event_total_performance', 'all');

if ( ! $show_players && ! $show_staff && ! $show_total ) return;

if ( ! isset( $id ) )
	$id = get_the_ID();

$teams = get_post_meta( $id, 'sp_team', false );

if ( is_array( $teams ) ):
	?>
	<div class="sp-event-performance-tables sp-event-performance-teams">
	<?php

	$event = new SP_Event( $id );
	$performance = $event->performance();

	$link_posts = get_option( 'sportspress_link_players', 'yes' ) == 'yes' ? true : false;
	$scrollable = get_option( 'sportspress_enable_scrollable_tables', 'yes' ) == 'yes' ? true : false;
	$sortable = get_option( 'sportspress_enable_sortable_tables', 'yes' ) == 'yes' ? true : false;
	$mode = get_option( 'sportspress_event_performance_mode', 'values' );

	// The first row should be column labels
	$labels =  apply_filters( 'sportspress_event_box_score_labels', $performance[0], $event, $mode );

	// Remove the first row to leave us with the actual data
	unset( $performance[0] );

	$performance = array_filter( $performance );

	$status = $event->status();

	// Get performance ids for icons
	if ( $mode == 'icons' ):
		$performance_ids = array();
		$performance_posts = get_posts( array( 'posts_per_page' => -1, 'post_type' => 'sp_performance' ) );
		foreach ( $performance_posts as $post ):
			$performance_ids[ $post->post_name ] = $post->ID;
		endforeach;
	endif;

	if ( $reverse_teams ) {
		$teams = array_reverse( $teams, true );
	}
	
	// Prepare for offense and defense sections
	if ( -1 != $sections ) {
		
		// Determine order of sections
		if ( 1 == $sections ) {
			$section_order = array( __( 'Defense', 'sportspress' ), __( 'Offense', 'sportspress' ) );
		} else {
			$section_order = array( __( 'Offense', 'sportspress' ), __( 'Defense', 'sportspress' ) );
		}
		
		// Initialize labels
		$labels = array( array(), array() );
		
		// Add positions if applicable
		if ( 'yes' == get_option( 'sportspress_event_show_position', 'yes' ) ) {
			$labels[0]['position'] = $labels[1]['position'] = __( 'Position', 'sportspress' );
		}

		// Get labels by section
		$args = array(
			'post_type' => 'sp_performance',
			'numberposts' => 100,
			'posts_per_page' => 100,
			'orderby' => 'menu_order',
			'order' => 'ASC',
		);

		$columns = get_posts( $args );

		foreach ( $columns as $column ):
			$section = get_post_meta( $column->ID, 'sp_section', true );
			if ( '' === $section ) {
				$section = -1;
			}
			switch ( $section ):
				case 1:
					$labels[1][ $column->post_name ] = $column->post_title;
					break;
				case 0:
					$labels[0][ $column->post_name ] = $column->post_title;
					break;
				default:
					$labels[0][ $column->post_name ] = $column->post_title;
					$labels[1][ $column->post_name ] = $column->post_title;
			endswitch;
		endforeach;
	}

	foreach( $teams as $index => $team_id ):
		if ( -1 == $team_id ) continue;

		// Get results for players in the team
		$players = sp_array_between( (array)get_post_meta( $id, 'sp_player', false ), 0, $index );
		$has_players = sizeof( $players ) > 1;

		$players = apply_filters( 'sportspress_event_performance_split_team_players', $players );

		$show_team_players = $show_players && $has_players;

		if ( ! $show_team_players && ! $show_staff && ! $show_total ) continue;

		if ( $show_team_players || $show_total ) {
			if ( -1 != $sections ) {
				
				$data = array();
				
				// Get results for offensive players in the team
				$offense = sp_array_between( (array)get_post_meta( $id, 'sp_offense', false ), 0, $index );
				$data[0] = sp_array_combine( $offense, sp_array_value( $performance, $team_id, array() ) );
				
				// Get results for defensive players in the team
				$defense = sp_array_between( (array)get_post_meta( $id, 'sp_defense', false ), 0, $index );
				$data[1] = sp_array_combine( $defense, sp_array_value( $performance, $team_id, array() ) );
				
				foreach ( $section_order as $section_id => $section_label ) {
					if ( sizeof( $data[ $section_id ] ) ) {
						sp_get_template( 'event-performance-table.php', array(
							'section' => $section_label,
							'scrollable' => $scrollable,
							'sortable' => $sortable,
							'show_players' => $show_team_players,
							'show_numbers' => $show_numbers,
							'show_total' => $show_total,
							'caption' => 0 == $section_id && $team_id ? get_the_title( $team_id ) : null,
							'labels' => $labels[ $section_id ],
							'mode' => $mode,
							'data' => $data[ $section_id ],
							'event' => $event,
							'link_posts' => $link_posts,
							'performance_ids' => isset( $performance_ids ) ? $performance_ids : null,
							'primary' => 'primary' == $total ? $primary : null,
							'class' => 'sp-template-event-performance-team-' . $index . '-section-' . $section_id,
						) );
					}
				}
			} else {
				if ( 0 < $team_id ) {
					$data = sp_array_combine( $players, sp_array_value( $performance, $team_id, array() ) );
				} elseif ( 0 == $team_id ) {
					$data = array();
					foreach ( $players as $player_id ) {
						if ( isset( $performance[ $player_id ][ $player_id ] ) ) {
							$data[ $player_id ] = $performance[ $player_id ][ $player_id ];
						}
					}
				} else {
					$data = sp_array_value( array_values( $performance ), $index );
				}
				
				sp_get_template( 'event-performance-table.php', array(
					'scrollable' => $scrollable,
					'sortable' => $sortable,
					'show_players' => $show_team_players,
					'show_numbers' => $show_numbers,
					'show_total' => $show_total,
					'caption' => $team_id ? get_the_title( $team_id ) : null,
					'labels' => $labels,
					'mode' => $mode,
					'data' => $data,
					'event' => $event,
					'link_posts' => $link_posts,
					'performance_ids' => isset( $performance_ids ) ? $performance_ids : null,
					'primary' => 'primary' == $total ? $primary : null,

				) );
			}
		}
		if ( $show_staff ):
			sp_get_template( 'event-staff.php', array( 'id' => $id, 'index' => $index ) );
		endif;
		?>
		<?php
	endforeach;

	do_action( 'sportspress_event_performance' );
	?>
	</div><!-- .sp-event-performance-tables -->
	<?php
endif;
