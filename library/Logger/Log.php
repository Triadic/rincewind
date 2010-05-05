<?php

/**
 * This file contains the Log definition.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Logger
 **/


if (!class_exists('LogException')) include(dirname(__FILE__) . '/LoggerExceptions.php');

/**
 * The Log class is abstract and only used with static function.
 * It's used to store loggers, and select the correct one for
 * a specific context.
 *
 * Example:
 *
 * <code>
 * <?php
 *   Log::error('Some message', 'Dao');
 * ?>
 * </code>
 *
 * If you didn't set a logger for a specific context, it will be ignored.
 *
 * If you don't pass a context, the context Log::GENERAL is used.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Logger
 **/
abstract class Log {

  /**
   * The string for the general logger.
   * @var string
   */
  const GENERAL = ' GENERAL ';

  /**
   * Contains a list of loggers for each context.
   * You can add a logger with Log::addLogger()
   * @var array
   * @see addLogger
   */
  protected static $loggers = array();


  /**
   * Adds a logger in the loggers array to be used in a certain context.
   *
   * @param Logger $logger
   * @param string $context Only letters, numbers, underscore and dash is allowed to avoid errors.
   */
  public static function addLogger($logger, $context = self::GENERAL) {
    if ($context !== self::GENERAL && (empty($context) || preg_replace('/[^a-z0-9\_\-]/im', '', $context) != $context)) throw new LogException("The context name '$context' is not allowed.");
    $context = self::sanitizeContext($context);
    self::$loggers[$context] = $logger;
  }


  /**
   * Returns a logger for a specific context.
   *
   * @param string $context
   * @return Logger or null if not set for this context.
   */
  public static function getLogger($context = self::GENERAL) {
    $context = self::sanitizeContext($context);
    if (isset(self::$loggers[$context])) return self::$loggers[$context];
    else return null;
  }


  /**
   * Logs a debug message in the logger for the specific context
   *
   * @param string $message
   * @param string $context
   * @return bool true on success, false if no logger specified for the context.
   */
  public static function debug($message, $context = self::GENERAL) {
    $context = self::sanitizeContext($context);
    if ($logger = self::getLogger($context)) {
      $logger->debug($message);
      return true;
    }
    return false;
  }


  /**
   * Logs an info message in the logger for the specific context
   *
   * @param string $message
   * @param string $context
   * @return bool true on success, false if no logger specified for the context.
   */
  public static function info($message, $context = self::GENERAL) {
    $context = self::sanitizeContext($context);
    if ($logger = self::getLogger($context)) {
      $logger->info($message);
      return true;
    }
    return false;
  }


  /**
   * Logs a warning message in the logger for the specific context
   *
   * @param string $message
   * @param string $context
   * @return bool true on success, false if no logger specified for the context.
   */
  public static function warning($message, $context = self::GENERAL) {
    $context = self::sanitizeContext($context);
    if ($logger = self::getLogger($context)) {
      $logger->warning($message);
      return true;
    }
    return false;
  }


  /**
   * Logs an error message in the logger for the specific context
   *
   * @param string $message
   * @param string $context
   * @return bool true on success, false if no logger specified for the context.
   */
  public static function error($message, $context = self::GENERAL) {
    $context = self::sanitizeContext($context);
    if ($logger = self::getLogger($context)) {
      $logger->error($message);
      return true;
    }
    return false;
  }


  /**
   * Logs a fatal error message in the logger for the specific context
   *
   * @param string $message
   * @param string $context
   * @return bool true on success, false if no logger specified for the context.
   */
  public static function fatal($message, $context = self::GENERAL) {
    $context = self::sanitizeContext($context);
    if ($logger = self::getLogger($context)) {
      $logger->fatal($message);
      return true;
    }
    return false;
  }


  /**
   * Sanitizes a context.
   *
   * @param string $context
   * @return string sanitized context.
   */
  protected static function sanitizeContext($context) {
    return strtolower($context);
  }


}

?>