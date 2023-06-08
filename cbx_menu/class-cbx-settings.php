<?php
/**
 * Displays the content on the plugin settings page
 * @package CybexSecurity
 * @since 1.9.8
 */

if ( ! class_exists( 'cbx_Settings_Tabs' ) ) {
	class cbx_Settings_Tabs {
		private $tabs;
		private $pro_plugin_is_activated = false;
		private $custom_code_args = array();
		private $cbx_plugin_link = '';

		public $plugin_basename;
		public $prefix;
		public $wp_slug;

		public $options;
		public $default_options;
		public $is_network_options;
		public $plugins_info  = array();
		public $hide_pro_tabs = false;
		public $demo_data;

		public $is_pro = false;
		public $pro_page;
		public $cbx_license_plugin;
		public $link_key;
		public $link_pn;
		public $is_trial = false;
		public $licenses;
		public $trial_days;
		public $cbx_hide_pro_option_exist = true;

		public $forbid_view = false;
		public $change_permission_attr = '';

		public $version;
		public $upload_dir;
		public $all_plugins;
		public $is_multisite;

		public $doc_link;
		public $doc_video_link;

		/**
		 * Constructor.
		 *
		 * The child class should call this constructor from its own constructor to override
		 * the default $args.
		 * @access public
		 *
		 * @param array|string $args
		 */
		public function __construct( $args = array() ) {
			global $wp_version;

			$args = wp_parse_args( $args, array(
				'plugin_basename' 	 => '',
				'prefix' 			 => '',
				'plugins_info'		 => array(),
				'default_options' 	 => array(),
				'options' 			 => array(),
				'is_network_options' => false,
				'tabs' 				 => array(),
				'doc_link'			 => '',
				'doc_video_link'	 => '',
				'wp_slug'			 => '',
				'demo_data' 		 => false,
				/* if this is free version and pro exist */
				'link_key'			 => '',
				'link_pn'			 => '',
				'trial_days'		 => false,
				'licenses'			 => array()
			) );

			$args['plugins_info']['Name'] = str_replace( ' by CybexSecurity', '', $args['plugins_info']['Name'] );

			$this->plugin_basename		= $args['plugin_basename'];
			$this->prefix				= $args['prefix'];
			$this->plugins_info			= $args['plugins_info'];
			$this->options				= $args['options'];
			$this->default_options  	= $args['default_options'];
			$this->wp_slug  			= $args['wp_slug'];
			$this->demo_data  			= $args['demo_data'];

			$this->tabs  				= $args['tabs'];
			$this->is_network_options  	= $args['is_network_options'];

			$this->doc_link  			= $args['doc_link'];
			$this->doc_video_link  		= $args['doc_video_link'];

			$this->link_key  			= $args['link_key'];
			$this->link_pn  			= $args['link_pn'];
			$this->trial_days  			= $args['trial_days'];
			$this->licenses 			= $args['licenses'];

			$this->pro_page = $this->cbx_license_plugin = '';
			/* get $cbx_plugins */
			// require( dirname( __FILE__ ) . '/product_list.php' );
			if ( isset( $cbx_plugins[ $this->plugin_basename ] ) ) {
				if ( isset( $cbx_plugins[ $this->plugin_basename ]['pro_settings'] ) ) {
					$this->pro_page  			= $cbx_plugins[ $this->plugin_basename ]['pro_settings'];
					$this->cbx_license_plugin  	= $cbx_plugins[ $this->plugin_basename ]['pro_version'];
				}						

				$this->cbx_plugin_link = substr( $cbx_plugins[ $this->plugin_basename ]['link'],0 , strpos( $cbx_plugins[ $this->plugin_basename ]['link'], '?' ) ); 

				if ( ! empty( $this->link_key ) && ! empty( $this->link_pn ) )
					$this->cbx_plugin_link .= '?k=' . $this->link_key . '&pn=' . $this->link_pn . '&v=' . $this->plugins_info["Version"] . '&wp_v=' . $wp_version;
			}

			$this->hide_pro_tabs = cbx_hide_premium_options_check( $this->options );
			$this->version = '1.0.0';
			$this->is_multisite = is_multisite();

			if ( empty( $this->pro_page ) && array_key_exists( 'license', $this->tabs ) ) {
				$this->is_pro = true;
				$this->licenses[ $this->plugins_info['TextDomain'] ] = array(
					'name'     => $this->plugins_info['Name'],
					'slug'     => $this->plugins_info['TextDomain'],
					'basename' => $this->plugin_basename
				);
			} else {
				$this->licenses[ $this->plugins_info['TextDomain'] ] = array(
					'name'          => $this->plugins_info['Name'],
					'slug'          => $this->plugins_info['TextDomain'],
					'pro_slug'      => substr( $this->cbx_license_plugin, 0, stripos( $this->cbx_license_plugin, '/' ) ),
					'basename'      => $this->plugin_basename,
					'pro_basename'  => $this->cbx_license_plugin
				);
			}
		}

		/**
		 * Displays the content of the "Settings" on the plugin settings page
		 * @access public
		 * @param  void
		 * @return void
		 */
		public function display_content() {
			global $bstwbsftwppdtplgns_options;
			if ( array_key_exists( 'custom_code', $this->tabs ) ) {
				/* get args for `custom code` tab */
				$this->get_custom_code();
			}

			$save_results = $this->save_all_tabs_options();

			$this->display_messages( $save_results );
			if ( isset( $_REQUEST['cbx_restore_default'] ) && check_admin_referer( $this->plugin_basename, 'cbx_nonce_name' ) ) {
				cbx_form_restore_default_confirm( $this->plugin_basename );
			} elseif ( isset( $_POST['cbx_handle_demo'] ) && check_admin_referer( $this->plugin_basename, 'cbx_nonce_name' ) ) {
				$this->demo_data->cbx_demo_confirm();
			} else {
				cbx_show_settings_notice(); ?>
                <form class="cbx_form" method="post" action="" enctype="multipart/form-data">
                    <div id="poststuff">
                        <div id="post-body" class="metabox-holder columns-2">
                            <div id="post-body-content" style="position: relative;">
								<?php $this->display_tabs(); ?>
                            </div><!-- #post-body-content -->
                            <div id="postbox-container-1" class="postbox-container">
                                <div class="meta-box-sortables ui-sortable">
                                    <div id="submitdiv" class="postbox">
                                        <h3 class="hndle"><?php _e( 'Information', 'xcellorate' ); ?></h3>
                                        <div class="inside">
                                            <div class="submitbox" id="submitpost">
                                                <div id="minor-publishing">
                                                    <div id="misc-publishing-actions">
                                                        <?php /**
                                                         * action - Display additional content for #misc-publishing-actions
                                                         */
                                                        do_action( __CLASS__ . '_information_postbox_top' ); ?>
														<?php if ( $this->is_pro ) {
															if ( isset( $bstwbsftwppdtplgns_options['wrong_license_key'][ $this->plugin_basename ] ) || empty( $bstwbsftwppdtplgns_options['time_out'] ) || ! array_key_exists( $this->plugin_basename, $bstwbsftwppdtplgns_options['time_out'] ) ) {
																$license_type = 'Pro';
																$license_status = __( 'Inactive', 'xcellorate' ) . ' <a href="#' . $this->prefix . '_license_tab" class="cbx_trigger_tab_click">' . __( 'Learn More', 'xcellorate' ) . '</a>';
															} else {
																$finish = strtotime( $bstwbsftwppdtplgns_options['time_out'][ $this->plugin_basename ] );
																$today = strtotime( date( "m/d/Y" ) );
																if ( isset( $bstwbsftwppdtplgns_options['trial'][ $this->plugin_basename ] ) ) {
																	$license_type = 'Trial Pro';

																	if ( $finish < $today ) {
																		$license_status = __( 'Expired', 'xcellorate' );
																	} else {
																		$daysleft = floor( ( $finish - $today ) / ( 60*60*24 ) );
																		$license_status = sprintf( __( '%s day(-s) left', 'xcellorate' ), $daysleft );
																	}
																	$license_status .= '. <a target="_blank" href="' . esc_url( $this->plugins_info['PluginURI'] ) . '">' . __( 'Upgrade to Pro', 'xcellorate' ) . '</a>';
																} else {
																	$license_type = isset( $bstwbsftwppdtplgns_options['nonprofit'][ $this->plugin_basename ] ) ? 'Nonprofit Pro' : 'Pro';
																	if ( ! empty( $bstwbsftwppdtplgns_options['time_out'][ $this->plugin_basename ] ) && $finish < $today ) {
																		$license_status = sprintf( __( 'Expired on %s', 'xcellorate' ), $bstwbsftwppdtplgns_options['time_out'][ $this->plugin_basename ] ) . '. <a target="_blank" href="https://support.cybexsecurity.co.uk/entries/53487136">' . __( 'Renew Now', 'xcellorate' ) . '</a>';
																	} else {
																		$license_status = __( 'Active', 'xcellorate' );
																	}
																}
															} ?>
                                                            <div class="misc-pub-section">
                                                                <strong><?php _e( 'License', 'xcellorate' ); ?>:</strong> <?php echo $license_type; ?>
                                                            </div>
                                                            <div class="misc-pub-section">
                                                                <strong><?php _e( 'Status', 'xcellorate' ); ?>:</strong> <?php echo $license_status; ?>
                                                            </div><!-- .misc-pub-section -->
														<?php } ?>
                                                        <div class="misc-pub-section">
                                                            <strong><?php _e( 'Version', 'xcellorate' ); ?>:</strong> <?php echo $this->plugins_info['Version']; ?>
                                                        </div><!-- .misc-pub-section -->
                                                        <?php /**
                                                         * action - Display additional content for #misc-publishing-actions
                                                         */
                                                        do_action( __CLASS__ . '_information_postbox_bottom' ); ?>
                                                    </div>
                                                    <div class="clear"></div>
                                                </div>
                                                <div id="major-publishing-actions">
                                                    <div id="publishing-action">
                                                        <input type="hidden" name="<?php echo $this->prefix; ?>_form_submit" value="submit" />
                                                        <input id="cbx-submit-button" type="submit" class="button button-primary button-large" value="<?php _e( 'Save Changes', 'xcellorate' ); ?>" />
														<?php wp_nonce_field( $this->plugin_basename, 'cbx_nonce_name' ); ?>
                                                    </div>
                                                    <div class="clear"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
									<?php /**
									 * action - Display custom metabox
									 */
									do_action( __CLASS__ . '_display_metabox' ); ?>
                                </div>
                            </div>
                            <div id="postbox-container-2" class="postbox-container">
								<?php /**
								 * action - Display additional content for #postbox-container-2
								 */
								do_action( __CLASS__ . '_display_second_postbox' ); ?>
                                <div class="submit">
                                    <input type="submit" class="button button-primary button-large" value="<?php _e( 'Save Changes', 'xcellorate' ); ?>" />
                                </div>
                            </div>
                        </div>
                </form>
                </div>
			<?php }
		}

		/**
		 * Displays the Tabs
		 * @access public
		 * @param  void
		 * @return void
		 */
		public function display_tabs() { ?>
            <div id="cbx_settings_tabs_wrapper">
                <ul id="cbx_settings_tabs">
					<?php $this->display_tabs_list(); ?>
                </ul>
				<?php $this->display_tabs_content(); ?>
                <div class="clear"></div>
                <input type="hidden" name="cbx_active_tab" value="<?php if ( isset( $_REQUEST['cbx_active_tab'] ) ) echo esc_attr( $_REQUEST['cbx_active_tab'] ); ?>" />
            </div>
		<?php }

		/**
		 * Displays the list of tabs
		 * @access private
		 * @return void
		 */
		private function display_tabs_list() {
			foreach ( $this->tabs as $tab_slug => $data ) {
				if ( ! empty( $data['is_pro'] ) && $this->hide_pro_tabs ) {
					continue;
				}
				$tab_class = 'cbx-tab-' . $tab_slug;
				if ( ! empty( $data['is_pro'] ) ) {
					$tab_class .= ' cbx_pro_tab';
				}
				if ( ! empty( $data['class'] ) ) {
					$tab_class .= ' ' . esc_attr( $data['class'] );
				}
				?>
				<li class="<?php echo esc_attr( $tab_class ); ?>" data-slug="<?php echo esc_attr( $tab_slug ); ?>">
					<a href="#<?php echo esc_attr( $this->prefix ); ?>_<?php echo esc_attr( $tab_slug ); ?>_tab">
						<span><?php echo esc_html( $data['label'] ); ?></span>
					</a>
				</li>
				<?php
			}
		}
		

		/**
		 * Displays the content of tabs
		 * @access private
		 * @param  string $tab_slug
		 * @return void
		 */
		public function display_tabs_content() {
			foreach ( $this->tabs as $tab_slug => $data ) {
				if ( ! empty( $data['is_pro'] ) && $this->hide_pro_tabs )
					continue; ?>
                <div class="cbx_tab ui-tabs-panel ui-widget-content ui-corner-bottom" id="<?php echo esc_attr( $this->prefix . '_' . $tab_slug . '_tab' ); ?>" aria-labelledby="ui-id-2" role="tabpanel" aria-hidden="false" style="display: block;">
					<?php $tab_slug = str_replace( '-', '_', $tab_slug );
					if ( method_exists( $this, 'tab_' . $tab_slug ) ) {
						call_user_func( array( $this, 'tab_' . $tab_slug ) );
						do_action_ref_array( __CLASS__ . '_after_tab_' . $tab_slug, array( &$this ) );
					} ?>
                </div>
			<?php }
		}

		/**
		 * Save all options from all tabs and display errors\messages
		 * @access public
		 * @param  void
		 * @return void
		 */
		public function save_all_tabs_options() {
			$message = $notice = $error = '';
			/* Restore default settings */
			if ( isset( $_POST['cbx_restore_confirm'] ) && check_admin_referer( $this->plugin_basename, 'cbx_settings_nonce_name' ) ) {
				$this->restore_options();
				$message = __( 'All plugin settings were restored.', 'xcellorate' );
				/* Go Pro - check license key */
			} elseif ( isset( $_POST['cbx_license_submit'] ) && check_admin_referer( $this->plugin_basename, 'cbx_nonce_name' ) ) {
				if ( ! empty( $result['empty_field_error'] ) )
					$error = $result['empty_field_error'];
				if ( ! empty( $result['error'] ) )
					$error = $result['error'];
				if ( ! empty( $result['message'] ) )
					$message = $result['message'];
				if ( ! empty( $result['notice'] ) )
					$notice = $result['notice'];
				/* check demo data */
			} else {
				$demo_result = ! empty( $this->demo_data ) ? $this->demo_data->cbx_handle_demo_data() : false;
				if ( false !== $demo_result ) {
					if ( ! empty( $demo_result ) && is_array( $demo_result ) ) {
						$error   = $demo_result['error'];
						$message = $demo_result['done'];
						if ( ! empty( $demo_result['done'] ) && ! empty( $demo_result['options'] ) )
							$this->options = $demo_result['options'];
					}
					/* Save options */
				} elseif ( ! isset( $_REQUEST['cbx_restore_default'] ) && ! isset( $_POST['cbx_handle_demo'] ) && isset( $_REQUEST[ $this->prefix . '_form_submit'] ) && check_admin_referer( $this->plugin_basename, 'cbx_nonce_name' ) ) {
					/* save tabs */
					$result = $this->save_options();
					if ( ! empty( $result['error'] ) )
						$error = $result['error'];
					if ( ! empty( $result['message'] ) )
						$message = $result['message'];
					if ( ! empty( $result['notice'] ) )
						$notice = $result['notice'];

					if ( '' == $this->change_permission_attr ) {
						/* save `misc` tab */
						$result = $this->save_options_misc();
						if ( ! empty( $result['notice'] ) )
							$notice .= $result['notice'];
					}

					if ( array_key_exists( 'custom_code', $this->tabs ) ) {
						/* save `custom code` tab */
						$this->save_options_custom_code();
					}
				}
			}

			return compact( 'message', 'notice', 'error' );
		}

		/**
		 * Display error\message\notice
		 * @access public
		 * @param  $save_results - array with error\message\notice
		 * @return void
		 */
		public function display_messages( $save_results ) {
			/**
			 * action - Display custom error\message\notice
			 */
			do_action( __CLASS__ . '_display_custom_messages', $save_results ); ?>
            <div class="updated fade inline" <?php if ( empty( $save_results['message'] ) ) echo "style=\"display:none\""; ?>><p><strong><?php echo $save_results['message']; ?></strong></p></div>
            <div class="updated cbx-notice inline" <?php if ( empty( $save_results['notice'] ) ) echo "style=\"display:none\""; ?>><p><strong><?php echo $save_results['notice']; ?></strong></p></div>
            <div class="error inline" <?php if ( empty( $save_results['error'] ) ) echo "style=\"display:none\""; ?>><p><strong><?php echo $save_results['error']; ?></strong></p></div>
		<?php }

		/**
		 * Save plugin options to the database
		 * @access public
		 * @param  ab
		 * @return array    The action results
		 * @abstract
		 */
		public function save_options() {
			die( 'function cbx_Settings_Tabs::save_options() must be over-ridden in a sub-class.' );
		}

		/**
		 * Get 'custom_code' status and content
		 * @access private
		 */
		private function get_custom_code() {
			global $bstwbsftwppdtplgns_options;

			$this->custom_code_args = array(
				'is_css_active' => false,
				'content_css'  	=> '',
				'css_writeable'	=> false,
				'is_php_active' => false,
				'content_php' 	=> '',
				'php_writeable'	=> false,
				'is_js_active' 	=> false,
				'content_js' 	=> '',
				'js_writeable'	=> false,
			);

			if ( ! $this->upload_dir )
				$this->upload_dir = wp_upload_dir();

			$folder = $this->upload_dir['basedir'] . '/cbx-custom-code';
			if ( ! $this->upload_dir["error"] ) {
				if ( ! is_dir( $folder ) )
					wp_mkdir_p( $folder, 0755 );

				$index_file = $this->upload_dir['basedir'] . '/cbx-custom-code/index.php';
				if ( ! file_exists( $index_file ) ) {
					if ( $f = fopen( $index_file, 'w+' ) )
						fclose( $f );
				}
			}

			if ( $this->is_multisite )
				$this->custom_code_args['blog_id'] = get_current_blog_id();

			foreach ( array( 'css', 'php', 'js' ) as $extension ) {
				$file = 'cbx-custom-code.' . $extension;
				$real_file = $folder . '/' . $file;

				if ( file_exists( $real_file ) ) {
					update_recently_edited( $real_file );
					$this->custom_code_args["content_{$extension}"] = file_get_contents( $real_file );
					if ( ( $this->is_multisite && isset( $bstwbsftwppdtplgns_options['custom_code'][ $this->custom_code_args['blog_id'] ][ $file ] ) ) ||
					     ( ! $this->is_multisite && isset( $bstwbsftwppdtplgns_options['custom_code'][ $file ] ) ) ) {
						$this->custom_code_args["is_{$extension}_active"] = true;
					}
					if ( is_writeable( $real_file ) )
						$this->custom_code_args["{$extension}_writeable"] = true;
				} else {
					$this->custom_code_args["{$extension}_writeable"] = true;
					if ( 'php' == $extension )
						$this->custom_code_args["content_{$extension}"] = "<?php" . "\n" . "if ( ! defined( 'ABSPATH' ) ) exit;" . "\n" . "if ( ! defined( 'cbx_GLOBAL' ) ) exit;" . "\n\n" . "/* Start your code here */" . "\n";
				}
			}
		}

		/**
		 * Display 'custom_code' tab
		 * @access private
		 */
		private function tab_custom_code() { ?>
            <h3 class="cbx_tab_label"><?php _e( 'Custom Code', 'xcellorate' ); ?></h3>
			<?php $this->help_phrase(); ?>
            <hr>
			<?php if ( ! current_user_can( 'edit_plugins' ) ) {
				echo '<p>' . __( 'You do not have sufficient permissions to edit plugins for this site.', 'xcellorate' ) . '</p>';
				return;
			}

			$list = array(
				'css' => array( 'description' 	=> __( 'These styles will be added to the header on all pages of your site.', 'xcellorate' ),
				                'learn_more_link'	=> 'https://developer.mozilla.org/en-US/docs/Web/Guide/CSS/Getting_started'
				),
				'php' => array( 'description' 	=> sprintf( __( 'This PHP code will be hooked to the %s action and will be printed on front end only.', 'xcellorate' ), '<a href="https://codex.wordpress.org/Plugin_API/Action_Reference/init" target="_blank"><code>init</code></a>' ),
				                'learn_more_link'	=> 'https://php.net/'
				),
				'js' => array( 'description' 	=> __( 'These code will be added to the header on all pages of your site.', 'xcellorate' ),
				               'learn_more_link'	=> 'https://developer.mozilla.org/en-US/docs/Web/JavaScript'
				),
			);

			if ( ! $this->custom_code_args['css_writeable'] ||
			     ! $this->custom_code_args['php_writeable'] ||
			     ! $this->custom_code_args['js_writeable'] ) { ?>
                <p><em><?php printf( __( 'You need to make this files writable before you can save your changes. See %s the Codex %s for more information.', 'xcellorate' ),
							'<a href="https://codex.wordpress.org/Changing_File_Permissions" target="_blank">',
							'</a>' ); ?></em></p>
			<?php }

			foreach ( $list as $extension => $extension_data ) {
				$name = 'js' == $extension ? 'JavaScript' : strtoupper( $extension ); ?>
                <p><big>
                        <strong><?php echo $name; ?></strong>
						<?php if ( ! $this->custom_code_args["{$extension}_writeable"] )
							echo '(' . __( 'Browsing', 'xcellorate' ) . ')'; ?>
                    </big></p>
                <p class="cbx_info">
                    <label>
                        <input type="checkbox" name="cbx_custom_<?php echo $extension; ?>_active" value="1" <?php if ( $this->custom_code_args["is_{$extension}_active"] ) echo "checked"; ?> />
						<?php printf( __( 'Activate custom %s code.', 'xcellorate' ), $name ); ?>
                    </label>
                </p>
                <textarea cols="70" rows="25" name="cbx_newcontent_<?php echo $extension; ?>" id="cbx_newcontent_<?php echo $extension; ?>"><?php if ( isset( $this->custom_code_args["content_{$extension}"] ) ) echo esc_textarea( $this->custom_code_args["content_{$extension}"] ); ?></textarea>
                <p class="cbx_info">
					<?php echo $extension_data['description']; ?>
                    <br>
                    <a href="<?php echo esc_url( $extension_data['learn_more_link'] ); ?>" target="_blank">
						<?php printf( __( 'Learn more about %s', 'xcellorate' ), $name ); ?>
                    </a>
                </p>
			<?php }
		}

		/**
		 * Save plugin options to the database
		 * @access private
		 * @return array    The action results
		 */
		private function save_options_custom_code() {
			global $bstwbsftwppdtplgns_options;
			$folder = $this->upload_dir['basedir'] . '/cbx-custom-code';

			foreach ( array( 'css', 'php', 'js' ) as $extension ) {
				$file = 'cbx-custom-code.' . $extension;
				$real_file = $folder . '/' . $file;

				if ( isset( $_POST["cbx_newcontent_{$extension}"] ) &&
					$this->custom_code_args["{$extension}_writeable"] ) {
					$newcontent = trim( sanitize_textarea_field( wp_unslash( $_POST["cbx_newcontent_{$extension}"] ) ) );

					if ( 'css' == $extension ) {
						$newcontent = wp_kses( $newcontent, array( '\'', '\"' ) );
					}

					if ( ! empty( $newcontent ) && isset( $_POST["cbx_custom_{$extension}_active"] ) ) {
						$this->custom_code_args["is_{$extension}_active"] = true;
						if ( $this->is_multisite ) {
							$bstwbsftwppdtplgns_options['custom_code'][ $this->custom_code_args['blog_id'] ][ $file ] = ( 'php' == $extension ) ? $real_file : $this->upload_dir['baseurl'] . '/cbx-custom-code/' . $file;
						} else {
							$bstwbsftwppdtplgns_options['custom_code'][ $file ] = ( 'php' == $extension ) ? $real_file : $this->upload_dir['baseurl'] . '/cbx-custom-code/' . $file;
						}
					} else {
						$this->custom_code_args["is_{$extension}_active"] = false;
						if ( $this->is_multisite ) {
							if ( isset( $bstwbsftwppdtplgns_options['custom_code'][ $this->custom_code_args['blog_id'] ][ $file ] ) ) {
								unset( $bstwbsftwppdtplgns_options['custom_code'][ $this->custom_code_args['blog_id'] ][ $file ] );
							}
						} else {
							if ( isset( $bstwbsftwppdtplgns_options['custom_code'][ $file ] ) ) {
								unset( $bstwbsftwppdtplgns_options['custom_code'][ $file ] );
							}
						}
					}
					
					if ( $f = fopen( $real_file, 'w+' ) ) {
						fwrite( $f, $newcontent );
						fclose( $f );
						$this->custom_code_args["content_{$extension}"] = $newcontent;
					}
				}

			}

			if ( $this->is_multisite )
				update_site_option( 'bstwbsftwppdtplgns_options', $bstwbsftwppdtplgns_options );
			else
				update_option( 'bstwbsftwppdtplgns_options', $bstwbsftwppdtplgns_options );
		}

		/**
		 * Display 'misc' tab
		 * @access private
		 */
		private function tab_misc() {
			global $bstwbsftwppdtplgns_options; ?>
            <h3 class="cbx_tab_label"><?php _e( 'Miscellaneous Settings', 'xcellorate' ); ?></h3>
			<?php $this->help_phrase(); ?>
            <hr>
			<?php /**
			 * action - Display custom options on the Import / Export' tab
			 */
			do_action( __CLASS__ . '_additional_misc_options' );

			if ( ! $this->forbid_view && ! empty( $this->change_permission_attr ) ) { ?>
                <div class="error inline cbx_visible"><p><strong><?php _e( "Notice", 'xcellorate' ); ?>:</strong> <strong><?php printf( __( "It is prohibited to change %s settings on this site in the %s network settings.", 'xcellorate' ), $this->plugins_info["Name"], $this->plugins_info["Name"] ); ?></strong></p></div>
			<?php }
			if ( $this->forbid_view ) { ?>
                <div class="error inline cbx_visible"><p><strong><?php _e( "Notice", 'xcellorate' ); ?>:</strong> <strong><?php printf( __( "It is prohibited to view %s settings on this site in the %s network settings.", 'xcellorate' ), $this->plugins_info["Name"], $this->plugins_info["Name"] ); ?></strong></p></div>
			<?php } else { ?>
                <table class="form-table">
					<?php /**
					 * action - Display custom options on the 'misc' tab
					 */
					do_action( __CLASS__ . '_additional_misc_options_affected' );
					if ( ! empty( $this->pro_page ) && $this->cbx_hide_pro_option_exist ) { ?>
                        <!-- <tr>
                            <th scope="row"><?php _e( 'Pro Options', 'xcellorate' ); ?></th>
                            <td>
                                <label>
                                    <input <?php echo $this->change_permission_attr; ?> name="cbx_hide_premium_options_submit" type="checkbox" value="1" <?php if ( ! $this->hide_pro_tabs ) echo 'checked="checked "'; ?> />
                                    <span class="cbx_info"><?php _e( 'Enable to display plugin Pro options.', 'xcellorate' ); ?></span>
                                </label>
                            </td>
                        </tr> -->
					<?php } ?>
                    <tr>
                        <th scope="row"><?php _e( 'Track Usage', 'xcellorate' ); ?></th>
                        <td>
                            <label>
                                <input <?php echo $this->change_permission_attr; ?> name="cbx_track_usage" type="checkbox" value="1" <?php if ( ! empty( $bstwbsftwppdtplgns_options['track_usage']['products'][ $this->plugin_basename ] ) ) echo 'checked="checked "'; ?>/>
                                <span class="cbx_info"><?php _e( 'Enable to allow tracking plugin usage anonymously in order to make it better.', 'xcellorate' ); ?></span>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e( 'Default Settings', 'xcellorate' ); ?></th>
                        <td>
                            <input<?php echo $this->change_permission_attr; ?> name="cbx_restore_default" type="submit" class="button" value="<?php _e( 'Restore Settings', 'xcellorate' ); ?>" />
                            <div class="cbx_info"><?php _e( 'This will restore plugin settings to defaults.', 'xcellorate' ); ?></div>
                        </td>
                    </tr>
                </table>
			<?php }
		}

		/**
		 * Display 'Import / Export' tab
		 * @access private
		 */
		public function tab_import_export() { ?>
            <h3 class="cbx_tab_label"><?php _e( 'Import / Export', 'xcellorate' ); ?></h3>
			<?php $this->help_phrase(); ?>
            <hr>
			<?php /**
			 * action - Display custom options on the Import / Export' tab
			 */
			do_action( __CLASS__ . '_additional_import_export_options' );

			if ( ! $this->forbid_view && ! empty( $this->change_permission_attr ) ) { ?>
                <div class="error inline cbx_visible"><p><strong><?php _e( "Notice", 'xcellorate' ); ?>:</strong> <strong><?php printf( __( "It is prohibited to change %s settings on this site in the %s network settings.", 'xcellorate' ), $this->plugins_info["Name"], $this->plugins_info["Name"] ); ?></strong></p></div>
			<?php }
			if ( $this->forbid_view ) { ?>
                <div class="error inline cbx_visible"><p><strong><?php _e( "Notice", 'xcellorate' ); ?>:</strong> <strong><?php printf( __( "It is prohibited to view %s settings on this site in the %s network settings.", 'xcellorate' ), $this->plugins_info["Name"], $this->plugins_info["Name"] ); ?></strong></p></div>
			<?php } else { ?>
                <table class="form-table">
					<?php /**
					 * action - Display custom options on the Import / Export' tab
					 */
					do_action( __CLASS__ . '_additional_import_export_options_affected' ); ?>
                </table>
			<?php }
		}

		/**
		 * Save plugin options to the database
		 * @access private
		 */
		private function save_options_misc() {
			global $bstwbsftwppdtplgns_options, $wp_version;
			$notice = '';

			/* hide premium options */
			if ( ! empty( $this->pro_page ) ) {
				if ( isset( $_POST['cbx_hide_premium_options'] ) ) {
					$hide_result = cbx_hide_premium_options( $this->options );
					$this->hide_pro_tabs = true;
					$this->options = $hide_result['options'];
					if ( ! empty( $hide_result['message'] ) )
						$notice = $hide_result['message'];
					if ( $this->is_network_options )
						update_site_option( $this->prefix . '_options', $this->options );
					else
						update_option( $this->prefix . '_options', $this->options );
				} else if ( isset( $_POST['cbx_hide_premium_options_submit'] ) ) {
					if ( ! empty( $this->options['hide_premium_options'] ) ) {
						$key = array_search( get_current_user_id(), $this->options['hide_premium_options'] );
						if ( false !== $key )
							unset( $this->options['hide_premium_options'][ $key ] );
						if ( $this->is_network_options )
							update_site_option( $this->prefix . '_options', $this->options );
						else
							update_option( $this->prefix . '_options', $this->options );
					}
					$this->hide_pro_tabs = false;
				} else {
					if ( empty( $this->options['hide_premium_options'] ) ) {
						$this->options['hide_premium_options'][] = get_current_user_id();
						if ( $this->is_network_options )
							update_site_option( $this->prefix . '_options', $this->options );
						else
							update_option( $this->prefix . '_options', $this->options );
					}
					$this->hide_pro_tabs = true;
				}
			}
			/* Save 'Track Usage' option */
			if ( isset( $_POST['cbx_track_usage'] ) ) {
				if ( empty( $bstwbsftwppdtplgns_options['track_usage']['products'][ $this->plugin_basename ] ) ) {
					$bstwbsftwppdtplgns_options['track_usage']['products'][ $this->plugin_basename ] = true;
					$track_usage = true;
				}
			} else {
				if ( ! empty( $bstwbsftwppdtplgns_options['track_usage']['products'][ $this->plugin_basename ] ) ) {
					unset( $bstwbsftwppdtplgns_options['track_usage']['products'][ $this->plugin_basename ] ); false;
					$track_usage = false;
				}
			}
			return compact( 'notice' );
		}

		/**
		 * Display help phrase
		 * @access public
		 * @param  void
		 * @return array    The action results
		 */
		public function help_phrase() {

		}

		public function cbx_pro_block_links() {
			global $wp_version; ?>
            <div class="cbx_pro_version_tooltip">
                <a class="cbx_button" href="<?php echo esc_url( $this->plugins_info['PluginURI'] ); ?>?k=<?php echo $this->link_key; ?>&amp;pn=<?php echo $this->link_pn; ?>&amp;v=<?php echo $this->plugins_info["Version"]; ?>&amp;wp_v=<?php echo $wp_version; ?>" target="_blank" title="<?php echo $this->plugins_info["Name"]; ?>"><?php _e( 'Upgrade to Pro', 'xcellorate' ); ?></a>
				<?php if ( $this->trial_days !== false ) { ?>
                    <span class="cbx_trial_info">
						<?php _e( 'or', 'xcellorate' ); ?>
                        <a href="<?php echo esc_url( $this->plugins_info['PluginURI'] . '?k=' . $this->link_key . '&pn=' . $this->link_pn . '&v=' . $this->plugins_info["Version"] . '&wp_v=' . $wp_version ); ?>" target="_blank" title="<?php echo $this->plugins_info["Name"]; ?>"><?php _e( 'Start Your Free Trial', 'xcellorate' ); ?></a>
					</span>
				<?php } ?>
                <div class="clear"></div>
            </div>
		<?php }

		/**
		 * Restore plugin options to defaults
		 * @access public
		 * @param  void
		 * @return void
		 */
		public function restore_options() {
			unset(
				$this->default_options['first_install'],
				$this->default_options['suggest_feature_banner'],
				$this->default_options['display_settings_notice']
			);
			/**
			 * filter - Change default_options array OR process custom functions
			 */
			$this->options = apply_filters( __CLASS__ . '_additional_restore_options', $this->default_options );
			if ( $this->is_network_options ) {
				$this->options['network_apply'] = 'default';
				$this->options['network_view'] = '1';
				$this->options['network_change'] = '1';
				update_site_option( $this->prefix . '_options', $this->options );
			} else {
				update_option( $this->prefix . '_options', $this->options );
			}
			update_site_option( 'rwl_page', 'login' );
			update_option( 'rwl_redirect_field', '' );

		}
	}
}
