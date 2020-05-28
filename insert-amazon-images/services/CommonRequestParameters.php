<?php

namespace AmazonImages\Services;

/**
 * Class CommonRequestParameters
 * @package AmazonImages\Services
 * PAAPI host and region to which you want to send request
 * For more details refer: https://webservices.amazon.com/paapi5/documentation/common-request-parameters.html#host-and-region
 */
class CommonRequestParameters {
	protected $host;
	protected $region;
	protected $locale;

	public function __construct($locale=null) {
		$this->setLocale($locale);
	}

	/**
	 * @return mixed
	 */
	public function getHost() {
		return $this->host;
	}

	/**
	 * @param mixed $host
	 */
	public function setHost( $host ) {
		$this->host = $host;
	}

	/**
	 * @return mixed
	 */
	public function getRegion() {
		return $this->region;
	}

	/**
	 * @param mixed $region
	 */
	public function setRegion( $region ) {
		$this->region = $region;
	}

	/**
	 * @param null $locale
	 */
	public function setLocale( $locale=null ) {
		$this->locale = $locale;
		switch($this->locale){
			case 'com.au':
				$this->setHost('webservices.amazon.com.au');
				$this->setRegion('us-west-2');
				break;
			case 'com.br':
				$this->setHost('webservices.amazon.com.br');
				$this->setRegion('us-east-1');
				break;
			case 'ca':
				$this->setHost('webservices.amazon.ca');
				$this->setRegion('us-east-1');
				break;
			case 'fr':
				$this->setHost('webservices.amazon.fr');
				$this->setRegion('eu-west-1');
				break;
			case 'de':
				$this->setHost('webservices.amazon.de');
				$this->setRegion('eu-west-1');
				break;
			case 'in':
				$this->setHost('webservices.amazon.in');
				$this->setRegion('eu-west-1');
				break;
			case 'it':
				$this->setHost('webservices.amazon.it');
				$this->setRegion('eu-west-1');
				break;
			case 'es':
				$this->setHost('webservices.amazon.es');
				$this->setRegion('eu-west-1');
				break;
			case 'com.tr':
				$this->setHost('webservices.amazon.com.tr');
				$this->setRegion('eu-west-1');
				break;
			case 'co.uk':
				$this->setHost('webservices.amazon.co.uk');
				$this->setRegion('eu-west-1');
				break;
			case 'ae':
				$this->setHost('webservices.amazon.ae'); // United Arab Emirates
				$this->setRegion('eu-west-1');
				break;
			case 'co.jp':
				$this->setHost('webservices.amazon.co.jp');
				$this->setRegion('us-west-2');
				break;
			case 'com.mx':
				$this->setHost('webservices.amazon.com.mx');
				$this->setRegion('us-east-1');
				break;
			case 'com':
				$this->setHost('webservices.amazon.com');
				$this->setRegion('us-east-1');
				break;
			default:
		}
	}

	/**
	 * @return null
	 */
	public function getLocale() {
		return $this->locale;
	}
}