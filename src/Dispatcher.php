<?php

namespace Phalconry;

class Dispatcher extends \Phalcon\Mvc\Dispatcher
{
	
	public function setDefaultNamespace($namespace) {
		parent::setDefaultNamespace($namespace);
		$this->_namespaceName = $namespace;
	}
	
}
