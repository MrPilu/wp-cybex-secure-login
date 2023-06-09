<?php
/*
* Function for displaying CybexSecurity menu
* Version: 1.0.0
*/

if ( ! function_exists ( 'cbx_admin_enqueue_scripts' ) )
	require_once( dirname( __FILE__ ) . '/cbx_functions.php' );

if ( ! function_exists( 'cbx_add_menu_render' ) ) {
	function cbx_add_menu_render() {
		global $wpdb, $wp_version, $bstwbsftwppdtplgns_options;
		$error = $message = '';


		$is_main_page = in_array( $_GET['page'], array( 'cbx_panel', 'cbx_themes', 'cbx_system_status' ) );
		$page = sanitize_text_field(wp_unslash( $_GET['page']) );
		$tab = isset( $_GET['tab'] ) ? sanitize_text_field(wp_unslash( $_GET['tab'] )) : '';

		if ( $is_main_page )
			$current_page = 'admin.php?page=' . $page;
		else
			$current_page = isset( $_GET['tab'] ) ? 'admin.php?page=' . $page . '&tab=' . $tab : 'admin.php?page=' . $page;

		if ( 'cbx_panel' == $page || ( ! $is_main_page && '' == $tab ) ) {

			if ( ! function_exists( 'is_plugin_active_for_network' ) )
				require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

			/* get $cbx_plugins */

			$all_plugins = get_plugins();
			$active_plugins = get_option( 'active_plugins' );
			$sitewide_active_plugins = ( function_exists( 'is_multisite' ) && is_multisite() ) ? get_site_option( 'active_sitewide_plugins' ) : array();
			$update_available_all = get_site_transient( 'update_plugins' );
			
			if ( isset( $_GET['category'] ) ) {
				$plugin_category = sanitize_text_field( $_GET['category'] );
			
				// Validate the $plugin_category value
				if ( ! in_array( $plugin_category, array( 'all', 'category1', 'category2' ) ) ) {
					// Handle invalid input, such as setting a default category or showing an error message.
					$plugin_category = 'all';
				}
			} else {
				$plugin_category = 'all';
			}
			
			if ( ( isset( $_GET['sub'] ) && 'installed' == $_GET['sub'] ) || ! isset( $_GET['sub'] ) ) {
				$cbx_plugins_update_available = $cbx_plugins_expired = array();
			
				foreach ( $cbx_plugins as $key_plugin => $value_plugin ) {
					foreach ( $value_plugin['category'] as $category_key ) {
						$cbx_plugins_category[ $category_key ]['count'] = isset( $cbx_plugins_category[ $category_key ]['count'] ) ? $cbx_plugins_category[ $category_key ]['count'] + 1 : 1;
					}
			
					$is_installed = array_key_exists( $key_plugin, $all_plugins );
					$is_pro_installed = false;
			
					if ( isset( $value_plugin['pro_version'] ) ) {
						$is_pro_installed = array_key_exists( $value_plugin['pro_version'], $all_plugins );
					}
			
					/* Check for update availability */
					if ( ! empty( $update_available_all ) && ! empty( $update_available_all->response ) ) {
						if ( $is_pro_installed && array_key_exists( $value_plugin['pro_version'], $update_available_all->response ) ) {
							unset( $cbx_plugins[ $key_plugin ] );
							$value_plugin['update_available'] = $value_plugin['pro_version'];
							$cbx_plugins_update_available[ $key_plugin ] = $value_plugin;
						} else if ( $is_installed && array_key_exists( $key_plugin, $update_available_all->response ) ) {
							unset( $cbx_plugins[ $key_plugin ] );
							$value_plugin['update_available'] = $key_plugin;
							$cbx_plugins_update_available[ $key_plugin ] = $value_plugin;
						}
					}
			
					/* Check for expiration */
					if ( $is_pro_installed && isset( $bstwbsftwppdtplgns_options['time_out'][ $value_plugin['pro_version'] ] ) &&
						strtotime( $bstwbsftwppdtplgns_options['time_out'][ $value_plugin['pro_version'] ] ) < strtotime( date( "m/d/Y" ) ) ) {
						unset( $cbx_plugins[ $key_plugin ] );
						$value_plugin['expired'] = $bstwbsftwppdtplgns_options['time_out'][ $value_plugin['pro_version'] ];
						$cbx_plugins_expired[ $key_plugin ] = $value_plugin;
					}
				}
			
				$cbx_plugins = $cbx_plugins_update_available + $cbx_plugins_expired + $cbx_plugins;
			} else {
				foreach ( $cbx_plugins as $key_plugin => $value_plugin ) {
					foreach ( $value_plugin['category'] as $category_key ) {
						$cbx_plugins_category[ $category_key ]['count'] = isset( $cbx_plugins_category[ $category_key ]['count'] ) ? $cbx_plugins_category[ $category_key ]['count'] + 1 : 1;
					}
				}
			}

			/*** membership ***/
			$cbx_license_plugin = 'cbx_get_list_for_membership';
			$cbx_license_key = isset( $bstwbsftwppdtplgns_options[ $cbx_license_plugin ] ) ? $bstwbsftwppdtplgns_options[ $cbx_license_plugin ] : '';
			$update_membership_list = true;

			if ( isset( $_POST['cbx_license_key'] ) )
				$cbx_license_key = sanitize_text_field( $_POST['cbx_license_key'] );

			if ( isset( $_SESSION['cbx_membership_time_check'] ) && isset( $_SESSION['cbx_membership_list'] ) && $_SESSION['cbx_membership_time_check'] < strtotime( '+12 hours' ) ) {
				$update_membership_list = false;
				$plugins_array = isset( $_SESSION['cbx_membership_list'] ) ? (array) $_SESSION['cbx_membership_list'] : array();
			}
		} elseif ( 'cbx_system_status' == $page || 'system-status' == $tab ) {

			$all_plugins = get_plugins();
			$active_plugins = get_option( 'active_plugins' );
			$mysql_info = $wpdb->get_results( "SHOW VARIABLES LIKE 'sql_mode'" );
			if ( is_array( $mysql_info ) )
				$sql_mode = $mysql_info[0]->Value;
			if ( empty( $sql_mode ) )
				$sql_mode = __( 'Not set', 'xcellorate' );

			$allow_url_fopen = ( ini_get( 'allow_url_fopen' ) ) ? __( 'On', 'xcellorate' ) : __( 'Off', 'xcellorate' );
			$upload_max_filesize = ( ini_get( 'upload_max_filesize' ) )? ini_get( 'upload_max_filesize' ) : __( 'N/A', 'xcellorate' );
			$post_max_size = ( ini_get( 'post_max_size' ) ) ? ini_get( 'post_max_size' ) : __( 'N/A', 'xcellorate' );
			$max_execution_time = ( ini_get( 'max_execution_time' ) ) ? ini_get( 'max_execution_time' ) : __( 'N/A', 'xcellorate' );
			$memory_limit = ( ini_get( 'memory_limit' ) ) ? ini_get( 'memory_limit' ) : __( 'N/A', 'xcellorate' );
			$wp_memory_limit = ( defined( 'WP_MEMORY_LIMIT' ) ) ? WP_MEMORY_LIMIT : __( 'N/A', 'xcellorate' );
			$memory_usage = ( function_exists( 'memory_get_usage' ) ) ? round( memory_get_usage() / 1024 / 1024, 2 ) . ' ' . __( 'Mb', 'xcellorate' ) : __( 'N/A', 'xcellorate' );
			$exif_read_data = ( is_callable( 'exif_read_data' ) ) ? __( 'Yes', 'xcellorate' ) . " ( V" . substr( phpversion( 'exif' ), 0,4 ) . ")" : __( 'No', 'xcellorate' );
			$iptcparse = ( is_callable( 'iptcparse' ) ) ? __( 'Yes', 'xcellorate' ) : __( 'No', 'xcellorate' );
			$xml_parser_create = ( is_callable( 'xml_parser_create' ) ) ? __( 'Yes', 'xcellorate' ) : __( 'No', 'xcellorate' );
			$theme = ( function_exists( 'wp_get_theme' ) ) ? wp_get_theme() : get_theme( get_current_theme() );

			if ( function_exists( 'is_multisite' ) ) {
				$multisite = is_multisite() ? __( 'Yes', 'xcellorate' ) : __( 'No', 'xcellorate' );
			} else {
				$multisite = __( 'N/A', 'xcellorate' );
			}

			$system_info = array(
				'wp_environment' => array(
					'name' => __( 'WordPress Environment', 'xcellorate' ),
					'data' => array(
						__( 'Home URL', 'xcellorate' )						=> home_url(),
						__( 'Website URL', 'xcellorate' )					=> get_option( 'siteurl' ),
						__( 'WP Version', 'xcellorate' )					=> $wp_version,
						__( 'WP Multisite', 'xcellorate' )					=> $multisite,
						__( 'WP Memory Limit', 'xcellorate' )				=> $wp_memory_limit,
						__( 'Active Theme', 'xcellorate' )					=> $theme['Name'] . ' ' . $theme['Version'] . ' (' . sprintf( __( 'by %s', 'xcellorate' ), $theme['Author'] ) . ')'
					),
				),
				'server_environment' => array(
					'name' => __( 'Server Environment', 'xcellorate' ),
					'data' => array(
						__( 'Operating System', 'xcellorate' )				=> PHP_OS,
						__( 'Server', 'xcellorate' )						=> sanitize_text_field($_SERVER["SERVER_SOFTWARE"]),
						__( 'PHP Version', 'xcellorate' )					=> PHP_VERSION,
						__( 'PHP Allow URL fopen', 'xcellorate' )			=> $allow_url_fopen,
						__( 'PHP Memory Limit', 'xcellorate' )				=> $memory_limit,
						__( 'Memory Usage', 'xcellorate' )					=> $memory_usage,
						__( 'PHP Max Upload Size', 'xcellorate' )			=> $upload_max_filesize,
						__( 'PHP Max Post Size', 'xcellorate' )			=> $post_max_size,
						__( 'PHP Max Script Execute Time', 'xcellorate' )	=> $max_execution_time,
						__( 'PHP Exif support', 'xcellorate' )				=> $exif_read_data,
						__( 'PHP IPTC support', 'xcellorate' )				=> $iptcparse,
						__( 'PHP XML support', 'xcellorate' )				=> $xml_parser_create,
						'$_SERVER[HTTP_HOST]'								=> sanitize_text_field($_SERVER['HTTP_HOST']),
						'$_SERVER[SERVER_NAME]'								=> sanitize_text_field($_SERVER['SERVER_NAME']),
					),
				),
				'db'	=> array(
					'name' => __( 'Database', 'xcellorate' ),
					'data' => array(
						__( 'WP DB version', 'xcellorate' )	=> get_option( 'db_version' ),
						__( 'MySQL version', 'xcellorate' )	=> $wpdb->get_var( "SELECT VERSION() AS version" ),
						__( 'SQL Mode', 'xcellorate' )			=> $sql_mode,
					),
				),
				'active_plugins'	=> array(
					'name' 	=> __( 'Active Plugins', 'xcellorate' ),
					'data' 	=> array(),
					'count'	=> 0
				),
				'inactive_plugins'	=> array(
					'name' 	=> __( 'Inactive Plugins', 'xcellorate' ),
					'data' 	=> array(),
					'count'	=> 0
				)
			);

			foreach ( $all_plugins as $path => $plugin ) {
				$name = str_replace( 'by CybexSecurity', '', $plugin['Name'] );
				if ( is_plugin_active( $path ) ) {
					$system_info['active_plugins']['data'][ $name ] = sprintf( __( 'by %s', 'xcellorate' ), $plugin['Author'] ) . ' - ' . $plugin['Version'];
					$system_info['active_plugins']['count'] = $system_info['active_plugins']['count'] + 1;
				} else {
					$system_info['inactive_plugins']['data'][ $name ] = sprintf( __( 'by %s', 'xcellorate' ), $plugin['Author'] ) . ' - ' . $plugin['Version'];
					$system_info['inactive_plugins']['count'] = $system_info['inactive_plugins']['count'] + 1;
				}
			}

			if ( ( isset( $_REQUEST['cbxmn_form_submit'] ) && check_admin_referer( plugin_basename(__FILE__), 'cbxmn_nonce_submit' ) ) || ( isset( $_REQUEST['cbxmn_form_submit_custom_email'] ) && check_admin_referer( plugin_basename(__FILE__), 'cbxmn_nonce_submit_custom_email' ) ) ) {
				if ( isset( $_REQUEST['cbxmn_form_email'] ) ) {
					$email = sanitize_email( $_REQUEST['cbxmn_form_email'] );
					if ( '' == $email ) {
						$error = __( 'Please enter a valid email address.', 'xcellorate' );
					} else {
						$message = sprintf( __( 'Email with system info is sent to %s.', 'xcellorate' ), $email );
					}
				} else {
					$email = 'plugin_system_status@cybexsecurity.co.uk';
					$message = __( 'Thank you for contacting us.', 'xcellorate' );
				}

				if ( $error == '' ) {
					$headers  = 'MIME-Version: 1.0' . "\n";
					$headers .= 'Content-type: text/html; charset=utf-8' . "\n";
					$headers .= 'From: ' . get_option( 'admin_email' );
					$message_text = '<html><head><title>System Info From ' . home_url() . '</title></head><body>';
					foreach ( $system_info as $info ) {
						if ( ! empty( $info['data'] ) ) {
							$message_text .= '<h4>' . $info['name'];
							if ( isset( $info['count'] ) )
								$message_text .= ' (' . $info['count'] . ')';
							$message_text .= '</h4><table>';
							foreach ( $info['data'] as $key => $value ) {
								$message_text .= '<tr><td>' . $key . '</td><td>' . $value . '</td></tr>';
							}
							$message_text .= '</table>';
						}
					}
					$message_text .= '</body></html>';
					$result = wp_mail( $email, 'System Info From ' . home_url(), $message_text, $headers );
					if ( $result != true )
						$error = __( "Sorry, email message could not be delivered.", 'xcellorate' );
				}
			}
		} ?>
        <div class="cbx-wrap">
            <div class="cbx-header">
                <div class="cbx-title">
                    <a href="<?php echo ( $is_main_page ) ? self_admin_url( 'admin.php?page=cbx_panel' ) : esc_url( self_admin_url( 'admin.php?page=' . $page ) ); ?>">
                        <span class="cbx-logo cbxicons cbxicons-cbx-logo"></span>
                        CybexSecurity
                        <span>panel</span>
                    </a>
                </div>
                <div class="cbx-menu-item-icon">&#8226;&#8226;&#8226;</div>
				<div class="cbx-nav-tab-wrapper">
					<?php if ($is_main_page) { ?>
						<a class="cbx-nav-tab<?php if ('cbx_panel' == $page) echo ' cbx-nav-tab-active'; ?>" href="<?php echo esc_url(self_admin_url('admin.php?page=cbx_panel')); ?>"><?php esc_html_e('Plugins', 'xcellorate'); ?></a>
						<a class="cbx-nav-tab<?php if ('cbx_themes' == $page) echo ' cbx-nav-tab-active'; ?>" href="<?php echo esc_url(self_admin_url('admin.php?page=cbx_themes')); ?>"><?php esc_html_e('Themes', 'xcellorate'); ?></a>
						<a class="cbx-nav-tab<?php if ('cbx_system_status' == $page) echo ' cbx-nav-tab-active'; ?>" href="<?php echo esc_url(self_admin_url('admin.php?page=cbx_system_status')); ?>"><?php esc_html_e('System status', 'xcellorate'); ?></a>
					<?php } else { ?>
						<a class="cbx-nav-tab<?php if (!isset($_GET['tab'])) echo ' cbx-nav-tab-active'; ?>" href="<?php echo esc_url(self_admin_url('admin.php?page=' . esc_attr($page))); ?>"><?php esc_html_e('Plugins', 'xcellorate'); ?></a>
						<a class="cbx-nav-tab<?php if ('themes' == $tab) echo ' cbx-nav-tab-active'; ?>" href="<?php echo esc_url(self_admin_url('admin.php?page=' . esc_attr($page) . '&tab=themes')); ?>"><?php esc_html_e('Themes', 'xcellorate'); ?></a>
						<a class="cbx-nav-tab<?php if ('system-status' == $tab) echo ' cbx-nav-tab-active'; ?>" href="<?php echo esc_url(self_admin_url('admin.php?page=' . esc_attr($page) . '&tab=system-status')); ?>"><?php esc_html_e('System status', 'xcellorate'); ?></a>
					<?php } ?>
				</div>
                <div class="cbx-help-links-wrapper">
                    <a href="https://support.cybexsecurity.co.uk" target="_blank"><?php _e( 'Support', 'xcellorate' ); ?></a>
                    <a href="https://cybexsecurity.co.uk/client-area" target="_blank" title="<?php _e( 'Manage purchased licenses & subscriptions', 'xcellorate' ); ?>">Client Area</a>
                </div>
                <div class="clear"></div>
            </div>
			<?php if (('cbx_panel' == $page || (!isset($_GET['tab']) && !$is_main_page)) && !isset($_POST['cbx_plugin_action_submit'])) { ?>
				<div class="cbx-membership-wrap">
					<div class="cbx-membership-backround"></div>
					<div class="cbx-membership">
						<div class="cbx-membership-title"><?php printf(__('Get Access to %s+ Premium Plugins', 'xcellorate'), '30'); ?></div>
						<form class="cbx-membership-form" method="post" action="">
							<span class="cbx-membership-link"><a target="_blank" href="https://cybexsecurity.co.uk/membership/"><?php _e('Subscribe to Pro Membership', 'xcellorate'); ?></a> <?php _e('or', 'xcellorate'); ?></span>
							<?php if (isset($bstwbsftwppdtplgns_options['go_pro'][$cbx_license_plugin]['count']) &&
								'5' < $bstwbsftwppdtplgns_options['go_pro'][$cbx_license_plugin]['count'] &&
								$bstwbsftwppdtplgns_options['go_pro'][$cbx_license_plugin]['time'] > (time() - (24 * 60 * 60))) { ?>
								<div class="cbx_form_input_wrap">
									<input disabled="disabled" type="text" name="cbx_license_key" value="<?php echo esc_attr($cbx_license_key); ?>" />
									<div class="cbx_error"><?php _e("Unfortunately, you have exceeded the number of available tries per day.", 'xcellorate'); ?></div>
								</div>
								<input disabled="disabled" type="submit" class="cbx-button" value="<?php _e('Check license key', 'xcellorate'); ?>" />
							<?php } else { ?>
								<div class="cbx_form_input_wrap">
									<input <?php if ("" != $error) echo 'class="cbx_input_error"'; ?> type="text" placeholder="<?php _e('Enter your license key', 'xcellorate'); ?>" maxlength="100" name="cbx_license_key" value="<?php echo esc_attr($cbx_license_key); ?>" />
									<div class="cbx_error" <?php if ("" == $error) echo 'style="display:none"'; ?>><?php echo $error; ?></div>
								</div>
								<input type="hidden" name="cbx_license_plugin" value="<?php echo esc_attr($cbx_license_plugin); ?>" />
								<input type="hidden" name="cbx_license_submit" value="submit" />
								<?php if (empty($plugins_array)) { ?>
									<input type="submit" class="cbx-button" value="<?php _e('Activate', 'xcellorate'); ?>" />
								<?php } else { ?>
									<input type="submit" class="cbx-button" value="<?php _e('Check license key', 'xcellorate'); ?>" />
								<?php } ?>
								<?php wp_nonce_field(plugin_basename(__FILE__), 'cbx_license_nonce_name'); ?>
							<?php } ?>
						</form>
						<div class="clear"></div>
					</div>
				</div>
			<?php } ?>

            <div class="cbx-wrap-content wrap">
				<?php if ( 'cbx_panel' == $page || ( ! isset( $_GET['tab'] ) && ! $is_main_page ) ) { ?>
                    <div class="updated notice is-dismissible inline" <?php if ( '' == $message || '' != $error ) echo 'style="display:none"'; ?>><p><?php echo $message; ?></p></div>
                    <h1>
						<?php _e( 'Plugins', 'xcellorate' ); ?>
                        <a href="<?php echo self_admin_url( 'plugin-install.php?tab=upload' ); ?>" class="upload page-title-action add-new-h2"><?php _e( 'Upload Plugin', 'xcellorate' ); ?></a>
                    </h1>
					<?php if ( isset( $_GET['error'] ) ) {
						if ( isset( $_GET['charsout'] ) )
							$errmsg = sprintf( __( 'The plugin generated %d characters of <strong>unexpected output</strong> during activation. If you notice &#8220;headers already sent&#8221; messages, problems with syndication feeds or other issues, try deactivating or removing this plugin.' ), $_GET['charsout'] );
						else
							$errmsg = __( 'Plugin could not be activated because it triggered a <strong>fatal error</strong>.' ); ?>
                        <div id="message" class="error is-dismissible"><p><?php echo $errmsg; ?></p></div>
					<?php } elseif ( isset( $_GET['activate'] ) ) { ?>
                        <div id="message" class="updated notice is-dismissible"><p><?php _e( 'Plugin <strong>activated</strong>.' ) ?></p></div>
					<?php }

					if ( isset( $_POST['cbx_plugin_action_submit'] ) && isset( $_POST['cbx_install_plugin'] ) && check_admin_referer( plugin_basename(__FILE__), 'cbx_license_install_nonce_name' ) ) {

						$cbx_license_plugin = sanitize_text_field( $_POST['cbx_install_plugin'] );

						$bstwbsftwppdtplgns_options[ $cbx_license_plugin ] = $cbx_license_key;
						if ( is_multisite() )
							update_site_option( 'bstwbsftwppdtplgns_options', $bstwbsftwppdtplgns_options );
						else
							update_option( 'bstwbsftwppdtplgns_options', $bstwbsftwppdtplgns_options );

						$url = $plugins_array[ $cbx_license_plugin ]['link'] . '&download_from=5'; ?>
						<h2><?php _e( 'Download Pro Plugin', 'xcellorate' ); ?></h2>		
						<p>
							<strong><?php _e( 'Your Pro plugin is ready', 'xcellorate' ); ?></strong>
							<br>
							<?php _e( 'Your plugin has been zipped, and now is ready to download.', 'xcellorate' ); ?>
						</p>
						<p>
							<a class="button button-secondary" target="_parent" href="<?php echo esc_url( $url ); ?>"><?php _e( 'Download Now', 'xcellorate' ); ?></a>
						</p>
						<br>
						<p>
							<strong><?php _e( 'Need help installing the plugin?', 'xcellorate' ); ?></strong>
							<br>
							<a target="_blank" href="https://docs.google.com/document/d/1-hvn6WRvWnOqj5v5pLUk7Awyu87lq5B_dO-Tv-MC9JQ/"><?php _e( 'How to install WordPress plugin from your admin Dashboard (ZIP archive)', 'xcellorate' ); ?></a>
						</p>						
						<p>
							<strong><?php _e( 'Get Started', 'xcellorate' ); ?></strong>
							<br>
							<a target="_blank" href="https://drive.google.com/drive/u/0/folders/0B5l8lO-CaKt9VGh0a09vUjNFNjA"><?php _e( 'Documentation', 'xcellorate' ); ?></a>
							<br>
							<a target="_blank" href="https://www.youtube.com/user/xcellorate"><?php _e( 'Video Instructions', 'xcellorate' ); ?></a>
							<br>
							<a target="_blank" href="https://support.cybexsecurity.co.uk"><?php _e( 'Knowledge Base', 'xcellorate' ); ?></a>
						</p>
						<p>
							<strong><?php _e( 'Licenses & Domains', 'xcellorate' ); ?></strong>
							<br>
							<?php printf( 'Manage your license(-s) and change domain names using the %s at CybexSecurity.',
							'<a target="_blank" href="https://cybexsecurity.co.uk/client-area">' . __( 'Client Area', 'xcellorate' ) . '</a>' ); ?>
						</p>
						<p><a href="<?php echo esc_url( self_admin_url( $current_page ) ); ?>" target="_parent"><?php _e( 'Return to CybexSecurity Panel', 'xcellorate' ); ?></a></p>
					<?php } else {
						$category_href = $current_page;
						if ( 'all' != $plugin_category )
							$category_href .= '&category=' . $plugin_category; ?>
                        <ul class="subsubsub">
                            <li>
                                <a <?php if ( ! isset( $_GET['sub'] ) ) echo 'class="current" '; ?>href="<?php echo esc_url( self_admin_url( $category_href ) ); ?>"><?php _e( 'All', 'xcellorate' ); ?></a>
                            </li> |
                            <li>
                                <a <?php if ( isset( $_GET['sub'] ) && 'installed' == $_GET['sub'] ) echo 'class="current" '; ?>href="<?php echo esc_url( self_admin_url( $category_href . '&sub=installed' ) ); ?>"><?php _e( 'Installed', 'xcellorate' ); ?></a>
                            </li> |
                            <li>
                                <a <?php if ( isset( $_GET['sub'] ) && 'not_installed' == $_GET['sub'] ) echo 'class="current" '; ?>href="<?php echo esc_url( self_admin_url( $category_href . '&sub=not_installed' ) ); ?>"><?php _e( 'Not Installed', 'xcellorate' ); ?></a>
                            </li>
                        </ul>
                        <div class="clear"></div>
                        <div class="cbx-filter-top">
                            <h2>
                                <span class="cbx-toggle-indicator"></span>
								<?php _e( 'Filter results', 'xcellorate' ); ?>
                            </h2>
                            <div class="cbx-filter-top-inside">
                                <div class="cbx-filter-title"><?php _e( 'Category', 'xcellorate' ); ?></div>
                                <ul class="cbx-category">
                                    <li>
										<?php $sub_in_url = ( isset( $_GET['sub'] ) && in_array( $_GET['sub'], array( 'installed', 'not_installed' ) ) ) ? '&sub=' . $_GET['sub'] : ''; ?>
                                        <a <?php if ( 'all' == $plugin_category ) echo ' class="cbx-active"'; ?> href="<?php echo esc_url(self_admin_url( $current_page . $sub_in_url ) ); ?>"><?php _e( 'All', 'xcellorate' ); ?>
                                            <span>(<?php echo count( $cbx_plugins ); ?>)</span>
                                        </a>
                                    </li>
									<?php foreach ( $cbx_plugins_category as $category_key => $category_value ) { ?>
                                        <li>
                                            <a <?php if ( $category_key == $plugin_category ) echo ' class="cbx-active"'; ?> href="<?php echo esc_url( self_admin_url( $current_page . $sub_in_url . '&category=' . $category_key ) ); ?>"><?php echo $category_value['name']; ?>
                                                <span>(<?php echo $category_value['count']; ?>)</span>
                                            </a>
                                        </li>
									<?php } ?>
                                </ul>
                            </div>
                        </div>
                        <div class="cbx-products">
							<?php $nothing_found = true;
							foreach ( $cbx_plugins as $key_plugin => $value_plugin ) {

								if ( 'all' != $plugin_category && isset( $cbx_plugins_category[ $plugin_category ] ) && ! in_array( $plugin_category, $value_plugin['category'] ) )
									continue;

								$key_plugin_explode = explode( '/', $key_plugin );

								$icon = isset( $value_plugin['icon'] ) ? $value_plugin['icon'] : '//ps.w.org/' . $key_plugin_explode[0] . '/assets/icon-256x256.png';
								$is_pro_isset = isset( $value_plugin['pro_version'] );
								$is_installed = array_key_exists( $key_plugin, $all_plugins );
								$is_active = in_array( $key_plugin, $active_plugins ) || isset( $sitewide_active_plugins[ $key_plugin ] );

								$is_pro_installed = $is_pro_active = false;
								if ( $is_pro_isset ) {
									$is_pro_installed = array_key_exists( $value_plugin['pro_version'], $all_plugins );
									$is_pro_active = in_array( $value_plugin['pro_version'], $active_plugins ) || isset( $sitewide_active_plugins[ $value_plugin['pro_version'] ] );
								}

								if ( ( isset( $_GET['sub'] ) && 'installed' == $_GET['sub'] && ! $is_pro_installed && ! $is_installed ) ||
								     ( isset( $_GET['sub'] ) && 'not_installed' == $_GET['sub'] && ( $is_pro_installed || $is_installed ) ) )
									continue;

								$link_attr = isset( $value_plugin['install_url'] ) ? 'href="' . esc_url( $value_plugin['install_url'] ) . '" target="_blank"' : 'href="' . esc_url( self_admin_url( 'plugin-install.php?tab=plugin-information&plugin=' . $key_plugin_explode[0] . '&from=import&TB_iframe=true&width=600&height=550' ) ) . '" class="thickbox open-plugin-details-modal"';

								$nothing_found = false; ?>
                                <div class="cbx_product_box<?php if ( $is_active || $is_pro_active ) echo ' cbx_product_active'; ?>">
                                    <div class="cbx_product_image">
                                        <a <?php echo $link_attr; ?>><img src="<?php echo $icon; ?>"/></a>
                                    </div>
                                    <div class="cbx_product_content">
                                        <div class="cbx_product_title"><a <?php echo $link_attr; ?>><?php echo $value_plugin['name']; ?></a></div>
                                        <div class="cbx-version">
											<?php
											if ( $is_pro_installed ) {
												echo '<span';
												if ( ! empty( $value_plugin['expired'] ) || ! empty( $value_plugin['update_availible'] ) )
													echo ' class="cbx-update-available"';
												echo '>v ' . $all_plugins[ $value_plugin['pro_version'] ]['Version'] . '</span>';
											} elseif ( $is_installed ) {
												echo '<span';
												if ( ! empty( $value_plugin['expired'] ) || ! empty( $value_plugin['update_availible'] ) )
													echo ' class="cbx-update-available"';
												echo '>v ' . $all_plugins[ $key_plugin ]['Version'] . '</span>';
											} else {
												echo '<span>' . __( 'Not installed', 'xcellorate' ) . '</span>';
											}

											if ( ! empty( $value_plugin['expired'] ) ) {
												echo ' - <a class="cbx-update-now" href="https://support.cybexsecurity.co.uk/hc/en-us/articles/202356359" target="_blank">' . __( 'Renew to get updates', 'xcellorate' ) . '</a>';
											} elseif ( ! empty( $value_plugin['update_availible'] ) ) {
												$r = $update_availible_all->response[ $value_plugin['update_availible'] ];
												echo ' - <a class="cbx-update-now" href="' . esc_url( wp_nonce_url( self_admin_url( 'update.php?action=upgrade-plugin&plugin=' . $value_plugin['update_availible'] ), 'upgrade-plugin_' . $value_plugin['update_availible'] ) ) . '" class="update-link" aria-label="' . sprintf( __( 'Update to v %s', 'xcellorate' ), $r->new_version ) . '">' . sprintf( __( 'Update to v %s', 'xcellorate' ), $r->new_version ) . '</a>';
											} ?>
                                        </div>
                                        <div class="cbx_product_description">
											<?php echo ( strlen( $value_plugin['description'] ) > 100 ) ? mb_substr( $value_plugin['description'], 0, 100 ) . '...' : $value_plugin['description']; ?>
                                        </div>
                                        <div class="cbx_product_links">
											<?php if ( $is_active || $is_pro_active ) {
												if ( $is_pro_isset ) {
													if ( ! $is_pro_installed ) {
														if ( ! empty( $plugins_array ) && array_key_exists( $value_plugin['pro_version'], $plugins_array ) ) { ?>
                                                            <form method="post" action="">
                                                                <input type="submit" class="button button-secondary" value="<?php _e( 'Get Pro', 'xcellorate' ); ?>" />
                                                                <input type="hidden" name="cbx_plugin_action_submit" value="submit" />
                                                                <input type="hidden" name="cbx_install_plugin" value="<?php echo $value_plugin['pro_version']; ?>" />
																<?php wp_nonce_field( plugin_basename(__FILE__), 'cbx_license_install_nonce_name' ); ?>
                                                            </form>
														<?php } else { ?>
                                                            <a class="button button-secondary cbx_upgrade_button" href="<?php echo esc_url( $cbx_plugins[ $key_plugin ]['purchase'] ); ?>" target="_blank"><?php _e( 'Upgrade to Pro', 'xcellorate' ); ?></a>
														<?php }
													}
												} else { ?>
                                                    <a class="cbx_donate" href="https://cybexsecurity.co.uk/donate/" target="_blank"><?php _e( 'Donate', 'xcellorate' ); ?></a> <span>|</span>
												<?php }

												if ( $is_pro_active ) { ?>
                                                    <a class="cbx_settings" href="<?php echo esc_url( self_admin_url( $cbx_plugins[ $key_plugin ]["pro_settings"] ) ); ?>"><?php _e( 'Settings', 'xcellorate' ); ?></a>
												<?php } else { ?>
                                                    <a class="cbx_settings" href="<?php echo esc_url( self_admin_url( $cbx_plugins[ $key_plugin ]["settings"] ) ); ?>"><?php _e( 'Settings', 'xcellorate' ); ?></a>
												<?php }
											} else {
												if ( $is_pro_installed ) { ?>
                                                    <a class="button button-secondary" href="<?php echo esc_url( wp_nonce_url( self_admin_url( $current_page . '&cbx_activate_plugin=' . $value_plugin['pro_version'] ), 'cbx_activate_plugin' . $value_plugin['pro_version'] ) ); ?>" title="<?php _e( 'Activate this plugin', 'xcellorate' ); ?>"><?php _e( 'Activate', 'xcellorate' ); ?></a>
												<?php } elseif ( ! empty( $plugins_array ) && isset( $value_plugin['pro_version'] ) && array_key_exists( $value_plugin['pro_version'], $plugins_array ) ) { ?>
                                                    <form method="post" action="">
                                                        <input type="submit" class="button button-secondary" value="<?php _e( 'Get Pro', 'xcellorate' ); ?>" />
                                                        <input type="hidden" name="cbx_plugin_action_submit" value="submit" />
                                                        <input type="hidden" name="cbx_install_plugin" value="<?php echo $value_plugin['pro_version']; ?>" />
														<?php wp_nonce_field( plugin_basename(__FILE__), 'cbx_license_install_nonce_name' ); ?>
                                                    </form>
												<?php } elseif ( $is_installed ) { ?>
                                                    <a class="button button-secondary" href="<?php echo esc_url( wp_nonce_url( self_admin_url( $current_page . '&cbx_activate_plugin=' . $key_plugin ), 'cbx_activate_plugin' . $key_plugin ) ); ?>" title="<?php _e( 'Activate this plugin', 'xcellorate' ); ?>"><?php _e( 'Activate', 'xcellorate' ); ?></a>
												<?php } else {
													$install_url = isset( $value_plugin['install_url'] ) ? $value_plugin['install_url'] : network_admin_url( 'plugin-install.php?tab=search&type=term&s=' . str_replace( array( ' ', '-' ), '+', str_replace( '&', '', $value_plugin['name'] ) ) . '+CybexSecurity&plugin-search-input=Search+Plugins' ); ?>
                                                    <a class="button button-secondary" href="<?php echo esc_url( $install_url ); ?>" title="<?php _e( 'Install this plugin', 'xcellorate' ); ?>" target="_blank"><?php _e( 'Install Now', 'xcellorate' ); ?></a>
												<?php }
											} ?>
                                        </div>
                                    </div>
                                    <div class="clear"></div>
                                </div>
							<?php }
							if ( $nothing_found ) { ?>
                                <p class="description"><?php _e( 'Nothing found. Try another criteria.', 'xcellorate' ); ?></p>
							<?php } ?>
                        </div>
                        <div id="cbx-filter-wrapper">
                            <div class="cbx-filter">
                                <div class="cbx-filter-title"><?php _e( 'Category', 'xcellorate' ); ?></div>
                                <ul class="cbx-category">
                                    <li>
										<?php $sub_in_url = ( isset( $_GET['sub'] ) && in_array( $_GET['sub'], array( 'installed', 'not_installed' ) ) ) ? '&sub=' . $_GET['sub'] : ''; ?>
                                        <a <?php if ( 'all' == $plugin_category ) echo ' class="cbx-active"'; ?> href="<?php echo esc_url( self_admin_url( $current_page . $sub_in_url ) ); ?>"><?php _e( 'All', 'xcellorate' ); ?>
                                            <span>(<?php echo count( $cbx_plugins ); ?>)</span>
                                        </a>
                                    </li>
									<?php foreach ( $cbx_plugins_category as $category_key => $category_value ) { ?>
                                        <li>
                                            <a <?php if ( $category_key == $plugin_category ) echo ' class="cbx-active"'; ?> href="<?php echo esc_url( self_admin_url( $current_page . $sub_in_url . '&category=' . $category_key ) ); ?>"><?php echo $category_value['name']; ?>
                                                <span>(<?php echo $category_value['count']; ?>)</span>
                                            </a>
                                        </li>
									<?php } ?>
                                </ul>
                            </div>
                        </div><!-- #cbx-filter-wrapper -->
                        <div class="clear"></div>
					<?php }
				} elseif ( 'cbx_themes' == $page || 'themes' == $tab ) {
					?>
                    <h1><?php _e( 'Themes', 'xcellorate' ); ?></h1>
                    <div id="availablethemes" class="cbx-availablethemes">
                        <div class="theme-browser content-filterable rendered">
                            <div class="themes wp-clearfix">
								<?php foreach ( $themes as $key => $theme ) {
									$installed_theme = wp_get_theme( $theme->slug ); ?>
                                    <div class="theme" tabindex="0">
                                        <div class="theme-screenshot">
                                            <img src="<?php echo cbx_menu_url( "icons/themes/" ) . $theme->slug . '.png'; ?>" alt="" />
                                        </div>
                                        <div class="theme-author"><?php printf( __( 'By %s', 'xcellorate' ), 'CybexSecurity' ); ?></div>
                                        <h3 class="theme-name"><?php echo $theme->name; ?></h3>
                                        <div class="theme-actions">
                                            <a class="button button-secondary preview install-theme-preview" href="<?php echo esc_url( $theme->href ); ?>" target="_blank"><?php _e( 'Learn More', 'xcellorate' ); ?></a>
                                        </div>
										<?php if ( $installed_theme->exists() ) {
											if ( $wp_version < '4.6' ) { ?>
                                                <div class="theme-installed"><?php _e( 'Already Installed', 'xcellorate' ); ?></div>
											<?php } else { ?>
                                                <div class="notice notice-success notice-alt inline"><p><?php _e( 'Installed', 'xcellorate' ); ?></p></div>
											<?php }
										} ?>
                                    </div>
								<?php } ?>
                                <br class="clear" />
                            </div>
                        </div>
                        <p><a class="cbx_browse_link" href="https://cybexsecurity.co.uk/products/wordpress/themes/" target="_blank"><?php _e( 'Browse More WordPress Themes', 'xcellorate' ); ?> <span class="dashicons dashicons-arrow-right-alt2"></span></a></p>
                    </div>
				<?php } elseif ( 'cbx_system_status' == $page || 'system-status' == $tab ) { ?>
                    <h1><?php _e( 'System status', 'xcellorate' ); ?></h1>
                    <div class="updated fade notice is-dismissible inline" <?php if ( ! ( isset( $_REQUEST['cbxmn_form_submit'] ) || isset( $_REQUEST['cbxmn_form_submit_custom_email'] ) ) || $error != "" ) echo 'style="display:none"'; ?>><p><strong><?php echo $message; ?></strong></p></div>
                    <div class="error" <?php if ( "" == $error ) echo 'style="display:none"'; ?>><p><strong><?php echo $error; ?></strong></p></div>
                    <form method="post" action="">
                        <p>
                            <input type="hidden" name="cbxmn_form_submit" value="submit" />
                            <input type="submit" class="button-primary" value="<?php _e( 'Send to support', 'xcellorate' ) ?>" />
							<?php wp_nonce_field( plugin_basename(__FILE__), 'cbxmn_nonce_submit' ); ?>
                        </p>
                    </form>
                    <form method="post" action="">
                        <p>
                            <input type="hidden" name="cbxmn_form_submit_custom_email" value="submit" />
                            <input type="submit" class="button" value="<?php _e( 'Send to custom email &#187;', 'xcellorate' ) ?>" />
                            <input type="text" maxlength="250" value="" name="cbxmn_form_email" />
							<?php wp_nonce_field( plugin_basename(__FILE__), 'cbxmn_nonce_submit_custom_email' ); ?>
                        </p>
                    </form>
					<?php foreach ( $system_info as $info ) { ?>
                        <table class="widefat cbx-system-info" cellspacing="0">
                            <thead>
                            <tr>
                                <th colspan="2">
                                    <strong>
										<?php echo $info['name'];
										if ( isset( $info['count'] ) )
											echo ' (' . $info['count'] . ')'; ?>
                                    </strong>
                                </th>
                            </tr>
                            </thead>
                            <tbody>
							<?php foreach ( $info['data'] as $key => $value ) { ?>
                                <tr>
                                    <td scope="row"><?php echo $key; ?></td>
                                    <td scope="row"><?php echo $value; ?></td>
                                </tr>
							<?php } ?>
                            </tbody>
                        </table>
					<?php }
				} ?>
            </div>
        </div>
	<?php }
}


