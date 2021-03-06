<?php

namespace Garphild\SettingsManager\Errors;

use Garphild\SettingsManager\Errors\DefaultException;

/**
 * Exception fired when script try create a property with existing name
 *
 * @package Garphild\SettingsManager\Errors
 */
class NoAdapterException extends DefaultException {
  protected $propertyName = "Adapter named %PARAM% not exists";
}
