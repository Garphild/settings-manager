<?php

namespace Garphild\SettingsManager\Interfaces;

interface iSettingsManagerStructure {
  /* Works with structure and defaults CRUD */

  public function isPublic($name);
  public function canUserChangeValue($name);

  // READ MULTIPLE
  public function getDefaultValues();
  public function getDefaultValuesForPublic();
  public function getDefaultNames();
  public function getDefaultNamesForPublic();
  // UPDATE
  public function setDefaultValue($name, $value);


}
