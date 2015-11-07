<?php

namespace App\Http;

class PostGCM
{
	function post($message, $id)
	{
		//$ids = array( 'abc', 'def' );
		//$ids = array( $ids );
		$data = array( 'message' => $message );
		
		//------------------------------
		// Replace with real GCM API 
		// key from Google APIs Console
		// 
		// https://code.google.com/apis/console/
		//------------------------------

		$apiKey = 'AIzaSyBtGz81bar_LcwtN_fpPTKRMBL5glp2T18';

		//------------------------------
		// Define URL to GCM endpoint
		//------------------------------

		$url = 'https://android.googleapis.com/gcm/send';

		//------------------------------
		// Set GCM post variables
		// (Device IDs and push payload)
		//------------------------------

		$post = array(
						//'registration_ids'  => $id,
						'to' 	=> $id,
						'data' => $data,
						);

		//------------------------------
		// Set CURL request headers
		// (Authentication and type)
		//------------------------------

		$headers = array( 
							'Authorization: key=' . $apiKey,
							'Content-Type: application/json'
						);

		//------------------------------
		// Initialize curl handle
		//------------------------------

		$ch = curl_init();

		//------------------------------
		// Set URL to GCM endpoint
		//------------------------------

		curl_setopt( $ch, CURLOPT_URL, $url );

		//------------------------------
		// Set request method to POST
		//------------------------------

		curl_setopt( $ch, CURLOPT_POST, true );

		//------------------------------
		// Set our custom headers
		//------------------------------

		curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );

		//------------------------------
		// Get the response back as 
		// string instead of printing it
		//------------------------------

		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );

		//------------------------------
		// Set post data as JSON
		//------------------------------

		curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode( $post ) );

		//------------------------------
		// Actually send the push!
		//------------------------------

		$result = curl_exec( $ch );

		//------------------------------
		// Error? Display it!
		//------------------------------

		if ( curl_errno( $ch ) )
		{
			return 'GCM error: ' . curl_error( $ch );
		}

		//------------------------------
		// Close curl handle
		//------------------------------

		curl_close( $ch );

		//------------------------------
		// Debug GCM response
		//------------------------------

		return $result;
	}
}
