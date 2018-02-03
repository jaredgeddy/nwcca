<?php
defined( 'WPINC' ) or die;

if ( ! function_exists( 'generate_page_header_inside' ) ) :
/**
 * Add page header inside content
 * @since 0.3
 */
function generate_page_header_inside() {
	if ( ! is_page() ) {
		return;
	}

	if ( 'inside-content' == generate_get_page_header_location() ) {
		generate_page_header_area( 'page-header-image', 'page-header-content' );
	}
}
endif;

if ( ! function_exists( 'generate_page_header_single_below_title' ) ) :
/**
 * Add post header below title
 * @since 0.3
 */
function generate_page_header_single_below_title() {
	if ( ! is_single() ) {
		return;
	}

	if ( 'below-title' == generate_get_page_header_location() ) {
		generate_page_header_area( 'page-header-image-single page-header-below-title', 'page-header-content-single page-header-below-title' );
	}
}
endif;

if ( ! function_exists( 'generate_page_header_single_above' ) ) :
/**
 * Add post header above content
 * @since 0.3
 */
function generate_page_header_single_above() {
	if ( ! is_single() ) {
		return;
	}
		
	if ( 'above-content' == generate_get_page_header_location() ) {
		generate_page_header_area( 'page-header-image-single', 'page-header-content-single' );
	}
}
endif;

if ( ! function_exists( 'generate_page_header_single' ) ) :
/**
 * Add post header inside content
 * @since 0.3
 */
function generate_page_header_single() {
	$image_class = 'page-header-image-single';
	$content_class = 'page-header-content-single';
	
	if ( 'below-title' == generate_get_page_header_location() ) {
		$image_class = 'page-header-image-single page-header-below-title';
		$content_class = 'page-header-content-single page-header-below-title';
	}
	
	if ( 'inside-content' == generate_get_page_header_location() ) {
		generate_page_header_area( $image_class, $content_class );
	}
}
endif;