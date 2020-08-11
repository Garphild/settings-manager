<?php

namespace Garphild\SettingsManager\Interfaces;

interface iSettingsManagerStructure {
  /* Works with structure and defaults CRUD */

  // Checks
  public function haveItem($name);
  public function isPublic($name);
  public function canUserChangeValue($name);
  public function structureIsChanged($name);

  // CREATE
  public function structureAdd($name, iStructureAdapter $item);
  // Value READ SINGLE
  public function structureGetValue($name);
  // Structure READ SINGLE
  public function structureGetItem($name);
  // READ MULTIPLE
  public function getDefaultValues();
  public function getDefaultValuesForPublic();
  public function getDefaultNames();
  public function getDefaultNamesForPublic();
  // UPDATE
  public function setDefaultValue($name, $value);
  public function setProperty($name, $propertyName, $propertyValue);
  public function setRestrictionGlobal($name, $propertyValue);
  public function setRestrictionGroup($name, $propertyValue);
  public function setRestrictionUser($name, $propertyValue);
  public function changeType($name, $type);
  public function addVariant($name, $variant);
  // DELETE
  public function structureDeleteItem($name);
  // LOAD
  public function structureload();
  // SAVE PERSISTENT
  public function structureSave();


}
