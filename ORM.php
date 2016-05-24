<?php

namespace FSBO;

use FSBO\ORM\Exception;

abstract class ORM {

	/** @var array Keeps references to all objects created this using this Factory.  */
	static $storage = [];

	/** @var array Default options used in find method. */
	static $query = [
		'where' => null,
		'order' => null,
		'limit' => '100',
	];

	/**
	 * Returns object of called class.
	 *
	 * @param null $id Primary id of object we want to call.
	 * @return mixed Returns object of called class.
	 */
	final static function getInstance($id = null) {
		$class = get_called_class();
		if(!empty($id) &&  isset(static::$storage[$class][$id])) {
			return static::$storage[$class][$id];
		} elseif(!empty($id)) {
			$object = new $class($id);
			static::$storage[$class][$id] = $object;
			return $object;
		} else{
			return new $class($id);
		}
	}

	/**
	 * Using object properties or raw where query this returns array.
	 *
	 * @return array
	 * @throws Exception
	 * @throws \FSBO\Exception
	 */
	final static function find($options = []) {

		$options = array_replace_recursive(static::$query, $options);
		$class = get_called_class();
		$table = $class::TABLE;

		$db = PDO::getInstance();

		if(is_array($options['where'])) {
			$summation = [];
			foreach($options['where'] as $property => $condition) {
				$property = array_search($property, $class::selector());
				if(!$property) {
					throw new Exception("Property {$property} is not defined!");
				} elseif(static::toSQL($condition)) {
					$condition = static::toSQL($condition);
					$summation[] = "{$property} {$condition}";
				}

			}
			$options['where'] = implode(" AND ", $summation);
		}

		$where = !empty($options['where'])
			? "WHERE {$options['where']}"
			: null;

		$limit = !empty($options['limit'])
			? "LIMIT {$options['limit']}"
			: null;

		$columns = [];
		foreach($class::selector() as $key => $value) {
			$columns[] = empty($value) ? $key : "{$key} AS {$value}";
		}
		$columns = implode(',', $columns);
		$sql = "SELECT {$columns} FROM {$table} {$where} {$limit}";

		$conn = $db->prepare($sql);

		if(!$conn->execute()) {
			throw new Exception("Find on {$table} failed!");
		}

		return $conn->fetchAll(\PDO::FETCH_CLASS, $class) ?: [];

	}

	/**
	 * ORM constructor.
	 *
	 * @param mixed|null $id Value of primary key
	 * @throws Exception When record failed to load from a table.
	 */
	final public function __construct($id = null) {

		$table = $this::TABLE;
		$primary = $this::PRIMARY_KEY;

		$db = PDO::getInstance();

		$columns = [];
		foreach($this::selector() as $key => $value) {
			$columns[] = empty($value) ? $key : "{$key} AS {$value}";
		}
		$columns = implode(',',$columns);

		$conn = $db->prepare("SELECT {$columns} FROM {$table} WHERE {$primary} = ?");
		$conn->setFetchMode(\PDO::FETCH_INTO, $this);
		if(!$conn->execute([$id])) {
			throw new Exception("Select to in {$table} for primary key {$id} failed!");
		}
		$conn->fetch();

	}

	/**
	 * "Smart" object save.
	 */
	final public function save() {

		if(empty($this->id)) {
			$this->insert();
		} else {
			$this->update();
		}
	}

	/**
	 * Object update based on primary key.
	 *
	 * @throws Exception
	 * @throws \FSBO\Exception
	 */
	private function update() {

		$table = $this::TABLE;
		$primary = $this::PRIMARY_KEY;

		$db = PDO::getInstance();

		$changes = [];
		foreach($this->updater() as $column => $value) {
			$changes[] = "{$column} = {$db->quote($value)}";
		}

		$changes = implode(',', $changes);

		$conn = $db->prepare("UPDATE {$table} SET {$changes} WHERE {$primary} = ?");
		if(!$conn->execute([$this->id])) {
			throw new Exception("Update to {$table} failed!");
		}
	}

	/**
	 * @return object Called object or exception on error.
	 * @throws Exception
	 * @throws \FSBO\Exception
	 */
	private function insert() {

		$table = $this::TABLE;

		$db = PDO::getInstance();

		$columns = $values = $placeholders = [];
		foreach($this->updater() as $column => $value) {
			$columns[] = "{$column}";
			$values[] = $db->quote($value);
			$placeholders[] = ":{$column}";
		}

		$columnsStr = implode(',',$columns);
		$valuesStr = implode(',',$values);

		$conn = $db->prepare("INSERT INTO {$table} ({$columnsStr}) VALUES ({$valuesStr})");

		$conn->execute(array_combine($placeholders, $values));

		if($db->lastInsertId()) {
			// May return $this with updated primary key but
			// this approach will ignore any transformation triggered by DB.
			return static::getInstance($db->lastInsertId());
		}

		throw new Exception("Insert into {$table} failed!");
	}

	/**
	 * @throws Exception
	 * @throws \FSBO\Exception
	 */
	protected function delete() {

		$table = $this::TABLE;
		$primary = $this::PRIMARY_KEY;

		$db = PDO::getInstance();
		$conn = $db->prepare("DELETE FROM {$table} WHERE {$primary} = ?");

		if(!$conn->execute([$this->id])) {
			throw new Exception("Delete from {$table} failed!");
		}

	}

	/**
	 * Used for generating WHERE clause for searches.
	 *
	 * @param mixed $value
	 * @return bool|string
	 * @throws \FSBO\Exception
	 */
	static function toSQL($value) {

		if(empty($value)) return;

		$db = PDO::getInstance();
		if(is_string($value)) {
			return "= {$db->quote($value)}";
		} elseif(is_null($value)) {
			return "IS NULL";
		} elseif(is_int($value)) {
			return "= {$value}";
		} elseif(is_array($value)) {
			$in = implode(",", array_map(function($item) use ($db) {
				return static::toSQL($item);
			}, $value));
			return "IN ({$in})";
		}
		return false;
	}

}