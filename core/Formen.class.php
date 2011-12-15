<?php

/**
 * Formen stands for Fast Object Relational Mapping ENgine. It's the quick and
 * dirty work of only an hour or two, and lacks many of the advanced features
 * of more mature ORM engines. Therefore, the "fast" is not to be taken
 * performance wise, but to the time it took to put together.
 *
 * It does the job it was intented to do pretty well, though.
 *
 * @author Marco Amado <mjamado@dreamsincode.com>
 * @since 2011-11-18 [last review: 2011-12-10]
 * @license Creative Commons 2.5 Portugal BY-NC-SA
 * @link http://www.dreamsincode.com/
 */
class Formen
{
	protected $_idField;
	protected $_table;
	/**
	 * relationMap['db_field'] = array(
	 *		'var' => string,	// camel cased representation of the field,
	 *							// which will be the name of the propery of the
	 *							// child class
	 *		'val' => mixed,		// setted internally, do not declare it
	 *		'type' => int,		// PDO::PARAM_BOOL|PDO::PARAM_INT|PDO::PARAM_STR,
	 *		'unsafe' => bool	// whether this field can be bulk-setted, via
	 *							// data property
	 * )
	 * @var array
	 */
	protected $_relationMap;
	/**
	 * modelRelations['relation'] = array(
	 *		'type' => string,			// N:1, 1:N, 1:1, N:M
	 *		'relationField' => string,	// property in this class (in 1:N) or
	 *									// in the foreign class (in all other
	 *									// types) which binds the relation
	 *		'class' => string,			// class name of the foreign model
	 *		'auxTable' => string,		// auxiliary table name for N:M types
	 *		'auxMyField' => string,		// name of the field representing this
	 *									// class id on the auxiliary table
	 *		'auxRelationField' => string// name of the field representing the
	 *									// foreign class id on the auxiliary
	 *									// table
	 * )
	 *
	 * @var array
	 */
	protected $_modelRelations;

	protected $_id;

	protected $_toDelete;
	protected $_isLoaded;
	protected $_modifiedFields;
	/**
	 * @var PDO
	 */
	protected $_db;

	/**
	 * Constructor; initializes some vars
	 *
	 * @param int $id
	 */
	public function __construct($id = null)
	{
		if(isset($id))
			$this->_id = $id;

		$this->_toDelete = false;
		$this->_isLoaded = false;
		$this->_modifiedFields = array();

		$this->_db = PDOSingleton::GetObj();
	}

	/**
	 * Destructor; if the active record is marked for deletion, deletes it
	 */
	public function __destruct()
	{
		if(isset($this->_id) && $this->_toDelete)
		{
			$sql = "DELETE FROM `" . $this->_table . "` WHERE `" . $this->_idField . "` = :eid";
			$stmt = $this->_db->prepare($sql);
			$stmt->bindValue(":eid", $this->_id);
			$stmt->execute();
		}
	}

	/**
	 * Lazy loader; fills the active record with values from the database
	 */
	private function Load()
	{
		if(isset($this->_id))
		{
			$sql = "SELECT ";
			foreach($this->_relationMap as $key => $val)
				$sql .= "`" . $key . "`,";
			$sql = rtrim($sql, ",") . " FROM `" . $this->_table . "` WHERE `" . $this->_idField . "` = :eid";

			$stmt = $this->_db->prepare($sql);
			$stmt->bindParam(":eid", $this->_id, PDO::PARAM_INT);
			$stmt->execute();

			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			foreach($row as $key => $val)
				if(isset($this->_relationMap[$key]) && !isset($this->_modifiedFields[$key]))
					$this->_relationMap[$key]['val'] = $val;

			$this->_isLoaded = true;
		}
	}

	/**
	 * Helper method to find the key of the relation map pertaining an asked
	 * variable. Returns a string in case of success, boolean false otherwise.
	 *
	 * @param string $var
	 *
	 * @return mixed
	 */
	private function GetRelationVar($var)
	{
		foreach($this->_relationMap as $key=>$val)
			if($val['var'] == $var)
				return $key;

		return false;
	}

	/**
	 * Sets the value for a property
	 *
	 * @param type $var
	 * @param type $val
	 * @param type $unsafeContext
	 */
	private function SetVar($var, $val, $unsafeContext = false)
	{
		if((($keyVar = $this->GetRelationVar($var)) !== false) && (!$unsafeContext || !isset($this->_relationMap[$keyVar]['unsafe']) || ($this->_relationMap[$keyVar]['unsafe'] === false)))
		{
			$this->_relationMap[$keyVar]['val'] = $val;
			$this->_modifiedFields[$keyVar] = true;
		}
		elseif(property_exists($this, $var))
			$this->$var = $val;
	}

	/**
	 * If the relation is of 1:N or N:M type, returns an instace of
	 * ModelMultiFactory class, which implements ArrayAccess, Countable and
	 * Iterator. If the relation is of N:1 or 1:1 type, returns an instance of
	 * the proper class.
	 *
	 * $conditions, $order and $paginate are passed along to GetAll, if it's
	 * used (on 1:N and N:M relations). Documentation of these arrays are down there in
	 * the GetAll method.
	 *
	 * @param string $relationName
	 *
	 * @return mixed
	 */
	public function GetRelated($relationName, $conditions = null, $order = null, $paginate = null)
	{
		if(isset($this->_modelRelations[$relationName]) && isset($this->_id))
		{
			if(!isset($this->_modelRelations[$relationName]['fetched']))
			{
				$newClass = $this->_modelRelations[$relationName]['class'];

				if($this->_modelRelations[$relationName]['type'] == '1:N')
				{
					$newObj = new $newClass();

					if(is_null($conditions))
						$conditions = array();
					$conditions[] = array($this->_modelRelations[$relationName]['relationField'], '=', $this->id);

					$this->_modelRelations[$relationName]['fetched'] = $newObj->getAll($conditions, $order, $paginate);
				}
				elseif($this->_modelRelations[$relationName]['type'] == 'N:M')
				{
					$myField = $this->_modelRelations[$relationName]['relationField'];
					$newObj = new $newClass();

					$sql = "SELECT `" . $this->_modelRelations[$relationName]['auxRelationField'] . "`
							FROM `" . $this->_modelRelations[$relationName]['auxTable'] . "`
							WHERE `" . $this->_modelRelations[$relationName]['auxMyField'] . "` = :myid";
					$stmt = $this->_db->prepare($sql);
					// this is copied to another value, because bindParam wants it by reference
					// and since it's overloaded, wouldn't work directly
					$myValue = $this->$myField;
					$stmt->bindParam(':myid', $myValue);
					$stmt->execute();

					$ids = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

					// call and return iterator class
					$this->_modelRelations[$relationName]['fetched'] = $newObj->getAll($conditions, $order, $paginate, $ids);
				}
				else // N:1 or 1:1
				{
					$myField = $this->_modelRelations[$relationName]['relationField'];
					$this->_modelRelations[$relationName]['fetched'] = new $newClass($this->$myField);
				}
			}

			return $this->_modelRelations[$relationName]['fetched'];
		}
		else
			return false;
	}

	/**
	 * Saves the current active record, inserting it in case of a new record,
	 * updating otherwise. The record maintains its availability.
	 */
	public function Save()
	{
		/* This is a hook - if the class that extends this one has this method, it's executed
		 * before saving the model's data. For an use case, check out the User model. Other use
		 * cases include a possible last modified attribute, an automatic backup generator or
		 * a version increment.
		 */
		if(method_exists($this, 'onBeforeSave'))
			$this->onBeforeSave();

		$upd = isset($this->_id);

		$sql = (($upd) ? "UPDATE `" : "INSERT INTO `") . $this->_table . "` SET ";
		foreach($this->_relationMap as $key => $val)
			if(isset($this->_modifiedFields[$key]))
				$sql .= "`" . $key . "` = :" . $val['var'] . ", ";
		$sql = rtrim($sql, ", ") . (($upd) ? (" WHERE `" . $this->_idField . "` = :eid") : "");

		$stmt = $this->_db->prepare($sql);
		if($upd)
			$stmt->bindValue(":eid", $this->_id, PDO::PARAM_INT);

		foreach($this->_relationMap as $key => $val)
			if(isset($this->_modifiedFields[$key]))
				$stmt->bindValue(":" . $val['var'], $val['val'], isset($val['type']) ? $val['type'] : PDO::PARAM_STR);

		$stmt->execute();

		if(!$upd)
			$this->_id = $this->_db->lastInsertId();
	}

	/**
	 * Marks the active record for deletion. It will be deleted on object
	 * destruction
	 */
	public function ToDelete()
	{
		$this->_toDelete = true;
	}

	/**
	 * Getter; returns the value of the asked variable or relation, if it
	 * exists, false otherwise.
	 *
	 * @param string $var
	 *
	 * @return mixed
	 */
	public function __get($var)
	{
		if($var == 'id')
			return $this->_id;
		elseif(($keyVar = $this->GetRelationVar($var)) !== false)
		{
			if(($this->_isLoaded == false) && !isset($this->_modifiedFields[$keyVar]))
				$this->Load();

			return $this->_relationMap[$keyVar]['val'];
		}
		elseif(!empty($this->_modelRelations) && is_array($this->_modelRelations) && array_key_exists($var, $this->_modelRelations))
			return $this->GetRelated($var);
		else
			return false;
	}

	/**
	 * Provides isset() and empty() functionality to model properties
	 *
	 * @param string $var
	 *
	 * @return boolean
	 */
	public function __isset($var)
	{
		return ($this->__get($var) !== false);
	}

	/**
	 * Setter; sets the value into the variable, if it exists and isn't marked
	 * as read only. Returns false in case the variable doesn't exist.
	 *
	 * It also accepts a special variable, data, in which several model
	 * properties are present, iterating and setting them. Unsafe attribute of
	 * the properties will be respected in this iteration (ie, no unsafe
	 * properties will be set).
	 *
	 * @param string $var
	 * @param mixed $val
	 *
	 * @return mixed
	 */
	public function __set($var, $val)
	{
		if(($var == 'data') && !empty($val) && is_array($val))
		{
			foreach($val as $key => $data)
				if($data != "")
					$this->SetVar($key, $data, true);

			return true;
		}
		else
			return $this->SetVar($var, $val);

	}

	/**
	 * Returns an instace of ModelMultiFactory class, which implements
	 * ArrayAccess, Countable and Iterator. This class will create the
	 * necessary model objects on the fly, based on the resulting IDs of the
	 * parameters of $conditions.
	 *
	 * The $conditions and $order arrays are of the following formats:
	 *
	 * $conditions = array(
	 *		array(string $fieldName, string $comparison, mixed $value)
	 * );
	 *
	 * $comparison can be "=", ">=", "<=", "<>", "!=", ">" or "<"
	 * $value can be any scalar
	 *
	 * $order = array(string $field, string $direction);
	 *
	 * $direction can be "ASC", "DESC" or "RAND". If "RAND" is intended, $field
	 * must be an empty string-
	 *
	 * $ids is only used in GetRelated (for N:M relations) and caution is
	 * advised - these parameters are NOT passed to PDO by means of bindParam
	 * (they're just concatenated) and therefore they're not secure. Never,
	 * ever, use this parameter with user provided values. Refrain from using
	 * it, at all.
	 *
	 * @param array $conditions
	 * @param array $order
	 * @param array $paginator
	 * @param array $ids
	 *
	 * @return ModelMultiFactory
	 */
	public function GetAll($conditions = null, $order = null, $paginate = null, $ids = null)
	{
		$paginator = null;

		$whereClause = "";
		$orderClause = "";
		$hasWhere = false;

		if(isset($paginate))
			$counter = "SELECT COUNT(*) FROM `" . $this->_table . "`";

		$sql = "SELECT `" . $this->_idField . "` FROM `" . $this->_table . "`";
		$conditionValues = array();
		if(!empty($conditions) && is_array($conditions))
		{
			$validComparisons = array("=", ">=", "<=", "<>", "!=", ">", "<");
			foreach($conditions as $condition)
			{
				$keyVar = $this->GetRelationVar($condition[0]);
				if(($keyVar !== false) && in_array($condition[1], $validComparisons))
				{
					$whereClause .= (($hasWhere) ? " AND " : " WHERE ") . "`" . $keyVar . "` " . $condition[1] . " :" . $this->_relationMap[$keyVar]['var'];
					$hasWhere = true;
					$conditionValues[] = array(":" . $this->_relationMap[$keyVar]['var'], $condition[2], isset($this->_relationMap[$keyVar]['type']) ? $this->_relationMap[$keyVar]['type'] : PDO::PARAM_STR);
				}
			}
		}

		if(!empty($ids) && is_array($ids))
			$whereClause .= (($hasWhere) ? " AND " : " WHERE ") . "`" . $this->_idField . "` IN (" . implode(",", $ids) . ")";

		if(!empty($order) && is_array($order))
		{
			$keyVar = $this->GetRelationVar($order[0]);
			if($keyVar !== false)
				$orderClause = " ORDER BY `" . $keyVar . "` " . (($order[1] == "ASC") ? "ASC" : "DESC");
			elseif($order[1] == "RAND")
				$orderClause = " ORDER BY RAND()";
		}

		if(isset($paginate))
		{
			$stmt = $this->_db->prepare($counter . $whereClause);
			if(!empty($conditionValues) && is_array($conditionValues))
				foreach($conditionValues as $val)
					$stmt->bindParam($val[0], $val[1], $val[2]);
			$stmt->execute();
			$counterNum = $stmt->fetchColumn();
			$paginator = new Paginator($counterNum, $paginate['page'], $paginate['numItemsPage']);
		}

		$stmt = $this->_db->prepare($sql . $whereClause . $orderClause . (isset($paginate) ? $paginator->limitClause : ""));
		if(!empty($conditionValues) && is_array($conditionValues))
			foreach($conditionValues as $val)
				$stmt->bindParam($val[0], $val[1], $val[2]);
		$stmt->execute();

		$ids = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

		// call and return iterator class
		return new ModelMultiFactory(get_class($this), $ids, $paginator);
	}

	/**
	 * Sets the N:M relationship in the auxiliary table. If it was set and now
	 * it's not, removes the relation; if it wasn't set and it is now, inserts
	 * a new line representing said relation; if the relation maintains its
	 * previous status, nothing will be done (obviously).
	 *
	 * The $relations array should be in the following format:
	 * array(
	 *		id_of_the_related_model => array(
	 *			'new' => 1|0, // set or not set
	 *			'old' => 1|0  // set or not set
	 *		),
	 *		id_of_the_related_model => (...),
	 *		(...)
	 * )
	 *
	 * @param string $relationName
	 * @param array $relations
	 */
	public function SetNMRelated($relationName, $relations)
	{
		if(isset($this->_modelRelations[$relationName]) && ($this->_modelRelations[$relationName]['type'] == 'N:M') && isset($this->_id) && !empty($relations) && is_array($relations))
		{
			foreach($relations as $id => $relation)
			{
				$sql = false;
				if(!isset($relation['new']) && ($relation['old'] == 1))
					$sql = "DELETE FROM `" . $this->_modelRelations[$relationName]['auxTable'] . "`
							WHERE `" . $this->_modelRelations[$relationName]['auxMyField'] . "` = :eid
								AND `" . $this->_modelRelations[$relationName]['auxRelationField'] . "` = :rid";
				elseif(isset($relation['new']) && ($relation['old'] == 0))
					$sql = "INSERT INTO `" . $this->_modelRelations[$relationName]['auxTable'] . "`
							SET `" . $this->_modelRelations[$relationName]['auxMyField'] . "` = :eid,
								`" . $this->_modelRelations[$relationName]['auxRelationField'] . "` = :rid";

				if($sql != false)
				{
					$stmt = $this->_db->prepare($sql);
					$stmt->bindParam(':eid', $this->id, PDO::PARAM_INT);
					$stmt->bindParam(':rid', $id, PDO::PARAM_INT);
					$stmt->execute();
				}
			}
		}
	}
}
?>