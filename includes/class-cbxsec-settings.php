<?php
/**
 * Displays the content on the plugin settings page
 */

if ( ! class_exists( 'Cbxsec_Settings_Tabs' ) ) {
	class Cbxsec_Settings_Tabs extends cbx_Settings_Tabs {
		public $active_plugins;
		public $all_plugins;
		/**
		 * Constructor.
		 *
		 * @access public
		 *
		 * @see cbx_Settings_Tabs::__construct() for more information on default arguments.
		 *
		 * @param string $plugin_basename
		 */
		public function __construct( $plugin_basename ) {
			global $cbxsec_options, $cbxsec_plugin_info;

			$tabs = array(
				'url' 			=> array( 'label' => __( 'Login Url', 'cybex-security' ) ),
				'login' 		=> array( 'label' => __( 'Limit Login', 'cybex-security' ) ),
				'2fa' 			=> array( 'label' => __( '2FA', 'cybex-security' ) ),
				'errors' 		=> array( 'label' => __( 'Errors', 'cybex-security' ) ),
				'notifications' => array( 'label' => __( 'Notifications', 'cybex-security' ) ),
				'misc' 			=> array( 'label' => __( 'Misc', 'cybex-security' ) ),
				'custom_code' 	=> array( 'label' => __( 'Custom Code', 'cybex-security' ) ),
			);

			parent::__construct( array(
				'plugin_basename' 	 => $plugin_basename,
				'plugins_info'		 => $cbxsec_plugin_info,
				'prefix' 			 => 'cbxsec',
				'default_options' 	 => cbxsec_get_options_default(),
				'options' 			 => $cbxsec_options,
				'tabs' 				 => $tabs,
				'wp_slug'			 => 'cybex-security',
				'link_key' 			 => 'fdac994c203b41e499a2818c409ff2bc',
				'link_pn' 			 => '140',
                'doc_link'           => ''
			) );

			add_action( get_parent_class( $this ) . '_additional_misc_options_affected', array( $this, 'additional_misc_options_affected' ) );
		}

		/**
		 * Save plugin options to the database
		 * @access public
		 * @param  void
		 * @return array    The action results
		 */
		public function save_options() {
			global $wpdb;

			$message = $notice = $error = '';

			if ( ! $this->all_plugins ) {
				if ( ! function_exists( 'get_plugins' ) )
					require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
				$this->all_plugins = get_plugins();
			}
			if ( ! $this->active_plugins ) {
				if ( $this->is_multisite ) {
					$this->active_plugins = (array) array_keys( get_site_option( 'active_sitewide_plugins', array() ) );
					$this->active_plugins = array_merge( $this->active_plugins , get_option( 'active_plugins' ) );
				} else {
					$this->active_plugins = get_option( 'active_plugins' );
				}
			}

			$numeric_options = array(
				'rwl_page_input', 'rwl_redirect_func',
				'allowed_retries', 'days_of_lock', 'hours_of_lock', 'minutes_of_lock',
				'days_to_reset', 'hours_to_reset', 'minutes_to_reset', 'allowed_locks',
				'days_to_reset_block', 'hours_to_reset_block', 'minutes_to_reset_block',
				'letters_days', 'letters_hours', 'letters_minutes', 'letters_seconds'
			);
			$force_reset_block_event = false;
			foreach ( $numeric_options as $option ) {
				if ( isset( $_POST["cbxsec_{$option}"] ) && $_POST["cbxsec_{$option}"] != $this->options[ $option ] )
					$force_reset_block_event = true;
				$this->options[ $option ] = isset( $_POST["cbxsec_{$option}"] ) ? sanitize_text_field( $_POST["cbxsec_{$option}"] ) : $this->options[ $option ];
			}

			if ( isset( $_POST["cbxsecl_rwl_page_input"] ) ) {
				$this->options["cbxsecl_rwl_page_input"] = sanitize_title_with_dashes( $_POST["cbxsecl_rwl_page_input"] );
				update_site_option( 'rwl_page', $this->options["cbxsecl_rwl_page_input"] );
				if ( $_POST["cbxsecl_rwl_page_input"] !== $this->options["cbxsecl_rwl_page_input"] ) {
					$force_reset_block_event = true;
				}
			}

			if ( isset( $_POST["cbxsecl_rwl_redirect_func"] ) ) {
				$this->options["cbxsecl_rwl_redirect_func"] = sanitize_title_with_dashes( $_POST["cbxsecl_rwl_redirect_func"] );
				update_option( 'rwl_redirect_field', $this->options["cbxsecl_rwl_redirect_func"] );
				if ( $_POST["cbxsecl_rwl_redirect_func"] !== $this->options["cbxsecl_rwl_redirect_func"] ) {
					$force_reset_block_event = true;
				}
			}
			
			if ( $this->options['days_of_lock'] == 0 && $this->options['hours_of_lock'] == 0 && $this->options['minutes_of_lock'] == 0 )
				$this->options['minutes_of_lock'] = 1;
			if ( $this->options['days_to_reset'] == 0 && $this->options['hours_to_reset'] == 0 && $this->options['minutes_to_reset'] == 0 )
				$this->options['minutes_to_reset'] = 1;
			if ( $this->options['days_to_reset_block'] == 0 && $this->options['hours_to_reset_block'] == 0 && $this->options['minutes_to_reset_block'] == 0 )
				$this->options['minutes_to_reset_block'] = 1;
			if ( 0 == $this->options['letters_days'] && 0 == $this->options['letters_hours'] && 0 == $this->options['letters_minutes'] && 0 == $this->options['letters_seconds'] )
				$this->options['letters_minutes'] = 1;

			if ( $force_reset_block_event ) {
				wp_clear_scheduled_hook( 'cbxsec_event_for_reset_block' );
				cbxsec_reset_block();
			}
			if ( isset( $_POST["cbxsec_days_to_clear_statistics"] ) ) {
				if ( $this->options["days_to_clear_statistics"] != $_POST["cbxsec_days_to_clear_statistics"] ) {
					if ( $this->options["days_to_clear_statistics"] == 0 ) {
                        $time = time() - fmod( time(), 86400 ) + 86400;
						wp_schedule_event( $time, 'daily', "cbxsec_daily_statistics_clear" );
					} elseif ( $_POST["cbxsec_days_to_clear_statistics"] == 0 ) {
						wp_clear_scheduled_hook( "cbxsec_daily_statistics_clear" );
					}
				}
				$this->options["days_to_clear_statistics"] = absint( $_POST["cbxsec_days_to_clear_statistics"] );
			}

			$this->options['hide_login_form'] = isset( $_POST['cbxsec_hide_login_form'] ) ? 1: 0;

			/* Updating options of interaction with Htaccess plugin */
			$htaccess_is_active = 0 < count( preg_grep( '/htaccess\/htaccess.php/', $this->active_plugins ) ) || 0 < count( preg_grep( '/htaccess-pro\/htaccess-pro.php/', $this->active_plugins ) ) ? true : false;
			if ( isset( $_POST['cbxsec_block_by_htaccess'] ) ) {
				if ( $htaccess_is_active && 0 == $this->options['block_by_htaccess'] ) {
					$blocked_ips = $wpdb->get_col( "SELECT `ip` FROM `{$wpdb->prefix}cbxsec_denylist`;" );
					if ( is_array( $blocked_ips ) && ! empty( $blocked_ips ) ) {
						do_action( 'cbxsec_htaccess_hook_for_block', $blocked_ips );
					}

					$whitelisted_ips = $wpdb->get_col( "SELECT `ip` FROM `{$wpdb->prefix}cbxsec_allowlist`;" );
					if ( is_array( $whitelisted_ips ) && ! empty( $whitelisted_ips ) ) {
						do_action( 'cbxsec_htaccess_hook_for_add_to_whitelist', $whitelisted_ips );
					}
				}
				$this->options['block_by_htaccess'] = 1;
			} else {
				if ( $htaccess_is_active && 1 == $this->options['block_by_htaccess'] ) {
					do_action( 'cbxsec_htaccess_hook_for_delete_all' );
				}
				$this->options['block_by_htaccess'] = 0;
			}

			/*Updating options of interaction with Captcha plugin in login form*/
			$this->options['login_form_captcha_check']      = isset( $_POST['cbxsec_login_form_captcha_check'] ) ? 1 : 0;
			$this->options['login_form_recaptcha_check']    = isset( $_POST['cbxsec_login_form_recaptcha_check'] ) ? 1 : 0;

			// save CF options
			$this->options['contact_form_restrict_sending_emails'] = ( isset( $_POST['cbxsec_contact_form'] ) ) ? 1 : 0;
			$this->options['number_of_letters'] = ( isset( $_POST['cbxsec_number_of_letters'] ) ) ? absint( $_POST['cbxsec_number_of_letters'] ) : 1;

			/* Updating options with notify by email options */
			$this->options['notify_email'] = isset( $_POST['cbxsec_notify_email'] ) && ! empty( $_POST['cbxsec_email_denylisted'] ) && ! empty( $_POST['cbxsec_email_blocked'] ) ? true : false;
			if ( isset( $_POST['cbxsec_mailto'] ) ) {
				$this->options['mailto'] = isset( $_POST['cbxsec_mailto'] ) ? sanitize_email( $_POST['cbxsec_mailto'] ) : '';
				if ( 'admin' == $_POST['cbxsec_mailto'] && isset( $_POST['cbxsec_user_email_address'] ) ) {
					$this->options['email_address'] = isset( $_POST['cbxsec_user_email_address'] ) ? sanitize_email( $_POST['cbxsec_user_email_address'] ) : '';

				} elseif ( 'custom' == $_POST['cbxsec_mailto'] && isset( $_POST['cbxsec_email_address'] ) && is_email( $_POST['cbxsec_email_address'] ) ) {
					$this->options['email_address'] = isset( $_POST['cbxsec_email_address'] ) ? sanitize_email( $_POST['cbxsec_email_address'] ) : '';
				}
			}
			/* array for saving and restoring default messages */
			$messages = array(
				'failed_message', 'blocked_message', 'denylisted_message', 'email_subject', 'email_subject_denylisted',
				'email_blocked', 'email_denylisted'
			);
			/* Update messages when login failed, address blocked or denylisted, email subject and text when address blocked or denylisted */
			foreach ( $messages as $single_message ) {
				if ( ! empty( $_POST["cbxsec_{$single_message}"] ) )
					$this->options[ $single_message ] = trim( sanitize_text_field( $_POST["cbxsec_{$single_message}"] ) );
			}

			/* Restore default messages */
			if ( isset( $_POST['cbxsec_return_default'] ) ) {
				$default_messages = cbxsec_get_default_messages();
				if ( 'email' == $_POST['cbxsec_return_default'] ) {
					unset( $default_messages['failed_message'], $default_messages['blocked_message'], $default_messages['denylisted_message'] );
					$message = __( 'Email notifications have been restored to default.', 'cybex-security' ) . '<br />';
				} else {
					unset( $default_messages['email_subject'], $default_messages['email_subject_denylisted'], $default_messages['email_blocked'], $default_messages['email_denylisted'] );
					$message = __( 'Messages have been restored to default.', 'cybex-security' ) . '<br />';
				}
				foreach ( $default_messages as $key => $value ) {
					$this->options[ $key ] = $value;
				}
			}

			$this->options = array_map( 'stripslashes_deep', $this->options );

			$message .= __( 'Settings saved.', 'cybex-security' );

			update_option( 'cbxsec_options', $this->options );

			return compact( 'message', 'notice', 'error' );
		}

		/**
		 *    	Login Url teb in setting
		 * 		@access public
		 */
		public function tab_url() {
			global $wp_version;
			if ( ! $this->all_plugins ) {
				if ( ! function_exists( 'get_plugins' ) )
					require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
				$this->all_plugins = get_plugins();
			}
			if ( ! $this->active_plugins ) {
				if ( $this->is_multisite ) {
					$this->active_plugins = (array) array_keys( get_site_option( 'active_sitewide_plugins', array() ) );
					$this->active_plugins = array_merge( $this->active_plugins , get_option( 'active_plugins' ) );
				} else {
					$this->active_plugins = get_option( 'active_plugins' );
				}
			} ?>
			<h3 class="cbx_tab_label"><?php _e( 'Wp Login Url Settings', 'cybex-security' ); ?></h3>
			<?php $this->help_phrase(); ?>
			<hr>
			<table class="form-table cbxsec_settings_form">
				<tr>
					<th><?php _e( 'Login url', 'cybex-security' ); ?></th>
					<td>
						<code> <?php echo  trailingslashit( home_url() ); ?> </code>
						<input type="text" value="<?php echo get_site_option( 'rwl_page', 'login' ); ?>" name="cbxsecl_rwl_page_input" />
						<code>/</code> 
						<!-- <?php print_r(get_site_option( 'rwl_page', 'login' )); ?> -->
						<div class="cbx_info"><?php printf('If you leave the above field empty the login url defaults to <code>'.trailingslashit( home_url() ).'login </code>', 'cybex-security'); ?></div>
					</td>
				</tr>
				<tr>
					<th><?php _e( 'Redirect URL', 'cybex-security' ); ?></th>
					<td>
						<code> <?php echo  trailingslashit( home_url() ); ?> </code>
						<input type="text" value="<?php echo get_option( 'rwl_redirect_field'); ?>" name="cbxsecl_rwl_redirect_func" />
						<code>/</code>
						<!-- <?php print_r(get_option( 'rwl_redirect_field' )); ?>  -->
						<div class="cbx_info"><?php printf('If you leave the above field empty the plugin will add a redirect to the website homepage.', 'cybex-security'); ?></div>
					</td>
			 	</tr>

			</table>
		<?php }


		/**
		 *    	Login Url teb in setting
		 * 		@access public
		 */
		public function tab_2fa() {
			global $wp_version;
			if ( ! $this->all_plugins ) {
				if ( ! function_exists( 'get_plugins' ) )
					require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
				$this->all_plugins = get_plugins();
			}
			if ( ! $this->active_plugins ) {
				if ( $this->is_multisite ) {
					$this->active_plugins = (array) array_keys( get_site_option( 'active_sitewide_plugins', array() ) );
					$this->active_plugins = array_merge( $this->active_plugins , get_option( 'active_plugins' ) );
				} else {
					$this->active_plugins = get_option( 'active_plugins' );
				}
			} ?>
			<h3 class="cbx_tab_label"><?php _e( 'Google Authenticator', 'cybex-security' ); ?></h3>
			<?php $this->help_phrase(); ?>
			<hr>
			<table class="form-table cbxsec_settings_form">
				<tr>
					<th>
						<p style='font-size:16px; font-weight: 400; word-break: unset;'>
							The two-factor authentication requirement can be enabled on a per-user basis. <br>
							You could enable it for your administrator account and for enable this feature 
							<a href='<?php echo get_edit_user_link(get_current_user_id()); ?>#cbx_googleauth_section'>edit your profile.</a>
						</p>
					</th>
				</tr>
				
			</table>
		<?php }

		/**
		 *     		Limit Login tab in setting
		 * 			@access public
		 */
		public function tab_login() {
			global $wp_version;
			if ( ! $this->all_plugins ) {
				if ( ! function_exists( 'get_plugins' ) )
					require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
				$this->all_plugins = get_plugins();
			}
			if ( ! $this->active_plugins ) {
				if ( $this->is_multisite ) {
					$this->active_plugins = (array) array_keys( get_site_option( 'active_sitewide_plugins', array() ) );
					$this->active_plugins = array_merge( $this->active_plugins , get_option( 'active_plugins' ) );
				} else {
					$this->active_plugins = get_option( 'active_plugins' );
				}
			} ?>
			<h3 class="cbx_tab_label"><?php _e( 'Wp Limit Login Settings', 'cybex-security' ); ?></h3>
			<?php $this->help_phrase(); ?>
			<hr>
			<table class="form-table cbxsec_settings_form">
				<tr>
					<th><?php _e( 'Block IP Address After', 'cybex-security' ); ?></th>
					<td>
						<input type="number" min="1" max="99" step="1" maxlength="2" value="<?php echo $this->options['allowed_retries']; ?>" name="cbxsec_allowed_retries" /> <?php echo _n( 'attempt', 'attempts', $this->options['allowed_retries'], 'cybex-security' ); ?>
						<div class="cbx_info"><?php printf( __( 'Number of failed attempts (default is %d).', 'cybex-security' ), 5 ); ?></div>
					</td>
				</tr>
				<tr>
					<th><?php _e( 'Block IP or Email Address For', 'cybex-security' ); ?></th>
					<td>
						<fieldset id="cbxsec-time-of-lock-display" class="cbxsec_hidden cbxsec-display">
							<label<?php if ( 0 == $this->options['days_of_lock'] ) echo ' class="cbxsec-zero-value"'; ?>><span class="cbxsec-unit-measure" ><?php echo $this->options['days_of_lock']; ?></span> <?php echo _n( 'day', 'days', $this->options['days_of_lock'], 'cybex-security' ); ?></label>
							<label<?php if ( 0 == $this->options['hours_of_lock'] ) echo ' class="cbxsec-zero-value"'; ?>><span class="cbxsec-unit-measure" ><?php echo $this->options['hours_of_lock']; ?></span> <?php echo _n( 'hour', 'hours', $this->options['hours_of_lock'], 'cybex-security' ); ?></label>
							<label<?php if ( 0 == $this->options['minutes_of_lock'] ) echo ' class="cbxsec-zero-value"'; ?>><span class="cbxsec-unit-measure" ><?php echo $this->options['minutes_of_lock']; ?></span> <?php echo _n( 'minute', 'minutes', $this->options['minutes_of_lock'], 'cybex-security' ); ?></label>
							<label id="cbxsec-time-of-lock-edit" class="cbxsec-edit"><?php _e( 'Edit', 'cybex-security' ); ?></label>
						</fieldset>
						<fieldset id="cbxsec-time-of-lock" class="cbxsec-hidden-input">
							<label><input id="cbxsec-days-of-lock-display" type="number" max="999" min="0" step="1" maxlength="3" value="<?php echo $this->options['days_of_lock']; ?>" name="cbxsec_days_of_lock" /> <?php echo _n( 'day', 'days', $this->options['days_of_lock'], 'cybex-security' ); ?></label>
							<label><input id="cbxsec-hours-of-lock-display" type="number" max="23" min="0" step="1" maxlength="2" value="<?php echo $this->options['hours_of_lock']; ?>" name="cbxsec_hours_of_lock" /> <?php echo _n( 'hour', 'hours', $this->options['hours_of_lock'], 'cybex-security' ); ?></label>
							<label><input id="cbxsec-minutes-of-lock-display" type="number" max="59" min="0" step="1" maxlength="2" value="<?php echo $this->options['minutes_of_lock']; ?>" name="cbxsec_minutes_of_lock" /> <?php echo _n( 'minute', 'minutes', $this->options['minutes_of_lock'], 'cybex-security' ); ?></label>
						</fieldset>
						<div class="cbx_info">
							<?php printf( __( 'Time IP or Email address will be blocked for (default is %d hour %d minutes).', 'cybex-security' ), 1, 30); ?>
						</div>
					</td>
				</tr>
				<tr>
					<th><?php _e( 'Reset Failed Attempts After', 'cybex-security' ); ?></th>
					<td>
						<fieldset id="cbxsec-time-to-reset-display" class="cbxsec_hidden cbxsec-display">
							<label <?php if ( 0 == $this->options['days_to_reset'] ) echo 'class="cbxsec-zero-value"';  ?>><span class="cbxsec-unit-measure"><?php echo esc_html( $this->options['days_to_reset'] ); ?></span> <?php echo esc_html( _n( 'day', 'days', $this->options['days_to_reset'], 'cybex-security' ) ); ?></label>
							<label <?php if ( 0 == $this->options['hours_to_reset'] ) echo 'class="cbxsec-zero-value"';  ?>><span class="cbxsec-unit-measure"><?php echo esc_html( $this->options['hours_to_reset'] ); ?></span> <?php echo esc_html( _n( 'hour', 'hours', $this->options['hours_to_reset'], 'cybex-security' ) ); ?></label>
							<label <?php if ( 0 == $this->options['minutes_to_reset'] ) echo 'class="cbxsec-zero-value"';  ?>><span class="cbxsec-unit-measure"><?php echo esc_html( $this->options['minutes_to_reset'] ); ?></span> <?php echo esc_html( _n( 'minute', 'minutes', $this->options['minutes_to_reset'], 'cybex-security' ) ); ?></label>
							<label id="cbxsec-time-to-reset-edit" class="cbxsec-edit"><?php _e( 'Edit', 'cybex-security' ); ?></label>
						</fieldset>
						<fieldset id="cbxsec-time-to-reset" class="cbxsec-hidden-input">
							<label><input id="cbxsec-days-to-reset-display" type="number" max="999" min="0" step="1" maxlength="3" value="<?php echo $this->options['days_to_reset'] ; ?>" name="cbxsec_days_to_reset" /> <?php echo _n( 'day', 'days', $this->options['days_to_reset'], 'cybex-security' ); ?></label>
							<label><input id="cbxsec-hours-to-reset-display" type="number" max="23" min="0" step="1" maxlength="2" value="<?php echo $this->options['hours_to_reset'] ; ?>" name="cbxsec_hours_to_reset" /> <?php echo _n( 'hour', 'hours', $this->options['hours_to_reset'], 'cybex-security' ); ?></label>
							<label><input id="cbxsec-minutes-to-reset-display" type="number" max="59" min="0" step="1" maxlength="2" value="<?php echo $this->options['minutes_to_reset'] ; ?>" name="cbxsec_minutes_to_reset" /> <?php echo _n( 'minute', 'minutes', $this->options['minutes_to_reset'], 'cybex-security' ); ?></label>
						</fieldset>
						<div class="cbx_info">
							<?php _e( 'Time after which the failed attempts will be reset.', 'cybex-security' ); ?>
						</div>
					</td>
				</tr>
				<tr>
					<th><?php _e( 'Deny List IP or Email After', 'cybex-security' ); ?></th>
					<td>
						<input type="number" min="1" max="99" step="1" maxlength="2" value="<?php echo $this->options['allowed_locks']; ?>" name="cbxsec_allowed_locks" /> <?php echo _n( 'blocking', 'blockings', $this->options['allowed_locks'], 'cybex-security' ); ?>
						<div class="cbx_info"><?php _e( 'Number of blocking after which the IP or Email address will be add to deny list.', 'cybex-security' ); ?></div>
					</td>
				</tr>
			</table>
		<?php }

		/**
		 *   	Error tab in setting
		 * 		@access public
		 */
		public function tab_errors() { ?>
			<h3 class="cbx_tab_label"><?php _e( 'Error Messages Settings', 'cybex-security' ); ?></h3>
			<?php $this->help_phrase(); ?>
			<hr>
			<table class="form-table">
				<tr>
					<th scope="row"><?php _e( 'Invalid Attempt', 'cybex-security' ); ?></th>
					<td>
						<textarea rows="5" name="cbxsec_failed_message"><?php echo $this->options['failed_message']; ?></textarea>
						<div class="cbx_info">
							<?php _e( 'Allowed Variables:', 'cybex-security' ); ?><br/>
							'%ATTEMPTS%' - <?php _e( 'quantity of attempts left', 'cybex-security' ); ?>
						</div>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php _e( 'Blocked', 'cybex-security' ); ?></th>
					<td>
						<textarea rows="5" name="cbxsec_blocked_message"><?php echo $this->options['blocked_message']; ?></textarea>
						<div class="cbx_info">
							<?php _e( 'Allowed Variables:', 'cybex-security' ); ?><br/>
							'%DATE%' - <?php _e( 'blocking time', 'cybex-security' ); ?><br/>
							'%MAIL%' - <?php _e( 'administrator&rsquo;s email address', 'cybex-security' ); ?>
						</div>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php _e( 'Deny Listed', 'cybex-security' ); ?></th>
					<td>
						<textarea rows="5" name="cbxsec_denylisted_message"><?php echo $this->options['denylisted_message']; ?></textarea>
						<div class="cbx_info">
							<?php _e( 'Allowed Variables:', 'cybex-security' ); ?><br/>
							'%MAIL%' - <?php _e( 'administrator&rsquo;s email address', 'cybex-security' ); ?>
						</div>
					</td>
				</tr>
            </table>
            <table class="form-table">
				<tr>
					<th scope="row"><?php _e( 'Restore Default Error Messages', 'cybex-security' ); ?></th>
					<td>
						<button class="button-secondary" name="cbxsec_return_default" value="error"><?php _e( 'Restore Error Messages', 'cybex-security' ) ?></button>
					</td>
				</tr>
			</table>
		<?php }

		/**
		 *    	notification tab in Setting
		 * 		@access public
		 */
		public function tab_notifications() {
			/* get admins for emails */
			$userslogin = get_users( 'blog_id=' . $GLOBALS['blog_id'] . '&role=administrator' ); ?>
			<h3 class="cbx_tab_label"><?php _e( 'Email Notifications Settings', 'cybex-security' ); ?></h3>
			<?php $this->help_phrase(); ?>
			<hr>
			<table class="form-table cbxsec_settings_form">
				<tr>
					<th><?php _e( 'Email Notifications', 'cybex-security' ); ?></th>
					<td>
						<input type="checkbox" name="cbxsec_notify_email" value="1" <?php checked( $this->options['notify_email'], 1 ); ?> class="cbx_option_affect" data-affect-show=".cbxsec_email_notifications" /> <span class="cbx_info"><?php _e( 'Enable to receive email notifications.', 'cybex-security' ); ?></span>
					</td>
				</tr>
				<tr class="cbxsec_email_notifications">
					<th><?php _e( 'Send Email Notifications to', 'cybex-security' ) ?></th>
					<td>
                        <fieldset>
                            <label>
                                <input type="radio" id="cbxsec_user_mailto" name="cbxsec_mailto" value="admin" class="cbx_option_affect" data-affect-show="#cbxsec_user_email_address" data-affect-hide="#cbxsec_email_address" <?php checked( $this->options['mailto'], 'admin' ); ?> /><span class="cbx_info"><?php _e( 'User', 'cybex-security' ); ?></span>
                                
                            </label>
                            <br/>
                            <label>
                               <input type="radio" id="cbxsec_custom_mailto" name="cbxsec_mailto" value="custom" class="cbx_option_affect" data-affect-show="#cbxsec_email_address" data-affect-hide="#cbxsec_user_email_address" <?php checked( $this->options['mailto'], 'custom' ); ?> /><span class="cbx_info"><?php _e( 'Custom Email', 'cybex-security' ); ?></span>
                            </label>
                        </fieldset>
                        <select id="cbxsec_user_email_address" class="cbxsec_email_notifications_input" name="cbxsec_user_email_address">
	                        <option disabled><?php _e( "Choose a username", 'cybex-security' ); ?></option>
	                        <?php foreach ( $userslogin as $key => $value ) {
	                            if ( $value->data->user_email != '' ) { ?>
	                                <option value="<?php echo $value->data->user_email; ?>" <?php selected( $value->data->user_email, $this->options['email_address'] ); ?>><?php echo $value->data->user_login; ?></option>
	                            <?php }
	                        } ?>
	                    </select>
	                    <input id="cbxsec_email_address" type="email" class="cbxsec_email_notifications_input" name="cbxsec_email_address" maxlength="100" value="<?php if ( $this->options['mailto'] == 'custom' ) echo $this->options['email_address']; ?>" />
					</td>
				</tr>
				<tr class="cbxsec_email_notifications">
					<th><?php _e( 'Block Notifications', 'cybex-security' ); ?></th>
					<td>
						<p><?php _e( 'Subject', 'cybex-security' ); ?></p>
						<textarea rows="5" name="cbxsec_email_subject"><?php echo $this->options['email_subject']; ?></textarea>
						<div class="cbx_info">
							<?php _e( 'Allowed Variables:', 'cybex-security' ); ?><br/>
							'%IP%' - <?php _e( 'blocked IP address', 'cybex-security' ); ?><br/>
							'%SITE_NAME%' - <?php _e( 'website name', 'cybex-security' ); ?>
						</div>
						<p style="margin-top: 6px;"><?php _e( 'Message', 'cybex-security' ); ?></p>
						<textarea rows="5" name="cbxsec_email_blocked"><?php echo $this->options['email_blocked']; ?></textarea>
						<div class="cbx_info">
							<?php _e( 'Allowed Variables:', 'cybex-security' ); ?><br/>
							'%IP%' - <?php _e( 'blocked IP address', 'cybex-security' ); ?><br/>
							'%PLUGIN_LINK%' - <?php _e( 'Wp Cybex Security plugin link', 'cybex-security' ); ?><br/>
							'%WHEN%' - <?php _e( 'date and time when IP address was blocked', 'cybex-security' ); ?><br/>
							'%SITE_NAME%' - <?php _e( 'website name', 'cybex-security' ); ?><br/>
							'%SITE_URL%' - <?php _e( 'website URL', 'cybex-security' ); ?>
						</div>
					</td>
				</tr>
				<tr class="cbxsec_email_notifications">
					<th><?php _e( 'Deny List Notifications', 'cybex-security' ); ?></th>
					<td>
						<p><?php _e( 'Subject', 'cybex-security' ); ?></p>
						<textarea rows="5" name="cbxsec_email_subject_denylisted"><?php echo $this->options['email_subject_denylisted']; ?></textarea>
						<div class="cbx_info">
							<?php _e( 'Allowed Variables:', 'cybex-security' ); ?><br/>
							'%IP%' - <?php _e( 'deny listed IP address', 'cybex-security' ); ?><br/>
							'%SITE_NAME%' - <?php _e( 'website name', 'cybex-security' ); ?>
						</div>
						<p style="margin-top: 6px;"><?php _e( 'Message', 'cybex-security' ); ?></p>
						<textarea rows="5" name="cbxsec_email_denylisted"><?php echo $this->options['email_denylisted']; ?></textarea>
						<div class="cbx_info">
							<?php _e( 'Allowed Variables:', 'cybex-security' ); ?><br/>
							'%IP%' - <?php _e( 'deny listed IP address', 'cybex-security' ); ?><br/>
							'%PLUGIN_LINK%' - <?php _e( 'Wp Cybex Security plugin link', 'cybex-security' ); ?><br/>
							'%WHEN%' - <?php _e( 'date and time when IP address was blocked', 'cybex-security' ); ?><br/>
							'%SITE_NAME%' - <?php _e( 'website name', 'cybex-security' ); ?><br/>
							'%SITE_URL%' - <?php _e( 'website URL', 'cybex-security' ); ?>
						</div>
					</td>
				</tr>
				<tr class="cbxsec_email_notifications">
					<th scope="row"><?php _e( 'Restore Default Email Notifications', 'cybex-security' ); ?></th>
					<td>
						<button class="button-secondary" name="cbxsec_return_default" value="email"><?php _e( 'Restore Email Notifications', 'cybex-security' ) ?></button>
					</td>
				</tr>
			</table>
		<?php }

		/**
		 * Display custom options on the 'misc' tab
		 * @access public
		 */
		public function additional_misc_options_affected() {
			global $wpdb, $cbxsec_country_table;
			/* get DB size or update if it's empty 1 hour old or more */
			if ( empty( $this->options['db_size'] ) || ( $this->options['db_size']['last_updated_timestamp'] + 3600 ) < time() ) {
				/* get the size of 'log' and 'statistics' tables in DB */
				$tables = $wpdb->get_results(
					"SHOW TABLE STATUS WHERE `Name` in ( '{$wpdb->prefix}cbxsec_failed_attempts_statistics', '{$wpdb->prefix}cbxsec_failed_forms_by_ip' )",
					ARRAY_A );
				if ( $tables && 3 == count( $tables ) ) {
					foreach ( $tables as $value ) {
						$tables[ $value['Name'] ] = $value['Data_length'];
					}
					$db_size = array(
						'stats_size' 				=> (string) round( ( $tables[ $wpdb->prefix . 'cbxsec_failed_attempts_statistics' ] + $tables[ $wpdb->prefix . 'cbxsec_failed_forms_by_ip' ] ) / 1000000, 3 ),
						'last_updated_timestamp' 	=> time()
					);
				} else {
					$db_size = '';
				}
				unset( $tables );
				/* update options with new data */
				$this->options['db_size'] = $db_size;
				update_option( 'cbxsec_options', $this->options );
			} else {
				$db_size = $this->options['db_size'];
			} ?>
			</table>
			<table class="form-table cbxsec_settings_form">
			<tr>
				<th><?php _e( 'Remove Stats Entries Older Than', 'cybex-security' ) ?></th>
				<td>
					<fieldset>
						<label><input type="number" min="0" max="999" step="1" maxlength="3" value="<?php echo $this->options['days_to_clear_statistics']; ?>" name="cbxsec_days_to_clear_statistics" /> <?php _e( 'days', 'cybex-security' ) ?></label>
						<br/>
						<span class="cbx_info"><?php _e( 'Set "0" if you do not want to clear the statistics.', 'cybex-security' ) ?></span>
						<?php if ( ! empty( $db_size ) && isset( $db_size['stats_size'] ) && is_numeric( $db_size['stats_size'] ) ) { ?>
							<p class="cbx_info_small"><?php printf( __( 'Current size of DB table is %s', 'cybex-security' ), '&asymp; ' . $db_size['stats_size'] . __( 'Mb', 'cybex-security' ) ); ?></p>
						<?php } ?>
					</fieldset>
				</td>
			</tr>
			</table>
			<table class="form-table cbxsec_settings_form">
		<?php }
	}
}