<?php

namespace Phalconry\Http\Response;

class JsonContent implements \JsonSerializable
{
	
	/**
	 * JSON content mime/type.
	 * @var string
	 */
	const CONTENT_TYPE = 'application/json';
	
	/**
	 * Key used for response data
	 * @var string
	 */
	const DATA_KEY = 'data';
	
	/**
	 * Response content data
	 * @var array
	 */
	protected $data;
	
	/**
	 * Response content prepended to data
	 * @var array
	 */
	protected $prepend;
	
	/**
	 * Response content appended to data
	 * @var array
	 */
	protected $append;
	
	/**
	 * JSON options
	 * @var array
	 */
	protected $options;
	
	public function __construct($data = null) {
		if (isset($data)) {
			$this->setData($data);
		}
		$this->prepend = array();
		$this->append = array();
		$this->options = 0;
	}
	
	public function setData($data) {
		if (! is_array($data)) {
			$data = $this->valueToArray($data);
		}
		$this->data = $data;
		return $this;
	}
	
	public function setDataFromEncoded($jsonString, $jsonOptions = 0) {
		return $this->setData(json_decode($jsonString, true, 512, $jsonOptions));
	}
	
	public function getData() {
		return $this->data;
	}
	
	public function prepend($key, $value) {
		$this->prepend[$key] = $value;
		return $this;
	}
	
	public function append($key, $value) {
		$this->append[$key] = $value;
		return $this;
	}
	
	public function setOptions($options) {
		$this->options = $options;
		return $this;
	}
	
	public function getOptions() {
		return $this->options;
	}
	
	public function addOption($option) {
		$this->options |= $option;
		return $this;
	}
	
	public function clearOptions() {
		$this->options = 0;
		return $this;
	}
	
	public function encode() {
		return json_encode($this->collect(), $this->getOptions());
	}
	
	public function collect() {
		$content = array();
		foreach($this->prepend as $key => $value) {
			$content[$key] = is_callable($value) ? $value() : $value;
		}
		$content[$this::DATA_KEY] = $this->getData();
		foreach($this->append as $key => $value) {
			$content[$key] = is_callable($value) ? $value() : $value;
		}
		return $content;
	}
	
	public function jsonSerialize() {
		return $this->collect();
	}
	
	protected function valueToArray($value) {
		if (is_object($value)) {
			if (method_exists($value, 'jsonSerialize')) {
				return (array)$value->jsonSerialize();
			} else if (method_exists($value, 'toArray')) {
				return $value->toArray();
			} else if ($value instanceof \Traversable) {
				return iterator_to_array($value);
			} else {
				return get_object_vars($value);
			}
		}
		return (array)$value;
	}
}
