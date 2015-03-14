<?php

namespace Phalconry\Http\Response;

use Phalcon\Http\ResponseInterface;

class ViewResponder extends AbstractResponder
{
	
	protected function intervene(ResponseInterface $response) {
		
		$returnValue = $this->getReturnedValue($response);
		
		if (false === $returnValue || $response === $returnValue) {
			return;
		}
		
		$content = $response->getContent();
		
		if (empty($content) && ! empty($returnValue)) {
			$response->setContent($returnValue);
		}
	}
	
}
