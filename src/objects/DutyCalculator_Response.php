<?php

class DutyCalculator_Response
{
	protected $_responseXML;

	public function __construct($responseXML)
	{
		$this->_responseXML = $responseXML;
		$this->checkError();
	}

	/**
	 * @return string
	 */
	public function getAsXML()
	{
		return $this->_responseXML;
	}

	/**
	 * @return array
	 */
	public function getAsArray()
	{
		$root = simplexml_load_string($this->_responseXML);
		$result = $this->convertToArray($root);
		return $result;
	}

	private function convertToArray(SimpleXMLElement $node)
	{
		$result = array();
		$nodeData = array();

		foreach($node->attributes() as $attribute)
		{
			$nodeData[$attribute->getName()] = (string)$attribute;
		}
		if ($node->count())
		{
			$nodeData['node-value'] = array();
			foreach ($node as $child)
			{
				$nodeData['node-value'][] = $this->convertToArray($child);
			}
		}
		else
		{
			$value = trim((string)$node);
			if ($value)
			{
				$nodeData['node-value'] = $value;
			}
		}
		$result[$node->getName()] = $nodeData;
		return $result;
	}

	/**
	 * @return string
	 */
	public function getAsJSON()
	{
		$result = $this->getAsArray();
		return json_encode($result);
	}

	private function checkError()
	{
		if (stripos($this->_responseXML, '<?xml') === false)
		{
			throw new DutyCalculator_Exception('Response is not a valid XML.', 0);
		}
		else
		{
			$xml = simplexml_load_string($this->_responseXML);
			if (isset($xml->code) && (string)$xml->code)
			{
				throw new DutyCalculator_Exception((string)$xml->message, (string)$xml->code);
			}
		}
	}
}