<?php

namespace Phalconry;

use Phalcon\Config;
use Phalcon\Registry;
use Phalcon\Loader;
use Phalcon\DiInterface;
use Phalcon\DI\FactoryDefault;
use Phalcon\Http\ResponseInterface;
use Phalcon\Events\Event;
use Phalcon\Events\Manager as EventsManager;
use Phalcon\Mvc\ViewInterface;
use Phalcon\Mvc\View;
use Phalcon\Mvc\Dispatcher as MvcDispatcher;
use Phalcon\Mvc\Application as PhalconApp;
use Phalcon\Mvc\ModuleDefinitionInterface as PhalconModule;

class Application extends PhalconApp
{
	
	/**
	 * Directory path registry
	 * 
	 * @var Phalcon\Registry
	 */
	protected $_paths;
	
	/**
	 * Name of the active module
	 * 
	 * @var string
	 */
	protected $_moduleName;
	
	/**
	 * Application constructor.
	 *
	 * @param Phalcon\Config $config Global config settings
	 * @param Phalcon\Registry $pathRegistry A registry filled with named directory paths
	 */
	public function __construct(Config $config, Registry $pathRegistry) {

		$this->_paths = $config['paths'] = $pathRegistry;
		
		$di = new FactoryDefault();
		
		$di->set('app', $this, true);
		$di->set('config', $config, true);
		
		parent::__construct($di);
		
		$eventsManager = new EventsManager();
		$eventsManager->attach('application', $this);
		$this->setEventsManager($eventsManager);
	}
	
	/**
	 * Returns a directory path from the registry.
	 *
	 * @param string $name Path name.
	 * @return string
	 */
	public function getPath($name) {
		return $this->_paths[$name];
	}

	/**
	 * Sets a named directory path.
	 *
	 * @param string $name Path name.
	 * @param string $value Absolute directory path.
	 */
	public function setPath($name, $value) {
		$this->_paths[$name] = realpath($value).DIRECTORY_SEPARATOR;
	}
	
	/**
	 * Returns the active module name.
	 * 
	 * @return string
	 */
	public function getModuleName() {
		return $this->_moduleName;
	}
	
	/**
	 * Returns the active module.
	 *
	 * @return Phalcon\Mvc\ModuleDefinitionInterface
	 */
	public function getModule() {
		return $this->_moduleObject;
	}
	
	public function setResponseMode($mode) {
		$this->getDI()->getResponder()->setMode($mode);
	}
	
	/** 
	 * Runs the application and sends the response.
	 */
	public function run() {
		
		$response = $this->handle();
		$responder = $this->getDI()->getResponder();
		
		$responder->setResponse($response);
		$responder->respond();
	}
	
	/**
	 * Register class loader(s)
	 */
	protected function _registerAutoloaders() {
		$loader	= new Loader();
		$paths = $this->_paths;
		require $paths['config'].'loader.php';
		$loader->register();
	}
	
	/**
	 * Register global services
	 */
	protected function _registerServices() {
		
		$app = $this;
		$paths = $this->_paths;
		$di = $this->getDI();
		
		$di->setShared('router', function() use($di, $app) {
			return require $app->getPath('config').'routes.php';
		});
		
		$di->setShared('dispatcherEvents', function () {
			$object = new EventsManager();
			$object->attach('dispatch', new Dispatcher\ExceptionHandler('index', 'serverError'));
			return $object;
		});
		
		$di->setShared('dispatcher', function () use($di) {
			$dispatcher = new MvcDispatcher();
			$dispatcher->setEventsManager($di['dispatcherEvents']);
			return $dispatcher;
		});
		
		$di->setShared('viewEvents', function () use($app) {
			$object = new EventsManager();
			$object->attach('view', $app);
			return $object;
		});
		
		$di->setShared('view', function () use($di) {
			$view = new View();
			$view->setEventsManager($di['viewEvents']);
			return $view;
		});
		
		$di->setShared('responder', function () {
			return new Responder();
		});
		
		require $this->_paths['config'].'services.php';	
	}
	
	/**
	 * Register modules
	 */
	protected function _registerModules() {
		$app = $this;
		$paths = $this->_paths;
		require $paths['config'].'modules.php';
	}
	
	/**
	 * --------------------------------------------------------
	 * Application events
	 * --------------------------------------------------------
	 */
	
	/**
	 * application:boot
	 */
	public function boot(Event $event) {
		$this->_registerAutoloaders();
		$this->_registerServices();
		$this->_registerModules();
	}
	
	/**
	 * application:beforeStartModule
	 */
	public function beforeStartModule(Event $event, PhalconApp $application, $moduleName) {
		$this->_moduleName = $moduleName;
	}
	
	/**
	 * application:afterStartModule
	 */
	public function afterStartModule(Event $event) {
		
		$module = $this->getModule();
		
		$module->setApp($this);
		
		$this->getDI()->getDispatcher()->setDefaultNamespace($module->getControllerNamespace());
		
		$module->onLoad();
	}
	
	/**
	 * application:beforeHandleRequest
	 */
	#public function beforeHandleRequest(Event $event) {}

	/**
	 * application:afterHandleRequest
	 */
	public function afterHandleRequest(Event $event) {
		
		$di = $this->getDI();
		$view = $di['view'];
		
		if ($di->getResponder()->isView()) {
			$this->getModule()->configureView($view);
			$di['eventsManager']->attach('view', $this);
		} else {
			$view->disable();
		}
	}
	
	/**
	 * --------------------------------------------------------
	 * View events
	 * --------------------------------------------------------
	 */
	
	/**
	 * view:beforeRender
	 */
	public function beforeRender(Event $event, ViewInterface $view) {
		$this->getModule()->registerAssets($this->getDI()->getAssets());
	}
	
	/**
	 * view:beforeRenderView
	 */
	#public function beforeRenderView(Event $event, ViewInterface $view) {}
	
	/**
	 * view:afterRenderView
	 */
	#public function afterRenderView(Event $event, ViewInterface $view) {}
	
	/**
	 * view:afterRender
	 */
	#public function afterRender(Event $event, ViewInterface $view) {}
	
	/**
	 * view:notFoundView
	 */
	#public function notFoundView(Event $event, ViewInterface $view) {}
	
}
