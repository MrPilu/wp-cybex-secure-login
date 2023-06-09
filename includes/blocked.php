<?php
/**
 * Display list of IP, which are temporary blocked
 * @package Wp Cybex Security
 * @since 1.1.3
 */
if ( ! class_exists( 'WP_List_Table' ) )
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );

if ( ! class_exists( 'Cbxlogin_Blocked_List' ) ) {
	class Cbxlogin_Blocked_List extends WP_List_Table {
		function get_columns() {
			/* adding collumns to table and their view */
			$columns = array(
				'cb'			=> '<input type="checkbox" />',
				'ip'			=> __( 'IP Address', 'cybex-security' ),
				'block_till'	=> __( 'Date Expires', 'cybex-security' )
			);
			return $columns;
		}

		function get_sortable_columns() {
			/* seting sortable collumns */
			$sortable_columns = array(
				'ip'			=> array( 'ip', true ),
				'block_till'	=> array( 'block_till', false )
			);
			return $sortable_columns;
		}

		function column_ip( $item ) {
			/* adding action to 'ip' collumn */
			$actions = array(
				'reset_block' => '<a href="' . wp_nonce_url( sprintf( '?page=%s&cbxsec_reset_block=%s', esc_url( $_REQUEST['page'] ), $item['ip'] ), 'cbxsec_reset_block_' . $item['ip'], 'cbxsec_nonce_name' ) . '">' . __( 'Reset Block', 'cybex-security' ) . '</a>',
				'add_to_allowlist' => '<a href="' . wp_nonce_url( sprintf( '?page=%s&cbxsec_add_to_allowlist=%s', esc_url( $_REQUEST['page'] ), $item['ip'] ), 'cbxsec_add_to_allowlist_' . $item['ip'], 'cbxsec_nonce_name' ) . '">' . __( 'Add to Allow list', 'cybex-security' ) . '</a>'
			);
			return sprintf( '%1$s %2$s', $item['ip'], $this->row_actions( $actions ) );
		}

		function get_bulk_actions() {
			/* adding bulk action */
			$actions = array(
				'reset_blocks'		=> __( 'Reset Block', 'cybex-security' ),
				'add_to_allowlist'	=> __( 'Add to Allow List', 'cybex-security' )
			);
			return $actions;
		}

		function column_cb( $item ) {
			/* customize displaying cb collumn */
			return sprintf(
				'<input type="checkbox" name="ip[]" value="%s" />', $item['ip']
			);
		}

		function prepare_items() {
			/* preparing table items */
			global $wpdb;

			$and = '';

			$prefix = $wpdb->prefix . 'cbxsec_';
			$part_ip = isset( $_REQUEST['s'] ) ? trim( sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) ) : '';

			/* if search */
			if ( isset( $_REQUEST['s'] ) ) {
				$search_ip = sprintf( '%u', ip2long( str_replace( " ", "", trim( $_REQUEST['s'] ) ) ) );
				if ( 0 != $search_ip || preg_match( "/^(\.|\d)?(\.?[0-9]{1,3}?\.?){1,4}?(\.|\d)?$/i", $part_ip ) ) {
					$and = " AND ( ip_int = {$search_ip} OR ip LIKE '%{$part_ip}%' ) ";
				}
			}

			/* query for total number of IPs */
			$count_query = "
                SELECT 
                    COUNT( ip )
                FROM 
                    {$prefix}failed_attempts 
                WHERE 
                    block = TRUE AND 
                    block_by = 'ip'
                    {$and}
            ";

			/* get the total number of IPs */
			$totalitems = $wpdb->get_var( $count_query );
			/* get the value of number of IPs on one page */
			$perpage = $this->get_items_per_page( 'addresses_per_page', 20 );

			/* set pagination arguments */
			$this->set_pagination_args( array(
				"total_items" 	=> $totalitems,
				"per_page" 		=> $perpage
			) );

			/* the 'orderby' and 'order' values */
			$orderby = isset( $_REQUEST['orderby'] ) && in_array( $_REQUEST['orderby'], array_keys( $this->get_sortable_columns() ) ) ? $_REQUEST['orderby'] : 'block_till';
			$order   = ( isset( $_REQUEST['order'] ) && in_array( $_REQUEST['order'], array( 'asc', 'desc' ) ) ) ? $_REQUEST['order'] : 'asc';
			/* calculate offset for pagination */
			$paged   = ( isset( $_REQUEST['paged'] ) && is_numeric( $_REQUEST['paged'] ) && 0 < $_REQUEST['paged'] ) ? $_REQUEST['paged'] : 1;
			$offset  = ( $paged - 1 ) * $perpage;

			/* general query */
			$query = "
                SELECT 
                    ip, 
                    block_till 
                FROM 
                    {$prefix}failed_attempts 
                WHERE 
                    block = TRUE AND 
                    block_by = 'ip' 
                    {$and}
            ";

			/* add calculated values (order and pagination) to our query */
			$query .= " ORDER BY `" . $orderby. "` " . $order . " LIMIT " . $offset . "," . $perpage;
			/* get data from our failed_attempts table - list of blocked IPs */
			$blocked_items = $wpdb->get_results( $query, ARRAY_A );
			/* get site date and time format from DB option */
			$date_time_format = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );
			foreach ( $blocked_items as &$blocked_item ) {
				/* process block_till date */
				$blocked_item['block_till'] = date( $date_time_format, strtotime( $blocked_item['block_till'] ) );
			}

			$columns 				= $this->get_columns();
			$hidden 				= array();
			$sortable 				= $this->get_sortable_columns();
			$this->_column_headers 	= array( $columns, $hidden, $sortable );
			$this->items 			= $blocked_items;
		}

		function column_default( $item, $column_name ) {
			/* setting default view for collumn items */
			switch( $column_name ) {
				case 'ip':
				case 'block_till':
					return $item[ $column_name ];
				default:
					/* Show whole array for bugfix */
					return print_r( $item, true ) ;
			}
		}

		function action_message() {
			global $wpdb, $cbxsec_options;
			$action_message = array(
				'error' 			=> false,
				'done'  			=> false,
				'error_country'		=> false,
				'wrong_ip_format'	=> ''
			);
			$error = $done = '';
			$prefix = "{$wpdb->prefix}cbxsec_";
			$message_list = array(
				'notice'						=> __( 'Notice:', 'cybex-security' ),
				'empty_ip_list'					=> __( 'No address has been selected', 'cybex-security' ),
				'block_reset_done'				=> __( 'Block has been reset for', 'cybex-security' ),
				'block_reset_error'				=> __( 'Error while reseting block for', 'cybex-security' ),
				'single_add_to_allowlist_done'	=> __( 'IP address was added to allow list', 'cybex-security' ),
				'add_to_allowlist_done'			=> __( 'IP addresses were added to allow list', 'cybex-security' ),
			);
			/* Realization action in table with blocked addresses */
			if (
                isset( $_GET['cbxsec_add_to_allowlist'] ) &&
                check_admin_referer( 'cbxsec_add_to_allowlist_' . $_GET['cbxsec_add_to_allowlist'], 'cbxsec_nonce_name' )
            ) {
				if ( filter_var( $_GET['cbxsec_add_to_allowlist'], FILTER_VALIDATE_IP ) ) {
					$ip = $_GET['cbxsec_add_to_allowlist'];
					$ip_int = sprintf( '%u', ip2long( $ip ) );

					/* single IP de-block */
					$result_reset_block = $wpdb->update(
						'{$prefix}failed_attempts',
						array(
							'block' => false,
							'block_till' => null,
							'block_by' => null
						),
						array( 'ip_int' => sprintf( '%u', $ip_int ) )
					);
					/* single IP add to allow list */
					if ( false !== $result_reset_block ) {
						$wpdb->insert(
							'{$prefix}allowlist',
							array(
								'ip' => $ip,
								'add_time' => date( 'Y-m-d H:i:s', current_time( 'timestamp' ) )
							)
						);

						$action_message['done'] = $message_list['single_add_to_allowlist_done'] . ':&nbsp;' . esc_html( $ip );

						if ( $cbxsec_options['block_by_htaccess'] ) {
							do_action( 'cbxsec_htaccess_hook_for_add_to_whitelist', $ip );
						}
					}
				}
			} elseif (
                isset( $_REQUEST['cbxsec_reset_block'] ) &&
                check_admin_referer( 'cbxsec_reset_block_' . $_REQUEST['cbxsec_reset_block'], 'cbxsec_nonce_name' )
            ) {
				/* single IP de-block */
				$result_reset_block = $wpdb->update(
					'{$prefix}failed_attempts',
					array( 'block' => false, 'block_till' => null, 'block_by' => null ),
					array( 'ip_int' => sprintf( '%u', ip2long( $_REQUEST['cbxsec_reset_block'] ) ), 'block_by' => 'ip' ),
					array( '%s' ),
					array( '%s' )
				);

				if ( false !== $result_reset_block ) {
					/* if operation with DB was succesful */
					$action_message['done'] = $message_list['block_reset_done'] . '&nbsp;' . esc_html( $_REQUEST['cbxsec_reset_block'] );

					if ( 1 == $cbxsec_options["block_by_htaccess"] ) {
						do_action( 'cbxsec_htaccess_hook_for_reset_block', trim( $_REQUEST['cbxsec_reset_block'] ) ); /* hook for deblocking by Htaccess */
					}
				} else {
					/* if error */
					$action_message['error'] = $message_list['block_reset_error'] . '&nbsp;' . esc_html( $_REQUEST['cbxsec_reset_block'] );
				}
			} elseif ( ( ( isset( $_POST['action'] ) && $_POST['action'] == 'reset_blocks' ) || ( isset( $_POST['action2'] ) && $_POST['action2'] == 'reset_blocks' ) ) && check_admin_referer( 'bulk-' . $this->_args['plural'] ) ) {
				$done_reset_block = array();
				/* Realization bulk action in table with blocked addresses */
				if ( isset( $_POST['ip'] ) ) {
					/* array for loop */
					$ips = sanitize_text_field($_POST['ip']);
					foreach ( $ips as $ip ) {
						$result_reset_block = $wpdb->update(
							'{$prefix}cbxsec_failed_attempts',
							array( 'block' => false, 'block_till' => null, 'block_by' => null ),
							array( 'ip_int' => sprintf( '%u', ip2long( $ip ) ), 'block_by' => 'ip' ),
							array( '%s' ),
							array( '%s' )
						);
						if ( false !== $result_reset_block ) {
							/* if success */
							$done .= empty( $done ) ? $ip : ', ' . $ip;
							$done_reset_block[] = $ip;
						} else {
							/* if error */
							$error .= empty( $error ) ? $ip : ', ' . $ip;
						}
					}

					if ( 1 == $cbxsec_options["block_by_htaccess"] && ! empty( $done_reset_block ) ) {
						do_action( 'cbxsec_htaccess_hook_for_reset_block', $done_reset_block ); /* hook for deblocking by Htaccess */
					}

					if ( ! empty( $done ) ) {
						/* if some IPs were de-blocked */
						$action_message['done'] = $message_list['block_reset_done'] . '&nbsp;' . $done;
					}
					if ( ! empty( $error ) ) {
						/* if some IPs were not de-blocked because of error in DB */
						$action_message['error'] = $message_list['block_reset_error'] . '&nbsp;' . $error;
					}
				} else {
					/* if empty IP list */
					$action_message['done'] = $message_list['notice'] . '&nbsp;' . $message_list['empty_ip_list'];
				}
			} elseif ( ( ( isset( $_POST['action'] ) && $_POST['action'] == 'add_to_allowlist' ) || ( isset( $_POST['action2'] ) && $_POST['action2'] == 'add_to_allowlist' ) ) && check_admin_referer( 'bulk-' . $this->_args['plural'] ) ) {
                $done_add_to_whitelist = array();
                /* Realization bulk action in table with blocked addresses */
                if ( isset( $_POST['ip'] ) ) {
                    /* array for loop */
                    $ips = sanitize_text_field($_POST['ip']);
                    foreach ( $ips as $ip ) {
                        $ip_int = sprintf( '%u', ip2long( $ip ) );
                        $result_reset_block = $wpdb->update(
                            '{$prefix}failed_attempts',
                            array( 'block' => false, 'block_till' => null, 'block_by' => null ),
                            array( 'ip_int' => $ip_int, 'block_by' => 'ip' ),
                            array( '%s' ),
                            array( '%s' )
                        );
                        /* if success */
                        if ( false !== $result_reset_block ) {
                            $wpdb->insert(
                                '{$prefix}allowlist',
                                array(
                                    'ip' => $ip,
                                    'add_time' => date( 'Y-m-d H:i:s', current_time( 'timestamp' ) )
                                )
                            );

                            $action_message['done'] = $message_list['single_add_to_allowlist_done'] . ':&nbsp;' . esc_html( $ip );

                            if ( $cbxsec_options['block_by_htaccess'] ) {
                                do_action( 'cbxsec_htaccess_hook_for_add_to_whitelist', $ip );
                            }
                        }
                    }
                    if ( isset( $cbxsec_options['block_by_htaccess'] ) && ! empty( $done_add_to_whitelist ) ) {
                        do_action( 'cbxsec_htaccess_hook_for_add_to_whitelist', $done_add_to_whitelist );
                    }
                    $action_message['done'] = $message_list['add_to_allowlist_done'] . '&nbsp;' . $done;
                } else {
                    /* if empty IP list */
                    $action_message['done'] = $message_list['notice'] . '&nbsp;' . $message_list['empty_ip_list'];
                }
            }

			if ( isset( $_REQUEST['s'] ) ) {
				$search_request = sanitize_text_field($_REQUEST['s']);
				if ( ! empty( $search_request ) ) {
					if ( preg_match( '/^(25[0-5]|2[0-4][0-9]|[1][0-9]{2}|[1-9][0-9]|[0-9])?(\.?(25[0-5]|2[0-4][0-9]|[1][0-9]{2}|[1-9][0-9]|[-0-9])?){0,3}?$/', $search_request ) ) {
						$action_message['done'] .= ( empty( $action_message['done'] ) ? '' : '<br/>' ) . __( 'Search results for', 'cybex-security' ) . '&nbsp;' . $search_request;
					} else {
						$action_message['error'] .= ( empty( $action_message['error'] ) ? '' : '<br/>' ) . sprintf( __( 'Wrong format or it does not lie in range %s.', 'cybex-security' ), '0.0.0.0 - 255.255.255.255' );
				    }
				}
			}

			if ( ! empty( $action_message['error'] ) ) { ?>
				<div class="error inline lmttmpts_message"><p><strong><?php echo $action_message['error']; ?></strong></div>
			<?php }
			if ( ! empty( $action_message['done'] ) ) { ?>
				<div class="updated inline lmttmpts_message"><p><?php echo $action_message['done'] ?></p></div>
			<?php }
		}
	}
}