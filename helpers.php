<?php
if ( !function_exists( 'sp_get_array_depth' ) ) {
	function sp_get_array_depth( $array ) {
	    $max_depth = 1;
	    if ( is_array( $array ) ):
		    foreach ( $array as $value ):
		        if ( is_array( $value ) ):
		            $depth = sp_get_array_depth( $value ) + 1;
		            if ( $depth > $max_depth )
		                $max_depth = $depth;
		        endif;
		    endforeach;
	    	return $max_depth;
		else:
			return 0;
		endif;
	}
}

if ( !function_exists( 'sp_array_between' ) ) {
	function sp_array_between ( $array = array(), $delimiter = 0, $index = 0 ) {
		$keys = array_keys( $array, $delimiter );
		if ( array_key_exists( $index, $keys ) ):
			$offset = $keys[ $index ];
			$end = sizeof( $array );
			if ( array_key_exists( $index + 1, $keys ) )
				$end = $keys[ $index + 1 ];
			$length = $end - $offset;
			$array = array_slice( $array, $offset, $length );
		endif;
		return $array;
	}
}

if ( !function_exists( 'sp_array_value' ) ) {
	function sp_array_value( $arr = array(), $key = 0, $default = null ) {
		if ( is_array( $arr ) && array_key_exists( $key, $arr ) )
			$subset = $arr[ $key ];
		else
			$subset = $default;
		return $subset;
	}
}

if ( !function_exists( 'sp_array_combine' ) ) {
	function sp_array_combine( $keys = array(), $values = array() ) {
		$output = array();
		foreach ( $keys as $key ):
			if ( is_array( $values ) && array_key_exists( $key, $values ) )
				$output[ $key ] = $values[ $key ];
			else
				$output[ $key ] = array();
		endforeach;
		return $output;
	}
}

if ( !function_exists( 'sp_cpt_labels' ) ) {
	function sp_cpt_labels( $name, $singular_name ) {
		$labels = array(
			'name' => $name,
			'singular_name' => $singular_name,
			'all_items' => sprintf( __( 'All %s', 'sportspress' ), $name ),
			'add_new_item' => sprintf( __( 'Add New %s', 'sportspress' ), $singular_name ),
			'edit_item' => sprintf( __( 'Edit %s', 'sportspress' ), $singular_name ),
			'new_item' => sprintf( __( 'New %s', 'sportspress' ), $singular_name ),
			'view_item' => sprintf( __( 'View %s', 'sportspress' ), $singular_name ),
			'search_items' => sprintf( __( 'Search %s', 'sportspress' ), $name ),
			'not_found' => sprintf( __( 'No %s found', 'sportspress' ), $name ),
			'not_found_in_trash' => sprintf( __( 'No %s found in trash', 'sportspress' ), $name ),
			'parent_item_colon' => sprintf( __( 'Parent %s', 'sportspress' ), $singular_name ) . ':'
		);
		return $labels;
	}
}

if ( !function_exists( 'sp_tax_labels' ) ) {
	function sp_tax_labels( $name, $singular_name ) {
		$labels = array(
			'name' => $name,
			'singular_name' => $singular_name,
			'all_items' => sprintf( __( 'All %s', 'sportspress' ), $name ),
			'edit_item' => sprintf( __( 'Edit %s', 'sportspress' ), $singular_name ),
			'view_item' => sprintf( __( 'View %s', 'sportspress' ), $singular_name ),
			'update_item' => sprintf( __( 'Update %s', 'sportspress' ), $singular_name ),
			'add_new_item' => sprintf( __( 'Add New %s', 'sportspress' ), $singular_name ),
			'new_item_name' => sprintf( __( 'New %s Name', 'sportspress' ), $singular_name ),
			'parent_item' => sprintf( __( 'Parent %s', 'sportspress' ), $singular_name ),
			'parent_item_colon' => sprintf( __( 'Parent %s', 'sportspress' ), $singular_name ) . ':',
			'search_items' =>  sprintf( __( 'Search %s', 'sportspress' ), $name ),
			'not_found' => sprintf( __( 'No %s found', 'sportspress' ), $name )
		);
		return $labels;
	}
}

if ( !function_exists( 'sp_dropdown_taxonomies' ) ) {
	function sp_dropdown_taxonomies( $args = array() ) {
		$defaults = array(
			'show_option_all' => false,
			'show_option_none' => false,
			'taxonomy' => null,
			'name' => null,
			'selected' => null,
		);
		$args = array_merge( $defaults, $args ); 
		$terms = get_terms( $args['taxonomy'] );
		$name = ( $args['name'] ) ? $args['name'] : $args['taxonomy'];
		if ( $terms ) {
			printf( '<select name="%s" class="postform">', $name );
			if ( $args['show_option_all'] ) {
				printf( '<option value="0">%s</option>', $args['show_option_all'] );
			}
			if ( $args['show_option_none'] ) {
				printf( '<option value="-1">%s</option>', $args['show_option_none'] );
			}
			foreach ( $terms as $term ) {
				printf( '<option value="%s" %s>%s</option>', $term->term_id, selected( true, $args['selected'] == $term->term_id, false ), $term->name );
			}
			print( '</select>' );
		}
	}
}

if ( !function_exists( 'sp_the_posts' ) ) {
	function sp_the_posts( $post_id = null, $meta = 'post', $before = '', $sep = ', ', $after = '', $delimiter = ' - ' ) {
		if ( ! isset( $post_id ) )
			global $post_id;
		$ids = get_post_meta( $post_id, $meta, false );
		if ( ( $key = array_search( 0, $ids ) ) !== false )
		    unset( $ids[ $key ] );
		$i = 0;
		$count = count( $ids );
		if ( isset( $ids ) && $ids && is_array( $ids ) && !empty( $ids ) ):
			foreach ( $ids as $id ):
				if ( !$id ) continue;
				if ( !empty( $before ) ):
					if ( is_array( $before ) && array_key_exists( $i, $before ) )
						echo $before[ $i ] . ' - ';
					else
						echo $before;
				endif;
				$parents = get_post_ancestors( $id );
				$parents = array_combine( array_keys( $parents ), array_reverse( array_values( $parents ) ) );
				foreach ( $parents as $parent ):
					if ( !in_array( $parent, $ids ) )
						edit_post_link( get_the_title( $parent ), '', '', $parent );
					echo $delimiter;
				endforeach;
				$title = get_the_title( $id );
				if ( empty( $title ) )
					$title = __( '(no title)' );
				edit_post_link( $title, '', '', $id );
				if ( !empty( $after ) ):
					if ( is_array( $after ) && array_key_exists( $i, $after ) )
						echo ' - ' . $after[ $i ];
					else
						echo $after;
				endif;
				if ( ++$i !== $count )
					echo $sep;
			endforeach;
		endif;
	}
}

if ( !function_exists( 'sp_post_checklist' ) ) {
	function sp_post_checklist( $post_id = null, $meta = 'post', $display = 'block', $filter = null, $index = null ) {
		if ( ! isset( $post_id ) )
			global $post_id;
		?>
		<div id="<?php echo $meta; ?>-all" class="posttypediv wp-tab-panel sp-tab-panel" style="display: <?php echo $display; ?>;">
			<input type="hidden" value="0" name="sportspress[<?php echo $meta; ?>]<?php if ( isset( $index ) ) echo '[' . $index . ']'; ?>[]" />
			<ul class="categorychecklist form-no-clear">
				<?php
				$selected = sp_array_between( (array)get_post_meta( $post_id, $meta, false ), 0, $index );
				$posts = get_pages( array( 'post_type' => $meta, 'number' => 0 ) );
				if ( empty( $posts ) )
					$posts = get_posts( array( 'post_type' => $meta, 'numberposts' => 0 ) );
				foreach ( $posts as $post ):
					$parents = get_post_ancestors( $post );
					if ( $filter ):
						$filter_values = (array)get_post_meta( $post->ID, $filter, false );
						$terms = (array)get_the_terms( $post->ID, 'sp_league' );
						foreach ( $terms as $term ):
							if ( is_object( $term ) && property_exists( $term, 'term_id' ) )
								$filter_values[] = $term->term_id;
						endforeach;
					endif;
					?>
					<li class="sp-post<?php
						if ( $filter ):
							echo ' sp-filter-0';
							foreach ( $filter_values as $filter_value ):
								echo ' sp-filter-' . $filter_value;
							endforeach;
						endif;
					?>">
						<?php echo str_repeat( '<ul><li>', sizeof( $parents ) ); ?>
						<label class="selectit">
							<input type="checkbox" value="<?php echo $post->ID; ?>" name="sportspress[<?php echo $meta; ?>]<?php if ( isset( $index ) ) echo '[' . $index . ']'; ?>[]"<?php if ( in_array( $post->ID, $selected ) ) echo ' checked="checked"'; ?>>
							<?php
							$title = $post->post_title;
							if ( empty( $title ) )
								$title = __( '(no title)' );
							echo $title;
							?>
						</label>
						<?php echo str_repeat( '</li></ul>', sizeof( $parents ) ); ?>
					</li>
					<?php
				endforeach;
				?>
			</ul>
		</div>
		<?php
	}
}

if ( !function_exists( 'sp_get_stats' ) ) {
	function sp_get_stats( $post_id, $set_id = 0, $subset_id = 0, $slug = 'sp_stats' ) {
		if ( isset( $post_id ) )
			return sp_array_value( sp_array_value( (array)get_post_meta( $post_id, $slug, true ), $set_id, array() ), $subset_id, array() );
		else
			return array();
	}
}

if ( !function_exists( 'sp_stats_table' ) ) {
	function sp_stats_table( $stats = array(), $placeholders = array(), $index = 0, $columns = array( 'Name' ), $total = true, $rowtype = 'post', $slug = 'sp_stats' ) {
		global $pagenow;
		if ( !is_array( $stats ) )
			$stats = array();
		?>
		<table class="widefat sp-stats-table">
			<thead>
				<tr>
					<?php foreach ( $columns as $column ): ?>
						<th><?php echo $column; ?></th>
					<?php endforeach; ?>
				</tr>
			</thead>
			<tbody>
				<?php
				$i = 0;
				foreach ( $stats as $key => $values ):
					if ( !$key ) continue;
					?>
					<tr class="sp-row sp-post<?php if ( $i % 2 == 0 ) echo ' alternate'; ?>">
						<td>
							<?php
							switch( $rowtype ):
								case 'post':
									$title = get_the_title( $key );
									break;
								default:
									$term = get_term( $key, $rowtype );
									$title = $term->name;;
									break;
							endswitch;
							if ( empty( $title ) )
								$title = __( '(no title)' );
							echo $title;
							?>
						</td>
						<?php for ( $j = 0; $j < sizeof( $columns ) - 1; $j ++ ):
							$value = sp_array_value( $values, $j, '' );
							$placeholder = (int)sp_array_value( sp_array_value( $placeholders, $key, 0), $j, 0 );
							?>
							<td><input type="text" name="sportspress[<?php echo $slug; ?>][<?php echo $index; ?>][<?php echo $key; ?>][]" value="<?php echo $value; ?>" placeholder="<?php echo $placeholder; ?>" /></td>
						<?php endfor; ?>
					</tr>
					<?php
					$i++;
				endforeach;
				if ( $total ):
					$values = array_key_exists( 0, $stats ) ? $stats[0] : array();
					?>
					<tr class="sp-row sp-total<?php if ( $i % 2 == 0 ) echo ' alternate'; ?>">
						<td><strong><?php _e( 'Total', 'sportspress' ); ?></strong></td>
						<?php for ( $j = 0; $j < sizeof( $columns ) - 1; $j ++ ):
							$value = sp_array_value( $values, $j, '' );
							$placeholder = 0;
							?>
							<td><input type="text" name="sportspress[<?php echo $slug; ?>][<?php echo $index; ?>][0][]" value="<?php echo $value; ?>" placeholder="<?php echo $placeholder; ?>" /></td>
						<?php endfor; ?>
					</tr>
				<?php endif; ?>
			</tbody>
		</table>
		<?php
	}
}

if ( !function_exists( 'sp_post_adder' ) ) {
	function sp_post_adder( $meta = 'post' ) {
		$obj = get_post_type_object( $meta );
		?>
		<div id="<?php echo $meta; ?>-adder">
			<h4>
				<a title="<?php echo sprintf( esc_attr__( 'Add New %s', 'sportspress' ), esc_attr__( 'Team', 'sportspress' ) ); ?>" href="<?php echo admin_url( 'post-new.php?post_type=' . $meta ); ?>" target="_blank">
					+ <?php echo sprintf( __( 'Add New %s', 'sportspress' ), $obj->labels->singular_name ); ?>
				</a>
			</h4>
		</div>
		<?php
	}
}
?>