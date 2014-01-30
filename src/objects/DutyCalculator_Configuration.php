<?php

class DutyCalculator_Configuration
{
	const RESPONSE_FORMAT_XML = 'xml';
	const RESPONSE_FORMAT_ARRAY = 'array';
	const RESPONSE_FORMAT_JSON = 'json';

	protected $_apiKey;
	protected $_endPoint = 'stagebeta3.dutycalculator.com/api2.1';
	protected $_useSSL = false;

	public function setApiKey($apiKey)
	{
		$this->_apiKey = $apiKey;
		return $this;
	}

	public function getApiKey()
	{
		return $this->_apiKey;
	}

	public function setUseSSL($useSSL = false)
	{
		$this->_useSSL = (bool)$useSSL;
		return $this;
	}

	public function getUseSSL()
	{
		return $this->_useSSL;
	}

	public function getEndPoint()
	{
		return ($this->getUseSSL() ? 'https://' : 'http://') . $this->_endPoint . '/' . $this->getApiKey() . '/';
	}
}