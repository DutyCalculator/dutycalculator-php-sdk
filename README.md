DutyCalculator PHP SDK (v.1.0.0)
-----

This repository contains the open source PHP SDK that allows you to access DutyCalculator from your PHP app.
The SDK requires a DutyCalculator API account (go to [http://www.dutycalculator.com/compare-plans/](http://www.dutycalculator.com/compare-plans/)).
Full documentation about DutyCalculator API you can find [here](http://www.dutycalculator.com/api-center/dutycalculator-api-2-1-documentation/).


Usage
-----

The [examples][examples] are a good place to start. The minimal you'll need to
have is:
```php
require 'dutycalculator-php-sdk/src/dutycalculator.php';

$client = new DutyCalculator_Client('YOUR_API_KEY');

/*
 * Example of getting available import to countries
 */
$countriesTo = $client->getImportToCountries();
```

All [API calls](http://www.dutycalculator.com/api-center/dutycalculator-api-2-1-documentation/) have function representation in the client class.
But you can make an API call using next code:
```php
try
{
	$countriesFrom = $client->sendRequest('supported-countries/from', array('display_alpha2_code' => true));
}
catch (DutyCalculator_Exception $e)
{
	error_log($e);
	$countriesFrom = null;
}
```

With Composer:

- Add the `"dutycalculator/dutycalculator-php-sdk": "@stable"` into the `require` section of your `composer.json`.
- Run `composer install`.
- The example will look like

```php
$client = new DutyCalculator_Client('YOUR_API_KEY');

/*
 * Example of getting available import to countries
 */
$countriesTo = $client->getImportToCountries();
```
[examples]: /examples/example.php


Report Issues/Bugs
===============
[Bugs](mailto:bugs@dutycalculator.com)