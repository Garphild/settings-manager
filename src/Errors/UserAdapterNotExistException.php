<?php

namespace Garphild\SettingsManager\Errors;

use Garphild\SettingsManager\Errors\DefaultException;

/**
 * Exception fired when script try create a property with existing name
 *
 * @package Garphild\SettingsManager\Errors
 */
class UserAdapterNotExistException extends DefaultException {
  protected $propertyName = "User's settings adapter not exists";
}
