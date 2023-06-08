<?php
/*
* Deprecated functions for CybexSecurity plugins
*/

/**
* Function add cbx Plugins page
* @deprecated 1.9.8 (15.12.2016)
* @return void
*/
if ( ! function_exists ( 'cbx_general_menu' ) ) {
	function cbx_general_menu() {
		global $menu, $cbx_general_menu_exist;

		if ( ! $cbx_general_menu_exist ) {
			/* we check also menu exist in global array as in old plugins $cbx_general_menu_exist variable not exist */
			foreach ( $menu as $value_menu ) {
				if ( 'cbx_panel' == $value_menu[2] ) {
					$cbx_general_menu_exist = true;
					return;
				}
			}

			add_menu_page( 'cbx Panel', 'cbx Panel', 'manage_options', 'cbx_panel', 'cbx_add_menu_render', 'none', '1001' );

			add_submenu_page( 'cbx_panel', __( 'Plugins', 'xcellorate' ), __( 'Plugins', 'xcellorate' ), 'manage_options', 'cbx_panel', 'cbx_add_menu_render' );
			add_submenu_page( 'cbx_panel', __( 'Themes', 'xcellorate' ), __( 'Themes', 'xcellorate' ), 'manage_options', 'cbx_themes', 'cbx_add_menu_render' );
			add_submenu_page( 'cbx_panel', __( 'System Status', 'xcellorate' ), __( 'System Status', 'xcellorate' ), 'manage_options', 'cbx_system_status', 'cbx_add_menu_render' );

			$cbx_general_menu_exist = true;
		}
	}
}

/**
* Function process submit on the `Go Pro` tab
* @deprecated 1.9.8 (15.12.2016)
* @todo Remove function after 01.01.2021
*/
if ( ! function_exists( 'cbx_go_pro_tab_check' ) ) {
	function cbx_go_pro_tab_check( $plugin_basename, $plugin_options_name = false, $is_network_option = false ) {
		global $bstwbsftwppdtplgns_options;
		if ( ! isset( $bstwbsftwppdtplgns_options ) )
			$bstwbsftwppdtplgns_options = ( function_exists( 'is_multisite' ) && is_multisite() ) ? get_site_option( 'bstwbsftwppdtplgns_options' ) : get_option( 'bstwbsftwppdtplgns_options' );
		if ( ! isset( $bstwbsftwppdtplgns_options['deprecated_function']['cbx_go_pro_tab_check'] ) ) {
			$get_debug_backtrace = debug_backtrace();
			$file = ( ! empty( $get_debug_backtrace[0]['file'] ) ) ? $get_debug_backtrace[0]['file'] : '';
			$bstwbsftwppdtplgns_options['deprecated_function']['cbx_go_pro_tab_check'] = array(
				'file' => $file
			);
			if ( is_multisite() )
				update_site_option( 'bstwbsftwppdtplgns_options', $bstwbsftwppdtplgns_options );
			else
				update_option( 'bstwbsftwppdtplgns_options', $bstwbsftwppdtplgns_options );
		}
	}
}

/**
* Function display 'Custom code' tab
*
* @deprecated 1.9.8 (15.12.2016)
* @todo Remove function after 01.01.2021
*/
if ( ! function_exists( 'cbx_custom_code_tab' ) ) {
	function cbx_custom_code_tab() {
		global $bstwbsftwppdtplgns_options;
		if ( ! isset( $bstwbsftwppdtplgns_options ) )
			$bstwbsftwppdtplgns_options = ( function_exists( 'is_multisite' ) && is_multisite() ) ? get_site_option( 'bstwbsftwppdtplgns_options' ) : get_option( 'bstwbsftwppdtplgns_options' );
		if ( ! isset( $bstwbsftwppdtplgns_options['deprecated_function']['cbx_custom_code_tab'] ) ) {
			$get_debug_backtrace = debug_backtrace();
			$file = ( ! empty( $get_debug_backtrace[0]['file'] ) ) ? $get_debug_backtrace[0]['file'] : '';
			$bstwbsftwppdtplgns_options['deprecated_function']['cbx_custom_code_tab'] = array(
				'file' => $file
			);
			if ( is_multisite() )
				update_site_option( 'bstwbsftwppdtplgns_options', $bstwbsftwppdtplgns_options );
			else
				update_option( 'bstwbsftwppdtplgns_options', $bstwbsftwppdtplgns_options );
		}
	}
}

/**
* Function check license key for Pro plugins version
* @deprecated 1.9.8 (15.12.2016)
* @todo Remove function after 01.01.2021
*/
if ( ! function_exists( 'cbx_check_pro_license' ) ) {
	function cbx_check_pro_license( $plugin_basename, $trial_plugin = false ) {
		global $bstwbsftwppdtplgns_options;
		if ( ! isset( $bstwbsftwppdtplgns_options ) )
			$bstwbsftwppdtplgns_options = ( function_exists( 'is_multisite' ) && is_multisite() ) ? get_site_option( 'bstwbsftwppdtplgns_options' ) : get_option( 'bstwbsftwppdtplgns_options' );
		if ( ! isset( $bstwbsftwppdtplgns_options['deprecated_function']['cbx_custom_code_tab'] ) ) {
			$get_debug_backtrace = debug_backtrace();
			$file = ( ! empty( $get_debug_backtrace[0]['file'] ) ) ? $get_debug_backtrace[0]['file'] : '';
			$bstwbsftwppdtplgns_options['deprecated_function']['cbx_custom_code_tab'] = array(
				'file' => $file
			);
			if ( is_multisite() )
				update_site_option( 'bstwbsftwppdtplgns_options', $bstwbsftwppdtplgns_options );
			else
				update_option( 'bstwbsftwppdtplgns_options', $bstwbsftwppdtplgns_options );
		}
	}
}

/**
* Function display block for checking license key for Pro plugins version
* @deprecated 1.9.8 (15.12.2016)
* @todo add notice and remove functional after 01.01.2018. Remove function after 01.01.2019
*/
if ( ! function_exists ( 'cbx_check_pro_license_form' ) ) {
	function cbx_check_pro_license_form( $plugin_basename ) {
		global $bstwbsftwppdtplgns_options;
		$license_key = ( isset( $bstwbsftwppdtplgns_options[ $plugin_basename ] ) ) ? $bstwbsftwppdtplgns_options[ $plugin_basename ] : ''; ?>
		<div class="clear"></div>
		<form method="post" action="">
			<p><?php echo _e( 'If necessary, you can check if the license key is correct or reenter it in the field below. You can find your license key on your personal page - Client Area - on our website', 'xcellorate' ) . ' <a href="https://cybexsecurity.co.uk/client-area">https://cybexsecurity.co.uk/client-area</a> ' . __( '(your username is the email address specified during the purchase). If necessary, please submit "Lost your password?" request.', 'xcellorate' ); ?></p>
			<p>
				<input type="text" maxlength="100" name="cbx_license_key" value="<?php echo esc_attr( $license_key ); ?>" />
				<input type="hidden" name="cbx_license_submit" value="submit" />
				<input type="submit" class="button" value="<?php _e( 'Check license key', 'xcellorate' ) ?>" />
				<?php wp_nonce_field( $plugin_basename, 'cbx_license_nonce_name' ); ?>
			</p>
		</form>
	<?php }
}

/**
* Function process submit on the `Go Pro` tab for TRIAL
* @deprecated 1.9.8 (15.12.2016)
* @todo add notice and remove functional after 01.01.2018. Remove function after 01.01.2019
*/
if ( ! function_exists( 'cbx_go_pro_from_trial_tab' ) ) {
	function cbx_go_pro_from_trial_tab( $plugin_info, $plugin_basename, $page, $link_slug, $link_key, $link_pn, $trial_license_is_set = true ) {
		global $wp_version, $bstwbsftwppdtplgns_options;
		$cbx_license_key = ( isset( $_POST['cbx_license_key'] ) ) ? sanitize_text_field( $_POST['cbx_license_key'] ) : "";
		if ( $trial_license_is_set ) { ?>
			<form method="post" action="">
				<p>
					<?php printf( __( 'In order to continue using the plugin it is necessary to buy a %s license.', 'xcellorate' ), '<a href="https://cybexsecurity.co.uk/products/wordpress/plugins/' . $link_slug . '/?k=' . $link_key . '&amp;pn=' . $link_pn . '&amp;v=' . $plugin_info["Version"] . '&amp;wp_v=' . $wp_version .'" target="_blank" title="' . $plugin_info["Name"] . '">Pro</a>' ); ?> <?php _e( 'After that, you can activate it by entering your license key.', 'xcellorate' ); ?>
					<br />
					<span class="cbx_info">
						<?php _e( 'License key can be found in the', 'xcellorate' ); ?>
						<a href="https://cybexsecurity.co.uk/wp-login.php">Client Area</a>
						<?php _e( '(your username is the email address specified during the purchase).', 'xcellorate' ); ?>
					</span>
				</p>
				<?php if ( isset( $bstwbsftwppdtplgns_options['go_pro'][ $plugin_basename ]['count'] ) &&
					'5' < $bstwbsftwppdtplgns_options['go_pro'][ $plugin_basename ]['count'] &&
					$bstwbsftwppdtplgns_options['go_pro'][ $plugin_basename ]['time'] > ( time() - ( 24 * 60 * 60 ) ) ) { ?>
					<p>
						<input disabled="disabled" type="text" name="cbx_license_key" value="" />
						<input disabled="disabled" type="submit" class="button-primary" value="<?php _e( 'Activate', 'xcellorate' ); ?>" />
					</p>
					<p><?php _e( "Unfortunately, you have exceeded the number of available tries per day.", 'xcellorate' ); ?></p>
				<?php } else { ?>
					<p>
						<input type="text" maxlength="100" name="cbx_license_key" value="" />
						<input type="hidden" name="cbx_license_plugin" value="<?php echo esc_attr( $plugin_basename ); ?>" />
						<input type="hidden" name="cbx_license_submit" value="submit" />
						<input type="submit" class="button-primary" value="<?php _e( 'Activate', 'xcellorate' ); ?>" />
						<?php wp_nonce_field( $plugin_basename, 'cbx_license_nonce_name' ); ?>
					</p>
				<?php } ?>
			</form>
		<?php } else { 
			$page_url = esc_url( self_admin_url( 'admin.php?page=' . $page ) ); ?>
			<p><?php _e( "Congratulations! The Pro license of the plugin is activated successfully.", 'xcellorate' ); ?></p>
			<p>
				<?php _e( "Please, go to", 'xcellorate' ); ?> <a href="<?php echo $page_url; ?>"><?php _e( 'the setting page', 'xcellorate' ); ?></a>
			</p>
		<?php }
	}
}


/**
* Function display block for restoring default product settings
* @deprecated 1.9.8 (15.12.2016)
* @todo add notice and remove functional after 01.01.2018. Remove function after 01.01.2019
*/
if ( ! function_exists ( 'cbx_form_restore_default_settings' ) ) {
	function cbx_form_restore_default_settings( $plugin_basename, $change_permission_attr = '' ) { ?>
		<form method="post" action="">
			<p><?php _e( 'Restore all plugin settings to defaults', 'xcellorate' ); ?></p>
			<p>
				<input <?php echo $change_permission_attr; ?> type="submit" class="button" value="<?php _e( 'Restore settings', 'xcellorate' ); ?>" />
			</p>
			<input type="hidden" name="cbx_restore_default" value="submit" />
			<?php wp_nonce_field( $plugin_basename, 'cbx_settings_nonce_name' ); ?>
		</form>
	<?php }
}

/**
* Function display GO PRO tab
* @deprecated 1.9.8 (15.12.2016)
* @todo add notice and remove functional after 01.01.2018. Remove function after 01.01.2019
*/
if ( ! function_exists( 'cbx_go_pro_tab_show' ) ) {
	function cbx_go_pro_tab_show( $cbx_hide_premium_options_check, $plugin_info, $plugin_basename, $page, $pro_page, $cbx_license_plugin, $link_slug, $link_key, $link_pn, $pro_plugin_is_activated = false, $trial_days_number = false ) {
		global $wp_version, $bstwbsftwppdtplgns_options;
		$cbx_license_key = ( isset( $_POST['cbx_license_key'] ) ) ? sanitize_text_field( $_POST['cbx_license_key'] ) : "";
		if ( $pro_plugin_is_activated ) { 
			$page_url = esc_url( self_admin_url( 'admin.php?page=' . $pro_page ) ); ?>
			<p><?php _e( "Congratulations! Pro version of the plugin is  installed and activated successfully.", 'xcellorate' ); ?></p>
			<p>
				<?php _e( "Please, go to", 'xcellorate' ); ?> <a href="<?php echo $page_url; ?>"><?php _e( 'the setting page', 'xcellorate' ); ?></a>
			</p>
		<?php } else {
			if ( $cbx_hide_premium_options_check ) { ?>
				<form method="post" action="">
					<p>
						<input type="hidden" name="cbx_hide_premium_options_submit" value="submit" />
						<input type="submit" class="button" value="<?php _e( 'Show Pro features', 'xcellorate' ); ?>" />
						<?php wp_nonce_field( $plugin_basename, 'cbx_license_nonce_name' ); ?>
					</p>
				</form>
			<?php } ?>
			<form method="post" action="">
				<p>
					<?php _e( 'Enter your license key to install and activate', 'xcellorate' ); ?>
					<a href="<?php echo esc_url( 'https://cybexsecurity.co.uk/products/wordpress/plugins/' . $link_slug . '/?k=' . $link_key . '&pn=' . $link_pn . '&v=' . $plugin_info["Version"] . '&wp_v=' . $wp_version ); ?>" target="_blank" title="<?php echo $plugin_info["Name"]; ?> Pro">Pro</a>
					<?php _e( 'version of the plugin.', 'xcellorate' ); ?><br />
					<span class="cbx_info">
						<?php _e( 'License key can be found in the', 'xcellorate' ); ?>
						<a href="https://cybexsecurity.co.uk/wp-login.php">Client Area</a>
						<?php _e( '(your username is the email address specified during the purchase).', 'xcellorate' ); ?>
					</span>
				</p>
				<?php if ( $trial_days_number !== false )
					$trial_days_number = __( 'or', 'xcellorate' ) . ' <a href="https://cybexsecurity.co.uk/products/wordpress/plugins/' . $link_slug . '/trial/" target="_blank">' . sprintf( __( 'Start Your Free %s-Day Trial Now', 'xcellorate' ), $trial_days_number ) . '</a>';
				if ( isset( $bstwbsftwppdtplgns_options['go_pro'][ $cbx_license_plugin ]['count'] ) &&
					'5' < $bstwbsftwppdtplgns_options['go_pro'][ $cbx_license_plugin ]['count'] &&
					$bstwbsftwppdtplgns_options['go_pro'][ $cbx_license_plugin ]['time'] > ( time() - ( 24 * 60 * 60 ) ) ) { ?>
					<p>
						<input disabled="disabled" type="text" name="cbx_license_key" value="<?php echo esc_attr( $cbx_license_key ); ?>" />
						<input disabled="disabled" type="submit" class="button-primary" value="<?php _e( 'Activate', 'xcellorate' ); ?>" />
						<?php if ( $trial_days_number !== false ) echo $trial_days_number; ?>
					</p>
					<p><?php _e( "Unfortunately, you have exceeded the number of available tries per day. Please, upload the plugin manually.", 'xcellorate' ); ?></p>
				<?php } else { ?>
					<p>
						<input type="text" maxlength="100" name="cbx_license_key" value="<?php echo esc_attr( $cbx_license_key ); ?>" />
						<input type="hidden" name="cbx_license_plugin" value="<?php echo esc_attr( $cbx_license_plugin ); ?>" />
						<input type="hidden" name="cbx_license_submit" value="submit" />
						<input type="submit" class="button-primary" value="<?php _e( 'Activate', 'xcellorate' ); ?>" />
						<?php if ( $trial_days_number !== false )
							echo $trial_days_number;
						wp_nonce_field( $plugin_basename, 'cbx_license_nonce_name' ); ?>
					</p>
				<?php } ?>
			</form>
		<?php }
	}
}

/**
* Function display GO PRO Banner (inline in 'admin_notices' action )
* @deprecated 2.2.5 (29.11.2019)
* @todo Remove notice after 01.12.2021
*/
if ( ! function_exists( 'cbx_plugin_banner' ) ) {
	function cbx_plugin_banner( $plugin_info, $this_banner_prefix, $link_slug, $link_key, $link_pn, $banner_url_or_slug ) {
		/* the function is not longer use, but we need to store it */
	}
}

/**
* Function display timeout PRO Banner (inline in 'admin_notices' action )
* @deprecated 2.2.5 (29.11.2019)
* @todo Remove notice after 01.12.2021
*/
if ( ! function_exists ( 'cbx_plugin_banner_timeout' ) ) {
	function cbx_plugin_banner_timeout( $plugin_key, $plugin_prefix, $plugin_name, $banner_url_or_slug = false ) {
		/* the function is not longer use, but we need to store it */
	}
}