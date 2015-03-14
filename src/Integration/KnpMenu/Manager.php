<?php

namespace Phalconry\Integration\KnpMenu;

class Manager extends Adapter
{
	
	protected $definition_classes = array();
	protected $definitions = array();
	
	public function setDefinition($menuName, $definition) {
		if ($definition instanceof MenuDefinition) {
			$this->definitions[$menuName] = $definition;
		} else {
			$this->definition_classes[$menuName] = $definition;
		}
	}
	
	public function hasDefinition($menuName) {
		return isset($this->definitions[$menuName]) || isset($this->definition_classes[$menuName]);
	}
	
	public function getDefinition($menuName) {
		if (! isset($this->definitions[$menuName])) {
			if (! isset($this->definition_classes[$menuName])) {
				return null;
			}
			$class = $this->definition_classes[$menuName];
			$this->definitions[$menuName] = new $class($this);
		}
		return $this->definitions[$menuName];
	}
	
	public function getMenu($name) {
		if (! isset($this->menus[$name])) {
			if ($this->hasDefinition($name)) {
				$this->menus[$name] = $this->getDefinition($name)->get();
			} else {
				$this->menus[$name] = $this->getFactory()->createItem($name);
			}
		}
		return $this->menus[$name];
	}
	
}
