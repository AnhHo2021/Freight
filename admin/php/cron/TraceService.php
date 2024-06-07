<?php

if (!class_exists("getTraceData")) {
/**
 * getTraceData
 */
class getTraceData {
	/**
	 * @access public
	 * @var string
	 */
	public $pro;
	/**
	 * @access public
	 * @var string
	 */
	public $type;
}}

if (!class_exists("TraceResult")) {
/**
 * TraceResult
 */
class TraceResult {
	/**
	 * @access public
	 * @var string
	 */
	public $proNum;
	/**
	 * @access public
	 * @var string
	 */
	public $proDate;
	/**
	 * @access public
	 * @var string
	 */
	public $statusCode;
	/**
	 * @access public
	 * @var string
	 */
	public $status;
	/**
	 * @access public
	 * @var string
	 */
	public $appointment;
	/**
	 * @access public
	 * @var string
	 */
	public $pieces;
	/**
	 * @access public
	 * @var string
	 */
	public $weight;
	/**
	 * @access public
	 * @var string
	 */
	public $po;
	/**
	 * @access public
	 * @var string
	 */
	public $bol;
	/**
	 * @access public
	 * @var string
	 */
	public $trailer;
	/**
	 * @access public
	 * @var string
	 */
	public $signature;
	/**
	 * @access public
	 * @var string
	 */
	public $origTerminal;
	/**
	 * @access public
	 * @var string
	 */
	public $origAddress;
	/**
	 * @access public
	 * @var string
	 */
	public $origState;
	/**
	 * @access public
	 * @var string
	 */
	public $origName;
	/**
	 * @access public
	 * @var string
	 */
	public $origCity;
	/**
	 * @access public
	 * @var string
	 */
	public $origZip;
	/**
	 * @access public
	 * @var string
	 */
	public $origPhone;
	/**
	 * @access public
	 * @var string
	 */
	public $origFax;
	/**
	 * @access public
	 * @var string
	 */
	public $destTerminal;
	/**
	 * @access public
	 * @var string
	 */
	public $destAddress;
	/**
	 * @access public
	 * @var string
	 */
	public $destState;
	/**
	 * @access public
	 * @var string
	 */
	public $destName;
	/**
	 * @access public
	 * @var string
	 */
	public $destCity;
	/**
	 * @access public
	 * @var string
	 */
	public $destZip;
	/**
	 * @access public
	 * @var string
	 */
	public $destPhone;
	/**
	 * @access public
	 * @var string
	 */
	public $destFax;
	/**
	 * @access public
	 * @var string
	 */
	public $delivered;
	/**
	 * @access public
	 * @var string
	 */
	public $url;
	/**
	 * @access public
	 * @var string
	 */
	public $type;
	/**
	 * @access public
	 * @var string
	 */
	public $scac;
	/**
	 * @access public
	 * @var string
	 */
	public $errorMessage;
	/**
	 * @access public
	 * @var string
	 */
	public $guaranteed;
	/**
	 * @access public
	 * @var string
	 */
	public $call;
}}

if (!class_exists("getTraceDataResponse")) {
/**
 * getTraceDataResponse
 */
class getTraceDataResponse {
	/**
	 * @access public
	 * @var TraceResult
	 */
	public $getTraceDataReturn;
}}

if (!class_exists("TraceService")) {
/**
 * TraceService
 * @author WSDLInterpreter
 */
class TraceService extends SoapClient {
	/**
	 * Default class map for wsdl=>php
	 * @access private
	 * @var array
	 */
	private static $classmap = array(
		"getTraceData" => "getTraceData",
		"TraceResult" => "TraceResult",
		"getTraceDataResponse" => "getTraceDataResponse",
	);

	/**
	 * Constructor using wsdl location and options array
	 * @param string $wsdl WSDL location for this service
	 * @param array $options Options for the SoapClient
	 */
	//public function __construct($wsdl="http://www.odfl.com/TraceWebServiceWeb/services/Trace/wsdl/Trace.wsdl", $options=array()) {
	public function __construct($wsdl="/mnt/web/transport/transport/freight/admin/php/cron/ODFLTrace.wsdl", $options=array()) {
		foreach(self::$classmap as $wsdlClassName => $phpClassName) {
		    if(!isset($options['classmap'][$wsdlClassName])) {
		        $options['classmap'][$wsdlClassName] = $phpClassName;
		    }
		}
		parent::__construct($wsdl, $options);
	}

	/**
	 * Checks if an argument list matches against a valid argument type list
	 * @param array $arguments The argument list to check
	 * @param array $validParameters A list of valid argument types
	 * @return boolean true if arguments match against validParameters
	 * @throws Exception invalid function signature message
	 */
	public function _checkArguments($arguments, $validParameters) {
		$variables = "";        
		foreach ($arguments as $arg) {
		    $type = gettype($arg);
		    if ($type == "object") {
		        $type = get_class($arg);
		    }
		    $variables .= "(".$type.")";
		}
		if (!in_array($variables, $validParameters)) {
		    throw new Exception("Invalid parameter types: ".str_replace(")(", ", ", $variables));
		}
		return true;
	}

	/**
	 * Service Call: getTraceData
	 * Parameter options:
	 * (getTraceData) parameters
	 * @param mixed,... See function description for parameter options
	 * @return getTraceDataResponse
	 * @throws Exception invalid function signature message
	 */
	public function getTraceData($mixed = null) {
		$validParameters = array(
			"(getTraceData)",
		);
		$args = func_get_args();
		$this->_checkArguments($args, $validParameters);
		return $this->__soapCall("getTraceData", $args);
	}


}}

?>
