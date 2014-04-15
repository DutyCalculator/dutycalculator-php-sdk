<?php

class DutyCalculator_Response
{
    /** @var  string */
	protected $_responseXML;
    /** @var DutyCalculator_Request  */
    protected $_request;

    /**
     * @param $responseXML string
     * @param DutyCalculator_Request $request
     */
    public function __construct($responseXML,DutyCalculator_Request $request)
	{
		$this->_responseXML = $responseXML;
        $this->_request = $request;
		$this->checkError();
	}

    /** @return DutyCalculator_Request */
    public function getRequest()
    {
        return $this->_request;
    }

	/** @return string */
	public function getAsXML()
	{
		return $this->_responseXML;
	}

	/** @return array */
	public function getAsArray()
	{
		$root = simplexml_load_string($this->_responseXML);
		$result = $this->convertToArray($root);
		return $result;
	}

    /**
     * @param SimpleXMLElement $node
     * @return array
     */
    private function convertToArray(SimpleXMLElement $node)
	{
		$result = array();
		$nodeData = array();

        /** @var $attribute SimpleXMLElement */
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
			$nodeData['node-value'] = $value;
		}
		$result[$node->getName()] = $nodeData;
		return $result;
	}

	/** @return string */
	public function getAsJSON()
	{
		$result = $this->getAsArray();
		return json_encode($result);
	}

    /** @throws DutyCalculator_Exception */
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