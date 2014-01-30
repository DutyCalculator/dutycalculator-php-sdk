<html>
<head>
	<meta charset="utf-8"/>
</head>
<body>
	<pre>
	<?php
	require_once('../src/dutycalculator.php');

	$apiKey = '';
	$client = new DutyCalculator_Client($apiKey);

	/*
	 * Example of getting available import to countries
	 */
	$countriesTo = $client->getImportToCountries();
	print_r($countriesTo->getAsArray());

	/*
	 * Example of getting available import from countries
	 */
	$countriesFrom = $client->getImportFromCountries(true);
	print_r($countriesFrom->getAsJSON());

	/*
	 * Example of calculation
	 */
	$items = array();
	$items[] = array("desc" => 'iPad 2',
				   "sku" => 'internal_ipad_2_sku',
				   "value" => 499,
				   "weight" => 1,
				   "weight_unit" => 'kg',
				   "qty" => 1,
				   "origin" => 'USA',
				   "reference" => 'ipad2_0001');
	$items[] = array("desc" => 'iPhone 5s',
				   "sku" => 'internal_iphone5s_sku',
				   "value" => 799,
				   "weight" => 1,
				   "weight_unit" => 'kg',
				   "qty" => 1,
				   "origin" => 'USA',
				   "reference" => 'iphone5s_0001');
	$countryFrom = 'US';
	$countryTo = 'GBR';
	$province = '';
	$shipping = 10;
	$insurance = 10;
	$currency = 'GBP';
	$calculation = $client->calculateImportDutyAndTaxes($items, $countryFrom, $countryTo, $province, $shipping, $insurance, $currency);
	print_r(htmlspecialchars($calculation->getAsXML()));
	?>
	</pre>
</body>
</html>