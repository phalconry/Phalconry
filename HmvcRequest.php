<?php

namespace Phalconry;

use Phalcon\DiInterface;
use Phalcon\DI\Injectable;
use Phalcon\Http\ResponseInterface;
use Phalcon\Http\Response;

/**
 * Wrapper for an HMVC request.
 */
class HmvcRequest extends Injectable
{
	protected $_controller;
	protected $_action;
	protected $_params;
	protected $_response;
	
	public function __construct($controller = null, $action = null) {
		if (isset($controller)) {
			$this->setControllerName($controller);
		}
		if (isset($action)) {
			$this->setActionName($action);
		}
	}
	
	/**
	 * Sets the controller name
	 * 
	 * @param string $controller Controller name. Default "index".
	 * @return $this
	 */
	public function setControllerName($controller) {
		$this->_controller = $controller;
		return $this;
	}
	
	/**
	 * Returns the controller name
	 * 
	 * @return string
	 */
	public function getControllerName() {
		return empty($this->_controller) ? 'index' : $this->_controller;
	}
	
	/**
	 * Sets the action name
	 * 
	 * @param string $action Action name. Default "index".
	 * @return $this
	 */
	public function setActionName($action) {
		$this->_action = $action;
		return $this;
	}
	
	/**
	 * Returns the action name
	 * 
	 * @return string
	 */
	public function getActionName() {
		return empty($this->_action) ? 'index' : $this->_action;
	}
	
	/**
	 * Sets the request parameters
	 * 
	 * @param array|string $params Parameters. Default empty array.
	 * @return $this
	 */
	public function setParams($params) {
		if (! is_array($params)) {
			$params = (array)$params;
		}
		$this->_params = $params;
		return $this;
	}
	
	/**
	 * Returns the request parameters
	 * 
	 * @return array
	 */
	public function getParams() {
		return empty($this->_params) ? array() : $this->_params;
	}
	
	/**
	 * Dispatches the request and returns a response
	 *
	 * @return mixed
	 */
	public function __invoke($controller = null, $action = null, $params = null) {
		
		if (isset($controller)) {
			$this->setControllerName($controller);
		}
		if (isset($action)) {
			$this->setActionName($action);
		}
		if (isset($params)) {
			$this->setParams($params);
		}
		
		$dispatcher = clone $this->getDI()->get('dispatcher');
		
		$dispatcher->setControllerName($this->getControllerName());
		$dispatcher->setActionName($this->getActionName());
		$dispatcher->setParams($this->getParams());
		
		$dispatcher->dispatch();
		
		$this->_response = $dispatcher->getReturnedValue();
		
		if ($this->_response instanceof ResponseInterface) {
			return $this->_response->getContent();
		}
		
		return $this->_response;
	}
	
	public function getResponse() {
		return $this->_response;
	}
	
}
