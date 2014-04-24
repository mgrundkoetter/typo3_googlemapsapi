<?php
/**
 * This is just a data container for returned addresses and thus only contains getters.
 * @author Michael Grundkoetter
 *
 */
class AddressInformationData {

	private $fullAddress;
	private $administrativeArea;
	private $subAdministrativeArea;
	private $dependentLocality;
	private $postalCode;
	private $streetName;
	private $city;
	private $countryName;
	private $countryCode;
	private $latLonBox;
	private $coordinates;

	/**
	 * Contructs this data object.
	 * @param stdClass $jsonObject, as used in AddressInformationResponse
	 */
	public function __construct(stdClass $jsonObject) {
		$this->fullAddress = $jsonObject->formatted_address;
		$this->administrativeArea = $this->parseAdministrativeArea($jsonObject->address_components);
		$subInfo = &$jsonObject->AddressDetails->Country->AdministrativeArea->SubAdministrativeArea;
		$this->subAdministrativeArea = $subInfo->SubAdministrativeAreaName;
		$this->dependentLocality = $subInfo->Locality->DependentLocality->DependentLocalityName;
		$this->postalCode = $subInfo->Locality->DependentLocality->PostalCode->PostalCodeNumber;
		$this->streetName = $subInfo->Locality->DependentLocality->Thoroughfare->ThoroughfareName;
		$this->city = $subInfo->Locality->LocalityName;
		$this->countryName = $jsonObject->AddressDetails->Country->CountryName;
		$this->countryCode = $jsonObject->AddressDetails->Country->CountryNameCode;
		$this->latLonBox = $jsonObject->geometry->bounds;
		$this->coordinates = $jsonObject->geometry->location;

	}

	/**
	 * Returns full address as string (street, zip city, country)
	 * @return string
	 */
	public function getFullAddress() {
		return $this->fullAddress;
	}

	/**
	 * Returns administrative area, state names in Germany (e.g. "Sachsen")
	 * @return string
	 */
	public function getAdministrativeArea() {
		return $this->administrativeArea;
	}

	/**
	 * Returns subarea of administration, mostly next bigger city
	 * @return string
	 */
	public function getSubAdministrativeArea() {
		return $this->subAdministrativeArea;
	}

	/**
	 * Returns city district name (e.g. "Löbtau-Süd")
	 * @return string
	 */
	public function getDependentLocality() {
		return $this->dependentLocality;
	}

	/**
	 * Returns postal code (zip) of the address
	 * @return string, 5 digits
	 */
	public function getPostalCode() {
		return $this->postalCode;
	}

	/**
	 * Returns street name of found address
	 * @return string
	 */
	public function getStreetName() {
		return $this->streetName;
	}

	/**
	 * Returns city name of found address (e.g. "Dresden")
	 * @return string
	 */
	public function getCity() {
		return $this->city;
	}

	/**
	 * Returns country name in language of the address
	 * @return string
	 */
	public function getCountryName() {
		return $this->countryName;
	}

	/**
	 * Returns 2 digit country code (e.g. DE or US)
	 * @return string, 2 digits
	 */
	public function getCountryCode() {
		return $this->countryCode;
	}

	/**
	 * Returns the map view box for the address.
	 * @return stdClass with 4 members ("north", "east", "south", "west"),
	 * containing float numbers each
	 */
	public function getLatLonBox() {
		return $this->latLonBox;
	}

	/**
	 * Returns geo coordinates of the found address.
	 * @return array with 3 elements (0 = x, 1 = y, 2 empty)
	 */
	public function getCoordinates() {
		return $this->coordinates;
	}

	/**
	 * Parse the administrative area from area level 1
	 *
	 * @param array $areas
	 * @return null|string
	 */
	public function parseAdministrativeArea(array $areas) {
		$adminArea = null;
		foreach($areas as $areaObj) {
			if ($areaObj->types[0] == 'administrative_area_level_1') {
				$adminArea = $areaObj->long_name;
				break;
			}
		}
		return $adminArea;
	}


	/**
	 * Returns latitude by geo coordinate of the found address.
	 * @return mixed
	 */
	public function getLatitude() {
		return $this->coordinates->lat;
	}

	/**
	 * Returns longitude by geo coordinate of the found address.
	 * @return mixed
	 */
	public function getLongitude() {
		return $this->coordinates->lng;
	}

}