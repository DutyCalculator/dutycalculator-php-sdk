<?php

class DutyCalculator_Request
{
	protected $_config;
	protected $_action;
	protected $_params = array();

	public function __construct(DutyCalculator_Configuration $config)
	{
		$this->_config = $config;
	}

	public function setAction($action)
	{
		$this->_action = $action;
		return $this;
	}

	public function getAction()
	{
		return $this->_action;
	}

	public function setParams($params)
	{
		$this->_params = $params;
		return $this;
	}

	public function getParams()
	{
		return $this->_params;
	}

	public function send()
	{
		$uri = $this->_config->getEndPoint() . $this->getAction() . '/';
		$params = $this->getParams();
		if ($params)
		{
			$uri .= '?';
			foreach ($params as $key => $param)
			{
				if (is_array($param))
				{
					foreach ($param as $idx => $value)
					{
						$uri .= $key . '[' . $idx . ']=' . urlencode($value) . '&';
					}
				}
				else
				{
					$uri .= $key . '=' . urlencode($param) . '&';
				}
			}
		}
		$uri = rtrim($uri, '&');

		if (function_exists('curl_version'))
		{
			$curlHandler = curl_init();
			curl_setopt($curlHandler, CURLOPT_URL, $uri);
			curl_setopt($curlHandler, CURLOPT_POST, 0);
			ob_start();
			$result = curl_exec($curlHandler);
			$responseXML = ob_get_contents();
			ob_end_clean();
			if (!$result)
			{
				throw new DutyCalculator_Exception((string)curl_errno($curlHandler), (string)curl_error($curlHandler));
			}
			curl_close($curlHandler);
		}
		else
		{
			$responseXML = file_get_contents($uri);
			if ($responseXML === false)
			{
				throw new DutyCalculator_Exception('Can not receive the response from DutyCalculator.', '0');
			}
		}
		return new DutyCalculator_Response($responseXML);
	}
}