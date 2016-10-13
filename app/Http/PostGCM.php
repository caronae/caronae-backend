<?php

namespace App\Http;

class PostGCM
{
	public static function doPost($post)
	{
		//------------------------------
		// The GCM API key, generated using 
		// Google APIs Console, should be
		// placed inside the .env file.
		//------------------------------

		$apiKey = env('GCM_API_KEY');

		//------------------------------
		// Define URL to GCM endpoint
		//------------------------------

		$url = 'https://gcm-http.googleapis.com/gcm/send';

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

	public static function sendNotification($gcmTokens, $data) {
		$body = [
			'notification' 		=> ['body' => $data['message']],
			'content_available' => true,
			'data' 				=> $data
		];

		if (is_array($gcmTokens)) {
			$body['registration_ids'] = $gcmTokens;
		} else {
			$body['to'] = $gcmTokens;
		}

		return self::doPost($body);
	}
}
