<?php
/*!
* HybridAuth
* http://hybridauth.sourceforge.net | https://github.com/hybridauth/hybridauth
*  (c) 2009-2011 HybridAuth authors | hybridauth.sourceforge.net/licenses.html
*/

/**
 * Hybrid_Providers_Basecamp
 */
class Hybrid_Providers_Basecamp extends Hybrid_Provider_Model_OAuth2
{ 
	// default permissions  
	// (no scope) => public read-only access (includes public user profile info, public repo info, and gists).
	public $scope = "";

	/**
	* IDp wrappers initializer 
	*/
	function initialize() 
	{
		parent::initialize();

		// Provider api end-points
		$this->api->api_base_url  = "";
		$this->api->authorize_url = "https://launchpad.37signals.com/authorization/new";
		$this->api->token_url     = "https://launchpad.37signals.com/authorization/token?type=web_server";
	}

	/**
	* begin login step 
	*/
	function loginBegin()
	{
		$parameters = array("scope" => $this->scope, "redirect_uri" => $this->endpoint, "type" => "web_server");
		$optionals  = array("scope", "type", "redirect_uri", "approval_prompt", "hd");

		foreach ($optionals as $parameter){
			if( isset( $this->config[$parameter] ) && ! empty( $this->config[$parameter] ) ){
				$parameters[$parameter] = $this->config[$parameter];
			}
		}

		Hybrid_Auth::redirect( $this->api->authorizeUrl( $parameters ) ); 
	}

	/**
	* load the user profile from the IDp api client
	*/
	function getUserProfile()
	{	

		$data = $this->api->get( "https://launchpad.37signals.com/authorization.json", array('type' => 'web_server'));

		if ( ! isset( $data->identity ) ){
			throw new Exception( "User profile request failed! {$this->providerId} returned an invalid response.", 6 );
		}

		$this->user->profile->identifier  = @ $data->identity->id; 
		$this->user->profile->displayName = @ trim($data->identity->first_name . ' ' . $data->identity->last_name);
		$this->user->profile->email       = @ $data->identity->email_address;

		if( ! $this->user->profile->displayName ){
			$this->user->profile->displayName = @ $data->identity->email_address;
		}

		return $this->user->profile;
	}
}
