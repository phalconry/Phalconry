<?php

namespace Phalconry\Http\Response;

class ResponderFactory
{
	
	protected $_classes = array(
		'' => 'Phalconry\Http\Response\NullResponder',
		'view' => 'Phalconry\Http\Response\ViewResponder',
		'json' => 'Phalconry\Http\Response\JsonResponder',
		'xml' => 'Phalconry\Http\Response\XmlResponder',
	);
	
	public function setTypeClass($responseType, $responderClass) {
		if (false === $responseType) {
			$responseType = '';
		}
		$this->_classes[$responseType] = $responderClass;
	}
	
	public function getTypeClass($responseType) {
		if (false === $responseType) {
			$responseType = '';
		}
		return isset($this->_classes[$responseType]) ? $this->_classes[$responseType] : null;
	}
	
	public function __invoke($responseType) {
		if ($class = $this->getTypeClass($responseType)) {
			return new $class();
		}
		throw new \InvalidArgumentException("No responder for response type: '{$responseType}'.");
	}
	
	public function factory($responseType) {
		return $this($responseType);
	}
	
}
