<?php
/**
 * Parses the API response and fills more intuitive objects
 * @author Michael Grundkoetter
 *
 */
class AddressInformationResponse {

	//more information on status codes on:
	//http://code.google.com/intl/en/apis/maps/documentation/reference.html#GGeoStatusCode
	//no more comments for that, as the names should be enough

	const STATUS_SUCCESS = 200;
	const STATUS_BAD_REQUEST = 400;
	const STATUS_SERVER_ERROR = 500;
	const STATUS_MISSING_QUERY = 601;
	const STATUS_UNKNOWN_ADDRESS = 602;
	const STATUS_UNAVAILABLE_ADDRESS = 603;
	const STATUS_UNKNOWN_DIRECTIONS = 604;
	const STATUS_BAD_KEY = 610;
	const STATUS_TOO_MANY_QUERIES = 620;

	private $responseCode = '';
	private $requestedString = '';
	private $requestedUrl = '';
	private $results = array();

	/**
	 * Takes the plaintext json response from the API
	 *
	 * @param stdClass $jsonResponse
	 * @param          $requestedUrl
	 */
	public function __construct(stdClass $jsonResponse, $requestedUrl) {
		$this->responseCode = $this->mapStatusCode($jsonResponse->status);
		$this->requestedString = $jsonResponse->name;
		$this->results = $this->parsePlaceMarks($jsonResponse->results);
		$this->requestedUrl = $requestedUrl;
	}

	/**
	 * Creates an array out of the given API results.
	 * @param array $jsonObjects
	 * @return array of AddressInformationData objects
	 * @throws InvalidArgumentException
	 */
	private function parsePlaceMarks($jsonObjects) {
		if (!is_array($jsonObjects)) {
			throw new InvalidArgumentException($this->responseCode);
		}
		$results = array();
		foreach ($jsonObjects as $result) {
			$results[] = new AddressInformationData($result);
		}
		return $results;
	}

	/**
	 * Returns the results of the request.
	 * @return array of AddressInformationData objects
	 */
	public function getResults() {
		return $this->results;
	}

	/**
	 * Returns the amount of results in the response.
	 * @return integer
	 */
	public function getResultCount() {
		return count($this->results);
	}

	/**
	 * Returns the amount of results in the response.
	 * @throws Exception
	 * @return integer
	 */
	public function getFirstResult() {
		if ($this->getResultCount() === 0) {
			throw new Exception('no reuslts found', 400);
		}
		return $this->results[0];
	}

	/**
	 * Returns API status code. See STATUS_* constants for details.
	 * @return string
	 */
	public function getResponseCode() {
		return $this->responseCode;
	}

	/**
	 * Returns the requested string as it was given to the API
	 * @return string
	 */
	public function getRequestedString() {
		return $this->requestedString;
	}

	/**
	 * Returns the request string as sent to the Google API.
	 * @return string
	 */
	public function getRequestedUrl() {
		return $this->requestedUrl;
	}

	/**
	 * Status code from v3 API
	 *
	 * @param string $code
	 * @return int
	 */
	protected function mapStatusCode($code) {
		switch ($code) {

			default:
			case 'OK':
				$newCode = self::STATUS_SUCCESS;
				break;

			case 'ZERO_RESULTS':
				$newCode = self::STATUS_UNKNOWN_ADDRESS;
				break;

			case 'OVER_QUERY_LIMIT':
				$newCode = self::STATUS_TOO_MANY_QUERIES;
				break;

			case 'REQUEST_DENIED':
				$newCode = self::STATUS_BAD_REQUEST;
				break;

			case 'INVALID_REQUEST':
				$newCode = self::STATUS_BAD_REQUEST;
				break;
		}

		return $newCode;
	}

}
?>