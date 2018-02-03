<?php
// No direct access, please
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'customize_controls_enqueue_scripts', 'generatepress_woocommerce_customizer_scripts' );
function generatepress_woocommerce_customizer_scripts()
{
	wp_enqueue_script( 'generate-wc-customizer', trailingslashit( plugin_dir_url( __FILE__ ) ) . 'js/customizer.js', array( 'jquery','customize-controls' ), GENERATE_WOOCOMMERCE_VERSION, true );
}

add_action( 'customize_preview_init', 'generatepress_wc_live_preview_scripts', 20 );
function generatepress_wc_live_preview_scripts()
{
	wp_enqueue_script( 'generate-wc-colors-customizer' );
}

/**
 * Add postMessage support for site title and description for the Theme Customizer.
 *
 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
 */
add_action( 'customize_register', 'generatepress_woocommerce_customize_register' );
function generatepress_woocommerce_customize_register( $wp_customize ) {

	// Defaults
	$defaults = generatepress_wc_defaults();
	
	// Controls
	require_once GP_LIBRARY_DIRECTORY . 'customizer-helpers.php';
	
	// Add control types so controls can be built using JS
	if ( method_exists( $wp_customize, 'register_control_type' ) ) {
		$wp_customize->register_control_type( 'GeneratePress_Title_Customize_Control' );
		$wp_customize->register_control_type( 'GeneratePress_Pro_Range_Slider_Control' );
		$wp_customize->register_control_type( 'GeneratePress_Information_Customize_Control' );
	}

	$wp_customize->add_section(
		'generate_woocommerce_layout',
		array(
			'title' => __( 'WooCommerce', 'generate-woocommerce' ),
			'capability' => 'edit_theme_options',
			'priority' => 100,
			'panel' => 'generate_layout_panel'
		)
	);
	
	$wp_customize->add_control(
		new GeneratePress_Title_Customize_Control(
			$wp_customize,
			'generate_woocommerce_general_title',
			array(
				'section'     => 'generate_woocommerce_layout',
				'type'        => 'generatepress-customizer-title',
				'title'			=> __( 'General','generate-woocommerce' ),
				'settings' => ( isset( $wp_customize->selective_refresh ) ) ? array() : 'blogname'
			)
		)
	);
	
	$wp_customize->add_setting(
		'generate_woocommerce_settings[cart_menu_item]',
		array(
			'default' => $defaults['cart_menu_item'],
			'type' => 'option',
			'sanitize_callback' => 'generate_premium_sanitize_checkbox'
		)
	);

	$wp_customize->add_control(
		'generate_woocommerce_settings[cart_menu_item]',
		array(
			'type' => 'checkbox',
			'label' => __( 'Display cart in menu', 'generate-woocommerce' ),
			'section' => 'generate_woocommerce_layout',
			'settings' => 'generate_woocommerce_settings[cart_menu_item]',
		)
	);
	
	$wp_customize->add_setting(
		'generate_woocommerce_settings[breadcrumbs]',
		array(
			'default' => $defaults['breadcrumbs'],
			'type' => 'option',
			'sanitize_callback' => 'generate_premium_sanitize_checkbox'
		)
	);

	$wp_customize->add_control(
		'generate_woocommerce_settings[breadcrumbs]',
		array(
			'type' => 'checkbox',
			'label' => __( 'Display breadcrumbs', 'generate-woocommerce' ),
			'section' => 'generate_woocommerce_layout',
			'settings' => 'generate_woocommerce_settings[breadcrumbs]',
		)
	);
	
	$wp_customize->add_control(
		new GeneratePress_Title_Customize_Control(
			$wp_customize,
			'generate_woocommerce_shop_page_title',
			array(
				'section'     => 'generate_woocommerce_layout',
				'type'        => 'generatepress-customizer-title',
				'title'			=> __( 'Shop','generate-woocommerce' ),
				'settings' => ( isset( $wp_customize->selective_refresh ) ) ? array() : 'blogname'
			)
		)
	);
	
	$wp_customize->add_setting(
		'generate_woocommerce_settings[sidebar_layout]',
		array(
			'default' => $defaults['sidebar_layout'],
			'type' => 'option',
			'sanitize_callback' => 'generate_premium_sanitize_choices'
		)
	);
	
	$wp_customize->add_control(
		'generate_woocommerce_settings[sidebar_layout]',
		array(
			'type' => 'select',
			'label' => __( 'Sidebar Layout', 'generate-woocommerce' ),
			'section' => 'generate_woocommerce_layout',
			'choices' => array(
				'left-sidebar' => __( 'Sidebar / Content', 'generate-woocommerce' ),
				'right-sidebar' => __( 'Content / Sidebar', 'generate-woocommerce' ),
				'no-sidebar' => __( 'Content (no sidebars)', 'generate-woocommerce' ),
				'both-sidebars' => __( 'Sidebar / Content / Sidebar', 'generate-woocommerce' ),
				'both-left' => __( 'Sidebar / Sidebar / Content', 'generate-woocommerce' ),
				'both-right' => __( 'Content / Sidebar / Sidebar', 'generate-woocommerce' )
			),
			'settings' => 'generate_woocommerce_settings[sidebar_layout]',
		)
	);
	
	$wp_customize->add_setting(
		'generate_woocommerce_settings[products_per_page]',
		array(
			'default' => $defaults['products_per_page'],
			'type' => 'option',
			'sanitize_callback' => 'absint'
		)
	);
	
	$wp_customize->add_control(
		'generate_woocommerce_settings[products_per_page]',
		array(
			'type' => 'text',
			'label' => __( 'Products Per Page', 'generate-woocommerce' ),
			'section' => 'generate_woocommerce_layout',
			'settings' => 'generate_woocommerce_settings[products_per_page]',
		)
	);
	
	$wp_customize->add_setting(
		'generate_woocommerce_settings[columns]', array(
			'default' => $defaults['columns'],
			'type' => 'option', 
			'capability' => 'edit_theme_options',
			'sanitize_callback' => 'absint'
		)
	);
	
	$wp_customize->add_setting(
		'generate_woocommerce_settings[tablet_columns]', array(
			'default' => $defaults['tablet_columns'],
			'type' => 'option', 
			'capability' => 'edit_theme_options',
			'sanitize_callback' => 'absint'
		)
	);
	
	$wp_customize->add_setting(
		'generate_woocommerce_settings[mobile_columns]', array(
			'default' => $defaults['mobile_columns'],
			'type' => 'option', 
			'capability' => 'edit_theme_options',
			'sanitize_callback' => 'absint'
		)
	);

	$wp_customize->add_control(
		new GeneratePress_Pro_Range_Slider_Control(
			$wp_customize,
			'gp_woocommerce_columns', 
			array(
				'label' => __( 'Product Columns', 'generate-woocommerce' ), 
				'section' => 'generate_woocommerce_layout',
				'settings' => array( 
					'desktop' => 'generate_woocommerce_settings[columns]',
					//'tablet' => 'generate_woocommerce_settings[tablet_columns]',
					'mobile' => 'generate_woocommerce_settings[mobile_columns]',
				),
				'choices' => array(
					'desktop' => array(
						'min' => 1,
						'max' => 6,
						'step' => 1,
						'edit' => false,
						'unit' => 'Col',
					),
					// 'tablet' => array(
						// 'min' => 1,
						// 'max' => 4,
						// 'step' => 1,
						// 'edit' => false,
						// 'unit' => '',
					// ),
					'mobile' => array(
						'min' => 1,
						'max' => 3,
						'step' => 1,
						'edit' => false,
						'unit' => 'Col',
					),
				),
			)
		)
	);
	
	$wp_customize->add_setting(
		'generate_woocommerce_settings[product_archive_image_alignment]',
		array(
			'default' => $defaults['product_archive_image_alignment'],
			'type' => 'option',
			'sanitize_callback' => 'generate_premium_sanitize_choices'
		)
	);

	$wp_customize->add_control(
		'generate_woocommerce_settings[product_archive_image_alignment]',
		array(
			'type' => 'radio',
			'label' => __( 'Image Alignment', 'generate-woocommerce' ),
			'section' => 'generate_woocommerce_layout',
			'choices' => array(
				'left' => __( 'Left', 'generate-woocommerce' ),
				'center' => __( 'Center', 'generate-woocommerce' ),
				'right' => __( 'Right', 'generate-woocommerce' ),
			),
			'settings' => 'generate_woocommerce_settings[product_archive_image_alignment]',
		)
	);

	$wp_customize->add_setting(
		'generate_woocommerce_settings[products_per_page]',
		array(
			'default' => $defaults['products_per_page'],
			'type' => 'option',
			'sanitize_callback' => 'absint'
		)
	);

	$wp_customize->add_control(
		'generate_woocommerce_settings[products_per_page]',
		array(
			'type' => 'text',
			'label' => __( 'Products Per Page', 'generate-woocommerce' ),
			'section' => 'generate_woocommerce_layout',
			'settings' => 'generate_woocommerce_settings[products_per_page]',
		)
	);
	
	$wp_customize->add_setting(
		'generate_woocommerce_settings[product_archive_alignment]',
		array(
			'default' => $defaults['product_archive_alignment'],
			'type' => 'option',
			'sanitize_callback' => 'generate_premium_sanitize_choices'
		)
	);

	$wp_customize->add_control(
		'generate_woocommerce_settings[product_archive_alignment]',
		array(
			'type' => 'radio',
			'label' => __( 'Text Alignment', 'generate-woocommerce' ),
			'section' => 'generate_woocommerce_layout',
			'choices' => array(
				'left' => __( 'Left', 'generate-woocommerce' ),
				'center' => __( 'Center', 'generate-woocommerce' ),
				'right' => __( 'Right', 'generate-woocommerce' ),
			),
			'settings' => 'generate_woocommerce_settings[product_archive_alignment]',
		)
	);
	
	$wp_customize->add_setting(
		'generate_woocommerce_settings[shop_page_title]',
		array(
			'default' => $defaults['shop_page_title'],
			'type' => 'option',
			'sanitize_callback' => 'generate_premium_sanitize_checkbox'
		)
	);

	$wp_customize->add_control(
		'generate_woocommerce_settings[shop_page_title]',
		array(
			'type' => 'checkbox',
			'label' => __( 'Display page title', 'generate-woocommerce' ),
			'section' => 'generate_woocommerce_layout',
			'settings' => 'generate_woocommerce_settings[shop_page_title]',
		)
	);
	
	$wp_customize->add_setting(
		'generate_woocommerce_settings[product_results_count]',
		array(
			'default' => $defaults['product_results_count'],
			'type' => 'option',
			'sanitize_callback' => 'generate_premium_sanitize_checkbox'
		)
	);

	$wp_customize->add_control(
		'generate_woocommerce_settings[product_results_count]',
		array(
			'type' => 'checkbox',
			'label' => __( 'Display product results count', 'generate-woocommerce' ),
			'section' => 'generate_woocommerce_layout',
			'settings' => 'generate_woocommerce_settings[product_results_count]',
		)
	);
	
	$wp_customize->add_setting(
		'generate_woocommerce_settings[product_sorting]',
		array(
			'default' => $defaults['product_sorting'],
			'type' => 'option',
			'sanitize_callback' => 'generate_premium_sanitize_checkbox'
		)
	);

	$wp_customize->add_control(
		'generate_woocommerce_settings[product_sorting]',
		array(
			'type' => 'checkbox',
			'label' => __( 'Display product sorting', 'generate-woocommerce' ),
			'section' => 'generate_woocommerce_layout',
			'settings' => 'generate_woocommerce_settings[product_sorting]',
		)
	);
	
	$wp_customize->add_setting(
		'generate_woocommerce_settings[product_archive_image]',
		array(
			'default' => $defaults['product_archive_image'],
			'type' => 'option',
			'sanitize_callback' => 'generate_premium_sanitize_checkbox'
		)
	);

	$wp_customize->add_control(
		'generate_woocommerce_settings[product_archive_image]',
		array(
			'type' => 'checkbox',
			'label' => __( 'Display product image', 'generate-woocommerce' ),
			'section' => 'generate_woocommerce_layout',
			'settings' => 'generate_woocommerce_settings[product_archive_image]',
		)
	);
	
	$wp_customize->add_setting(
		'generate_woocommerce_settings[product_secondary_image]',
		array(
			'default' => $defaults['product_secondary_image'],
			'type' => 'option',
			'sanitize_callback' => 'generate_premium_sanitize_checkbox'
		)
	);

	$wp_customize->add_control(
		'generate_woocommerce_settings[product_secondary_image]',
		array(
			'type' => 'checkbox',
			'label' => __( 'Display secondary image on hover', 'generate-woocommerce' ),
			'section' => 'generate_woocommerce_layout',
			'settings' => 'generate_woocommerce_settings[product_secondary_image]',
		)
	);
	
	$wp_customize->add_setting(
		'generate_woocommerce_settings[product_archive_title]',
		array(
			'default' => $defaults['product_archive_title'],
			'type' => 'option',
			'sanitize_callback' => 'generate_premium_sanitize_checkbox'
		)
	);

	$wp_customize->add_control(
		'generate_woocommerce_settings[product_archive_title]',
		array(
			'type' => 'checkbox',
			'label' => __( 'Display product title', 'generate-woocommerce' ),
			'section' => 'generate_woocommerce_layout',
			'settings' => 'generate_woocommerce_settings[product_archive_title]',
		)
	);
	
	$wp_customize->add_setting(
		'generate_woocommerce_settings[product_archive_sale_flash]',
		array(
			'default' => $defaults['product_archive_sale_flash'],
			'type' => 'option',
			'sanitize_callback' => 'generate_premium_sanitize_checkbox'
		)
	);

	$wp_customize->add_control(
		'generate_woocommerce_settings[product_archive_sale_flash]',
		array(
			'type' => 'checkbox',
			'label' => __( 'Display sale flash', 'generate-woocommerce' ),
			'section' => 'generate_woocommerce_layout',
			'settings' => 'generate_woocommerce_settings[product_archive_sale_flash]',
		)
	);
	
	$wp_customize->add_setting(
		'generate_woocommerce_settings[product_archive_sale_flash_overlay]',
		array(
			'default' => $defaults['product_archive_sale_flash_overlay'],
			'type' => 'option',
			'sanitize_callback' => 'generate_premium_sanitize_checkbox'
		)
	);

	$wp_customize->add_control(
		'generate_woocommerce_settings[product_archive_sale_flash_overlay]',
		array(
			'type' => 'checkbox',
			'label' => __( 'Sale flash over image', 'generate-woocommerce' ),
			'section' => 'generate_woocommerce_layout',
			'settings' => 'generate_woocommerce_settings[product_archive_sale_flash_overlay]',
		)
	);
	
	$wp_customize->add_setting(
		'generate_woocommerce_settings[product_archive_rating]',
		array(
			'default' => $defaults['product_archive_rating'],
			'type' => 'option',
			'sanitize_callback' => 'generate_premium_sanitize_checkbox'
		)
	);

	$wp_customize->add_control(
		'generate_woocommerce_settings[product_archive_rating]',
		array(
			'type' => 'checkbox',
			'label' => __( 'Display rating', 'generate-woocommerce' ),
			'section' => 'generate_woocommerce_layout',
			'settings' => 'generate_woocommerce_settings[product_archive_rating]',
		)
	);
	
	$wp_customize->add_setting(
		'generate_woocommerce_settings[product_archive_price]',
		array(
			'default' => $defaults['product_archive_price'],
			'type' => 'option',
			'sanitize_callback' => 'generate_premium_sanitize_checkbox'
		)
	);

	$wp_customize->add_control(
		'generate_woocommerce_settings[product_archive_price]',
		array(
			'type' => 'checkbox',
			'label' => __( 'Display price', 'generate-woocommerce' ),
			'section' => 'generate_woocommerce_layout',
			'settings' => 'generate_woocommerce_settings[product_archive_price]',
		)
	);
	
	$wp_customize->add_setting(
		'generate_woocommerce_settings[product_archive_add_to_cart]',
		array(
			'default' => $defaults['product_archive_add_to_cart'],
			'type' => 'option',
			'sanitize_callback' => 'generate_premium_sanitize_checkbox'
		)
	);

	$wp_customize->add_control(
		'generate_woocommerce_settings[product_archive_add_to_cart]',
		array(
			'type' => 'checkbox',
			'label' => __( 'Display add to cart button', 'generate-woocommerce' ),
			'section' => 'generate_woocommerce_layout',
			'settings' => 'generate_woocommerce_settings[product_archive_add_to_cart]',
		)
	);
	
	$wp_customize->add_setting(
		'generate_woocommerce_settings[product_archive_description]',
		array(
			'default' => $defaults['product_archive_description'],
			'type' => 'option',
			'sanitize_callback' => 'generate_premium_sanitize_checkbox'
		)
	);

	$wp_customize->add_control(
		'generate_woocommerce_settings[product_archive_description]',
		array(
			'type' => 'checkbox',
			'label' => __( 'Display short description', 'generate-woocommerce' ),
			'section' => 'generate_woocommerce_layout',
			'settings' => 'generate_woocommerce_settings[product_archive_description]',
		)
	);
	
	$wp_customize->add_control(
		new GeneratePress_Title_Customize_Control(
			$wp_customize,
			'generate_woocommerce_single_product_title',
			array(
				'section'     => 'generate_woocommerce_layout',
				'type'        => 'generatepress-customizer-title',
				'title'			=> __( 'Single Product','generate-woocommerce' ),
				'settings' => ( isset( $wp_customize->selective_refresh ) ) ? array() : 'blogname'
			)
		)
	);
	
	$wp_customize->add_setting(
		'generate_woocommerce_settings[single_sidebar_layout]',
		array(
			'default' => $defaults['single_sidebar_layout'],
			'type' => 'option',
			'sanitize_callback' => 'generate_premium_sanitize_choices'
		)
	);
	
	$wp_customize->add_control(
		'generate_woocommerce_settings[single_sidebar_layout]',
		array(
			'type' => 'select',
			'label' => __( 'Sidebar Layout', 'generate-woocommerce' ),
			'section' => 'generate_woocommerce_layout',
			'choices' => array(
				'inherit' => __( 'inherit','generate-woocommerce' ),
				'left-sidebar' => __( 'Sidebar / Content', 'generate-woocommerce' ),
				'right-sidebar' => __( 'Content / Sidebar', 'generate-woocommerce' ),
				'no-sidebar' => __( 'Content (no sidebars)', 'generate-woocommerce' ),
				'both-sidebars' => __( 'Sidebar / Content / Sidebar', 'generate-woocommerce' ),
				'both-left' => __( 'Sidebar / Sidebar / Content', 'generate-woocommerce' ),
				'both-right' => __( 'Content / Sidebar / Sidebar', 'generate-woocommerce' )
			),
			'settings' => 'generate_woocommerce_settings[single_sidebar_layout]',
		)
	);
	
	$wp_customize->add_setting(
		'generate_woocommerce_settings[product_tabs]',
		array(
			'default' => $defaults['product_tabs'],
			'type' => 'option',
			'sanitize_callback' => 'generate_premium_sanitize_checkbox'
		)
	);

	$wp_customize->add_control(
		'generate_woocommerce_settings[product_tabs]',
		array(
			'type' => 'checkbox',
			'label' => __( 'Display product tabs', 'generate-woocommerce' ),
			'section' => 'generate_woocommerce_layout',
			'settings' => 'generate_woocommerce_settings[product_tabs]',
		)
	);
	
	$wp_customize->add_setting(
		'generate_woocommerce_settings[product_related]',
		array(
			'default' => $defaults['product_related'],
			'type' => 'option',
			'sanitize_callback' => 'generate_premium_sanitize_checkbox'
		)
	);

	$wp_customize->add_control(
		'generate_woocommerce_settings[product_related]',
		array(
			'type' => 'checkbox',
			'label' => __( 'Display related products', 'generate-woocommerce' ),
			'section' => 'generate_woocommerce_layout',
			'settings' => 'generate_woocommerce_settings[product_related]',
		)
	);
	
	$wp_customize->add_setting(
		'generate_woocommerce_settings[product_upsells]',
		array(
			'default' => $defaults['product_upsells'],
			'type' => 'option',
			'sanitize_callback' => 'generate_premium_sanitize_checkbox'
		)
	);

	$wp_customize->add_control(
		'generate_woocommerce_settings[product_upsells]',
		array(
			'type' => 'checkbox',
			'label' => __( 'Display upsell products', 'generate-woocommerce' ),
			'section' => 'generate_woocommerce_layout',
			'settings' => 'generate_woocommerce_settings[product_upsells]',
		)
	);
	
	$wp_customize->add_setting(
		'generate_woocommerce_settings[related_upsell_columns]', array(
			'default' => $defaults['related_upsell_columns'],
			'type' => 'option', 
			'capability' => 'edit_theme_options',
			'sanitize_callback' => 'absint'
		)
	);
	
	$wp_customize->add_setting(
		'generate_woocommerce_settings[mobile_related_upsell_columns]', array(
			'default' => $defaults['mobile_related_upsell_columns'],
			'type' => 'option', 
			'capability' => 'edit_theme_options',
			'sanitize_callback' => 'absint'
		)
	);

	$wp_customize->add_control(
		new GeneratePress_Pro_Range_Slider_Control(
			$wp_customize,
			'gp_woocommerce_related_upsell_columns', 
			array(
				'label' => __( 'Related/Upsell Columns', 'generate-woocommerce' ), 
				'section' => 'generate_woocommerce_layout',
				'settings' => array( 
					'desktop' => 'generate_woocommerce_settings[related_upsell_columns]',
					'mobile' => 'generate_woocommerce_settings[mobile_related_upsell_columns]',
				),
				'choices' => array(
					'desktop' => array(
						'min' => 1,
						'max' => 6,
						'step' => 1,
						'edit' => false,
						'unit' => 'Col',
					),
					'mobile' => array(
						'min' => 1,
						'max' => 3,
						'step' => 1,
						'edit' => false,
						'unit' => 'Col',
					),
				),
			)
		)
	);
	
	$wp_customize->add_setting(
		'generate_woocommerce_settings[product_meta]',
		array(
			'default' => $defaults['product_meta'],
			'type' => 'option',
			'sanitize_callback' => 'generate_premium_sanitize_checkbox'
		)
	);

	$wp_customize->add_control(
		'generate_woocommerce_settings[product_meta]',
		array(
			'type' => 'checkbox',
			'label' => __( 'Display product meta data', 'generate-woocommerce' ),
			'section' => 'generate_woocommerce_layout',
			'settings' => 'generate_woocommerce_settings[product_meta]',
		)
	);
	
	$wp_customize->add_setting(
		'generate_woocommerce_settings[product_description]',
		array(
			'default' => $defaults['product_description'],
			'type' => 'option',
			'sanitize_callback' => 'generate_premium_sanitize_checkbox'
		)
	);

	$wp_customize->add_control(
		'generate_woocommerce_settings[product_description]',
		array(
			'type' => 'checkbox',
			'label' => __( 'Display short description', 'generate-woocommerce' ),
			'section' => 'generate_woocommerce_layout',
			'settings' => 'generate_woocommerce_settings[product_description]',
		)
	);
	
	$wp_customize->add_section(
		'generate_woocommerce_colors',
		array(
			'title' => __( 'WooCommerce', 'generate-woocommerce' ),
			'capability' => 'edit_theme_options',
			'priority' => 200,
			'panel' => 'generate_colors_panel'
		)
	);
	
	$wp_customize->add_section(
		'generate_woocommerce_typography',
		array(
			'title' => __( 'WooCommerce', 'generate-woocommerce' ),
			'capability' => 'edit_theme_options',
			'priority' => 200,
			'panel' => 'generate_typography_panel'
		)
	);
	
	$wp_customize->add_control(
		new GeneratePress_Information_Customize_Control(
			$wp_customize,
			'generate_woocommerce_primary_button_message',
			array(
				'section'     => 'generate_woocommerce_colors',
				'label'			=> __( 'Primary button colors','generate-woocommerce' ),
				'description' => __( 'Primary button colors can be set <a href="#">here</a>.','generate-woocommerce' ),
				'settings' => ( isset( $wp_customize->selective_refresh ) ) ? array() : 'blogname',
				'priority' => 1,
			)
		)
	);
	
	$wp_customize->add_control(
		new GeneratePress_Title_Customize_Control(
			$wp_customize,
			'generate_woocommerce_checkout_title',
			array(
				'section'     => 'generate_woocommerce_layout',
				'type'        => 'generatepress-customizer-title',
				'title'			=> __( 'Checkout','generate-woocommerce' ),
				'settings' => ( isset( $wp_customize->selective_refresh ) ) ? array() : 'blogname'
			)
		)
	);
	
	$wp_customize->add_setting(
		'generate_woocommerce_settings[distraction_free]',
		array(
			'default' => $defaults['distraction_free'],
			'type' => 'option',
			'sanitize_callback' => 'generate_premium_sanitize_checkbox'
		)
	);

	$wp_customize->add_control(
		'generate_woocommerce_settings[distraction_free]',
		array(
			'type' => 'checkbox',
			'label' => __( 'Distraction-free mode', 'generate-woocommerce' ),
			'description' => __( 'Remove unnecessary distractions like sidebars, footer widgets and sticky menus.','generate-woocommerce' ),
			'section' => 'generate_woocommerce_layout',
			'settings' => 'generate_woocommerce_settings[distraction_free]',
		)
	);
	
}