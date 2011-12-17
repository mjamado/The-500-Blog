<?php class Formen {
	protected $_idField;
	protected $_table;
	protected $_relationMap;
	protected $_modelRelations;
	protected $_id;
	protected $_toDelete;
	protected $_isLoaded;
	protected $_modifiedFields;
	protected $_db;
	public function __construct($id = null) {
		if(isset($id)) $this->_id = $id;
		$this->_toDelete = false;
		$this->_isLoaded = false;
		$this->_modifiedFields = array();
		$this->_db = PDOSingleton::GetObj();
	}
	public function __destruct() {
		if(isset($this->_id) && $this->_toDelete) {
			$sql = "DELETE FROM `" . $this->_table . "` WHERE `" . $this->_idField . "` = :eid";
			$stmt = $this->_db->prepare($sql);
			$stmt->bindValue(":eid", $this->_id);
			$stmt->execute();
		}
	}
	private function Load() {
		if(isset($this->_id)) {
			$sql = "SELECT ";
			foreach($this->_relationMap as $key => $val) $sql .= "`" . $key . "`,";
			$sql = rtrim($sql, ",") . " FROM `" . $this->_table . "` WHERE `" . $this->_idField . "` = :eid";
			$stmt = $this->_db->prepare($sql);
			$stmt->bindParam(":eid", $this->_id, PDO::PARAM_INT);
			$stmt->execute();
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			foreach($row as $key => $val) if(isset($this->_relationMap[$key]) && !isset($this->_modifiedFields[$key])) $this->_relationMap[$key]['val'] = $val;
			$this->_isLoaded = true;
		}
	}
	private function GetRelationVar($var) {
		foreach($this->_relationMap as $key=>$val) if($val['var'] == $var) return $key;
		return false;
	}
	private function SetVar($var, $val, $unsafeContext = false) {
		if((($keyVar = $this->GetRelationVar($var)) !== false) && (!$unsafeContext || !isset($this->_relationMap[$keyVar]['unsafe']) || ($this->_relationMap[$keyVar]['unsafe'] === false))) {
			$this->_relationMap[$keyVar]['val'] = $val;
			$this->_modifiedFields[$keyVar] = true;
		} elseif(property_exists($this, $var)) $this->$var = $val;
	}
	public function GetRelated($relationName, $conditions = null, $order = null, $paginate = null) {
		if(isset($this->_modelRelations[$relationName]) && isset($this->_id)) {
			if(!isset($this->_modelRelations[$relationName]['fetched'])) {
				$newClass = $this->_modelRelations[$relationName]['class'];
				if($this->_modelRelations[$relationName]['type'] == '1:N') {
					$newObj = new $newClass();
					if(is_null($conditions)) $conditions = array();
					$conditions[] = array($this->_modelRelations[$relationName]['relationField'], '=', $this->id);
					$this->_modelRelations[$relationName]['fetched'] = $newObj->getAll($conditions, $order, $paginate);
				} elseif($this->_modelRelations[$relationName]['type'] == 'N:M') {
					$myField = $this->_modelRelations[$relationName]['relationField'];
					$newObj = new $newClass();
					$sql = "SELECT `" . $this->_modelRelations[$relationName]['auxRelationField'] . "` FROM `" . $this->_modelRelations[$relationName]['auxTable'] . "` WHERE `" . $this->_modelRelations[$relationName]['auxMyField'] . "` = :myid";
					$stmt = $this->_db->prepare($sql);
					$myValue = $this->$myField;
					$stmt->bindParam(':myid', $myValue);
					$stmt->execute();
					$ids = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
					$this->_modelRelations[$relationName]['fetched'] = $newObj->getAll($conditions, $order, $paginate, $ids);
				} else {
					$myField = $this->_modelRelations[$relationName]['relationField'];
					$this->_modelRelations[$relationName]['fetched'] = new $newClass($this->$myField);
				}
			}
			return $this->_modelRelations[$relationName]['fetched'];
		} else return false;
	}
	public function Save() {
		if(method_exists($this, 'onBeforeSave')) $this->onBeforeSave();
		$upd = isset($this->_id);
		$sql = (($upd) ? "UPDATE `" : "INSERT INTO `") . $this->_table . "` SET ";
		foreach($this->_relationMap as $key => $val) if(isset($this->_modifiedFields[$key])) $sql .= "`" . $key . "` = :" . $val['var'] . ", ";
		$sql = rtrim($sql, ", ") . (($upd) ? (" WHERE `" . $this->_idField . "` = :eid") : "");
		$stmt = $this->_db->prepare($sql);
		if($upd) $stmt->bindValue(":eid", $this->_id, PDO::PARAM_INT);
		foreach($this->_relationMap as $key => $val) if(isset($this->_modifiedFields[$key])) $stmt->bindValue(":" . $val['var'], $val['val'], isset($val['type']) ? $val['type'] : PDO::PARAM_STR);
		$stmt->execute();
		if(!$upd) $this->_id = $this->_db->lastInsertId();
	}
	public function ToDelete() {
		$this->_toDelete = true;
	}
	public function __get($var) {
		if($var == 'id') return $this->_id;
		elseif(($keyVar = $this->GetRelationVar($var)) !== false) {
			if(($this->_isLoaded == false) && !isset($this->_modifiedFields[$keyVar])) $this->Load();
			return $this->_relationMap[$keyVar]['val'];
		} elseif(!empty($this->_modelRelations) && is_array($this->_modelRelations) && array_key_exists($var, $this->_modelRelations)) return $this->GetRelated($var);
		else return false;
	}
	public function __isset($var) {
		return ($this->__get($var) !== false);
	}
	public function __set($var, $val) {
		if(($var == 'data') && !empty($val) && is_array($val)) {
			foreach($val as $key => $data) if($data != "") $this->SetVar($key, $data, true);
			return true;
		} else return $this->SetVar($var, $val);

	}
	public function GetAll($conditions = null, $order = null, $paginate = null, $ids = null) {
		$paginator = null;
		$whereClause = "";
		$orderClause = "";
		$hasWhere = false;
		if(isset($paginate)) $counter = "SELECT COUNT(*) FROM `" . $this->_table . "`";
		$sql = "SELECT `" . $this->_idField . "` FROM `" . $this->_table . "`";
		$conditionValues = array();
		if(!empty($conditions) && is_array($conditions)) {
			$validComparisons = array("=", ">=", "<=", "<>", "!=", ">", "<");
			foreach($conditions as $condition) {
				$keyVar = $this->GetRelationVar($condition[0]);
				if(($keyVar !== false) && in_array($condition[1], $validComparisons)) {
					$whereClause .= (($hasWhere) ? " AND " : " WHERE ") . "`" . $keyVar . "` " . $condition[1] . " :" . $this->_relationMap[$keyVar]['var'];
					$hasWhere = true;
					$conditionValues[] = array(":" . $this->_relationMap[$keyVar]['var'], $condition[2], isset($this->_relationMap[$keyVar]['type']) ? $this->_relationMap[$keyVar]['type'] : PDO::PARAM_STR);
				}
			}
		}
		if(!empty($ids) && is_array($ids)) $whereClause .= (($hasWhere) ? " AND " : " WHERE ") . "`" . $this->_idField . "` IN (" . implode(",", $ids) . ")";
		if(!empty($order) && is_array($order)) {
			$keyVar = $this->GetRelationVar($order[0]);
			if($keyVar !== false) $orderClause = " ORDER BY `" . $keyVar . "` " . (($order[1] == "ASC") ? "ASC" : "DESC");
			elseif($order[1] == "RAND") $orderClause = " ORDER BY RAND()";
		}
		if(isset($paginate)) {
			$stmt = $this->_db->prepare($counter . $whereClause);
			if(!empty($conditionValues) && is_array($conditionValues)) foreach($conditionValues as $val) $stmt->bindParam($val[0], $val[1], $val[2]);
			$stmt->execute();
			$counterNum = $stmt->fetchColumn();
			$paginator = new Paginator($counterNum, $paginate['page'], $paginate['numItemsPage']);
		}
		$stmt = $this->_db->prepare($sql . $whereClause . $orderClause . (isset($paginate) ? $paginator->limitClause : ""));
		if(!empty($conditionValues) && is_array($conditionValues)) foreach($conditionValues as $val) $stmt->bindParam($val[0], $val[1], $val[2]);
		$stmt->execute();
		$ids = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
		return new ModelMultiFactory(get_class($this), $ids, $paginator);
	}
	public function SetNMRelated($relationName, $relations) {
		if(isset($this->_modelRelations[$relationName]) && ($this->_modelRelations[$relationName]['type'] == 'N:M') && isset($this->_id) && !empty($relations) && is_array($relations)) {
			foreach($relations as $id => $relation) {
				$sql = false;
				if(!isset($relation['new']) && ($relation['old'] == 1)) $sql = "DELETE FROM `" . $this->_modelRelations[$relationName]['auxTable'] . "` WHERE `" . $this->_modelRelations[$relationName]['auxMyField'] . "` = :eid AND `" . $this->_modelRelations[$relationName]['auxRelationField'] . "` = :rid";
				elseif(isset($relation['new']) && ($relation['old'] == 0)) $sql = "INSERT INTO `" . $this->_modelRelations[$relationName]['auxTable'] . "` SET `" . $this->_modelRelations[$relationName]['auxMyField'] . "` = :eid, `" . $this->_modelRelations[$relationName]['auxRelationField'] . "` = :rid";
				if($sql != false) {
					$stmt = $this->_db->prepare($sql);
					$stmt->bindParam(':eid', $this->id, PDO::PARAM_INT);
					$stmt->bindParam(':rid', $id, PDO::PARAM_INT);
					$stmt->execute();
				}
			}
		}
	}
} ?>