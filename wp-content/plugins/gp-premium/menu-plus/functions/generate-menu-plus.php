<?php
// No direct access, please
if ( ! defined( 'ABSPATH' ) ) exit;

require plugin_dir_path( __FILE__ ) . 'deprecated.php';

if ( ! function_exists( 'generate_menu_plus_setup' ) ) :
/**
 * Register the slide-out menu
 */
add_action( 'after_setup_theme','generate_menu_plus_setup' );
function generate_menu_plus_setup()
{
	register_nav_menus( array(
		'slideout' => __( 'Slideout Menu','menu-plus' ),
	) );
}
endif;

if ( ! function_exists( 'generate_menu_plus_get_defaults' ) ) :
/**
 * Set default options
 */
function generate_menu_plus_get_defaults()
{
	$generate_menu_plus_get_defaults = array(
		'mobile_menu_label' => __( 'Menu','menu-plus' ),
		'sticky_menu' => 'false',
		'sticky_menu_effect' => 'fade',
		'sticky_menu_logo' => '',
		'sticky_menu_logo_position' => 'sticky-menu',
		'mobile_header' => 'disable',
		'mobile_header_logo' => '',
		'mobile_header_sticky' => 'disable',
		'slideout_menu' => 'false',
		'auto_hide_sticky' => false,
		'mobile_header_auto_hide_sticky' => false,
	);
	
	return apply_filters( 'generate_menu_plus_option_defaults', $generate_menu_plus_get_defaults );
}
endif;

if ( ! function_exists( 'generate_menu_plus_customize_register' ) ) :
/**
 * Initiate Customizer controls
 */
add_action( 'customize_register', 'generate_menu_plus_customize_register', 100 );
function generate_menu_plus_customize_register( $wp_customize )
{
	// Get our defaults
	$defaults = generate_menu_plus_get_defaults();
	
	// Get our Customizer helpers
	require_once GP_LIBRARY_DIRECTORY . 'customizer-helpers.php';
	
	// Add our old Menu Plus panel
	// This panel shouldn't display anymore but is left for back compat
	if ( class_exists( 'WP_Customize_Panel' ) ) :
		if ( ! $wp_customize->get_panel( 'generate_menu_plus' ) ) {
			$wp_customize->add_panel( 'generate_menu_plus', array(
				'priority'       => 50,
				'capability'     => 'edit_theme_options',
				'theme_supports' => '',
				'title'          => __( 'Menu Plus','menu-plus' ),
				'description'    => '',
			) );
		}
	endif;
	
	// Add our options to the Layout panel if it exists
	// The layout panel is in the free theme, so we have the fallback in case people haven't updated
	if ( $wp_customize->get_panel( 'generate_layout_panel' ) ) {
		$panel = 'generate_layout_panel';
		$navigation_section = 'generate_layout_navigation';
		$header_section = 'generate_layout_header';
		$sticky_menu_section = 'generate_layout_navigation';
	} else {
		$panel = 'generate_menu_plus';
		$navigation_section = 'menu_plus_section';
		$header_section = 'menu_plus_mobile_header';
		$sticky_menu_section = 'menu_plus_sticky_menu';
	}
	
	// Add Menu Plus section
	// This section shouldn't display anymore for the above reasons
	$wp_customize->add_section(
		'menu_plus_section',
		array(
			'title' => __( 'General Settings','menu-plus' ),
			'capability' => 'edit_theme_options',
			'panel' => 'generate_menu_plus'
		)
	);
	
	// Mobile menu label
	$wp_customize->add_setting(
		'generate_menu_plus_settings[mobile_menu_label]', 
		array(
			'default' => $defaults['mobile_menu_label'],
			'type' => 'option',
			'sanitize_callback' => 'wp_kses_post'
		)
	);
		 
	$wp_customize->add_control(
		'mobile_menu_label_control', array(
			'label' => __('Mobile Menu Label','menu-plus'),
			'section' => $navigation_section,
			'settings' => 'generate_menu_plus_settings[mobile_menu_label]'
		)
	);
	
	// Sticky menu section
	$wp_customize->add_section(
		'menu_plus_sticky_menu',
		array(
			'title' => __( 'Sticky Navigation','menu-plus' ),
			'capability' => 'edit_theme_options',
			'panel' => $panel,
			'priority' => 33
		)
	);
	
	// Sticky menu
	$wp_customize->add_setting(
		'generate_menu_plus_settings[sticky_menu]',
		array(
			'default' => $defaults['sticky_menu'],
			'type' => 'option',
			'sanitize_callback' => 'generate_premium_sanitize_choices'
		)
	);
	
	$wp_customize->add_control(
		'generate_menu_plus_settings[sticky_menu]',
		array(
			'type' => 'select',
			'label' => __( 'Sticky Navigation','menu-plus' ),
			'section' => 'menu_plus_sticky_menu',
			'choices' => array(
				'mobile' => __( 'Mobile only','menu-plus' ),
				'desktop' => __( 'Desktop only','menu-plus' ),
				'true' => __( 'Both','menu-plus' ),
				'false' => __( 'Disable','menu-plus' )
			),
			'settings' => 'generate_menu_plus_settings[sticky_menu]',
			'priority' => 105
		)
	);
	
	// Transition
	$wp_customize->add_setting(
		'generate_menu_plus_settings[sticky_menu_effect]',
		array(
			'default' => $defaults['sticky_menu_effect'],
			'type' => 'option',
			'sanitize_callback' => 'generate_premium_sanitize_choices'
		)
	);
	
	$wp_customize->add_control(
		'generate_menu_plus_settings[sticky_menu_effect]',
		array(
			'type' => 'select',
			'label' => __( 'Sticky Navigation Transition','menu-plus' ),
			'section' => 'menu_plus_sticky_menu',
			'choices' => array(
				'fade' => __( 'Fade','menu-plus' ),
				'slide' => __( 'Slide','menu-plus' ),
				'none' => __( 'None','menu-plus' )
			),
			'settings' => 'generate_menu_plus_settings[sticky_menu_effect]',
			'active_callback' => 'generate_sticky_navigation_activated',
			'priority' => 110
		)
	);
	
	// Auto hide on scroll down
	$wp_customize->add_setting(
		'generate_menu_plus_settings[auto_hide_sticky]',
		array(
			'default' => $defaults['auto_hide_sticky'],
			'type' => 'option',
			'sanitize_callback' => 'generate_premium_sanitize_checkbox'
		)
	);
	
	$wp_customize->add_control(
		'generate_menu_plus_settings[auto_hide_sticky]',
		array(
			'type' => 'checkbox',
			'label' => __( 'Hide when scrolling down','menu-plus' ),
			'section' => 'menu_plus_sticky_menu',
			'settings' => 'generate_menu_plus_settings[auto_hide_sticky]',
			'priority' => 120,
			'active_callback' => 'generate_sticky_navigation_activated',
		)
	);
	
	// Navigation logo
	$wp_customize->add_setting( 
		'generate_menu_plus_settings[sticky_menu_logo]', 
		array(
			'default' => $defaults['sticky_menu_logo'],
			'type' => 'option',
			'sanitize_callback' => 'esc_url_raw'
		)
	);
 
	$wp_customize->add_control(
		new WP_Customize_Image_Control(
			$wp_customize,
			'generate_menu_plus_settings[sticky_menu_logo]',
			array(
				'label' => __('Navigation Logo','menu-plus'),
				'section' => $sticky_menu_section,
				'settings' => 'generate_menu_plus_settings[sticky_menu_logo]',
				'priority' => 115
			)
		)
	);
	
	// Logo placement
	$wp_customize->add_setting(
		'generate_menu_plus_settings[sticky_menu_logo_position]',
		array(
			'default' => $defaults['sticky_menu_logo_position'],
			'type' => 'option',
			'sanitize_callback' => 'generate_premium_sanitize_choices'
		)
	);
	
	$wp_customize->add_control(
		'generate_menu_plus_settings[sticky_menu_logo_position]',
		array(
			'type' => 'select',
			'label' => __( 'Navigation Logo Placement','menu-plus' ),
			'section' => $sticky_menu_section,
			'choices' => array(
				'sticky-menu' => __( 'Sticky','menu-plus' ),
				'menu' => __( 'Sticky + Static','menu-plus' ),
				'regular-menu' => __( 'Static','menu-plus' )
			),
			'settings' => 'generate_menu_plus_settings[sticky_menu_logo_position]',
			'priority' => 120,
			'active_callback' => 'generate_navigation_logo_activated',
		)
	);
	
	// Mobile Header section
	// No longer displays
	$wp_customize->add_section(
		'menu_plus_mobile_header',
		array(
			'title' => __( 'Mobile Header','menu-plus' ),
			'capability' => 'edit_theme_options',
			'panel' => $panel,
			'priority' => 11
		)
	);
	
	// Mobile header
	$wp_customize->add_setting(
		'generate_menu_plus_settings[mobile_header]',
		array(
			'default' => $defaults['mobile_header'],
			'type' => 'option',
			'sanitize_callback' => 'generate_premium_sanitize_choices'
		)
	);
	
	$wp_customize->add_control(
		'generate_menu_plus_settings[mobile_header]',
		array(
			'type' => 'select',
			'label' => __( 'Mobile Header','menu-plus' ),
			'section' => $header_section,
			'choices' => array(
				'disable' => __( 'Disable','menu-plus' ),
				'enable' => __( 'Enable','menu-plus' )
			),
			'settings' => 'generate_menu_plus_settings[mobile_header]',
		)
	);
	
	// Mobile header logo
	$wp_customize->add_setting( 
		'generate_menu_plus_settings[mobile_header_logo]', 
		array(
			'default' => $defaults['mobile_header_logo'],
			'type' => 'option',
			'sanitize_callback' => 'esc_url_raw'
		)
	);
 
	$wp_customize->add_control(
		new WP_Customize_Image_Control(
			$wp_customize,
			'generate_menu_plus_settings[mobile_header_logo]',
			array(
				'label' => __('Mobile Header Logo','menu-plus'),
				'section' => $header_section,
				'settings' => 'generate_menu_plus_settings[mobile_header_logo]',
				'active_callback' => 'generate_mobile_header_activated'
			)
		)
	);
	
	// Sticky mobile header
	$wp_customize->add_setting(
		'generate_menu_plus_settings[mobile_header_sticky]',
		array(
			'default' => $defaults['mobile_header_sticky'],
			'type' => 'option',
			'sanitize_callback' => 'generate_premium_sanitize_choices'
		)
	);
	
	$wp_customize->add_control(
		'generate_menu_plus_settings[mobile_header_sticky]',
		array(
			'type' => 'select',
			'label' => __( 'Sticky Mobile Header','menu-plus' ),
			'section' => $header_section,
			'choices' => array(
				'enable' => __( 'Enable','menu-plus' ),
				'disable' => __( 'Disable','menu-plus' )
			),
			'settings' => 'generate_menu_plus_settings[mobile_header_sticky]',
			'active_callback' => 'generate_mobile_header_activated'
		)
	);
	
	// Auto hide on scroll down
	$wp_customize->add_setting(
		'generate_menu_plus_settings[mobile_header_auto_hide_sticky]',
		array(
			'default' => $defaults['mobile_header_auto_hide_sticky'],
			'type' => 'option',
			'sanitize_callback' => 'generate_premium_sanitize_checkbox'
		)
	);
	
	$wp_customize->add_control(
		'generate_menu_plus_settings[mobile_header_auto_hide_sticky]',
		array(
			'type' => 'checkbox',
			'label' => __( 'Hide when scrolling down','menu-plus' ),
			'section' => $header_section,
			'settings' => 'generate_menu_plus_settings[mobile_header_auto_hide_sticky]',
			'active_callback' => 'generate_mobile_header_sticky_activated'
		)
	);
	
	// Slide-out menu section
	$wp_customize->add_section(
		'menu_plus_slideout_menu',
		array(
			'title' => __( 'Slide-out Navigation','menu-plus' ),
			'capability' => 'edit_theme_options',
			'panel' => $panel,
			'priority' => 34
		)
	);
	
	// Slide-out menu
	$wp_customize->add_setting(
		'generate_menu_plus_settings[slideout_menu]',
		array(
			'default' => $defaults['slideout_menu'],
			'type' => 'option',
			'sanitize_callback' => 'generate_premium_sanitize_choices'
		)
	);
	
	$wp_customize->add_control(
		'generate_menu_plus_settings[slideout_menu]',
		array(
			'type' => 'select',
			'label' => __( 'Slideout Navigation','menu-plus' ),
			'section' => 'menu_plus_slideout_menu',
			'choices' => array(
				'mobile' => __( 'Mobile only','menu-plus' ),
				'desktop' => __( 'Desktop only','menu-plus' ),
				'both' => __( 'Both','menu-plus' ),
				'false' => __( 'Disable','menu-plus' )
			),
			'settings' => 'generate_menu_plus_settings[slideout_menu]',
			'priority' => 150
		)
	);
}
endif;

if ( ! function_exists( 'generate_menu_plus_enqueue_css' ) ) :
/**
 * Enqueue scripts
 */
add_action( 'wp_enqueue_scripts','generate_menu_plus_enqueue_css', 100 );
function generate_menu_plus_enqueue_css()
{
	$generate_menu_plus_settings = wp_parse_args( 
		get_option( 'generate_menu_plus_settings', array() ), 
		generate_menu_plus_get_defaults() 
	);
	
	$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
	
	// Add sticky menu script
	if ( 'false' !== $generate_menu_plus_settings['sticky_menu'] ) {
		wp_enqueue_style( 'generate-sticky', plugin_dir_url( __FILE__ ) . "css/sticky{$suffix}.css", array(), GENERATE_MENU_PLUS_VERSION );
	}
	
	// Add slideout menu script
	if ( 'false' !== $generate_menu_plus_settings['slideout_menu'] ) {
		wp_enqueue_style( 'generate-sliiide', plugin_dir_url( __FILE__ ) . "css/sliiide{$suffix}.css", array(), GENERATE_MENU_PLUS_VERSION );
	}
	
	// Add regular menu logo styling
	if ( '' !== $generate_menu_plus_settings['sticky_menu_logo'] ) {
		wp_enqueue_style( 'generate-menu-logo', plugin_dir_url( __FILE__ ) . "css/menu-logo{$suffix}.css", array(), GENERATE_MENU_PLUS_VERSION );
	}
	
	// Add mobile header CSS
	if ( 'enable' == $generate_menu_plus_settings['mobile_header'] ) {
		wp_enqueue_style( 'generate-mobile-header', plugin_dir_url( __FILE__ ) . "css/mobile-header{$suffix}.css", array(), GENERATE_MENU_PLUS_VERSION );
	}
	
	// Add inline CSS
	wp_add_inline_style( 'generate-style', generate_menu_plus_inline_css() );
	
}
endif;

if ( ! function_exists( 'generate_menu_plus_enqueue_js' ) ) :
/**
 * Enqueue scripts
 */
add_action( 'wp_enqueue_scripts','generate_menu_plus_enqueue_js', 0 );
function generate_menu_plus_enqueue_js()
{
	$generate_menu_plus_settings = wp_parse_args( 
		get_option( 'generate_menu_plus_settings', array() ), 
		generate_menu_plus_get_defaults() 
	);
	
	if ( function_exists( 'generate_get_defaults' ) ) {
		$generate_settings = wp_parse_args( 
			get_option( 'generate_settings', array() ), 
			generate_get_defaults() 
		);
	}
	
	$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
	
	// Add sticky menu script
	if ( ( 'false' !== $generate_menu_plus_settings['sticky_menu'] ) || ( 'enable' == $generate_menu_plus_settings['mobile_header'] && 'enable' == $generate_menu_plus_settings['mobile_header_sticky'] ) ) {
		wp_enqueue_script( 'generate-sticky', plugin_dir_url( __FILE__ ) . "js/sticky{$suffix}.js", array( 'jquery' ), GENERATE_MENU_PLUS_VERSION, true );
	}
	
	// Add slideout menu script
	if ( 'false' !== $generate_menu_plus_settings['slideout_menu'] ) {
		wp_enqueue_script( 'generate-sliiide', plugin_dir_url( __FILE__ ) . "js/sliiide{$suffix}.js", array( 'jquery' ), GENERATE_MENU_PLUS_VERSION, true );
	}
}
endif;

if ( ! function_exists( 'generate_menu_plus_mobile_header_js' ) ) :
/**
 * Enqueue scripts
 */
add_action( 'wp_enqueue_scripts','generate_menu_plus_mobile_header_js', 15 );
function generate_menu_plus_mobile_header_js()
{
	if ( function_exists( 'wp_add_inline_script' ) ) {
	
		$generate_menu_plus_settings = wp_parse_args( 
			get_option( 'generate_menu_plus_settings', array() ), 
			generate_menu_plus_get_defaults() 
		);
	
		if ( 'enable' == $generate_menu_plus_settings[ 'mobile_header' ] && ( 'desktop' == $generate_menu_plus_settings[ 'slideout_menu' ] || 'false' == $generate_menu_plus_settings[ 'slideout_menu' ] ) ) {
			wp_add_inline_script( 'generate-navigation', 
				"jQuery( document ).ready( function($) {
					$( '#mobile-header .menu-toggle' ).on( 'click', function( e ) {
						e.preventDefault();
						$( this ).closest( '#mobile-header' ).toggleClass( 'toggled' );
						$( this ).closest( '#mobile-header' ).attr( 'aria-expanded', $( this ).closest( '#mobile-header' ).attr( 'aria-expanded' ) === 'true' ? 'false' : 'true' );
						$( this ).toggleClass( 'toggled' );
						$( this ).children( 'i' ).toggleClass( 'fa-bars' ).toggleClass( 'fa-close' );
						$( this ).attr( 'aria-expanded', $( this ).attr( 'aria-expanded' ) === 'false' ? 'true' : 'false' );
					});
				});" 
			);
		}
	}
}
endif;

if ( ! function_exists( 'generate_menu_plus_inline_css' ) ) :
/**
 * Enqueue inline CSS
 */
function generate_menu_plus_inline_css()
{
	// Bail if GP isn't active
	if ( ! function_exists( 'generate_get_defaults' ) ) {
		return;
	}
	
	$generate_settings = wp_parse_args( 
		get_option( 'generate_settings', array() ), 
		generate_get_defaults()
	);
	
	$generate_menu_plus_settings = wp_parse_args( 
		get_option( 'generate_menu_plus_settings', array() ), 
		generate_menu_plus_get_defaults() 
	);
	
	if ( function_exists( 'generate_spacing_get_defaults' ) ) {
		$spacing_settings = wp_parse_args( 
			get_option( 'generate_spacing_settings', array() ), 
			generate_spacing_get_defaults() 
		);
		$menu_height = $spacing_settings['menu_item_height'];
	} else {
		$menu_height = 60;
	}
	
	$return = '';
	
	if ( '' !== $generate_menu_plus_settings['sticky_menu_logo'] ) {
		$return .= '.main-navigation .navigation-logo img {height:' . absint( $menu_height ) . 'px;}';
		$return .= '@media (max-width: ' . ( absint( $generate_settings['container_width'] + 10 ) ) . 'px) {.main-navigation .navigation-logo.site-logo {margin-left:0;}body.sticky-menu-logo.nav-float-left .main-navigation .site-logo.navigation-logo {margin-right:0;}}';
	}
	
	if ( '' !== $generate_menu_plus_settings['mobile_header_logo'] ) {
		$return .= '.mobile-header-navigation .mobile-header-logo img {height:' . absint( $menu_height ) . 'px;}';
	}
	
	if ( 'false' !== $generate_menu_plus_settings['sticky_menu'] ) {
		$return .= '.main-navigation .main-nav ul li a,.menu-toggle,.main-navigation .mobile-bar-items a{transition: line-height 300ms ease}';
	}
	
	return $return;
}
endif;

if ( ! function_exists( 'generate_menu_plus_mobile_header' ) ) :
add_action( 'generate_after_header', 'generate_menu_plus_mobile_header', 5 );
add_action( 'generate_inside_mobile_header','generate_navigation_search');
add_action( 'generate_inside_mobile_header','generate_mobile_menu_search_icon' );
function generate_menu_plus_mobile_header()
{
	$generate_menu_plus_settings = wp_parse_args( 
		get_option( 'generate_menu_plus_settings', array() ), 
		generate_menu_plus_get_defaults() 
	);
	
	if ( 'disable' == $generate_menu_plus_settings[ 'mobile_header' ] ) {
		return;
	}

	if ( 'false' !== $generate_menu_plus_settings['mobile_header_auto_hide_sticky'] && $generate_menu_plus_settings[ 'mobile_header_auto_hide_sticky' ] ) {
		$hide_sticky = ' data-auto-hide-sticky="true"';
	} else {
		$hide_sticky = '';
	}
	?>
	<nav itemtype="http://schema.org/SiteNavigationElement" itemscope="itemscope" id="mobile-header"<?php echo $hide_sticky;?> class="main-navigation mobile-header-navigation">
		<div class="inside-navigation grid-container grid-parent">
			<?php do_action( 'generate_inside_mobile_header' ); ?>
			<button class="menu-toggle" aria-controls="mobile-menu" aria-expanded="false">
				<?php do_action( 'generate_inside_mobile_header_menu' ); ?>
				<span class="mobile-menu"><?php echo apply_filters('generate_mobile_menu_label', __( 'Menu', 'generatepress' ) ); ?></span>
			</button>
			<?php 
			wp_nav_menu( 
				array( 
					'theme_location' => apply_filters( 'generate_mobile_header_theme_location', 'primary' ),
					'container' => 'div',
					'container_class' => 'main-nav',
					'container_id' => 'mobile-menu',
					'menu_class' => '',
					'fallback_cb' => 'generate_menu_fallback',
					'items_wrap' => '<ul id="%1$s" class="%2$s ' . join( ' ', generate_get_menu_class() ) . '">%3$s</ul>'
				) 
			);
			?>
		</div><!-- .inside-navigation -->
	</nav><!-- #site-navigation -->
	<?php
}
endif;

if ( ! function_exists( 'generate_slideout_navigation' ) ) :
/**
 *
 * Build the navigation
 * @since 0.1
 *
 */
add_action( 'wp_footer','generate_slideout_navigation', 0 );
function generate_slideout_navigation()
{
	$generate_menu_plus_settings = wp_parse_args( 
		get_option( 'generate_menu_plus_settings', array() ), 
		generate_menu_plus_get_defaults() 
	);
	
	if ( 'false' == $generate_menu_plus_settings['slideout_menu'] ) {
		return;
	}
	?>
	<nav itemtype="http://schema.org/SiteNavigationElement" itemscope="itemscope" id="generate-slideout-menu" class="main-navigation slideout-navigation">
		<div class="inside-navigation grid-container grid-parent">
			<?php 
			do_action( 'generate_inside_slideout_navigation' );
			wp_nav_menu( 
				array( 
					'theme_location' => 'slideout',
					'container' => 'div',
					'container_class' => 'main-nav',
					'menu_class' => '',
					'fallback_cb' => 'generate_slideout_menu_fallback',
					'items_wrap' => '<ul id="%1$s" class="%2$s slideout-menu">%3$s</ul>'
				) 
			);
			?>
			<?php do_action( 'generate_after_slideout_navigation' ); ?>
		</div><!-- .inside-navigation -->
	</nav><!-- #site-navigation -->
	<div class="slideout-overlay" style="display: none;"></div>
	<?php
}
endif;

if ( ! function_exists( 'generate_slideout_menu_fallback' ) ) :
/**
 * Menu fallback. 
 *
 * @param  array $args
 * @return string
 * @since 1.1.4
 */
function generate_slideout_menu_fallback( $args )
{ 
	$generate_settings = wp_parse_args( 
		get_option( 'generate_settings', array() ), 
		generate_get_defaults() 
	);
	?>
	<div class="main-nav">
		<ul <?php generate_slideout_menu_class(); ?>>
			<?php wp_list_pages('sort_column=menu_order&title_li='); ?>
		</ul>
	</div><!-- .main-nav -->
	<?php 
}
endif;

if ( ! function_exists( 'generate_slideout_body_classes' ) ) :
/**
 * Adds custom classes to body
 * @since 0.1
 */
add_filter( 'body_class', 'generate_slideout_body_classes');
function generate_slideout_body_classes( $classes )
{
	$generate_menu_plus_settings = wp_parse_args( 
		get_option( 'generate_menu_plus_settings', array() ), 
		generate_menu_plus_get_defaults() 
	);
	
	// Slide-out menu classes
	if ( 'false' !== $generate_menu_plus_settings['slideout_menu'] ) {
		$classes[] = 'slideout-enabled';
	}
	
	if ( 'mobile' == $generate_menu_plus_settings['slideout_menu'] ) {
		$classes[] = 'slideout-mobile';
	}
	
	if ( 'desktop' == $generate_menu_plus_settings['slideout_menu'] ) {
		$classes[] = 'slideout-desktop';
	}
	
	if ( 'both' == $generate_menu_plus_settings['slideout_menu'] ) {
		$classes[] = 'slideout-both';
	}
	
	// Sticky menu transition class
	if ( 'slide' == $generate_menu_plus_settings['sticky_menu_effect'] ) {
		$classes[] = 'sticky-menu-slide';
	}
	
	if ( 'fade' == $generate_menu_plus_settings['sticky_menu_effect'] ) {
		$classes[] = 'sticky-menu-fade';
	}
	
	if ( 'none' == $generate_menu_plus_settings['sticky_menu_effect'] ) {
		$classes[] = 'sticky-menu-no-transition';
	}
	
	// If sticky menu is enabled
	if ( 'false' !== $generate_menu_plus_settings['sticky_menu'] ) {
		$classes[] = 'sticky-enabled';
	}
	
	// Sticky menu classes
	if ( '' !== $generate_menu_plus_settings['sticky_menu_logo'] ) {
		
		if ( 'sticky-menu' == $generate_menu_plus_settings['sticky_menu_logo_position'] ) {
			$classes[] = 'sticky-menu-logo';
		} elseif ( 'menu' == $generate_menu_plus_settings['sticky_menu_logo_position'] ) {
			$classes[] = 'menu-logo';
		} elseif ( 'regular-menu' == $generate_menu_plus_settings['sticky_menu_logo_position'] ) {
			$classes[] = 'regular-menu-logo';
		}
		
		$classes[] = 'menu-logo-enabled';
		
	}
	
	// Menu logo classes
	if ( 'mobile' == $generate_menu_plus_settings['sticky_menu'] ) {
		$classes[] = 'mobile-sticky-menu';
	}
	
	if ( 'desktop' == $generate_menu_plus_settings['sticky_menu'] ) {
		$classes[] = 'desktop-sticky-menu';
	}
	
	if ( 'true' == $generate_menu_plus_settings['sticky_menu'] ) {
		$classes[] = 'both-sticky-menu';
	}
	
	// Mobile header classes
	if ( 'enable' == $generate_menu_plus_settings['mobile_header'] ) {
		$classes[] = 'mobile-header';
	}
	
	if ( '' !== $generate_menu_plus_settings['mobile_header_logo'] && 'enable' == $generate_menu_plus_settings['mobile_header'] ) {
		$classes[] = 'mobile-header-logo';
	}
	
	if ( 'enable' == $generate_menu_plus_settings['mobile_header_sticky'] && 'enable' == $generate_menu_plus_settings['mobile_header'] ) {
		$classes[] = 'mobile-header-sticky';
	}
	
	return $classes;
	
}
endif;

if ( ! function_exists( 'generate_menu_plus_slidebar_icon' ) ) :
/**
 * Add slidebar icon to primary menu if set
 *
 * @since 0.1
 */
add_filter( 'wp_nav_menu_items','generate_menu_plus_slidebar_icon', 10, 2 );
function generate_menu_plus_slidebar_icon( $nav, $args ) 
{
	$generate_menu_plus_settings = wp_parse_args( 
		get_option( 'generate_menu_plus_settings', array() ), 
		generate_menu_plus_get_defaults() 
	);
	
	// If the search icon isn't enabled, return the regular nav
	if ( 'desktop' !== $generate_menu_plus_settings['slideout_menu'] && 'both' !== $generate_menu_plus_settings['slideout_menu'] ) {
		return $nav;
	}
	
	// If our primary menu is set, add the search icon
    if( $args->theme_location == 'primary' ) {
        return $nav . '<li class="slideout-toggle"><a href="#generate-slideout-menu" data-transition="overlay"></a></li>';
	}
	
	// Our primary menu isn't set, return the regular nav
	// In this case, the search icon is added to the generate_menu_fallback() function in navigation.php
    return $nav;
}
endif;

if ( ! function_exists( 'generate_sticky_navigation_classes' ) ) :
/**
 * Adds custom classes to the navigation
 * @since 0.1
 */
add_filter( 'generate_navigation_class', 'generate_sticky_navigation_classes');
function generate_sticky_navigation_classes( $classes ) {
	
	$generate_menu_plus_settings = wp_parse_args( 
		get_option( 'generate_menu_plus_settings', array() ), 
		generate_menu_plus_get_defaults() 
	);

	if ( 'false' !== $generate_menu_plus_settings['sticky_menu'] && $generate_menu_plus_settings[ 'auto_hide_sticky' ] ) {
		$classes[] = 'auto-hide-sticky';
	}

	return $classes;
	
}
endif;

if ( ! function_exists( 'generate_menu_plus_label' ) ) :
/**
 * Add mobile menu label
 *
 * @since 0.1
 */
add_filter( 'generate_mobile_menu_label','generate_menu_plus_label' );
function generate_menu_plus_label()
{
	$generate_menu_plus_settings = wp_parse_args( 
		get_option( 'generate_menu_plus_settings', array() ), 
		generate_menu_plus_get_defaults() 
	);
	
	return wp_kses_post( $generate_menu_plus_settings['mobile_menu_label'] );
}
endif;

if ( ! function_exists( 'generate_menu_plus_sticky_logo' ) ) :
/**
 * Add logo to sticky menu
 *
 * @since 0.1
 */
add_action( 'generate_inside_navigation','generate_menu_plus_sticky_logo' );
function generate_menu_plus_sticky_logo()
{
	$generate_menu_plus_settings = wp_parse_args( 
		get_option( 'generate_menu_plus_settings', array() ), 
		generate_menu_plus_get_defaults() 
	);
	
	if ( '' == $generate_menu_plus_settings['sticky_menu_logo'] ) {
		return;
	}
	
	echo apply_filters( 'generate_navigation_logo_output', sprintf(
		'<div class="site-logo sticky-logo navigation-logo">
			<a href="%1$s" title="%2$s" rel="home">
				<img class="header-image" src="%3$s" alt="%4$s" />
			</a>
		</div>',
		esc_url( apply_filters( 'generate_logo_href' , home_url( '/' ) ) ),
		esc_attr( apply_filters( 'generate_logo_title', get_bloginfo( 'name', 'display' ) ) ),
		esc_url( apply_filters( 'generate_navigation_logo', $generate_menu_plus_settings['sticky_menu_logo'] ) ),
		esc_attr( apply_filters( 'generate_logo_title', get_bloginfo( 'name', 'display' ) ) )
	) );
}
endif;

if ( ! function_exists( 'generate_menu_plus_mobile_header_logo' ) ) :
/**
 * Add logo to mobile header
 *
 * @since 0.1
 */
add_action( 'generate_inside_mobile_header','generate_menu_plus_mobile_header_logo' );
function generate_menu_plus_mobile_header_logo()
{
	$generate_menu_plus_settings = wp_parse_args( 
		get_option( 'generate_menu_plus_settings', array() ), 
		generate_menu_plus_get_defaults() 
	);
	
	if ( '' == $generate_menu_plus_settings['mobile_header_logo'] ) {
		return;
	}
	
	echo apply_filters( 'generate_mobile_header_logo_output', sprintf(
		'<div class="site-logo mobile-header-logo">
			<a href="%1$s" title="%2$s" rel="home">
				<img class="header-image" src="%3$s" alt="%4$s" />
			</a>
		</div>',
		esc_url( apply_filters( 'generate_logo_href' , home_url( '/' ) ) ),
		esc_attr( apply_filters( 'generate_logo_title', get_bloginfo( 'name', 'display' ) ) ),
		esc_url( apply_filters( 'generate_mobile_header_logo', $generate_menu_plus_settings['mobile_header_logo'] ) ),
		esc_attr( apply_filters( 'generate_logo_title', get_bloginfo( 'name', 'display' ) ) )
	) );
}
endif;