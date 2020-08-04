<?php

namespace Garphild\SettingsManager\Errors;

/**
 * Exception fired when check for dir or file failed
 *
 * @package Garphild\SettingsManager\Errors
 */
class MissingFileException extends \Exception {
  /**
   * @var string path oto dir or file
   */
  private $dirName = '';
  private $currentMessage = "";

  /**
   * Constructor
   *
   * @param string $message message with param %PARAM% which replace path
   * @param string $path name of manager who fires exception
   * @param int $code error code (default: ErrorCodes::PATH_NOT_EXISTS)
   * @param Exception|null $previous
   */
  public function __construct($message, $path = '', $code = ErrorCodes::PATH_NOT_EXISTS, Exception $previous = null) {
    $this->dirName = $path;
    $this->currentMessage = $message;
    parent::__construct($this->getMessageAsString(), $code, $previous);
  }

  /**
   * Convert exception to string
   * @return string
   */
  public function __toString() {
    return __CLASS__ . ": [{$this->code}]: ".$this->getMessageAsString()."\n";
  }

  /**
   * Replace params in message
   *
   * @return string
   */
  function getMessageAsString() {
    return str_replace("%PARAM%", $this->dirName, $this->currentMessage);
  }
}
