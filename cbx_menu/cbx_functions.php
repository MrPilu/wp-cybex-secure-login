<?php
/*
* General functions for CybexSecurity plugins
*/

require( dirname( __FILE__ ) . '/deprecated.php' );
require_once( dirname( __FILE__ ) . '/deactivation-form.php' );

/**
 * Function to add 'xcellorate' slug for cbx_Menu MO file if cbx_Menu loaded from theme.
 *
 * @since 1.9.7
 */
if ( ! function_exists ( 'cbx_get_mofile' ) ) {
	function cbx_get_mofile( $mofile, $domain ) {
		if ( 'xcellorate' == $domain ) {
			$locale = get_locale();
			return str_replace( $locale, "xcellorate-{$locale}", $mofile );
		}

		return $mofile;
	}
}

/* Internationalization, first(!) */
if ( isset( $cbx_menu_source ) && 'themes' == $cbx_menu_source ) {
	add_filter( 'load_textdomain_mofile', 'cbx_get_mofile', 10, 2 );
	load_theme_textdomain( 'xcellorate', get_stylesheet_directory() . '/inc/cbx_menu/languages' );
	remove_filter( 'load_textdomain_mofile', 'cbx_get_mofile' );
} else {
	load_plugin_textdomain( 'xcellorate', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}

/**
 * Function to getting url to current cbx_Menu.
 *
 * @since 1.9.7
 */
if ( ! function_exists ( 'cbx_menu_url' ) ) {
	if ( ! isset( $cbx_menu_source ) || 'plugins' == $cbx_menu_source ) {
		function cbx_menu_url( $path = '' ) {
			return plugins_url( $path, __FILE__ );
		}
	} else {
		function cbx_menu_url( $path = '' ) {
			$cbx_menu_current_dir = str_replace( '\\', '/', dirname( __FILE__ ) );
			$cbx_menu_abspath = str_replace( '\\', '/', ABSPATH );
			$cbx_menu_current_url = site_url( str_replace( $cbx_menu_abspath, '', $cbx_menu_current_dir ) );

			return sprintf( '%s/%s', $cbx_menu_current_url, $path );
		}
	}
}

/**
* Function check if plugin is compatible with current WP version
* @return void
*/
if ( ! function_exists( 'cbx_wp_min_version_check' ) ) {
	function cbx_wp_min_version_check( $plugin_basename, $plugin_info, $require_wp, $min_wp = false ) {
		global $wp_version, $cbx_versions_notice_array;
		if ( false == $min_wp )
			$min_wp = $require_wp;
		if ( version_compare( $wp_version, $min_wp, "<" ) ) {
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			if ( is_plugin_active( $plugin_basename ) ) {
				deactivate_plugins( $plugin_basename );
				$admin_url = ( function_exists( 'get_admin_url' ) ) ? get_admin_url( null, 'plugins.php' ) : esc_url( '/wp-admin/plugins.php' );
				wp_die(
					sprintf(
						"<strong>%s</strong> %s <strong>WordPress %s</strong> %s <br /><br />%s <a href='%s'>%s</a>.",
						$plugin_info['Name'],
						__( 'requires', 'xcellorate' ),
						$require_wp,
						__( 'or higher, that is why it has been deactivated! Please upgrade WordPress and try again.', 'xcellorate' ),
						__( 'Back to the WordPress', 'xcellorate' ),
						$admin_url,
						__( 'Plugins page', 'xcellorate' )
					)
				);
			}
		} elseif ( version_compare( $wp_version, $require_wp, "<" ) ) {
			$cbx_versions_notice_array[] = array( 'name' => $plugin_info['Name'], 'version' => $require_wp );
		}
	}
}

if ( ! function_exists( 'cbx_admin_notices' ) ) {
	function cbx_admin_notices() {
		global $cbx_versions_notice_array, $cbx_plugin_banner_to_settings, $bstwbsftwppdtplgns_options, $cbx_plugin_banner_go_pro, $bstwbsftwppdtplgns_banner_array, $cbx_plugin_banner_timeout;

		/* cbx_plugin_banner_go_pro */
		if ( ! empty( $cbx_plugin_banner_go_pro ) ) {
			/* get $cbx_plugins */
			// require( dirname( __FILE__ ) . '/product_list.php' );
			
			foreach ( $bstwbsftwppdtplgns_banner_array as $value ) {
				if ( isset( $cbx_plugin_banner_go_pro[ $value[0] ] ) && ! isset( $_COOKIE[ $value[0] ] ) ) {

					if ( isset( $cbx_plugins[ $value[1] ]['pro_version'] ) && is_plugin_active( $cbx_plugins[ $value[1] ]['pro_version'] ) ) {
						continue;
					}

					$single_banner_value = $cbx_plugin_banner_go_pro[ $value[0] ]; ?>
					<div class="updated" style="padding: 0; margin: 0; border: none; background: none;">
						<div class="<?php echo $single_banner_value['prefix']; ?>_message cbx_banner_on_plugin_page cbx_go_pro_banner" style="display: none;">
							<button class="<?php echo $single_banner_value['prefix']; ?>_close_icon close_icon notice-dismiss cbx_hide_settings_notice" title="<?php _e( 'Close notice', 'xcellorate' ); ?>"></button>
							<div class="icon">
								<img title="" src="<?php echo esc_attr( $single_banner_value['banner_url'] ); ?>" alt="" />
							</div>
							<div class="text">
								<?php _e( 'It’s time to upgrade your', 'xcellorate' ); ?> <strong><?php echo $single_banner_value['plugin_info']['Name']; ?> plugin</strong> <?php _e( 'to', 'xcellorate' ); ?> <strong>Pro</strong> <?php _e( 'version!', 'xcellorate' ); ?><br />
								<span><?php _e( 'Extend standard plugin functionality with new great options.', 'xcellorate' ); ?></span>
							</div>
							<div class="button_div">
								<a class="button" target="_blank" href="<?php echo $single_banner_value['cbx_link']; ?>"><?php _e( 'Learn More', 'xcellorate' ); ?></a>
							</div>
						</div>
					</div>
					<?php break;
				}
			}
		}

		/* $cbx_plugin_banner_timeout */
		if ( ! empty( $cbx_plugin_banner_timeout ) ) {
			foreach ( $cbx_plugin_banner_timeout as $banner_value ) { ?>
				<div class="updated" style="padding: 0; margin: 0; border: none; background: none;">
					<div class="<?php echo $banner_value['prefix']; ?>_message_timeout cbx_banner_on_plugin_page cbx_banner_timeout" style="display:none;">
						<button class="<?php echo $banner_value['prefix']; ?>_close_icon close_icon notice-dismiss cbx_hide_settings_notice" title="<?php _e( 'Close notice', 'xcellorate' ); ?>"></button>
						<div class="icon">
							<img title="" src="<?php echo esc_url( $banner_value['banner_url'] ); ?>" alt="" />
						</div>
						<div class="text"><?php printf( __( "Your license key for %s expires on %s and you won't be granted TOP-PRIORITY SUPPORT or UPDATES.", 'xcellorate' ), '<strong>' . $banner_value['plugin_name'] . '</strong>', esc_html( $bstwbsftwppdtplgns_options['time_out'][ $banner_value['plugin_key'] ] ) ); ?>
					</div>
				</div>
			<?php }
		}

		/*  versions notice */
		if ( ! empty( $cbx_versions_notice_array ) ) {
			foreach ( $cbx_versions_notice_array as $key => $value ) { ?>
				<div class="update-nag">
					<?php printf(
						"<strong>%s</strong> %s <strong>WordPress %s</strong> %s",
						$value['name'],
						__( 'requires', 'xcellorate' ),
						$value['version'],
						__( 'or higher! We do not guarantee that our plugin will work correctly. Please upgrade to WordPress latest version.', 'xcellorate' )
					); ?>
				</div>
			<?php }
		}

		/*  banner_to_settings notice */
		if ( ! empty( $cbx_plugin_banner_to_settings ) ) {
			if ( 1 == count( $cbx_plugin_banner_to_settings ) ) { ?>
				<div class="updated" style="padding: 0; margin: 0; border: none; background: none;">
					<div class="cbx_banner_on_plugin_page cbx_banner_to_settings">
						<div class="icon">
							<img title="" src="<?php echo esc_url( $cbx_plugin_banner_to_settings[0]['banner_url'] ); ?>" alt="" />
						</div>
						<div class="text">
							<strong><?php printf( __( 'Thank you for installing %s plugin!', 'xcellorate' ), esc_html ($cbx_plugin_banner_to_settings[0]['plugin_info']['Name']) ); ?></strong>
							<br />
							<?php _e( "Let's get started", 'xcellorate' ); ?>:
							<a href="<?php echo esc_url( self_admin_url( $cbx_plugin_banner_to_settings[0]['settings_url'] ) ); ?>"><?php _e( 'Settings', 'xcellorate' ); ?></a>
							<?php if ( false != $cbx_plugin_banner_to_settings[0]['post_type_url'] ) { ?>
								<?php _e( 'or', 'xcellorate' ); ?>
								<a href="<?php echo esc_url( self_admin_url( $cbx_plugin_banner_to_settings[0]['post_type_url'] ) ); ?>"><?php _e( 'Add New', 'xcellorate' ); ?></a>
							<?php } ?>
						</div>
						<form action="" method="post">
							<button class="notice-dismiss cbx_hide_settings_notice" title="<?php _e( 'Close notice', 'xcellorate' ); ?>"></button>
							<input type="hidden" name="cbx_hide_settings_notice_<?php echo $cbx_plugin_banner_to_settings[0]['plugin_options_name']; ?>" value="hide" />
							<?php wp_nonce_field( plugin_basename( __FILE__ ), 'cbx_settings_nonce_name' ); ?>
						</form>
					</div>
				</div>
			<?php } else { ?>
				<div class="updated" style="padding: 0; margin: 0; border: none; background: none;">
					<div class="cbx_banner_on_plugin_page cbx_banner_to_settings_joint">
						<form action="" method="post">
							<button class="notice-dismiss cbx_hide_settings_notice" title="<?php _e( 'Close notice', 'xcellorate' ); ?>"></button>
							<div class="cbx-text">
								<div class="icon">
									<span class="dashicons dashicons-admin-plugins"></span>
								</div>
								<strong><?php printf( __( 'Thank you for installing plugins by %s!', 'xcellorate' ), 'CybexSecurity' ); ?></strong>
								<div class="hide-if-no-js cbx-more-links">
									<a href="#" class="cbx-more"><?php _e( 'More Details', 'xcellorate' ); ?></a>
									<a href="#" class="cbx-less hidden"><?php _e( 'Less Details', 'xcellorate' ); ?></a>
								</div>
								<?php wp_nonce_field( plugin_basename( __FILE__ ), 'cbx_settings_nonce_name' ); ?>
								<div class="clear"></div>
							</div>
							<div class="cbx-details hide-if-js">
								<?php foreach ( $cbx_plugin_banner_to_settings as $value ) { ?>
									<div>
										<strong><?php echo str_replace( ' by CybexSecurity', '', $value['plugin_info']['Name'] ); ?></strong>&ensp;<a href="<?php echo esc_url( self_admin_url( $value['settings_url'] ) ); ?>"><?php _e( 'Settings', 'xcellorate' ); ?></a>
										<?php if ( false != $value['post_type_url'] ) { ?>
											&ensp;|&ensp;<a target="_blank" href="<?php echo esc_url( self_admin_url( $value['post_type_url'] ) ); ?>"><?php _e( 'Add New', 'xcellorate' ); ?></a>
										<?php } ?>
										<input type="hidden" name="cbx_hide_settings_notice_<?php echo $value['plugin_options_name']; ?>" value="hide" />
									</div>
								<?php } ?>
							</div>
						</div>
					</form>
				</div>
			<?php }
		}

		/**
		 * show notices about deprecated_function
		 * @since 1.9.8
		*/
		if ( ! empty( $bstwbsftwppdtplgns_options['deprecated_function'] ) ) { ?>
			<div class="update-nag">
				<strong><?php _e( 'Deprecated function(-s) is used on the site here:', 'xcellorate' ); ?></strong>
				<?php $i = 1;
				foreach ( $bstwbsftwppdtplgns_options['deprecated_function'] as $function_name => $attr ) {
					if ( 1 != $i )
						echo ' ,';
					if ( ! empty( $attr['product-name'] ) ) {
						echo $attr['product-name'];
					} elseif ( ! empty( $attr['file'] ) ) {
						echo $attr['file'];
					}
					unset( $bstwbsftwppdtplgns_options['deprecated_function'][ $function_name ] );
					$i++;
				} ?>.
				<br/>
				<?php _e( 'This function(-s) will be removed over time. Please update the product(-s).', 'xcellorate' ); ?>
			</div>
			<?php if ( is_multisite() )
				update_site_option( 'bstwbsftwppdtplgns_options', $bstwbsftwppdtplgns_options );
			else
				update_option( 'bstwbsftwppdtplgns_options', $bstwbsftwppdtplgns_options );
		}
	}
}

if ( ! function_exists( 'cbx_add_plugin_banner_timeout' ) ) {
	function cbx_add_plugin_banner_timeout( $plugin_key, $plugin_prefix, $plugin_name, $banner_url_or_slug ) {
		global $cbx_plugin_banner_timeout;

		if ( isset( $bstwbsftwppdtplgns_options['time_out'][ $plugin_key ] ) && ( strtotime( $bstwbsftwppdtplgns_options['time_out'][ $plugin_key ] ) < strtotime( date("m/d/Y") . '+1 month' ) ) && ( strtotime( $bstwbsftwppdtplgns_options['time_out'][ $plugin_key ] ) > strtotime( date("m/d/Y") ) ) ) {			

			if ( false == strrpos( $banner_url_or_slug, '/' ) ) {
				$banner_url_or_slug = '//ps.w.org/' . $banner_url_or_slug . '/assets/icon-256x256.png';
			}

			$cbx_plugin_banner_timeout[] = array(
				'plugin_key'	=> $plugin_key,
				'prefix'		=> $plugin_prefix,
				'plugin_name'	=> $plugin_name,
				'banner_url'	=> $banner_url_or_slug
			);
		}
	}
}

if ( ! function_exists( 'cbx_plugin_banner_to_settings' ) ) {
	function cbx_plugin_banner_to_settings( $plugin_info, $plugin_options_name, $banner_url_or_slug, $settings_url, $post_type_url = false ) {
		global $cbx_plugin_banner_to_settings;

		$is_network_admin = is_network_admin();

		$plugin_options = $is_network_admin ? get_site_option( $plugin_options_name ) : get_option( $plugin_options_name );

		if ( isset( $plugin_options['display_settings_notice'] ) && 0 == $plugin_options['display_settings_notice'] )
			return;

		if ( isset( $_POST['cbx_hide_settings_notice_' . $plugin_options_name ] ) && check_admin_referer( plugin_basename( __FILE__ ), 'cbx_settings_nonce_name' )  ) {
			$plugin_options['display_settings_notice'] = 0;
			if ( $is_network_admin )
				update_site_option( $plugin_options_name, $plugin_options );
			else
				update_option( $plugin_options_name, $plugin_options );
			return;
		}

		if ( false == strrpos( $banner_url_or_slug, '/' ) ) {
			$banner_url_or_slug = '//ps.w.org/' . $banner_url_or_slug . '/assets/icon-256x256.png';
		}

		$cbx_plugin_banner_to_settings[] = array(
			'plugin_info'			=> $plugin_info,
			'plugin_options_name'	=> $plugin_options_name,
			'banner_url'			=> $banner_url_or_slug,
			'settings_url'			=> $settings_url,
			'post_type_url'			=> $post_type_url
		);
	}
}

if ( ! function_exists( 'cbx_plugin_suggest_feature_banner' ) ) {
	function cbx_plugin_suggest_feature_banner( $plugin_info, $plugin_options_name, $banner_url_or_slug ) {
		$is_network_admin = is_network_admin();

		$plugin_options = $is_network_admin ? get_site_option( $plugin_options_name ) : get_option( $plugin_options_name );

		if ( isset( $plugin_options['display_suggest_feature_banner'] ) && 0 == $plugin_options['display_suggest_feature_banner'] )
			return;

		if ( ! isset( $plugin_options['first_install'] ) ) {
			$plugin_options['first_install'] = strtotime( "now" );
			$update_option = $return = true;
		} elseif ( strtotime( '-2 week' ) < $plugin_options['first_install'] ) {
			$return = true;
		}

		if ( ! isset( $plugin_options['go_settings_counter'] ) ) {
			$plugin_options['go_settings_counter'] = 1;
			$update_option = $return = true;
		} elseif ( 20 > $plugin_options['go_settings_counter'] ) {
			$plugin_options['go_settings_counter'] = $plugin_options['go_settings_counter'] + 1;
			$update_option = $return = true;
		}

		if ( isset( $update_option ) ) {
			if ( $is_network_admin )
				update_site_option( $plugin_options_name, $plugin_options );
			else
				update_option( $plugin_options_name, $plugin_options );
		}

		if ( isset( $return ) )
			return;

		if ( isset( $_POST['cbx_hide_suggest_feature_banner_' . $plugin_options_name ] ) && check_admin_referer( $plugin_info['Name'], 'cbx_settings_nonce_name' )  ) {
			$plugin_options['display_suggest_feature_banner'] = 0;
			if ( $is_network_admin )
				update_site_option( $plugin_options_name, $plugin_options );
			else
				update_option( $plugin_options_name, $plugin_options );
			return;
		}

		if ( false == strrpos( $banner_url_or_slug, '/' ) ) {
			$banner_url_or_slug = '//ps.w.org/' . $banner_url_or_slug . '/assets/icon-256x256.png';
		} ?>
		<div class="updated" style="padding: 0; margin: 0; border: none; background: none;">
			<div class="cbx_banner_on_plugin_page cbx_suggest_feature_banner">
				<div class="icon">
					<img title="" src="<?php echo esc_attr( $banner_url_or_slug ); ?>" alt="" />
				</div>
				<div class="text">
					<strong><?php printf( __( 'Thank you for choosing %s plugin!', 'xcellorate' ), $plugin_info['Name'] ); ?></strong><br />
					<?php _e( "If you have a feature, suggestion or idea you'd like to see in the plugin, we'd love to hear about it!", 'xcellorate' ); ?>
					<a target="_blank" href="https://support.cybexsecurity.co.uk/hc/en-us/requests/new"><?php _e( 'Suggest a Feature', 'xcellorate' ); ?></a>
				</div>
				<form action="" method="post">
					<button class="notice-dismiss cbx_hide_settings_notice" title="<?php _e( 'Close notice', 'xcellorate' ); ?>"></button>
					<input type="hidden" name="cbx_hide_suggest_feature_banner_<?php echo $plugin_options_name; ?>" value="hide" />
					<?php wp_nonce_field( $plugin_info['Name'], 'cbx_settings_nonce_name' ); ?>
				</form>
			</div>
		</div>
	<?php }
}

if ( ! function_exists( 'cbx_show_settings_notice' ) ) {
	function cbx_show_settings_notice() { ?>
		<div id="cbx_save_settings_notice" class="updated fade below-h2" style="display:none;">
			<p>
				<strong><?php _e( 'Notice', 'xcellorate' ); ?></strong>: <?php _e( "The plugin's settings have been changed.", 'xcellorate' ); ?>
				<a class="cbx_save_anchor" href="#cbx-submit-button"><?php _e( 'Save Changes', 'xcellorate' ); ?></a>
			</p>
		</div>
	<?php }
}

if ( ! function_exists( 'cbx_hide_premium_options' ) ) {
	function cbx_hide_premium_options( $options ) {
		if ( ! isset( $options['hide_premium_options'] ) || ! is_array( $options['hide_premium_options'] ) )
			$options['hide_premium_options'] = array();

		$options['hide_premium_options'][] = get_current_user_id();

		return array(
				'message' => __( 'You can always look at premium options by checking the "Pro Options" in the "Misc" tab.', 'xcellorate' ),
				'options' => $options );
	}
}

if ( ! function_exists( 'cbx_hide_premium_options_check' ) ) {
	function cbx_hide_premium_options_check( $options ) {
		if ( ! empty( $options['hide_premium_options'] ) && in_array( get_current_user_id(), $options['hide_premium_options'] ) )
			return true;
		else
			return false;
	}
}

if ( ! function_exists ( 'cbx_plugins_admin_init' ) ) {
	function cbx_plugins_admin_init() {
		if ( isset( $_GET['cbx_activate_plugin'] ) && check_admin_referer( 'cbx_activate_plugin' . $_GET['cbx_activate_plugin'] ) ) {

			$plugin = isset( $_GET['cbx_activate_plugin'] ) ? sanitize_text_field( $_GET['cbx_activate_plugin'] ) : '';
			$result = activate_plugin( $plugin, '', is_network_admin() );
			if ( is_wp_error( $result ) ) {
				if ( 'unexpected_output' == $result->get_error_code() ) {
					$redirect = self_admin_url( 'admin.php?page=cbx_panel&error=true&charsout=' . strlen( $result->get_error_data() ) . '&plugin=' . $plugin );
					wp_redirect( add_query_arg( '_error_nonce', wp_create_nonce( 'plugin-activation-error_' . $plugin ), $redirect ) );
					exit();
				} else {
					wp_die( $result );
				}
			}

			if ( ! is_network_admin() ) {
				$recent = (array) get_option( 'recently_activated' );
				unset( $recent[ $plugin ] );
				update_option( 'recently_activated', $recent );
			} else {
				$recent = (array) get_site_option( 'recently_activated' );
				unset( $recent[ $plugin ] );
				update_site_option( 'recently_activated', $recent );
			}
			/**
			* @deprecated 1.9.8 (15.12.2016)
			*/
			$is_main_page = in_array( $_GET['page'], array( 'cbx_panel', 'cbx_themes', 'cbx_system_status' ) );
			$page = sanitize_text_field( wp_unslash( $_GET['page'] ) );
			$tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : '';

			if ( $is_main_page )
				$current_page = 'admin.php?page=' . $page;
			else
				$current_page = isset( $_GET['tab'] ) ? 'admin.php?page=' . $page . '&tab=' . $tab : 'admin.php?page=' . $page;
			/*end deprecated */

			wp_redirect( self_admin_url( esc_url( $current_page . '&activate=true' ) ) );
			exit();
		}

		if ( isset( $_GET['page'] ) && ( $_GET['page'] == 'cbx_panel' || strpos( $_GET['page'], '-cbx-panel' ) ) ) {
			if ( ! session_id() )
				@session_start();
		}

		cbx_add_editor_buttons();
	}
}

if ( ! function_exists ( 'cbx_admin_enqueue_scripts' ) ) {
	function cbx_admin_enqueue_scripts() {
		global $wp_scripts, $hook_suffix,
			$post_type,
			$cbx_plugin_banner_go_pro, $cbx_plugin_banner_timeout, $bstwbsftwppdtplgns_banner_array,
			$cbx_shortcode_list;

		$jquery_ui_version = isset( $wp_scripts->registered['jquery-ui-core']->ver ) ? $wp_scripts->registered['jquery-ui-core']->ver : '1.12.1';
		wp_enqueue_style( 'jquery-ui-style', cbx_menu_url( 'css/jquery-ui-styles/' . $jquery_ui_version . '/jquery-ui.css' ) );
		wp_enqueue_style( 'cbx-admin-css', cbx_menu_url( 'css/general_style.css' ) );
		wp_enqueue_script( 'cbx-admin-scripts', cbx_menu_url( 'js/general_script.js' ), array( 'jquery', 'jquery-ui-tooltip' ) );

		if ( isset( $_GET['page'] ) && ( in_array( $_GET['page'], array( 'cbx_panel', 'cbx_themes', 'cbx_system_status' ) ) || strpos( $_GET['page'], '-cbx-panel' ) ) ) {
			wp_enqueue_style( 'cbx_menu_style', cbx_menu_url( 'css/style.css' ) );
			wp_enqueue_script( 'cbx_menu_script', cbx_menu_url( 'js/cbx_menu.js' ) );
			wp_enqueue_script( 'theme-install' );
			add_thickbox();
			wp_enqueue_script( 'plugin-install' );
		}

		if ( 'plugins.php' == $hook_suffix ) {
			if ( ! empty( $cbx_plugin_banner_go_pro ) || ! empty( $cbx_plugin_banner_timeout ) ) {
				wp_enqueue_script( 'cbx_menu_cookie', cbx_menu_url( 'js/c_o_o_k_i_e.js' ) );

				if ( ! empty( $cbx_plugin_banner_go_pro ) ) {

					foreach ( $bstwbsftwppdtplgns_banner_array as $value ) {
						if ( isset( $cbx_plugin_banner_go_pro[ $value[0] ] ) && ! isset( $_COOKIE[ $value[0] ] ) ) {
							$prefix = $cbx_plugin_banner_go_pro[ $value[0] ]['prefix'];

							$script = "(function($) {
								$(document).ready( function() {
									var hide_message = $.cookie( '" . $prefix . "_hide_banner_on_plugin_page' );
									if ( hide_message == 'true' ) {
										$( '." . $prefix . "_message' ).css( 'display', 'none' );
									} else {
										$( '." . $prefix . "_message' ).css( 'display', 'block' );
									};
									$( '." . $prefix . "_close_icon' ).click( function() {
										$( '." . $prefix . "_message' ).css( 'display', 'none' );
										$.cookie( '" . $prefix . "_hide_banner_on_plugin_page', 'true', { expires: 32, secure: true } );
									});
								});
							})(jQuery);";

							wp_register_script( $prefix . '_hide_banner_on_plugin_page', '' );
							wp_enqueue_script( $prefix . '_hide_banner_on_plugin_page' );
							wp_add_inline_script( $prefix . '_hide_banner_on_plugin_page', sprintf( $script ) );
							break;
						}
					}
				}

				if ( ! empty( $cbx_plugin_banner_timeout ) ) {
					$script = '(function($) {
							$(document).ready( function() {';

					foreach ( $cbx_plugin_banner_timeout as $banner_value ) {
						$script .= "var hide_message = $.cookie( '" . $banner_value['prefix'] . "_timeout_hide_banner_on_plugin_page' );
							if ( hide_message == 'true' ) {
								$( '." . $banner_value['prefix'] . "_message_timeout' ).css( 'display', 'none' );
							} else {
								$( '." . $banner_value['prefix'] . "_message_timeout' ).css( 'display', 'block' );
							}
							$( '." . $banner_value['prefix'] . "_close_icon' ).click( function() {
								$( '." . $banner_value['prefix'] . "_message_timeout' ).css( 'display', 'none' );
								$.cookie( '" . $banner_value['prefix'] . "_timeout_hide_banner_on_plugin_page', 'true', { expires: 30, secure: true } );
							});";
					}

					$script .= "});
						})(jQuery);";

					wp_register_script( 'plugin_banner_timeout_hide', '' );
					wp_enqueue_script( 'plugin_banner_timeout_hide' );
					wp_add_inline_script( 'plugin_banner_timeout_hide', sprintf( $script ) );
				}
			}

			if ( ! defined( 'DOING_AJAX' ) ) {
				wp_enqueue_style( 'cbx-modal-css', cbx_menu_url( 'css/modal.css' ) );
			}
		}

		if ( ! empty( $cbx_shortcode_list ) ) {
			/* TinyMCE Shortcode Plugin */
			$script = "var cbx_shortcode_button = {
					'label': '" . esc_attr__( "Add cbx Shortcode", "xcellorate" ) . "',
					'title': '" . esc_attr__( "Add cbx Plugins Shortcode", "xcellorate" ) . "',
					'function_name': [";
						foreach ( $cbx_shortcode_list as $value ) {
							if ( isset( $value['js_function'] ) )
								$script .= "'" . $value['js_function'] . "',";
						}
					$script .= "]
				};";
			wp_register_script( 'cbx_shortcode_button', '' );
			wp_enqueue_script( 'cbx_shortcode_button' );
			wp_add_inline_script( 'cbx_shortcode_button', sprintf( $script ) );

			/* TinyMCE Shortcode Plugin */
			if ( isset( $post_type ) && in_array( $post_type, array( 'post', 'page' ) ) ) {
				$tooltip_args = array(
					'tooltip_id'	=> 'cbx_shortcode_button_tooltip',
					'css_selector' 	=> '.mce-cbx_shortcode_button',
					'actions' 		=> array(
						'click' 	=> false,
						'onload' 	=> true
					),
					'content' 		=> '<h3>' . __( 'Add shortcode', 'xcellorate' ) . '</h3><p>' . __( "Add CybexSecurity plugins' shortcodes using this button.", 'xcellorate' ) . '</p>',
					'position' => array(
						'edge' 		=> 'right'
					),
					'set_timeout' => 2000
				);
				cbx_add_tooltip_in_admin( $tooltip_args );
			}
		}
	}
}

/**
* add styles and scripts for cbx_Settings_Tabs
*
* @since 1.9.8
*/
if ( ! function_exists( 'cbx_enqueue_settings_scripts' ) ) {
	function cbx_enqueue_settings_scripts() {
		wp_enqueue_script( 'jquery-ui-resizable' );
		wp_enqueue_script( 'jquery-ui-tabs' );
		wp_enqueue_style( 'cbx-modal-css', cbx_menu_url( 'css/modal.css' ) );
	}
}

if ( ! function_exists ( 'cbx_plugins_admin_head' ) ) {
	function cbx_plugins_admin_head() {
		if ( isset( $_GET['page'] ) && $_GET['page'] == "cbx_panel" ) { ?>
			<noscript>
				<style type="text/css">
					.cbx_product_button {
						display: inline-block;
					}
				</style>
			</noscript>
		<?php }
    }
}

if ( ! function_exists ( 'cbx_plugins_admin_footer' ) ) {
	function cbx_plugins_admin_footer() {
		cbx_shortcode_media_button_popup();
	}
}

if ( ! function_exists ( 'cbx_plugins_include_codemirror' ) ) {
	function cbx_plugins_include_codemirror() {
		global $wp_version;
		if ( version_compare( $wp_version, '4.9.0',  '>=' ) ) {
			wp_enqueue_style( 'wp-codemirror' );
			wp_enqueue_script( 'wp-codemirror' );
        } else {
			wp_enqueue_style( 'codemirror.css', cbx_menu_url( 'css/codemirror.css' ) );
        }

    }
}

/**
 * Tooltip block
 */
if ( ! function_exists( 'cbx_add_tooltip_in_admin' ) ) {
	function cbx_add_tooltip_in_admin( $tooltip_args = array() ) {
		new cbx_admin_tooltip( $tooltip_args );
	}
}

if ( ! class_exists( 'cbx_admin_tooltip' ) ) {
	class cbx_admin_tooltip {
		private $tooltip_args;

		public function __construct( $tooltip_args ) {
			global $bstwbsftwppdtplgns_tooltip_script_add;

			/* Default arguments */
			$tooltip_args_default = array(
				'tooltip_id'	=> false,
				'css_selector' 	=> false,
				'actions' 		=> array(
					'click' 	=> true,
					'onload' 	=> false,
				),
				'buttons'		=> array(
					'close' 	=> array(
						'type' => 'dismiss',
						'text' => __( 'Close', 'xcellorate' ),
					),
				),
				'position' => array(
					'edge'  	=> 'top',
					'align' 	=> 'center',
					'pos-left'	=> 0,
					'pos-top'	=> 0,
					'zindex' 	=> 10000
				),
				'set_timeout' => 0
			);
			$tooltip_args = array_merge( $tooltip_args_default, $tooltip_args );
			/* Check that our merged array has default values */
			foreach ( $tooltip_args_default as $arg_key => $arg_value ) {
				if ( is_array( $arg_value ) ) {
					foreach ( $arg_value as $key => $value) {
						if ( ! isset( $tooltip_args[ $arg_key ][ $key ] ) ) {
							$tooltip_args[ $arg_key ][ $key ] = $tooltip_args_default[ $arg_key ][ $key ];
						}
					}
				}
			}
			/* Check if tooltip is dismissed */
			if ( true === $tooltip_args['actions']['onload'] ) {
				if ( in_array( $tooltip_args['tooltip_id'], array_filter( explode( ',', (string) get_user_meta( get_current_user_id(), 'dismissed_wp_pointers', true ) ) ) ) ) {
					$tooltip_args['actions']['onload'] = false;
				}
			}
			/* Check entered data */
			if ( false === $tooltip_args['tooltip_id'] || false === $tooltip_args['css_selector'] || ( false === $tooltip_args['actions']['click'] && false === $tooltip_args['actions']['onload'] ) ) {
				/* if not enough data to output a tooltip or both actions (click, onload) are false */
				return;
			} else {
				/* check position */
				if ( ! in_array( $tooltip_args['position']['edge'], array( 'left', 'right', 'top', 'bottom' ) )  ) {
					$tooltip_args['position']['edge'] = 'top';
				}
				if ( ! in_array( $tooltip_args['position']['align'], array( 'top', 'bottom', 'left', 'right', 'center', ) ) ) {
					$tooltip_args['position']['align'] = 'center';
				}
			}
			/* fix position */
			switch ( $tooltip_args['position']['edge'] ) {
				case 'left':
				case 'right':
					switch ( $tooltip_args['position']['align'] ) {
						case 'top':
						case 'bottom':
							$tooltip_args['position']['align'] = 'center';
							break;
					}
					break;
				case 'top':
				case 'bottom':
					if ( $tooltip_args['position']['align'] == 'left' ) {
						$tooltip_args['position']['pos-left'] -= 65;
					}
					break;
			}
			$this->tooltip_args = $tooltip_args;
			/* add styles and scripts */
			wp_enqueue_style( 'wp-pointer' );
			wp_enqueue_script( 'wp-pointer' );
			/* add script that displays our tooltip */
			if ( ! isset( $bstwbsftwppdtplgns_tooltip_script_add ) ) {
				wp_enqueue_script( 'cbx-tooltip-script', cbx_menu_url( 'js/cbx_tooltip.js' ) );
				$bstwbsftwppdtplgns_tooltip_script_add = true;
			}
			$tooltip_args = $this->tooltip_args;

			$script = "(function($) {
					$(document).ready( function() {
						$.cbxTooltip( " . json_encode( $tooltip_args ) . " );
					})
				})(jQuery);";
			wp_register_script( 'cbx-tooltip-script-single-' . $this->tooltip_args['tooltip_id'], '' );
			wp_enqueue_script( 'cbx-tooltip-script-single-' . $this->tooltip_args['tooltip_id'] );
			wp_add_inline_script( 'cbx-tooltip-script-single-' . $this->tooltip_args['tooltip_id'], sprintf( $script ) );
		}
	}
}

if ( ! function_exists ( 'cbx_form_restore_default_confirm' ) ) {
	function cbx_form_restore_default_confirm( $plugin_basename ) { ?>
		<div>
			<p><?php _e( 'Are you sure you want to restore default settings?', 'xcellorate' ) ?></p>
			<form method="post" action="">
				<p>
					<button class="button button-primary" name="cbx_restore_confirm"><?php _e( 'Yes, restore all settings', 'xcellorate' ) ?></button>
					<button class="button" name="cbx_restore_deny"><?php _e( 'No, go back to the settings page', 'xcellorate' ) ?></button>
					<?php wp_nonce_field( $plugin_basename, 'cbx_settings_nonce_name' ); ?>
				</p>
			</form>
		</div>
	<?php }
}

/* shortcode */
if ( ! function_exists( 'cbx_add_editor_buttons' ) ) {
	function cbx_add_editor_buttons() {
		global $cbx_shortcode_list;
		if ( ! empty( $cbx_shortcode_list ) && current_user_can( 'edit_posts' ) && current_user_can( 'edit_pages' ) ) {
			add_filter( 'mce_external_plugins', 'cbx_add_buttons' );
			add_filter( 'mce_buttons', 'cbx_register_buttons' );
		}
	}
}

if ( ! function_exists( 'cbx_add_buttons' ) ){
	function cbx_add_buttons( $plugin_array ) {
		$plugin_array['add_cbx_shortcode'] = cbx_menu_url( 'js/shortcode-button.js' );
		return $plugin_array;
	}
}

if ( ! function_exists( 'cbx_register_buttons' ) ) {
	function cbx_register_buttons( $buttons ) {
		array_push( $buttons, 'add_cbx_shortcode' ); /* dropcap', 'recentposts */
		return $buttons;
	}
}

/* Generate inline content for the popup window when the "cbx shortcode" button is clicked */
if ( ! function_exists( 'cbx_shortcode_media_button_popup' ) ) {
	function cbx_shortcode_media_button_popup() {
		global $cbx_shortcode_list;

		if ( ! empty( $cbx_shortcode_list ) ) { ?>
			<div id="cbx_shortcode_popup" style="display:none;">
				<div id="cbx_shortcode_popup_block">
					<div id="cbx_shortcode_select_plugin">
						<h4><?php _e( 'Plugin', 'xcellorate' ); ?></h4>
						<select name="cbx_shortcode_select" id="cbx_shortcode_select">
							<?php foreach ( $cbx_shortcode_list as $key => $value ) { ?>
								<option value="<?php echo esc_attr( $key ); ?>"><?php echo $value['name']; ?></option>
							<?php } ?>
						</select>
					</div>
					<div class="clear"></div>
					<div id="cbx_shortcode_content">
						<h4><?php _e( 'Shortcode settings', 'xcellorate' ); ?></h4>
						<?php echo apply_filters( 'cbx_shortcode_button_content', '' ); ?>
					</div>
					<div class="clear"></div>
					<div id="cbx_shortcode_content_bottom">
						<p><?php _e( 'The shortcode will be inserted', 'xcellorate' ); ?></p>
						<div id="cbx_shortcode_block"><div id="cbx_shortcode_display"></div></div>
					</div>
				</div>
			</div>
		<?php }
	}
}

/**
 * output shortcode in a special block
 * @since 1.9.8
 */
if ( ! function_exists( 'cbx_shortcode_output' ) ) {
	function cbx_shortcode_output( $shortcode ) { ?>
		<span class="cbx_shortcode_output"><input type="text" onfocus="this.select();" readonly="readonly" value="<?php echo $shortcode; ?>" class="large-text cbx_no_bind_notice"></span>
	<?php }
}

/**
 * output tooltip
 * @since 1.9.8
 * @param   string   $content  - HTML content for the tooltip
 * @param   string   $class  - Can be standart "cbx-hide-for-mobile" (tooltip will be hidden in 782px) and "cbx-auto-width" (need for img) or some custom class.
 */
if ( ! function_exists( 'cbx_add_help_box' ) ) {
	function cbx_add_help_box( $content, $class = '' ) {
		return '<span class="cbx_help_box dashicons dashicons-editor-help ' . $class . ' hide-if-no-js">
			<span class="cbx_hidden_help_text">' . $content . '</span>
		</span>';
	}
}

/* add help tab  */
if ( ! function_exists( 'cbx_help_tab' ) ) {
	function cbx_help_tab( $screen, $args ) {
		$url = ( ! empty( $args['section'] ) ) ? 'https://support.cybexsecurity.co.uk/hc/en-us/sections/' . $args['section'] : 'https://support.cybexsecurity.co.uk/';

		$content = '<p><a href="' . esc_url( $url ) . '" target="_blank">' . __( 'Visit Help Center', 'xcellorate' ) . '</a></p>';

		$screen->add_help_tab(
			array(
				'id'      => $args['id'] . '_help_tab',
				'title'   => __( 'FAQ', 'xcellorate' ),
				'content' => $content
			)
		);

		$screen->set_help_sidebar(
			'<p><strong>' . __( 'For more information:', 'xcellorate' ) . '</strong></p>' .
			'<p><a href="https://drive.google.com/folderview?id=0B5l8lO-CaKt9VGh0a09vUjNFNjA&usp=sharing#list" target="_blank">' . __( 'Documentation', 'xcellorate' ) . '</a></p>' .
			'<p><a href="https://www.youtube.com/user/xcellorate/playlists?flow=grid&sort=da&view=1" target="_blank">' . __( 'Video Instructions', 'xcellorate' ) . '</a></p>' .
			'<p><a href="https://support.cybexsecurity.co.uk/hc/en-us/requests/new" target="_blank">' . __( 'Submit a Request', 'xcellorate' ) . '</a></p>'
		);
	}
}

if ( ! function_exists( 'cbx_enqueue_custom_code_css_js' ) ) {
	function cbx_enqueue_custom_code_css_js() {
		global $bstwbsftwppdtplgns_options;

		if ( ! isset( $bstwbsftwppdtplgns_options ) )
			$bstwbsftwppdtplgns_options = ( function_exists( 'is_multisite' ) && is_multisite() ) ? get_site_option( 'bstwbsftwppdtplgns_options' ) : get_option( 'bstwbsftwppdtplgns_options' );

		if ( ! empty( $bstwbsftwppdtplgns_options['custom_code'] ) ) {
			$is_multisite = is_multisite();
			if ( $is_multisite )
				$blog_id = get_current_blog_id();

			if ( ! $is_multisite && ! empty( $bstwbsftwppdtplgns_options['custom_code']['cbx-custom-code.css'] ) )
				wp_enqueue_style( 'cbx-custom-style', $bstwbsftwppdtplgns_options['custom_code']['cbx-custom-code.css'] );
			elseif ( $is_multisite && ! empty( $bstwbsftwppdtplgns_options['custom_code'][ $blog_id ]['cbx-custom-code.css'] ) )
				wp_enqueue_style( 'cbx-custom-style', $bstwbsftwppdtplgns_options['custom_code'][ $blog_id ]['cbx-custom-code.css'] );

			if ( ! $is_multisite && ! empty( $bstwbsftwppdtplgns_options['custom_code']['cbx-custom-code.js'] ) )
				wp_enqueue_script( 'cbx-custom-style', $bstwbsftwppdtplgns_options['custom_code']['cbx-custom-code.js'] );
			elseif ( $is_multisite && ! empty( $bstwbsftwppdtplgns_options['custom_code'][ $blog_id ]['cbx-custom-code.js'] ) )
				wp_enqueue_script( 'cbx-custom-style', $bstwbsftwppdtplgns_options['custom_code'][ $blog_id ]['cbx-custom-code.js'] );
		}
	}
}

if ( ! function_exists( 'cbx_enqueue_custom_code_php' ) ) {
	function cbx_enqueue_custom_code_php() {
		if ( is_admin() )
			return;

		global $bstwbsftwppdtplgns_options;

		if ( ! isset( $bstwbsftwppdtplgns_options ) )
			$bstwbsftwppdtplgns_options = ( function_exists( 'is_multisite' ) && is_multisite() ) ? get_site_option( 'bstwbsftwppdtplgns_options' ) : get_option( 'bstwbsftwppdtplgns_options' );

		if ( ! empty( $bstwbsftwppdtplgns_options['custom_code'] ) ) {

			$is_multisite = is_multisite();
			if ( $is_multisite )
				$blog_id = get_current_blog_id();

			if ( ! $is_multisite && ! empty( $bstwbsftwppdtplgns_options['custom_code']['cbx-custom-code.php'] ) ) {
				if ( file_exists( $bstwbsftwppdtplgns_options['custom_code']['cbx-custom-code.php'] ) ) {
					if ( ! defined( 'cbx_GLOBAL' ) )
						define( 'cbx_GLOBAL', true );
					require_once( $bstwbsftwppdtplgns_options['custom_code']['cbx-custom-code.php'] );
				} else {
					unset( $bstwbsftwppdtplgns_options['custom_code']['cbx-custom-code.php'] );
					if ( $is_multisite )
						update_site_option( 'bstwbsftwppdtplgns_options', $bstwbsftwppdtplgns_options );
					else
						update_option( 'bstwbsftwppdtplgns_options', $bstwbsftwppdtplgns_options );
				}
			} elseif ( $is_multisite && ! empty( $bstwbsftwppdtplgns_options['custom_code'][ $blog_id ]['cbx-custom-code.php'] ) ) {
				if ( file_exists( $bstwbsftwppdtplgns_options['custom_code'][ $blog_id ]['cbx-custom-code.php'] ) ) {
					if ( ! defined( 'cbx_GLOBAL' ) )
						define( 'cbx_GLOBAL', true );
					require_once( $bstwbsftwppdtplgns_options['custom_code'][ $blog_id ]['cbx-custom-code.php'] );
				} else {
					unset( $bstwbsftwppdtplgns_options['custom_code'][ $blog_id ]['cbx-custom-code.php'] );
					if ( $is_multisite )
						update_site_option( 'bstwbsftwppdtplgns_options', $bstwbsftwppdtplgns_options );
					else
						update_option( 'bstwbsftwppdtplgns_options', $bstwbsftwppdtplgns_options );
				}
			}
		}
	}
}

if ( ! function_exists( 'cbx_delete_plugin' ) ) {
	function cbx_delete_plugin( $basename ) {
		global $bstwbsftwppdtplgns_options;

		$is_multisite = is_multisite();
		if ( $is_multisite )
			$blog_id = get_current_blog_id();

		if ( ! isset( $bstwbsftwppdtplgns_options ) )
			$bstwbsftwppdtplgns_options = ( $is_multisite ) ? get_site_option( 'bstwbsftwppdtplgns_options' ) : get_option( 'bstwbsftwppdtplgns_options' );

		/* remove cbx_menu versions */
		unset( $bstwbsftwppdtplgns_options['cbx_menu']['version'][ $basename ] );
		/* remove track usage data */
		if ( isset( $bstwbsftwppdtplgns_options['cbx_menu']['track_usage']['products'][ $basename ] ) )
			unset( $bstwbsftwppdtplgns_options['cbx_menu']['track_usage']['products'][ $basename ] );
		/* if empty ['cbx_menu']['version'] - there is no other cbx plugins - delete all */
		if ( empty( $bstwbsftwppdtplgns_options['cbx_menu']['version'] ) ) {
			/* remove options */
			if ( $is_multisite )
				delete_site_option( 'bstwbsftwppdtplgns_options' );
			else
				delete_option( 'bstwbsftwppdtplgns_options' );

			/* remove custom_code */
			if ( $is_multisite ) {
				global $wpdb;
				$old_blog = $wpdb->blogid;
				/* Get all blog ids */
				$blogids = $wpdb->get_col( "SELECT `blog_id` FROM $wpdb->blogs" );
				foreach ( $blogids as $blog_id ) {
					switch_to_blog( $blog_id );
					$upload_dir = wp_upload_dir();
					$folder = $upload_dir['basedir'] . '/cbx-custom-code';
					if ( file_exists( $folder ) && is_dir( $folder ) ) {
						array_map( 'unlink', glob( "$folder/*" ) );
						rmdir( $folder );
					}
				}
				switch_to_blog( $old_blog );
			} else {
				$upload_dir = wp_upload_dir();
				$folder = $upload_dir['basedir'] . '/cbx-custom-code';
				if ( file_exists( $folder ) && is_dir( $folder ) ) {
					array_map( 'unlink', glob( "$folder/*" ) );
					rmdir( $folder );
				}
			}
		}
	}
}

add_action( 'admin_init', 'cbx_plugins_admin_init' );
add_action( 'admin_enqueue_scripts', 'cbx_admin_enqueue_scripts' );
add_action( 'admin_head', 'cbx_plugins_admin_head' );
add_action( 'admin_footer','cbx_plugins_admin_footer' );

add_action( 'admin_notices', 'cbx_admin_notices', 30 );

add_action( 'wp_enqueue_scripts', 'cbx_enqueue_custom_code_css_js', 20 );

cbx_enqueue_custom_code_php();
