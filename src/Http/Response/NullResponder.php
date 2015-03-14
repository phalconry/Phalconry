<?php

namespace Phalconry\Http\Response;

use Phalcon\Http\ResponseInterface;

class NullResponder extends AbstractResponder
{
	
	final protected function intervene(ResponseInterface $response) {
		// do nothing
	}
	
}
