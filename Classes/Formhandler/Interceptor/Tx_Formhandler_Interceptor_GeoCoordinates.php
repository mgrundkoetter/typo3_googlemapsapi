<?php

require_once t3lib_extMgm::extPath('googlemapsapi', 'Classes/AddressInformationData.php');
require_once t3lib_extMgm::extPath('googlemapsapi', 'Classes/AddressInformationRequestService.php');
require_once t3lib_extMgm::extPath('googlemapsapi', 'Classes/AddressInformationResponse.php');

/**
 * An interceptor converting an address to lat/lng values by utilising the Google Maps api.
 * Be aware that there is a query limit and the script may get blocked after too many requests!
 *
 * @author Michael Grundkoetter
 */
class Tx_Formhandler_Interceptor_GeoCoordinates extends Tx_Formhandler_AbstractInterceptor {

	/**
	 * The main method called by the controller
	 *
	 * @return array The probably modified GET/POST parameters
	 */
	public function process() {
		$addressFields = $this->utilityFuncs->getSingle($this->settings, 'addressFields');
		$fields = t3lib_div::trimExplode(',', $addressFields, TRUE);

		if ($this->checkConfig($fields, $this->settings['latfield'], $this->settings['lngfield'])) {
			$address = array();
			foreach ($fields as $field) {
				$address[] = $this->gp[$field];
			}
			$address = trim(implode(' ', $address));
			if (!empty($address)) {
				$service = new AddressInformationRequestService();
				$response = $service->getInformationFor($address);

				if ($response->getResponseCode() == AddressInformationResponse::STATUS_SUCCESS) {
					if ($response->getResultCount() > 0) {
						$result = $response->getResults();
						$coords = $result[0]->getCoordinates();

						$this->gp[$this->settings['latfield']] = $coords->lat;
						$this->gp[$this->settings['lngfield']] = $coords->lng;
					} else {
						$this->utilityFuncs->debugMessage('No result for "' . $address . '" from google. maybe your input was bad?');
					}
				} else {
					$this->utilityFuncs->debugMessage('geo data request for "' . $address . '" did not succeed. Error code: ' . $response->getResponseCode());
				}
			} else {
				$this->utilityFuncs->debugMessage('All the given address fields are empty (' . $addressFields . '), so no api query was made.');
			}
		}

		return $this->gp;
	}

	/**
	 * Checks interceptor config.
	 *
	 * @param array $fields fields that should be used as source address
	 * @param string $latfield field name of target field for latitude value
	 * @param string $lngfield field name of target field for longitude value
	 *
	 * @return bool
	 */
	protected function checkConfig($fields, $latfield, $lngfield) {
		$isOk = true;

		if (count($fields) < 1) {
			$this->utilityFuncs->debugMessage('field configuration is missing. Add "settings.addressFields = street, zip, city" or something similar.');
			$isOk = false;
		}

		if (empty($latfield) || empty($lngfield)) {
			$this->utilityFuncs->debugMessage('target field configuration is missing. Add "settings.latfield = latitude" or something similar for latfield and lngfield.');
			$isOk = false;
		}

		return $isOk;
	}
}

?>
