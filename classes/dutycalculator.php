<?php defined('SYSPATH') OR die('No direct access allowed.');

class DutyCalculator {

	protected $_config;

	protected static $_instance;

	public static function instance()
	{
		if ( ! isset(DutyCalculator::$_instance))
		{
			$config = Kohana::$config->load('dutycalculator');

            DutyCalculator::$_instance = new DutyCalculator($config);
		}
		
		return DutyCalculator::$_instance;
	}
	
	private function __construct($config)
	{
		$this->_config = $config;
	}

    private function _request($url_string)
    {
        $request = $this->_config['domain'].$this->_config['key'].$url_string;

        $context = stream_context_create(array(
            'http' => array('ignore_errors' => true)
        ));

        $response = file_get_contents($request, FALSE, $context);

        libxml_use_internal_errors(true);

        $xml = simplexml_load_string($response);

        if (!$xml) {
            throw new Kohana_Exception('DutyCalculator API Error: :error (:request)',
                array(':error' => $response, ':request' => $request));

            libxml_clear_errors();
        }

        return $xml;

    }

    public function countries_to()
    {
        $response = $this->_request('/supported-countries/to');

        $return = array();
        foreach ($response->country as $country)
        {
            $code = (string)$country->attributes()->code;
            $name = (string)$country;
            $return[$code] = array('code' => $code, 'name' => $name);
        }

        return $return;
    }

    public function countries_from()
    {
        $response = $this->_request('/supported-countries/from');

        $return = array();
        foreach ($response->country as $country)
        {
            $code = (string)$country->attributes()->code;
            $name = (string)$country;
            $return[$code] = array('code' => $code, 'name' => $name);
        }

        return $return;
    }

    public function calculation($request)
    {
        $response = $this->_request('/calculation'.URL::query($request));

        $return = $response;

        return $return;
    }

}
