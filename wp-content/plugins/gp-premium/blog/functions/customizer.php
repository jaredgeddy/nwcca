<?php
defined( 'WPINC' ) or die;

if ( ! function_exists( 'generate_blog_customize_register' ) ) :
add_action( 'customize_register', 'generate_blog_customize_register', 99 );
function generate_blog_customize_register( $wp_customize ) {
	// Get our defaults
	$defaults = generate_blog_get_defaults();

	// Get our controls
	require_once GP_LIBRARY_DIRECTORY . 'customizer-helpers.php';

	// Add control types so controls can be built using JS
	if ( method_exists( $wp_customize, 'register_control_type' ) ) {
		$wp_customize->register_control_type( 'GeneratePress_Title_Customize_Control' );
	}

	// Add our blog panel
	if ( class_exists( 'WP_Customize_Panel' ) ) {
		$wp_customize->add_panel( 'generate_blog_panel', array(
			'priority'       => 35,
			'capability'     => 'edit_theme_options',
			'theme_supports' => '',
			'title'          => __( 'Blog','generate-blog' ),
			'description'    => ''
		) );
	}

	// Remove our blog control from the free theme
	if ( $wp_customize->get_control( 'blog_content_control' ) ) {
		$wp_customize->remove_control( 'blog_content_control' );
	}

	// Register our custom controls
	if ( method_exists( $wp_customize,'register_control_type' ) ) {
		$wp_customize->register_control_type( 'GeneratePress_Refresh_Button_Customize_Control' );
		$wp_customize->register_control_type( 'GeneratePress_Information_Customize_Control' );
		$wp_customize->register_control_type( 'Generate_Control_Toggle_Customize_Control' );
	}

	// Blog content section
	$wp_customize->add_section(
		'generate_blog_section',
		array(
			'title' => __( 'Blog', 'generate-blog' ),
			'capability' => 'edit_theme_options',
			'panel' => 'generate_layout_panel',
			'priority' => 40
		)
	);

	$wp_customize->add_control(
		new GeneratePress_Title_Customize_Control(
			$wp_customize,
			'generate_blog_archives_title',
			array(
				'section'     => 'generate_blog_section',
				'type'        => 'generatepress-customizer-title',
				'title'			=> __( 'Content','generate-blog' ),
				'settings' => ( isset( $wp_customize->selective_refresh ) ) ? array() : 'blogname',
				'priority' => 0
			)
		)
	);

	$wp_customize->add_control(
		new Generate_Control_Toggle_Customize_Control(
			$wp_customize,
			'generate_post_meta_toggle',
			array(
				'section' => 'generate_blog_section',
				'targets' => array(
					'post-meta-archives' => __( 'Archives', 'generate-blog' ),
					'post-meta-single' => __( 'Single', 'generate-blog' )
				),
				'settings' => ( isset( $wp_customize->selective_refresh ) ) ? array() : 'blogname',
				'priority' => 0
			)
		)
	);

	$wp_customize->add_control(
		'generate_settings[post_content]',
		array(
			'type' => 'select',
			'label' => __( 'Content type', 'generate-blog' ),
			'section' => 'generate_blog_section',
			'choices' => array(
				'full' => __( 'Full', 'generate-blog' ),
				'excerpt' => __( 'Excerpt', 'generate-blog' )
			),
			'settings' => 'generate_settings[post_content]',
		)
	);

	// Excerpt length
	$wp_customize->add_setting(
		'generate_blog_settings[excerpt_length]', array(
			'default' => $defaults['excerpt_length'],
			'capability' => 'edit_theme_options',
			'type' => 'option',
			'sanitize_callback' => 'absint'
		)
	);

	$wp_customize->add_control(
		'generate_blog_settings[excerpt_length]', array(
			'type' => 'number',
			'label' => __( 'Excerpt word count', 'generate-blog' ),
			'section' => 'generate_blog_section',
			'settings' => 'generate_blog_settings[excerpt_length]',
			'active_callback' => 'generate_premium_is_excerpt',
		)
	);

	// Read more text
	$wp_customize->add_setting(
		'generate_blog_settings[read_more]', array(
			'default' => $defaults['read_more'],
			'capability' => 'edit_theme_options',
			'type' => 'option',
			'sanitize_callback' => 'wp_kses_post'
		)
	);

	$wp_customize->add_control(
		'generate_blog_settings[read_more]', array(
			'type' => 'text',
			'label' => __('Read more label', 'generate-blog'),
			'section' => 'generate_blog_section',
			'settings' => 'generate_blog_settings[read_more]',
		)
	);

	// Read more button
	$wp_customize->add_setting(
		'generate_blog_settings[read_more_button]',
		array(
			'default' => $defaults['read_more_button'],
			'type' => 'option',
			'sanitize_callback' => 'generate_premium_sanitize_checkbox'
		)
	);

	$wp_customize->add_control(
		'generate_blog_settings[read_more_button]',
		array(
			'type' => 'checkbox',
			'label' => __( 'Display read more as button', 'generate-blog' ),
			'section' => 'generate_blog_section',
			'settings' => 'generate_blog_settings[read_more_button]',
		)
	);

	// Post date
	$wp_customize->add_setting(
		'generate_blog_settings[date]',
		array(
			'default' => $defaults['date'],
			'type' => 'option',
			'sanitize_callback' => 'generate_premium_sanitize_checkbox'
		)
	);

	$wp_customize->add_control(
		'generate_blog_settings[date]',
		array(
			'type' => 'checkbox',
			'label' => __( 'Display post date', 'generate-blog' ),
			'section' => 'generate_blog_section',
			'settings' => 'generate_blog_settings[date]',
		)
	);

	// Post author
	$wp_customize->add_setting(
		'generate_blog_settings[author]',
		array(
			'default' => $defaults['author'],
			'type' => 'option',
			'sanitize_callback' => 'generate_premium_sanitize_checkbox'
		)
	);

	$wp_customize->add_control(
		'generate_blog_settings[author]',
		array(
			'type' => 'checkbox',
			'label' => __( 'Display post author', 'generate-blog' ),
			'section' => 'generate_blog_section',
			'settings' => 'generate_blog_settings[author]',
		)
	);

	// Category links
	$wp_customize->add_setting(
		'generate_blog_settings[categories]',
		array(
			'default' => $defaults['categories'],
			'type' => 'option',
			'sanitize_callback' => 'generate_premium_sanitize_checkbox'
		)
	);

	$wp_customize->add_control(
		'generate_blog_settings[categories]',
		array(
			'type' => 'checkbox',
			'label' => __( 'Display post categories', 'generate-blog' ),
			'section' => 'generate_blog_section',
			'settings' => 'generate_blog_settings[categories]',
		)
	);

	// Tag links
	$wp_customize->add_setting(
		'generate_blog_settings[tags]',
		array(
			'default' => $defaults['tags'],
			'type' => 'option',
			'sanitize_callback' => 'generate_premium_sanitize_checkbox'
		)
	);

	$wp_customize->add_control(
		'generate_blog_settings[tags]',
		array(
			'type' => 'checkbox',
			'label' => __( 'Display post tags', 'generate-blog' ),
			'section' => 'generate_blog_section',
			'settings' => 'generate_blog_settings[tags]',
		)
	);

	// Comment link
	$wp_customize->add_setting(
		'generate_blog_settings[comments]',
		array(
			'default' => $defaults['comments'],
			'type' => 'option',
			'sanitize_callback' => 'generate_premium_sanitize_checkbox'
		)
	);

	$wp_customize->add_control(
		'generate_blog_settings[comments]',
		array(
			'type' => 'checkbox',
			'label' => __( 'Display comment count', 'generate-blog' ),
			'section' => 'generate_blog_section',
			'settings' => 'generate_blog_settings[comments]',
		)
	);

	// Infinite scroll
	$wp_customize->add_setting(
		'generate_blog_settings[infinite_scroll]',
		array(
			'default' => $defaults['infinite_scroll'],
			'type' => 'option',
			'sanitize_callback' => 'generate_premium_sanitize_checkbox'
		)
	);

	$wp_customize->add_control(
		'generate_blog_settings[infinite_scroll]',
		array(
			'type' => 'checkbox',
			'label' => __( 'Use infinite scroll', 'generate-blog' ),
			'section' => 'generate_blog_section',
			'settings' => 'generate_blog_settings[infinite_scroll]',
		)
	);

	// Infinite scroll
	$wp_customize->add_setting(
		'generate_blog_settings[infinite_scroll_button]',
		array(
			'default' => $defaults['infinite_scroll_button'],
			'type' => 'option',
			'sanitize_callback' => 'generate_premium_sanitize_checkbox'
		)
	);

	$wp_customize->add_control(
		'generate_blog_settings[infinite_scroll_button]',
		array(
			'type' => 'checkbox',
			'label' => __( 'Use button to load more posts', 'generate-blog' ),
			'section' => 'generate_blog_section',
			'settings' => 'generate_blog_settings[infinite_scroll_button]',
			'active_callback' => 'generate_premium_infinite_scroll_active',
		)
	);

	// Load more text
	$wp_customize->add_setting(
		'generate_blog_settings[masonry_load_more]', array(
			'default' => $defaults['masonry_load_more'],
			'capability' => 'edit_theme_options',
			'type' => 'option',
			'sanitize_callback' => 'wp_kses_post'
		)
	);

	$wp_customize->add_control(
		'blog_masonry_load_more_control', array(
			'label' => __( 'Load more label', 'generate-blog' ),
			'section' => 'generate_blog_section',
			'settings' => 'generate_blog_settings[masonry_load_more]',
			'active_callback' => 'generate_premium_infinite_scroll_button_active'
		)
	);

	// Loading text
	$wp_customize->add_setting(
		'generate_blog_settings[masonry_loading]', array(
			'default' => $defaults['masonry_loading'],
			'capability' => 'edit_theme_options',
			'type' => 'option',
			'sanitize_callback' => 'wp_kses_post'
		)
	);

	$wp_customize->add_control(
		'blog_masonry_loading_control', array(
			'label' => __( 'Loading label', 'generate-blog' ),
			'section' => 'generate_blog_section',
			'settings' => 'generate_blog_settings[masonry_loading]',
			'active_callback' => 'generate_premium_infinite_scroll_button_active'
		)
	);

	$wp_customize->add_setting(
		'generate_blog_settings[single_date]',
		array(
			'default' => $defaults['single_date'],
			'type' => 'option',
			'sanitize_callback' => 'generate_premium_sanitize_checkbox'
		)
	);

	$wp_customize->add_control(
		'generate_blog_settings[single_date]',
		array(
			'type' => 'checkbox',
			'label' => __( 'Display post date', 'generate-blog' ),
			'section' => 'generate_blog_section',
			'settings' => 'generate_blog_settings[single_date]',
		)
	);

	$wp_customize->add_setting(
		'generate_blog_settings[single_author]',
		array(
			'default' => $defaults['single_author'],
			'type' => 'option',
			'sanitize_callback' => 'generate_premium_sanitize_checkbox'
		)
	);

	$wp_customize->add_control(
		'generate_blog_settings[single_author]',
		array(
			'type' => 'checkbox',
			'label' => __( 'Display post author', 'generate-blog' ),
			'section' => 'generate_blog_section',
			'settings' => 'generate_blog_settings[single_author]',
		)
	);

	$wp_customize->add_setting(
		'generate_blog_settings[single_categories]',
		array(
			'default' => $defaults['single_categories'],
			'type' => 'option',
			'sanitize_callback' => 'generate_premium_sanitize_checkbox'
		)
	);

	$wp_customize->add_control(
		'generate_blog_settings[single_categories]',
		array(
			'type' => 'checkbox',
			'label' => __( 'Display post categories', 'generate-blog' ),
			'section' => 'generate_blog_section',
			'settings' => 'generate_blog_settings[single_categories]',
		)
	);

	$wp_customize->add_setting(
		'generate_blog_settings[single_tags]',
		array(
			'default' => $defaults['single_tags'],
			'type' => 'option',
			'sanitize_callback' => 'generate_premium_sanitize_checkbox'
		)
	);

	$wp_customize->add_control(
		'generate_blog_settings[single_tags]',
		array(
			'type' => 'checkbox',
			'label' => __( 'Display post tags', 'generate-blog' ),
			'section' => 'generate_blog_section',
			'settings' => 'generate_blog_settings[single_tags]',
		)
	);

	$wp_customize->add_setting(
		'generate_blog_settings[single_post_navigation]',
		array(
			'default' => $defaults['single_post_navigation'],
			'type' => 'option',
			'sanitize_callback' => 'generate_premium_sanitize_checkbox'
		)
	);

	$wp_customize->add_control(
		'generate_blog_settings[single_post_navigation]',
		array(
			'type' => 'checkbox',
			'label' => __( 'Display post navigation', 'generate-blog' ),
			'section' => 'generate_blog_section',
			'settings' => 'generate_blog_settings[single_post_navigation]',
		)
	);

	$wp_customize->add_control(
		new GeneratePress_Title_Customize_Control(
			$wp_customize,
			'generate_blog_featured_images_title',
			array(
				'section'     => 'generate_blog_section',
				'type'        => 'generatepress-customizer-title',
				'title'			=> __( 'Featured Images', 'generate-blog' ),
				'settings' => ( isset( $wp_customize->selective_refresh ) ) ? array() : 'blogname',
			)
		)
	);

	$wp_customize->add_control(
		new Generate_Control_Toggle_Customize_Control(
			$wp_customize,
			'generate_featured_image_toggle',
			array(
				'section' => 'generate_blog_section',
				'targets' => array(
					'featured-image-archives' => __( 'Archives', 'generate-blog' ),
					'featured-image-single' => __( 'Posts', 'generate-blog' ),
					'featured-image-page' => __( 'Pages', 'generate-blog' ),
				),
				'settings' => ( isset( $wp_customize->selective_refresh ) ) ? array() : 'blogname'
			)
		)
	);

	// Show featured images
	$wp_customize->add_setting(
		'generate_blog_settings[post_image]',
		array(
			'default' => $defaults['post_image'],
			'type' => 'option',
			'sanitize_callback' => 'generate_premium_sanitize_checkbox'
		)
	);

	$wp_customize->add_control(
		'generate_blog_settings[post_image]',
		array(
			'type' => 'checkbox',
			'label' => __( 'Display featured images', 'generate-blog' ),
			'section' => 'generate_blog_section',
			'settings' => 'generate_blog_settings[post_image]',
		)
	);

	// Padding
	$wp_customize->add_setting(
		'generate_blog_settings[post_image_padding]',
		array(
			'default' => $defaults['post_image_padding'],
			'type' => 'option',
			'sanitize_callback' => 'generate_premium_sanitize_checkbox'
		)
	);

	$wp_customize->add_control(
		'generate_blog_settings[post_image_padding]',
		array(
			'type' => 'checkbox',
			'label' => __( 'Display padding around images', 'generate-blog' ),
			'section' => 'generate_blog_section',
			'settings' => 'generate_blog_settings[post_image_padding]',
			'active_callback' => 'generate_premium_display_image_padding',
		)
	);

	// Location
	$wp_customize->add_setting(
		'generate_blog_settings[post_image_position]',
		array(
			'default' => $defaults['post_image_position'],
			'type' => 'option',
			'sanitize_callback' => 'generate_premium_sanitize_choices'
		)
	);

	$wp_customize->add_control(
		'generate_blog_settings[post_image_position]',
		array(
			'type' => 'select',
			'label' => __( 'Location', 'generate-blog' ),
			'section' => 'generate_blog_section',
			'choices' => array(
				'' => __( 'Below Title', 'generate-blog' ),
				'post-image-above-header' => __( 'Above Title', 'generate-blog' )
			),
			'settings' => 'generate_blog_settings[post_image_position]',
			'active_callback' => 'generate_premium_featured_image_active'
		)
	);

	// Alignment
	$wp_customize->add_setting(
		'generate_blog_settings[post_image_alignment]',
		array(
			'default' => $defaults['post_image_alignment'],
			'type' => 'option',
			'sanitize_callback' => 'generate_premium_sanitize_choices'
		)
	);

	$wp_customize->add_control(
		'generate_blog_settings[post_image_alignment]',
		array(
			'type' => 'select',
			'label' => __( 'Alignment', 'generate-blog' ),
			'section' => 'generate_blog_section',
			'choices' => array(
				'post-image-aligned-center' => __( 'Center', 'generate-blog' ),
				'post-image-aligned-left' => __( 'Left', 'generate-blog' ),
				'post-image-aligned-right' => __( 'Right', 'generate-blog' ),
			),
			'settings' => 'generate_blog_settings[post_image_alignment]',
			'active_callback' => 'generate_premium_featured_image_active'
		)
	);

	// Width
	$wp_customize->add_setting(
		'generate_blog_settings[post_image_width]', array(
			'default' => $defaults['post_image_width'],
			'capability' => 'edit_theme_options',
			'type' => 'option',
			'transport' => 'postMessage',
			'sanitize_callback' => 'generate_premium_sanitize_empty_absint'
		)
	);

	$wp_customize->add_control(
		'generate_blog_settings[post_image_width]',
		array(
			'type' => 'number',
			'label' => __( 'Width', 'generate-blog' ),
			'section' => 'generate_blog_section',
			'input_attrs' => array(
				'style' => 'text-align:center;',
				'placeholder' => __('Auto','generate-blog'),
				'min' => 5,
			),
			'settings' => 'generate_blog_settings[post_image_width]',
			'active_callback' => 'generate_premium_featured_image_active'
		)
	);

	// Height
	$wp_customize->add_setting(
		'generate_blog_settings[post_image_height]', array(
			'default' => $defaults['post_image_height'],
			'capability' => 'edit_theme_options',
			'type' => 'option',
			'transport' => 'postMessage',
			'sanitize_callback' => 'generate_premium_sanitize_empty_absint'
		)
	);

	$wp_customize->add_control(
		'generate_blog_settings[post_image_height]',
		array(
			'type' => 'number',
			'label' => __( 'Height', 'generate-blog' ),
			'section' => 'generate_blog_section',
			'input_attrs' => array(
				'style' => 'text-align:center;',
				'placeholder' => __('Auto','generate-blog'),
				'min' => 5,
			),
			'settings' => 'generate_blog_settings[post_image_height]',
			'active_callback' => 'generate_premium_featured_image_active'
		)
	);

	// Save dimensions
	$wp_customize->add_control(
		new GeneratePress_Refresh_Button_Customize_Control(
			$wp_customize,
			'post_image_apply_sizes',
			array(
				'section' => 'generate_blog_section',
				'label'	=> __( 'Apply sizes','generate-blog' ),
				'settings' => ( isset( $wp_customize->selective_refresh ) ) ? array() : 'blogname',
				'active_callback' => 'generate_premium_featured_image_active'
			)
		)
	);

	/*
	 * Single featured images
	 */

	$wp_customize->add_setting(
		'generate_blog_settings[single_post_image]',
		array(
			'default' => $defaults['single_post_image'],
			'type' => 'option',
			'sanitize_callback' => 'generate_premium_sanitize_checkbox'
		)
	);

	$wp_customize->add_control(
		'generate_blog_settings[single_post_image]',
		array(
			'type' => 'checkbox',
			'label' => __( 'Display featured images', 'generate-blog' ),
			'section' => 'generate_blog_section',
			'settings' => 'generate_blog_settings[single_post_image]',
		)
	);

	// Padding
	$wp_customize->add_setting(
		'generate_blog_settings[single_post_image_padding]',
		array(
			'default' => $defaults['single_post_image_padding'],
			'type' => 'option',
			'sanitize_callback' => 'generate_premium_sanitize_checkbox'
		)
	);

	$wp_customize->add_control(
		'generate_blog_settings[single_post_image_padding]',
		array(
			'type' => 'checkbox',
			'label' => __( 'Display padding around images', 'generate-blog' ),
			'section' => 'generate_blog_section',
			'settings' => 'generate_blog_settings[single_post_image_padding]',
			'active_callback' => 'generate_premium_display_image_padding_single',
		)
	);

	// Location
	$wp_customize->add_setting(
		'generate_blog_settings[single_post_image_position]',
		array(
			'default' => $defaults['single_post_image_position'],
			'type' => 'option',
			'sanitize_callback' => 'generate_premium_sanitize_choices'
		)
	);

	$wp_customize->add_control(
		'generate_blog_settings[single_post_image_position]',
		array(
			'type' => 'select',
			'label' => __( 'Location', 'generate-blog' ),
			'section' => 'generate_blog_section',
			'choices' => array(
				'below-title' => __( 'Below Title', 'generate-blog' ),
				'inside-content' => __( 'Above Title', 'generate-blog' ),
				'above-content' => __( 'Above Content Area', 'generate-blog' ),
			),
			'settings' => 'generate_blog_settings[single_post_image_position]',
			'active_callback' => 'generate_premium_single_featured_image_active'
		)
	);

	// Alignment
	$wp_customize->add_setting(
		'generate_blog_settings[single_post_image_alignment]',
		array(
			'default' => $defaults['single_post_image_alignment'],
			'type' => 'option',
			'sanitize_callback' => 'generate_premium_sanitize_choices'
		)
	);

	$wp_customize->add_control(
		'generate_blog_settings[single_post_image_alignment]',
		array(
			'type' => 'select',
			'label' => __( 'Alignment', 'generate-blog' ),
			'section' => 'generate_blog_section',
			'choices' => array(
				'center' => __( 'Center', 'generate-blog' ),
				'left' => __( 'Left', 'generate-blog' ),
				'right' => __( 'Right', 'generate-blog' ),
			),
			'settings' => 'generate_blog_settings[single_post_image_alignment]',
			'active_callback' => 'generate_premium_single_featured_image_active'
		)
	);

	// Width
	$wp_customize->add_setting(
		'generate_blog_settings[single_post_image_width]', array(
			'default' => $defaults['single_post_image_width'],
			'capability' => 'edit_theme_options',
			'type' => 'option',
			'transport' => 'postMessage',
			'sanitize_callback' => 'generate_premium_sanitize_empty_absint'
		)
	);

	$wp_customize->add_control(
		'generate_blog_settings[single_post_image_width]',
		array(
			'type' => 'number',
			'label' => __( 'Width', 'generate-blog' ),
			'section' => 'generate_blog_section',
			'input_attrs' => array(
				'style' => 'text-align:center;',
				'placeholder' => __('Auto','generate-blog'),
				'min' => 5,
			),
			'settings' => 'generate_blog_settings[single_post_image_width]',
			'active_callback' => 'generate_premium_single_featured_image_active'
		)
	);

	// Height
	$wp_customize->add_setting(
		'generate_blog_settings[single_post_image_height]', array(
			'default' => $defaults['single_post_image_height'],
			'capability' => 'edit_theme_options',
			'type' => 'option',
			'transport' => 'postMessage',
			'sanitize_callback' => 'generate_premium_sanitize_empty_absint'
		)
	);

	$wp_customize->add_control(
		'generate_blog_settings[single_post_image_height]',
		array(
			'type' => 'number',
			'label' => __( 'Height', 'generate-blog' ),
			'section' => 'generate_blog_section',
			'input_attrs' => array(
				'style' => 'text-align:center;',
				'placeholder' => __('Auto','generate-blog'),
				'min' => 5,
			),
			'settings' => 'generate_blog_settings[single_post_image_height]',
			'active_callback' => 'generate_premium_single_featured_image_active'
		)
	);

	// Save dimensions
	$wp_customize->add_control(
		new GeneratePress_Refresh_Button_Customize_Control(
			$wp_customize,
			'single_post_image_apply_sizes',
			array(
				'section' => 'generate_blog_section',
				'label'	=> __( 'Apply sizes','generate-blog' ),
				'settings' => ( isset( $wp_customize->selective_refresh ) ) ? array() : 'blogname',
				'active_callback' => 'generate_premium_single_featured_image_active'
			)
		)
	);

	/*
	 * Page featured images
	 */

	$wp_customize->add_setting(
		'generate_blog_settings[page_post_image]',
		array(
			'default' => $defaults['page_post_image'],
			'type' => 'option',
			'sanitize_callback' => 'generate_premium_sanitize_checkbox'
		)
	);

	$wp_customize->add_control(
		'generate_blog_settings[page_post_image]',
		array(
			'type' => 'checkbox',
			'label' => __( 'Display featured images', 'generate-blog' ),
			'section' => 'generate_blog_section',
			'settings' => 'generate_blog_settings[page_post_image]',
		)
	);

	// Padding
	$wp_customize->add_setting(
		'generate_blog_settings[page_post_image_padding]',
		array(
			'default' => $defaults['page_post_image_padding'],
			'type' => 'option',
			'sanitize_callback' => 'generate_premium_sanitize_checkbox'
		)
	);

	$wp_customize->add_control(
		'generate_blog_settings[page_post_image_padding]',
		array(
			'type' => 'checkbox',
			'label' => __( 'Display padding around images', 'generate-blog' ),
			'section' => 'generate_blog_section',
			'settings' => 'generate_blog_settings[page_post_image_padding]',
			'active_callback' => 'generate_premium_display_image_padding_single_page',
		)
	);

	// Location
	$wp_customize->add_setting(
		'generate_blog_settings[page_post_image_position]',
		array(
			'default' => $defaults['page_post_image_position'],
			'type' => 'option',
			'sanitize_callback' => 'generate_premium_sanitize_choices'
		)
	);

	$wp_customize->add_control(
		'generate_blog_settings[page_post_image_position]',
		array(
			'type' => 'select',
			'label' => __( 'Location', 'generate-blog' ),
			'section' => 'generate_blog_section',
			'choices' => array(
				'below-title' => __( 'Below Title', 'generate-blog' ),
				'inside-content' => __( 'Above Title', 'generate-blog' ),
				'above-content' => __( 'Above Content Area', 'generate-blog' ),
			),
			'settings' => 'generate_blog_settings[page_post_image_position]',
			'active_callback' => 'generate_premium_single_page_featured_image_active'
		)
	);

	// Alignment
	$wp_customize->add_setting(
		'generate_blog_settings[page_post_image_alignment]',
		array(
			'default' => $defaults['page_post_image_alignment'],
			'type' => 'option',
			'sanitize_callback' => 'generate_premium_sanitize_choices'
		)
	);

	$wp_customize->add_control(
		'generate_blog_settings[page_post_image_alignment]',
		array(
			'type' => 'select',
			'label' => __( 'Alignment', 'generate-blog' ),
			'section' => 'generate_blog_section',
			'choices' => array(
				'center' => __( 'Center', 'generate-blog' ),
				'left' => __( 'Left', 'generate-blog' ),
				'right' => __( 'Right', 'generate-blog' ),
			),
			'settings' => 'generate_blog_settings[page_post_image_alignment]',
			'active_callback' => 'generate_premium_single_page_featured_image_active'
		)
	);

	// Width
	$wp_customize->add_setting(
		'generate_blog_settings[page_post_image_width]', array(
			'default' => $defaults['page_post_image_width'],
			'capability' => 'edit_theme_options',
			'type' => 'option',
			'transport' => 'postMessage',
			'sanitize_callback' => 'generate_premium_sanitize_empty_absint'
		)
	);

	$wp_customize->add_control(
		'generate_blog_settings[page_post_image_width]',
		array(
			'type' => 'number',
			'label' => __( 'Width', 'generate-blog' ),
			'section' => 'generate_blog_section',
			'input_attrs' => array(
				'style' => 'text-align:center;',
				'placeholder' => __('Auto','generate-blog'),
				'min' => 5,
			),
			'settings' => 'generate_blog_settings[page_post_image_width]',
			'active_callback' => 'generate_premium_single_page_featured_image_active'
		)
	);

	// Height
	$wp_customize->add_setting(
		'generate_blog_settings[page_post_image_height]', array(
			'default' => $defaults['page_post_image_height'],
			'capability' => 'edit_theme_options',
			'type' => 'option',
			'transport' => 'postMessage',
			'sanitize_callback' => 'generate_premium_sanitize_empty_absint'
		)
	);

	$wp_customize->add_control(
		'generate_blog_settings[page_post_image_height]',
		array(
			'type' => 'number',
			'label' => __( 'Height', 'generate-blog' ),
			'section' => 'generate_blog_section',
			'input_attrs' => array(
				'style' => 'text-align:center;',
				'placeholder' => __( 'Auto', 'generate-blog' ),
				'min' => 5,
			),
			'settings' => 'generate_blog_settings[page_post_image_height]',
			'active_callback' => 'generate_premium_single_page_featured_image_active'
		)
	);

	// Save dimensions
	$wp_customize->add_control(
		new GeneratePress_Refresh_Button_Customize_Control(
			$wp_customize,
			'page_post_image_apply_sizes',
			array(
				'section' => 'generate_blog_section',
				'label'	=> __( 'Apply sizes','generate-blog' ),
				'settings' => ( isset( $wp_customize->selective_refresh ) ) ? array() : 'blogname',
				'active_callback' => 'generate_premium_single_page_featured_image_active'
			)
		)
	);

	$wp_customize->add_control(
		new GeneratePress_Title_Customize_Control(
			$wp_customize,
			'generate_blog_columns_title',
			array(
				'section'     => 'generate_blog_section',
				'type'        => 'generatepress-customizer-title',
				'title'			=> __( 'Columns', 'generate-blog' ),
				'settings' => ( isset( $wp_customize->selective_refresh ) ) ? array() : 'blogname',
			)
		)
	);

	// Enable columns
	$wp_customize->add_setting(
		'generate_blog_settings[column_layout]',
		array(
			'default' => $defaults['column_layout'],
			'type' => 'option',
			'sanitize_callback' => 'generate_premium_sanitize_checkbox'
		)
	);

	$wp_customize->add_control(
		'generate_blog_settings[column_layout]',
		array(
			'type' => 'checkbox',
			'label' => __( 'Display posts in columns', 'generate-blog' ),
			'section' => 'generate_blog_section',
			'settings' => 'generate_blog_settings[column_layout]',
		)
	);

	// Column count class
	$wp_customize->add_setting(
		'generate_blog_settings[columns]',
		array(
			'default' => $defaults['columns'],
			'type' => 'option',
			'sanitize_callback' => 'generate_premium_sanitize_choices'
		)
	);

	$wp_customize->add_control(
		'generate_blog_settings[columns]',
		array(
			'type' => 'select',
			'label' => __( 'Columns', 'generate-blog' ),
			'section' => 'generate_blog_section',
			'choices' => array(
				'50' => '2',
				'33' => '3',
				'25' => '4',
				'20' => '5'
			),
			'settings' => 'generate_blog_settings[columns]',
			'active_callback' => 'generate_premium_blog_columns_active'
		)
	);

	// Featured column
	$wp_customize->add_setting(
		'generate_blog_settings[featured_column]',
		array(
			'default' => $defaults['featured_column'],
			'type' => 'option',
			'sanitize_callback' => 'generate_premium_sanitize_checkbox'
		)
	);

	$wp_customize->add_control(
		'generate_blog_settings[featured_column]',
		array(
			'type' => 'checkbox',
			'label' => __( 'Make first post featured', 'generate-blog' ),
			'section' => 'generate_blog_section',
			'settings' => 'generate_blog_settings[featured_column]',
			'active_callback' => 'generate_premium_blog_columns_active'
		)
	);

	// Masonry
	$wp_customize->add_setting(
		'generate_blog_settings[masonry]',
		array(
			'default' => $defaults['masonry'],
			'type' => 'option',
			'sanitize_callback' => 'generate_premium_sanitize_checkbox'
		)
	);

	$wp_customize->add_control(
		'generate_blog_settings[masonry]',
		array(
			'type' => 'checkbox',
			'label' => __( 'Display posts in masonry grid', 'generate-blog' ),
			'section' => 'generate_blog_section',
			'settings' => 'generate_blog_settings[masonry]',
			'active_callback' => 'generate_premium_blog_columns_active'
		)
	);
}
endif;

add_action( 'customize_controls_print_styles', 'generate_blog_customizer_controls_css' );
function generate_blog_customizer_controls_css() {
	?>
	<style>
		#customize-control-generate_blog_settings-post_image_width:not([style*="display: none;"]),
		#customize-control-generate_blog_settings-post_image_height:not([style*="display: none;"]),
		#customize-control-post_image_apply_sizes:not([style*="display: none;"]),
		#customize-control-generate_blog_settings-single_post_image_width:not([style*="display: none;"]),
		#customize-control-generate_blog_settings-single_post_image_height:not([style*="display: none;"]),
		#customize-control-single_post_image_apply_sizes:not([style*="display: none;"]),
		#customize-control-generate_blog_settings-page_post_image_width:not([style*="display: none;"]),
		#customize-control-generate_blog_settings-page_post_image_height:not([style*="display: none;"]),
		#customize-control-page_post_image_apply_sizes:not([style*="display: none;"]) {
			display: inline-block !important;
			width: 30%;
			clear: none;
			text-align: center;
			vertical-align: bottom;
			float: none;
		}

		#customize-control-post_image_apply_sizes,
		#customize-control-single_post_image_apply_sizes,
		#customize-control-page_post_image_apply_sizes {
			margin-left: 1%;
		}

		#customize-control-generate_blog_settings-post_image_width input {
			border-right: 0;
		}

		#customize-control-generate_blog_settings-post_image_width .customize-control-title:after,
		#customize-control-generate_blog_settings-post_image_height .customize-control-title:after,
		#customize-control-generate_blog_settings-single_post_image_width .customize-control-title:after,
		#customize-control-generate_blog_settings-single_post_image_height .customize-control-title:after,
		#customize-control-generate_blog_settings-page_post_image_width .customize-control-title:after,
		#customize-control-generate_blog_settings-page_post_image_height .customize-control-title:after {
			content: "px";
			width: 22px;
			display: inline-block;
			background: #FFF;
			height: 18px;
			border: 1px solid #DDD;
			text-align: center;
			text-transform: uppercase;
			font-size: 10px;
			line-height: 18px;
			margin-left: 5px
		}
	</style>
	<?php
}

add_action( 'customize_controls_enqueue_scripts', 'generate_blog_customizer_control_scripts' );
function generate_blog_customizer_control_scripts() {
	wp_enqueue_script( 'generate-blog-customizer-control-scripts', trailingslashit( plugin_dir_url( __FILE__ ) ) . 'js/controls.js', array( 'jquery','customize-controls' ), GENERATE_BLOG_VERSION, true );
}

if ( ! function_exists( 'generate_blog_customizer_live_preview' ) ) :
/**
 * Add our live preview javascript
 */
add_action( 'customize_preview_init', 'generate_blog_customizer_live_preview' );
function generate_blog_customizer_live_preview() {
	wp_enqueue_script(
		'generate-blog-themecustomizer',
		trailingslashit( plugin_dir_url( __FILE__ ) ) . 'js/customizer.js',
		array( 'jquery', 'customize-preview', 'generate-blog' ),
		GENERATE_BLOG_VERSION,
		true
	);
}
endif;
