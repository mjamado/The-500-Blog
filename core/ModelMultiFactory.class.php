<?php

/**
 * This class holds the ids of a number of records of a given Model, ready to
 * be passed to new instances of that Model.
 *
 * It implements ArrayAccess, for access like a standard array
 * ($multiModelFactoryInstance[0]), Iterator, for access with a foreach
 * construct (foreach($multiModelFactoryInstace as $modelInstace) and
 * Countable, just so we can count how many objects it has inside, array-like
 * (count($multiModelFactoryInstace)).
 *
 * This class is not to be instatiated on it's own: it's returned from Models'
 * GetAll() and GetRelated() methods. Also, it can be passed to Paginator in
 * the constructor.
 *
 * Not many method comments on this class. It's just the implementation of the
 * three interfaces. The only significative diference it's on the methods that
 * return the values: instead of returning the value of the current position
 * (or asked position), they return an instance of the model passed on the
 * constructor, initialized with the id of the position.
 *
 * @author Marco Amado <mjamado@dreamsincode.com>
 * @since 2011-11-19 [last review: 2011-11-19]
 * @license Creative Commons 2.5 Portugal BY-NC-SA
 * @link http://www.dreamsincode.com/
 */
class ModelMultiFactory implements ArrayAccess, Iterator, Countable
{
	private $_ids;
	private $_className;
	private $_count;
	private $_position;
	/**
	 * @var Paginator
	 */
	private $_paginator;

	public function __construct($class, $ids, $paginator = null)
	{
		$this->_ids = $ids;
		$this->_className = $class;
		$this->_paginator = $paginator;

		$this->_count = count($ids);
		$this->_position = 0;
	}

	public function __get($var)
	{
		if($var == 'paginator')
			return $this->_paginator;
	}

	/****************************************
	 *		ArrayAccess Implementation		*
	 ****************************************/

	public function offsetExists($offset)
	{
		return isset($this->_ids[$offset]);
	}

	public function offsetGet($offset)
	{
		$class = $this->_className;
		return isset($this->_ids[$offset]) ? new $class($this->_ids[$offset]) : null;
	}

	public function offsetSet($offset, $value)
	{
		if(is_null($offset))
		{
			$this->_ids[] = $value->id;
			++$this->_count;
		}
		else
			$this->_ids[$offset] = $value->id;
	}

	public function offsetUnset($offset)
	{
		if(isset($this->_ids[$offset]))
		{
			unset($this->_ids[$offset]);
			--$this->_count;
		}
	}

	/****************************************
	 *		 Iterator Implementation		*
	 ****************************************/

	public function current()
	{
		$class = $this->_className;
		return new $class($this->_ids[$this->_position]);
	}

	public function valid()
	{
		return isset($this->_ids[$this->_position]);
	}

	public function key()
	{
		return $this->_position;
	}

	public function rewind()
	{
		$this->_position = 0;
	}

	public function next()
	{
		++$this->_position;
	}

	/****************************************
	 *		 Countable Implementation		*
	 ****************************************/

	public function count()
	{
		return $this->_count;
	}
}
?>