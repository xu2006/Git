<?php
/**
 * Points Admin class
 */
class Points_Admin {

	public static function init () {
		add_action( 'admin_notices', array( __CLASS__, 'admin_notices' ) );
		add_action( 'admin_menu', array( __CLASS__, 'admin_menu' ), 'manage_options' );
	}

	public static function admin_notices() {
		if ( !empty( self::$notices ) ) {
			foreach ( self::$notices as $notice ) {
				echo $notice;
			}
		}
	}

	/**
	 * Adds the admin section.
	 */
	public static function admin_menu() {
		$admin_page = add_menu_page('金币','金币','manage_options','points',
				array( __CLASS__, 'points_menu'),'dashicons-awards'
		);

		$page = add_submenu_page('points','设置','设置','manage_options','points-admin-options',
				array( __CLASS__, 'points_admin_options')
		);
	}

	public static function points_menu() {

		$alert = "";
		if ( isset( $_POST['save'] ) && isset( $_POST['action'] ) ) {
			if ( $_POST['action'] == "edit" ) {
				$point_id = isset($_POST['point_id'])?intval( $_POST['point_id'] ) : null;
				$points = Points::get_point( $point_id );
				$data = array();
				if ( isset( $_POST['user_mail'] ) ) {
					$xuser = get_user_by( 'email', $_POST['user_mail'] );
					$data['user_id'] = $xuser->ID;
				}
				if ( isset( $_POST['user_id'] ) ) {
					$data['user_id'] = $_POST['user_id'];
				}
				if ( isset( $_POST['datetime'] ) ) {
					$data['datetime'] = $_POST['datetime'];
				}
				if ( isset( $_POST['description'] ) ) {
					$data['description'] = $_POST['description'];
				}
				if ( isset( $_POST['status'] ) ) {
					$data['status'] = $_POST['status'];
				}
				if ( isset( $_POST['points'] ) ) {
					$data['points'] = $_POST['points'];
				}

				if ( $points ) {  // 编辑金币
					Points::update_points($point_id, $data);
						/*更新金币*/
						$user = get_user_by( 'id', $_POST['user_id'] );
						$message = '<div class="emailcontent" style="width:100%;max-width:720px;text-align:left;margin:0 auto;padding-top:80px;padding-bottom:20px"><div class="emailtitle"><h1 style="color:#fff;background:#51a0e3;line-height:70px;font-size:24px;font-weight:400;padding-left:40px;margin:0">充值到账通知</h1><div class="emailtext" style="background:#fff;padding:20px 32px 40px"><div style="padding:0;font-weight:700;color:#6e6e6e;font-size:16px">尊敬的'.$user->display_name.',您好！</div><p style="color:#6e6e6e;font-size:13px;line-height:24px">您的金币充值已成功到账，请查收！</p><table cellpadding="0" cellspacing="0" border="0" style="width:100%;border-top:1px solid #eee;border-left:1px solid #eee;color:#6e6e6e;font-size:16px;font-weight:normal"><thead><tr><th colspan="2" style="padding:10px 0;border-right:1px solid #eee;border-bottom:1px solid #eee;text-align:center;background:#f8f8f8">您的金币详细情况</th></tr></thead><tbody><tr><td style="padding:10px 0;border-right:1px solid #eee;border-bottom:1px solid #eee;text-align:center;width:100px">用户名</td><td style="padding:10px 20px 10px 30px;border-right:1px solid #eee;border-bottom:1px solid #eee;line-height:30px">'.$user->display_name.'</td></tr><tr><td style="padding:10px 0;border-right:1px solid #eee;border-bottom:1px solid #eee;text-align:center">充值金币</td><td style="padding:10px 20px 10px 30px;border-right:1px solid #eee;border-bottom:1px solid #eee;line-height:30px">'.$_POST['points'].'</td></tr><tr><td style="padding:10px 0;border-right:1px solid #eee;border-bottom:1px solid #eee;text-align:center">金币总额</td><td style="padding:10px 20px 10px 30px;border-right:1px solid #eee;border-bottom:1px solid #eee;line-height:30px">'.Points::get_user_total_points($_POST['user_id'], POINTS_STATUS_ACCEPTED ).'</td></tr></tbody></table><p style="color:#6e6e6e;font-size:13px;line-height:24px">如果您的金币金额有异常，请您在第一时间和我们取得联系哦，联系邮箱：'.get_bloginfo('admin_email').'</p></div><div class="emailad" style="margin-top:4px"><a href="'.home_url().'"><img src="http://reg.163.com/images/secmail/adv.png" alt="" style="margin:auto;width:100%;max-width:700px;height:auto"></a></div></div></div>';
						$headers = "Content-Type:text/html;charset=UTF-8\n";
						if($_POST['status'] == 'accepted'){
						wp_mail( $user->user_email , 'Hi,'.$user->display_name.'，充值成功到账通知！', $message, $headers);
						}
						/*发邮件*/
				} else {  // 增加金币
					if ( isset( $_POST['user_mail'] ) ) {//如果输入邮箱的话
						$usermail = $_POST['user_mail'];
						$userid = $xuser->ID;
						$username = $xuser->display_name;
					}elseif ( isset( $_POST['user_id'] ) ) {//如果输入用户ID的话
						$user = get_user_by( 'id', $_POST['user_id'] );
						$usermail = $user->user_email;
						$userid = $_POST['user_id'];
						$username = $user->display_name;
					}
					Points::set_points($_POST['points'], $userid, $data);
					$message = '<div class="emailcontent" style="width:100%;max-width:720px;text-align:left;margin:0 auto;padding-top:80px;padding-bottom:20px"><div class="emailtitle"><h1 style="color:#fff;background:#51a0e3;line-height:70px;font-size:24px;font-weight:400;padding-left:40px;margin:0">金币金额调整通知</h1><div class="emailtext" style="background:#fff;padding:20px 32px 40px"><div style="padding:0;font-weight:700;color:#6e6e6e;font-size:16px">尊敬的'.$username.',您好！</div><p style="color:#6e6e6e;font-size:13px;line-height:24px">您的金币金额被管理员调整，请查收！</p><table cellpadding="0" cellspacing="0" border="0" style="width:100%;border-top:1px solid #eee;border-left:1px solid #eee;color:#6e6e6e;font-size:16px;font-weight:normal"><thead><tr><th colspan="2" style="padding:10px 0;border-right:1px solid #eee;border-bottom:1px solid #eee;text-align:center;background:#f8f8f8">您的金币详细情况</th></tr></thead><tbody><tr><td style="padding:10px 0;border-right:1px solid #eee;border-bottom:1px solid #eee;text-align:center;width:100px">用户名</td><td style="padding:10px 20px 10px 30px;border-right:1px solid #eee;border-bottom:1px solid #eee;line-height:30px">'.$username.'</td></tr><tr><td style="padding:10px 0;border-right:1px solid #eee;border-bottom:1px solid #eee;text-align:center">调整金币</td><td style="padding:10px 20px 10px 30px;border-right:1px solid #eee;border-bottom:1px solid #eee;line-height:30px">'.$_POST['points'].'</td></tr><tr><td style="padding:10px 0;border-right:1px solid #eee;border-bottom:1px solid #eee;text-align:center">金币总额</td><td style="padding:10px 20px 10px 30px;border-right:1px solid #eee;border-bottom:1px solid #eee;line-height:30px">'.Points::get_user_total_points($userid, POINTS_STATUS_ACCEPTED ).'</td></tr></tbody></table><p style="color:#6e6e6e;font-size:13px;line-height:24px">如果您的金币金额有异常，请您在第一时间和我们取得联系哦，联系邮箱：'.get_bloginfo('admin_email').'</p></div><div class="emailad" style="margin-top:4px"><a href="'.home_url().'"><img src="http://reg.163.com/images/secmail/adv.png" alt="" style="margin:auto;width:100%;max-width:700px;height:auto"></a></div></div></div>';
					$headers = "Content-Type:text/html;charset=UTF-8\n";
					wp_mail( $usermail , 'Hi,'.$username.'，金币账户金额增加通知！', $message ,$headers);
				}
			}
			$alert= "金币已更新";
		}
		if ( isset( $_GET["action"] ) ) {
			$action = $_GET["action"];
			if ( $action !== null ) {
				switch ( $action ) {
					case 'edit' :
						if ( isset( $_GET['point_id'] ) && ( $_GET['point_id'] !== null ) ) {
							return self::points_admin_points_edit( intval( $_GET['point_id'] ) );

						} else {
							return self::points_admin_points_edit();
						}
						break;
					case 'success' :
						if ( isset( $_GET['point_id'] ) && ( $_GET['point_id'] !== null ) ) {
							if ( current_user_can( 'administrator' ) ) {
								Points::update_points( $_GET['point_id'], array( 'status' => 'accepted') );
								$alert= "金币已到账";
								echo '<script type="text/javascript">setTimeout("window.opener=null;window.close()",500);</script>';//关闭浏览器窗口
								global $wpdb;
								$useresult = $wpdb->get_row( "SELECT user_id FROM " . Points_Database::points_get_table( "users" ) . " WHERE point_id=".$_GET['point_id']." AND description LIKE '%chongzhi%' LIMIT 0, 1;", ARRAY_A )['user_id'];
								$pointss = $wpdb->get_row( "SELECT points FROM " . Points_Database::points_get_table( "users" ) . " WHERE point_id=".$_GET['point_id']." LIMIT 0, 1;", ARRAY_A )['points'];
								$user = get_user_by( 'id', $useresult  );
								$message = '<div class="emailcontent" style="width:100%;max-width:720px;text-align:left;margin:0 auto;padding-top:80px;padding-bottom:20px"><div class="emailtitle"><h1 style="color:#fff;background:#51a0e3;line-height:70px;font-size:24px;font-weight:400;padding-left:40px;margin:0">充值到账通知</h1><div class="emailtext" style="background:#fff;padding:20px 32px 40px"><div style="padding:0;font-weight:700;color:#6e6e6e;font-size:16px">尊敬的'.$user->display_name.',您好！</div><p style="color:#6e6e6e;font-size:13px;line-height:24px">您的金币充值已成功到账，请查收！</p><table cellpadding="0" cellspacing="0" border="0" style="width:100%;border-top:1px solid #eee;border-left:1px solid #eee;color:#6e6e6e;font-size:16px;font-weight:normal"><thead><tr><th colspan="2" style="padding:10px 0;border-right:1px solid #eee;border-bottom:1px solid #eee;text-align:center;background:#f8f8f8">您的金币详细情况</th></tr></thead><tbody><tr><td style="padding:10px 0;border-right:1px solid #eee;border-bottom:1px solid #eee;text-align:center;width:100px">用户名</td><td style="padding:10px 20px 10px 30px;border-right:1px solid #eee;border-bottom:1px solid #eee;line-height:30px">'.$user->display_name.'</td></tr><tr><td style="padding:10px 0;border-right:1px solid #eee;border-bottom:1px solid #eee;text-align:center">充值金币</td><td style="padding:10px 20px 10px 30px;border-right:1px solid #eee;border-bottom:1px solid #eee;line-height:30px">'.$pointss.'</td></tr><tr><td style="padding:10px 0;border-right:1px solid #eee;border-bottom:1px solid #eee;text-align:center">金币总额</td><td style="padding:10px 20px 10px 30px;border-right:1px solid #eee;border-bottom:1px solid #eee;line-height:30px">'.Points::get_user_total_points($useresult, POINTS_STATUS_ACCEPTED ).'</td></tr></tbody></table><p style="color:#6e6e6e;font-size:13px;line-height:24px">如果您的金币金额有异常，请您在第一时间和我们取得联系哦，联系邮箱：'.get_bloginfo('admin_email').'</p></div><div class="emailad" style="margin-top:4px"><a href="'.home_url().'"><img src="http://reg.163.com/images/secmail/adv.png" alt="" style="margin:auto;width:100%;max-width:700px;height:auto"></a></div></div></div>';
								$headers = "Content-Type:text/html;charset=UTF-8\n";
								wp_mail( $user->user_email , 'Hi,'.$user->display_name.'，充值成功到账通知！', $message, $headers);
							}
						}
						break;
					case 'delete' :
						if ( $_GET['point_id'] !== null ) {
							if ( current_user_can( 'administrator' ) ) {
								Points::remove_points( $_GET['point_id'] );
								$alert= "金币已删除";
								echo '<script type="text/javascript">setTimeout("window.opener=null;window.close()",500);</script>';//关闭浏览器窗口
								/*删除金币*/
								global $wpdb;
								$useresult = $wpdb->get_row( "SELECT user_id FROM " . Points_Database::points_get_table( "users" ) . " WHERE point_id=".$_GET['point_id']." AND description LIKE '%chongzhi%' LIMIT 0, 1;", ARRAY_A )['user_id'];
								$users = get_user_by( 'id', $useresult  );
								$message = '<div class="emailcontent" style="width:100%;max-width:720px;text-align:left;margin:0 auto;padding-top:80px;padding-bottom:20px"><div class="emailtitle"><h1 style="color:#fff;background:#51a0e3;line-height:70px;font-size:24px;font-weight:400;padding-left:40px;margin:0">充值失败通知</h1><div class="emailtext" style="background:#fff;padding:20px 32px 40px"><div style="padding:0;font-weight:700;color:#6e6e6e;font-size:16px">尊敬的'.$users->display_name.',您好！</div><p style="color:#6e6e6e;font-size:13px;line-height:24px">您的充值因未向指定支付宝/微信二维码扫码转账导致充值失败，请重试！</p><table cellpadding="0" cellspacing="0" border="0" style="width:100%;border-top:1px solid #eee;border-left:1px solid #eee;color:#6e6e6e;font-size:16px;font-weight:normal"><thead><tr><th colspan="2" style="padding:10px 0;border-right:1px solid #eee;border-bottom:1px solid #eee;text-align:center;background:#f8f8f8">您的金币详细情况</th></tr></thead><tbody><tr><td style="padding:10px 0;border-right:1px solid #eee;border-bottom:1px solid #eee;text-align:center;width:100px">用户名</td><td style="padding:10px 20px 10px 30px;border-right:1px solid #eee;border-bottom:1px solid #eee;line-height:30px">'.$users->display_name.'</td></tr><tr><td style="padding:10px 0;border-right:1px solid #eee;border-bottom:1px solid #eee;text-align:center">金币总额</td><td style="padding:10px 20px 10px 30px;border-right:1px solid #eee;border-bottom:1px solid #eee;line-height:30px">'.Points::get_user_total_points($useresult, POINTS_STATUS_ACCEPTED ).'</td></tr></tbody></table><p style="color:#6e6e6e;font-size:13px;line-height:24px">如果您的金币金额有异常，请您在第一时间和我们取得联系哦，联系邮箱：'.get_bloginfo('admin_email').'</p></div><div class="emailad" style="margin-top:4px"><a href="'.home_url().'"><img src="http://reg.163.com/images/secmail/adv.png" alt="" style="margin:auto;width:100%;max-width:700px;height:auto"></a></div></div></div>';
								$headers = "Content-Type:text/html;charset=UTF-8\n";
								wp_mail( $users->user_email , '金币充值失败通知！', $message, $headers);/*邮件通知*/
							}
						}
						break;
					case 'cancel' :
						if ( $_GET['point_id'] !== null ) {
							if ( current_user_can( 'administrator' ) ) {
								Points::remove_points( $_GET['point_id'] );
								$alert= "充值已取消";
								echo '<script type="text/javascript">setTimeout("window.opener=null;window.close()",500);</script>';//关闭浏览器窗口
								/*删除金币*/
								global $wpdb;
								$useresult = $wpdb->get_row( "SELECT user_id FROM " . Points_Database::points_get_table( "users" ) . " WHERE point_id=".$_GET['point_id']." AND description LIKE '%chongzhi%' LIMIT 0, 1;", ARRAY_A )['user_id'];
								$users = get_user_by( 'id', $useresult  );
								$message = '<div class="emailcontent" style="width:100%;max-width:720px;text-align:left;margin:0 auto;padding-top:80px;padding-bottom:20px"><div class="emailtitle"><h1 style="color:#fff;background:#51a0e3;line-height:70px;font-size:24px;font-weight:400;padding-left:40px;margin:0">充值订单关闭通知</h1><div class="emailtext" style="background:#fff;padding:20px 32px 40px"><div style="padding:0;font-weight:700;color:#6e6e6e;font-size:16px">尊敬的'.$users->display_name.',您好！</div><p style="color:#6e6e6e;font-size:13px;line-height:24px">您的充值因重复提交等原因已被管理员关闭，请见谅！</p><table cellpadding="0" cellspacing="0" border="0" style="width:100%;border-top:1px solid #eee;border-left:1px solid #eee;color:#6e6e6e;font-size:16px;font-weight:normal"><thead><tr><th colspan="2" style="padding:10px 0;border-right:1px solid #eee;border-bottom:1px solid #eee;text-align:center;background:#f8f8f8">您的金币详细情况</th></tr></thead><tbody><tr><td style="padding:10px 0;border-right:1px solid #eee;border-bottom:1px solid #eee;text-align:center;width:100px">用户名</td><td style="padding:10px 20px 10px 30px;border-right:1px solid #eee;border-bottom:1px solid #eee;line-height:30px">'.$users->display_name.'</td></tr><tr><td style="padding:10px 0;border-right:1px solid #eee;border-bottom:1px solid #eee;text-align:center">金币总额</td><td style="padding:10px 20px 10px 30px;border-right:1px solid #eee;border-bottom:1px solid #eee;line-height:30px">'.Points::get_user_total_points($useresult, POINTS_STATUS_ACCEPTED ).'</td></tr></tbody></table><p style="color:#6e6e6e;font-size:13px;line-height:24px">如果您的金币金额有异常，请您在第一时间和我们取得联系哦，联系邮箱：'.get_bloginfo('admin_email').'</p></div><div class="emailad" style="margin-top:4px"><a href="'.home_url().'"><img src="http://reg.163.com/images/secmail/adv.png" alt="" style="margin:auto;width:100%;max-width:700px;height:auto"></a></div></div></div>';
								$headers = "Content-Type:text/html;charset=UTF-8\n";
								wp_mail( $users->user_email , '金币充值订单关闭通知！', $message, $headers);/*邮件通知*/
							}
						}
						break;
				}
			}
		}

		if ($alert != "") {
			echo '<div style="background-color: #ffffe0;border: 1px solid #993;padding: 1em;margin-right: 1em;">' . $alert . '</div>';
		}

		$current_url = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		$cancel_url  = remove_query_arg( 'point_id', remove_query_arg( 'action', $current_url ) );
		$current_url = remove_query_arg( 'point_id', $current_url );
		$current_url = remove_query_arg( 'action', $current_url );

		$exampleListTable = new Points_List_Table();
		$exampleListTable->prepare_items();
		?>
		<div class="wrap">
			<div id="icon-users" class="icon32"></div>
			<h2>金币管理</h2>
			<div class="manage add">
				<a class="add button" href="<?php echo esc_url( add_query_arg( 'action', 'edit', $current_url ) ); ?>" title="<?php echo '点击手动添加金币';?>">添加金币</a>
			</div>
			<?php echo '<style type="text/css">tbody#the-list tr:hover{background:rgba(132,219,162,.61)}</style>';$exampleListTable->display(); ?>
		</div>
		<?php
	}

	/**
	 * Show Points options page.
	 */
	public static function points_admin_options() {
		$alert = "";
		if ( isset( $_POST['submit'] ) ) {
			add_option( 'points-comments_enable', $_POST['points_comments_enable'] );
			update_option( 'points-comments_enable', $_POST['points_comments_enable'] );

			add_option( 'points-comments', $_POST['points_comments'] );
			update_option( 'points-comments', $_POST['points_comments'] );

			add_option( 'points-welcome', $_POST['points_welcome'] );
			update_option( 'points-welcome', $_POST['points_welcome'] );

			add_option( 'points-post', $_POST['points_post'] );
			update_option( 'points-post', $_POST['points_post'] );

			$label = ( isset( $_POST['points_label'] ) && $_POST['points_label'] !== "" )?$_POST['points_label']:"";
			add_option( 'points-points_label', $label );
			update_option( 'points-points_label', $label );

			add_option( 'points-points_status', $_POST['points_status'] );
			update_option( 'points-points_status', $_POST['points_status'] );

			$alert= "保存";
		}

		if ($alert != "") {
			echo '<div style="background-color: #ffffe0;border: 1px solid #993;padding: 1em;margin-right: 1em;">' . $alert . '</div>';
		}
		?>
			<h2>金币设置</h2>
			<hr>
			<style type="text/css">.points-admin-line{clear:both}.points-admin-label{min-width:200px;width:25%}</style>
			<form method="post" action="">
				<div class="wrap" style="border: 1px solid #ccc; padding:10px;">
					<h3>常规</h3>
					<div class="points-admin-line">
						<div class="points-admin-label">金币后缀</div>
						<div class="points-admin-value">
							<?php
							$label = get_option('points-points_label', '金币');
							?>
							<input type="text" name="points_label" value="<?php echo $label; ?>" class="regular-text" />
						</div>
					</div>

					<div class="points-admin-line">
						<div class="points-admin-label">
							<?php echo __( '默认金币状态', 'points' ); ?>
						</div>
						<div class="points-admin-value">
							<select name="points_status">
							<?php
							$output = "";
							$status = get_option( 'points-points_status', POINTS_STATUS_ACCEPTED );
							$status_descriptions = array(
									POINTS_STATUS_ACCEPTED => __( 'Accepted', 'points' ),
									POINTS_STATUS_PENDING   => __( 'Pending', 'points' ),
									POINTS_STATUS_REJECTED => __( 'Rejected', 'points' ),
							);
							foreach ( $status_descriptions as $key => $label ) {
								$selected = $key == $status ? ' selected="selected" ' : '';
								$output .= '<option ' . $selected . ' value="' . esc_attr( $key ) . '">' . $label . '</option>';
							}
							echo $output;
							?>
							</select>
						</div>
					</div>
				</div>
				<div class="wrap" style="border: 1px solid #ccc; padding:10px;">
					<h3>评论</h3>
					<div class="points-admin-line">
						<div class="points-admin-label">启用评论金币</div>
						<div class="points-admin-label">
							<?php
							$enable_comments = get_option('points-comments_enable', 1);
							?>
							<input type="checkbox" name="points_comments_enable" value="1" <?php echo $enable_comments=="1"?" checked ":""?>>
						</div>
					</div>
					<div class="points-admin-line">
						<div class="points-admin-label">
							评论金币
						</div>
						<div class="points-admin-label">
							<?php
							$enable_comments = get_option('points-comments_enable', 1);
							?>
							<input type="text" name="points_comments" value="<?php echo get_option('points-comments', 1); ?>" size="4">
						</div>
					</div>
				</div>
				<div class="wrap" style="border: 1px solid #ccc; padding:10px;">
					<h3>其他</h3>
					<div class="points-admin-line">
						<div class="points-admin-label">
							<?php echo '注册欢迎金币'; ?>
						</div>
						<div class="points-admin-label">
							<input type="text" name="points_welcome" value="<?php echo get_option('points-welcome', "0"); ?>" size="4">
						</div>

						<div class="points-admin-label">
							<?php echo '论坛发帖金币'; ?>
						</div>
						<div class="points-admin-label">
							<input type="text" name="points_post" value="<?php echo get_option('points-post', "0"); ?>" size="4">
						</div>

					</div>
				</div>

				<div class="points-admin-line">
					<?php submit_button("保存"); ?>
				</div>
				<?php settings_fields( 'points-settings' ); ?>
			</form>
		<?php
	}

	public static function points_admin_points_edit( $point_id = null ) {

		global $wpdb;

		$output = '';

		if ( !current_user_can( 'administrator' ) ) {
			wp_die( __( 'Access denied.', 'points' ) );
		}

		$current_url = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		$cancel_url  = remove_query_arg( 'point_id', remove_query_arg( 'action', $current_url ) );
		$current_url = remove_query_arg( 'point_id', $current_url );
		$current_url = remove_query_arg( 'action', $current_url );

		$saved = false;  // temporal

		if ( $point_id !== null ) {
			$points = Points::get_point( $point_id );

			if ( $points !== null ) {
				$user_id = $points->user_id;
				$num_points = $points->points;
				$description = $points->description;
				$datetime = $points->datetime;
				$status = $points->status;
			}
		} else {
			$user_id = "";
			$num_points = 0;
			$description = "";
			$datetime = "";
			$status = POINTS_STATUS_ACCEPTED;
		}

		if ( empty( $point_id ) ) {
			$pointsclass = 'newpoint';
		} else {
			$pointsclass = 'editpoint';
		}
		$output .= '<style type="text/css">.newpoint .userid,.editpoint .usermail{display:none}</style>';
		$output .= '<div class="points '.$pointsclass.'">';
		$output .= '<h2>';
		if ( empty( $point_id ) ) {
			$output .= '新金币';
		} else {
			$output .= '编辑金币';
		}
		$output .= '</h2>';

		$output .= '<form id="points" action="' . $current_url . '" method="post">';
		$output .= '<div>';

		if ( $point_id ) {
			$output .= sprintf( '<input type="hidden" name="point_id" value="%d" />', intval( $point_id ) );
		}

		$output .= '<input type="hidden" name="action" value="edit" />';

		$output .= '<p class="usermail">';
		$output .= '<label>';
		$output .= '<span class="title">用户邮箱</span>';
		$output .= ' ';
		$output .= sprintf( '<input type="text" name="user_mail" value="%s" />',  $user_mail );
		$output .= ' ';
		$output .= '<span class="description">用户在网站的注册邮箱</span>';
		$output .= '</label>';
		$output .= '</p>';

		$output .= '<p class="userid">';
		$output .= '<label>';
		$output .= '<span class="title">用户ID</span>';
		$output .= ' ';
		$output .= sprintf( '<input type="text" name="user_id" value="%s" />',  $user_id );
		$output .= ' ';
		$output .= '<span class="description">请勿填写，仅供自动生成</span>';
		$output .= '</label>';
		$output .= '</p>';

		$output .= '<p>';
		$output .= '<label>';
		$output .= '<span class="title">日期&时间</span>';
		$output .= ' ';
		$output .= sprintf( '<input type="text" name="datetime" value="%s" id="datetimepicker" />', esc_attr( $datetime ) );
		$output .= ' ';
		$output .= '<span class="description">格式 : YYYY-MM-DD HH:MM:SS【可忽略，自动生成】</span>';
		$output .= '</label>';
		$output .= '</p>';

		$output .= '<p>';
		$output .= '<label>';
		$output .= '<span class="title">描述</span>';
		$output .= '<br>';
		$output .= '<textarea name="description">';
		$output .= stripslashes( $description );
		$output .= '</textarea>';
		$output .= '</label>';
		$output .= '</p>';

		$output .= '<p>';
		$output .= '<label>';
		$output .= '<span class="title">金币</span>';
		$output .= ' ';
		$output .= sprintf( '<input type="text" name="points" value="%s" />', esc_attr( $num_points ) );
		$output .= '</label>';
		$output .= '</p>';

		$status_descriptions = array(
				POINTS_STATUS_ACCEPTED => __( 'Accepted', 'points' ),
				POINTS_STATUS_PENDING  => __( 'Pending', 'points' ),
				POINTS_STATUS_REJECTED => __( 'Rejected', 'points' ),
		);
		$output .= '<p>';
		$output .= '<label>';
		$output .= '<span class="title">' . __( '状态', 'points' ) . '</span>';
		$output .= ' ';
		$output .= '<select name="status">';
		foreach ( $status_descriptions as $key => $label ) {
			$selected = $key == $status ? ' selected="selected" ' : '';
			$output .= '<option ' . $selected . ' value="' . esc_attr( $key ) . '">' . $label . '</option>';
		}
		$output .= '</select>';
		$output .= '</label>';
		$output .= '</p>';

		$output .= wp_nonce_field( 'save', 'points-nonce', true, false );

		$output .= sprintf( '<input class="button" type="submit" name="save" value="%s"/>', '保存' );
		$output .= ' ';
		$output .= sprintf( '<a class="cancel" href="%s">%s</a>', $cancel_url, $saved ? '返回' : '取消' );

		$output .= '</div>';
		$output .= '</form>';

		$output .= '</div>';

		echo $output;
	}

}