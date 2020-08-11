<?php

namespace Garphild\SettingsManager\Interfaces;

use Garphild\SettingsManager\Models\SettingsItem;

interface iStructureAdapter {
  function load();
  function save();
  function getItems();

  /**
   * @return array
   */
  function getDefaultValues();
  function createItem(string $name, SettingsItem $item);
  function removeItem(string $name);
  function haveItem(string $name): bool;
  function getItemNames();
  function getItemNamesForPublic();
  function getDefaultValuesForPublic();
  function getValue($name);
  function setValue($name, $value);

  function isPublic($name);
  function makePublic($name);
  function makePrivate($name);

  function getItem($name);
}
