<?php

namespace Garphild\SettingsManager\Errors;
use Garphild\SettingsManager\Errors\ErrorCodes;

class DefaultException extends \Exception {
  protected $currentMessage = "";

  /**
   * Constructor
   *
   * @param string $message message with param %PARAM% which replace manager name
   * @param string $path name of manager who fires exception
   * @param int $code error code (default: ErrorCodes::PROPERTY_EXISTS)
   * @param Exception|null $previous
   */
  public function __construct($message = null, $param = null, $code = ErrorCodes::DEFAULT, Exception $previous = null) {
    if ($param !== null) $this->propertyName = $param;
    if ($message !== null) $this->currentMessage = $message;
    parent::__construct($this->getMessageAsString(), $code, $previous);
  }

  /**
   * Replace params in message
   *
   * @return string
   */
  function getMessageAsString() {
    return str_replace("%PARAM%", $this->propertyName, $this->currentMessage);
  }

  /**
   * Convert exception to string
   * @return string
   */
  public function __toString() {
    return __CLASS__ . ": [{$this->code}]: ".$this->getMessage()."\n";
  }
}
