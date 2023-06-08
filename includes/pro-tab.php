<?php
/**
 *
 * @package Wp Cybex Security
 * @since 1.1.3
 */

if ( ! function_exists( 'cbxsec_display_advertising' ) ) {
	function cbxsec_display_advertising( $what ) {
		global $cbxsec_plugin_info, $wp_version, $cbxsec_options;		
		if ( isset( $_POST['cbx_hide_premium_options'] ) ) {
			check_admin_referer( plugin_basename( __FILE__ ), 'cbxsec_nonce_name' );
			$result = cbx_hide_premium_options( $cbxsec_options );
			update_option( 'cbxsec_options', $result['options'] ); ?>
			<div class="updated fade inline"><p><strong><?php echo $result['message']; ?></strong></p></div>
		<?php } elseif ( ! cbx_hide_premium_options_check( $cbxsec_options ) ) { ?>
			<form method="post" action=""<?php if ( 'allowlist' == $what || 'denylist' == $what || 'allowlist-email' == $what || 'denylist-email' == $what ) echo ' style="max-width: 610px;"'; ?>>
				<!-- <div class="cbx_pro_version_bloc"> -->
					<div class="cbx_pro_version_table_bloc">
						<button type="submit" name="cbx_hide_premium_options" class="notice-dismiss cbx_hide_premium_options" title="<?php _e( 'Close', 'cybex-security' ); ?>"></button>
						<!-- <div class="cbx_table_bg"></div> -->
						<!-- <div style="padding: 5px;"> -->
							<?php if ( 'allowlist' == $what || 'denylist' == $what ) { ?>
								<div class="cbxsec_edit_list_form">
									<table>
										<tr>
											<td>
												<label><?php _e( 'Enter IP', 'cybex-security' ); ?></label>
												<?php $content = __( "Allowed formats", 'cybex-security' ) . ':<br /><code>192.168.0.1, 192.168.0.,<br/>192.168., 192.,<br/>192.168.0.1/8,<br/>123.126.12.243-185.239.34.54</code>
												<p>' . __( "Allowed range", 'cybex-security' ) . ':<br />
													<code>0.0.0.0 - 255.255.255.255</code>
												</p>
												<p>' . __( "Allowed separators", 'cybex-security' ) . ':<br />' . __( 'a comma', 'cybex-security' ) . '&nbsp;(<code>,</code>), ' . __( 'semicolon', 'cybex-security' ) . ' (<code>;</code>), ' . __( 'ordinary space, tab, new line or carriage return', 'cybex-security' ) . '</p>';
												echo cbx_add_help_box( $content ); ?>
												<br>
												<input type="text" disabled="disabled" />
											</td>
											<td>
												<label><?php _e( 'Reason for IP', 'cybex-security' ); ?></label>
												<?php echo cbx_add_help_box( __( "Allowed separators", 'cybex-security' ) . ':<br />' . __( 'a comma', 'cybex-security' ) . '&nbsp;(<code>,</code>), ' . __( 'semicolon', 'cybex-security' ) . ' (<code>;</code>), ' . __( 'tab, new line or carriage return', 'cybex-security' ) ); ?>
												<br>
												<input type="text" disabled="disabled" />
											</td>
										</tr>
										<tr>
											<td valign="top">
												<label><?php _e( 'Select country', 'cybex-security' ); ?></label><br>
												<select disabled="disabled" style="width: 100%;"></select>
											</td>
											<td>
												<label><?php _e( 'Reason for country', 'cybex-security' ); ?></label><br>
												<input type="text" disabled="disabled" />
											</td>
										</tr>
									</table>
								</div>
                            <?php } elseif ( 'allowlist-email' == $what ) { ?>
                                <div class="cbxsec_edit_list_form">
                                    <table>
                                        <tr>
                                            <td>
                                                <label><?php _e( 'Enter Email', 'cybex-security' ); ?></label>
                                                <?php $content = __( "Forbidden symbols", 'cybex-security' ) . ':<br /><code>! # $ % & \' * + /=  ? ^ ` { | } ~</code>
												<p>' . __( "Allowed separators", 'cybex-security' ) . ':<br />' . __( 'a comma', 'cybex-security' ) . '&nbsp;(<code>,</code>), ' . __( 'semicolon', 'cybex-security' ) . ' (<code>;</code>), ' . __( 'ordinary space, tab, new line or carriage return', 'cybex-security' ) . '</p>';
                                                echo cbx_add_help_box( $content ); ?>
                                                <br>
                                                <input type="text" disabled="disabled" />
                                            </td>
                                            <td>
                                                <label><?php _e( 'Reason for Email', 'cybex-security' ); ?></label>
                                                <?php echo cbx_add_help_box( __( "Allowed separators", 'cybex-security' ) . ':<br />' . __( 'a comma', 'cybex-security' ) . '&nbsp;(<code>,</code>), ' . __( 'semicolon', 'cybex-security' ) . ' (<code>;</code>), ' . __( 'tab, new line or carriage return', 'cybex-security' ) ); ?>
                                                <br>
                                                <input type="text" disabled="disabled" />
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <label style="display:inline-block; padding-bottom: 10px;" disabled="disabled" for="cbxsec_my_email"><input type="checkbox" id="cbxsec_my_email" name="cbxsec_my_email" /><?php _e( 'My Email', 'cybex-security' ); ?></label>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="position: relative;">
                                                <input class="button-primary" type="submit" disabled="disabled" value="<?php _e( 'Add New', 'cybex-security' ); ?>" />
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            <?php } elseif ( 'allowlist-email-table' == $what ) { ?>
                            	<p class="search-box">
                                    <input disabled="disabled" type="search" name="s" />
                                    <input disabled="disabled" type="submit" value="<?php _e( 'Search Email', 'cybex-security' ); ?>" class="button" />
                                </p>
                                <div class="tablenav top">
                                    <div class="alignleft actions bulkactions">
                                        <select disabled="disabled">
                                            <option><?php _e( 'Bulk Actions', 'cybex-security' ); ?></option>
                                        </select>
                                        <input disabled="disabled" type="submit" value="Apply" class="button action" />
                                    </div>
                                    <div class="tablenav-pages one-page"><span class="displaying-num">1 item</span></div>
                                    <br class="clear">
                                </div>
                                <table class="wp-list-table widefat fixed">
                                    <thead>
                                    <tr>
                                        <th class="manage-column check-column" scope="col"><input disabled="disabled" type="checkbox" /></th>
                                        <th class="manage-column column-primary" scope="col"><a href="#"><span><?php _e( 'Email', 'cybex-security' ); ?></span></a></th>
                                        <th class="manage-column" scope="col"><a href="#"><span><?php _e( 'Reason', 'cybex-security' ); ?></span></a></th>
                                        <th class="manage-column" scope="col"><a href="#"><span><?php _e( 'Date Added', 'cybex-security' ); ?></span></a></th>
                                    </tr>
                                    </thead>
                                    <tfoot>
                                    <tr>
                                        <th class="manage-column check-column" scope="col"><input disabled="disabled" type="checkbox" /></th>
                                        <th class="manage-column column-primary" scope="col"><a href="#"><span><?php _e( 'Email', 'cybex-security' ); ?></span></a></th>
                                        <th class="manage-column" scope="col"><a href="#"><span><?php _e( 'Reason', 'cybex-security' ); ?></span></a></th>
                                        <th class="manage-column" scope="col"><a href="#"><span><?php _e( 'Date Added', 'cybex-security' ); ?></span></a></th>
                                    </tr>
                                    </tfoot>
                                    <tbody>
                                    <tr class="alternate">
                                        <th class="check-column" scope="row"><input disabled="disabled" type="checkbox"></th>
                                        <td class="column-primary">example@example.com</td>
                                        <td><?php _e( 'My Email', 'cybex-security' ); ?></td>
                                        <td>November 25, 2014 11:55 am</td>
                                    </tr>
                                    </tbody>
                                </table>
                                <div class="tablenav bottom">
                                    <div class="alignleft actions bulkactions">
                                        <select disabled="disabled">
                                            <option><?php _e( 'Bulk Actions', 'cybex-security' ); ?></option>
                                        </select>
                                        <input disabled="disabled" type="submit" value="Apply" class="button action" />
                                    </div>
                                    <div class="tablenav-pages one-page"><span class="displaying-num">1 item</span></div>
                                    <br class="clear">
                                </div>
							<?php } elseif ( 'summaries' == $what ) { ?>
								<!-- <div>
									<img class="cbxsec_attempts" src="<?php echo plugins_url( '../images/attempts.png', __FILE__ ); ?>" alt="" />
								</div> -->
							<?php } elseif ( 'log' == $what ) { ?>
								<p class="search-box">
									<input disabled="disabled" type="search" name="s" />
									<input disabled="disabled" type="submit" value="<?php _e( 'Search IP', 'cybex-security' ); ?>" class="button" />
								</p>
								<input disabled="disabled" type="submit" value="<?php _e( 'Clear Log', 'cybex-security' ); ?>" class="button" />
								<div class="tablenav top">
									<div class="alignleft actions bulkactions">
										<select disabled="disabled">
											<option><?php _e( 'Delete log entry', 'cybex-security' ); ?></option>
										</select>
										<input disabled="disabled" type="submit" value="Apply" class="button action" />
									</div>
									<div class="tablenav-pages one-page"><span class="displaying-num">1 item</span></div>
									<br class="clear">
								</div>
								<table class="wp-list-table widefat fixed">
									<thead>
										<tr>
											<th class="manage-column check-column" scope="col"><input disabled="disabled" type="checkbox" /></th>
											<th class="manage-column column-primary" scope="col"><a href="#"><span><?php _e( 'IP address', 'cybex-security' ); ?></span></a></th>
                                            <th class="manage-column" scope="col"><a href="#"><span><?php _e( 'Email', 'cybex-security' ); ?></span></a></th>
                                            <th class="manage-column" scope="col"><a href="#"><span><?php _e( 'Login', 'cybex-security' ); ?></span></a></th>
                                            <th class="manage-column" scope="col"><a href="#"><span><?php _e( 'Password', 'cybex-security' ); ?></span></a></th>
                                            <th class="manage-column" scope="col"><a href="#"><span><?php _e( 'Hostname', 'cybex-security' ); ?></span></a></th>
											<th class="manage-column" scope="col"><a href="#"><span><?php _e( 'Event', 'cybex-security' ); ?></span></a></th>
											<th class="manage-column" scope="col"><a href="#"><span><?php _e( 'Form', 'cybex-security' ); ?></span></a></th>
											<th class="manage-column" scope="col"><a href="#"><span><?php _e( 'Event time', 'cybex-security' ); ?></a></th>
										</tr>
									</thead>
									<tfoot>
										<tr>
											<th class="manage-column check-column" scope="col"><input disabled="disabled" type="checkbox" /></th>
											<th class="manage-column column-primary" scope="col"><a href="#"><span><?php _e( 'IP address', 'cybex-security' ); ?></span></a></th>
                                            <th class="manage-column" scope="col"><a href="#"><span><?php _e( 'Email', 'cybex-security' ); ?></span></a></th>
                                            <th class="manage-column" scope="col"><a href="#"><span><?php _e( 'Login', 'cybex-security' ); ?></span></a></th>
                                            <th class="manage-column" scope="col"><a href="#"><span><?php _e( 'Password', 'cybex-security' ); ?></span></a></th>
                                            <th class="manage-column" scope="col"><a href="#"><span><?php _e( 'Hostname', 'cybex-security' ); ?></span></a></th>
											<th class="manage-column" scope="col"><a href="#"><span><?php _e( 'Event', 'cybex-security' ); ?></span></a></th>
											<th class="manage-column" scope="col"><a href="#"><span><?php _e( 'Form', 'cybex-security' ); ?></span></a></th>
											<th class="manage-column" scope="col"><a href="#"><span><?php _e( 'Event time', 'cybex-security' ); ?></a></th>
										</tr>
									</tfoot>
									<tbody>
										<tr class="alternate">
											<th class="check-column" scope="row"><input disabled="disabled" type="checkbox"></th>
											<td class="column-primary">127.0.0.1</td>
                                            <td>example@gmail.com</td>
                                            <td>admin</td>
                                            <td>123456</td>
                                            <td>localhost</td>
											<td><?php _e( 'Failed attempt', 'cybex-security' ); ?></td>
											<td><?php _e( 'Login form', 'cybex-security' ); ?></td>
											<td>November 25, 2014 11:55 am</td>
										</tr>
									</tbody>
								</table>
								<div class="tablenav bottom">
									<div class="alignleft actions bulkactions">
										<select disabled="disabled">
											<option><?php _e( 'Delete log entry', 'cybex-security' ); ?></option>
										</select>
										<input disabled="disabled" type="submit" value="Apply" class="button action" />
									</div>
									<div class="tablenav-pages one-page"><span class="displaying-num">1 item</span></div>
									<br class="clear">
								</div>
							<?php } elseif ( 'denylist-email' == $what ) { ?>
                                <div class="cbxsec_edit_list_form">
                                    <table>
                                        <tr>
                                            <td>
                                                <label><?php _e( 'Enter Email', 'cybex-security' ); ?></label>
                                                <?php $content = __( "Forbidden symbols", 'cybex-security' ) . ':<br /><code>! # $ % & \' * + /=  ? ^ ` { | } ~</code>
                                                <p>' . __( "Allowed separators", 'cybex-security' ) . ':<br />' . __( 'a comma', 'cybex-security' ) . '&nbsp;(<code>,</code>), ' . __( 'semicolon', 'cybex-security' ) . ' (<code>;</code>), ' . __( 'ordinary space, tab, new line or carriage return', 'cybex-security' ) . '</p>';
                                                echo cbx_add_help_box( $content ); ?>
                                                <br>
                                                <textarea rows="2" cols="32" disabled></textarea>
                                            </td>
                                            <td>
                                                <label><?php _e( 'Reason for Email', 'cybex-security' ); ?></label>
                                                <?php echo cbx_add_help_box( __( "Allowed separators", 'cybex-security' ) . ':<br />' . __( 'a comma', 'cybex-security' ) . '&nbsp;(<code>,</code>), ' . __( 'semicolon', 'cybex-security' ) . ' (<code>;</code>), ' . __( 'tab, new line or carriage return', 'cybex-security' ) ); ?>
                                                <br>
                                                <textarea rows="2" cols="32" disabled></textarea>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="position: relative;">
                                                <input class="button-primary" type="submit" disabled="disabled" value="<?php _e( 'Add New', 'cybex-security' ); ?>" />
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                           <?php } ?>
						<!-- </div> -->
					</div>

				<!-- </div> -->
				<?php wp_nonce_field( plugin_basename( __FILE__ ), 'cbxsec_nonce_name' ); ?>
			</form>
		<?php } elseif ( 'log' == $what ) { ?>
            <p>
				<?php _e( 'This tab contains Pro options only.', 'cybex-security' );
				echo ' ' . sprintf(
						__( '%sChange the settings%s to view the Pro options.', 'cybex-security' ),
						'<a href="admin.php?page=cybex-security.php&cbx_active_tab=misc">',
						'</a>'
					); ?>
            </p>
		<?php }
	}
}