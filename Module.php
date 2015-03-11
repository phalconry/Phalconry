<?php

namespace Phalconry;

use Phalcon\DI;
use Phalcon\Assets\Manager as AssetsManager;
use Phalcon\Mvc\View;
use Phalcon\Mvc\ModuleDefinitionInterface;

/**
 * Module
 * 
 * This class is "pseudo-DI-aware" in that its getDI() method returns the default
 * DI container (i.e. using DI::getDefault()).
 */
abstract class Module implements ModuleDefinitionInterface
{
	
	/**
	 * @var \Phalconry\Application
	 */
	protected $_application;
	
	/**
	 * Returns the DI container
	 * @return \Phalcon\DiInterface
	 */
	public function getDI() {
		return DI::getDefault();
	}
	
	/**
	 * Returns a shared item from the DI container
	 * @param string $key
	 * @return mixed
	 */
	public function __get($key) {
		return $this->getDI()->getShared($key);
	}
	
	/**
	 * Sets the application
	 * @param \Phalconry\Application $app
	 */
	public function setApp(Application $app) {
		$this->_application = $app;
	}
	
	/**
	 * Returns the application
	 * @return \Phalconry\Application
	 * @throws \RuntimeException if app is not set
	 */
	public function getApp() {
		if (! isset($this->_application)) {
			throw new \RuntimeException("Module is not active");
		}
		return $this->_application;
	}
	
	/**
	 * Register separate autoloaders for the module, if any
	 */
	public function registerAutoloaders() {
		
	}
	
	/**
	 * Returns the default namespace to use for controllers.
	 * 
	 * Called in Application on "application:afterStartModule"
	 * 
	 * @return string
	 */
	abstract public function getControllerNamespace();
	
	/**
	 * Allows the module to perform start-up tasks
	 * 
	 * Called in Application on "application:afterStartModule"
	 */
	public function onLoad() {
		
	}
	
	/**
	 * Register assets for the module
	 * 
	 * Called in Application on "view:beforeRender"
	 * 
	 * @param \Phalcon\Assets\Manager $assetsManager
	 */
	public function registerAssets(AssetsManager $assetsManager) {
		
	}
	
	/**
	 * Allows the module to configure the view
	 * 
	 * Called in Application on "application:afterHandleRequest"
	 * ONLY IF Phalconry\Respond\Responder mode is 'view' - otherwise, the view is disabled
	 * 
	 * @param \Phalcon\Mvc\View $view
	 */
	public function configureView(View $view) {
		
	}
	
}
