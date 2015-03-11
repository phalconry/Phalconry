<?php

namespace Phalconry;

use Phalcon\Http\ResponseInterface;
use Phalcon\Mvc\User\Component;

class Responder extends Component
{
	const MODE_DISABLE = false;
	const MODE_VIEW = 'view';
	const MODE_JSON = 'json';
	const MODE_XML = 'xml';
	
	protected $_mode = self::MODE_VIEW;
	protected $_dataModes = array(self::MODE_JSON, self::MODE_XML);
	protected $_response;
	
	public function setResponse(ResponseInterface $response) {
		$this->_response = $response;
		return $this;
	}
	
	public function getResponse() {
		return $this->_response;
	}
	
	public function hasResponse() {
		return isset($this->_response);
	}
	
	public function setMode($mode) {
		$this->_mode = $mode;
	}
	
	public function disable() {
		$this->setMode($this::MODE_DISABLE);
	}
	
	public function getMode() {
		return $this->_mode;
	}
	
	public function isMode($mode) {
		if (is_array($mode)) {
			return in_array($this->_mode, $mode, true);
		}
		return $this->_mode === $mode;
	}
	
	public function isDisabled() {
		return $this->_mode === $this::MODE_DISABLE;
	}
	
	public function isView() {
		return $this->_mode === $this::MODE_VIEW;
	}
	
	public function isJson() {
		return $this->_mode === $this::MODE_JSON;
	}
	
	public function isXml() {
		return $this->_mode === $this::MODE_XML;
	}
	
	public function isData() {
		return in_array($this->_mode, $this->_dataModes, true);
	}
	
	public function respond() {
		
		if ($this->hasResponse()) {
			$response = $this->getResponse();
		} else {
			$response = $this->getDI()->getResponse();
		}
		
		if (! $this->isDisabled()) {
			$this->intervene($response);
		}
		
		$response->send();
	}
	
	public function __invoke() {
		$this->respond();
	}
	
	protected function intervene(ResponseInterface $response) {
		
		$content = $response->getContent();
		
		if (empty($content)) {
			$content = $response->getDI()->getDispatcher()->getReturnedValue();
			if ($this->isView()) {
				$response->setContent($content);
			}
		}
		
		if ($this->isData()) {
			if (empty($content)) {
				$content = $response->getDI()->getView()->getParamsToView();
			} else if (is_scalar($content)) {
				$content = array('content' => $content);
			}
		}
		
		if ($this->isJson()) {
			$this->prepareJson($response, $content);
		}
	}
	
	protected function prepareJson(ResponseInterface $response, $content = '') {
		
		$headers = $response->getHeaders();
		
		if (! $headers->get('Status')) {
			$response->setStatusCode(200, 'OK');
		}
		
		$json = new Response\JsonContent($content);
		$json->prepend('status', substr($headers->get('Status'), 4));
		
		if ($this->getDI()->getRequest()->hasQuery('dev')) {
			$json->addOption(JSON_PRETTY_PRINT);
			$json->append('diagnostics', function () {
				return $this->getDI()->getDiagnostics()->getAll();
			});
		}
		
		$response->setContentType($json::CONTENT_TYPE);
		$response->setJsonContent($json, $json->getOptions());
	}
	
}
