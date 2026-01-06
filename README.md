# tirreno PHP tracker library

This is the official PHP implementation of the [tirreno Tracking API](https://docs.tirreno.com/api-integration.html).

```php
<?php

// Load object
require_once("TirrenoTracker.php");

$tirrenoUrl = "https://example.tld/sensor/"; // Sensor URL
$trackingId = "XXX"; // Tracking ID

// Create object
$tracker = new TirrenoTracker($tirrenoUrl, $trackingId);

// Override defaults of required params
$tracker->setUserName("johndoe42")
        ->setIpAddress("1.1.1.1")
        ->setUrl("/login")
        ->setUserAgent("Mozilla/5.0 (X11; Linux x86_64)")
        ->setEventTypeAccountLogin();

// Set optional params
$tracker->setFirstName("John")
        ->setBrowserLanguage("fr-FR,fr;q=0.9")
        ->setHttpMethod("POST");

// Track event
$tracker->track();
```

## Requirements

* `cURL` PHP extension

## Installation

### Composer

```
composer require tirreno/tirreno-tracker
```

### Manualy

Via file download.
```php
require_once("TirrenoTracker.php");
```

## License

Released under the [BSD License](https://opensource.org/license/bsd-3-clause).
tirreno is a registered trademark of tirreno technologies sàrl, Switzerland.
