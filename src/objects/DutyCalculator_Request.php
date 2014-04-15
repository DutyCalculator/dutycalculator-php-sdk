<?php

class DutyCalculator_Request
{
    /** @var DutyCalculator_Configuration  */
	protected $_config;
    /** @var  string */
	protected $_action;
    /** @var  string */
    protected $_uri;
    /** @var array */
	protected $_params = array();

    /**
     * @param DutyCalculator_Configuration $config
     */
    public function __construct(DutyCalculator_Configuration $config)
	{
		$this->_config = $config;
	}

    /**
     * @param $action string
     * @return $this
     */
    public function setAction($action)
	{
		$this->_action = $action;
		return $this;
	}

    /** @return string */
    public function getAction()
	{
		return $this->_action;
	}

    /**
     * @param $params array
     * @return $this
     */
    public function setParams($params)
	{
		$this->_params = $params;
		return $this;
	}

    /** @return array */
	public function getParams()
	{
		return $this->_params;
	}

    /** @return string */
    public function getUri()
    {
        return $this->_uri;
    }

    /**
     * Send request to DutyCalculator API
     * @return DutyCalculator_Response
     * @throws DutyCalculator_Exception
     */
    public function send()
	{
		$this->_uri = $this->_config->getEndPoint() . $this->getAction() . '/';
		$params = $this->getParams();
		if ($params)
		{
            $this->_uri .= '?';
			foreach ($params as $key => $param)
			{
				if (is_array($param))
				{
					foreach ($param as $idx => $value)
					{
                        $this->_uri .= $key . '[' . $idx . ']=' . urlencode($value) . '&';
					}
				}
				else
				{
                    $this->_uri .= $key . '=' . urlencode($param) . '&';
				}
			}
		}
        $this->_uri = rtrim($this->_uri, '&');

		if (function_exists('curl_version'))
		{
			$curlHandler = curl_init();
			curl_setopt($curlHandler, CURLOPT_URL, $this->_uri);
			curl_setopt($curlHandler, CURLOPT_POST, 0);
			ob_start();
			$result = curl_exec($curlHandler);
			$responseXML = ob_get_contents();
			ob_end_clean();
			if (!$result)
			{
				throw new DutyCalculator_Exception((string)curl_error($curlHandler),(int)curl_errno($curlHandler));
			}
			curl_close($curlHandler);
		}
		else
		{
			$responseXML = file_get_contents($this->_uri);
			if ($responseXML === false)
			{
				throw new DutyCalculator_Exception('Can not receive the response from DutyCalculator.', '0');
			}
		}
		return new DutyCalculator_Response($responseXML,$this);
	}
}