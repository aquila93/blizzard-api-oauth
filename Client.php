<?php
/**
 * Note : Code is released under the GNU LGPL
 *
 * Please do not change the header of this file
 *
 * This library is free software; you can redistribute it and/or modify it under the terms of the GNU
 * Lesser General Public License as published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * See the GNU Lesser General Public License for more details.
 */

/**
 * Based off Light PHP wrapper for the OAuth 2.0 protocol.
 *
 * This client is based on the OAuth2 specification draft v2.15
 * http://tools.ietf.org/html/draft-ietf-oauth-v2-15
 *
 * @author      Pierrick Charron <pierrick@webstart.fr>
 * @author      Anis Berejeb <anis.berejeb@gmail.com>

 *
 * @author      Joe Foster (Ulminia) <ulminia@gmail.com>

 */
namespace OAuth;

require_once( 'GrantType/IGrantType.php');
require_once( 'GrantType/AuthorizationCode.php');
require_once( 'GrantType/ClientCredentials.php');


class oauthApi
{
    /**
     * Different AUTH method
     */
    const AUTH_TYPE_URI                 = 0;
    const AUTH_TYPE_AUTHORIZATION_BASIC = 1;
    const AUTH_TYPE_FORM                = 2;

    /**
     * Different Access token type
     */
    const ACCESS_TOKEN_URI      = 0;
    const ACCESS_TOKEN_BEARER   = 1;
    const ACCESS_TOKEN_OAUTH    = 2;
    const ACCESS_TOKEN_MAC      = 3;

    /**
    * Different Grant types
    */
    const GRANT_TYPE_AUTH_CODE          = 'authorization_code';
    const GRANT_TYPE_PASSWORD           = 'password';
    const GRANT_TYPE_CLIENT_CREDENTIALS = 'client_id';
    const GRANT_TYPE_REFRESH_TOKEN      = 'refresh_token';
	const GRANT_TYPE_C_C 				= 'client_credentials';
	
	const INVALID_GRANT_TYPE			= 'bob';

    /**
     * HTTP Methods
     */
    const HTTP_METHOD_GET    = 'GET';
    const HTTP_METHOD_POST   = 'POST';
    const HTTP_METHOD_PUT    = 'PUT';
    const HTTP_METHOD_DELETE = 'DELETE';
    const HTTP_METHOD_HEAD   = 'HEAD';
    const HTTP_METHOD_PATCH  = 'PATCH';

    /**
     * HTTP Form content types
     */
    const HTTP_FORM_CONTENT_TYPE_APPLICATION = 0;
    const HTTP_FORM_CONTENT_TYPE_MULTIPART = 1;

    /**
     * Client ID
     *
     * @var string
     */
    protected $client_id = null;

    /**
     * Client Secret
     *
     * @var string
     */
    protected $client_secret = null;

    /**
     * Client Authentication method
     *
     * @var int
     */
    protected $client_auth = self::AUTH_TYPE_URI;

    /**
     * Access Token
     *
     * @var string
     */
    protected $access_token = null;

    /**
     * Access Token Type
     *
     * @var int
     */
    protected $access_token_type = self::ACCESS_TOKEN_URI;

    /**
     * Access Token Secret
     *
     * @var string
     */
    protected $access_token_secret = null;

    /**
     * Access Token crypt algorithm
     *
     * @var string
     */
    protected $access_token_algorithm = null;

    /**
     * Access Token Parameter name
     *
     * @var string
     */
    protected $access_token_param_name = 'access_token';

    /**
     * The path to the certificate file to use for https connections
     *
     * @var string  Defaults to .
     */
    protected $certificate_file = null;

    /**
     * cURL options
     *
     * @var array
     */
    protected $curl_options = array();

	/**
	 *	Redirect uri
	 *
	 */
	public $redirect_uri = '';
	
	
	/**
	 *	Base url setting
	 *
	 */
	public $baseurl = array(

			'US' => array(
				'urlbase'					=> 'https://us.api.blizzard.com',
				'AUTHORIZATION_ENDPOINT'	=> 'https://us.battle.net/oauth/authorize',
				'TOKEN_ENDPOINT'			=> 'https://us.battle.net/oauth/token',
				'ACCOUNT_ENDPOINT'			=> 'https://us.battle.net',
			),
			'EU' => array(
				'urlbase'					=> 'https://eu.api.blizzard.com',
				'AUTHORIZATION_ENDPOINT'	=> 'https://eu.battle.net/oauth/authorize',
				'TOKEN_ENDPOINT'			=> 'https://eu.battle.net/oauth/token',
				'ACCOUNT_ENDPOINT'			=> 'https://eu.battle.net',
			),
			'KR' => array(
				'urlbase'					=> 'https://kr.api.blizzard.com',
				'AUTHORIZATION_ENDPOINT'	=> 'https://kr.battle.net/oauth/authorize',
				'TOKEN_ENDPOINT'			=> 'https://kr.battle.net/oauth/token',
				'ACCOUNT_ENDPOINT'			=> 'https://kr.battle.net',
			),
			'TW' => array(
				'urlbase'					=> 'https://tw.api.blizzard.com',
				'AUTHORIZATION_ENDPOINT'	=> 'https://tw.battle.net/oauth/authorize',
				'TOKEN_ENDPOINT'			=> 'https://tw.battle.net/oauth/token',
				'ACCOUNT_ENDPOINT'			=> 'https://tw.battle.net',
			),
			'CN' => array(
				'urlbase'					=> 'https://cn.api.blizzard.com',
				'AUTHORIZATION_ENDPOINT'	=> 'https://cn.battle.net/oauth/authorize',
				'TOKEN_ENDPOINT'			=> 'https://cn.battle.net/oauth/token',
				'ACCOUNT_ENDPOINT'			=> 'https://cn.battle.net',
			),
			'SEA' => array(
				'urlbase'					=> 'https://sea.api.blizzard.com',
				'AUTHORIZATION_ENDPOINT'	=> 'https://sea.battle.net/oauth/authorize',
				'TOKEN_ENDPOINT'			=> 'https://sea.battle.net/oauth/token',
				'ACCOUNT_ENDPOINT'			=> 'https://sea.battle.net',
			),
	);
	
	public $ignore_cache = false;
	/**
	 *	region setting
	 *
	 */
	public $region = '';
	
	 /**
	 *	Locale setting
	 *
	 */
	public $locale = '';
	
	/*
	*	some tracking bits for people
	*/
	public $usage = array(
				'type'				=> '',
				'url'				=> '',
				'responce_code'		=> '',
				'content_type'		=> '',
				'locale'			=> '',
			);
	public $cache;
	public $item;
	
	public $errno = CURLE_OK;
	public $error = '';
    /**
     * Construct
     *
     * @param string $client_id Client ID
     * @param string $client_secret Client Secret
     * @param int    $client_auth (AUTH_TYPE_URI, AUTH_TYPE_AUTHORIZATION_BASIC, AUTH_TYPE_FORM)
     * @param string $certificate_file Indicates if we want to use a certificate file to trust the server. Optional, defaults to null.
     * @return void
     */
    public function __construct($client_id, $client_secret, $region, $locale, $redirect_uri)
    {
        if (!extension_loaded('curl')) {
            throw new Exception('The PHP exention curl must be installed to use this library.', Exception::CURL_NOT_FOUND);
        }

		$r = preg_replace('/http:/', 'https:', $redirect_uri);
		$client_auth			= self::AUTH_TYPE_URI;
        $this->client_id		= $client_id;
        $this->client_secret	= $client_secret;
		$this->region			= $region;
		$this->locale			= $locale;
        $this->client_auth		= $client_auth;
		$this->redirect_uri		= $r;
		$bob = $this->getAccessToken($this->baseurl[$this->region]['TOKEN_ENDPOINT'], 'client_credentials',array());
		$this->setAccessToken($bob['access_token']);
		$this->setAccessTokenType(1);
	
    }

	public function set_region($region)
	{
		$this->region = $region;
	}
	
    /**
     * Get the client Id
     *
     * @return string Client ID
     */
    public function getClientId()
    {
        return $this->client_id;
    }

    /**
     * Get the client Secret
     *
     * @return string Client Secret
     */
    public function getClientSecret()
    {
        return $this->client_secret;
    }

    /**
     * getAuthenticationUrl
     *
     * @param string $auth_endpoint Url of the authentication endpoint
     * @param string $redirect_uri  Redirection URI
     * @param array  $extra_parameters  Array of extra parameters like scope or state (Ex: array('scope' => null, 'state' => ''))
     * @return string URL used for authentication
     */
    public function getAuthenticationUrl($auth_endpoint, $redirect_uri, array $extra_parameters = array())
    {
        $parameters = array_merge(array(
            'response_type' => 'code',
            'client_id'     => $this->client_id,
			'scope'			=> 'wow.profile',
			'auth_flow'		=> 'auth_code',
            'redirect_uri'  => $redirect_uri
        ), $extra_parameters);
        return $auth_endpoint . '?' . http_build_query($parameters, null, '&');
    }

    /**
     * getAccessToken
     *
     * @param string $token_endpoint    Url of the token endpoint
     * @param int    $grant_type        Grant Type ('authorization_code', 'password', 'client_credentials', 'refresh_token', or a custom code (@see GrantType Classes)
     * @param array  $parameters        Array sent to the server (depend on which grant type you're using)
     * @return array Array of parameters required by the grant_type (CF SPEC)
     */
    public function getAccessToken($token_endpoint, $grant_type, array $parameters)
    {
        if (!$grant_type) {
            throw new InvalidArgumentException('The grant_type is mandatory.', InvalidArgumentException::INVALID_GRANT_TYPE);
        }
        $grantTypeClassName = $this->convertToCamelCase($grant_type);
        $grantTypeClass =  __NAMESPACE__ . '' . $grantTypeClassName;
        if (!class_exists($grantTypeClass)) {
            throw new InvalidArgumentException('Unknown grant type \'' . $grant_type . '\' ['.$grantTypeClass.']', InvalidArgumentException::INVALID_GRANT_TYPE);
        }
        $grantTypeObject = new $grantTypeClass();
        $grantTypeObject->validateParameters($parameters);
        if (!defined($grantTypeClass . '::GRANT_TYPE')) {
            throw new Exception('Unknown constant GRANT_TYPE for class ' . $grantTypeClassName, Exception::GRANT_TYPE_ERROR);
        }
        $parameters['grant_type'] = $grantTypeClass::GRANT_TYPE;
        $http_headers = array();
        switch ($this->client_auth) {
            case self::AUTH_TYPE_URI:
            case self::AUTH_TYPE_FORM:
                $parameters['client_id'] = $this->client_id;
                $parameters['client_secret'] = $this->client_secret;
                break;
            case self::AUTH_TYPE_AUTHORIZATION_BASIC:
                $parameters['client_id'] = $this->client_id;
                $http_headers['Authorization'] = 'Basic ' . base64_encode($this->client_id .  ':' . $this->client_secret);
                break;
            default:
                throw new Exception('Unknown client auth type.', Exception::INVALID_CLIENT_AUTHENTICATION_TYPE);
                break;
        }

        $result = $this->executeRequest($token_endpoint, $parameters, self::HTTP_METHOD_POST, $http_headers, self::HTTP_FORM_CONTENT_TYPE_APPLICATION);

		return $result;
    }

    /**
     * setToken
     *
     * @param string $token Set the access token
     * @return void
     */
    public function setAccessToken($token)
    {
        $this->access_token = $token;
    }

    /**
     * Set the client authentication type
     *
     * @param string $client_auth (AUTH_TYPE_URI, AUTH_TYPE_AUTHORIZATION_BASIC, AUTH_TYPE_FORM)
     * @return void
     */
    public function setClientAuthType($client_auth)
    {
        $this->client_auth = $client_auth;
    }

    /**
     * Set an option for the curl transfer
     *
     * @param int   $option The CURLOPT_XXX option to set
     * @param mixed $value  The value to be set on option
     * @return void
     */
    public function setCurlOption($option, $value)
    {
        $this->curl_options[$option] = $value;
    }

    /**
     * Set multiple options for a cURL transfer
     *
     * @param array $options An array specifying which options to set and their values
     * @return void
     */
    public function setCurlOptions($options) 
    {
        $this->curl_options = array_merge($this->curl_options, $options);
    }

    /**
     * Set the access token type
     *
     * @param int $type Access token type (ACCESS_TOKEN_BEARER, ACCESS_TOKEN_MAC, ACCESS_TOKEN_URI)
     * @param string $secret The secret key used to encrypt the MAC header
     * @param string $algorithm Algorithm used to encrypt the signature
     * @return void
     */
    public function setAccessTokenType($type, $secret = null, $algorithm = null)
    {
        $this->access_token_type = $type;
        $this->access_token_secret = $secret;
        $this->access_token_algorithm = $algorithm;
    }

	
	protected function _buildUrl($path, $params = array())
    {
		// allways called in all api calls
		$params['apikey'] = $this->client_id;
		if (isset($this->access_token))
		{
			$params['access_token']	= $this->access_token;
		}
		//set for translation
		$params['locale'] = $this->locale;

		if ($path == 'account')
		{
			$url = $this->baseurl[$this->region]['ACCOUNT_ENDPOINT'];
		}
		else
		{				
			$url = $this->baseurl[$this->region]['urlbase'];
		}
		//$url .= $path;
		$url .= self::_buildtype($path,$params);
		unset($params['name']);
		unset($params['server']);
		$url .= (count($params)) ? '?' . $this->_build_strings($params, '&') : '';
		$this->usage = array (
			'type'		=> $path,
			'url'		=> $url,
			'locale'	=> $this->locale
		);
		//echo $url;
		return $url;
		
    }
	
	function _build_strings($params, $sep)
	{
		$e = '';
		$r = array();
		foreach($params as $key=>$val)
		{
			$r[] = $key.'='.$val;
		}
		$e = implode($sep, $r);
		return $e;
	}
	
	/**
	*	Type of call uri build
	*	$class - type of call
	*	$fields - array of data (name,server,size)
	**/
	public function _buildtype($class,$fields)
	{
		$fields['realm'] = $fields['server'];
		switch ($class)
		{
			/*
				Achievement API
			*/
			case 'achievement':
				$q = '/wow/achievement/'.$fields['id'].'';
			break;


			/*
				Auction API
			*/
			case 'auction_data':
				$q = '/wow/auction/data/'.$fields['realm'].'';
			break;


			/*
				Boss API
			*/
			case 'boss_list':
				$q = '/wow/boss/';
			break;
			case 'boss':
				$q = '/wow/boss/'.$fields['bossid'].'';
			break;


			/*
				Challenge Mode API
			*/
			case 'realm_leaderboard':
				$q = '/wow/challenge/'.$fields['realm'].'';
			break;
			case 'region_leaderboard':
				$q = '/wow/challenge/region';
			break;


			/*
				Character Profile API
			*/
			case 'character':
				$q = '/wow/character/'.$fields['realm'].'/'.$fields['name'].'';
			break;
			case 'achievements':
				$q = '/wow/character/'.$fields['realm'].'/'.$fields['name'].'';
			break;
			case 'appearance':
				$q = '/wow/character/'.$fields['realm'].'/'.$fields['name'].'';
			break;
			case 'feed':
				$q = '/wow/character/'.$fields['realm'].'/'.$fields['name'].'';
			break;
			case 'guild':
				$q = '/wow/character/'.$fields['realm'].'/'.$fields['name'].'';
			break;
			case 'hunter_pets':
				$q = '/wow/character/'.$fields['realm'].'/'.$fields['name'].'';
			break;
			case 'items':
				$q = '/wow/character/'.$fields['realm'].'/'.$fields['name'].'';
			break;
			case 'mounts':
				$q = '/wow/character/'.$fields['realm'].'/'.$fields['name'].'';
			break;
			case 'pets':
				$q = '/wow/character/'.$fields['realm'].'/'.$fields['name'].'';
			break;
			case 'pet_slots':
				$q = '/wow/character/'.$fields['realm'].'/'.$fields['name'].'';
			break;
			case 'professions':
				$q = '/wow/character/'.$fields['realm'].'/'.$fields['name'].'';
			break;
			case 'progression':
				$q = '/wow/character/'.$fields['realm'].'/'.$fields['name'].'';
			break;
			case 'pvp':
				$q = '/wow/character/'.$fields['realm'].'/'.$fields['name'].'';
			break;
			case 'quests':
				$q = '/wow/character/'.$fields['realm'].'/'.$fields['name'].'';
			break;
			case 'reputation':
				$q = '/wow/character/'.$fields['realm'].'/'.$fields['name'].'';
			break;
			case 'statistics':
				$q = '/wow/character/'.$fields['realm'].'/'.$fields['name'].'';
			break;
			case 'stats':
				$q = '/wow/character/'.$fields['realm'].'/'.$fields['name'].'';
			break;
			case 'talents':
				$q = '/wow/character/'.$fields['realm'].'/'.$fields['name'].'';
			break;
			case 'titles':
				$q = '/wow/character/'.$fields['realm'].'/'.$fields['name'].'';
			break;
			case 'audit':
				$q = '/wow/character/'.$fields['realm'].'/'.$fields['name'].'';
			break;


			/*
				Guild Profile API
			*/
			case 'guild':
				$q = '/wow/guild/'.$fields['realm'].'/'.$fields['name'].'';
			break;
			case 'members':
				$q = '/wow/guild/'.$fields['realm'].'/'.$fields['name'].'';
			break;
			case 'achievements':
				$q = '/wow/guild/'.$fields['realm'].'/'.$fields['name'].'';
			break;
			case 'news':
				$q = '/wow/guild/'.$fields['realm'].'/'.$fields['name'].'';
			break;
			case 'challenge':
				$q = '/wow/guild/'.$fields['realm'].'/'.$fields['name'].'';
			break;
				
			/*
				Item API
			*/
			case 'item':
				$q = '/wow/item/'.$fields['id'].'';
			break;
			case 'item_set':
				$q = '/wow/item/set/'.$fields['setid'].'';
			break;
				
			/*
				Mount API
			*/
			case 'mount_list':
				$q = '/wow/mount/';
			break;
				
			/*
				Pet API
			*/
			case 'pet_list':
				$q = '/wow/pet/';
			break;
			case 'abilities':
				$q = '/wow/pet/ability/'.$fields['abilityID'].'';
			break;
			case 'species':
				$q = '/wow/pet/species/'.$fields['speciesID'].'';
			break;
			case 'stats':
				$q = '/wow/pet/stats/'.$fields['speciesID'].'';
			break;
				
			/*
				PVP API
			*/
			case 'leaderboards':
				$q = '/wow/leaderboard/'.$fields['bracket'].'';
			break;
				
			/*
				Quest API
			*/
			case 'quest':
				$q = '/wow/quest/'.$fields['questId'].'';
			break;


			/*
				Realm Status API
			*/
			case 'realm_status':
				$q = '/wow/realm/status';
			break;
				
			/*
				Recipe API
			*/
			case 'recipe':
				$q = '/wow/recipe/'.$fields['recipeId'].'';
			break;
				
			/*
				Spell API
			*/
			case 'spell':
				$q = '/wow/spell/'.$fields['spellId'].'';
			break;
				
			/*
				User API
			*/
			case 'wowprofile':
				$q = '/wow/user/characters';
			break;
			case 'account':
				$q = '/oauth/userinfo';
			break;


			/*
				Zone API
			*/
			case 'master_list':
				$q = '/wow/zone/';
			break;
			case 'zone':
				$q = '/wow/zone/'.$fields['zoneid'].'';
			break;


			/*
				Data Resources
			*/
			case 'battlegroups':
				$q = '/wow/data/battlegroups/';
			break;
			case 'character_races':
				$q = '/wow/data/character/races';
			break;
			case 'character_classes':
				$q = '/wow/data/character/classes';
			break;
			case 'character_achievements':
				$q = '/wow/data/character/achievements';
			break;
			case 'guild_rewards':
				$q = '/wow/data/guild/rewards';
			break;
			case 'guild_perks':
				$q = '/wow/data/guild/perks';
			break;
			case 'guild_achievements':
				$q = '/wow/data/guild/achievements';
			break;
			case 'item_classes':
				$q = '/wow/data/item/classes';
			break;
			case 'talents':
				$q = '/wow/data/talents';
			break;
			case 'pet_types':
				$q = '/wow/data/pet/types';
			break;
		}
		$q = str_replace(' ' , '%20' , $q);
		return $q;
	}
	
    /**
     * Fetch a protected ressource
     *
     * @param string $protected_ressource_url Protected resource URL
     * @param array  $parameters Array of parameters
     * @param string $http_method HTTP Method to use (POST, PUT, GET, HEAD, DELETE)
     * @param array  $http_headers HTTP headers
     * @param int    $form_content_type HTTP form content type to use
     * @return array
     */
    public function fetch($protected_resource_url, $parameters = array(), $http_headers = array(), $http_method = self::HTTP_METHOD_GET, $form_content_type = self::HTTP_FORM_CONTENT_TYPE_MULTIPART)
    {
		global $roster;
		$protected_resource_url = self::_buildUrl($protected_resource_url, $parameters);
		
        if ($this->access_token) {
            switch ($this->access_token_type) {
                case self::ACCESS_TOKEN_URI:
                    if (is_array($parameters)) {
                        $parameters[$this->access_token_param_name] = $this->access_token;
                    } else {
                        throw new InvalidArgumentException(
                            'You need to give parameters as array if you want to give the token within the URI.',
                            InvalidArgumentException::REQUIRE_PARAMS_AS_ARRAY
                        );
                    }
                    break;
                case self::ACCESS_TOKEN_BEARER:
                    $http_headers['Authorization'] = 'Bearer ' . $this->access_token;
                    break;
                case self::ACCESS_TOKEN_OAUTH:
                    $http_headers['Authorization'] = 'OAuth ' . $this->access_token;
                    break;
                case self::ACCESS_TOKEN_MAC:
                    $http_headers['Authorization'] = 'MAC ' . $this->generateMACSignature($protected_resource_url, $parameters, $http_method);
                    break;
                default:
                    throw new Exception('Unknown access token type.', Exception::INVALID_ACCESS_TOKEN_TYPE);
                    break;
            }
        }
		
		$result = $this->executeRequest($protected_resource_url, $parameters, $http_method, $http_headers, $form_content_type);
			
		return $result;
    }

    /**
     * Generate the MAC signature
     *
     * @param string $url Called URL
     * @param array  $parameters Parameters
     * @param string $http_method Http Method
     * @return string
     */
    private function generateMACSignature($url, $parameters, $http_method)
    {
        $timestamp = time();
        $nonce = uniqid();
        $parsed_url = parse_url($url);
        if (!isset($parsed_url['port']))
        {
            $parsed_url['port'] = ($parsed_url['scheme'] == 'https') ? 443 : 80;
        }
        if ($http_method == self::HTTP_METHOD_GET) {
            if (is_array($parameters)) {
                $parsed_url['path'] .= '?' . http_build_query($parameters, null, '&');
            } elseif ($parameters) {
                $parsed_url['path'] .= '?' . $parameters;
            }
        }

        $signature = base64_encode(hash_hmac($this->access_token_algorithm,
                    $timestamp . "\n"
                    . $nonce . "\n"
                    . $http_method . "\n"
                    . $parsed_url['path'] . "\n"
                    . $parsed_url['host'] . "\n"
                    . $parsed_url['port'] . "\n\n"
                    , $this->access_token_secret, true));

        return 'id="' . $this->access_token . '", ts="' . $timestamp . '", nonce="' . $nonce . '", mac="' . $signature . '"';
    }

    /**
     * Execute a request (with curl)
     *
     * @param string $url URL
     * @param mixed  $parameters Array of parameters
     * @param string $http_method HTTP Method
     * @param array  $http_headers HTTP Headers
     * @param int    $form_content_type HTTP form content type to use
     * @return array
     */
    private function executeRequest($url, $parameters = array(), $http_method = self::HTTP_METHOD_GET, array $http_headers = null, $form_content_type = self::HTTP_FORM_CONTENT_TYPE_MULTIPART)
    {
		global $roster;
		
		//echo $url.'<br>'.$http_method.'<br>';
		
        $curl_options = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_CUSTOMREQUEST  => $http_method
        );
        switch($http_method) {
            case self::HTTP_METHOD_POST:
                $curl_options[CURLOPT_POST] = true;
				
                /* No break */
            case self::HTTP_METHOD_PUT:
			case self::HTTP_METHOD_PATCH:

                /**
                 * Passing an array to CURLOPT_POSTFIELDS will encode the data as multipart/form-data,
                 * while passing a URL-encoded string will encode the data as application/x-www-form-urlencoded.
                 * http://php.net/manual/en/function.curl-setopt.php
                 */
                if(is_array($parameters) && self::HTTP_FORM_CONTENT_TYPE_APPLICATION === $form_content_type) {
                    $parameters = http_build_query($parameters, null, '&');
                }
                $curl_options[CURLOPT_POSTFIELDS] = $parameters;
                break;
            case self::HTTP_METHOD_HEAD:
                $curl_options[CURLOPT_NOBODY] = true;
                /* No break */
            case self::HTTP_METHOD_DELETE:
            case self::HTTP_METHOD_GET:
                
                break;
            default:
                break;
        }
		//echo $url.'<br>';
        $curl_options[CURLOPT_URL] = $url;
		$curl_options[CURLOPT_HEADER] = true;

        if (is_array($http_headers)) {
            $header = array();
            foreach($http_headers as $key => $parsed_urlvalue) {
                $header[] = "$key: $parsed_urlvalue";
            }
            $curl_options[CURLOPT_HTTPHEADER] = $header;
        }

        $ch = curl_init();
        curl_setopt_array($ch, $curl_options);
        // https handling
        if (!empty($this->certificate_file))
		{
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($ch, CURLOPT_CAINFO, $this->certificate_file);
        } else {
            // bypass ssl verification
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        }
        if (!empty($this->curl_options))
		{
            curl_setopt_array($ch, $this->curl_options);
        }
		curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        $result = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
		$this->errno	= curl_errno($ch);
		$this->error	= curl_error($ch);
		$header_size = curl_getinfo($ch,CURLINFO_HEADER_SIZE);
		
		//d($result,$http_code,$this->errno,$this->error,$content_type);

		$this->usage['responce_code'] = $http_code;
		$this->usage['content_type'] = $content_type;

        if ($this->errno)
		{
			$json_decode = json_decode(substr( $result, $header_size ), true);
			$json_decode['header'] = $this->get_headers_from_curl_response(substr($result, 0, $header_size));
			$json_decode['http_code'] = '~'.$http_code;
			$json_decode['last_url'] = curl_getinfo($ch,CURLINFO_EFFECTIVE_URL);
			curl_close($ch);
			return $json_decode;
        }
		else
		{
			$header_size = curl_getinfo($ch,CURLINFO_HEADER_SIZE);
			$json_decode = json_decode(substr( $result, $header_size ), true);
			$json_decode['header'] = $this->get_headers_from_curl_response(substr($result, 0, $header_size));
			$json_decode['http_code'] = $http_code;
			$json_decode['last_url'] = curl_getinfo($ch,CURLINFO_EFFECTIVE_URL);
			
        }

		//if (isset($json_decode['status']) OR $json_decode['status'] == 'nok' OR 
		if ( $json_decode['http_code'] != 200 && $json_decode['http_code'] != 304 )
		{
			$header_size = curl_getinfo($ch,CURLINFO_HEADER_SIZE);
			$json_decode = json_decode(substr( $result, $header_size ), true);
			$json_decode['header'] = $this->get_headers_from_curl_response(substr($result, 0, $header_size));
			$json_decode['http_code'] = $http_code;//curl_getinfo($ch,CURLINFO_HTTP_CODE);
			$json_decode['last_url'] = curl_getinfo($ch,CURLINFO_EFFECTIVE_URL);
			
		}
        curl_close($ch);
		return (null === $json_decode) ? $result : $json_decode;
    }

	public function get_headers_from_curl_response($response)
	{
		$headers = array();

		$header_text = substr($response, 0, strpos($response, "\r\n\r\n"));

		foreach (explode("\r\n", $header_text) as $i => $line)
			if ($i === 0)
				$headers['http_code'] = $line;
			else
			{
				list ($key, $value) = explode(': ', $line);

				$headers[$key] = $value;
			}

		return $headers;
	}
    /**
     * Set the name of the parameter that carry the access token
     *
     * @param string $name Token parameter name
     * @return void
     */
    public function setAccessTokenParamName($name)
    {
        $this->access_token_param_name = $name;
    }

    /**
     * Converts the class name to camel case
     *
     * @param  mixed  $grant_type  the grant type
     * @return string
     */
    private function convertToCamelCase($grant_type)
    {
        $parts = explode('_', $grant_type);
        array_walk($parts, function(&$item) { $item = ucfirst($item);});
        return implode('', $parts);
    }
}
/*
class Exception extends Exception
{
    const CURL_NOT_FOUND                     = 0x01;
    const CURL_ERROR                         = 0x02;
    const GRANT_TYPE_ERROR                   = 0x03;
    const INVALID_CLIENT_AUTHENTICATION_TYPE = 0x04;
    const INVALID_ACCESS_TOKEN_TYPE          = 0x05;
}

class InvalidArgumentException extends InvalidArgumentException
{
    const INVALID_GRANT_TYPE      = 0x01;
    const CERTIFICATE_NOT_FOUND   = 0x02;
    const REQUIRE_PARAMS_AS_ARRAY = 0x03;
    const MISSING_PARAMETER       = 0x04;
}
*/