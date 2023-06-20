<?php
/**
 * Display statistics
 * @package Wp Cybex Security
 * @since 1.1.3
 */

if ( ! class_exists( 'WP_List_Table' ) )
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );

if ( ! class_exists( 'Cbxlogin_Statistics' ) ) {
	class Cbxlogin_Statistics extends WP_List_Table {
		function get_columns() {
			/* adding collumns to table and their view */
			$columns = array(
				'cb'				=> '<input type="checkbox" />',
				'ip'				=> __( 'Ip Address', 'cybex-security' ),
				'email'				=> __( 'Email', 'cybex-security' ),
				'failed_attempts'	=> __( 'Failed Attempts', 'cybex-security' ),
				'block_quantity'	=> __( 'Blocks', 'cybex-security' ),
				'status'			=> __( 'Status', 'cybex-security' )
			);
			return $columns;
		}

		function get_bulk_actions() {
			/* adding bulk action */
			$actions = array(
				'clear_statistics_for_ips'	=> __( 'Delete statistics entry', 'cybex-security' )
			);
			return $actions;
		}

		function column_cb( $item ) {
			/* customize displaying cb collumn */
			return sprintf(
				'<input type="checkbox" name="id[]" value="%s" />', $item['id']
			);
		}

		function get_sortable_columns() {
			/* seting sortable collumns */
			$sortable_columns = array(
				'ip'				=> array( 'ip', true ),
				'failed_attempts'	=> array( 'failed_attempts', false ),
				'block_quantity'	=> array( 'block_quantity', false )
			);
			return $sortable_columns;
		}

		function single_row( $item ) {
			/* add class to non 'not_blocked' rows (deny-, allowlist or blocked) */
			$row_class = '';
			if ( isset( $item['row_class'] ) ) {
				/* if IP is deny-, allowlisted or blocked */
				$row_class = ' class="' . $item['row_class'] . '"';
			}

			echo '<tr' . esc_attr($row_class) . '>';
			$this->single_row_columns( $item );
			echo '</tr>';
		}

		function prepare_items() { /* preparing table items */
			global $wpdb;
			$prefix = $wpdb->prefix . 'cbxsec_';
			$where = '';

			$part_ip = isset( $_REQUEST['s'] ) ? sanitize_text_field( $_REQUEST['s'] ) : '';

			if ( isset( $_REQUEST['s'] ) ) {
				$search_ip = sprintf( '%u', ip2long( str_replace( " ", "", sanitize_text_field( $_REQUEST['s'] ) ) ) );
				if ( 0 != $search_ip || preg_match( "/^(\.|\d)?(\.?[0-9]{1,3}?\.?){1,4}?(\.|\d)?$/i", $part_ip ) ) {
					$where = " WHERE ip_int = {$search_ip} OR ip LIKE '%{$part_ip}%' ";
				}
			}

			/* query for total number of IPs */
			$count_query = "
                SELECT 
                    COUNT(*) 
                FROM 
                    {$prefix}all_failed_attempts
                {$where}
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

			/* the 'orderby' and 'order' values - If no sort, default to IP */
			$orderby = ( isset( $_REQUEST['orderby'] ) && in_array( $_REQUEST['orderby'], array_keys( $this->get_sortable_columns() ) ) && 'ip' != $_REQUEST['orderby'] ) ? $_REQUEST['orderby'] : 'ip_int';
			$order = ( isset( $_REQUEST['order'] ) && in_array( $_REQUEST['order'], array('asc', 'desc') ) ) ? $_REQUEST['order'] : 'asc';

			/* calculate offset for pagination */
			$paged = ( isset( $_REQUEST['paged'] ) && is_numeric( $_REQUEST['paged'] ) && 0 < $_REQUEST['paged'] ) ? $_REQUEST['paged'] : 1;
			/* set pagination arguments */
			$offset = ( $paged - 1 ) * $perpage;

			/* general query */
			$query = "
                SELECT
                    id, 
                    ip,
                    email,
                    block, 
                    failed_attempts, 
                    block_quantity 
                FROM 
                    {$prefix}all_failed_attempts
                    {$where}
            ";

			/* add calculated values (order and pagination) to our query */
			$query .= " ORDER BY `" . $orderby . "` " . $order . " LIMIT " . $offset . "," . $perpage;
			/* get data from 'all_failed_attempts' table */
			$statistics = $wpdb->get_results( $query, ARRAY_A );
			if ( $statistics ) {
				/* loop - we calculate and add 'status' column and class data */
				foreach ( $statistics as &$statistic ) {

					$get_email_arr = $wpdb->get_col( $wpdb->prepare( "
                        SELECT 
                            email 
                        FROM 
                            {$prefix}email_list 
                        WHERE 
                            id_failed_attempts_statistics = %s
                    ", $statistic['id'] ) );

					$statistic['email'] = ( $get_email_arr ) ? implode( '<br />', $get_email_arr ) : 'N/A';

					if ( cbxsec_is_ip_in_table( $statistic['ip'], 'denylist' ) ) {
						$statistic['status'] = '<a href="?page=' . esc_attr( $_REQUEST['page'] ) . '&action=denylist&s=' . esc_attr( $statistic['ip'] ) . '">' . __( 'denylisted', 'cybex-security' ) . '</a>';
						$statistic['row_class'] = 'cbxsec_denylist';
					} elseif ( cbxsec_is_ip_in_table( $statistic['ip'], 'allowlist' ) ) {
						$statistic['status'] = '<a href="?page=' . esc_attr($_REQUEST['page']) . '&action=allowlist&s=' . esc_attr($statistic['ip']) . '">' . __('allowlisted', 'cybex-security') . '</a>';
						$statistic['row_class'] = 'cbxsec_allowlist';
					} elseif ( cbxsec_is_blocked( $statistic['ip'], $get_email_arr ) ) {
						$statistic['status'] = '<a href="?page=' . esc_attr($_REQUEST['page']) . '&action=blocked&s=' . esc_attr($statistic['ip']) . '">' . __('blocked', 'cybex-security') . '</a>';
						$statistic['row_class'] = 'cbxsec_blocked';
					} else {
						$statistic['status'] = __( 'not blocked', 'cybex-security' );
					}
				}
			}

			$columns 				= $this->get_columns();
			$hidden 				= array();
			$sortable 				= $this->get_sortable_columns();
			$this->_column_headers 	= array( $columns, $hidden, $sortable );
			$this->items 			= $statistics;
		}

		function column_default( $item, $column_name ) {
			/* setting default view for collumn items */
			switch( $column_name ) {
				case 'ip':
				case 'email':
				case 'failed_attempts':
				case 'block_quantity':
				case 'status':
					return $item[ $column_name ];
				default:
					/* Show whole array for bugfix */
					return print_r( $item, true );
			}
		}

		function action_message() {
			global $wpdb;
			$action_message = array(
				'error' 			=> false,
				'done'  			=> false,
				'error_country'		=> false,
				'wrong_ip_format'	=> ''
			);
			$error = $done = '';
			$prefix = "{$wpdb->prefix}cbxsec_";
			$cbxsec_message_list = array(
				'notice'						=> __( 'Notice:', 'cybex-security' ),
				'empty_ip_list'					=> __( 'No address has been selected', 'cybex-security' ),
				'clear_stats_complete_done'		=> __( 'Statistics has been cleared completely', 'cybex-security' ),
				'stats_already_empty'			=> __( 'Statistics is already empty', 'cybex-security' ),
				'clear_stats_complete_error'	=> __( 'Error while clearing statistics completely', 'cybex-security' ),
				'clear_stats_for_ips_done'		=> __( 'Selected statistics entry (entries) has been deleted', 'cybex-security' ),
				'clear_stats_for_ips_error'		=> __( 'Error while deleting statistics entry (entries)', 'cybex-security' )
			);
			/* Clear Statistics */
			if ( isset( $_POST['cbxsec_clear_statistics_complete_confirm'] ) && check_admin_referer( "cybex-security/cybex-security.php" , 'cbxsec_nonce_name' ) ) {
				/* if clear completely */
				$result = cbxsec_clear_statistics_completely();
				if ( false === $result ) {
					/* if error */
					$action_message['error'] = $cbxsec_message_list['clear_stats_complete_error'];
				} elseif ( 0 === $result ) {
					/* if empty */
					$action_message['done'] = $cbxsec_message_list['notice'] . ' ' . $cbxsec_message_list['stats_already_empty'];
				} else {
					/* if success */
					$action_message['done'] = $cbxsec_message_list['clear_stats_complete_done'];
				}
			} elseif ( ( ( isset( $_POST['action'] ) && $_POST['action'] == 'clear_statistics_for_ips' ) || ( isset ( $_POST['action2'] ) && $_POST['action2'] == 'clear_statistics_for_ips' ) ) && check_admin_referer( 'bulk-' . $this->_args['plural'] ) ) {
				/* Clear some entries */
				if ( isset( $_POST['id'] ) ) {
					/* if statistics entries exist */
					$ids = isset( $_POST['id'] ) ? sanitize_text_field( $_POST['id'] ) : '';
					$error = $done = 0;
					foreach ( $ids as $id ) {
						if ( false === cbxsec_clear_statistics( $id ) ) {
							$error++;
                        } else {
							$done++;
                        }
					}
					if ( 0 < $error ) {
						$action_message['error'] = $cbxsec_message_list['clear_stats_for_ips_error'] . '. ' . __( 'Total', 'cybex-security') . ': ' . $error . ' ' . _n( 'entry', 'entries', $error, 'cybex-security' );
					}
					if ( 0 < $done ) {
						$action_message['done'] = $cbxsec_message_list['clear_stats_for_ips_done'] . '. ' . __( 'Total', 'cybex-security') . ': ' . $done . ' ' . _n( 'entry', 'entries', $done, 'cybex-security' );
					}
				} else {
					$action_message['done'] = $cbxsec_message_list['notice'] . ' ' . $cbxsec_message_list['empty_ip_list'];
				}
			}

			if ( isset( $_REQUEST['s'] ) ) {
				$search_request = isset( $_REQUEST['s'] ) ? sanitize_text_field( $_REQUEST['s'] ) : '';
				if ( ! empty( $search_request ) ) {
					if ( preg_match( '/^(25[0-5]|2[0-4][0-9]|[1][0-9]{2}|[1-9][0-9]|[0-9])?(\.?(25[0-5]|2[0-4][0-9]|[1][0-9]{2}|[1-9][0-9]|[-0-9])?){0,3}?$/', $search_request ) )
						$action_message['done'] .= ( empty( $action_message['done'] ) ? '' : '<br/>' ) . __( 'Search results for', 'cybex-security' ) . '&nbsp;' . $search_request;
					else
						$action_message['error'] .= ( empty( $action_message['error'] ) ? '' : '<br/>' ) . sprintf( __( 'Wrong format or it does not lie in range %s.', 'cybex-security' ), '0.0.0.0 - 255.255.255.255' );
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

if ( ! function_exists( 'cbxsec_display_statistics' ) ) {
	function cbxsec_display_statistics( $plugin_basename ) {
		global $cbxsec_options, $cbxsec_plugin_info, $wp_version;

		if ( isset( $_POST['cbxsec_clear_statistics_complete'] ) && check_admin_referer( $plugin_basename, 'cbxsec_nonce_name' ) ) { ?>
			<div id="cbxsec_clear_statistics_confirm">
				<p><?php _e( 'Are you sure you want to delete all statistics entries?', 'cybex-security' ) ?></p>
				<form method="post" action="" style="margin-bottom: 10px;">
					<button class="button button-primary" name="cbxsec_clear_statistics_complete_confirm"><?php _e( 'Yes, delete these entries', 'cybex-security' ) ?></button>
					<button class="button button-secondary" name="cbxsec_clear_statistics_complete_deny"><?php _e( 'No, go back to the Statistics page', 'cybex-security' ) ?></button>
					<?php wp_nonce_field( $plugin_basename, 'cbxsec_nonce_name' ); ?>
				</form>
			</div>
		<?php } else {
			?>
			<div id="cbxsec_statistics" class="cbxsec_list">
				<?php 
				$cbxsec_statistics_list = new Cbxlogin_Statistics();
				$cbxsec_statistics_list->action_message();
				$cbxsec_statistics_list->prepare_items(); ?>
				<form method="get" action="admin.php">
					<?php $cbxsec_statistics_list->search_box( __( 'Search IP', 'cybex-security' ), 'search_statistics_ip' ); ?>
					<input type="hidden" name="page" value="cybex-security-statistics.php" />
				</form>
				<form method="post" action="">
					<input type="hidden" name="cbxsec_clear_statistics_complete" />
					<input type="submit" class="button" value="<?php _e( 'Clear Statistics', 'cybex-security' ) ?>" />
					<?php wp_nonce_field( $plugin_basename, 'cbxsec_nonce_name' ); ?>
				</form>
				<form method="post" action="">
					<?php $cbxsec_statistics_list->display(); ?>
				</form>
			</div>
		<?php }
	}
}