<?php
/*
* Get latest version
*/

if ( ! function_exists( 'cbx_include_init' ) ) {
	function cbx_include_init( $base, $cbx_menu_source = 'plugins' ) {
		global $bstwbsftwppdtplgns_options, $bstwbsftwppdtplgns_added_menu, $bstwbsftwppdtplgns_active_plugins;
		if ( ! function_exists( 'get_plugin_data' ) )
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

		$wp_content_dir = defined( 'WP_CONTENT_DIR' ) ? WP_CONTENT_DIR : ABSPATH . 'wp-content';
		$wp_plugins_dir = defined( 'WP_PLUGIN_DIR' ) ? WP_PLUGIN_DIR : $wp_content_dir . '/plugins';

		if ( $cbx_menu_source == 'plugins' ) {
			$cbx_menu_dir = $wp_plugins_dir . '/' .  dirname( $base ) . '/cbx_menu/cbx_menu.php';
			$bstwbsftwppdtplgns_active_plugins[ $base ] = get_plugin_data( $wp_plugins_dir . '/' . $base );
		} else {
			$cbx_menu_dir = $wp_content_dir . '/themes/' . $base . '/inc/cbx_menu/cbx_menu.php';
		}

		$cbx_menu_info = get_plugin_data( $cbx_menu_dir );

		$is_pro_cbx_menu = ( strpos( $cbx_menu_info["Version"], 'pro' ) !== false );
		$cbx_menu_version = str_replace( '-pro', '', $cbx_menu_info["Version"] );		

		if ( ! isset( $bstwbsftwppdtplgns_options ) ) {
			if ( function_exists( 'is_multisite' ) && is_multisite() ) {
				if ( ! get_site_option( 'bstwbsftwppdtplgns_options' ) )
					add_site_option( 'bstwbsftwppdtplgns_options', array() );
				$bstwbsftwppdtplgns_options = get_site_option( 'bstwbsftwppdtplgns_options' );
			} else {
				if ( ! get_option( 'bstwbsftwppdtplgns_options' ) )
					add_option( 'bstwbsftwppdtplgns_options', array() );
				$bstwbsftwppdtplgns_options = get_option( 'bstwbsftwppdtplgns_options' );
			}
		}

		if ( isset( $bstwbsftwppdtplgns_options['cbx_menu_version'] ) ) {
			$bstwbsftwppdtplgns_options['cbx_menu']['version'][ $base ] = $cbx_menu_version;
			unset( $bstwbsftwppdtplgns_options['cbx_menu_version'] );
			if ( function_exists( 'is_multisite' ) && is_multisite() )
				update_site_option( 'bstwbsftwppdtplgns_options', $bstwbsftwppdtplgns_options );
			else
				update_option( 'bstwbsftwppdtplgns_options', $bstwbsftwppdtplgns_options );
			require_once( dirname( __FILE__ ) . '/cbx_menu.php' );
			require_once( dirname( __FILE__ ) . '/cbx_functions.php' );
			require_once( dirname( __FILE__ ) . '/class-cbx-settings.php' );
		} else if ( ! $is_pro_cbx_menu && ( ! isset( $bstwbsftwppdtplgns_options['cbx_menu']['version'][ $base ] ) || $bstwbsftwppdtplgns_options['cbx_menu']['version'][ $base ] != $cbx_menu_version ) ) {
			$bstwbsftwppdtplgns_options['cbx_menu']['version'][ $base ] = $cbx_menu_version;
			if ( function_exists( 'is_multisite' ) && is_multisite() )
				update_site_option( 'bstwbsftwppdtplgns_options', $bstwbsftwppdtplgns_options );
			else
				update_option( 'bstwbsftwppdtplgns_options', $bstwbsftwppdtplgns_options );
			require_once( dirname( __FILE__ ) . '/cbx_menu.php' );
			require_once( dirname( __FILE__ ) . '/cbx_functions.php' );
			require_once( dirname( __FILE__ ) . '/class-cbx-settings.php' );
		} else if ( $is_pro_cbx_menu && ( ! isset( $bstwbsftwppdtplgns_options['cbx_menu']['version_pro'][ $base ] ) || $bstwbsftwppdtplgns_options['cbx_menu']['version_pro'][ $base ] != $cbx_menu_version ) ) {
			$bstwbsftwppdtplgns_options['cbx_menu']['version_pro'][ $base ] = $cbx_menu_version;
			
			if ( isset( $bstwbsftwppdtplgns_options['cbx_menu']['version'][ $base ] ) )
				unset( $bstwbsftwppdtplgns_options['cbx_menu']['version'][ $base ] );
			
			if ( function_exists( 'is_multisite' ) && is_multisite() )
				update_site_option( 'bstwbsftwppdtplgns_options', $bstwbsftwppdtplgns_options );
			else
				update_option( 'bstwbsftwppdtplgns_options', $bstwbsftwppdtplgns_options );
			require_once( dirname( __FILE__ ) . '/cbx_menu.php' );
			require_once( dirname( __FILE__ ) . '/cbx_functions.php' );
			require_once( dirname( __FILE__ ) . '/class-cbx-settings.php' );
		} else if ( ! isset( $bstwbsftwppdtplgns_added_menu ) ) {

			$all_plugins = get_plugins();
			$all_themes = wp_get_themes();

			if ( ! empty( $bstwbsftwppdtplgns_options['cbx_menu']['version_pro'] ) ) {
				foreach ( $bstwbsftwppdtplgns_options['cbx_menu']['version_pro'] as $key => $value ) {
					if ( array_key_exists( $key, $all_plugins ) || array_key_exists( $key, $all_themes ) ) {
						if ( $cbx_menu_version < $value && ( is_plugin_active( $key ) || preg_match( '|' . $key . '$|', get_template_directory() ) ) ) {
							if ( ! isset( $product_with_newer_menu ) )
								$product_with_newer_menu = $key;
							elseif ( $bstwbsftwppdtplgns_options['cbx_menu']['version_pro'][ $product_with_newer_menu ] < $bstwbsftwppdtplgns_options['cbx_menu']['version_pro'][ $key ] )
								$product_with_newer_menu = $key;
						}
					} else {
						unset( $bstwbsftwppdtplgns_options['cbx_menu']['version_pro'][ $key ] );
						if ( function_exists( 'is_multisite' ) && is_multisite() )
							update_site_option( 'bstwbsftwppdtplgns_options', $bstwbsftwppdtplgns_options );
						else
							update_option( 'bstwbsftwppdtplgns_options', $bstwbsftwppdtplgns_options );
					}
				}
			}

			if ( ! isset( $product_with_newer_menu ) ) {
				if ( $is_pro_cbx_menu ) {
					$product_with_newer_menu = $base;
				} else {
					foreach ( $bstwbsftwppdtplgns_options['cbx_menu']['version'] as $key => $value ) {
						if ( array_key_exists( $key, $all_plugins ) || array_key_exists( $key, $all_themes ) ) {
							if ( $cbx_menu_version < $value && ( is_plugin_active( $key ) || preg_match( '|' . $key . '$|', get_template_directory() ) ) ) {
								if ( ! isset( $product_with_newer_menu ) )
									$product_with_newer_menu = $key;
								elseif ( $bstwbsftwppdtplgns_options['cbx_menu']['version'][ $product_with_newer_menu ] < $bstwbsftwppdtplgns_options['cbx_menu']['version'][ $key ] )
									$product_with_newer_menu = $key;
							}
						} else {
							unset( $bstwbsftwppdtplgns_options['cbx_menu']['version'][ $key ] );
							if ( function_exists( 'is_multisite' ) && is_multisite() )
								update_site_option( 'bstwbsftwppdtplgns_options', $bstwbsftwppdtplgns_options );
							else
								update_option( 'bstwbsftwppdtplgns_options', $bstwbsftwppdtplgns_options );
						}
					}
				}
			}

			if ( ! isset( $product_with_newer_menu ) )
				$product_with_newer_menu = $base;

			$folder_with_newer_menu = explode( '/', $product_with_newer_menu );

			if ( array_key_exists( $product_with_newer_menu, $all_plugins ) ) {
				$cbx_menu_source = 'plugins';
				$cbx_menu_new_dir = $wp_plugins_dir . '/' . $folder_with_newer_menu[0];
			} else if ( array_key_exists( $product_with_newer_menu, $all_themes ) ) {
				$cbx_menu_source = 'themes';
				$cbx_menu_new_dir = $wp_content_dir . '/themes/' . $folder_with_newer_menu[0] . '/inc';
			} else {
				$cbx_menu_new_dir = '';
			}

			if ( file_exists( $cbx_menu_new_dir . '/cbx_menu/cbx_functions.php' ) ) {
				require_once( $cbx_menu_new_dir . '/cbx_menu/cbx_functions.php' );
				require_once( $cbx_menu_new_dir . '/cbx_menu/cbx_menu.php' );
				require_once( $cbx_menu_new_dir . '/cbx_menu/class-cbx-settings.php' );
			} else {
				require_once( dirname( __FILE__ ) . '/cbx_menu.php' );
				require_once( dirname( __FILE__ ) . '/cbx_functions.php' );
				require_once( dirname( __FILE__ ) . '/class-cbx-settings.php' );
			}

			$bstwbsftwppdtplgns_added_menu = true;
		}
	}
}