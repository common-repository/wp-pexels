<?php
/*
  Plugin Name: WPC Pexels
  Plugin URI: https://wpclever.net/
  Description: WPC Pexels help you search over million free photos from https://pexels.com then insert to content or set as featured image very quickly.
  Version: 2.1.8
  Author: WPClever.net
  Author URI: https://wpclever.net
  Text Domain: wppx
  Domain Path: /languages/
  Requires at least: 4.0
  Tested up to: 5.4
 */

defined( 'ABSPATH' ) || exit;

! defined( 'WPPX_VERSION' ) && define( 'WPPX_VERSION', '2.1.8' );
! defined( 'WPPX_URI' ) && define( 'WPPX_URI', plugin_dir_url( __FILE__ ) );
! defined( 'WPPX_REVIEWS' ) && define( 'WPPX_REVIEWS', 'https://wordpress.org/support/plugin/wp-pexels/reviews/?filter=5' );
! defined( 'WPPX_CHANGELOG' ) && define( 'WPPX_CHANGELOG', 'https://wordpress.org/plugins/wp-pexels/#developers' );
! defined( 'WPPX_DISCUSSION' ) && define( 'WPPX_DISCUSSION', 'https://wordpress.org/support/plugin/wp-pexels' );
! defined( 'WPC_URI' ) && define( 'WPC_URI', WPPX_URI );

include( 'includes/wpc-menu.php' );
include( 'includes/wpc-dashboard.php' );

if ( ! class_exists( 'WPCleverWppx' ) ) {
	class WPCleverWppx {
		function __construct() {
			add_action( 'plugins_loaded', array( $this, 'wppx_load_textdomain' ) );
			add_action( 'admin_menu', array( $this, 'wppx_menu' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'wppx_load_scripts' ) );
			add_filter( 'plugin_action_links', array( $this, 'wppx_settings_link' ), 10, 2 );
			add_action( 'wp_ajax_wppx_search', array( $this, 'wppx_search_ajax' ) );
			add_action( 'wp_ajax_nopriv_wppx_search', array( $this, 'wppx_search_ajax' ) );
			add_action( 'media_buttons', array( $this, 'wppx_add_button' ) );
			add_action( 'admin_footer', array( $this, 'wppx_area_content' ) );
			add_action( 'save_post', array( $this, 'wppx_save_post_data' ), 10, 3 );

			// media tabs
			add_filter( 'media_upload_tabs', array( $this, 'wppx_media_upload_tabs' ) );
			add_action( 'media_upload_wppx', array( $this, 'wppx_media_upload_iframe' ) );
		}

		function wppx_load_textdomain() {
			load_plugin_textdomain( 'wppx', false, basename( dirname( __FILE__ ) ) . '/languages/' );
		}

		function wppx_media_upload_tabs( $tabs ) {
			$tabs['wppx'] = esc_html__( 'Pexels', 'wppx' );

			return ( $tabs );
		}

		function wppx_media_upload_iframe() {
			return wp_iframe( array( $this, 'wppx_media_upload_content' ) );
		}

		function wppx_media_upload_content() {
			media_upload_header();
			self::wppx_area();
		}

		function wppx_menu() {
			add_submenu_page( 'upload.php', esc_html__( 'WPC Pexels', 'wppx' ), esc_html__( 'Pexels', 'wppx' ), 'manage_options', 'media-wppx', array(
				&$this,
				'wppx_menu_media'
			) );
			add_submenu_page( 'wpclever', esc_html__( 'WPC Pexels', 'wppx' ), esc_html__( 'WPC Pexels', 'wppx' ), 'manage_options', 'wpclever-wppx', array(
				&$this,
				'wppx_menu_settings'
			) );
		}

		function wppx_menu_media() {
			?>
            <div class="wppx_media_container">
                <div class="wppx_header">
                    <h1><?php echo esc_html__( 'WPC Pexels', 'wppx' ); ?>
                        <span><?php esc_html_e( 'You can search & save the image(s) to Media Library.', 'wppx' ); ?></span>
                    </h1>
                </div>
				<?php self::wppx_area(); ?>
            </div>
			<?php
		}

		function wppx_menu_settings() {
			$page_slug  = 'wpclever-wppx';
			$active_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'how';
			?>
            <div class="wpclever_settings_page wrap">
                <h1 class="wpclever_settings_page_title"><?php echo esc_html__( 'WPC Pexels', 'wppx' ) . ' ' . WPPX_VERSION; ?></h1>
                <div class="wpclever_settings_page_desc about-text">
                    <p>
						<?php printf( esc_html__( 'Thank you for using our plugin! If you are satisfied, please reward it a full five-star %s rating.', 'wppx' ), '<span style="color:#ffb900">&#9733;&#9733;&#9733;&#9733;&#9733;</span>' ); ?>
                        <br/>
                        <a href="<?php echo esc_url( WPPX_REVIEWS ); ?>"
                           target="_blank"><?php esc_html_e( 'Reviews', 'wppx' ); ?></a> | <a
                                href="<?php echo esc_url( WPPX_CHANGELOG ); ?>"
                                target="_blank"><?php esc_html_e( 'Changelog', 'wppx' ); ?></a>
                        | <a href="<?php echo esc_url( WPPX_DISCUSSION ); ?>"
                             target="_blank"><?php esc_html_e( 'Discussion', 'wppx' ); ?></a>
                    </p>
                </div>
                <div class="wpclever_settings_page_nav">
                    <h2 class="nav-tab-wrapper">
                        <a href="?page=<?php echo $page_slug; ?>&amp;tab=how"
                           class="nav-tab <?php echo $active_tab == 'how' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'How to use?', 'wppx' ); ?></a>
                        <a href="?page=<?php echo $page_slug; ?>&amp;tab=premium"
                           class="nav-tab <?php echo $active_tab == 'premium' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Premium Version', 'wppx' ); ?></a>
                    </h2>
                </div>
                <div class="wpclever_settings_page_content">
					<?php if ( $active_tab == 'how' ) { ?>
                        <div class="wpclever_settings_page_content_text">
                            <p><?php esc_html_e( '1. Press the "Pexels" button above editor', 'wppx' ); ?></p>
                            <p><img src="<?php echo WPPX_URI; ?>assets/images/how-01.jpg"/></p>

                            <p><?php esc_html_e( '2. Type any key to search', 'wppx' ); ?></p>
                            <p><img src="<?php echo WPPX_URI; ?>assets/images/how-02.jpg"/></p>

                            <p><?php esc_html_e( '3. Choose the photo as you want then insert or set featured', 'wppx' ); ?></p>
                            <p><img src="<?php echo WPPX_URI; ?>assets/images/how-03.jpg"/></p>
                        </div>
					<?php } elseif ( $active_tab == 'premium' ) { ?>
                        <div class="wpclever_settings_page_content_text">
                            <p>Get the Premium Version just $29! <a
                                        href="https://wpclever.net/downloads/wp-pexels?utm_source=premium&utm_medium=wpxs&utm_campaign=wporg"
                                        target="_blank">https://wpclever.net/downloads/wp-pexels</a>
                            </p>
                            <p><strong>Extra features for Premium Version:</strong></p>
                            <ul style="margin-bottom: 0">
                                <li>- Upload image(s) to Media Library.</li>
                                <li>- Get lifetime update & premium support.</li>
                            </ul>
                        </div>
					<?php } ?>
                </div>
            </div>
			<?php
		}

		function wppx_load_scripts() {
			wp_enqueue_script( 'colorbox', WPPX_URI . 'assets/js/jquery.colorbox.js', array( 'jquery' ), WPPX_VERSION );
			wp_enqueue_style( 'colorbox', WPPX_URI . 'assets/css/colorbox.css' );
			wp_enqueue_style( 'wppx', WPPX_URI . 'assets/css/backend.css' );
			wp_enqueue_script( 'wppx', WPPX_URI . 'assets/js/backend.js', array( 'jquery' ), WPPX_VERSION, true );
			wp_localize_script( 'wppx', 'wppx_vars', array(
				'wppx_username'  => get_option( 'wppx_username' ) ? get_option( 'wppx_username' ) : 'baby2j',
				'wppx_key'       => get_option( 'wppx_key' ) ? get_option( 'wppx_key' ) : '1485725-fcbfa6badf33d350b5eb4670a',
				'wppx_ajax_url'  => admin_url( 'admin-ajax.php' ),
				'wppx_media_url' => admin_url( 'upload.php' ),
				'wppx_nonce'     => wp_create_nonce( 'wppx_nonce' )
			) );
		}

		function wppx_settings_link( $links, $file ) {
			static $plugin;

			if ( ! isset( $plugin ) ) {
				$plugin = plugin_basename( __FILE__ );
			}

			if ( $plugin == $file ) {
				$links[] = '<a href="' . admin_url( 'admin.php?page=wpclever-wppx&tab=premium' ) . '">' . esc_html__( 'Premium Version', 'wppx' ) . '</a>';
			}

			return $links;
		}

		function wppx_search_ajax() {
			if ( ! isset( $_POST['wppx_nonce'] ) || ! wp_verify_nonce( $_POST['wppx_nonce'], 'wppx_nonce' ) ) {
				die( esc_html__( 'Permissions check failed', 'wppx' ) );
			}

			$ch   = curl_init();
			$page = isset( $_POST['page'] ) ? $_POST['page'] : 1;

			if ( isset( $_POST['key'] ) ) {
				curl_setopt( $ch, CURLOPT_URL, 'http://api.pexels.com/v1/search?query=' . esc_attr( $_POST['key'] ) . '&per_page=12&page=' . $page );
			} else {
				curl_setopt( $ch, CURLOPT_URL, 'http://api.pexels.com/v1/popular?per_page=8&page=1' );
			}

			curl_setopt( $ch, CURLOPT_HTTPHEADER, array(
				'Authorization: 563492ad6f91700001000001f27710937a744dc14b607b8c6d8d72d5',
				'Content-Type: application/json'
			) );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
			echo curl_exec( $ch );
			curl_close( $ch );
			die();
		}

		function wppx_add_button( $editor_id ) {
			echo ' <a href="#wppx_area" id="wppx_btn" data-editor="' . $editor_id . '" class="wppx_btn button add_media" title="Pexels">' . esc_html__( 'Pexels', 'wppx' ) . '</a><input type="hidden" class="wppx_featured_url" name="wppx_featured_url" value="" /><input type="hidden" class="wppx_featured_title" name="wppx_featured_title" value="" /><input type="hidden" class="wppx_featured_caption" name="wppx_featured_caption" value="" /> ';
		}

		function wppx_save_post_data( $post_id, $post ) {
			if ( isset( $post->post_status ) && 'auto-draft' == $post->post_status ) {
				return;
			}

			if ( wp_is_post_revision( $post_id ) ) {
				return;
			}

			if ( ! empty( $_POST['wppx_featured_url'] ) ) {
				if ( strstr( $_SERVER['REQUEST_URI'], 'wp-admin/post-new.php' ) || strstr( $_SERVER['REQUEST_URI'], 'wp-admin/post.php' ) ) {
					if ( 'page' == $_POST['post_type'] ) {
						if ( ! current_user_can( 'edit_page', $post_id ) ) {
							return;
						}
					} else {
						if ( ! current_user_can( 'edit_post', $post_id ) ) {
							return;
						}
					}

					$wppx_url     = sanitize_text_field( $_POST['wppx_featured_url'] );
					$wppx_title   = sanitize_text_field( $_POST['wppx_featured_title'] );
					$wppx_caption = sanitize_text_field( $_POST['wppx_featured_caption'] );
					self::wppx_save_featured( $wppx_url, $wppx_title, $wppx_caption );
				}
			}
		}

		function wppx_save_featured( $file_url, $title = null, $caption = null ) {
			global $post;

			if ( ! function_exists( 'media_handle_upload' ) ) {
				require_once( ABSPATH . 'wp-admin' . '/includes/image.php' );
				require_once( ABSPATH . 'wp-admin' . '/includes/file.php' );
				require_once( ABSPATH . 'wp-admin' . '/includes/media.php' );
			}

			$thumb_id  = 0;
			$post_data = array(
				'post_title'   => $title,
				'post_excerpt' => $caption
			);
			$filename  = pathinfo( $file_url, PATHINFO_FILENAME );
			@set_time_limit( 300 );

			if ( ! empty( $file_url ) ) {
				$tmp                    = download_url( $file_url );
				$ext                    = pathinfo( $file_url, PATHINFO_EXTENSION );
				$file_array['name']     = $filename . '.' . $ext;
				$file_array['tmp_name'] = $tmp;

				if ( is_wp_error( $tmp ) ) {
					@unlink( $file_array['tmp_name'] );
					$file_array['tmp_name'] = '';
				}

				$thumb_id = media_handle_sideload( $file_array, $post->ID, $desc = null, $post_data );

				if ( is_wp_error( $thumb_id ) ) {
					@unlink( $file_array['tmp_name'] );

					return $thumb_id;
				}
			}

			set_post_thumbnail( $post, $thumb_id );
		}

		function wppx_area_content() { ?>
            <div style='display:none'>
				<?php self::wppx_area( true ); ?>
            </div>
		<?php }

		function wppx_area( $full = false ) {
			?>
            <div id="wppx_area" class="wppx_area">
                <div class="wppx_area_content">
                    <div class="wppx_area_content_col wppx_area_content_left">
                        <div class="wppx_area_content_col_inner">
                            <div class="wppx_area_content_col_top">
                                <input type="text" id="wppx_input" name="wppx_input" class="w300"
                                       placeholder="<?php esc_html_e( 'keyword', 'wppx' ); ?>"/>
                                <input type="button" id="wppx_search" class="p20"
                                       value="<?php esc_html_e( 'Search', 'wppx' ); ?>"/>
                            </div>
                            <div class="wppx_area_content_col_mid">
                                <div id="wppx_container" class="wppx_container"></div>
                            </div>
                            <div class="wppx_area_content_col_bot">
                                <div id="wppx_page" class="wppx_page"></div>
                            </div>
                        </div>
                    </div>
                    <div class="wppx_area_content_col wppx_area_content_right">
                        <div id="wppx_use_image" class="wppx_area_content_right_inner wppx_area_content_col_inner">
                            <div class="wppx_area_content_col_mid">
                                <div id="wppx_view"></div>
                                <div class="wppx_item_info">
                                    <div><?php esc_html_e( 'Title', 'wppx' ); ?></div>
                                    <div>
                                        <input type="text" id="wppx_title"
                                               placeholder="<?php esc_html_e( 'title', 'wppx' ); ?>"/>
                                    </div>
                                </div>
                                <div class="wppx_item_info">
                                    <div><?php esc_html_e( 'Caption', 'wppx' ); ?></div>
                                    <div>
                                        <textarea id="wppx_caption" name="wppx_caption"></textarea>
                                    </div>
                                </div>
                                <div class="wppx_item_info">
                                    <div><?php esc_html_e( 'File name', 'wppx' ); ?></div>
                                    <div>
                                        <select name="wppx_filename" id="wppx_filename" class="wppx_select">
                                            <option
                                                    value="0"><?php esc_html_e( 'Keep original file name', 'wppx' ); ?></option>
                                            <option
                                                    value="1"><?php esc_html_e( 'Generate from title', 'wppx' ); ?></option>
                                        </select>
                                    </div>
                                </div>
								<?php if ( $full ) { ?>
                                    <div class="wppx_item_info">
                                        <div><?php esc_html_e( 'Size', 'wppx' ); ?></div>
                                        <div>
                                            <input type="number" id="wppx_width" value="0"
                                                   class="wppx_input wppx_input_small"
                                                   placeholder="<?php esc_html_e( 'width', 'wppx' ); ?>"/>
                                            <input
                                                    type="number" id="wppx_height" value="0"
                                                    class="wppx_input wppx_input_small"
                                                    placeholder="<?php esc_html_e( 'height', 'wppx' ); ?>"/>
                                        </div>
                                    </div>
                                    <div class="wppx_item_info">
                                        <div><?php esc_html_e( 'Alignment', 'wppx' ); ?></div>
                                        <div>
                                            <select name="wppx_align" id="wppx_align" class="wppx_select">
                                                <option
                                                        value="alignnone"><?php esc_html_e( 'None', 'wppx' ); ?>
                                                </option>
                                                <option
                                                        value="alignleft"><?php esc_html_e( 'Left', 'wppx' ); ?>
                                                </option>
                                                <option
                                                        value="alignright"><?php esc_html_e( 'Right', 'wppx' ); ?>
                                                </option>
                                                <option
                                                        value="aligncenter"><?php esc_html_e( 'Center', 'wppx' ); ?>
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="wppx_item_info">
                                        <div><?php esc_html_e( 'Link to', 'wppx' ); ?></div>
                                        <div>
                                            <select name="wppx_link" id="wppx_link" class="wppx_select">
                                                <option
                                                        value="0"><?php esc_html_e( 'None', 'wppx' ); ?></option>
                                                <option
                                                        value="1"><?php esc_html_e( 'Original site', 'wppx' ); ?></option>
                                                <option
                                                        value="2"><?php esc_html_e( 'Original image', 'wppx' ); ?></option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="wppx_item_info">
                                        <div>&nbsp;</div>
                                        <div>
                                            <input name="wppx_blank" id="wppx_blank" type="checkbox"
                                                   class="wppx_checkbox"/> <?php esc_html_e( 'Open in new windows', 'wppx' ); ?>
                                        </div>
                                    </div>
                                    <div class="wppx_item_info">
                                        <div>&nbsp;</div>
                                        <div>
                                            <input name="wppx_nofollow" id="wppx_nofollow" type="checkbox"
                                                   class="wppx_checkbox"/> <?php esc_html_e( 'Rel nofollow', 'wppx' ); ?>
                                        </div>
                                    </div>
								<?php } ?>
                            </div>
                            <div class="wppx_area_content_col_bot">
                                <div class="wppx_notice">
                                    <div id="wppx_loading_text"
                                         class="wppx_loading_text"><?php esc_html_e( 'Saving to Media Library...', 'wppx' ); ?></div>
                                    <div id="wppx_error" class="wppx_error"></div>
                                </div>
								<?php if ( $full ) { ?>
                                    <div class="wppx_actions">
                                        <div>
                                            <input type="hidden" id="wppx_site"/>
                                            <input type="hidden" id="wppx_url"/>
                                            <input type="hidden" id="wppx_editor_id"/>
                                            <button id="wppx_insert">
												<?php esc_html_e( 'Insert', 'wppx' ); ?><span></span>
                                            </button>
                                        </div>
                                        <div>
                                            <a href="https://wpclever.net/downloads/wp-pexels?utm_source=premium&utm_medium=wpxs&utm_campaign=wporg"
                                               target="_blank"
                                               onclick="return confirm('This feature only available in Premium Version!\nBuy it now? Just $29')">
                                                <button id="wppx_save" class="disable">
													<?php esc_html_e( 'Save&Insert', 'wppx' ); ?><span></span>
                                                </button>
                                            </a>
                                        </div>
                                        <div>
                                            <button id="wppx_featured">
												<?php esc_html_e( 'Featured', 'wppx' ); ?>
                                            </button>
                                        </div>
                                    </div>
								<?php } else { ?>
                                    <div class="wppx_actions one_button">
                                        <div>
                                            <a href="https://wpclever.net/downloads/wp-pexels?utm_source=premium&utm_medium=wpxs&utm_campaign=wporg"
                                               target="_blank"
                                               onclick="return confirm('This feature only available in Premium Version!\nBuy it now? Just $29')">
                                                <button id="wppx_save_only" class="disable">
													<?php esc_html_e( 'Save to Media Library', 'wppx' ); ?><span></span>
                                                </button>
                                            </a>
                                        </div>
                                    </div>
								<?php } ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
			<?php
		}
	}

	new WPCleverWppx();
}