<?php

namespace Garphild\SettingsManager\Interfaces;

use Garphild\SettingsManager\Models\SettingsItem;

interface iStructureAdapter {
  function load();
  function save();
  function getValues();
  function getDefaultValues();
  function createItem(string $name, SettingsItem $item);
  function removeItem(string $name);
  function haveItem(string $name): bool;
}
