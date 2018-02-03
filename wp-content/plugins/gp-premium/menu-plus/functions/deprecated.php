<?php
defined( 'WPINC' ) or die;

if ( ! function_exists( 'generate_slideout_navigation_class' ) ) :
/**
 * Display the classes for the slideout navigation.
 *
 * @since 0.1
 * @param string|array $class One or more classes to add to the class list.
 */
function generate_slideout_navigation_class( $class = '' ) {
	// Separates classes with a single space, collates classes for post DIV
	echo 'class="' . join( ' ', generate_get_slideout_navigation_class( $class ) ) . '"';
}
endif;

if ( ! function_exists( 'generate_get_slideout_navigation_class' ) ) :
/**
 * Retrieve the classes for the slideout navigation.
 *
 * @since 0.1
 * @param string|array $class One or more classes to add to the class list.
 * @return array Array of classes.
 */
function generate_get_slideout_navigation_class( $class = '' ) {
	$classes = array();

	if ( !empty($class) ) {
		if ( !is_array( $class ) )
			$class = preg_split('#\s+#', $class);
		$classes = array_merge($classes, $class);
	}

	$classes = array_map('esc_attr', $classes);

	return apply_filters('generate_slideout_navigation_class', $classes, $class);
}
endif;

if ( ! function_exists( 'generate_slideout_menu_class' ) ) :
/**
 * Display the classes for the slideout navigation.
 *
 * @since 0.1
 * @param string|array $class One or more classes to add to the class list.
 */
function generate_slideout_menu_class( $class = '' ) {
	// Separates classes with a single space, collates classes for post DIV
	echo 'class="' . join( ' ', generate_get_slideout_menu_class( $class ) ) . '"';
}
endif;

if ( ! function_exists( 'generate_get_slideout_menu_class' ) ) :
/**
 * Retrieve the classes for the slideout navigation.
 *
 * @since 0.1
 * @param string|array $class One or more classes to add to the class list.
 * @return array Array of classes.
 */
function generate_get_slideout_menu_class( $class = '' ) {
	$classes = array();

	if ( !empty($class) ) {
		if ( !is_array( $class ) )
			$class = preg_split('#\s+#', $class);
		$classes = array_merge($classes, $class);
	}

	$classes = array_map('esc_attr', $classes);

	return apply_filters('generate_slideout_menu_class', $classes, $class);
}
endif;

if ( ! function_exists( 'generate_slideout_menu_classes' ) ) :
/**
 * Adds custom classes to the menu
 * @since 0.1
 */
add_filter( 'generate_slideout_menu_class', 'generate_slideout_menu_classes');
function generate_slideout_menu_classes( $classes ) {
	$classes[] = 'slideout-menu';
	return $classes;
	
}
endif;

if ( ! function_exists( 'generate_slideout_navigation_classes' ) ) :
/**
 * Adds custom classes to the navigation
 * @since 0.1
 */
add_filter( 'generate_slideout_navigation_class', 'generate_slideout_navigation_classes');
function generate_slideout_navigation_classes( $classes ){
	$slideout_effect = apply_filters( 'generate_menu_slideout_effect','overlay' );
	$slideout_position = apply_filters( 'generate_menu_slideout_position','left' );
	
	$classes[] = 'main-navigation';
	$classes[] = 'slideout-navigation';

	return $classes;
}
endif;