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
	
	/**
	 * Module name
	 * @var string
	 */
	protected $_module;
	
	/**
	 * Controller name
	 * @var string
	 */
	protected $_controller;
	
	/**
	 * Action name
	 * @var string
	 */
	protected $_action;
	
	/**
	 * Request parameters
	 * @var array
	 */
	protected $_params;
	
	/**
	 * Request response
	 * @var mixed
	 */
	protected $_response;
	
	/**
	 * HmvcRequest constructor.
	 * 
	 * @param array $args [Optional] Request args {@see set()}
	 */
	public function __construct(array $args = null) {
		if (isset($args)) {
			$this->set($args);
		}
	}
	
	/**
	 * Sets module, controller, action, and/or params from an array.
	 * 
	 * @param array $args
	 * @return $this
	 */
	public function set(array $args) {
		if (isset($args['module'])) {
			$this->setModuleName($args['module']);
		}
		if (isset($args['controller'])) {
			$this->setControllerName($args['controller']);
		}
		if (isset($args['action'])) {
			$this->setActionName($args['action']);
		}
		if (isset($args['params'])) {
			$this->setParams($args['params']);
		}
		return $this;
	}
	
	/**
	 * Sets the module name
	 * 
	 * @param string $module Module name
	 * @return $this
	 */
	public function setModuleName($module) {
		$this->_module = $module;
		return $this;
	}
	
	/**
	 * Returns the module name
	 * 
	 * @return string
	 */
	public function getModuleName() {
		return $this->_module;
	}
	
	/**
	 * Sets the controller name
	 * 
	 * @param string $controller Controller name
	 * @return $this
	 */
	public function setControllerName($controller) {
		$this->_controller = $controller;
		return $this;
	}
	
	/**
	 * Returns the controller name. Default "index"
	 * 
	 * @return string
	 */
	public function getControllerName() {
		return empty($this->_controller) ? 'index' : $this->_controller;
	}
	
	/**
	 * Sets the action name
	 * 
	 * @param string $action Action name
	 * @return $this
	 */
	public function setActionName($action) {
		$this->_action = $action;
		return $this;
	}
	
	/**
	 * Returns the action name. Default "index"
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
	 * @param array $args [Optional]
	 * @return mixed
	 */
	public function __invoke(array $args = null) {
		
		if (isset($args)) {
			$this->set($args);
		}
		
		$dispatcher = $this->getDispatcher();
		
		if ($moduleName = $this->getModuleName()) {
			$this->prepareModuleForDispatch($dispatcher, $moduleName);
		}
		
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
	
	/**
	 * Returns the response
	 * 
	 * @return mixed
	 */
	public function getResponse() {
		return $this->_response;
	}
	
	/**
	 * Returns the dispatcher to use for the request
	 * 
	 * @return \Phalcon\Mvc\Dispatcher
	 */
	protected function getDispatcher() {
		return clone $this->getDI()->get('dispatcher');
	}
	
	/**
	 * Prepares to dispatch to a module
	 * 
	 * The module is loaded if not already. If it's not the primary module, the
	 * default controller namespace is reset on the cloned dispatcher.
	 * 
	 * @param \Phalconry\Dispatcher $dispatcher
	 * @param string $moduleName
	 */
	protected function prepareModuleForDispatch(Dispatcher $dispatcher, $moduleName) {
		
		$app = $this->getDI()->getApp();
		
		if ($moduleName !== $app->getPrimaryModuleName()) {
		
			if ($app->isModuleLoaded($moduleName)) {
				$module = $app->getModule($moduleName);
			} else {
				$module = $app->loadModule($moduleName);
			}
		
			$dispatcher->setDefaultNamespace($module->getControllerNamespace());
		}
	}
	
}
