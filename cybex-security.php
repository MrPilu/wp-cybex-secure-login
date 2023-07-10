<?php
/*
Plugin Name: CybexSecurity
Plugin URI: https://cybexsecurity.co.uk/products/wordpress/plugins/cybex-security/
Description: WP CybeX Security offers a range of powerful and useful features such as login URL customisation, limit login, and two-factor authentication using Google Authenticator.
Author: CybexSecurity
Version: 1.0.0
Text Domain: cybex-security
Domain Path: /languages
Author URI: https://cybexsecurity.co.uk/
License: GPLv3 or later
*/

/*  © Copyright 2023  CybexSecurity  ( https://support.cybexsecurity.co.uk )

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License version, as
	published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See th
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

// Plugin constants
define( 'WP_CYBEX_SECURITY_VERSION', '0.1' );
define( 'WP_CYBEX_SECURITY_FOLDER', 'cybex-security' );

define( 'WP_CYBEX_SECURITY_URL', plugin_dir_url( __FILE__ ) );
define( 'WP_CYBEX_SECURITY_DIR', plugin_dir_path( __FILE__ ) );
define( 'WP_CYBEX_SECURITY_BASENAME', plugin_basename( __FILE__ ) );

require_once( dirname( __FILE__ ) . '/includes/login-url-functions.php' );
require_once( dirname( __FILE__ ) . '/includes/google-authenticator.php' );

if ( ! is_admin() )
	require_once( dirname( __FILE__ ) . '/includes/front-end-functions.php' );

/**
 * Function for adding menu and submenu
 */
if ( ! function_exists( 'cbxsec_add_admin_menu' ) ) {
	function cbxsec_add_admin_menu() {
		global $wp_version, $submenu, $cbxsec_plugin_info;

		$hook = add_menu_page(
            __( 'Wp Cybex Security Settings', 'cybex-security' ),
            'Wp Cybex Security',
            'manage_options',
            'cybex-security.php',
            'cbxsec_settings_page',
            'none'
        );

		add_submenu_page(
            'cybex-security.php',
            __( 'Wp Cybex Security Settings', 'cybex-security' ),
            __( 'Settings', 'cybex-security' ),
            'manage_options',
            'cybex-security.php',
            'cbxsec_settings_page'
        );

		add_submenu_page(
            'cybex-security.php',
            __( 'Wp Cybex Security Blocked', 'cybex-security' ),
            __( 'Blocked', 'cybex-security' ),
            'manage_options',
            'cybex-security-blocked.php',
            'cbxsec_settings_page'
        );

		add_submenu_page(
            'cybex-security.php',
            __( 'Wp Cybex Security Deny & Allow List', 'cybex-security' ),
            __( 'Deny List', 'cybex-security' ),
            'manage_options',
            'cybex-security-deny-and-allowlist.php',
            'cbxsec_settings_page'
        );

		add_submenu_page(
            'cybex-security.php',
            __( 'Wp Cybex Security Statistics', 'cybex-security' ),
            __( 'Statistics', 'cybex-security' ),
            'manage_options',
            'cybex-security-statistics.php',
            'cbxsec_settings_page'
        );

    	add_submenu_page( 
       		'cybex-security-create-item.php', 
       		__( 'Add New', 'cybex-security' ), 
       		__( 'Add New', 'cybex-security' ), 
       		'manage_options', 
       		'cbxsec-create-new-items.php', 
       		'cbxsec_create_new_item' );

	}
}

if ( ! function_exists( 'cbxsec_plugins_loaded' ) ) {
	function cbxsec_plugins_loaded() {
		/* Internationalization, first(!) */
		load_plugin_textdomain( 'cybex-security', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}
}

/**
 * Function initialisation plugin for init
 */
if ( ! function_exists( 'cbxsec_plugin_init' ) ) {
	function cbxsec_plugin_init() {
		global $cbxsec_plugin_info, $cbxsec_page;
		$plugin_basename = plugin_basename( __FILE__ );

		require_once( dirname( __FILE__ ) . '/cbx_menu/cbx_include.php' );
		cbx_include_init( $plugin_basename );

		if ( empty( $cbxsec_plugin_info ) ) {
			if ( ! function_exists( 'get_plugin_data' ) ) {
				require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			}
			$cbxsec_plugin_info = get_plugin_data( __FILE__ );
		}

		/* check WordPress version */
		cbx_wp_min_version_check( $plugin_basename, $cbxsec_plugin_info, '4.5' );

		$cbxsec_page = array(
			'cybex-security.php',
			'cybex-security-blocked.php',
			'cybex-security-deny-and-allowlist.php',
			'cybex-security-log.php',
			'cybex-security-statistics.php',
			'cbxsec-create-new-items.php'
		);

		/* Call register settings function */
		if ( ( isset( $_GET['page'] ) && in_array( $_GET['page'], $cbxsec_page ) ) || ! is_admin() )
			register_cbxsec_settings();
	}
}

/**
 * Function initialisation plugin for admin_init
 */
if ( ! function_exists( 'cbxsec_plugin_admin_init' ) ) {
	function cbxsec_plugin_admin_init() {
		global $cbx_plugin_info, $cbxsec_plugin_info, $pagenow, $cbxsec_options;

		if ( empty( $cbx_plugin_info ) )
			$cbx_plugin_info = array( 'id' => '140', 'version' => $cbxsec_plugin_info["Version"] );

		if ( 'plugins.php' == $pagenow ) {
			/* Install the option defaults */
			if ( function_exists( 'cbx_plugin_banner_go_pro' ) ) {
				register_cbxsec_settings();
				cbx_plugin_banner_go_pro( $cbxsec_options, $cbxsec_plugin_info, 'cybex-security', 'cybex-security', '33bc89079511cdfe28aeba317abfaf37', '140', 'cybex-security' );
			}
		}

	}
}

/**
 * Function to add stylesheets - icon for menu
 */
if ( ! function_exists( 'cbxsec_admin_head' ) ) {
	function cbxsec_admin_head() { ?>
		<style type="text/css">
			.menu-top.toplevel_page_cybex-security .wp-menu-image {
			}
			.menu-top.toplevel_page_cybex-security .wp-menu-image:before {
				content: "\f198";
			}
		</style>
	<?php }
}

/**
 * Function to add stylesheets
 */
if ( ! function_exists( 'cbxsec_enqueue_scripts' ) ) {
	function cbxsec_enqueue_scripts() {
		global $cbxsec_page;
		if ( isset( $_GET['page'] ) && in_array( $_GET['page'], $cbxsec_page ) ) {
			wp_enqueue_style( 'cbxsec_stylesheet', plugins_url( 'css/style.css', __FILE__ ) );
			/* script */
			$script_vars = array(
				'cbxsec_ajax_nonce' => wp_create_nonce( 'cbxsec_ajax_nonce_value' ),
			);
			wp_enqueue_script( 'cbxsec_script', plugins_url( 'js/script.js', __FILE__ ), array( 'jquery' ) );
			wp_localize_script( 'cbxsec_script', 'cbxsecScriptVars', $script_vars );

			cbx_enqueue_settings_scripts();
			cbx_plugins_include_codemirror();
		}
	}
}

/**
 * Get $default_messages array with info on defaults messages
 *
 * @return array $default_messages with info on default messages
 */
if ( ! function_exists( 'cbxsec_get_default_messages' ) ) {
	function cbxsec_get_default_messages() {
		$default_messages = array(
			/* Error Messages */
			'failed_message'						=> sprintf( __( '%s attempts left before block.', 'cybex-security' ), '%ATTEMPTS%' ),
			'blocked_message'						=> sprintf( __( 'Too many failed attempts. You have been blocked until %s.', 'cybex-security' ), '%DATE%' ),
			'denylisted_message'					=> __( "You've been added to deny list. Please contact website administrator.", 'cybex-security' ),
			/* Email Notifications */
			'email_subject'							=> sprintf( __( '%s has been blocked on %s', 'cybex-security' ), '%IP%', '%SITE_NAME%' ),
			'email_subject_denylisted'				=> sprintf( __( '%s has been added to the deny list on %s', 'cybex-security' ), '%IP%', '%SITE_NAME%' ),
			'email_blocked'							=> sprintf( __( 'IP %s has been blocked automatically on %s due to the excess of login attempts on your website %s.', 'cybex-security' ), '%IP%', '%WHEN%', '<a href="%SITE_URL%">%SITE_NAME%</a>' ) . '<br/><br/>' . sprintf( __( 'Using the plugin %s', 'cybex-security' ), '<a href="%PLUGIN_LINK%">Wp Cybex Security by CybexSecurity</a>' ),
			'email_denylisted'						=> sprintf( __( 'IP %s has been added automatically to the deny list on %s due to the excess of locks quantity on your website %s.', 'cybex-security' ), '%IP%', '%WHEN%', '<a href="%SITE_URL%">%SITE_NAME%</a>' ) . '<br/><br/>' . sprintf( __( 'Using the plugin %s', 'cybex-security' ), '<a href="%PLUGIN_LINK%">Wp Cybex Security by CybexSecurity</a>' )
		);
		return $default_messages;
	}
}

/**
 * Activation plugin function
 */
if ( ! function_exists( 'cbxsec_plugin_activate' ) ) {
	function cbxsec_plugin_activate( $networkwide ) {
        global $wpdb;
	    if ( function_exists( 'is_multisite' ) && is_multisite() ) {
            /* check if it is a network activation - if so, run the activation function for each blog id */
            if ( $networkwide ) {
                $old_blog = $wpdb->blogid;
                /* Get all blog ids */
                $blogids = $wpdb->get_col( "SELECT `blog_id` FROM $wpdb->blogs" );
                foreach ( $blogids as $blog_id ) {
                    switch_to_blog( $blog_id );
                    cbxsec_create_table();
                }
                switch_to_blog( $old_blog );
                return;
            }
        }
        cbxsec_create_table();
	    if ( is_multisite() ) {
			switch_to_blog( 1 );
			register_uninstall_hook( __FILE__, 'cbxsec_plugin_uninstall' );
			restore_current_blog();
		} else {
			register_uninstall_hook( __FILE__, 'cbxsec_plugin_uninstall' );
		}
	}
}

/**
 * Activation function for new blog in network
 */
if ( ! function_exists( 'cbxsec_new_blog' ) ) {
	function cbxsec_new_blog( $blog_id, $user_id, $domain, $path, $site_id, $meta ) {
		global $wpdb;
		if ( is_plugin_active_for_network( 'cybex-security/cybex-security.php' ) ) {
			$old_blog = $wpdb->blogid;
			switch_to_blog( $blog_id );
			cbxsec_create_table();
			switch_to_blog( $old_blog );
		}
	}
}

/**
 * Initial tables create
 */
if ( ! function_exists( 'cbxsec_create_table' ) ) {
	function cbxsec_create_table() {
		global $wpdb;
		$prefix = $wpdb->prefix . 'cbxsec_';
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		/* Query for create table with current number of failed attempts and block quantity, block status and time when addres will be deblocked */
        $sql = "CREATE TABLE IF NOT EXISTS `" . $prefix . "failed_attempts` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `ip` CHAR(31) NOT NULL,
            `ip_int` BIGINT,
            `email` VARCHAR( 255 ),
            `failed_attempts` INT(3) NOT NULL DEFAULT '0',
            `block` BOOL DEFAULT FALSE,
            `block_quantity` INT(3) NOT NULL DEFAULT '0',
            `block_start` DATETIME,
            `block_till` DATETIME,
            `block_by` VARCHAR( 255 ),
            `last_failed_attempt` TIMESTAMP,
            PRIMARY KEY (`id`)
            ) DEFAULT CHARSET=utf8;";
        dbDelta( $sql );
		/* Query for create table with all number of failed attempts and block quantity, block status and time when addres will be deblocked */
        $sql = "CREATE TABLE IF NOT EXISTS `" . $prefix . "all_failed_attempts` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `ip` CHAR(31) NOT NULL,
            `ip_int` BIGINT,
            `email` VARCHAR( 255 ),
            `failed_attempts` INT(4) NOT NULL DEFAULT '0',
            `block` BOOL DEFAULT FALSE,
            `block_quantity` INT(3) NOT NULL DEFAULT '0',
            `last_failed_attempt` TIMESTAMP,
            PRIMARY KEY (`id`)
            ) DEFAULT CHARSET=utf8;";
        dbDelta( $sql );
		/* Query for create table with allowlisted addresses */
        $sql = "CREATE TABLE IF NOT EXISTS `" . $prefix . "allowlist` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `ip` CHAR(31) NOT NULL UNIQUE,
            `add_time` DATETIME,
            PRIMARY KEY (`id`)
            ) DEFAULT CHARSET=utf8;";
        dbDelta( $sql );
		/* Query for create table with denylisted addresse */
        $sql = "CREATE TABLE IF NOT EXISTS `" . $prefix . "denylist` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `ip` CHAR(31) NOT NULL UNIQUE,
            `add_time` DATETIME,
            PRIMARY KEY (`id`)
            ) DEFAULT CHARSET=utf8;";
        dbDelta( $sql );

        // Query to create table with denylisted email addresses
		$sql = "CREATE TABLE IF NOT EXISTS `" . $prefix . "denylist_email` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `email` VARCHAR( 255 ),
            `add_time` DATETIME,
            PRIMARY KEY (`id`)
            ) DEFAULT CHARSET=utf8;";
		dbDelta($sql);

		// Query to create table with emails for CF
		$sql = "CREATE TABLE IF NOT EXISTS `" . $prefix . "email_list` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `id_failed_attempts` INT,
            `id_failed_attempts_statistics` INT,
            `ip` CHAR(31) NOT NULL,
            `email` VARCHAR( 255 ),
            PRIMARY KEY (`id`)
            ) DEFAULT CHARSET=utf8;";
		dbDelta( $sql );
    }
}

/**
* Register settings function
*/
if ( ! function_exists( 'register_cbxsec_settings' ) ) {
	function register_cbxsec_settings() {
		global $cbxsec_options, $cbxsec_plugin_info, $wpdb;

		$prefix = $wpdb->prefix . 'cbxsec_';
		$db_version = "1.7";

		/* Install the option defaults */
		if ( ! get_option( 'cbxsec_options' ) ) {
			$options_default = cbxsec_get_options_default();
			add_option( 'cbxsec_options', $options_default );
			/* Schedule event to clear statistics daily */
			$time = time() - fmod( time(), 86400 ) + 86400;
			wp_schedule_event( $time, 'daily', 'cbxsec_daily_statistics_clear' );
		}
		/* Get options from the database */
		$cbxsec_options = get_option( 'cbxsec_options' );

		if ( ! isset( $cbxsec_options['plugin_db_version'] ) || $cbxsec_options['plugin_db_version'] != $db_version ) {

            cbxsec_create_table();

			/* crop table 'all_failed_attempts' */
			// $column_exists = $wpdb->query( "SHOW COLUMNS FROM `" . $prefix . "all_failed_attempts` LIKE 'invalid_captcha_from_login_form';" );
			$column_exists = $wpdb->get_var( $wpdb->prepare( "SHOW COLUMNS FROM `%sall_failed_attempts` LIKE %s;", $prefix, 'invalid_captcha_from_login_form' ) );

			if ( ! empty( $column_exists ) ) {
				$wpdb->query( $wpdb->prepare(
					"ALTER TABLE `%sall_failed_attempts`
					DROP `invalid_captcha_from_login_form`,
					DROP `invalid_captcha_from_registration_form`,
					DROP `invalid_captcha_from_reset_password_form`,
					DROP `invalid_captcha_from_comments_form`,
					DROP `invalid_captcha_from_contact_form`,
					DROP `invalid_captcha_from_subscriber`,
					DROP `invalid_captcha_from_bp_registration_form`,
					DROP `invalid_captcha_from_bp_comments_form`,
					DROP `invalid_captcha_from_bp_create_group_form`,
					DROP `invalid_captcha_from_contact_form_7`;", $prefix
				) );
			}
			
			$column_exists = $wpdb->get_var( $wpdb->prepare(
				"SHOW COLUMNS FROM `%sfailed_attempts` LIKE %s;", $prefix, 'block_by'
			) );
			
			if ( 0 == $column_exists ) {
				$wpdb->query( $wpdb->prepare(
					"ALTER TABLE `%sfailed_attempts` ADD `block_by` TEXT AFTER `block_till`;", $prefix
				) );
			}
			
			$column_exists = $wpdb->query( $wpdb->prepare(
				"SHOW COLUMNS FROM `%sfailed_attempts` LIKE 'email'", $prefix
			) );
			if ( 0 == $column_exists ) {
				$wpdb->query( $wpdb->prepare(
					"ALTER TABLE `%sfailed_attempts` ADD `email` TEXT AFTER `ip_int`;", $prefix
				) );
			}
			
			$column_exists = $wpdb->query( "SHOW COLUMNS FROM `{$prefix}failed_attempts` LIKE 'last_failed_attempt'" );
			if ( 0 == $column_exists ) {
				$wpdb->query( "ALTER TABLE `{$prefix}failed_attempts` ADD `last_failed_attempt` TIMESTAMP AFTER `block_by`;" );
			}
			
			$column_exists = $wpdb->query( "SHOW COLUMNS FROM `{$prefix}all_failed_attempts` LIKE 'email'" );
			if ( 0 == $column_exists ) {
				$wpdb->query( "ALTER TABLE `{$prefix}all_failed_attempts` ADD `email` TEXT AFTER `ip_int`;" );
			}
			
			$column_exists = $wpdb->query( "SHOW COLUMNS FROM `{$prefix}all_failed_attempts` LIKE 'block'" );
			if ( 0 == $column_exists ) {
				$wpdb->query( "ALTER TABLE `{$prefix}all_failed_attempts` ADD `block` BOOL DEFAULT FALSE AFTER `failed_attempts`;" );
			}			

			/* update database to version 1.3 */
			$tables = array( 'denylist', 'allowlist', 'failed_attempts', 'all_failed_attempts' );
			foreach ( $tables as $table_name ) {
				$table = $prefix . $table_name;
				if ( 0 == $wpdb->query( "SHOW COLUMNS FROM {$table} LIKE 'id';" ) ) {
					if ( in_array( $table_name, array( 'allowlist', 'denylist' ) ) ) {
						$wpdb->query(
							"ALTER TABLE {$table} DROP PRIMARY KEY,
							ADD `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST,
							ADD `add_time` DATETIME;" );
						$indexes = $wpdb->get_results( "SHOW KEYS FROM `{$table}` WHERE Key_name Like '%ip%'" );
						if ( empty( $indexes ) ) {
							/* add necessary indexes */
							$wpdb->query( "ALTER IGNORE TABLE `{$table}` ADD UNIQUE (`ip`);" );
						} else {
							/* remove excess indexes */
							$drop = array();
							foreach( $indexes as $index ) {
								if ( preg_match( '|ip_|', $index->Key_name ) && ! in_array( " DROP INDEX " . $index->Key_name, $drop ) )
									$drop[] = " DROP INDEX " . $index->Key_name;
							}
							if ( ! empty( $drop ) )
								$wpdb->query( "ALTER TABLE `{$table}`" . implode( ',', $drop ) );
						}
					} else {
						$wpdb->query( "ALTER TABLE {$table} DROP PRIMARY KEY, ADD `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;" );
					}
				}
				/* update database to version 1.4 */
				if ( in_array( $table_name, array( 'denylist', 'allowlist' ) ) ) {
					if ( 0 != $wpdb->query( "SHOW COLUMNS FROM {$table} LIKE 'ip\_%';" ) ) {
						$wpdb->query( "ALTER TABLE {$table}
							DROP `ip_from`,
							DROP `ip_to`,
							DROP `ip_from_int`,
							DROP `ip_to_int`;" );
					}
				}
			}
			/* update DB version */
			$cbxsec_options['plugin_db_version'] = $db_version;
			$update_option = true;
		}

        /* Update options when update plugin */
        if ( ! isset( $cbxsec_options['plugin_option_version'] ) || $cbxsec_options['plugin_option_version'] != $cbxsec_plugin_info["Version"] ) {

            /* delete default messages from wp_options - since v 1.0.6 */
            $cbxsec_messages_defaults = cbxsec_get_default_messages();
            foreach ( $cbxsec_messages_defaults as $key => $value ) {
                if ( isset( $cbxsec_options[ $key . '_default' ] ) )
                    unset( $cbxsec_options[ $key . '_default' ] );
            }
            /* rename hooks from 'log' to 'statistics' - since v 1.0.6 */
            if ( isset( $cbxsec_options[ 'days_to_clear_log' ] ) ) {
                $cbxsec_options[ 'days_to_clear_statistics' ] = $cbxsec_options[ 'days_to_clear_log' ];
                /* delete old 'log' cron hook */
                if ( wp_next_scheduled( 'cbxsec_daily_log_clear' ) ) {
                    wp_clear_scheduled_hook( 'cbxsec_daily_log_clear' );
                    if ( 0 != $cbxsec_options[ 'days_to_clear_statistics' ] ) {
                        $time = time() - fmod( time(), 86400 ) + 86400;
                        wp_schedule_event( $time, 'daily', 'cbxsec_daily_statistics_clear' );
                    }
                }
                unset( $cbxsec_options[ 'days_to_clear_log' ] );
            }

            /* check if old version of htaccess is used */
            if ( ! empty( $cbxsec_options['block_by_htaccess'] ) ) {
                if ( ! function_exists( 'get_plugins' ) )
                    require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
                $all_plugins = get_plugins();
                if (
                    is_plugin_active( 'htaccess/htaccess.php' ) ||
                    ( array_key_exists( 'htaccess/htaccess.php', $all_plugins ) && ! array_key_exists( 'htaccess-pro/htaccess-pro.php', $all_plugins ) )
                ) {
                    global $htccss_plugin_info;
                    if ( ! $htccss_plugin_info )
                        $htccss_plugin_info = get_plugin_data( plugin_dir_path( dirname( __FILE__ ) ) . 'htaccess/htaccess.php' );
                    if ( $htccss_plugin_info["Version"] < '1.6.2' ) {
                        do_action( 'cbxsec_htaccess_hook_for_delete_all' );
                        $cbxsec_options['htaccess_notice']	= sprintf( __( "Wp Cybex Security interaction with Htaccess was turned off since you are using an outdated Htaccess plugin version. If you want to keep using this interaction, please update Htaccess plugin at least to v%s.", 'cybex-security' ), '1.6.2' );
                    }
                }
            }
            /* show pro features */
            $cbxsec_options['hide_premium_options'] = array();
            $cbxsec_options[ 'blocked_message' ] = preg_replace( '|have been blocked till|', 'have been blocked for', $cbxsec_options[ 'blocked_message' ] );
            $options_default = cbxsec_get_options_default();
            $cbxsec_options = array_merge( $options_default, $cbxsec_options );
            $cbxsec_options['plugin_option_version'] = $cbxsec_plugin_info["Version"];
            $update_option = true;
        }

		if ( isset( $update_option ) )
			update_option( 'cbxsec_options', $cbxsec_options );
	}
}

if ( ! function_exists( 'cbxsec_get_options_default' ) ) {
	function cbxsec_get_options_default() {
		global $cbxsec_plugin_info;

		/*email addres that was setting Settings -> General -> E-mail Address */
		$email_address = get_bloginfo( 'admin_email' );

		$options_default = array(
			'plugin_option_version'			        => $cbxsec_plugin_info["Version"],
			'rwl_page_input'						=> 'login',
			'rwl_redirect_func'						=> '404',
			'allowed_retries'				        => '5',
			'days_of_lock'					        => '0',
			'hours_of_lock'					        => '1',
			'minutes_of_lock'				        => '30',
			'days_to_reset'					        => '0',
			'hours_to_reset'				        => '2',
			'minutes_to_reset'				        => '0',
			'allowed_locks'					        => '3',
			'days_to_reset_block'			        => '1',
			'hours_to_reset_block'			        => '0',
			'minutes_to_reset_block'		        => '0',
			'days_to_clear_statistics'		        => '30',
			'options_for_block_message'		        => 'hide',
			'options_for_email_message'		        => 'hide',
			'notify_email'					        => false,
			'mailto'						        => 'admin',
			'email_address'					        => $email_address,
			'failed_message'				        => sprintf( __( '%s attempts left before block.', 'cybex-security' ), '%ATTEMPTS%' ),
			'blocked_message'				        => sprintf( __( 'Too many failed attempts. You have been blocked until %s.', 'cybex-security' ), '%DATE%' ),
			'denylisted_message'			        => __( "You've been added to deny list. Please contact website administrator.", 'cybex-security' ),
			'email_subject'					        => sprintf( __( '%s has been blocked on %s', 'cybex-security' ), '%IP%', '%SITE_NAME%' ),
			'email_subject_denylisted'		        => sprintf( __( '%s has been added to the deny list on %s', 'cybex-security' ), '%IP%', '%SITE_NAME%' ),
			'email_blocked'					        => sprintf( __( 'IP %s has been blocked automatically on %s due to the excess of login attempts on your website %s.', 'cybex-security' ), '%IP%', '%WHEN%', '<a href="%SITE_URL%">%SITE_NAME%</a>' ) . '<br/><br/>' . sprintf( __( 'Using the plugin %s', 'cybex-security' ), '<a href="%PLUGIN_LINK%">Wp Cybex Security by CybexSecurity</a>' ),
			'email_denylisted'				        => sprintf( __( 'IP %s has been added automatically to the deny list on %s due to the excess of locks quantity on your website %s.', 'cybex-security' ), '%IP%', '%WHEN%', '<a href="%SITE_URL%">%SITE_NAME%</a>' ) . '<br/><br/>' . sprintf( __( 'Using the plugin %s', 'cybex-security' ), '<a href="%PLUGIN_LINK%">Wp Cybex Security by CybexSecurity</a>' ),
			'htaccess_notice'				        => '',
			'first_install'					        => strtotime( "now" ),
			'hide_login_form'				        => 0,
			'block_by_htaccess'				        => 0,
			'suggest_feature_banner'		        => 1,
            // CF options
            'contact_form_restrict_sending_emails'  => 0,
			'number_of_letters'                     => 1,
            'letters_days'                          => 0,
			'letters_hours'                         => 0,
			'letters_minutes'                       => 5,
			'letters_seconds'                       => 0
		);
		return $options_default;
	}
}

/**
 * Function to handle action links
 */
if ( ! function_exists( 'cbxsec_plugin_action_links' ) ) {
	function cbxsec_plugin_action_links( $links, $file ) {
		if ( ! is_network_admin() ) {
			/* Static so we don't call plugin_basename on every plugin row. */
			static $this_plugin;
			if ( ! $this_plugin )
				$this_plugin = plugin_basename( __FILE__ );

			if ( $file == $this_plugin ) {
				$settings_link = '<a href="admin.php?page=cybex-security.php">' . __( 'Settings', 'cybex-security' ) . '</a>';
				array_unshift( $links, $settings_link );
			}
		}
		return $links;
	}
}

/**
 * Function to register plugin links
 */
if ( ! function_exists( 'cbxsec_register_plugin_links' ) ) {
	function cbxsec_register_plugin_links( $links, $file ) {
		$base = plugin_basename( __FILE__ );
		if ( $file == $base ) {
			if ( ! is_network_admin() )
				$links[]	=	'<a href="admin.php?page=cybex-security.php">' . __( 'Settings', 'cybex-security' ) . '</a>';
			$links[]	=	'<a href="https://support.cybexsecurity.co.uk/hc/en-us/sections/200538789" target="_blank">' . __( 'FAQ', 'cybex-security' ) . '</a>';
			$links[]	=	'<a href="https://support.cybexsecurity.co.uk">' . __( 'Support', 'cybex-security' ) . '</a>';
		}
		return $links;
	}
}

if ( ! function_exists( 'cbxsec_create_new_item' ) ) {
	function cbxsec_create_new_item() {
		global $wpdb, $cbxsec_options;

		$cbxsec_table = $cbxsec_type_new_item = '';
		if ( isset( $_REQUEST['type'] ) ) {
			$cbxsec_table = 'denylist' == sanitize_text_field($_REQUEST['type']) || 'denylist-email' == sanitize_text_field($_REQUEST['type']) ? 'denylist' : 'allowlist';
			$cbxsec_type_new_item = 'denylist' == sanitize_text_field($_REQUEST['type']) || 'allowlist' == sanitize_text_field($_REQUEST['type']) ? 'ip' : 'email'; 
			$message = $error = '';

	
			if ( isset( $_POST['cbxsec_form_submit'] ) && check_admin_referer( 'cybex-security/cybex-security.php', 'cbxsec_nonce_name' ) ) {
		 		/* save data here */
				if ( 'allowlist' == $cbxsec_table ) {
					$add_ip = isset( $_POST['cbxsec_add_to_allowlist_my_ip'] ) ? sanitize_text_field( trim( $_POST['cbxsec_add_to_allowlist_my_ip_value'] ) ) : false;
					$add_ip = ! $add_ip && isset( $_POST['cbxsec_add_to_allowlist'] ) ? sanitize_text_field( trim( $_POST['cbxsec_add_to_allowlist'] ) ) : $add_ip;
					if ( empty( $add_ip ) ) {
						$error = __( 'ERROR:', 'cybex-security' ) . '&nbsp;' . __( 'You must type IP address', 'cybex-security' );
					} elseif ( filter_var( $add_ip, FILTER_VALIDATE_IP ) ) {
						if ( cbxsec_is_ip_in_table( $add_ip, 'allowlist' ) ) {
							$message .= __( 'Notice:', 'cybex-security' ) . '&nbsp;' . __( 'This IP address has already been added to allow list', 'cybex-security' ) . ' - ' . $add_ip;
						} else {
							if ( cbxsec_is_ip_in_table( $add_ip, 'denylist' ) ) {
								$message .= __( 'Notice:', 'cybex-security' ) . '&nbsp;' . __( 'This IP address is in deny list too, please check this to avoid errors', 'cybex-security' ) . ' - ' . $add_ip;
								$flag = false;
							} else {
								$flag = true;
							}

							cbxsec_remove_from_blocked_list( $add_ip );
							if ( false !== cbxsec_add_ip_to_allowlist( $add_ip ) ) {
								if ( ! empty( $message ) )
									$message .= '<br />';
								$message .= $add_ip . '&nbsp;' . __( 'has been added to allow list', 'cybex-security' );
							} else {
								if ( ! empty( $error ) )
									$error .= '<br />';
								$error .= $add_ip . '&nbsp;' . __( "can't be added to allow list.", 'cybex-security' );
							}
						}
					} else {
						$error .= sprintf( __( 'Wrong format or it does not lie in range %s.', 'cybex-security' ), '0.0.0.0 - 255.255.255.255' ) . '<br />' . $add_ip . '&nbsp;' . __( "can't be added to allow list.", 'cybex-security' );
					}
			 	} 
				else if ( 'denylist' == $cbxsec_table ) {
					/* IP to add to denylist */
					$add_to_blacklist_ip = isset( $_POST['cbxsec_add_to_denylist'] ) ? sanitize_text_field( trim( $_POST['cbxsec_add_to_denylist'] ) ) : '';
					if ( '' == $add_to_blacklist_ip ) {
						$error = __( 'ERROR:', 'cybex-security' ) . '&nbsp;' . __( 'You must type IP address', 'cybex-security' );
					} else {
						if ( preg_match( '/^(25[0-5]|2[0-4][0-9]|[1][0-9]{2}|[1-9][0-9]|[0-9])(\.(25[0-5]|2[0-4][0-9]|[1][0-9]{2}|[1-9][0-9]|[0-9])){3}$/', $add_to_blacklist_ip ) ) {
							if ( cbxsec_is_ip_in_table( $add_to_blacklist_ip, 'denylist' ) ) {
								$message .= __( 'Notice:', 'cybex-security' ) . '&nbsp;' . __( 'This IP address has already been added to  deny list', 'cybex-security' ) . ' - ' . $add_to_blacklist_ip;
							} else {
								if ( cbxsec_is_ip_in_table( $add_to_blacklist_ip, 'allowlist' ) ) {
									$message .= __( 'Notice:', 'cybex-security' ) . '&nbsp;' . __( 'This IP address is in allowlist too, please check this to avoid errors', 'cybex-security' ) . ' - ' . $add_to_blacklist_ip;
								}

								cbxsec_remove_from_blocked_list( $add_to_blacklist_ip );
								if ( false !== cbxsec_add_ip_to_denylist( $add_to_blacklist_ip ) ) {
									if ( ! empty( $message ) )
										$message .= '<br />';
									$message .= $add_to_blacklist_ip . '&nbsp;' . __( 'has been added to deny list', 'cybex-security' );
								} else {
									if ( ! empty( $error ) )
										$error .= '<br />';
									$error .= $add_to_blacklist_ip . '&nbsp;' . __( "can't be added to deny list.", 'cybex-security' );
								}
							}
						} else {
							/* wrong IP format */
							$error .= sprintf( __( 'Wrong format or it does not lie in range %s.', 'cybex-security' ), '0.0.0.0 - 255.255.255.255' ) . '<br />' . $add_to_blacklist_ip . '&nbsp;' . __( "can't be added to deny list.", 'cybex-security' );
						}
					}
				}
	 		}

            $page_title = sprintf(
	__( 'Add New %s : %s' ),
	( 'ip' == $cbxsec_type_new_item ? 'Ip' : 'Email' ),
	( 'denylist' == $cbxsec_table ? __( 'Denylist', 'cybex-security' ) : __( 'Allowlist', 'cybex-security' ) )
);
?>
<div class="wrap">
	<h1 class="wp-heading-inline"><?php echo esc_html( $page_title ); ?></h1>

<?php  
if ( ! empty( $error ) ) { ?>
	<div class="error inline"><p><?php echo esc_html( $error ); ?></p></div>
<?php }
if ( ! empty( $message ) ) { ?>
	<div class="updated inline"><p><?php echo esc_html( $message ); ?></p></div>
<?php }

if ( 'ip' == $cbxsec_type_new_item ) { ?>
	<form id="cbxsec_edit_list_form"
		  action="admin.php?page=cbxsec-create-new-items.php"
		  method="post">
		<input type="text" maxlength="31" name="cbxsec_add_to_<?php echo esc_attr( $cbxsec_table ); ?>"/>
		<?php $my_ip = cbxsec_get_ip();
		if ( 'denylist' != $cbxsec_table ) { ?>
			<br/>
			<label>
				<input type="checkbox" name="cbxsec_add_to_allowlist_my_ip" value="1"/>
				<?php _e( 'My IP', 'cybex-security' ); ?>
				<input type="hidden" name="cbxsec_add_to_allowlist_my_ip_value" value="<?php echo esc_attr( $my_ip ); ?>"/>
			</label>
		<?php } ?>
		<div>
			<span class="cbx_info" style="display: inline-block;margin: 10px 0;">
				<?php _e( "Allowed formats:", 'cybex-security' ); ?><code>192.168.0.1</code>
			</span>
		</div>
		
		<span id="cbxsec_img_loader" style="display: none;position: absolute;"><img src="<?php echo esc_url( plugins_url( 'images/ajax-loader.gif', dirname( __FILE__ ) ) ); ?>" alt=""/></span>
		<input class="button-primary" type="submit" name="cbxsec_form_submit" value="<?php _e( 'Add New', 'cybex-security' ); ?>" />
		<input type="hidden" name="cbxsec_table" value="<?php echo esc_attr( $cbxsec_table ); ?>" />
		<input type="hidden" name="type" value="<?php echo esc_attr( sanitize_text_field( $_REQUEST['type'] ) ); ?>" />
		<?php wp_nonce_field( 'cybex-security/cybex-security.php', 'cbxsec_nonce_name' ); ?>
	</form> <br/>

            <?php }
            cbxsec_display_advertising( sanitize_text_field( $_REQUEST['type'] ) ); ?>
            </div> 
		<?php }
	}
}

/**
 * Function for display limit attempts settings page in the admin area
 */
if ( ! function_exists( 'cbxsec_settings_page' ) ) {
	function cbxsec_settings_page() {
		global $cbxsec_plugin_info; ?>
		<?php if ( 'cybex-security.php' == $_GET['page'] ) { /* Showing settings tab */
			if ( ! class_exists( 'cbx_Settings_Tabs' ) )
				require_once( dirname( __FILE__ ) . '/cbx_menu/class-cbx-settings.php' );
				require_once( dirname( __FILE__ ) . '/includes/class-cbxsec-settings.php' );
				$page = new Cbxsec_Settings_Tabs( plugin_basename( __FILE__ ) );

			if ( method_exists( $page,'add_request_feature' ) )
                $page->add_request_feature(); ?>
			<div class="wrap">
				<h1>Wp Cybex Security <?php if ( is_network_admin() ) echo __( 'Network', 'cybex-security' ) . ' '; _e( 'Settings', 'cybex-security' ); ?></h1>
				<noscript><div class="error below-h2"><p><strong><?php _e( "Please enable JavaScript in Your browser.", 'cybex-security' ); ?></strong></p></div></noscript>
				<?php if ( $page->is_network_options ) { ?>
					<div id="cbxsec_network_notice" class="updated inline cbx_visible"><p><strong><?php _e( "Notice:", 'cybex-security' ); ?></strong> <?php _e( "This option will replace all current settings on separate sites.", 'cybex-security' ); ?></p></div>
				<?php }
				$page->display_content();
		} else { ?>
			<div class="wrap">
			<?php 
			if ( 'cybex-security-log.php' == $_GET['page'] ) { ?>	
				<h1><?php echo sanitize_text_field(  get_admin_page_title() ); ?></h1>
				<div id="cbxsec_statistics" class="cbxsec_list">
					<?php cbxsec_display_advertising( 'log' ); ?>
				</div>
			<?php } elseif ( 'cybex-security-deny-and-allowlist.php' == $_GET['page'] ) {
				require_once( dirname( __FILE__ ) . '/includes/edit-list-form.php' );
				cbxsec_display_list();
			} elseif ( 'cybex-security-blocked.php' == $_GET['page'] ) {
				require_once( dirname( __FILE__ ) . '/includes/edit-list-form.php' );
				cbxsec_display_blocked();
            } else {
				preg_match( '/cybex-security-(.*?).php/', esc_attr( sanitize_text_field ($_GET['page']) ), $page_name ); ?>
				<h1><?php echo sanitize_text_field( get_admin_page_title() ); ?></h1>
				<?php if ( file_exists( dirname( __FILE__ ) . '/includes/' . $page_name[1] . '.php' ) ) {
					require_once( dirname( __FILE__ ) . '/includes/' . $page_name[1] . '.php' );
					call_user_func_array( 'cbxsec_display_' . $page_name[1], array( plugin_basename( __FILE__ ) ) );
				}
			}
			} ?>
		</div>
	<?php }
}

/**
 * Add notises on plugins page
 */
if ( ! function_exists( 'cbxsec_show_notices' ) ) {
	function cbxsec_show_notices() {
		global $cbxsec_options, $hook_suffix, $cbxsec_plugin_info;

		register_cbxsec_settings();

		/* if limit-login-attempts is also installed */
		if ( 'plugins.php' == $hook_suffix && is_plugin_active( 'limit-login-attempts/limit-login-attempts.php' ) ) {
			echo '<div class="error"><p><strong>' . __( 'Notice:', 'cybex-security' ) . '</strong> ' . __( "Limit Login Attempts plugin is activated on your site, as well as Wp Cybex Security plugin. Please note that Wp Cybex Security ensures maximum security when no similar plugins are activated. Using other plugins that limit user's login attempts at the same time may lead to undesirable behaviour on your WP site.", 'cybex-security' ) . '</p></div>';
		}

		if ( 'plugins.php' == $hook_suffix ) {
			cbx_plugin_banner_to_settings( $cbxsec_plugin_info, 'cbxsec_options', 'cybex-security', 'admin.php?page=cybex-security.php' );
		}

		/* Need to update Htaccess */
		/* if option 'htaccess_notice' is not empty and we are on the 'right' page */
		if ( ! empty( $cbxsec_options['htaccess_notice'] ) && ( 'plugins.php' == $hook_suffix || 'update-core.php' == $hook_suffix || ( isset( $_REQUEST['page'] ) && in_array( $_REQUEST['page'], array( 'cybex-security.php', 'htaccess.php' ) ) ) ) ) {
			/* Save data for settings page */
			if ( isset( $_REQUEST['cbxsec_htaccess_notice_submit'] ) && check_admin_referer( plugin_basename( __FILE__ ), 'cbxsec_htaccess_notice_nonce_name' ) ) {
				$cbxsec_options['htaccess_notice'] = '';
				update_option( 'cbxsec_options', $cbxsec_options );
			} else {
				/* get action_slug */
				$action_slug = ( 'plugins.php' == $hook_suffix || 'update-core.php' == $hook_suffix ) ? $hook_suffix : 'admin.php?page=' . $_REQUEST['page']; ?>
				<div class="updated" style="padding: 0; margin: 0; border: none; background: none;">
					<div class="cbx_banner_on_plugin_page">
						<form method="post" action="<?php echo sanitize_text_field( $action_slug );  ?>">
							<div class="text" style="max-width: 100%;">
								<p>
									<strong><?php _e( "ATTENTION!", 'cybex-security' ); ?> </strong>
									<?php echo esc_attr (sanitize_text_field ($cbxsec_options['htaccess_notice']) ); ?>&nbsp;&nbsp;&nbsp;
									<input type="hidden" name="cbxsec_htaccess_notice_submit" value="submit" />
									<input type="submit" class="button-primary" value="<?php _e( 'Read and Understood', 'cybex-security' ); ?>" />
								</p>
								<?php wp_nonce_field( plugin_basename( __FILE__ ), 'cbxsec_htaccess_notice_nonce_name' ); ?>
							</div>
						</form>
					</div>
				</div>
			<?php }
		}

		if ( isset( $_GET['page'] ) && 'cybex-security.php' == $_GET['page'] )
			cbx_plugin_suggest_feature_banner( $cbxsec_plugin_info, 'cbxsec_options', 'cybex-security' );
	}
}

/**
 * Function to get correct IP address
 */
if ( ! function_exists( 'cbxsec_get_ip' ) ) {
	function cbxsec_get_ip() {
		$ip = '';
		$server_vars = [
			'HTTP_X_REAL_IP', 
			'HTTP_CLIENT_IP', 
			'HTTP_X_FORWARDED_FOR', 
			'REMOTE_ADDR'
		];
		
		foreach ($server_vars as $var) {
			if (isset($_SERVER[$var]) && !empty($_SERVER[$var])) {
				$ip_array = explode(',', sanitize_text_field( $_SERVER[$var] ));
				foreach ($ip_array as $ip_candidate) {
					$ip_candidate = trim($ip_candidate);
					if (filter_var($ip_candidate, FILTER_VALIDATE_IP)) {
						$ip = $ip_candidate;
						break 2;
					}
				}
			}
		}
		
		return $ip;
	}
}

/*
* Function for checking is current ip is blocked
*/
if ( ! function_exists( 'cbxsec_is_ip_blocked' ) ) {
	function cbxsec_is_ip_blocked( $ip ) {
		global $wpdb;
		$ip_int		= sprintf( '%u', ip2long( $ip ) );

		$ip_info = $wpdb->get_row( "
            SELECT
				`failed_attempts`,
				`block_quantity`,
				`block_till`
			FROM
				`{$wpdb->prefix}cbxsec_failed_attempts`
			WHERE
				`ip_int` = {$ip_int} AND 
				`block` = '1' AND 
				`block_by` = 'ip'
        ", ARRAY_A );

		return $ip_info;
	}
}

// Function for checking is current ip or email is blocked.
if ( ! function_exists( 'cbxsec_is_blocked' ) ) {
	function cbxsec_is_blocked( $ip = '', $emails = array() ) {
		global $wpdb;

		$ip_int         = sprintf( '%u', ip2long( $ip ) );
		$emails_list    = "'" . implode( "','", $emails ) . "'";

		$info = $wpdb->get_var( "
            SELECT
				COUNT(*)
			FROM
				{$wpdb->prefix}cbxsec_failed_attempts
			WHERE
			    block = '1' AND
				( ip_int = '$ip_int' OR email IN ({$emails_list}) )
        " );

		return $info;
	}
}

if ( ! function_exists( 'cbxsec_screen_options' ) ) {
	function cbxsec_screen_options() {
		$screen = get_current_screen();
		$args = array(
			'id'			=> 'cbxsec',
			'section'		=> '200538789'
		);
		cbx_help_tab( $screen, $args );

		if ( isset( $_GET['action'] ) && 'go_pro' != $_GET['action'] ) {
			$option = 'per_page';
			$args = array(
				'label'		=> __( 'Addresses per page', 'cybex-security' ),
				'default'	=> 20,
				'option'	=> 'addresses_per_page'
			);
			add_screen_option( $option, $args );
		}
	}
}

if ( ! function_exists( 'cbxsec_table_set_option' ) ) {
	function cbxsec_table_set_option( $status, $option, $value ) {
		return $value;
	}
}

/**
 *
 */
if ( ! function_exists( 'cbxsec_remove_from_blocked_list' ) ) {
	function cbxsec_remove_from_blocked_list( $ip ) {
		global $wpdb, $cbxsec_options;
		$wpdb->update(
			"{$wpdb->prefix}cbxsec_failed_attempts",
			array( 'block' => 0 ),
			array( 'ip' => $ip )
		);
		if ( 1 == $cbxsec_options["block_by_htaccess"] ) {
			do_action( 'cbxsec_htaccess_hook_for_reset_block', $ip );
		}
		wp_clear_scheduled_hook( 'cbxsec_event_for_reset_block_quantity', array( $ip ) );
	}
}

/**
 * Function for checking is current ip in current table
 */
if ( ! function_exists( 'cbxsec_is_ip_in_table' ) ) {
	function cbxsec_is_ip_in_table( $ip, $table ) {
		global $wpdb;

		$prefix = $wpdb->prefix . 'cbxsec_';
		/* integer value for our IP */
		$ip_int = sprintf( '%u', ip2long( $ip ) );
		if ( 'allowlist' == $table || 'denylist' == $table ) {
		/* for allowlist and denylist tables needs different method */
			/* if $ip variable is ip mask */
			$is_in = $wpdb->get_var( $wpdb->prepare(
				"SELECT `ip` FROM `" . $prefix . $table . "` WHERE `ip` = %s;", $ip
			) );
		} elseif ( 'failed_attempts' == $table ) {
			$is_in = $wpdb->get_var( $wpdb->prepare( "
                SELECT 
                    ip 
                FROM 
                    {$prefix}failed_attempts
                WHERE 
                    ip_int = %s AND
                    block_by <> 'email' OR
				    block_by IS NULL
			", $ip_int ) );
		} else { /* for other tables */
			$is_in = $wpdb->get_var( $wpdb->prepare(
				'SELECT `ip` FROM ' . $prefix . $table . ' WHERE `ip_int` = %s;', $ip_int
			) );
		}
		return $is_in;
	}
}

// Check is current email or email domain in current table.
if ( ! function_exists( 'cbxsec_is_email_in_table' ) ) {
	function cbxsec_is_email_in_table( $email, $table ) {
		global $wpdb;
		$prefix = $wpdb->prefix . 'cbxsec_';

		$parts  = explode( '@', $email );
		$domain = array_pop( $parts );
		$domain = '@' . $domain;

		$result = $wpdb->get_var( $wpdb->prepare( " 
            SELECT 
                COUNT(*)
            FROM 
                {$prefix}{$table}
            WHERE 
                email IN(%s, %s)
        ", $email, $domain ) );

		return $result;
	}
}

/**
 * Function for adding ip to denylist
 * @param ip - (string) IP
 * @return bool true/false with the result of DB add operation
 */
if ( ! function_exists( 'cbxsec_add_ip_to_denylist' ) ) {
	function cbxsec_add_ip_to_denylist( $ip ) {
		global $wpdb, $cbxsec_options;
		$prefix = $wpdb->prefix . 'cbxsec_';
		/* if IP isn't empty and isn't in denylist already */
		if ( '' != $ip && ! cbxsec_is_ip_in_table( $ip, 'denylist' ) ) {
			/* if insert single ip address */
			if ( preg_match( '/^(25[0-5]|2[0-4][0-9]|[1][0-9]{2}|[1-9][0-9]|[0-9])(\.(25[0-5]|2[0-4][0-9]|[1][0-9]{2}|[1-9][0-9]|[0-9])){3}$/', $ip ) ) {
				/* add a new row to db */
				$result = $wpdb->insert(
					$prefix . 'denylist',
					array(
						'ip'			=> $ip,
						'add_time'		=> date( 'Y-m-d H:i:s', current_time( 'timestamp' ) )
					),
					'%s' /* all '%s' because max value in '%d' is 2147483647 */
				);
				if ( 1 == $cbxsec_options["block_by_htaccess"] ) {
					do_action( 'cbxsec_htaccess_hook_for_block', $ip );
				}
				return $result;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
}

/**
 * Function for adding ip to allowlist
 * @param ip - (string) IP
 * @return bool true/false with the result of DB add operation
 */
if ( ! function_exists( 'cbxsec_add_ip_to_allowlist' ) ) {
	function cbxsec_add_ip_to_allowlist( $ip ) {
		global $wpdb, $cbxsec_options;
		$prefix = $wpdb->prefix . 'cbxsec_';
		/* if IP isn't empty and isn't in allowlist already */
		if ( '' != $ip && ! cbxsec_is_ip_in_table( $ip, 'allowlist' ) ) {
			/* if insert single ip address */
			if ( preg_match( '/^(25[0-5]|2[0-4][0-9]|[1][0-9]{2}|[1-9][0-9]|[0-9])(\.(25[0-5]|2[0-4][0-9]|[1][0-9]{2}|[1-9][0-9]|[0-9])){3}$/', $ip ) ) {
				/* add a new row to db */
				$result = $wpdb->insert(
					$prefix . 'allowlist',
					array(
						'ip'			=> $ip,
						'add_time'		=> date( 'Y-m-d H:i:s', current_time( 'timestamp' ) )
					),
					'%s' /* all '%s' because max value in '%d' is 2147483647 */
				);
				if ( 1 == $cbxsec_options["block_by_htaccess"] ) {
					do_action( 'cbxsec_htaccess_hook_for_add_to_whitelist', $ip );
				}
				return $result;
			} else {
				return false;
			}
		}

		return false;
	}
}

/**
 * Function to clear all statistics
 */
if ( ! function_exists( 'cbxsec_clear_statistics_completely' ) ) {
	function cbxsec_clear_statistics_completely() {
		global $wpdb;

		$result = $wpdb->query( "DELETE FROM `{$wpdb->prefix}cbxsec_all_failed_attempts`" );

		return ( $wpdb->last_error ) ? false : $result;
	}
}

/**
 * Function to clear single statistics entry
 */
if ( ! function_exists( 'cbxsec_clear_statistics' ) ) {
	function cbxsec_clear_statistics( $id ) {
		global $wpdb;
		$result = $wpdb->delete(
			$wpdb->prefix . 'cbxsec_all_failed_attempts',
			array( 'id' => $id ),
			array( '%s' )
		);
		return $wpdb->last_error ? false : $result;
	}
}

/**
 * Function to cron clear statistics daily
 */
if ( ! function_exists( 'cbxsec_clear_statistics_daily' ) ) {
	function cbxsec_clear_statistics_daily() {
		global $wpdb, $cbxsec_options;
		if ( empty( $cbxsec_options ) ) {
			$cbxsec_options = get_option( 'cbxsec_options' );
		}
		$time = date( 'Y-m-d H:i:s', time() - 86400 * $cbxsec_options['days_to_clear_statistics'] );
		$wpdb->query( "DELETE FROM `{$wpdb->prefix}cbxsec_all_failed_attempts` WHERE `last_failed_attempt` <= '{$time}'" );
	}
}

/**
 * Function to reset failed attempts
 */
if ( ! function_exists( 'cbxsec_reset_failed_attempts' ) ) {
	function cbxsec_reset_failed_attempts( $ip ) {
		global $wpdb;

		if ( ! empty( $ip ) ) {
			$array = array( 'ip_int' => sprintf( '%u', ip2long( $ip ) ) );
            $wpdb->update(
                $prefix = $wpdb->prefix . 'cbxsec_failed_attempts',
                array( 'failed_attempts' => 0 ),
                $array,
                array( '%d' ),
                array( '%s' )
            );
		}
	}
}

/**
 * Function to reset block
 */
if ( ! function_exists( 'cbxsec_reset_block' ) ) {
	function cbxsec_reset_block() {
		global $wpdb, $cbxsec_options;
		$reset_ip_db = array();
		$reset_ip_in_htaccess = array();

		if ( empty( $cbxsec_options ) ) {
			$cbxsec_options = get_option( 'cbxsec_options' );
		}

		$unlocking_timestamp	= date( 'Y-m-d H:i:s', ( current_time( 'timestamp' ) + 60 ) );
		$current_timestamp		= date( 'Y-m-d H:i:s', ( current_time( 'timestamp' ) ) );
		$blockeds				= $wpdb->get_results( "SELECT `ip_int`, `ip` FROM `{$wpdb->prefix}cbxsec_failed_attempts` WHERE `block_till` <= '{$unlocking_timestamp}' and `block` = '1'", ARRAY_A );

		if ( ! empty( $blockeds ) ) {
			foreach ( $blockeds as $blocked ) {
				$reset_ip_in_htaccess[] = $blocked['ip'];
				$reset_ip_db[] = "'{$blocked['ip_int']}'";
			}
		}
		$reset_ip_db = implode( ',', $reset_ip_db );

		if ( '' != $reset_ip_db ) {
			$wpdb->query( "UPDATE `{$wpdb->prefix}cbxsec_failed_attempts` SET `block` = '0', `block_till`=null, `block_by`=null WHERE `ip_int` IN ({$reset_ip_db})" );
		}

		$next_timestamp = $wpdb->get_row( "SELECT `block_till` FROM `{$wpdb->prefix}cbxsec_failed_attempts` WHERE `block_till` > '{$current_timestamp}' ORDER BY `block_till` LIMIT 1", ARRAY_A );
		if ( ! empty( $next_timestamp ) ) {
			$next_timestamp_unix_time = strtotime( $next_timestamp['block_till'] );
			wp_schedule_single_event( $next_timestamp_unix_time, 'cbxsec_event_for_reset_block' );
		}

		/* hook for deblocking by Htaccess */
		if ( 1 == $cbxsec_options["block_by_htaccess"] && ! empty( $reset_ip_in_htaccess ) ) {
			do_action( 'cbxsec_htaccess_hook_for_reset_block', $reset_ip_in_htaccess );
		}
	}
}
/**
 * Function to reset number of blocks
 */
if ( ! function_exists( 'cbxsec_reset_block_quantity' ) ) {
	function cbxsec_reset_block_quantity( $ip, $email = '', $priority = '' ) {
		global $wpdb;

		if ( 'ip' == $priority ) {
			$array = array( 'ip_int' => sprintf( '%u', ip2long( $ip ) ) );
		} else {
			$array = array( 'email' => $email );
		}

		$wpdb->update(
			$wpdb->prefix . 'cbxsec_failed_attempts',
			array( 'block_quantity' => 0 ),
			$array,
			array( '%d' ),
			array( '%s' )
		);
	}
}

/**
 * Filter to transfer message in html format
 */
if ( ! function_exists( 'cbxsec_set_html_content_type' ) ) {
	function cbxsec_set_html_content_type() {
		return 'text/html';
	}
}

/**
 * Checking for right captcha in login form
 */
if ( ! function_exists( 'cbxsec_login_form_captcha_checking' ) ) {
	function cbxsec_login_form_captcha_checking() {
		global $cbxsec_options;
		if ( '' == $cbxsec_options ) {
			$cbxsec_options = get_option( 'cbxsec_options' );
		}
		if ( is_multisite() ) {
			$active_plugins = ( array ) array_keys( get_site_option( 'active_sitewide_plugins', array() ) );
			$active_plugins = array_merge( $active_plugins , get_option( 'active_plugins' ) );
		} else {
			$active_plugins = get_option( 'active_plugins' );
		}
		if ( function_exists( 'cptch_cbxsec_interaction' ) && 0 < count( preg_grep( '/captcha\/captcha.php/', $active_plugins ) ) && ! isset( $cbxsec_options['login_form_captcha_check'] ) && ! cptch_cbxsec_interaction() ) {
			/* return false if only Captcha is instaled, is active, is exist in login form, user set consider captcha and captcha is invalid */
			return false;
		} elseif ( function_exists( 'cptchpls_cbxsec_interaction' ) && 0 < count( preg_grep( '/captcha-plus\/captcha-plus.php/', $active_plugins ) ) && ! isset( $cbxsec_options['login_form_captcha_check'] ) && ! cptchpls_cbxsec_interaction() ) {
			/* return false if only Captcha Plus is instaled, is active, is exist in login form, user set not consider captcha and captcha is invalid */
			return false;
		} elseif ( function_exists( 'cptchpr_cbxsec_interaction' ) && 0 < count( preg_grep( '/captcha-pro\/captcha_pro.php/', $active_plugins ) ) && ! isset( $cbxsec_options['login_form_captcha_check'] ) && ! cptchpr_cbxsec_interaction() ) {
			/* return false if only Captcha is instaled, is active, is exist in login form, user set consider captcha and captcha is invalid */
			return false;
		}
		return true;
	}
}

/**
 * Check plugin`s "block/denylist" options
 * @param		array		$option	plugin`s options
 * @return		mixed		the minimum period of time necessary for the user`s IP to be added to the denylist or false
 */
if ( ! function_exists( 'cbxsec_check_block_options' ) ) {
	function cbxsec_check_block_options( $option ) {
		/*
		 * Over what period of time the user can be blocked
		 */
		$time_to_block =
			$option['days_of_lock'] * 86400 +
			$option['hours_of_lock'] * 3600 +
			$option['minutes_of_lock'] * 60 +
			$option['allowed_retries'] * 60;

		/*
		 * The minimum period of time necessary for the user`s IP to be added to the denylist
		 */
		$time_for_blacklist = intval(
				(
					$option['days_to_reset_block'] * 86400 +
					$option['hours_to_reset_block'] * 3600 +
					$option['minutes_to_reset_block'] * 60
				) /
				$option['allowed_locks']
			);

		if ( $time_to_block > $time_for_blacklist ) {
			$days	= intval( ( $time_to_block ) / 86400 );
			$string	= ( 0 < $days ? '&nbsp;' . $days . '&nbsp;' . _n( 'day', 'days', $days, 'cybex-security' ) : '' );
			$sum	= $days * 86400;

			$hours	= intval( ( $time_to_block - $sum ) / 3600 );
			$string	.= ( 0 < $hours ? '&nbsp;' . $hours . '&nbsp;' . _n( 'hour', 'hours', $hours, 'cybex-security' ) : '' );
			$sum	+= $hours * 3600;

			$minutes = intval( ( $time_to_block - $sum ) / 60 ) + 1;
			$string .= ( 0 < $minutes ? '&nbsp;' . $minutes . '&nbsp;' . _n( 'minute', 'minutes', $minutes, 'cybex-security' ) : '' );
			return $string;
		}

		return false;
	}
}

/**
 * Function (ajax) to restore default message
 * @return void
 */
if ( ! function_exists( 'cbxsec_restore_default_message' ) ) {
	function cbxsec_restore_default_message() {
		check_ajax_referer( 'cbxsec_ajax_nonce_value', 'cbxsec_nonce' );
		if ( isset( $_POST['message_option_name'] ) &&
			( 'error' == $_POST['message_option_name'] || 'email' == $_POST['message_option_name'] ) ) {
			/* get the list of default messages */
			if ( ! function_exists( 'cbxsec_get_default_messages' ) )
				require_once( dirname( __FILE__ ) . '/includes/back-end-functions.php' );
			$default_messages = cbxsec_get_default_messages();

			if ( 'email' == $_POST['message_option_name'] ) {
				unset( $default_messages['failed_message'], $default_messages['blocked_message'], $default_messages['denylisted_message'] );
				$output_message = __( 'Email notifications have been restored to default.', 'cybex-security' );
			} else {
				unset( $default_messages['email_subject'], $default_messages['email_subject_denylisted'], $default_messages['email_blocked'], $default_messages['email_denylisted'] );
				$output_message = __( 'Messages have been restored to default.', 'cybex-security' );
			}
			/* set notice message, check what was changed - subject or body of the message */
			$output_message = '<div class="updated fade inline cbxsec_message cbxsec-restore-default-message"><p><strong>' . __( 'Notice', 'cybex-security' ) . ':</strong> ' . $output_message . '</p><p><strong>' . __( 'Changes are not saved.', 'cybex-security' ) . '</strong></p></div>';
			/* send default text of subject/body into ajax array */
			$restored_data = array(
				'restored_messages' => $default_messages,
				'admin_notice_message' => $output_message,
			);
			echo json_encode( $restored_data );
		}
		die();
	}
}

/**
 * Delete plugin for network
 */
if ( ! function_exists( 'cbxsec_plugin_uninstall' ) ) {
	function cbxsec_plugin_uninstall() {
		global $wpdb;
		if ( ! function_exists( 'get_plugins' ) )
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		$all_plugins = get_plugins();
		$pro_version_exist = array_key_exists( 'cybex-security-pro/cybex-security-pro.php', $all_plugins );
		if ( is_multisite() ) {
			$old_blog = $wpdb->blogid;
			/* Get all blog ids */
			$blogids = $wpdb->get_col( "SELECT `blog_id` FROM {$wpdb->blogs};" );
			foreach ( $blogids as $blog_id ) {
				switch_to_blog( $blog_id );
				cbxsec_delete_options( $pro_version_exist );
			}
			switch_to_blog( $old_blog );
		} else {
			cbxsec_delete_options( $pro_version_exist );
		}
		if ( is_multisite() ) {
			$blogs = $wpdb->get_col( "SELECT blog_id FROM {$wpdb->blogs}" );

			if ( $blogs ) {
				foreach ( $blogs as $blog ) {
					switch_to_blog( $blog );
					delete_option( 'rwl_page' );
				}

				restore_current_blog();
			}

			delete_site_option( 'rwl_page' );
		} else {
			delete_option( 'rwl_page' );
		}

		require_once( dirname( __FILE__ ) . '/cbx_menu/cbx_include.php' );
		cbx_include_init( plugin_basename( __FILE__ ) );
		cbx_delete_plugin( plugin_basename( __FILE__ ) );
	}
}

/**
 * Delete plugin blog
 */
if ( ! function_exists( 'cbxsec_delete_blog' ) ) {
	function cbxsec_delete_blog( $blog_id ) {
		global $wpdb;
		if ( is_plugin_active_for_network( 'cybex-security/cybex-security.php' ) ) {
			$old_blog = $wpdb->blogid;
			switch_to_blog( $blog_id );
			cbxsec_delete_options( false );
			switch_to_blog( $old_blog );
		}
	}
}

/**
 * Function for deleting options when uninstal current plugin
 */
if ( ! function_exists( 'cbxsec_delete_options' ) ) {
	function cbxsec_delete_options( $pro_version_exist = false ) {
		global $wpdb;
		$prefix = $wpdb->prefix . 'cbxsec_';
		/* drop tables */
		if ( ! $pro_version_exist ) {
			/* delete options */
			/* drop all tables */
			$sql = "DROP TABLE IF EXISTS `{$prefix}all_failed_attempts`, `{$prefix}failed_attempts`, `{$prefix}email_list`, `{$prefix}denylist`, `{$prefix}denylist_email`, `{$prefix}allowlist`;";
			/* remove IPs from .htaccess */
			do_action( 'cbxsec_htaccess_hook_for_delete_all' );
			delete_option( 'cbxsec_options' );
		} else {
			/* drop FREE tables only */
			$sql = "DROP TABLE IF EXISTS `{$prefix}all_failed_attempts`;";
		}
		$wpdb->query( $sql );
		/* clear hook to delete old statistics entries */
		wp_clear_scheduled_hook( 'cbxsec_daily_statistics_clear' );
	}
}

if ( ! function_exists( 'lmtttmpt_deactivate' ) ) {
    function lmtttmpt_deactivate() {
        $cptch_options = get_option( 'cptch_options' );
        if ( ! empty( $cptch_options )) {
            $cptch_options['use_limit_attempts_allowlist'] = 0;
            update_option('cptch_options' , $cptch_options );
        }
    }
}

/* installation */
register_activation_hook( __FILE__, 'cbxsec_plugin_activate' );
add_action( 'wpmu_new_blog', 'cbxsec_new_blog', 10, 6 );
add_action( 'delete_blog', 'cbxsec_delete_blog', 10 );
add_action( 'plugins_loaded', 'cbxsec_plugins_loaded' );
/* register */
add_action( 'admin_menu', 'cbxsec_add_admin_menu' );
add_action( 'init', 'cbxsec_plugin_init' );
add_action( 'admin_init', 'cbxsec_plugin_admin_init' );
add_action( 'admin_head', 'cbxsec_admin_head' );
add_action( 'admin_enqueue_scripts', 'cbxsec_enqueue_scripts' );
add_filter( 'set-screen-option', 'cbxsec_table_set_option', 10, 3 );
add_filter( 'plugin_action_links', 'cbxsec_plugin_action_links', 10, 2 );
add_filter( 'plugin_row_meta', 'cbxsec_register_plugin_links', 10, 2 );

/* reset blocks */
add_action( 'cbxsec_event_for_reset_failed_attempts', 'cbxsec_reset_failed_attempts' );
add_action( 'cbxsec_event_for_reset_block', 'cbxsec_reset_block' );
add_action( 'cbxsec_event_for_reset_block_quantity', 'cbxsec_reset_block_quantity' );
add_action( 'cbxsec_daily_statistics_clear', 'cbxsec_clear_statistics_daily' );
add_action( 'admin_notices', 'cbxsec_show_notices' );
/* ajax function */
add_action( 'wp_ajax_cbxsec_restore_default_message', 'cbxsec_restore_default_message' );

