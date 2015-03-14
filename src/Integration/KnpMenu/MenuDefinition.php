<?php

namespace Phalconry\Integration\KnpMenu;

use Knp\Menu\MenuItem;

abstract class MenuDefinition
{
	const CACHE_ENABLE = true;
	const CACHE_TTL = 300;
	const CACHE_KEY = '';
	
	/**
	 * KnpMenu adapter
	 * @var \Phalconry\Integration\KnpMenu\Adapter
	 */
	protected $adapter;
	
	/**
	 * Menu object
	 * @var \Knp\Menu\MenuItem
	 */
	protected $menu;
	
	/**
	 * Must call $this->set($menu);
	 */
	abstract public function rebuild();
	
	public function __construct(Adapter $knpMenuAdapter) {
		$this->adapter = $knpMenuAdapter;
	}
	
	public function getAdapter() {
		return $this->adapter;
	}
	
	public function get($reset = false) {
		if (! isset($this->menu)) {
			$this->build();
		}
		return $this->menu;
	}
	
	public function set(MenuItem $menu) {
		$this->menu = $menu;
	}
	
	public function render() {
		return $this->adapter->render($this->get());
	}
	
	public function build() {
		if (! $this->buildFromCache()) {
			$this->rebuild();
			$this->saveToCache();
		}
	}
	
	protected function buildFromCache() {
		if ($this::CACHE_ENABLE && function_exists('apc_fetch')) {
			$object = apc_fetch($this::CACHE_KEY);
			if (is_object($object)) {
				$this->set($object);
				return true;
			}
		}
		return false;
	}
	
	protected function saveToCache() {
		if ($this::CACHE_ENABLE && function_exists('apc_store')) {
			apc_store($this::CACHE_KEY, $this->menu, $this::CACHE_TTL);
		}
	}
	
}
