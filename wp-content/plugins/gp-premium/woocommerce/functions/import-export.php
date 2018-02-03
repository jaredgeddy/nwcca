<?php
// No direct access, please
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! function_exists( 'generatepress_wc_reset_settings' ) ) :
/**
 * Reset customizer settings
 */
add_action( 'generate_delete_settings_form','generatepress_wc_reset_settings');
function generatepress_wc_reset_settings()
{
	?>
	<form method="post">
		<p><input type="hidden" name="generatepress_wc_reset_customizer" value="generatepress_wc_reset_customizer_settings" /></p>
		<p>
			<?php 
			$warning = 'return confirm("' . __( 'Warning: This will delete your settings.','generate-woocommerce' ) . '")';
			wp_nonce_field( 'generatepress_wc_reset_customizer_nonce', 'generatepress_wc_reset_customizer_nonce' );
			submit_button( __( 'Delete WooCommerce Settings', 'generate-woocommerce' ), 'wc-button', 'wc-submit', false, array( 'onclick' => esc_js( $warning ) ) ); 
			?>
		</p>
	</form>
	<?php
}
endif;

if ( ! function_exists( 'generatepress_wc_reset_customizer_settings' ) ) :
add_action( 'admin_init', 'generatepress_wc_reset_customizer_settings' );
function generatepress_wc_reset_customizer_settings() {

	if( empty( $_POST['generatepress_wc_reset_customizer'] ) || 'generatepress_wc_reset_customizer_settings' != $_POST['generatepress_wc_reset_customizer'] )
		return;

	if( ! wp_verify_nonce( $_POST['generatepress_wc_reset_customizer_nonce'], 'generatepress_wc_reset_customizer_nonce' ) )
		return;

	if( ! current_user_can( 'manage_options' ) )
		return;

	delete_option('generate_woocommerce_settings');
	
	wp_safe_redirect( admin_url( 'themes.php?page=generate-options&status=reset' ) ); exit;

}
endif;

if ( ! function_exists( 'generatepress_wc_add_export' ) ) :
	add_action('generate_add_export_items','generatepress_wc_add_export');
	function generatepress_wc_add_export()
	{
		?>
		<form method="post">
			<p><input type="hidden" name="generatepress_wc_export_action" value="export_woocommerce_settings" /></p>
			<p>
				<?php wp_nonce_field( 'generatepress_wc_export_nonce', 'generatepress_wc_export_nonce' ); ?>
				<?php submit_button( __( 'Export WooCommerce Settings', 'generate-woocommerce' ), 'button', 'submit', false ); ?>
			</p>
		</form>
		<?php
	}
endif;

if ( ! function_exists( 'generatepress_wc_process_settings_export' ) ) :
	/**
	 * Process a settings export that generates a .json file of the shop settings
	 */
	add_action( 'admin_init', 'generatepress_wc_process_settings_export' );
	function generatepress_wc_process_settings_export() {

		if( empty( $_POST['generatepress_wc_export_action'] ) || 'export_woocommerce_settings' != $_POST['generatepress_wc_export_action'] )
			return;

		if( ! wp_verify_nonce( $_POST['generatepress_wc_export_nonce'], 'generatepress_wc_export_nonce' ) )
			return;

		if( ! current_user_can( 'manage_options' ) )
			return;
		
		$settings = get_option( 'generate_woocommerce_settings' );

		ignore_user_abort( true );

		nocache_headers();
		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=generate_woocommerce_settings-export-' . date( 'm-d-Y' ) . '.json' );
		header( "Expires: 0" );

		echo json_encode( $settings );
		exit;
	}
endif;