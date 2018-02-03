<?php
// No direct access, please
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define the version
define( 'GENERATE_WOOCOMMERCE_VERSION', GP_PREMIUM_VERSION );

if ( ! function_exists( 'generate_woocommerce_init' ) ) :
add_action('plugins_loaded', 'generate_woocommerce_init');
function generate_woocommerce_init() {
	load_plugin_textdomain( 'generate-woocommerce', false, 'gp-premium/langs/woocommerce/' );
}
endif;

// Include functions identical between standalone addon and GP Premium
require plugin_dir_path( __FILE__ ) . 'functions/functions.php';