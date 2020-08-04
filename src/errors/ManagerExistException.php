<?php

namespace Garphild\SettingsManager\Errors;

/**
 * Exception fired when script try create a manager with existing name
 * or try get a manager with name which was not exists
 *
 * @package Garphild\SettingsManager\Errors
 */
class ManagerExistException extends \Exception {
  /**
   * @var string name of manager which fire exception
   */
  private $managerName = '';

  /**
   * Constructor
   *
   * @param $message message with param %PARAM% which replace manager name
   * @param string $managerName name of manager who fires exception
   * @param int $code error code (default: ErrorCodes::PROPERTY_EXISTS)
   * @param Exception|null $previous
   */
  public function __construct($message, $managerName = '', $code = ErrorCodes::PROPERTY_EXISTS, Exception $previous = null) {
    $this->managerName = $managerName;
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
    return str_replace("%PARAM%", $this->managerName, $this->message);
  }
}
