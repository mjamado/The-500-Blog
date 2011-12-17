<?php class ModelMultiFactory implements ArrayAccess, Iterator, Countable {
	private $_ids;
	private $_className;
	private $_count;
	private $_position;
	private $_paginator;
	public function __construct($class, $ids, $paginator = null) {
		$this->_ids = $ids;
		$this->_className = $class;
		$this->_paginator = $paginator;
		$this->_count = count($ids);
		$this->_position = 0;
	}
	public function __get($var) {
		if($var == 'paginator') return $this->_paginator;
	}
	public function offsetExists($offset) {
		return isset($this->_ids[$offset]);
	}
	public function offsetGet($offset) {
		$class = $this->_className;
		return isset($this->_ids[$offset]) ? new $class($this->_ids[$offset]) : null;
	}
	public function offsetSet($offset, $value) {
		if(is_null($offset)) {
			$this->_ids[] = $value->id;
			++$this->_count;
		} else $this->_ids[$offset] = $value->id;
	}
	public function offsetUnset($offset) {
		if(isset($this->_ids[$offset])) {
			unset($this->_ids[$offset]);
			--$this->_count;
		}
	}
	public function current() {
		$class = $this->_className;
		return new $class($this->_ids[$this->_position]);
	}
	public function valid() {
		return isset($this->_ids[$this->_position]);
	}
	public function key() {
		return $this->_position;
	}
	public function rewind() {
		$this->_position = 0;
	}
	public function next(){
		++$this->_position;
	}
	public function count() {
		return $this->_count;
	}
} ?>