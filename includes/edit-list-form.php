<?php
/**
 * Display form for displaying, edititng or deleting of denylist/allowlist
 * @package Wp Cybex Security
 * @since 1.1.8
 */
if ( ! function_exists( 'cbxsec_display_list' ) ) {
	function cbxsec_display_list() {
		global $wpdb, $wp_version, $cbxsec_options;

		$list = isset( $_GET['list'] ) && 'allowlist' == $_GET['list'] ? 'allowlist' : 'denylist';

		if ( 'allowlist' == $list ) {
			$file = ( isset( $_GET['tab-action'] ) ) ? 'allowlist-email' : 'allowlist';
		} else {
			$file = ( isset( $_GET['tab-action'] ) ) ? 'denylist-email' : 'denylist';
		}
		if ( $file != 'allowlist-email' ) {
			require_once( dirname( __FILE__ ) . '/'. $file . '.php' );
		}

		$title = '';

		switch ( $file ) {
			case 'allowlist' :
				$list_table = new Cbxlogin_Allowlist();
				$title = __( 'IP Allow List', 'cybex-security' );
				break;
			case 'denylist' :
				$list_table = new Cbxlogin_Denylist();
				$title = __( 'IP Deny List', 'cybex-security' );
				break;
			case 'denylist-email' :
				$list_table = new Cbxlogin_Denylist_Email();
				$title = __( 'Email Deny List', 'cybex-security' );
				break;
			case 'allowlist-email' :
				$title = __( 'Email Allow List', 'cybex-security' );
				break;
		}

		$blacklist_count = $wpdb->get_var( "
            SELECT 
                SUM(T.id) 
            FROM 
            ( 
                SELECT 
                    COUNT(*) AS id 
                FROM `{$wpdb->prefix}cbxsec_denylist_email` 
                UNION ALL
                SELECT 
                    COUNT(*) AS id 
                FROM 
                    `{$wpdb->prefix}cbxsec_denylist`
            ) T
        " );
		$whitelist_count = $wpdb->get_var( "SELECT COUNT(*) FROM `{$wpdb->prefix}cbxsec_allowlist`" );
		if ( $wp_version >= '4.7' ) { ?>
			<h1 class="wp-heading-inline"><?php echo esc_attr($title); ?></h1>
			<a style=" <?php echo ( 'denylist' != $file && 'allowlist' != $file ) ? 'display: none;' : '';  
				echo ( cbx_hide_premium_options_check( $cbxsec_options ) && 'denylist-email' == $file ) ? 'display:none;' : '' ?>"
				href="<?php echo 'admin.php?page=cbxsec-create-new-items.php&type=' . $file ?>" class="add-new-h2"><?php _e( 'Add New', 'cybex-security' ); ?></a>		
			<hr class="wp-header-end">
		<?php } ?>
		<div class="clear"></div>
        <div id="cbxsec_<?php echo esc_attr($list); ?>" class="cbxsec_list">
            <?php cbxsec_edit_list();
            if ( $file != 'allowlist-email' ) {
	            $list_table->action_message();
	            $list_table->prepare_items();
	        }
	        else {
	        	cbxsec_display_advertising( 'allowlist-email-table' );
	        }
            if ( isset( $_GET['tab-action'] ) && 'denylist_email' == $_GET['tab-action'] ) {
				$list = "denylist&#38;tab-action=denylist_email";
                $search_text = __( 'Search Email', 'cybex-security' );
            } else {
				$search_text =__( 'Search IP', 'cybex-security' );
			}
            if ( ! isset( $_GET['tab-action'] ) || ( isset( $_GET['tab-action'] ) && 'allowlist_email' != $_GET['tab-action'] ) ) { ?>
                <form method="get" action="admin.php">
                    <?php $list_table->search_box( $search_text, 'search_' . $list . 'ed_ip' ); ?>
                    <input type="hidden" name="page" value="cybex-security-deny-and-allowlist.php" />
                    <input type="hidden" name="list" value="<?php echo esc_attr($list); ?>" />
                </form>
                <form method="post" action="admin.php?page=cybex-security-deny-and-allowlist.php&list=<?php echo esc_attr($list); ?>">
                    <?php $list_table->display(); ?>
                </form>
            <?php } ?>
        </div>
	<?php }
}

if ( ! function_exists( 'cbxsec_edit_list' ) ) {
	function cbxsec_edit_list( ) {
		global $wpdb, $cbxsec_options;
		$cbxsec_table = isset( $_GET['list'] ) && 'allowlist' == $_GET['list'] ? 'allowlist' : 'denylist';

		if ( ! empty( $cbxsec_table ) ) {
		    $display_style = ( cbx_hide_premium_options_check( $cbxsec_options ) && 'allowlist' == $cbxsec_table ) ? 'display:none;' : 'display:block;';
            $background = ( 'allowlist' == $cbxsec_table ) ? 'background: #f2eccc;' : '';
            ?>
            <h2 class="nav-tab-wrapper">
                <a class="nav-tab <?php if ( ! isset( $_GET['tab-action'] ) ) echo ' nav-tab-active'; ?>"
                   href="admin.php?page=cybex-security-deny-and-allowlist.php&list=<?php echo esc_attr($cbxsec_table) ?>"><?php _e( 'IP Address', 'cybex-security' ); ?>
                </a>
                <a class="nav-tab <?php if ( isset( $_GET['tab-action'] ) ) echo ' nav-tab-active'; ?>"
                   style="<?php echo esc_attr($background); echo  $display_style ?>"
                   href="admin.php?page=cybex-security-deny-and-allowlist.php&amp;list=<?php echo esc_attr($cbxsec_table) ?>&amp;tab-action=<?php echo esc_attr($cbxsec_table) ?>_email"><?php _e( 'Email', 'cybex-security' ); ?>
                </a>
            </h2>
        <?php }
	}
}

if ( ! function_exists( 'cbxsec_display_blocked' ) ) {
	function cbxsec_display_blocked() {
		$list = ( isset( $_GET['tab-action'] ) ) ? 'blocked-email' : 'blocked';
		require_once( dirname( __FILE__ ) . '/'. $list . '.php' );
		$list_table = ( 'blocked' == $list ) ? new Cbxlogin_Blocked_List() : new Cbxlogin_Blocked_List_Email();
		?>
        <h1 class="wp-heading-inline"><?php echo get_admin_page_title(); ?></h1>
        <div id="cbxsec_blocked" class="cbxsec_list">
            <h2 class="nav-tab-wrapper">
                <a class="nav-tab <?php if ( ! isset( $_GET['tab-action'] ) ) echo ' nav-tab-active'; ?>"
                   href="admin.php?page=cybex-security-blocked.php"><?php _e( 'IP Address', 'cybex-security' ); ?>
                </a>
                <a class="nav-tab <?php if ( isset( $_GET['tab-action'] ) ) echo ' nav-tab-active'; ?>"
                   href="admin.php?page=cybex-security-blocked.php&amp;tab-action=email"><?php _e( 'Email', 'cybex-security' ); ?>
                </a>
            </h2>
			<?php
			$list_table->action_message();
			$list_table->prepare_items();
			if ( isset( $_GET['tab-action'] ) && 'email' == $_GET['tab-action'] ) {
                $tab_action = "&tab-action=email";
				$search_text = __( 'Search Email', 'cybex-security' );
			} else {
				$search_text =__( 'Search IP', 'cybex-security' );
                $tab_action = "";
			}
			?>
            <form method="get" action="admin.php">
                <?php $list_table->search_box( $search_text, 'search_' . $list . 'ed_ip' ); ?>
                <input type="hidden" name="page" value="cybex-security-blocked.php" />
                <input type="hidden" name="list" value="<?php echo esc_attr($list); ?>" />
                <?php if ( isset( $_GET['tab-action'] ) && 'email' == $_GET['tab-action'] ) { ?>
                    <input type="hidden" name="tab-action" value="email" />
                <?php } ?>
            </form>
            <form method="post" action="admin.php?page=cybex-security-blocked.php<?php echo esc_attr($tab_action); ?>">
                <?php $list_table->display(); ?>
            </form>
        </div>
	<?php }
}