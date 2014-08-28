<?php

/**
 * Plugin Name: HM Mandrill
 * Description: Dropin replacemnt for wp_mail() to send email from Mandrill
 * Author: Human Made Limited
 * Version: 1.0
 * Author URI: http://hmn.md/
 */

if ( ! defined( 'MANDRILL_API_KEY' ) || ! MANDRILL_API_KEY ) {
	return;
}

require_once( dirname( __FILE__ ) . '/inc/class-hm-mandrill.php' );

if ( ! function_exists( 'wp_mail') ) :
	function wp_mail( $to, $subject, $content, $headers='' ) {

		$message_args = array();
		if ( ! is_array( $headers ) ) {
			$headers = explode( "\n", $headers );
		}

		//Get an associative array of any headers from the header string
		foreach( $headers as $header ) {
			$header_exploded = explode( ':', $header );

			if ( $header_exploded[0] && $header_exploded[1] )
				$headers_assoc[$header_exploded[0]] = $header_exploded[1];
		}

		//If a From header has been set, make sure to add it to the mandrill api args
		if ( ! empty( $headers_assoc['From'] ) ) {

			$matches = array();

			preg_match( '/\"(.*)\".*<(.*)>/', $headers_assoc['From'], $matches );

			if ( ! empty( $matches[1] ) && ! is_email( $matches[1] ) )
				$message_args['from_name'] = $matches[1];

			if (  ! empty( $matches[2] ) && is_email( $matches[2] ) )
				$message_args['from_email'] = $matches[2];
		}

		//if a reply-to header is set, make sure to add it to the mandrill api args
		if ( ! empty( $headers_assoc['Reply-To'] ) && is_email( trim( $headers_assoc['Reply-To'] ) ) )
			$message_args['headers']['Reply-To'] =  trim( $headers_assoc['Reply-To'] );

		if ( ! is_array( $to ) && strpos( $to, ', ' ) !== false )
			$to = explode( ', ', $to );

		$email = $content;

		try {
			HM_Mandrill::send( $to, $subject, $content, $message_args );
		} catch( Exception $e ) {
			return false;
		}

		return true;
	}
endif;