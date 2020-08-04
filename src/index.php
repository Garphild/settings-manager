<?php

namespace Garphild\SettingsManager;

use Garphild\SettingsManager\Interfaces\iSettingsAdapter;
use Garphild\SettingsManager\Interfaces\iStructureAdapter;
use Garphild\SettingsManager\Errors\ManagerExistException;

/**
 * Settings manager for users, groups and defaults.
 *
 * @package Garphild\SettingsManager
 */
class Index {
  /**
   * @var SettingsManager[] List of named settings managers
   */
  private static $managers = [];

  /**
   * Create manager, store it in list and return manager instance.
   *
   * @param string $name Manager name
   * @param iStructureAdapter $structureAdapter adapter for settings structure
   * @param iSettingsAdapter $settingsAdapter adapter for settings
   * @return SettingsManager
   * @throws ManagerExistException
   */
  public static function createManager(string $name, iStructureAdapter $structureAdapter, iSettingsAdapter $settingsAdapter) {
    if(isset(self::$managers[$name])) {
      throw new ManagerExistException("The manager named %PARAM% exists");
    }
    self::$managers[$name] = new SettingsManager($name, $structureAdapter, $settingsAdapter);
    return self::$managers[$name];
  }

  /**
   * Get a manager which was created early or throw error
   *
   * @param string $name manager name
   * @return SettingsManager
   * @throws ManagerExistException
   */
  public static function getManager($name) {
    if(isset(self::$managers[$name])) {
      throw new ManagerExistException("The manager named %PARAM% not exists");
    }
    return self::$managers[$name];
  }
}
