<?php

class HM_Mandrill {

	public static $sent = array();

	//TODO: CHECK if on dev version
	public static function send( $recipients, $subject, $message, $message_args = array() ) {

		if ( ! defined( 'HM_ONLY_STORE_EMAIL') && defined( 'HM_DEV' ) && HM_DEV )
			$recipients = ( defined( 'HM_DEV_EMAIL') ) ? HM_DEV_EMAIL : 'dev@humanmade.co.uk';

		$recipients = (array) $recipients;

		require_once( dirname( __FILE__ ) . '/class-mandrill.php' );

		foreach ( $recipients as &$recipient )
			$recipient = array( 'email' => $recipient );


		/**
		 * For testing purposes etc, we have teh option to not actually send email, but store it in the static variable $sent
		 */
		if ( defined( 'HM_ONLY_STORE_EMAIL' ) && HM_ONLY_STORE_EMAIL ) {

			foreach ( $recipients as $recipient )
				self::$sent[$recipient['email']][] = array( 'subject' => $subject, 'message' => $message );

			return;
		}

		$reply_to = '';

		if ( ! empty( $message_args['reply_to_email'] ) ) {
			$reply_to = $message_args['reply_to_email'];
		}
		elseif ( ! empty( $message_args['from_email'] ) ) {
			$reply_to = $message_args['from_email'];
		}

		return Mandrill::call( array( 
			'type' => 'messages', 
			'call' => 'send',
			'message' => wp_parse_args( $message_args, array(
				'headers' => array(
					'Reply-To' => $reply_to,
				),
				'preserve_recipients' => false,
				'from_email' => ! empty( $message_args['hard_from_email'] ) ? $message_args['from_email'] : get_option( 'admin_email' ),
				'from_name' => ! empty( $message_args['from_name'] ) ? $message_args['from_name'] : htmlspecialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ),
				'subject' => $subject,
				'to' => $recipients,
				'track_opens' => true,
				'track_clicks' => true,
				'html' => $message
			) )
		) );
	}

	/**
	 * @todo make this used
	 */
	private static function inline_css( $message ) {
		// convert html to inline css
		if ( ! class_exists( 'MCAPI' ) )
			include_once( dirname( __FILE__ ) . '/class-MCAPI.php' );

		$mc = new MCAPI( MC_API_KEY );
		$message = $mc->inlineCss( $message, true );

		return $message;
	}

}