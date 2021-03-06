<?php

/**
 * This file contains the abstract DaoResultIterator definition.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 */

/**
 * The Dao Result iterator is returned whenever a query returns more than one row.
 * It implements the default php Iterator Interface, so foreach() works on it, and the Countable interface
 * so you can call count($someResultIterator)
 *
 * Typical usage:
 * <code>
 * <?php
 * $users = $userDao->getAll(); // Returns the iterator
 * echo $users->count() . ' users returned.';
 * foreach ($users as $user) {
 *     // ...do stuff...
 * }
 * ?>
 * </code>
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 */
abstract class DaoResultIterator implements Iterator, Countable {

  /**
   * @var Dao
   */
  protected $dao = false;
  /**
   * The number of rows
   *
   * @var int
   */
  protected $length = 0;
  /**
   * The number of rows
   *
   * @var int
   */
  protected $totalLength = null;
  /**
   * Stores the current key (in this case: row number) of the iterator.
   * @var integer
   */
  protected $currentKey = 0;
  /**
   * If set to true, instead of returning the Record, Record->getArray() is returned.
   *
   * @var bool
   * @see asArrays()
   */
  protected $returnRecordsAsArray = false;
  /**
   * @var bool
   */
  protected $userRequestCache = true;
  /**
   * @var array
   */
  protected $requestCachedRecords = array();

  /**
   * @param Dao $dao
   */
  public function __construct($dao, $totalLength = null, $cacheValues = true) {
    $this->dao = $dao;
    $this->totalLength = $totalLength;
    $this->userRequestCache = $cacheValues;
  }

  /**
   * Returns the current key (row number).
   * @return int
   */
  public function key() {
    return $this->currentKey;
  }

  /**
   * Return the current Record.
   * If getAsArray() has been called, returns an array instead of the Record.
   *
   * @return Record|array
   */
  public function current() {
    if ( ! $this->valid()) return null;
    $record = null;
    if ($this->userRequestCache) {
      $record = $this->getCachedRecord();
    }
    if ( ! $record) {
      $record = $this->cacheAndReturnRecord($this->dao->getRecordFromData($this->getCurrentData()));
    }
    return $this->returnRecordsAsArray ? $record->getArray() : $record;
  }

  /**
   * If there is a cached record for the key, it gets returned.
   * 
   * @return Record
   */
  protected function getCachedRecord() {
    if (isset($this->requestCachedRecords[$this->key()])) return $this->requestCachedRecords[$this->key()];
  }

  protected function cacheAndReturnRecord($record) {
    $this->cachedRecord[$this->key()] = $record;
    return $record;
  }

  /**
   * Returns the data of the current iteration
   * @return array
   */
  abstract protected function getCurrentData();

  /**
   * @return Dao
   */
  public function getDao() {
    return $this->dao;
  }

  /**
   * @param bool $returnRecordsAsArray
   * @see $returnRecordsAsArray
   * @return DaoResultIterator Returns itself for chaining.
   */
  public function asArrays($returnRecordsAsArray = true) {
    $this->returnRecordsAsArray = ! ! $returnRecordsAsArray;
    return $this;
  }

  /**
   * Check if the pointer is still valid.
   *
   * @return bool
   */
  public function valid() {
    return $this->key() <= $this->count();
  }

  /**
   * Return the number of rows.
   *
   * @return int
   */
  public function count() {
    return $this->length;
  }

  public function countTotal() {
    return $this->totalLength ? $this->totalLength : $this->count();
  }

  /**
   * Returns all records as arrays in a list.
   * 
   * @param bool $resolveReferences
   * @return array
   */
  public function getArray($resolveReferences = false) {
    $array = array();

    foreach ($this as $record) {
      $array[] = $record->getArray($resolveReferences);
    }

    return $array;
  }

}

