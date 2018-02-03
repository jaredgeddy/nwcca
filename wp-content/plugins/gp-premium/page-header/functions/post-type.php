<?php
defined( 'WPINC' ) or die;

add_action( 'init', 'generate_page_header_post_type', 15 );
/**
 * Create our Page Header post type.
 *
 * @since 1.4
 */
function generate_page_header_post_type() {
	$labels = array(
		'name'                  => _x( 'Page Headers', 'Post Type General Name', 'page-header' ),
		'singular_name'         => _x( 'Page Header', 'Post Type Singular Name', 'page-header' ),
		'menu_name'             => __( 'Page Headers', 'page-header' ),
		'name_admin_bar'        => __( 'Page Header', 'page-header' ),
		'archives'              => __( 'Page Header Archives', 'page-header' ),
		'parent_item_colon'     => __( 'Parent Page Header:', 'page-header' ),
		'all_items'             => __( 'All Page Headers', 'page-header' ),
		'add_new_item'          => __( 'Add New Page Header', 'page-header' ),
		'new_item'              => __( 'New Page Header', 'page-header' ),
		'edit_item'             => __( 'Edit Page Header', 'page-header' ),
		'update_item'           => __( 'Update Page Header', 'page-header' ),
		'view_item'             => __( 'View Page Header', 'page-header' ),
		'search_items'          => __( 'Search Page Header', 'page-header' ),
		'insert_into_item'      => __( 'Insert into page header', 'page-header' ),
		'uploaded_to_this_item' => __( 'Uploaded to this page header', 'page-header' ),
	);
	$args = array(
		'label'                 => __( 'Page Header', 'page-header' ),
		'labels'                => $labels,
		'supports'              => array( 'title', 'thumbnail' ),
		'hierarchical'          => false,
		'public'                => false,
		'show_ui'               => true,
		'show_in_menu'          => true,
		'menu_position'         => 100,
		'menu_icon'				=> 'dashicons-welcome-widgets-menus',
		'show_in_admin_bar'     => false,
		'show_in_nav_menus'     => false,
		'can_export'            => true,
		'has_archive'           => false,
		'exclude_from_search'   => true,
		'publicly_queryable'    => false,
		'capability_type'       => 'page',
	);
	register_post_type( 'generate_page_header', $args );

	$post_types = get_post_types( array( 'public' => true ) );
	$term_args = array(
		'sanitize_callback' => 'int',
		'type' => 'string',
		'description' => '',
		'single' => true,
		'show_in_rest' => true,
	);
	foreach ( $post_types as $type ) {
		register_meta( $type, 'generate_page_header', $term_args );
	}

	$taxonomies = get_taxonomies( array( 'public' => true ) );
	if ( $taxonomies ) {
		foreach ( $taxonomies  as $taxonomy ) {
			add_action( $taxonomy . '_add_form_fields', 'generate_page_header_tax_new_ph_field' );
			add_action( $taxonomy . '_edit_form_fields', 'generate_page_header_tax_edit_ph_field' );
			add_action( 'edit_' . $taxonomy,   'generate_page_header_tax_save_ph' );
			add_action( 'create_' . $taxonomy, 'generate_page_header_tax_save_ph' );
		}
	}
}

/**
 * Build our taxonomy select option when adding new taxonomies.
 *
 * @since 1.4
 */
function generate_page_header_tax_new_ph_field() {
	wp_nonce_field( basename( __FILE__ ), 'generate_page_header_term_nonce' ); ?>
	<div class="form-field term-page-header-wrap">
		<label for="_generate-select-page-header"><?php _e( 'Page Header','page-header' ); ?></label>
		<select name="_generate-select-page-header" id="_generate-select-page-header">
			<option value=""></option>
			<?php
			$page_headers = get_posts(array(
				'posts_per_page' => -1,
				'orderby' => 'title',
				'post_type' => 'generate_page_header',
			));

			foreach( $page_headers as $header ) {
				printf( '<option value="%1$s">%2$s</option>',
					$header->ID,
					$header->post_title
				);
			}
			?>
		</select>
	</div>
	<?php
}

/**
 * Build our taxonomy select option when editing existing taxonomies.
 *
 * @since 1.4
 *
 * @param string $term The selected term.
 */
function generate_page_header_tax_edit_ph_field( $term ) {
?>
	<tr class="form-field form-required term-page-header-wrap">
		<th scope="row">
			<label for="_generate-select-page-header"><?php _e( 'Page Header','page-header' ); ?></label>
		</th>
		<td>
			<?php wp_nonce_field( basename( __FILE__ ), 'generate_page_header_term_nonce' ); ?>
			<select name="_generate-select-page-header" id="_generate-select-page-header">
				<option value="" <?php selected( get_term_meta( $term->term_id, '_generate-select-page-header', true ), ''  ); ?>></option>
				<?php
				$page_headers = get_posts(array(
					'posts_per_page' => -1,
					'orderby' => 'title',
					'post_type' => 'generate_page_header',
				));

				foreach( $page_headers as $header ) {
					printf( '<option value="%1$s" %2$s>%3$s</option>',
						$header->ID,
						selected( get_term_meta( $term->term_id, '_generate-select-page-header', true ), $header->ID ),
						$header->post_title
					);
				}
				?>
			</select>
		</td>
	</tr>
<?php }

/**
 * Save our selected page header inside taxonomies.
 *
 * @since 1.4
 *
 * @param int $term_id The selected term ID.
 */
function generate_page_header_tax_save_ph( $term_id ) {
	if ( ! isset( $_POST['generate_page_header_term_nonce'] ) || ! wp_verify_nonce( $_POST['generate_page_header_term_nonce'], basename( __FILE__ ) ) ) {
		return;
	}

	$old = get_term_meta( $term_id, '_generate-select-page-header', true );
	$new = isset( $_POST['_generate-select-page-header'] ) ? sanitize_key( $_POST['_generate-select-page-header'] ) : '';

	if ( $old && '' === $new ) {
		delete_term_meta( $term_id, '_generate-select-page-header' );
	} else if ( $old !== $new ) {
		update_term_meta( $term_id, '_generate-select-page-header', $new );
	}
}
