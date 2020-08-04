<?php

namespace Garphild\SettingsManager\Interfaces;

use Settings\Models\SettingsItem;

interface iStructureAdapter {
  function load();
  function save();
  function getValues();
  function createItem(string $name, SettingsItem $item);
  function removeItem(string $name);
}
