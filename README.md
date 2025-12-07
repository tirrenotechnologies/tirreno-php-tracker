# tirreno PHP tracker library

Send data from your PHP application to tirreno console.

```php
// Load object
require_once("TirrenoTracker.php");

$tirrenoUrl = "https://example.tld";
$trackingId = "XXX";

// Create object
$tracker = new TirrenoTracker($tirrenoUrl, $trackingId);

// Override defaults of required params
$tracker->setUserName($currentUser->username)       // johndoe42
        ->setEventTypeAccountLogin();

// Set optional params
$tracker->setFirstName($currentUser->firstname)     // John
        ->setLastName($currentUser->lastname)       // Doe
        ->setEmailAddress($currentUser->email);     // user@email.com

// Track event
$tracker->sendEvent();
```

## Requirements

* `cURL` PHP extension

## Installation

### Composer

```
composer require tirreno/tirreno-php-tracker
```

### Manualy

Via file download.
```php
require_once("TirrenoTracker.php");
```

## License

Released under the [BSD License](https://opensource.org/license/bsd-3-clause).
tirreno is a registered trademark of tirreno technologies sàrl, Switzerland.
