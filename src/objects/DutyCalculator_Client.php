<?php

class DutyCalculator_Client
{
	protected $_config;

	const CLASSIFY_BY_CATEGORY = 'cat';
	const CLASSIFY_BY_CATEGORY_AND_DESCRIPTION = 'cat desc';
	const CLASSIFY_BY_DESCRIPTION = 'desc';
	const CLASSIFY_BY_HS_CODE = 'hs';
	const CLASSIFY_BY_HS_CODE_AND_DESCRIPTION = 'hs desc';
	const CLASSIFY_BY_SKU = 'sku';
	const CLASSIFY_BY_SKU_AND_HS_CODE_AND_DESCRIPTION = 'sku hs desc';

	const CLASSIFICATION_REQUEST_COUNTRIES_ALL = 'all';
	const CLASSIFICATION_REQUEST_COUNTRIES_NONE = 'none';
	const CLASSIFICATION_REQUEST_COUNTRIES_CUSTOM = 'custom';

	const STORE_CALCULATION_ORDER_TYPE_ORDER = 'order';
	const STORE_CALCULATION_ORDER_TYPE_CREDIT_NOTE = 'credit_note';

	const IMPORTER_TYPE_COMMERCIAL = 'commercial';
	const IMPORTER_TYPE_PRIVATE = 'private';

	public function __construct($apiKey)
	{
		$this->_config = new DutyCalculator_Configuration();
		$this->_config->setApiKey($apiKey);
	}

	public function getConfig()
	{
		return $this->_config;
	}

	/**
	 * Send request to the API
	 *
	 * @param $action
	 * @param array $params
	 * @return DutyCalculator_Response
	 */
	public function sendRequest($action, $params = array())
	{
		$request = new DutyCalculator_Request($this->getConfig());
		$request->setAction($action);
		$request->setParams($params);
		$response = $request->send();
		return $response;
	}

	/**
	 * Returns a list of countries that DutyCalculator accepts as 'importing from' and 'manufactured in' parameter.
	 * The response includes the country name (in English) and the ISO 3166-1-alpha-3 or ISO 3166-1-alpha-2 code for the country.
	 * ISO 3166-1-alpha-3 or ISO 3166-1-alpha-2 format depends on $alpha2Code parameter.
	 *
	 * This call is free of charge.
	 *
	 * @param bool $alpha2Code
	 * @return DutyCalculator_Response
	 */
	public function getImportFromCountries($alpha2Code = false)
	{
		$countriesFrom = $this->sendRequest('supported-countries/from', array('display_alpha2_code' => $alpha2Code));
		return $countriesFrom;
	}

	/**
	 * Returns a list of countries that DutyCalculator accepts as 'importing to' parameter.
	 * The response includes the country name (in English) and the ISO 3166-1-alpha-3 or  ISO 3166-1-alpha-2 code for the country.
	 * ISO 3166-1-alpha-3 or ISO 3166-1-alpha-2 format depends on $alpha2Code parameter.
	 *
	 * This call is free of charge.
	 *
	 * @param bool $alpha2Code
	 * @return DutyCalculator_Response
	 */
	public function getImportToCountries($alpha2Code = false)
	{
		$countriesTo = $this->sendRequest('supported-countries/to', array('display_alpha2_code' => $alpha2Code));
		return $countriesTo;
	}

	/**
	 * Returns a list of currencies that DutyCalculator accepts as 'currency' parameter.
	 * The response includes the currency name (in English) and the ISO 4217 code for the currency.
	 *
	 * This call is free of charge.
	 *
	 * @return DutyCalculator_Response
	 */
	public function getSupportedCurrencies()
	{
		$currencies = $this->sendRequest('supported-currencies');
		return $currencies;
	}

	/**
	 * Returns the list of the categories, subcategories and product types that DutyCalculator accepts for all of the 'import to' countries.
	 * The DutyCalculator Item ID determines the duty & tax rates of a product for all 'import to' countries covered by DutyCalculator.
	 * So a selected DutyCalculator Item ID can be used for import duty & tax calculations for all 'import to' countries and will ensure that the correct duty & taxes rates are used.
	 *
	 * You can restrict your API services to certain Duty categories only, from your account section.
	 *
	 * This call is free of charge.
	 *
	 * @return DutyCalculator_Response
	 */
	public function getAvailableDutyCalculatorCategories()
	{
		$categories = $this->sendRequest('categories');
		return $categories;
	}

	/**
	 * Auto-suggests the most relevant DutyCalculator categories and items for your product.
	 * Using your short product categories and keywords including make and material will yield better results than long verbose product descriptions.
	 * You can restrict your classification  service to certain Duty categories only, from your account section, to increase API response time and reduce number of returned Duty Category options.
	 *
	 * @param $description
	 * @param bool $onlySuggested
	 * @return DutyCalculator_Response
	 */
	public function getSuggestedDutyCalculatorCategories($description, $onlySuggested = true)
	{
		$categories = $this->sendRequest('dc-id-classification', array('product_desc' => $description, 'only_suggested' => $onlySuggested));
		return $categories;
	}

	/**
	 * Returns best match HS code, short commodity description, DutyCalculator item ID details, duty & tax rates and any import restrictions for a given product and 'import to' country.
	 *
	 * Parameter $classifyBy is used to specify the request field that is used to classify the item and accepts values:
	 * - DutyCalculator_Client::CLASSIFY_BY_CATEGORY
	 * - DutyCalculator_Client::CLASSIFY_BY_CATEGORY_AND_DESCRIPTION
	 * - DutyCalculator_Client::CLASSIFY_BY_DESCRIPTION
	 * - DutyCalculator_Client::CLASSIFY_BY_HS_CODE
	 * - DutyCalculator_Client::CLASSIFY_BY_HS_CODE_AND_DESCRIPTION
	 * - DutyCalculator_Client::CLASSIFY_BY_SKU
	 * - DutyCalculator_Client::CLASSIFY_BY_SKU_AND_HS_CODE_AND_DESCRIPTION
	 *
	 * $items is array of items with single item structure:
	 * array("cat" => {DutyCalculator item id},
	 * 		 "hs" => {item HS code},
	 * 		 "country_of_hs_code" => {ISO alpha-3 country code or alpha-2 country code},
	 * 		 "desc" => {product description},
	 * 		 "sku" => {item sku})
	 *
	 * @param string $countryToCode ISO alpha-3 country code or alpha-2 country code
	 * @param array $items
	 * @param string $classifyBy items classification identifier
	 * @param string $province ISO alpha-2 province code of ‘importing to’ country (only required for Canada)
	 * @param bool $detailedResult true - for full details with duty& tax rates, import restrictions, short commodity & duty category descriptions, false - for HS codes only
	 * @param string $outputLanguage ISO 639-1 code of language of short commodity & duty category descriptions. Currently EN and RU are supported
	 * @return DutyCalculator_Response
	 */
	public function getHSCodeAndRates($countryToCode, $items, $classifyBy = self::CLASSIFY_BY_CATEGORY_AND_DESCRIPTION, $province = '', $detailedResult = true, $outputLanguage = 'en')
	{
		$params = array();
		$params['to'] = $countryToCode;
		$params['province'] = $province;
		$params['classify_by'] = $classifyBy;
		$params['detailed_result'] = $detailedResult;
		$params['output_language'] = $outputLanguage;
		$params['cat'] = array();
		$params['hs'] = array();
		$params['country_of_hs_code'] = array();
		$params['desc'] = array();
		$params['sku'] = array();
		$i = 0;
		if (!is_array($items))
		{
			$items = array($items);
		}
		foreach ($items as $item)
		{
			if (isset($item['cat']))
			{
				$params['cat'][$i] = $item['cat'];
			}
			if (isset($item['hs']))
			{
				$params['hs'][$i] = $item['hs'];
			}
			if (isset($item['country_of_hs_code']))
			{
				$params['country_of_hs_code'][$i] = $item['country_of_hs_code'];
			}
			if (isset($item['desc']))
			{
				$params['desc'][$i] = $item['desc'];
			}
			if (isset($item['sku']))
			{
				$params['sku'][$i] = $item['sku'];
			}
			$i++;
		}

		$result = $this->sendRequest('get-hscode', $params);
		return $result;
	}

	/**
	 * Files a product classification request with our customs expert team.
	 * We will classify the product for you and return within 24/72 hrs the DutyCalculator item ID and HS codes, duty & rates rates and any import restrictions for the requested import to countries.
	 *
	 * $countriesRequested may be:
	 * - DutyCalculator_Client::CLASSIFICATION_REQUEST_COUNTRIES_ALL - all countries
	 * - DutyCalculator_Client::CLASSIFICATION_REQUEST_COUNTRIES_NONE - no countries
	 * - DutyCalculator_Client::CLASSIFICATION_REQUEST_COUNTRIES_CUSTOM - specific countries ($countries parameter will be required and contains ISO alpha-3 country codes or alpha-2 country codes)
	 *
	 * @param string $productName
	 * @param string $productDescription
	 * @param string $productCost
	 * @param string $materialAndMaterialComposition
	 * @param string $productUrl
	 * @param $countriesRequested
	 * @param array $countries
	 * @param string $emailAddressForNotifications
	 * @return DutyCalculator_Response
	 */
	public function newClassificationRequest($productName, $productDescription, $productCost, $materialAndMaterialComposition, $productUrl, $countriesRequested, $countries = array(), $emailAddressForNotifications = '')
	{
		$params = array();
		$params['email_address'] = $emailAddressForNotifications;
		$params['product_name'] = $productName;
		$params['product_description'] = $productDescription;
		$params['product_cost'] = $productCost;
		$params['material_and_material_composition'] = $materialAndMaterialComposition;
		$params['product_url'] = $productUrl;
		$params['countries_requested'] = $countriesRequested;
		if ($countriesRequested == self::CLASSIFICATION_REQUEST_COUNTRIES_CUSTOM)
		{
			$params['country'] = array();
			foreach ($countries as $country)
			{
				$params['country'][] = $country;
			}
		}

		$result = $this->sendRequest('new-classification-request', $params);
		return $result;
	}

	/**
	 * Check current status of the classification request.
	 * Possible status codes: request_being_processed, classified, cant_classify, need_more_information, additional_information_received
	 *
	 * @param $classificationRequestId
	 * @return DutyCalculator_Response
	 */
	public function classificationRequestStatus($classificationRequestId)
	{
		$result = $this->sendRequest('classification-request-status', array('classification_request_id' => $classificationRequestId));
		return $result;
	}

	/**
	 * Returns the import duties and taxes due for a specified shipment.
	 *
	 * Parameter $classifyBy is used to specify the request field that is used to classify the item and accepts values:
	 * - DutyCalculator_Client::CLASSIFY_BY_CATEGORY
	 * - DutyCalculator_Client::CLASSIFY_BY_CATEGORY_AND_DESCRIPTION
	 * - DutyCalculator_Client::CLASSIFY_BY_DESCRIPTION
	 * - DutyCalculator_Client::CLASSIFY_BY_HS_CODE
	 * - DutyCalculator_Client::CLASSIFY_BY_HS_CODE_AND_DESCRIPTION
	 * - DutyCalculator_Client::CLASSIFY_BY_SKU
	 * - DutyCalculator_Client::CLASSIFY_BY_SKU_AND_HS_CODE_AND_DESCRIPTION
	 *
	 * Parameter $shipping and $insurance are shipping cost and cost of insurance of the whole shipment (not per item!) in indicated currency (parameter currency).
	 * Parameter $shipmentWeight (total shipment weight (in KG)) is required when importing into Brazil and import CIF exceeds US$3000.
	 * Parameter $outputCurrency indicates the desired currency of the calculation results. If left out, the currency of 'import to' country is used.
	 * Parameter $detailedResult (details) indicates the desired level of details in the calculation result. If set as 'true', import duty & taxes are returned for each item as well as the total import duty & taxes due for the whole shipment. If set as 'false', only the total import duty & taxes for the shipment are returned.
	 * Parameter $includeHsCodes (inclusion of HS codes into the calculation call response) accepts the values of 'true' and 'false'. If set to 'true', for each product, the 10/12 digit HS codes for country of destination are included in the response for each item. If set to 'false' or left out the HS codes are not included in the response.
	 *
	 * $items is array of items with single item structure:
	 * array("cat" => {DutyCalculator item id},
	 * 		 "hs" => {item HS code},
	 * 		 "country_of_hs_code" => {ISO alpha-3 country code or alpha-2 country code},
	 * 		 "desc" => {product description},
	 * 		 "sku" => {item sku},
	 * 		 "value" => {value per one item},
	 * 		 "weight" => {weight of one item},
	 * 		 "weight_unit" => {weight unit (kg or lb)},
	 * 		 "qty" => {item quantity},
	 * 		 "origin" => {ISO alpha-3 country code or alpha-2 country code indicates country of manufacture. If left out it is assumed that country of manufacture equals country from},
	 * 		 "reference" => {product reference is optional and can be used for your reference, calculation of import duty & taxes for split shipments and refunds for return shipments},
	 * 		 "volume" => {volume of one item (optional)},
	 * 		 "volume_unit" => {volume unit (m3, in3, cm3, l, hl, gal) (optional)},
	 * 		 "pcs" => {quantity of one item (optional)},
	 * 		 "pcs_unit" => {quantity unit (pcs, doz, gr) (optional)},
	 * 		 "area" => {area of one item (optional)},
	 * 		 "area_unit" => {area unit (m2) (optional)},
	 * 		 "pairs" => {pairs of one item (optional)},
	 * 		 "pairs_unit" => {pairs unit (pairs) (optional)},
	 * 		 "jwl" => {jwl of one item (optional)},
	 * 		 "jwl_unit" => {jwl unit (jwl) (optional)},
	 * 		 "power" => {power of one item (optional)},
	 * 		 "power_unit" => {power unit (kwt, hp) (optional)})
	 *
	 * The parameter commercial $commercialImporter is only required if the 'importing to' country is Russia.
	 * This parameter accepts values 1 (commercial importer) and 0 (private importer).
	 * For a 'Private importer' the parameters $importedWeight (imported weight (in KG)) and $importedValue (imported value) are required. Default values are 0 KG and €1000 and ensure maximum possible duty & tax liabilities are calculated.
	 *
	 * $shipping and $insurance can be an arrays of shipping and insurance amounts. In this case API will return several calculation results.
	 *
	 * @param $items
	 * @param $countryFrom
	 * @param $countryTo
	 * @param string $province
	 * @param $shipping
	 * @param $insurance
	 * @param $currency
	 * @param $classifyBy
	 * @param bool $outputCurrency
	 * @param bool $includeHsCodes
	 * @param bool $detailedResult
	 * @param $shipmentWeight
	 * @param bool $commercialImporter
	 * @param $importedWeight
	 * @param $importedValue
	 * @param array $additionalParams
	 * @return DutyCalculator_Response
	 */
	public function calculateImportDutyAndTaxes($items, $countryFrom, $countryTo, $province = '', $shipping, $insurance, $currency, $classifyBy = self::CLASSIFY_BY_CATEGORY_AND_DESCRIPTION, $outputCurrency = false, $includeHsCodes = false, $detailedResult = true, $shipmentWeight = -1, $commercialImporter = false, $importedWeight = 0, $importedValue = -1, $additionalParams=array())
	{
		$params = array();
		$params['from'] = $countryFrom;
		$params['to'] = $countryTo;
		$params['province'] = $province;
		$params['classify_by'] = $classifyBy;
		$params['cat'] = array();
		$params['hs'] = array();
		$params['country_of_hs_code'] = array();
		$params['desc'] = array();
		$params['sku'] = array();
		$params['value'] = array();
		$params['weight'] = array();
		$params['weight_unit'] = array();
		$params['qty'] = array();
		$params['origin'] = array();
		$params['reference'] = array();
		$params['volume'] = array();
		$params['volume_unit'] = array();
		$params['pcs'] = array();
		$params['pcs_unit'] = array();
		$params['area'] = array();
		$params['area_unit'] = array();
		$params['pairs'] = array();
		$params['pairs_unit'] = array();
		$params['jwl'] = array();
		$params['jwl_unit'] = array();
		$params['power'] = array();
		$params['power_unit'] = array();
		$i = 0;
		foreach ($items as $item)
		{
			if (isset($item['cat']))
			{
				$params['cat'][$i] = $item['cat'];
			}
			if (isset($item['hs']))
			{
				$params['hs'][$i] = $item['hs'];
			}
			if (isset($item['country_of_hs_code']))
			{
				$params['country_of_hs_code'][$i] = $item['country_of_hs_code'];
			}
			if (isset($item['desc']))
			{
				$params['desc'][$i] = $item['desc'];
			}
			if (isset($item['sku']))
			{
				$params['sku'][$i] = $item['sku'];
			}
			if (isset($item['value']))
			{
				$params['value'][$i] = $item['value'];
			}
			if (isset($item['weight_unit']))
			{
				$params['weight_unit'][$i] = $item['weight_unit'];
			}
			if (isset($item['weight']))
			{
				$params['weight'][$i] = $item['weight'];
			}
			if (isset($item['qty']))
			{
				$params['qty'][$i] = $item['qty'];
			}
			if (isset($item['origin']))
			{
				$params['origin'][$i] = $item['origin'];
			}
			if (isset($item['reference']))
			{
				$params['reference'][$i] = $item['reference'];
			}
			if (isset($item['volume']))
			{
				$params['volume'][$i] = $item['volume'];
			}
			if (isset($item['volume_unit']))
			{
				$params['volume_unit'][$i] = $item['volume_unit'];
			}
			if (isset($item['pcs']))
			{
				$params['pcs'][$i] = $item['pcs'];
			}
			if (isset($item['pcs_unit']))
			{
				$params['pcs_unit'][$i] = $item['pcs_unit'];
			}
			if (isset($item['area']))
			{
				$params['area'][$i] = $item['area'];
			}
			if (isset($item['area_unit']))
			{
				$params['area_unit'][$i] = $item['area_unit'];
			}
			if (isset($item['pairs']))
			{
				$params['pairs'][$i] = $item['pairs'];
			}
			if (isset($item['pairs_unit']))
			{
				$params['pairs_unit'][$i] = $item['pairs_unit'];
			}
			if (isset($item['jwl']))
			{
				$params['jwl'][$i] = $item['jwl'];
			}
			if (isset($item['jwl_unit']))
			{
				$params['jwl_unit'][$i] = $item['jwl_unit'];
			}
			if (isset($item['power']))
			{
				$params['power'][$i] = $item['power'];
			}
			if (isset($item['power_unit']))
			{
				$params['power_unit'][$i] = $item['power_unit'];
			}
			$i++;
		}

		if (is_array($shipping) && is_array($insurance))
		{
			foreach ($shipping as $num => $shippingOption)
			{
				$params['shipping'][] = $shippingOption;
				$params['insurance'][] = $insurance[$num];
			}
		}
		else
		{
			$params['shipping'] = $shipping;
			$params['insurance'] = $insurance;
		}

		$params['currency'] = $currency;
		$params['output_currency'] = ($outputCurrency !== false ? $outputCurrency : $currency);
		$params['shipment_wt'] = $shipmentWeight;
		$params['detailed_result'] = $detailedResult;
		$params['incl_hs_codes'] = $includeHsCodes;

		$params['commercial_importer'] = $commercialImporter;
		$params['imported_wt'] = $importedWeight;
		$params['imported_value'] = $importedValue;
		
		foreach($additionalParams as $key=>$value){
            		$params[$key]=$value;
        	}
		
		$result = $this->sendRequest('calculation', $params);
		return $result;
	}

	/**
	 * Returns the import duties and taxes for a specified invoice.
	 *
	 * Parameter reference is the item reference that was used in the original duty & tax calculation (referred to by calculation_id parameter) which is being shipped in a split shipment or refunded.
	 * Parameter qty indicated the quantity of items being shipped or refunded.
	 *
	 * $items parameter is array of items with single item structure:
	 * array("reference" => {product reference},
	 * 		 "qty" => {item quantity})
	 *
	 * @param $items
	 * @param $calculationId
	 * @param $outputCurrency
	 * @param $shipping
	 * @param bool $detailedResult
	 * @param bool $includeHsCodes
	 * @return DutyCalculator_Response
	 */
	public function invoiceCalculation($items, $calculationId, $outputCurrency, $shipping = 0, $detailedResult = true, $includeHsCodes = false)
	{
		$params = array();
		$params['calculation_id'] = $calculationId;
		$params['shipping'] = $shipping;
		$params['detailed_result'] = $detailedResult;
		$params['output_currency'] = $outputCurrency;
		$params['incl_hs_codes'] = $includeHsCodes;
		$params['reference'] = array();
		$params['qty'] = array();
		$i = 0;
		foreach ($items as $item)
		{
			if (isset($item['reference']))
			{
				$params['reference'][$i] = $item['reference'];
			}
			if (isset($item['qty']))
			{
				$params['qty'][$i] = $item['qty'];
			}
			$i++;
		}
		$result = $this->sendRequest('invoice_calculation', $params);
		return $result;
	}

	/**
	 * Returns the import duties and taxes for a specified shipment.
	 *
	 * Parameter reference is the item reference that was used in the original duty & tax calculation (referred to by calculation_id parameter) which is being shipped in a split shipment or refunded.
	 * Parameter qty indicated the quantity of items being shipped or refunded.
	 *
	 * $items parameter is array of items with single item structure:
	 * array("reference" => {product reference},
	 * 		 "qty" => {item quantity})
	 *
	 * @param $items
	 * @param $calculationId
	 * @param $outputCurrency
	 * @param int $shipping
	 * @param bool $detailedResult
	 * @param bool $includeHsCodes
	 * @return DutyCalculator_Response
	 */
	public function shipmentCalculation($items, $calculationId, $outputCurrency, $shipping = 0, $detailedResult = true, $includeHsCodes = false)
	{
		$params = array();
		$params['calculation_id'] = $calculationId;
		$params['shipping'] = $shipping;
		$params['detailed_result'] = $detailedResult;
		$params['output_currency'] = $outputCurrency;
		$params['incl_hs_codes'] = $includeHsCodes;
		$params['reference'] = array();
		$params['qty'] = array();
		$i = 0;
		foreach ($items as $item)
		{
			if (isset($item['reference']))
			{
				$params['reference'][$i] = $item['reference'];
			}
			if (isset($item['qty']))
			{
				$params['qty'][$i] = $item['qty'];
			}
			$i++;
		}
		$result = $this->sendRequest('shipment_calculation', $params);
		return $result;
	}

	/**
	 * Returns the import duties and taxes for a specified credit note.
	 *
	 * Parameter reference is the item reference that was used in the original duty & tax calculation (referred to by calculation_id parameter) which is being shipped in a split shipment or refunded.
	 * Parameter qty indicated the quantity of items being shipped or refunded.
	 *
	 * $items parameter is array of items with single item structure:
	 * array("reference" => {product reference},
	 * 		 "qty" => {item quantity})
	 *
	 * @param $items
	 * @param $calculationId
	 * @param $outputCurrency
	 * @param int $shipping
	 * @param bool $detailedResult
	 * @param bool $includeHsCodes
	 * @return DutyCalculator_Response
	 */
	public function creditNoteCalculation($items, $calculationId, $outputCurrency, $shipping = 0, $detailedResult = true, $includeHsCodes = false)
	{
		$params = array();
		$params['calculation_id'] = $calculationId;
		$params['shipping'] = $shipping;
		$params['detailed_result'] = $detailedResult;
		$params['output_currency'] = $outputCurrency;
		$params['incl_hs_codes'] = $includeHsCodes;
		$params['reference'] = array();
		$params['qty'] = array();
		$i = 0;
		foreach ($items as $item)
		{
			if (isset($item['reference']))
			{
				$params['reference'][$i] = $item['reference'];
			}
			if (isset($item['qty']))
			{
				$params['qty'][$i] = $item['qty'];
			}
			$i++;
		}
		$result = $this->sendRequest('credit_note_calculation', $params);
		return $result;
	}

	/**
	 * Stores a calculation result with DutyCalculator, with all associated data, for your reporting and compliance purposes, usually once an order has been actually fulfilled.
	 *
	 * Parameter $orderType accept following values:
	 * - DutyCalculator_Client::STORE_CALCULATION_ORDER_TYPE_ORDER
	 * - DutyCalculator_Client::STORE_CALCULATION_ORDER_TYPE_CREDIT_NOTE
	 *
	 * If $orderType is DutyCalculator_Client::STORE_CALCULATION_ORDER_TYPE_ORDER parameter $shipmentId is required.
	 * If $orderType is DutyCalculator_Client::STORE_CALCULATION_ORDER_TYPE_CREDIT_NOTE parameter $creditNoteId is required.
	 *
	 * @param $calculationId
	 * @param $orderId
	 * @param string $orderType
	 * @param string $shipmentId
	 * @param string $creditNoteId
	 * @return DutyCalculator_Response
	 */
	public function storeCalculation($calculationId, $orderId, $orderType = self::STORE_CALCULATION_ORDER_TYPE_ORDER, $shipmentId = '', $creditNoteId = '')
	{
		$params = array();
		$params['calculation_id'] = $calculationId;
		$params['order_id'] = $orderId;
		$params['order_type'] = $orderType;
		$params['shipment_id'] = $shipmentId;
		$params['credit_note_id'] = $creditNoteId;

		$result = $this->sendRequest('store_calculation', $params);
		return $result;
	}

	/**
	 * Returns the URLs to the documents of the shipment (commercial invoice & packing list) for a specified duty & taxes calculation.
	 *
	 * Parameter $shipmentData has following structure:
	 * array("invoice_no" => {seller internal invoice number},
	 * 		 "date" => {shipment date, e.g. 2012-03-26},
	 * 		 "number_parcels" => {number of shipment parcels},
	 * 		 "total_actual_weight" => {shipment total actual weight in KG},
	 * 		 "total_dimensional_weight" => {shipment total dimensional weight in KG},
	 * 		 "currency_sale" => {shipment currency of sale ISO code},
	 * 		 "tracking_id" => {shipment tracking number},
	 * 		 "incoterms" => {shipment incoterms})
	 *
	 * Parameters $sellerAddress, $shippingAddress and $billingAddress have following structure:
	 * array("first_name" => {first name (seller, payer or receiver)},
	 * 		 "last_name" => {last name},
	 * 		 "address_line_1" => {address line 1},
	 * 		 "address_line_2" => {address line 2},
	 * 		 "importer_type" => {importer type (DutyCalculator_Client::IMPORTER_TYPE_COMMERCIAL or DutyCalculator_Client::IMPORTER_TYPE_PRIVATE)},
	 * 		 "company_name" => {company name},
	 * 		 "company_vat_number" => {company tax/VAT number},
	 * 		 "city" => {city},
	 * 		 "zip" => {ZIP code},
	 * 		 "country" => {ISO alpha-3 country code or alpha-2 country code},
	 * 		 "phone" => {phone number},
	 * 		 "tax_id" => {tax ID})
	 *
	 * @param $calculationId
	 * @param $outputCurrency
	 * @param $shipmentData
	 * @param $sellerAddress
	 * @param $shippingAddress
	 * @param $billingAddress
	 * @param $printFirstName
	 * @param $printLastName
	 * @param $printDate
	 * @return DutyCalculator_Response
	 */
	public function documents($calculationId, $outputCurrency, $shipmentData, $sellerAddress, $shippingAddress, $billingAddress, $printFirstName, $printLastName, $printDate)
	{
		$params = array();
		$params['calculation_id'] = $calculationId;
		$params['output_currency'] = $outputCurrency;

		$params['shipment_invoice_no'] = (isset($shipmentData['invoice_no']) ? $shipmentData['invoice_no'] : '');
		$params['shipment_date'] = (isset($shipmentData['date']) ? $shipmentData['date'] : '');
		$params['shipment_number_parcels'] = (isset($shipmentData['number_parcels']) ? $shipmentData['number_parcels'] : '');
		$params['shipment_total_actual_weight'] = (isset($shipmentData['total_actual_weight']) ? $shipmentData['total_actual_weight'] : '');
		$params['shipment_total_dimensional_weight'] = (isset($shipmentData['total_dimensional_weight']) ? $shipmentData['total_dimensional_weight'] : '');
		$params['shipment_currency_sale'] = (isset($shipmentData['currency_sale']) ? $shipmentData['currency_sale'] : '');
		$params['shipment_tracking_id'] = (isset($shipmentData['tracking_id']) ? $shipmentData['tracking_id'] : '');
		$params['shipment_incoterms'] = (isset($shipmentData['incoterms']) ? $shipmentData['incoterms'] : '');

		$params['seller_first_name'] = (isset($sellerAddress['first_name']) ? $sellerAddress['first_name'] : '');
		$params['seller_last_name'] = (isset($sellerAddress['last_name']) ? $sellerAddress['last_name'] : '');
		$params['seller_address_line_1'] = (isset($sellerAddress['address_line_1']) ? $sellerAddress['address_line_1'] : '');
		$params['seller_address_line_2'] = (isset($sellerAddress['address_line_2']) ? $sellerAddress['address_line_2'] : '');
		$params['seller_importer_type'] = (isset($sellerAddress['importer_type']) ? $sellerAddress['importer_type'] : '');
		$params['seller_company_name'] = (isset($sellerAddress['company_name']) ? $sellerAddress['company_name'] : '');
		$params['seller_company_vat_number'] = (isset($sellerAddress['company_vat_number']) ? $sellerAddress['company_vat_number'] : '');
		$params['seller_city'] = (isset($sellerAddress['city']) ? $sellerAddress['city'] : '');
		$params['seller_zip'] = (isset($sellerAddress['zip']) ? $sellerAddress['zip'] : '');
		$params['seller_country'] = (isset($sellerAddress['country']) ? $sellerAddress['country'] : '');
		$params['seller_phone'] = (isset($sellerAddress['phone']) ? $sellerAddress['phone'] : '');
		$params['seller_tax_id'] = (isset($sellerAddress['tax_id']) ? $sellerAddress['tax_id'] : '');

		$params['shipto_first_name'] = (isset($shippingAddress['first_name']) ? $shippingAddress['first_name'] : '');
		$params['shipto_last_name'] = (isset($shippingAddress['last_name']) ? $shippingAddress['last_name'] : '');
		$params['shipto_address_line_1'] = (isset($shippingAddress['address_line_1']) ? $shippingAddress['address_line_1'] : '');
		$params['shipto_address_line_2'] = (isset($shippingAddress['address_line_2']) ? $shippingAddress['address_line_2'] : '');
		$params['shipto_importer_type'] = (isset($shippingAddress['importer_type']) ? $shippingAddress['importer_type'] : '');
		$params['shipto_company_name'] = (isset($shippingAddress['company_name']) ? $shippingAddress['company_name'] : '');
		$params['shipto_company_vat_number'] = (isset($shippingAddress['company_vat_number']) ? $shippingAddress['company_vat_number'] : '');
		$params['shipto_city'] = (isset($shippingAddress['city']) ? $shippingAddress['city'] : '');
		$params['shipto_zip'] = (isset($shippingAddress['zip']) ? $shippingAddress['zip'] : '');
		$params['shipto_country'] = (isset($shippingAddress['country']) ? $shippingAddress['country'] : '');
		$params['shipto_phone'] = (isset($shippingAddress['phone']) ? $shippingAddress['phone'] : '');
		$params['shipto_tax_id'] = (isset($shippingAddress['tax_id']) ? $shippingAddress['tax_id'] : '');

		$params['soldto_first_name'] = (isset($billingAddress['first_name']) ? $billingAddress['first_name'] : '');
		$params['soldto_last_name'] = (isset($billingAddress['last_name']) ? $billingAddress['last_name'] : '');
		$params['soldto_address_line_1'] = (isset($billingAddress['address_line_1']) ? $billingAddress['address_line_1'] : '');
		$params['soldto_address_line_2'] = (isset($billingAddress['address_line_2']) ? $billingAddress['address_line_2'] : '');
		$params['soldto_importer_type'] = (isset($billingAddress['importer_type']) ? $billingAddress['importer_type'] : '');
		$params['soldto_company_name'] = (isset($billingAddress['company_name']) ? $billingAddress['company_name'] : '');
		$params['soldto_company_vat_number'] = (isset($billingAddress['company_vat_number']) ? $billingAddress['company_vat_number'] : '');
		$params['soldto_city'] = (isset($billingAddress['city']) ? $billingAddress['city'] : '');
		$params['soldto_zip'] = (isset($billingAddress['zip']) ? $billingAddress['zip'] : '');
		$params['soldto_country'] = (isset($billingAddress['country']) ? $billingAddress['country'] : '');
		$params['soldto_phone'] = (isset($billingAddress['phone']) ? $billingAddress['phone'] : '');
		$params['soldto_tax_id'] = (isset($billingAddress['tax_id']) ? $billingAddress['tax_id'] : '');

		$params['print_first_name'] = $printFirstName;
		$params['print_last_name'] = $printLastName;
		$params['print_date'] = $printDate;

		$result = $this->sendRequest('documents', $params);
		return $result;
	}

	/**
	 * Screens the party against the consolidated USA export screening list.
	 * Returns matching code (Red, Amber or Green) and link to resolution page on DutyCalculator with all information on matched parties provided.
	 * Alternatively all information on matched parties can be returned with the API call.
	 * Denied party lists from other countries will be added shortly.
	 *
	 * @param $countryFrom
	 * @param $countryTo
	 * @param $nameTo
	 * @param $cityTo
	 * @param $zipTo
	 * @param $addressTo
	 * @param bool $detailedResult
	 * @return DutyCalculator_Response
	 */
	public function checkRestrictedParty($countryFrom, $countryTo, $nameTo, $cityTo, $zipTo, $addressTo, $detailedResult = true)
	{
		$params = array();
		$params['country_from'] = $countryFrom;
		$params['name_to'] = $nameTo;
		$params['country_to'] = $countryTo;
		$params['city_to'] = $cityTo;
		$params['zip_to'] = $zipTo;
		$params['address_to'] = $addressTo;
		$params['detailed_result'] = $detailedResult;

		$result = $this->sendRequest('check-restricted-party', $params);
		return $result;
	}

	/**
	 * API request returns the list of product descriptions that match the provided partial description.
	 *
	 * @param string $description
	 * @param int $limit
	 * @return DutyCalculator_Response
	 */
	public function descriptionAutoComplete($description, $limit = 8)
	{
		$params = array();
		$params['query'] = $description;
		$params['limit'] = $limit;
		$params['remote_addr'] = (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '');

		$result = $this->sendRequest('autocomplete', $params);
		return $result;
	}
}
