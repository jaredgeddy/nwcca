<?php
/*
Plugin Name: Dynamic Menu Manager
Plugin URI: https://duogeek.com/products/plugins/different-menu-in-different-pages/
Description: Add different wordpress menu in different pages. It works even in virtual pages. Very simple to implement and very user friendly.
Version: 1.0.7
Author: duogeek
Author URI: https://duogeek.com
*/

if( ! defined( 'DUO_PLUGIN_URI' ) ) define( 'DUO_PLUGIN_URI', plugin_dir_url( __FILE__ ) );

require 'duogeek/duogeek-panel.php';


if( !class_exists( 'DMM_Class' ) ) {
	
	global $jal_db_version;
	
	/**
	 * DMM_Class
	 */
	class DMM_Class {
		
		public $domain;
	    public $plugin_url;
	    public $plugin_dir;
		public $jal_db_version;
		public $main_locations;
		
		public function __construct() {
				
			global $wpdb;
			
			
	        $this->plugin_dir = WP_PLUGIN_DIR . '/dynamic-menu-manager/';
	        $this->plugin_url = plugins_url('/', __FILE__);
			$this->jal_db_version = "1.0";
			
			
			add_action( 'init', array( $this, 'dmm_load_textdomain' ) );
			register_activation_hook( __FILE__, array( $this, 'menu_tables_install' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'add_styles_scripts' ) );

			add_action( 'add_meta_boxes', array( $this, 'add_dmm_meta_box' ) );
			add_action( 'wp_loaded', array( $this, 'get_registered_nav') );
			add_action( 'save_post', array($this, 'save_dmm_meta_settings'), 1, 2 );
			
			$dg_license_verify = ( array ) get_option( 'dg_license_verify' );
			//if( in_array( 'different_menu_in_different_page', $dg_license_verify ) )
				add_filter( 'wp_nav_menu_args', array( $this, 'set_changed_menu' ), 10 );
			
			$this->register_all_nav();
			add_action( 'admin_init', array( $this, 'set_menu_field_for_tax' ) );
			add_filter( 'duogeek_submenu_pages', array( $this, 'dmm_menu' ) );
			add_shortcode( 'dmm_menu_loc', array( $this, 'dmm_menu_loc_cb' ) );
			add_filter( 'duo_panel_help', array( $this, 'dmm_help_cb' ) );
			register_activation_hook( __FILE__, array( $this, 'dmm_plugin_activate' ) );
			add_action( 'admin_init', array( $this, 'dmm_plugin_redirect' ) );

		}
                
		/*
	     * Adding language file
	     */
	    public function dmm_load_textdomain() {
	        load_plugin_textdomain( 'dmm', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' ); 
	    }
		
		/*
		 * Creating tables when the theme is installed
		 */
		public function menu_tables_install() {
			global $wpdb;
			global $jal_db_version;
			
			
			$table_name = $wpdb->prefix . 'dmm_menu';
			$table_name2 = $wpdb->prefix . 'dmm_url_groups';
			$table_name3 = $wpdb->prefix . 'dmm_url_mapping';
	  
			$sql = "CREATE TABLE IF NOT EXISTS $table_name (
				id INT(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
				menu_location VARCHAR(255) NOT NULL,
				menu_desc VARCHAR(255)
				);";
				
			$sql2 = "CREATE TABLE IF NOT EXISTS $table_name2 (
				id INT(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
				group_name VARCHAR(255) NOT NULL,
				url_list TEXT
				);";
				
			$sql3 = "CREATE TABLE IF NOT EXISTS $table_name3 (
				id INT(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
				group_id INT(20) NOT NULL,
				menu_replace VARCHAR(255) NOT NULL,
				replaced_menu VARCHAR(255),
				new_menu VARCHAR(255)
				);";
	
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql );
			dbDelta( $sql2 );
			dbDelta( $sql3 );
	 
			add_option( "jal_db_version", $jal_db_version );
		}
		
		/*
	     * Register style and scripts
	     */
	    public function add_styles_scripts() {
	        wp_register_script( 'dmm-js', $this->plugin_url . 'inc/js/admin.js', array( 'jquery' ) );
	        wp_enqueue_script( 'dmm-js' );
	        
	        wp_register_style( 'dmm-css', $this->plugin_url . 'inc/css/admin.css' );
	        wp_enqueue_style( 'dmm-css' );
			wp_localize_script( 'dmm-js', 'data', array( 
											'confirm_message' => __('Are you sure you want to delete?', 'dmm' ),
											'alert_msg' => __( 'You have missed a required field.', 'dmm' )
											) );
	    }

		/**
		 * DMM Settings Memu
		 */
		public function dmm_menu( $submenus ) {
			$submenus[] = array(
				'title' => __( 'Dynamic Menu Manager', 'dmm' ),
				'menu_title' => __( 'Menu Manager', 'dmm' ),
				'capability' => 'manage_options',
				'slug' => 'dynamic-menu-manager',
				'object' => $this,
				'function' => 'dynamic_menu_settings_page'
			);

			return $submenus;
		}

		 public function dynamic_menu_settings_page() {
		 	global $wpdb;
		 	if( isset( $_POST['save_location'] ) ) {
		 		if ( !check_admin_referer( 'dmm_nonce_action', 'dmm_nonce_field' )){
		            return;
		        }
				
				$dmm_menu_location = strtolower( str_replace( ' ', '-', trim( $_POST['dmm_menu_location'] ) ) );
				$dmm_menu_desc = $_POST['dmm_menu_desc'];
				
				$q = $wpdb->insert( $wpdb->prefix . 'dmm_menu', array( 'menu_location'=>$dmm_menu_location, 'menu_desc'=>$dmm_menu_desc ) );

				if( $q ) {
					do_action( 'dmm_menu_location_saved' );
					wp_redirect( admin_url( 'admin.php?page=dynamic-menu-manager&&action=menu_saved' ) );
					}
				else wp_die( 'Sorry, there is an error!', 'dmm' );
		 	}
			
			if( isset( $_POST['save_group'] ) ) {
		 		if ( !check_admin_referer( 'dmm_group_nonce_action', 'dmm_group_nonce_field' )){
		            return;
		        }
				
				$group_name = $_POST['group_name'];
				$url_list = $_POST['url_list'];
				
				$q = $wpdb->insert( $wpdb->prefix . 'dmm_url_groups', array( 'group_name'=>$group_name, 'url_list'=>$url_list ) );

				if( $q ) {
					do_action( 'dmm_url_group_saved' );
					wp_redirect( admin_url( 'admin.php?page=dynamic-menu-manager&&tab=url_groups&&action=group_saved' ) );
					}
				else wp_die( 'Sorry, there is an error!', 'dmm' );
		 	}
			
			if( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'menu_delete' ) {
				$id = $_REQUEST['menu_id'];
				$sql = $wpdb->prepare( "DELETE from " . $wpdb->prefix . "dmm_menu where id = '%s' ", $id );
				$q = $wpdb->query( $sql );
				if( $q ) {
					do_action( 'dmm_menu_location_deleted' );
					wp_redirect( admin_url( 'admin.php?page=dynamic-menu-manager&&action=menu_deleted' ) );
				}
			}
			
			if( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'group_delete' ) {
				$id = $_REQUEST['group_id'];
				$sql = $wpdb->prepare( "DELETE from " . $wpdb->prefix . "dmm_url_groups where id = '%s' ", $id );
				$q = $wpdb->query( $sql );
				if( $q ) {
					do_action( 'dmm_group_url_deleted' );
					wp_redirect( admin_url( 'admin.php?page=dynamic-menu-manager&&tab=url_groups&&action=group_deleted' ) );
				}
			}

			if( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'group_edit' ) {
				$id = $_REQUEST['group_id'];
				$sql = $wpdb->prepare( "SELECT * from " . $wpdb->prefix . "dmm_url_groups where id = '%s' ", $id );
				$groups = $wpdb->get_results( $sql, 'ARRAY_A' );
			}
			
			if( isset( $_POST['update_group'] ) ) {
				$id = $_POST['group_id'];
				$sql = $wpdb->prepare( "UPDATE " . $wpdb->prefix . "dmm_url_groups set group_name = '".$_POST['group_name']."', url_list = '".$_POST['url_list']."' where id = '%s' ", $id );
				$q = $wpdb->query( $sql );

				do_action( 'dmm_group_url_deleted' );
				wp_redirect( admin_url( 'admin.php?page=dynamic-menu-manager&&tab=url_groups&&action=group_updated' ) );

			}
			
			
			
			$dmm_menus = $this->get_menus();
			$url_groups = $this->get_group_name();
		 	
		 	if( !isset( $_REQUEST['tab'] ) ){
		 		include plugin_dir_path( __FILE__ ) . 'includes/add_menu.php';
		 	}
		 	else{
		 		if( $_REQUEST['tab'] == 'url_groups' ) {
		 			include plugin_dir_path( __FILE__ ) . 'includes/url_groups.php';
				}elseif( $_REQUEST['tab'] == 'customize' ) {
						
					if( isset( $_POST['set_rule'] ) ) {
						if ( !check_admin_referer( 'dmm_group_rule_nonce_action', 'dmm_group_rule_nonce_field' )){
				            return;
				        }
					}
					
					$id = $_REQUEST['group_id'];
					$sql = $wpdb->prepare( "SELECT * from " . $wpdb->prefix . "dmm_url_mapping where group_id = '%s'", $id );
					$rules = $wpdb->get_results( $sql, 'ARRAY_A' );
 					
					if( count( $rules ) < 1 ) {
						
						if( isset( $_POST['set_rule'] ) ) {
							$q = $wpdb->insert( $wpdb->prefix . "dmm_url_mapping", array( 'group_id' => $_POST['group_id'], 'menu_replace'=>$_POST['menu_replace'], 'replaced_menu'=>$_POST['replaced_menu'], 'new_menu'=>$_POST['new_menu'] ) );
							if( $q ) {
								do_action( 'url_group_updated', $_POST );
								wp_redirect( admin_url( 'admin.php?page=dynamic-menu-manager&&tab=url_groups&&action=customized' ) );
							}
						}
						
						$menu_replace = '';
						$replaced_menu = '';
						$new_menu = '';
					}else{
						
						if( isset( $_POST['set_rule'] ) ) {
							$q = $wpdb->update( $wpdb->prefix . "dmm_url_mapping", array( 'menu_replace'=>$_POST['menu_replace'], 'replaced_menu'=>$_POST['replaced_menu'], 'new_menu'=>$_POST['new_menu'] ), array( 'group_id' => $_POST['group_id'] ) );

							do_action( 'url_group_updated', $_POST );
							wp_redirect( admin_url( 'admin.php?page=dynamic-menu-manager&&tab=url_groups&&action=customized' ) );
							
						}
						
						$menu_replace = $rules[0]['menu_replace'];
						$replaced_menu = $rules[0]['replaced_menu'];
						$new_menu = $rules[0]['new_menu'];
					}
					
		 			include plugin_dir_path( __FILE__ ) . 'includes/customize.php';
				} 
			}
		 }

		public function get_menus() {
			global $wpdb;
			$sql = 'SELECT * from ' . $wpdb->prefix . 'dmm_menu';
			return $wpdb->get_results( $sql, 'ARRAY_A' );
		}

		public function get_group_name() {
			global $wpdb;
			$sql = 'SELECT * from ' . $wpdb->prefix . 'dmm_url_groups';
			return $wpdb->get_results( $sql, 'ARRAY_A' );
		}
		
		public function register_all_nav() {
			$dmm_menus = $this->get_menus();
			foreach( $dmm_menus as $dmm_menu ) {
				register_nav_menu( $dmm_menu['menu_location'], $dmm_menu['menu_desc'] );
			}
		}
		
		public function add_dmm_meta_box() {
			$post_types = get_post_types(array("public" => true));
			foreach ($post_types as $post_type)
				add_meta_box( 'dmm-meta-box', __( 'Menu Manager', 'dmm' ), array( $this, 'dmm_meta_box_cb' ), $post_type, 'side', 'high' );
		}
		
		public function dmm_meta_box_cb() {
			global $post;
			wp_nonce_field('dmm_meta_nonce_action','dmm_meta_nonce_field');
			
			$menu_replace = get_post_meta( $post->ID, 'menu_replace', true );
			$replaced_menu = get_post_meta( $post->ID, 'replaced_menu', true );
			$new_menu = get_post_meta( $post->ID, 'new_menu', true );
			
			?>
			<label>
				<input <?php echo !$menu_replace ? '' : 'checked' ?> type="checkbox" name="menu_replace" value="yes" /> <?php _e( 'Enable Menu Replacement', 'dmm' ); ?>
			</label>
			<hr>
			<?php
			$dmm_menus = $this->get_menus();
			foreach( $dmm_menus as $dmm_menu ) {
			    unset( $this->main_locations[$dmm_menu['menu_location']] );
			}
			?>
			<?php _e( 'Select replaced menu', 'dmm' ) ?><br>
			<select name="replaced_menu">
				<option value=""></option>
				<?php foreach( $this->main_locations as $main_location => $menu_desc ){ ?>
					<option <?php echo $main_location == $replaced_menu ? 'selected' : '' ?> value="<?php echo $main_location ?>"><?php echo $menu_desc ?></option>
				<?php } ?>
			</select>
			<br>
			<?php _e( 'Select new menu', 'dmm' ) ?><br>
			<select name="new_menu">
				<option value=""></option>
				<?php foreach( $dmm_menus as $dmm_menu ) { ?>
					<option <?php echo $dmm_menu['menu_location'] == $new_menu ? 'selected' : '' ?> value="<?php echo $dmm_menu['menu_location'] ?>"><?php echo $dmm_menu['menu_desc'] ?></option>
				<?php } ?>
			</select>
			<?php
                        
                        do_action( 'dmm_menu_meta' );
		}

		public function save_dmm_meta_settings( $post_id, $post ) {
			global $post;
			if( isset( $_POST['dmm_meta_nonce_field'] ) ) {
				if ( !check_admin_referer( 'dmm_meta_nonce_action', 'dmm_meta_nonce_field' )) return;
				if ($post->post_type == 'revision') return;
				
				$menu_replace = isset($_POST['menu_replace']) ? $_POST['menu_replace'] : '';
				$replaced_menu = isset($_POST['replaced_menu']) ? $_POST['replaced_menu'] : '';
				$new_menu = isset($_POST['new_menu']) ? $_POST['new_menu'] : '';
				
				!$menu_replace ?  delete_post_meta( $post->ID, 'menu_replace' ) : update_post_meta( $post->ID, 'menu_replace', $menu_replace );
				!$replaced_menu ?  delete_post_meta( $post->ID, 'replaced_menu' ) : update_post_meta( $post->ID, 'replaced_menu', $replaced_menu );
				!$new_menu ?  delete_post_meta( $post->ID, 'new_menu' ) : update_post_meta( $post->ID, 'new_menu', $new_menu );
			}
			
		}
		
		public function set_changed_menu( $args ) {
			global $post;
			global $wpdb;
			
                        $menu_replace = '';
			$current_page = $this->curPageURL();
			$url_groups = $this->get_group_name();
                        $locations = get_nav_menu_locations();
			
			foreach( $url_groups as $url_group ) {
				$url_list = explode( ",",  $url_group['url_list'] );
				$sql = $wpdb->prepare( "SELECT * from " . $wpdb->prefix . "dmm_url_mapping where group_id = '%s'", $url_group['id'] );
				$groups = $wpdb->get_results( $sql, 'ARRAY_A' );
				
				foreach( $groups as $group ) {
					if( $group['menu_replace'] == 'yes' ) {
						foreach($url_list as $url) {
							if( preg_match( "#$url#", $current_page, $matches ) ) {
								if( $group['replaced_menu'] != '' && $group['new_menu'] != '' ) {
                                                                    if( $args['theme_location'] == $group['replaced_menu'] )
                                                                    {
                                                                        $args['theme_location'] = $group['new_menu'];
                                                                        $locations = get_nav_menu_locations();
                                                                        $args['menu'] = $locations[$group['new_menu']];
                                                                    }
                                                                    
								    return apply_filters(
                                                                            'dmm_custom_menu_url',
                                                                            $args,
                                                                            $menu_replace, $locations, $current_page, $url_groups
                                                                        );
								}
							}
						}
					}
				} 
			}
			
			if( is_archive() ) {
				global $wp_query;
				$tax = $wp_query->get_queried_object();
				$t_id = $tax->term_id;
    			$cat_meta = get_option( "category_$t_id");
				
				if( $cat_meta['replaced_menu'] != '' && $cat_meta['new_menu'] != '' )
                                {
                                    if( $args['theme_location'] == $cat_meta['replaced_menu'] )
                                    {
                                        $args['theme_location'] = $cat_meta['new_menu'];
                                        $locations = get_nav_menu_locations();
                                        $args['menu'] = $locations[$cat_meta['new_menu']];
                                    }
                                }
				
				return apply_filters(
                                            'dmm_custom_menu_cat',
                                            $args,
                                            $menu_replace, $locations, $current_page, $url_groups
                                        );
			}
			
			if( is_page() || is_single() ) {
				
				global $post;
				$taxes = get_object_taxonomies( $post->post_type );
				
				foreach( $taxes as $tax ) {
					$terms = wp_get_post_terms( $post->ID, $tax );
					
					foreach( $terms as $term ) {
						$t_id = $term->term_id;
						$cat_meta = get_option( "category_$t_id" );
					
						if( $cat_meta['replaced_menu'] != '' && $cat_meta['new_menu'] != '' ) {
                                                    if( $args['theme_location'] == $cat_meta['replaced_menu'] )
                                                    {
                                                        $args['theme_location'] = $cat_meta['new_menu'];
                                                        $locations = get_nav_menu_locations();
                                                        $args['menu'] = $locations[$cat_meta['new_menu']];
                                                    }
						    return apply_filters(
                                                        'dmm_custom_menu_tax',
                                                        $args,
                                                        $menu_replace, $locations, $current_page, $url_groups
                                                    );
						}
					}
				}
				
				$menu_replace = get_post_meta( $post->ID, 'menu_replace', true );
				$replaced_menu = get_post_meta( $post->ID, 'replaced_menu', true );
				$new_menu = get_post_meta( $post->ID, 'new_menu', true );
				
				if( $menu_replace == 'yes' ) {
					if( $replaced_menu != '' && $new_menu != '' ){
                                            if( $args['theme_location'] == $replaced_menu )
                                            {
                                                $args['theme_location'] = $new_menu;
                                                $locations = get_nav_menu_locations();
                                                $args['menu'] = $locations[$new_menu];
                                            }
					}
					return apply_filters(
                                            'dmm_custom_menu_single',
                                            $args,
                                            $menu_replace, $locations, $current_page, $url_groups
                                        );
				}
			}	
			return apply_filters(
                                    'dmm_custom_menu_default',
                                    $args,
                                    $menu_replace
                                );
		}
                
		public function curPageURL() {
			$pageURL = 'http';
			if (isset( $_SERVER["HTTPS"] ) && $_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
			$pageURL .= "://";
			if ($_SERVER["SERVER_PORT"] != "80") {
				$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];	
			} else {
				$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
			}
			return $pageURL;
		}
		
		public function get_registered_nav() {
			$this->main_locations = get_registered_nav_menus();
		}
		
		public function set_menu_field_for_tax() {
			$taxes = get_taxonomies();
			foreach( $taxes as $key => $tax ) {
				add_action( "{$key}_edit_form_fields", array($this, 'tax_edit_form_fields'));
				add_action ( "edited_{$key}", array($this, 'save_extra_tax_fileds'));
			}
		}
		
		/*
		 * Tag edit form field
		 */
		public function tax_edit_form_fields( $tag ) {
			
			$dmm_menus = $this->get_menus();
			foreach( $dmm_menus as $dmm_menu ) {
				unset( $this->main_locations[$dmm_menu['menu_location']] );
			}
			
			$t_id = $tag->term_id;
    		$cat_meta = get_option( "category_$t_id");
			?>
			<tr class="form-field">
				<th scope="row"><label for="meny_selection"><?php _e( 'Replace menu for this category', 'dmm' ) ?></label></th>
				<td>
					<?php _e( 'Select replaced menu', 'dmm' ) ?><br>
					<select name="dmm_tax[replaced_menu]">
						<option value=""></option>
						<?php foreach( $this->main_locations as $main_location => $menu_desc ){ ?>
							<option <?php echo $main_location == $cat_meta['replaced_menu'] ? 'selected' : '' ?> value="<?php echo $main_location ?>"><?php echo $menu_desc ?></option>
						<?php } ?>
					</select>
					<br>
					<?php _e( 'Select new menu', 'dmm' ) ?><br>
					<select name="dmm_tax[new_menu]">
						<option value=""></option>
						<?php foreach( $dmm_menus as $dmm_menu ) { ?>
							<option <?php echo $dmm_menu['menu_location'] == $cat_meta['new_menu'] ? 'selected' : '' ?> value="<?php echo $dmm_menu['menu_location'] ?>"><?php echo $dmm_menu['menu_desc'] ?></option>
						<?php } ?>
					</select>
                                        <?php do_action( 'dmm_menu_cat_meta', $tag, $dmm_menus, $this->main_locations ); ?>
				</td>
			</tr>
			<?php
		}
		
		/*
		 * Save extra tag fields
		 */
		public function save_extra_tax_fileds( $term_id ) {
			if( isset( $_POST['dmm_tax'] ) ) {
				$t_id = $term_id;
		        $cat_meta = get_option( "category_$t_id");
		        $cat_keys = array_keys($_POST['dmm_tax']);
		            foreach ($cat_keys as $key){
		            if (isset($_POST['dmm_tax'][$key])){
		                $cat_meta[$key] = $_POST['dmm_tax'][$key];
		            }
		        }
		        //save the option array
		        update_option( "category_$t_id", $cat_meta );
                        
                        do_action( 'dmm_cat_meta_save', $term_id );
			}
		}


		/**
		 * Shortcode
		 */
		public function dmm_menu_loc_cb( $atts ){
			$data = shortcode_atts( array(
				'theme_location'  => '',
				'menu'            => '',
				'container'       => 'div',
				'container_class' => '',
				'container_id'    => '',
				'menu_class'      => 'menu',
				'menu_id'         => '',
				'echo'            => true,
				'fallback_cb'     => 'wp_page_menu',
				'before'          => '',
				'after'           => '',
				'link_before'     => '',
				'link_after'      => '',
				'items_wrap'      => '<ul id="%1$s" class="%2$s">%3$s</ul>',
				'depth'           => 0,
				'walker'          => ''
			), $atts );

			if( $data['theme_location'] == '' || ! isset( $data['theme_location'] ) ) return 'Please use a location attribute in the shortcode. For more information, please visit Dashboard > DuoGeek > Help';


			ob_start();
			wp_nav_menu( $data );
			$menu = ob_get_contents();
			ob_end_clean();

			return $menu;

		}


		/**
		 * Help menu data
		 */
		public function dmm_help_cb( $arr ) {
			$arr[] = array(
				'name'          => __( 'Dynamic Menu Manager' ),
				'shortcodes'    => array(
					array(
						'source'			=> __( 'Duo FAQ PLugin', 'dmm' ),
						'code'              => '[dmm_menu_loc]',
						'example'           => '<span class="code">[dmm_menu_loc theme_location="menu-location"]</span> or <span class="code">echo do_shortcode( \'[dmm_menu_loc theme_location="menu-location"]\' );</span>',
						'default'           => __( 'No default value. Without theme_location parameter, it won\'t work. Others available parameters are: menu, container, container_class, container_id, menu_class, menu_id, echo, fallback_cb, before, after, link_before, link_after, items_wrap, depth, walker. For more details, please visit <a href="http://codex.wordpress.org/Function_Reference/wp_nav_menu" target="_blank">here</a>.', 'dmm' ),
						'desc'              => __( 'You can show any menu in anywhere, even in your content. Even if you want to use at template file, just write like the above example.' , 'dmm' )
					),
				)
			);

			return $arr;
		}


		/**
		 * Menu plugin activation
		 */
		public function dmm_plugin_activate() {
			update_option( 'dmm_plugin_do_activation_redirect', true );
		}


		public function dmm_plugin_redirect() {
			if ( get_option( 'dmm_plugin_do_activation_redirect', false ) ) {
				delete_option( 'dmm_plugin_do_activation_redirect' );
				wp_redirect( admin_url( 'admin.php?page=dynamic-menu-manager' ) );
			}
		}

	}
	
	/*add_filter( 'dg_pro_licenses', function( $ltabs ) {
		$ltabs[] = 'Different Menu in Different Page';
		
		return $ltabs;
	} );
	
	add_action( 'dg_pro_license_form_different_menu_in_different_page', function() {
		
		if( isset( $_POST['dg_license'] ) )
		{
			update_option( 'dg_license', $_POST['dg_license'] );
		}
		
		$dg_license = ( array ) get_option( 'dg_license' );
		$dg_license_verify = ( array ) get_option( 'dg_license_verify' );
		
		if( isset( $_REQUEST['different_menu_in_different_page_verify'] ) )
		{
			$code_to_verify = $dg_license['different_menu_in_different_page'];
			$data = wp_remote_post(
						'https://duogeek.com/wp-admin/admin-ajax.php?action=license_checker&key=' . $code_to_verify
					);
			
			$res = json_decode( $data['body'] );
			
			if( $res->{"verify-purchase"}->item_id == 7857908 ) {
				echo 'Thank you for verification. Enjoy the plugin!';
				
				if( ! in_array( 'different_menu_in_different_page', $dg_license_verify ) )
				{
					$dg_license_verify[] = 'different_menu_in_different_page';
					update_option( 'dg_license_verify', $dg_license_verify );
				}
			}
			else{
				echo 'Sorry, your key is not verified. Please double check your license key.';
				
				if ( ( $key = array_search( 'different_menu_in_different_page', $dg_license_verify ) ) !== false ) {
					unset( $dg_license_verify[$key] );
					update_option( 'dg_license_verify', $dg_license_verify );
				}
			}
			
		}
		
		?>
		<div class="wrap">
			<h2>Verify Your License</h2>
			<form action="#" method="post">
				<table celspacing="5" cellpadding="5">
					<tr>
						<th>Your license key</th>
						<td><input type="text" name="dg_license[different_menu_in_different_page]" value="<?php echo isset( $dg_license['different_menu_in_different_page'] ) ? $dg_license['different_menu_in_different_page'] : '' ?>" size="50"></td>
						<td>
							<input type="submit" name="dg_license[submit]" value="Save" class="button button-primary">
						</td>
						<?php if( isset( $dg_license['different_menu_in_different_page'] ) && $dg_license['different_menu_in_different_page'] != '' ) { ?>
						<td>
							<input type="button" onclick="window.location.href='<?php echo add_query_arg( 'different_menu_in_different_page_verify', true ) ?>'" value="Verify" class="button button-primary">
						</td>
						<?php } ?>
						<td>
						<?php if( ! in_array( 'different_menu_in_different_page', $dg_license_verify ) ) { ?>
						Not Verified!
						<?php }else{ ?>
						Verified!
						<?php } ?>
						</td>
					</tr>
				</table>
			</form>
		</div>
		<?php
	} );
	
	function sample_admin_notice__success() {
		?>
		<div class="notice notice-success is-dismissible">
			<p><?php _e( 'Done!', 'sample-text-domain' ); ?></p>
		</div>
		<?php
	}
	add_action( 'admin_notices', function() {
		$dg_license_verify = ( array ) get_option( 'dg_license_verify' );
		if( ! in_array( 'different_menu_in_different_page', $dg_license_verify ) )
		{
			?>
			<div class="notice notice-success is-dismissible">
				<p><?php _e( 'Please verify your license key for <b>Different Menu in Different Pages</b> <a href="' . admin_url( 'admin.php?page=duogeek-pro-licenses&tab=different_menu_in_different_page' ) . '">here</a>.', 'sample-text-domain' ); ?></p>
			</div>
			<?php
		}
	} );*/

	$dmm = new DMM_Class();
	
}
