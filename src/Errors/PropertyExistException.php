<?php

namespace Garphild\SettingsManager\Errors;

/**
 * Exception fired when script try create a property with existing name
 *
 * @package Garphild\SettingsManager\Errors
 */
class PropertyExistException extends \Exception {
  private $propertyName = 'Unknown exception';

  /**
   * Constructor
   *
   * @param string $message message with param %PARAM% which replace manager name
   * @param string $path name of manager who fires exception
   * @param int $code error code (default: ErrorCodes::PROPERTY_EXISTS)
   * @param Exception|null $previous
   */
  public function __construct($message, $path = '', $code = ErrorCodes::PROPERTY_EXISTS, Exception $previous = null) {
    $this->propertyName = $path;
    parent::__construct($this->getMessageAsString(), $code, $previous);
  }

  /**
   * Convert exception to string
   * @return string
   */
  public function __toString() {
    return __CLASS__ . ": [{$this->code}]: ".$this->getMessage()."\n";
  }

  /**
   * Replace params in message
   *
   * @return string
   */
  function getMessageAsString() {
    return str_replace("%PROPERTY%", $this->propertyName, $this->message);
  }
}
