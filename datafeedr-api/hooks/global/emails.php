<?php

defined( 'ABSPATH' ) || exit;

function dfrapi_email_user_about_usage() {

	$percentage = dfrapi_get_api_usage_as_percentage( 0 );
	$status     = get_option( 'dfrapi_account', array() );

	$request_count      = ( isset( $status['request_count'] ) ) ? abs( $status['request_count'] ) : 0;
	$remaining_requests = ( isset( $status['max_requests'] ) ) ? abs( $status['max_requests'] - $request_count ) : 0;

	$reset_date = '';
	if ( isset( $status['bill_day'] ) ) {
		$today    = date( 'j' );
		$num_days = date( 't' );
		if ( $status['bill_day'] > $num_days ) {
			$bill_day = $num_days;
		} else {
			$bill_day = $status['bill_day'];
		}
		if ( $bill_day == 0 ) {
			$reset_date .= '<em>' . __( 'Never', 'datafeedr-api' ) . '</em>';
		} elseif ( $today >= $bill_day ) {
			$reset_date .= date( 'F', strtotime( '+1 month' ) ) . ' ' . $bill_day . ', ' . date( 'Y', strtotime( '+1 month' ) );
		} else {
			$reset_date .= date( 'F' ) . ' ' . $bill_day . ', ' . date( 'Y' );
		}
	}

	$default = array(
		'90_percent'  => '',
		'100_percent' => ''
	);

	// Don't do anything if less than 90%.
	if ( $percentage < 90 ) {
		update_option( 'dfrapi_usage_notification_tracker', $default );

		return;
	}

	$tracker = get_option( 'dfrapi_usage_notification_tracker', $default );

	$params            = array();
	$params['to']      = get_bloginfo( 'admin_email' );
	$params['message'] = "<p>" . __( "This is an automated message generated by: ", 'datafeedr-api' ) . get_bloginfo( 'wpurl' ) . "</p>";

	if ( $percentage >= 100 && empty( $tracker['100_percent'] ) ) {

		$params['subject'] = get_bloginfo( 'name' ) . __( ': Datafeedr API Usage (Critical)', 'datafeedr-api' );

		$params['message'] .= "<p>" . __( "You have used <strong>100%</strong> of your allocated Datafeedr API requests for this period. <u>You are no longer able to query the Datafeedr API to get product information.</u>", 'datafeedr-api' ) . "</p>";
		$params['message'] .= "<p><strong>" . __( "What to do next?", 'datafeedr-api' ) . "</strong></p>";
		$params['message'] .= "<p>" . __( "We strongly recommend that you upgrade to prevent your product information from becoming outdated.", 'datafeedr-api' ) . "</p>";
		$params['message'] .= "<p><a href=\"" . dfrapi_user_pages( 'change' ) . "?utm_source=email&utm_medium=link&utm_campaign=upgrade100percentnotice\"><strong>" . __( "UPGRADE NOW", 'datafeedr-api' ) . "</strong></a></p>";
		$params['message'] .= "<p>" . __( "Upgrading only takes a minute. You will have <strong>instant access</strong> to more API requests. Any remaining credit for your current plan will be applied to your new plan.", 'datafeedr-api' ) . "</p>";
		$params['message'] .= "<p>" . __( "You are under no obligation to upgrade. You may continue using your current plan for as long as you would like.", 'datafeedr-api' ) . "</p>";
		$params['message'] .= "<p>" . __( "If you have any questions about your account, please ", 'datafeedr-api' );
		$params['message'] .= "<a href=\"" . DFRAPI_EMAIL_US_URL . "?utm_source=email&utm_medium=link&utm_campaign=upgrade100percentnotice\">" . __( "contact us", 'datafeedr-api' ) . "</a>.</p>";
		$params['message'] .= "<p>" . __( "Thanks,<br />Eric &amp; Stefan<br />The Datafeedr Team", 'datafeedr-api' ) . "</p>";
		$params['message'] .= "<p>";
		$params['message'] .= "<a href=\"" . admin_url( 'admin.php?page=dfrapi_account' ) . "\">" . __( "Account Information", 'datafeedr-api' ) . "</a> | ";
		$params['message'] .= "<a href=\"" . dfrapi_user_pages( 'change' ) . "?utm_source=email&utm_medium=link&utm_campaign=upgrade100percentnotice\">" . __( "Upgrade Account", 'datafeedr-api' ) . "</a>";
		$params['message'] .= "</p>";

		$tracker['100_percent'] = 1;
		update_option( 'dfrapi_usage_notification_tracker', $tracker );

		add_filter( 'wp_mail_content_type', 'dfrapi_set_html_content_type' );
		wp_mail( $params['to'], $params['subject'], $params['message'] );
		remove_filter( 'wp_mail_content_type', 'dfrapi_set_html_content_type' );

	} elseif ( $percentage >= 90 && $percentage < 100 && empty( $tracker['90_percent'] ) ) {

		$params['subject'] = get_bloginfo( 'name' ) . __( ': Datafeedr API Usage (Warning)', 'datafeedr-api' );
		$params['message'] .= "<p>" . __( "You have used <strong>90%</strong> of your allocated Datafeedr API requests for this period.", 'datafeedr-api' ) . "</p>";
		$params['message'] .= "<p><strong>" . __( "API Usage", 'datafeedr-api' ) . "</strong></p>";
		$params['message'] .= "<ul>";
		$params['message'] .= "<li>" . __( "API requests used: ", 'datafeedr-api' ) . $request_count . "</li>";
		$params['message'] .= "<li>" . __( "API requests remaining: ", 'datafeedr-api' ) . $remaining_requests . "</li>";
		$params['message'] .= "<li>" . __( "API requests will reset on: ", 'datafeedr-api' ) . $reset_date . "</li>";
		$params['message'] .= "</ul>";
		$params['message'] .= "<p><strong>" . __( "What to do next?", 'datafeedr-api' ) . "</strong></p>";
		$params['message'] .= "<p>" . __( "We recommend that you upgrade to prevent your product information from becoming outdated.", 'datafeedr-api' ) . "</p>";
		$params['message'] .= "<p><a href=\"" . dfrapi_user_pages( 'change' ) . "?utm_source=email&utm_medium=link&utm_campaign=upgrade90percentnotice\"><strong>" . __( "UPGRADE NOW", 'datafeedr-api' ) . "</strong></a></p>";
		$params['message'] .= "<p>" . __( "Upgrading only takes a minute. You will have <strong>instant access</strong> to more API requests. Any remaining credit for your current plan will be applied to your new plan.", 'datafeedr-api' ) . "</p>";
		$params['message'] .= "<p>" . __( "You are under no obligation to upgrade. You may continue using your current plan for as long as you would like.", 'datafeedr-api' ) . "</p>";
		$params['message'] .= "<p>" . __( "If you have any questions about your account, please ", 'datafeedr-api' );
		$params['message'] .= "<a href=\"" . DFRAPI_EMAIL_US_URL . "?utm_source=email&utm_medium=link&utm_campaign=upgrade90percentnotice\">" . __( "contact us", 'datafeedr-api' ) . "</a>.</p>";
		$params['message'] .= "<p>" . __( "Thanks,<br />Eric &amp; Stefan<br />The Datafeedr Team", 'datafeedr-api' ) . "</p>";
		$params['message'] .= "<p>";
		$params['message'] .= "<a href=\"" . admin_url( 'admin.php?page=dfrapi_account' ) . "\">" . __( "Account Information", 'datafeedr-api' ) . "</a> | ";
		$params['message'] .= "<a href=\"" . dfrapi_user_pages( 'change' ) . "?utm_source=email&utm_medium=link&utm_campaign=upgrade90percentnotice\">" . __( "Upgrade Account", 'datafeedr-api' ) . "</a>";
		$params['message'] .= "</p>";

		$tracker['90_percent'] = 1;
		update_option( 'dfrapi_usage_notification_tracker', $tracker );

		add_filter( 'wp_mail_content_type', 'dfrapi_set_html_content_type' );
		wp_mail( $params['to'], $params['subject'], $params['message'] );
		remove_filter( 'wp_mail_content_type', 'dfrapi_set_html_content_type' );

	}
}

add_action( 'init', 'dfrapi_email_user_about_usage' );