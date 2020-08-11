<?php

namespace Garphild\SettingsManager\Errors;

/**
 * Exception fired when script try create a property with existing name
 *
 * @package Garphild\SettingsManager\Errors
 */
class PropertyExistException extends DefaultException {
  protected $propertyName = "Setting named %PARAM% already exists";
}
