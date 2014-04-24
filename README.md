TYPO3 extension Google Maps API
===============================

A TYPO3 extension that adds a litte PHP wrapper to the geocoding part of the Google Maps API.
Additionally, there is a [TYPO3 formhandler](http://www.typo3-formhandler.com/documentation/interceptors/) interceptor included, that can convert an address string into lat/lng values.

Why is this extension not in the TER?
-------------------------------------

I just don't like the user handling and all the pain stuff that is necessary to publish an extension into the official TYPO3 extension repository (TER). I prefer to use a more collaborative platform like GitHub and tools like composer (which plays nice with GitHub repos). The TER-way of extension ownership and publishing is not so fluent and easy and the whole infrastructure of GIT and gerrit in the TYPO3 domain is not really inspiring to collaboration in my eyes. But this is just my opinion and if you feel like adding this into TER, you may.

So please feel also free to fork this extension and send pull requests if you feel like this can be improved (code as well as documentation).

**Example code (PHP):**

```php
$service = new AddressInformationRequestService();
$response = $service->getInformationFor("Frankfurter Straße 4, 01159 Dresden");

if ($response->getResponseCode() == AddressInformationResponse::STATUS_SUCCESS) {
  if ($response->getResultCount() > 0) {
    $result = $response->getResults();

    //in the example we take the first result, but there may be multiple, of course!
    $coords = $result[0]->getCoordinates();

    //now, you have the float values
    $latitude = $coords->lat;
    $longitude = $coords->lng;
  } else {
    //there was no useful result from Google, so maybe the input was not so good
  }
} else {
  //something went wrong. Check the response code.
}
```

You will find useful documentation for all the methods in the code of the classes.

**Example code (typoscript):**

In this example, the coordinates are generated from the form fields street, zip and city in the given order. If you want to be even more precise, you can also add the country name. This property may also be used with a wrap, so “manual” additions like a static country string are possible, too. (e.g. if country is not a field in the form)
For a more detailed description of the typoscript settings, please refer to the /doc directory.

```
plugin.Tx_Formhandler.settings.predef.xyz {

  //without this, the interceptor won't be found
  additionalIncludePaths.10 = EXT:googlemapsapi/Classes/Formhandler

  //validators and so on

  saveInterceptors.1 {
    class = Tx_Formhandler_Interceptor_GeoCoordinates
    config {
      addressFields = street, zip, city
      latfield = latitude
      lngfield = longitude
    }
  }

  //finishers and so on
}
```