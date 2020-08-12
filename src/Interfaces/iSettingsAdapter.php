<?php

namespace Garphild\SettingsManager\Interfaces;

interface iSettingsAdapter {
  public function load();
  public function save();

  public function haveItem($name): bool;
  public function isChanged();

  public function removeItem($name);

  public function setValue($name, $value);

  public function getValues();
  public function getNames();
  public function getValue($name);

  public function injectStructure(iStructureAdapter $structureAdapter);
}
