<?php

/**
 * This file defines all DaoExceptions.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 * @subpackage Exceptions
 */

/**
 * The Exception base class for DaoExceptions.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 * @subpackage Exceptions
 */
class DaoException extends Exception {
  
}

/**
 * The WrongValue Exception. It's used if a value is not present in an enum for example.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 * @subpackage Exceptions
 */
class DaoWrongValueException extends DaoException {
  
}

/**
 * The Exception if some attribute types are not supported.
 * When importing a value, and your Dao does not support it, throw this exception.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 * @subpackage Exceptions
 */
class DaoNotSupportedException extends DaoException {
  
}

/**
 * The Exception if a query that should return a result gets nothing.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 * @subpackage Exceptions
 */
class DaoNotFoundException extends DaoException {
  
}

/**
 * This Exception gets thrown when a value can't be coerced, and provides the fallback value.
 * 
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 * @subpackage Exceptions
 */
class DaoCoerceException extends DaoException {

  /**
   * @var mixed
   */
  protected $fallbackValue;

  /**
   * @param mixed $fallbackValue
   * @param string $message 
   */
  public function __construct($fallbackValue, $message = '') {
    parent::__construct($message);
    $this->fallbackValue = $fallbackValue;
  }

  /**
   * @return mixed
   */
  public function getFallbackValue() {
    return $this->fallbackValue;
  }

}