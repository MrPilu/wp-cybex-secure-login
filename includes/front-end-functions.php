<?php
/**
 * Functions to get or check necessary user`s data and
 * handle errors, which occured during an incorrect data entering
 * @package Wp Cybex Security
 * @since 
 */

/**
 * Filter for authenticate access
 * @param       mixed           $user         an instance of class WP_Error/WP_User or null
 * @param       string          $username     value of "Username"-field
 * @param       string          $password     value of "Password"-field
 * @return        mixed         $user         an instance of class WP_Error/WP_User or null
 */
if ( ! function_exists( 'cbxsec_authenticate' ) ) {
	function cbxsec_authenticate( $user, $username, $password ) {
		global $cbxsec_options, $cbxsec_hide_form;
		$cbxsec_hide_form = false;

		/* get plugin`s options */
		if ( empty( $cbxsec_options ) ) {
			register_cbxsec_settings();
		}

		if ( isset( $_POST['wp-submit'] ) ) {
			$user = cbxsec_check_ip( $user );

			if ( ! is_wp_error( $user ) )
				return $user;

			$error_codes = $user->get_error_codes();
			$is_blocked  = is_array( $error_codes ) && array_intersect( $error_codes, array( 'cbxsec_denylisted', 'cbxsec_blocked' ) );

			if ( ! $is_blocked )
				$user = cbxsec_handle_error( $user );

			$error_codes = is_wp_error( $user ) ? $user->get_error_codes() : false;
			$cbxsec_hide_form = is_array( $error_codes ) && array_intersect( $error_codes, array( 'cbxsec_denylisted', 'cbxsec_blocked' ) ) && 1 == $cbxsec_options['hide_login_form'];
		}

		return $user;
	}
}

/**
 * Check user`s IP during form data checking
 * @param    mixed    $user    object, an instance of classes WP_Error or WP_User or true
 * @return   mixed    $user    object, an instance of classes WP_Error or WP_User or true
 */
if ( ! function_exists( 'cbxsec_form_check' ) ) {
	function cbxsec_form_check( $user = true ) {
		global $cbxsec_options, $cbxsec_hide_form;
		$cbxsec_hide_form = false;

			/* get plugin`s options */
		if ( empty( $cbxsec_options ) ) {
			register_cbxsec_settings();
		}

		if ( 0 == $cbxsec_options['hide_login_form'] )
			return $user;

		if ( is_wp_error( $user ) ) {
			$error_codes  = $user->get_error_codes();
			$ignore_codes = array( 'cbxsec_denylisted', 'cbxsec_blocked' );
			$check_ip     = ! is_array( $error_codes ) || ! array_intersect( $error_codes, $ignore_codes ) ? true : false;
		} else {
			$check_ip = false;
		}

		if ( $check_ip )
			$user = cbxsec_check_ip( $user );

		if ( is_wp_error( $user ) ) {
			$error_codes = $user->get_error_codes();
			$hide_form   = is_array( $error_codes ) && array_intersect( $error_codes, array( 'cbxsec_denylisted', 'cbxsec_blocked' ) ) ? true : false;
			if ( in_array( 'cbxsec_error', (array)$error_codes ) && $hide_form )
				$user->remove( 'cbxsec_error' );
			$cbxsec_hide_form = $hide_form ? true : false;
		}
		return $user;
	}
}

/**
 * Check whether user`s IP in denylist or it is blocked
 * @param      mixed       an instance of class WP_Error/WP_User or null
 * @return     mixed       an instance of class WP_Error/WP_User or null
 */
if ( ! function_exists( 'cbxsec_check_ip' ) ) {
	function cbxsec_check_ip( $user = true ) {
		global $cbxsec_options;

		/* get plugin`s options */
		if ( empty( $cbxsec_options ) ) {
			register_cbxsec_settings();
		}

		/* get user`s IP */
		$ip = cbxsec_get_ip();

		/* check if ip in denylist */
		if ( cbxsec_is_ip_in_table( $ip, 'denylist' ) ) {
			/* create new WP_ERROR object to skip brute force */
			$user  = new WP_Error();
			$error = str_replace( '%MAIL%', $cbxsec_options['email_address'], $cbxsec_options['denylisted_message'] );
			$error = wp_specialchars_decode( $error, ENT_COMPAT );
			$user->add( 'cbxsec_denylisted', $error );
			return $user;
		}

		/* check if ip in blocked list */
		$ip_info = cbxsec_is_ip_blocked( $ip );
		if ( $ip_info && is_array( $ip_info ) && ! empty( $ip_info ) ) {
			$block_till =
					! isset( $ip_info['block_till'] ) ||
					is_null( $ip_info['block_till'] )
				?
					date( 'Y-m-d H:i:s', current_time( 'timestamp' ) + $cbxsec_options['minutes_of_lock'] * 60 + $cbxsec_options['hours_of_lock'] * 3600 + $cbxsec_options['days_of_lock'] * 86400 )
				:
					$ip_info['block_till'];
			if ( ! is_wp_error( $user ) )
				$user = new WP_Error();
				$error = str_replace(
					array( '%DATE%', '%MAIL%' ),
					array( cbxsec_block_time( $block_till ), $cbxsec_options['email_address'] ),
					$cbxsec_options['blocked_message']
				);
			$error = wp_specialchars_decode( $error, ENT_COMPAT );
			$user->add( 'cbxsec_blocked', $error );
		}
		return $user;
	}
}

/**
 * Add info about user`s IP in to database
 * @param     mixed       an instance of class WP_Error/WP_User or string
 * @return    mixed       an instance of class WP_Error/WP_User or string
 */
if ( ! function_exists( "cbxsec_handle_error" ) ) {
	function cbxsec_handle_error( $user = true ) {
		global $wpdb, $cbxsec_options;

		/* get plugin`s options */
		if ( empty( $cbxsec_options ) ) {
			register_cbxsec_settings();
		}

		/* get necessary data */
		$ip						= cbxsec_get_ip();
		$ip_int					= sprintf( '%u', ip2long( $ip ) );
		$prefix					= "{$wpdb->prefix}cbxsec_";
		$current_timestamp		= current_time( 'mysql' );
		$timestamp				= time();
		$attempts_reset_time	= $timestamp + $cbxsec_options['minutes_to_reset'] * 60 + $cbxsec_options['hours_to_reset'] * 3600 + $cbxsec_options['days_to_reset'] * 86400;
		$block_till_time		= $timestamp + $cbxsec_options['minutes_of_lock'] * 60 + $cbxsec_options['hours_of_lock'] * 3600 + $cbxsec_options['days_of_lock'] * 86400;
		$blocks_reset_time		= $block_till_time + $cbxsec_options['minutes_to_reset_block'] * 60 + $cbxsec_options['hours_to_reset_block'] * 3600 + $cbxsec_options['days_to_reset_block'] * 86400;

		$wp_error		=  is_wp_error( $user );
		$error			= $error_code = '';

		/* get full info about IP */
		$ip_info = $wpdb->get_results(
			"SELECT
				`{$prefix}failed_attempts`.`block` AS `blocked`,
				`{$prefix}failed_attempts`.`failed_attempts`,
				`{$prefix}failed_attempts`.`block_quantity`,
				`{$prefix}failed_attempts`.`block_till`,
				`{$prefix}failed_attempts`.`block_by`,
				`{$prefix}all_failed_attempts`.`failed_attempts` AS `stat_attempts_number`,
				`{$prefix}all_failed_attempts`.`block_quantity` AS `stat_block_quantity`,
				`{$prefix}denylist`.`id` AS `in_denylist`,
				`{$prefix}allowlist`.`id` AS `in_allowlist`
			FROM `{$prefix}failed_attempts`
			LEFT JOIN `{$prefix}all_failed_attempts`
				ON `{$prefix}all_failed_attempts`.`ip_int`=`{$prefix}failed_attempts`.`ip_int`
			LEFT JOIN `{$prefix}denylist`
				ON `{$prefix}denylist`.`ip`='{$ip}'
			LEFT JOIN `{$prefix}allowlist`
				ON `{$prefix}allowlist`.`ip`='{$ip}'
			WHERE 
				`{$prefix}failed_attempts`.`ip_int`={$ip_int} AND
				`{$prefix}failed_attempts`.`block_by` = 'ip' OR 
				`{$prefix}failed_attempts`.`block_by` IS NULL
			LIMIT 1;"
		);

		$block_by = isset( $ip_info[0]->block_by ) ? $ip_info[0]->block_by : null;

		/* if IP is in denylist */
		if ( isset( $ip_info[0]->in_blacklist ) && ! is_null( $ip_info[0]->in_blacklist ) ) {
			$error = str_replace( '%MAIL%', $cbxsec_options['email_address'], $cbxsec_options['denylisted_message'] );
			$error = wp_specialchars_decode( $error, ENT_COMPAT );
			/* create new WP_ERROR object to skip brute force */
			$user = new WP_Error();
			$user->add( 'cbxsec_denylisted', $error );
			return $user;
		}

		/* if IP is blocked */
		if ( isset( $ip_info[0]->blocked ) && '1' == $ip_info[0]->blocked ) {
			$block_till =
				isset( $ip_info[0]->block_till ) && ! empty( $ip_info[0]->block_till )
					?
					$ip_info[0]->block_till
					:
					date( 'Y-m-d H:i:s', $block_till_time );
			$error = str_replace( array( '%DATE%', '%MAIL%' ), array( cbxsec_block_time( $block_till ), $cbxsec_options['email_address'] ), $cbxsec_options['blocked_message'] );
			$error = wp_specialchars_decode( $error, ENT_COMPAT );
			if ( ! $wp_error )
				$user = new WP_Error;
			$user->add( 'cbxsec_blocked', $error );
			return $user;
		}

		/* get some additional data */
		$ip_in_whitelist = isset( $ip_info[0]->in_whitelist ) && ! is_null( $ip_info[0]->in_whitelist ) ? true : false;
		$error_codes     = $user->get_error_codes();
		$array_intersect = is_array( $error_codes ) ? array_intersect( $error_codes, array( 'cptchpr_error', 'cptchpls_error', 'cptch_error' ) ) : array();
		if ( $ip_in_whitelist || ( ! empty( $array_intersect ) && ! isset( $cbxsec_options['login_form_captcha_check'] ) ) ) {
			/* event: failed_attempt */

			/*
			* skip errors handling for allowlisted ip or
			* for cbx CAPTCHA`s errors if corresponding option is disabled
			*/
		} else {

			$failed_attempts_number	= ( isset( $ip_info[0]->failed_attempts ) && ! is_null( $ip_info[0]->failed_attempts ) ) ? $ip_info[0]->failed_attempts : 0;
			$block_quantity			= ( isset( $ip_info[0]->block_quantity ) && ! is_null( $ip_info[0]->block_quantity ) ) ? $ip_info[0]->block_quantity : 0;

			$failed_attempts_number += 1;

			/* reset countdown to clear failed attempts number */
			wp_clear_scheduled_hook( 'cbxsec_event_for_reset_failed_attempts', array( $ip ) );

			/*
			 * if failed attempts number exceeds max allowed_retries value
			 * IP will be blocked
			 */
			if ( $failed_attempts_number < $cbxsec_options['allowed_retries'] ) {
				/* event: failed_attempt */
				wp_schedule_single_event( $attempts_reset_time, 'cbxsec_event_for_reset_failed_attempts', array( $ip ) );

				$block		= 0;
				$block_till	= null;
				$block_by = null;

				/* getting an error message */
				$error_code	= 'cbxsec_failed_attempts';
				$error		= str_replace( '%ATTEMPTS%', max( $cbxsec_options['allowed_retries'] - $failed_attempts_number, 0 ), $cbxsec_options['failed_message'] );
				$error		= wp_specialchars_decode( $error, ENT_COMPAT );

			} else {
				$block_quantity += 1;
				$failed_attempts_number = 0;
				/* reset countdown to clear blocks number */
				wp_clear_scheduled_hook( 'cbxsec_event_for_reset_block_quantity', array( $ip ) );

				if ( $block_quantity < $cbxsec_options['allowed_locks'] ) {
					/* event: auto_blocked */
					wp_schedule_single_event( $blocks_reset_time, 'cbxsec_event_for_reset_block_quantity', array( $ip ) );

					$block		= 1;
					$block_till	= date( 'Y-m-d H:i:s', $block_till_time );
					$block_by = 'ip';

					/* getting an error message */
					$error_code	= 'cbxsec_blocked';
					$error		= str_replace( array( '%DATE%', '%MAIL%' ), array( cbxsec_block_time( $block_till ), $cbxsec_options['email_address'] ), $cbxsec_options['blocked_message'] );
					$error		= wp_specialchars_decode( $error, ENT_COMPAT );

					/* clearing 'cbxsec_event_for_reset_block' event if current timestamp less than time of next scheduled event and resetting event with current timestamp */
					$next_timestamp = $wpdb->get_row( "SELECT `block_till` FROM `{$prefix}failed_attempts` WHERE `block_till` > '{$current_timestamp}' ORDER BY `block_till`", ARRAY_A );
					if ( ! empty( $next_timestamp ) ) {
						$next_timestamp_unix_time = strtotime( $next_timestamp['block_till'] );
						if ( $block_till_time < $next_timestamp_unix_time ) {
							if ( wp_next_scheduled( 'cbxsec_event_for_reset_block' ) )
								wp_clear_scheduled_hook( 'cbxsec_event_for_reset_block' );
						}
					}
					/* countdown to reset */
					if ( ! wp_next_scheduled( 'cbxsec_event_for_reset_block' ) )
						wp_schedule_single_event( $block_till_time, 'cbxsec_event_for_reset_block' );

					if ( 1 == $cbxsec_options["block_by_htaccess"] ) {
						do_action( 'cbxsec_htaccess_hook_for_block', $ip );
					}

					/* send e-mail to admin */
					if ( $cbxsec_options['notify_email'] ) {
						cbxsec_send_email(
							$cbxsec_options['email_address'],
							$cbxsec_options['email_subject'],
							$cbxsec_options['email_blocked'],
							$ip
						);
					}
					/* create new WP_ERROR object to skip brute force */
					if ( $wp_error )
						$user = new WP_Error();

				} else {
					/* event: auto_denylisted */
					/* getting an error message */
					$error_code = 'cbxsec_denylisted';
					$error      = str_replace( '%MAIL%', $cbxsec_options['email_address'], $cbxsec_options['denylisted_message'] );
					$error      = wp_specialchars_decode( $error, ENT_COMPAT );

					$block			= 0;
					$block_quantity	= 0;
					$block_till		= null;

					/*
					 * interaction with Htaccess plugin for blocking
					 * hook for blocking by Htaccess
					 */
					if ( 1 == $cbxsec_options["block_by_htaccess"] ) {
						do_action( 'cbxsec_htaccess_hook_for_block', $ip );
					}
					/*
					 * update denylist
					 */
					$wpdb->insert(
						"{$prefix}denylist",
						array(
							'ip' 			=> $ip,
							'add_time' 		=> date( 'Y-m-d H:i:s', $timestamp )
						)
					);
					/* send e-mail to admin */
					if ( $cbxsec_options['notify_email'] ) {
						cbxsec_send_email(
							$cbxsec_options['email_address'],
							$cbxsec_options['email_subject_denylisted'],
							$cbxsec_options['email_denylisted'],
							$ip
						);
					}
					/* create new WP_ERROR object to skip brute force */
					if ( $wp_error )
						$user = new WP_Error();
				}
			}

			/*
			 * update failed attempt info
			 */
			$is_ip_in_table = cbxsec_is_ip_in_table( $ip, 'failed_attempts' );
			if ( !! $is_ip_in_table ) {
				$wpdb->update(
					"{$prefix}failed_attempts",
					array(
						'failed_attempts'	=> $failed_attempts_number,
						'block'				=> $block,
						'block_quantity'	=> $block_quantity,
						'block_till'		=> $block_till,
						'block_by' 		  	=> $block_by
					),
					array( 'ip_int' => $ip_int, 'block_by' => null )
				);
			} else {
				$wpdb->insert(
					"{$prefix}failed_attempts",
					array(
						'ip'				=> $ip,
						'ip_int'			=> $ip_int,
						'failed_attempts'	=> $failed_attempts_number,
						'block'				=> $block,
						'block_quantity'	=> $block_quantity,
						'block_till'		=> $block_till,
						'block_by' 		  	=> $block_by
					)
				);
			}
		}

		/*
		 * update statistics
		 */
		if ( ( ! isset( $ip_info[0]->stat_attempts_number ) ) || is_null( $ip_info[0]->stat_attempts_number ) ) {
			$block_number = ! $ip_in_whitelist && 1 == $cbxsec_options['allowed_retries'] ? 1 : 0;
			$wpdb->insert(
				"{$prefix}all_failed_attempts",
				array(
					'ip' 					=> $ip,
					'ip_int'				=> $ip_int,
					'failed_attempts'		=> 1,
					'block_quantity'		=> $block_number,
					'last_failed_attempt'	=> date( 'Y-m-d H:i:s', $timestamp )
				)
			);
		} else {
			$attempts_number = $ip_info[0]->stat_attempts_number + 1;
			$block_number    = ! $ip_in_whitelist && $ip_info[0]->failed_attempts + 1 >= $cbxsec_options['allowed_retries'] ? $ip_info[0]->stat_block_quantity + 1 : $ip_info[0]->stat_block_quantity;
			$wpdb->update(
				"{$prefix}all_failed_attempts",
				array(
					'failed_attempts'		=> $attempts_number,
					'block_quantity'		=> $block_number,
					'last_failed_attempt'	=> date( 'Y-m-d H:i:s', $timestamp )
				),
				array(
					'ip' => $ip
				)
			);
		}

		if ( ! empty( $error_code ) && ! empty( $error ) ) {
			if ( ! $wp_error || 'cbxsec_denylisted' == $error_code )
				$user = new WP_Error;
			$user->add( $error_code, $error );
		}
		return $user;
	}
}

// computability with CF
if ( ! function_exists( 'cbxsec_contact_form' ) ) {
	function cbxsec_contact_form() {
		global $wpdb, $cbxsec_options;

		// get plugin's options
		if ( empty( $cbxsec_options ) ) {
			register_cbxsec_settings();
		}

		// some data
		$ip						= cbxsec_get_ip();
		$ip_int					= sprintf( '%u', ip2long( $ip ) );
		$prefix					= "{$wpdb->prefix}cbxsec_";
		$current_timestamp		= current_time( 'mysql' );
		$timestamp				= time();
		$attempts_reset_time	= $timestamp + $cbxsec_options['minutes_to_reset'] * 60 + $cbxsec_options['hours_to_reset'] * 3600 + $cbxsec_options['days_to_reset'] * 86400;
		$block_till_time		= $timestamp + $cbxsec_options['minutes_of_lock'] * 60 + $cbxsec_options['hours_of_lock'] * 3600 + $cbxsec_options['days_of_lock'] * 86400;
		$blocks_reset_time		= $block_till_time + $cbxsec_options['minutes_to_reset_block'] * 60 + $cbxsec_options['hours_to_reset_block'] * 3600 + $cbxsec_options['days_to_reset_block'] * 86400;
		$email 					= sanitize_email( $_POST['cntctfrm_contact_email'] );
		$letters_time_interval 	= $cbxsec_options['letters_days'] * 86400 + $cbxsec_options['letters_hours'] * 3600 + $cbxsec_options['letters_minutes'] * 60 + $cbxsec_options['letters_seconds'];
		$error					= $where = '';
		$block                  = 0;

		// get info
		$email_info = $wpdb->get_results( $wpdb->prepare( "
			SELECT 
				{$prefix}failed_attempts.id AS id_failed_attempts, 
				{$prefix}failed_attempts.ip, 
				{$prefix}failed_attempts.email, 
				{$prefix}failed_attempts.block AS blocked, 
				{$prefix}failed_attempts.block_by, 
				{$prefix}failed_attempts.failed_attempts, 
				{$prefix}failed_attempts.block_quantity,
				{$prefix}failed_attempts.block_till,
				{$prefix}failed_attempts.last_failed_attempt,
				{$prefix}all_failed_attempts.id AS id_failed_attempts_statistics,
				{$prefix}all_failed_attempts.failed_attempts AS stat_attempts_number,
				{$prefix}all_failed_attempts.block_quantity AS stat_block_quantity,
				{$prefix}denylist.id AS in_denylist,
				{$prefix}allowlist.id AS in_allowlist
			FROM 
				{$prefix}failed_attempts
			LEFT JOIN 
				{$prefix}all_failed_attempts ON 
				{$prefix}all_failed_attempts.ip_int = {$prefix}failed_attempts.ip_int
			LEFT JOIN 
				{$prefix}denylist ON 
				{$prefix}denylist.ip = '$ip'
			LEFT JOIN 
				{$prefix}allowlist ON 
				{$prefix}allowlist.ip = '$ip'
			WHERE 
				{$prefix}failed_attempts.email = %s OR
				{$prefix}failed_attempts.ip = %d
		", $email, $ip ), ARRAY_A );

		$email_info = ( $email_info ) ? end( $email_info ) : '';

		// get some additional data from query
		$id_failed_attempts		= ( isset( $email_info['id_failed_attempts'] ) ) ? $email_info['id_failed_attempts'] : '1';
		$id_failed_attempts_statistics	= ( isset( $email_info['id_failed_attempts_statistics'] ) ) ? $email_info['id_failed_attempts_statistics'] : '1';

		$failed_attempts_number	= ( isset( $email_info['failed_attempts'] ) && ! is_null( $email_info['failed_attempts'] ) ) ? $email_info['failed_attempts'] : 0;
		$blocked 				= isset( $email_info['blocked'] ) && '1' == $email_info['blocked'];
		$block_quantity_number 	= ( isset( $email_info['block_quantity'] ) && ! is_null( $email_info['block_quantity'] ) ) ? $email_info['block_quantity'] : 0;
		$block_till 			= ( isset( $email_info['block_till'] ) && ! empty( $email_info['block_till'] ) ) ? $email_info['block_till'] : date( 'Y-m-d H:i:s', $block_till_time );
		$block_by 				= isset( $email_info['block_by'] ) ? $email_info['block_by'] : '';
		$ip_in_blacklist 		= isset( $email_info['in_denylist'] ) && ! is_null( $email_info['in_denylist'] );
		$email_in_blacklist 	= cbxsec_is_email_in_table( $email, 'denylist_email' );
		$ip_in_whitelist 		= isset( $email_info['in_allowlist'] ) && ! is_null( $email_info['in_allowlist'] );
		$stat_attempts_number 	= ! isset( $email_info['stat_attempts_number'] ) || is_null( $email_info['stat_attempts_number'] );
		$stat_block_quantity	= ( isset( $email_info['stat_block_quantity'] ) && ! is_null( $email_info['stat_block_quantity'] ) ) ? $email_info['stat_block_quantity'] : 0;
		$is_ip_in_table 		= cbxsec_is_ip_in_table( $ip, 'failed_attempts' );
		$is_email_in_table 		= cbxsec_is_email_in_table( $email, 'email_list' );
		$ip_from_list 			= ( isset( $email_info['ip'] ) ) ? $email_info['ip'] : '';
		$email_from_list 		= ( isset( $email_info['email'] ) ) ? $email_info['email'] : '';

		// count sendings from ip and email
		$count_sendings = $wpdb->get_results( $wpdb->prepare( "
			SELECT 
				( 
					SELECT 
						COUNT( ip ) 
					FROM 
						{$prefix}failed_attempts 
					WHERE 
						ip = %s AND 
						block = '0' 
				) AS ip, 
				( 
					SELECT 
						COUNT( email ) 
					FROM 
						{$prefix}failed_attempts 
					WHERE 
						email = %s AND 
						block = '0' 
				) AS email
		", $ip, $email ), ARRAY_A );

		$priority = ( $count_sendings[0]['email'] >= $count_sendings[0]['ip'] ) ? 'email' : 'ip';

		$block_quantity_number = ( $block_by === $priority ) ? $block_quantity_number : 0;

		// if IP or Email in denylist
		if ( $ip_in_blacklist || $email_in_blacklist ) {
			$error = str_replace(
				'%MAIL%',
				$cbxsec_options['email_address'],
				$cbxsec_options['denylisted_message']
			);
			$error = wp_specialchars_decode( $error, ENT_COMPAT );

			return $error;
		}

		// if IP or Email is blocked
		if ( in_array( $block_by, array( 'ip', 'email' ) ) && ( $ip_from_list === $ip || $email_from_list === $email ) ) {
			$error = str_replace(
				array( '%DATE%', '%MAIL%' ),
				array( cbxsec_block_time( $block_till ), $cbxsec_options['email_address'] ),
				$cbxsec_options['blocked_message']
			);
			$error = wp_specialchars_decode( $error, ENT_COMPAT );

			return $error;
		}

		// count send letter
		if ( ! $ip_in_whitelist ) {

			$failed_attempts_number += 1;

			// reset countdown to clear failed attempts number
			wp_clear_scheduled_hook( 'cbxsec_event_for_reset_failed_attempts', array( $ip, $email, $priority ) );

			// get time of failed attempt
			$event_time = ( $email_info ) ? strtotime( $email_info['last_failed_attempt'] ) : $timestamp;

			// number of failed attempts not more than allowed one
			if (
				$failed_attempts_number <= $cbxsec_options['number_of_letters'] ||
				( $timestamp - $event_time ) > $letters_time_interval
			) {

				// new countdown to reset number of failed attempts
				wp_schedule_single_event( $attempts_reset_time, 'cbxsec_event_for_reset_failed_attempts', array( $ip, $email, $priority ) );

				$block = 0;
				$block_till	= null;
				$block_by = null;

			} elseif ( ( $timestamp - $event_time ) < $letters_time_interval ) {

				// number of failed attempts more than allowed number
				$failed_attempts_number = 0;
				$block_quantity_number += 1;

				// reset countdown to clear blocks number
				wp_clear_scheduled_hook( 'cbxsec_event_for_reset_block_quantity', array( $ip, $email, $priority ) );

				if ( $block_quantity_number < $cbxsec_options['allowed_locks'] ) {
					// event: auto_blocked

					// new countdown to reset number of blockings
					wp_schedule_single_event( $blocks_reset_time, 'cbxsec_event_for_reset_block_quantity', array( $ip, $email, $priority ) );

					$block = 1;
					$block_till	= date( 'Y-m-d H:i:s', $block_till_time );
					$block_by = $priority;

					/*
					 * clearing 'cbxsec_event_for_reset_block' event if current timestamp
					 * less than time of next scheduled event and resetting event with current timestamp
					 * */
					$next_timestamp = $wpdb->get_row( $wpdb->prepare( "
						SELECT 
							block_till
						FROM 
							{$prefix}failed_attempts
						WHERE 
							block_till > %d 
						ORDER BY 
							block_till
					", $current_timestamp ), ARRAY_A );

					if ( ! empty( $next_timestamp ) ) {
						$next_timestamp_unix_time = strtotime( $next_timestamp['block_till'] );
						if (
							$block_till_time < $next_timestamp_unix_time &&
							wp_next_scheduled( 'cbxsec_event_for_reset_block' )
						) {
							wp_clear_scheduled_hook( 'cbxsec_event_for_reset_block' );
						}
					}

					// countdown to reset
					if ( ! wp_next_scheduled( 'cbxsec_event_for_reset_block' ) ) {
						wp_schedule_single_event( $block_till_time, 'cbxsec_event_for_reset_block' );
					}

					// htaccess hook
					if ( $cbxsec_options['block_by_htaccess'] ) {
						do_action( 'cbxsec_htaccess_hook_for_block', $ip );
					}

					// send e-mail to admin
					if ( $cbxsec_options['notify_email'] ) {
						cbxsec_send_email(
							$cbxsec_options['email_address'],
							$cbxsec_options['email_subject'],
							$cbxsec_options['email_blocked'],
							$ip
						);
					}

					// getting an error message
					$error = str_replace(
						array( '%DATE%', '%MAIL%' ),
						array( cbxsec_block_time( $block_till ), $cbxsec_options['email_address'] ),
						$cbxsec_options['blocked_message']
					);
					$error = wp_specialchars_decode( $error, ENT_COMPAT );

				} else {
					// Number of blockings more than allowed number, adding to denylist automatically
					// event: auto_denylisted

					$block = 0;
					$block_quantity_number = 0;
					$block_till = null;
					$block_by = null;

					// htaccess hook
					if ( $cbxsec_options['block_by_htaccess'] ) {
						do_action( 'cbxsec_htaccess_hook_for_block', $ip );
					}

					// insert denylist of ip or email
					$table = ( 'ip' == $priority ) ? "{$prefix}denylist" : "{$prefix}denylist_email";
					$val = ( 'ip' == $priority ) ? $ip : $email;
					$wpdb->insert(
						$table,
						array(
							$priority 	=> $val,
							'add_time' 	=> date( 'Y-m-d H:i:s', $timestamp )
						)
					);

					// send e-mail to admin
					if ( $cbxsec_options['notify_email'] ) {
						cbxsec_send_email(
							$cbxsec_options['email_address'],
							$cbxsec_options['email_subject_denylisted'],
							$cbxsec_options['email_denylisted'],
							$ip
						);
					}

					// getting an error message
					$error = str_replace(
						'%MAIL%',
						$cbxsec_options['email_address'],
						$cbxsec_options['denylisted_message']
					);
					$error = wp_specialchars_decode( $error, ENT_COMPAT );
				}
			}

			if ( empty( $email ) && 'email' == $block_by ) {
				$block_by = 'ip';
			}

			if ( ( $is_ip_in_table && 'ip' == $priority ) || ( $is_email_in_table && 'email' == $priority ) ) {
				$wpdb->update(
					"{$prefix}failed_attempts",
					array(
						'failed_attempts'		=> $failed_attempts_number,
						'email'					=> $email,
						'block'					=> $block,
						'block_quantity'		=> $block_quantity_number,
						'block_till'			=> $block_till,
						'block_by'				=> $block_by,
						'last_failed_attempt'	=> date( 'Y-m-d H:i:s', $timestamp )
					),
					array( 'id' => $id_failed_attempts )
				);
			} else {
				$wpdb->insert(
					"{$prefix}failed_attempts",
					array(
						'ip'					=> $ip,
						'ip_int'				=> $ip_int,
						'email'					=> $email,
						'failed_attempts'		=> $failed_attempts_number,
						'block'					=> $block,
						'block_quantity'		=> $block_quantity_number,
						'block_till'			=> $block_till,
						'block_by'				=> $block_by,
						'last_failed_attempt'	=> date( 'Y-m-d H:i:s', $timestamp )
					)
				);
			}

			if ( ! $is_email_in_table && $email_info ) {
				$id_failed_attempts = $wpdb->get_var( "
					SELECT 
						id
					FROM 
						{$prefix}failed_attempts 
					WHERE 
						email = '$email'
				" );
			}
		}

		if ( $stat_attempts_number ) {
			$block_number = ! $ip_in_whitelist && 1 == $cbxsec_options['number_of_letters'] ? 1 : 0;
			$wpdb->insert(
				"{$prefix}all_failed_attempts",
				array(
					'ip' 					=> $ip,
					'ip_int'				=> $ip_int,
					'email'					=> $email,
					'failed_attempts'		=> 1,
					'block'					=> $block,
					'block_quantity'		=> $block_number,
					'last_failed_attempt'	=> date( 'Y-m-d H:i:s', $timestamp )
				)
			);

			$id_failed_attempts_statistics = $wpdb->get_var( "
				SELECT
					id
				FROM
					{$prefix}all_failed_attempts
				WHERE
					email = '$email'
			" );
		} else {
			$attempts_number = ( isset( $email_info['stat_attempts_number'] ) ) ? $email_info['stat_attempts_number'] + 1 : 1;
			$block_number = ( ! $ip_in_whitelist && $failed_attempts_number + 1 < $cbxsec_options['number_of_letters'] ) ? $stat_block_quantity + 1 : $stat_block_quantity;
			$wpdb->update(
				"{$prefix}all_failed_attempts",
				array(
					'email'					=> $email,
					'failed_attempts'		=> $attempts_number,
					'block'					=> $block,
					'block_quantity'		=> $block_number,
					'last_failed_attempt'	=> date( 'Y-m-d H:i:s', $timestamp )
				),
				array( 'id' => $id_failed_attempts_statistics )
			);
		}


		if ( ! $is_email_in_table ) {
			$wpdb->insert(
				"{$prefix}email_list",
				array(
					'id_failed_attempts' 		=> $id_failed_attempts,
					'id_failed_attempts_statistics' 	=> $id_failed_attempts_statistics,
					'ip' 						=> $ip,
					'email' 					=> $email
				)
			);
		}

		return $error;
	}
}

/**
 * Send e-mails to admin
 * with notices about blocked or denylisted IPs
 * @param   string   $to        admin e-mail
 * @param   string   $subject   subject of message
 * @param   string   $message   text of message
 * @param   string   $ip        blocked/denylisted IP
 * @return  void
 */
if ( ! function_exists( 'cbxsec_send_email' ) ) {
	function cbxsec_send_email( $to, $subject, $message, $ip ) {
		$subject = str_replace(
			array( '%IP%', '%SITE_NAME%' ),
			array( $ip, get_bloginfo( 'name' ) ),
			$subject
		);
		$message = str_replace(
			array( '%IP%', '%PLUGIN_LINK%', '%WHEN%', '%SITE_NAME%', '%SITE_URL%' ),
			array( "{$ip}", esc_url( admin_url( 'admin.php?page=cybex-security.php' ) ), current_time( 'mysql' ), get_bloginfo( 'name' ), esc_url( site_url() ) ),
			$message
		);
		$headers  = 'MIME-Version: 1.0' . "\n";
		$headers .= 'Content-type: text/html; charset=utf-8' . "\n";
		wp_mail( $to, wp_specialchars_decode( $subject, ENT_COMPAT ), wp_specialchars_decode( $message, ENT_COMPAT ), $headers );
	}
}

/**
 * Hide login/lostpassword/register forms for denylisted or blocked IPs
 * @param  void
 * @return void
 */
if ( ! function_exists( 'cbxsec_login_scripts' ) ) {
	function cbxsec_login_scripts() {
		global $cbxsec_options, $cbxsec_hide_form, $error;

		/* get plugin`s options */
		if ( empty( $cbxsec_options ) ) {
			register_cbxsec_settings();
		}

		/*
		 * Check user`s IP on "login", "register" and "lostpassword" pages before sending of forms
		 */
		if ( ! isset( $_REQUEST['wp-submit'] ) && 1 == $cbxsec_options['hide_login_form'] ) {
			$result = cbxsec_check_ip();
			if ( is_wp_error( $result ) ) {
				$error = $result->get_error_message();
				$cbxsec_hide_form = true;
			}
		}
		if ( $cbxsec_hide_form ) {
			echo
			'<style type="text/css">
				.login #loginform,
				.login #registerform,
				.login #lostpasswordform,
				.login .message,
				.login #nav {
					display: none;
				}
			</style>';
		}
	}
}

/**
 * Hide register forms for denylisted or blocked IPs on multisite
 * @param  void
 * @return void
 */
if ( ! function_exists( 'cbxsec_signup_scripts' ) ) {
	function cbxsec_signup_scripts() {
		global $cbxsec_options, $cbxsec_hide_form, $error;

		/* get plugin`s options */
		if ( empty( $cbxsec_options ) ) {
			register_cbxsec_settings();
		}

		if ( 0 == $cbxsec_options['hide_login_form'] )
			return false;

		$result = cbxsec_check_ip();
		if ( is_wp_error( $result ) ) {
			$error = $result->get_error_message();
			if ( ! $cbxsec_hide_form )
				$cbxsec_hide_form = true;
			add_action( 'after_signup_form', 'cbxsec_display_error' );
		}

		if ( $cbxsec_hide_form ) {
			echo
			'<style type="text/css">
				#setupform {
					display: none;
				}
			</style>';
		}
	}
}

/**
 * Display error message on "register" page on multisite
 * @param  void
 * @return void
 */
if ( ! function_exists( 'cbxsec_display_error' ) ) {
	function cbxsec_display_error() {
		global $error;
		echo
			'<div class="widecolumn" id="cbxsec_mu_error">
				<div class="mu_register wp-signup-container">
					<p class="error">' .
						$error .
					'</p>
				</div>
			</div>';
	}
}

/**
 * How much time is left until the moment when IP would be unblocked
 * @param    string   $unblock_date
 * @return   string
 */
if ( ! function_exists( 'cbxsec_block_time' ) ) {
	function cbxsec_block_time ( $unblock_date ) {
		/* time difference */

		$time_diff = strtotime( $unblock_date ) - time();

		/* time limit for blocking has been exhausted or unidentified */
		if ( $time_diff <= 0 ) {
			$string = __( 'some time. Try to reload the page. Perhaps, you already have been unlocked', 'cybex-security' );
			cbxsec_reset_block();
			return $string;
		}

		/* less then 1 months */
		if ( $time_diff > 0 && $time_diff < 2635200 ) {

			$weeks   = intval( $time_diff / 604800 );
			$string  = ( 0 < $weeks ? '&nbsp;' . $weeks . '&nbsp;' . _n( 'week', 'weeks', $weeks, 'cybex-security' ) : '' );
			$sum     = $weeks * 604800;

			$days    = intval( ( $time_diff - $sum ) / 86400 );
			$string .= ( 0 < $days ? '&nbsp;' . $days . '&nbsp;' . _n( 'day', 'days', $days, 'cybex-security' ) : '' );
			$sum    += $days * 86400;

			$hours   = intval( ( $time_diff - $sum ) / 3600 );
			$string .= ( 0 < $hours ? '&nbsp;' . $hours . '&nbsp;' . _n( 'hour', 'hours', $hours, 'cybex-security' ) : '' );
			$sum    += $hours * 3600;

			$minutes = intval( ( $time_diff - $sum ) / 60 );
			$string .= ( 0 < $minutes ? '&nbsp;' . $minutes . '&nbsp;' . _n( 'minute', 'minutes', $minutes, 'cybex-security' ) : '' );
			$sum    += $minutes * 60;

			$seconds = $time_diff - $sum;
			$string .= ( 0 < $seconds ? '&nbsp;' . $seconds . '&nbsp;' . _n( 'second', 'seconds', $seconds, 'cybex-security' ) : '' );

			return $string;
		}

		/* from 1 to 6 months */
		if ( $time_diff >= 2635200 && $time_diff < 15768000 ) {
			$months = intval( $time_diff / 2635200 );
			$days   = $time_diff % 2635200;
			$days_string = 0 < $days ? '&nbsp;' . $days . '&nbsp;' . _n( 'day', 'days', $days, 'cybex-security' ) : '';
			return $months .'&nbsp;' . _n( 'month', 'months', $months, 'cybex-security' ) . $days;
		}

		/* from 6 to 12 months */
		if ( $time_diff >= 15768000 && $time_diff < 31536000 )
			return round( $time_diff / 15768000, 2 ) . '&nbsp;' . __( 'months', 'cybex-security' );

		/* more than one year */
		if ( $time_diff >= 31536000 ) {
			$years = round( $time_diff / 31536000, 2 );
			return $years . '&nbsp;' . _n( 'year', 'years', $years, 'cybex-security' );
		}

		return false;
	}
}

// add error to the Contact Form
if ( ! function_exists( 'cbxsec_cntctfrm_check' ) ) {
	function cbxsec_cntctfrm_check( $cntctfrm_error_message ) {
		global $cbxsec_options;

		if ( empty( $cbxsec_options ) ) {
			register_cbxsec_settings();
		}

		if ( ! $cbxsec_options['contact_form_restrict_sending_emails'] ) {
			return false;
		}

		if ( 1 == count( $cntctfrm_error_message ) ) {
			$check_error = cbxsec_contact_form();
			if ( $check_error ) {
				$cntctfrm_error_message['error_cbxsec'] = $check_error;

				return $cntctfrm_error_message;
			}
		}

		return false;
	}
}

/* handle login form */
add_filter( 'authenticate', 'cbxsec_authenticate', 99999, 3 );
add_filter( 'allow_password_reset', 'cbxsec_form_check', 99999 );
add_filter( 'registration_errors', 'cbxsec_form_check', 99999, 1 );
add_action( 'login_head', 'cbxsec_login_scripts' );
add_action( 'signup_header', 'cbxsec_signup_scripts' );

// for Contact Form
add_filter( 'cntctfrm_check', 'cbxsec_cntctfrm_check' );