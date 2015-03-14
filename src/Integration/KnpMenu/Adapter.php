<?php

namespace Phalconry\Integration\KnpMenu;

use Phalcon\Mvc\User\Component;
use Knp\Menu\Matcher\MatcherInterface;
use Knp\Menu\Matcher\Matcher;
use Knp\Menu\Matcher\Voter\VoterInterface;
use Knp\Menu\Renderer\RendererInterface;
use Knp\Menu\Renderer\ListRenderer;
use Knp\Menu\FactoryInterface;
use Knp\Menu\MenuFactory;
use Knp\Menu\MenuItem;

class Adapter extends Component
{
	
	protected $factory;
	protected $matcher;
	protected $renderer;
	protected $voters = array();
	protected $render_options = array();
	protected $menus = array();
	
	public function setRenderOptions(array $options) {
		$this->render_options = $options;
	}
	
	public function getRenderOptions() {
		return $this->render_options;
	}
	
	public function setRenderOption($name, $value) {
		$this->render_options[$name] = $value;
	}
	
	public function getRenderOption($name) {
		return isset($this->render_options[$name]) ? $this->render_options[$name] : null;
	}
	
	public function setFactory(FactoryInterface $factory) {
		$this->factory = $factory;
	}
	
	public function getFactory() {
		if (! isset($this->factory)) {
			$this->factory = new MenuFactory();
		}
		return $this->factory;
	}
	
	public function setMatcher(MatcherInterface $matcher) {
		$this->matcher = $matcher;
	}
	
	public function getMatcher() {
		if (! isset($this->matcher)) {	
			$this->matcher = new Matcher();
			if ($this->hasVoters()) {
				foreach($this->getVoters() as $voter) {
					$this->matcher->addVoter($voter);
				}
			}
		}
		return $this->matcher;
	}
	
	public function setRenderer(RendererInterface $renderer) {
		$this->renderer = $renderer;
	}
	
	public function getRenderer() {
		if (! isset($this->renderer)) {
			$this->renderer = new ListRenderer($this->getMatcher(), $this->getRenderOptions());
		}
		return $this->renderer;
	}
	
	public function addVoter(VoterInterface $voter) {
		$this->voters[] = $voter;
	}
	
	public function hasVoters() {
		return ! empty($this->voters);
	}
	
	public function getVoters() {
		return $this->voters;
	}
	
	public function setMenu(MenuItem $menu) {
		$this->menus[$menu->getName()] = $menu;
	}
	
	public function getMenu($name) {
		if (! isset($this->menus[$name])) {
			$this->menus[$name] = $this->getFactory()->createItem($name);
		}
		return $this->menus[$name];
	}
	
	public function setMenuClass(MenuItem $menu, $class) {
		if (is_array($class)) {
			$class = implode(' ', $class);
		}
		$menu->setChildrenAttribute('class', $class);
	}
	
	public function setActiveItemClass($class) {
		if (is_array($class)) {
			$class = implode(' ', $class);
		}
		$this->setRenderOption('currentClass', $class);
	}
	
	public function render($menu) {
		if (! $menu instanceof MenuItem) {
			$menu = $this->getMenu($menu);
		}
		return $this->getRenderer()->render($menu);
	}
	
}
