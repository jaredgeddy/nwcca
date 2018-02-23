<?php



if ( ! defined( 'ABSPATH' ) ) wp_die( __( 'Sorry hackers! This is not your place!', 'dp' ) );

if( ! defined( 'DUO_PLUGIN_DIR' ) ) define( 'DUO_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );


if( ! defined( 'DUO_MENU_POSITION' ) ) define( 'DUO_MENU_POSITION', '38' );
if( ! defined( 'DUO_PANEL_SLUG' ) ) define( 'DUO_PANEL_SLUG', 'duogeek-panel' );
if( ! defined( 'DUO_HELP_SLUG' ) ) define( 'DUO_HELP_SLUG', 'duogeek-panel-help' );
if( ! defined( 'DUO_LICENSES_SLUG' ) ) define( 'DUO_LICENSES_SLUG', 'duogeek-pro-licenses' );
if( ! defined( 'DUO_VERSION' ) ) define( 'DUO_VERSION', '1.2' );


if( ! class_exists( 'DuoGeekPlugins' ) ){

    /*
     * Framework Class
     */

    class DuoGeekPlugins{

        private $menuPos;

        protected $admin_enq = array();

        protected $front_enq = array();

        public $help = array();

        private $DuoOptions;

        protected $admin_pages = array();

        public function __construct() {

            $this->menuPos = DUO_MENU_POSITION;

            add_action( 'init', array( $this, 'DuoPlugin_init' ) );
            add_action( 'admin_menu', array( $this, 'register_duogeek_menu_page' ) );
            add_action( 'admin_menu', array( $this, 'register_duogeek_submenu_page' ) );
            add_action( 'admin_enqueue_scripts', array( $this, 'admin_styles_scripts' ) );
            add_action( 'wp_enqueue_scripts', array( $this, 'front_styles_scripts' ) );
            add_action( 'wp_footer', array( $this, 'dg_equal_column' ) );

            add_shortcode( 'dg_grid', array( $this, 'dg_grid_cb' ) );
            add_shortcode( 'dg_grid_class', array( $this, 'dg_grid_class_cb' ) );

        }

        public function DuoPlugin_init() {
            $this->DuoOptions = get_option( 'DuoOptions' );
            $this->admin_pages = apply_filters( 'duogeek_panel_pages', array() );
            $this->admin_pages = array_merge( $this->admin_pages, array( DUO_PANEL_SLUG,DUO_HELP_SLUG  ) );
        }

        public function admin_styles_scripts() {

            $styles = array(
                array(
                    'name' => 'duogeek-css',
                    'src' => DUO_PLUGIN_URI . 'duogeek/inc/duogeek.css',
                    'dep' => '',
                    'version' => DUO_VERSION,
                    'media' => 'all',
                    'condition' => true
                )
            );

            $scripts = array(
                array(
                    'name' => 'duogeek-js',
                    'src' => DUO_PLUGIN_URI . 'duogeek/inc/duogeek.js',
                    'dep' => array( 'jquery' ),
                    'version' => DUO_VERSION,
                    'footer' => true,
                    'condition' => true
                )
            );

            $this->admin_enq = apply_filters( 'admin_scripts_styles', array() );

            if( count( $this->admin_enq ) > 0 ){
                $this->admin_enq['scripts'] = array_merge( $scripts, $this->admin_enq['scripts'] );
                $this->admin_enq['styles'] = array_merge( $styles, $this->admin_enq['styles'] );
            }else{
                $this->admin_enq['scripts'] = $scripts;
                $this->admin_enq['styles'] = $styles;
            }


            foreach( $this->admin_enq['scripts'] as $script ){

                if( $script['name'] == 'media' ){
                    wp_enqueue_media();
                }

                if( $script['condition'] ){
                    if( isset( $script['src'] ) ) {
                        wp_register_script( $script['name'], $script['src'], $script['dep'], $script['version'], $script['footer'] );
                    }
                    wp_enqueue_script( $script['name'] );


                    if( isset( $script['localize'] ) ){
                        wp_localize_script( $script['name'], $script['localize_data']['object'], $script['localize_data']['passed_data'] );
                    }
                }

            }

            foreach( $this->admin_enq['styles'] as $style ){

                if( $style['condition'] ){
                    if( isset( $style['src'] ) ) {
                        wp_register_style( $style['name'], $style['src'], $style['dep'], $style['version'], $style['media'] );
                    }
                    wp_enqueue_style( $style['name'] );
                }

            }

        }


        public function front_styles_scripts() {

            $styles = array(
                array(
                    'name' => 'dg-grid-css',
                    'src' => DUO_PLUGIN_URI . 'duogeek/inc/dg-grid.css',
                    'dep' => '',
                    'version' => DUO_VERSION,
                    'media' => 'all',
                    'condition' => true
                )
            );

            $scripts = array();

            $this->front_enq = apply_filters( 'front_scripts_styles', array() );

            if( count( $this->front_enq ) > 0 ){
                $this->front_enq['scripts'] = array_merge( $scripts, $this->front_enq['scripts'] );
                $this->front_enq['styles'] = array_merge( $styles, $this->front_enq['styles'] );
            }
            else{
                $this->front_enq['scripts'] = $scripts;
                $this->front_enq['styles'] = $styles;
            }


            foreach( $this->front_enq['scripts'] as $script ){

                if( $script['name'] == 'media' ){
                    wp_enqueue_media();
                }

                if( $script['condition'] ){
                    if( isset( $script['src'] ) ) {
                        wp_register_script( $script['name'], $script['src'], $script['dep'], $script['version'], $script['footer'] );
                    }
                    wp_enqueue_script( $script['name'] );


                    if( isset( $script['localize'] ) ){
                        wp_localize_script( $script['name'], $script['localize_data']['object'], $script['localize_data']['passed_data'] );
                    }
                }

            }

            foreach( $this->front_enq['styles'] as $style ){

                if( $style['condition'] ){
                    if( isset( $style['src'] ) ) {
                        wp_register_style( $style['name'], $style['src'], $style['dep'], $style['version'], $style['media'] );
                    }
                    wp_enqueue_style( $style['name'] );
                }

            }

        }


        public function register_duogeek_menu_page()
        {
            if( empty( $GLOBALS['admin_page_hooks']['duogeek-panel'] ) ) {
                add_menu_page(__('DuoGeek', 'dp'), __('DuoGeek', 'dp'), 'manage_options', DUO_PANEL_SLUG, array($this, 'duogeek_panel_cb'), '', $this->menuPos);
            }
        }


        public function duogeek_panel_cb() {
            $promo_content = wp_remote_get( 'http://duogeek.com/duo-promo.html' );
            ?>
            <div class="wrap duo_prod_panel">
                <?php echo $promo_content['body']; ?>
            </div>
        <?php
        }


        public function register_duogeek_submenu_page() {

            $submenus = apply_filters( 'duogeek_submenu_pages', array() );

            if( count( $submenus ) > 0 ) {
                foreach( $submenus as $submenu ){
                    if( isset( $submenu['object'] ) )
                        add_submenu_page( DUO_PANEL_SLUG, $submenu['title'], $submenu['menu_title'], $submenu['capability'], $submenu['slug'], array( $submenu['object'], $submenu['function'] ) );
                    else
                        add_submenu_page( DUO_PANEL_SLUG, $submenu['title'], $submenu['menu_title'], $submenu['capability'], $submenu['slug'], $submenu['function'] );
                }
            }

            add_submenu_page( DUO_PANEL_SLUG, __( 'Help', 'dp' ), __( 'Help', 'dp' ), 'manage_options', DUO_HELP_SLUG, array( $this, 'duogeek_panel_help_cb' ) );

            add_submenu_page( DUO_PANEL_SLUG, __( 'Licenses', 'dp' ), __( 'Licenses', 'dp' ), 'manage_options', DUO_LICENSES_SLUG, array( $this, 'duogeek_panel_licenses_cb' ) );
        }


        public function duogeek_panel_help_cb() {

            $this->help = array(
                'shortcodes'    => apply_filters( 'duo_panel_help_shortcodes', array( ) ),
                'filters'       => apply_filters( 'duo_panel_help_filters', array( ) ),
                'actions'       => apply_filters( 'duo_panel_help_actions', array( ) ),
                'tips'          => apply_filters( 'duo_panel_help_tips', array( ) ),
            );

            $this->help = apply_filters( 'duo_panel_help', array( ) );

            ?>
            <div class="wrap duo-kb">
                <h2><?php _e( 'Help', 'dp' ) ?></h2>
                <?php foreach( $this->help as $key => $helps ) { ?>
                    <div id="poststuff">
                        <div class="postbox">
                            <h3 class="hndle"><?php echo $helps['name'] ?> <span><?php _e( 'Click to expand/collapse', 'dp' ) ?></span></h3>
                            <div class="inside">
                                <div class="duo_help">
                                    <ul>
                                        <?php foreach( $helps as $key => $help ){ if( $key == 'name' ) continue; ?>
                                            <li>
                                                <h5><?php echo ucfirst( $key ) ?></h5>
                                                <div class="item_details">
                                                    <ul>
                                                        <?php foreach( $help as $details ){ ?>
                                                            <li>

                                                                <?php if( isset( $details['source'] ) ) { ?>
                                                                    <p>
                                                                        <b>
                                                                            <?php
                                                                            _e( 'Source: ', 'dp' );
                                                                            echo $details['source'];
                                                                            ?>
                                                                        </b>
                                                                    </p>
                                                                <?php } ?>

                                                                <?php if( isset( $details['code'] ) ) { ?>
                                                                    <p>
                                                                        <?php
                                                                        echo '<b>';
                                                                        _e( 'Code: ', 'dp' );
                                                                        echo '</b>';
                                                                        echo '<span class="code">' . $details['code'] . '</span>';
                                                                        ?>
                                                                    </p>
                                                                <?php } ?>

                                                                <?php if( isset( $details['example'] ) ) { ?>
                                                                    <p>
                                                                        <?php
                                                                        echo '<b>';
                                                                        _e( 'Example: ', 'dp' );
                                                                        echo '</b>';
                                                                        echo $details['example'];
                                                                        ?>
                                                                    </p>
                                                                <?php } ?>

                                                                <?php if( isset( $details['default'] ) ) { ?>
                                                                    <p>
                                                                        <?php
                                                                        echo '<b>';
                                                                        _e( 'Default: ', 'dp' );
                                                                        echo '</b>';
                                                                        echo $details['default'];
                                                                        ?>
                                                                    </p>
                                                                <?php } ?>

                                                                <?php if( isset( $details['desc'] ) ) { ?>
                                                                    <p>
                                                                        <?php
                                                                        echo '<b>';
                                                                        _e( 'Description: ', 'dp' );
                                                                        echo '</b>';
                                                                        echo $details['desc'];
                                                                        ?>
                                                                    </p>
                                                                <?php } ?>

                                                            </li>
                                                        <?php } ?>
                                                    </ul>
                                                </div>
                                            </li>
                                        <?php } ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
        <?php
        }


        public function duogeek_panel_licenses_cb() {
            ?>

            <div class="wrap">

            <?php

            $ltabs = apply_filters( 'dg_pro_licenses', array() );

            if( count( $ltabs ) < 1 ){
                echo '<p>You don\'t have any pro version yet!</p>';
            }else{
                echo '<h2 class="nav-tab-wrapper">';
                foreach( $ltabs as $ltab ){

                    $active = '';
                    if( ! isset( $_REQUEST['tab'] ) || $_REQUEST['tab'] == strtolower( str_replace( ' ', '_', $ltab ) ) ){
                        $active = 'nav-tab-active';
                    }

                    echo '<a class="nav-tab '. $active .'" href="' . admin_url( 'admin.php?page=' . DUO_LICENSES_SLUG . '&tab=' . strtolower( str_replace( ' ', '_', $ltab ) ) ) . '">' . $ltab . '</a>';

                }
                echo '</h2>';

                echo '<div class="lisence_wrap">';

                $tab = strtolower( str_replace( ' ', '_', isset( $_REQUEST['tab'] ) ? $_REQUEST['tab'] : $ltabs[0] ) );

                if( ! isset( $_REQUEST['tab'] ) || $_REQUEST['tab'] == $tab ){
                    do_action( 'dg_pro_license_form_' . $tab );
                }

                echo '</div>';

            }
            ?>
            </div>
            <?php

        }


        public function dg_grid_cb( $atts, $content = '' ){
            return '<div class="dg-grid dg-grid-shortcode">' . do_shortcode( $content ) . '</div>';
        }

        public function dg_grid_class_cb( $atts, $content = '' ){
            $atts = shortcode_atts( array(
                'desktop' => '1-1',
                'ipad' => '1-1',
                'ipad_mini' => '1-1',
                'mobile' => '1-1'
            ), $atts, 'dg_grid_class' );

            return '<div class="dg_grid-shortcode-col dg-col-'. $atts['desktop'] .' dg-col-md-'. $atts['ipad'] .' dg-col-sm-'. $atts['ipad_mini'] .' dg-col-xx-'. $atts['mobile'] .'">' . do_shortcode( $content ) . '</div>';

        }

        public function dg_equal_column() {
            ?>
            <script type="text/javascript">
                jQuery(function($) {
                    function equalHeight(group) {
                        tallest = 0;
                        group.each(function() {
                            thisHeight = $(this).height();
                            if(thisHeight > tallest) {
                                tallest = thisHeight;
                            }
                        });
                        group.height(tallest);
                    }

                    equalHeight($(".dg-grid-shortcode .dg_grid-shortcode-col"));

                    $(window).resize(function() {
                        equalHeight($(".dg-grid-shortcode .dg_grid-shortcode-col"));
                    });
                });
            </script>
            <?php
        }

    }

    new DuoGeekPlugins();

    require_once 'helper.php';
    require_once 'class.customPostType.php';

}
