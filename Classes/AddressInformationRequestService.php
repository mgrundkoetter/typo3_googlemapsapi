<?php
/**
 * This is the gateway to the Google maps API service for geocoding locations.
 * @author Michael Grundkoetter
 * @see http://code.google.com/intl/de/apis/maps/documentation/services.html#Geocoding_Direct
 */
class AddressInformationRequestService {

	const SERVICE_URL = 'http://maps.googleapis.com/maps/api/geocode/json?sensor=false';
	const OUTPUT_FORMAT = 'json'; //possible but not yet implemented here: xml, kml, csv

	private $language;
	private $additionalParams;

	/**
	 *
	 * @param string $language default is "de", must be TLD style; defines the language of the results
	 * @param array $additionalParams an array of key => value pairs with more parameters
	 * 	for the results
	 */
	public function __construct($language = 'de', array $additionalParams = array()) {
		$this->language = $language;
		$this->additionalParams = $additionalParams;
	}

	/**
	 * Returns address information as returned from the Google maps API.
	 * @param string $addressString
	 * @throws Exception
	 * @return AddressInformationResponse
	 */
	public function getInformationFor($addressString) {
		$addressString = $this->prepareAddress($addressString);
		$moreParams = '';

		foreach ($this->additionalParams as $paramName => $paramValue) {
			$moreParams .= '&' . $paramName . '=' . $paramValue;
		}
		$url = self::SERVICE_URL . '&region=' . $this->language . '&language=' . $this->language . $moreParams . '&address=' . $addressString;

		$jsonResult = json_decode($this->parseAPIURL($url));
		if ($jsonResult !== NULL && ($jsonResult instanceof stdClass)) {
			return new AddressInformationResponse($jsonResult, $url);
		} else {
			throw new Exception('no valid json resulted');
		}
	}

	/**
	 * This method removes bad chars and checks the encoding.
	 * It returns a string that can be given to the API.
	 * @param string $addressString
	 * @return string
	 */
	private function prepareAddress($addressString) {
		if (!$this->is_urlencoded($addressString)) {
			$addressString = urlencode($addressString);
		}
		return $addressString;
	}

	/**
	 * Returns true if given string is url encoded
	 *
	 * @param string $string
	 * @return boolean
	 */
	private function is_urlencoded($string) {
		return $string === urlencode(urldecode($string));
	}

	/**
	 * Parse the url by curl or file_get_content
	 *
	 * @param $url
	 * @return bool|mixed|string
	 */
	private function parseAPIURL($url) {

		$ch = curl_init();
		if (!$ch) {
			$c = file_get_contents($url);
			if (strlen($c) > 0) {
				return $c;
			} else {
				return FALSE;
			}
		}
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

		return curl_exec($ch);
	}

}
?>