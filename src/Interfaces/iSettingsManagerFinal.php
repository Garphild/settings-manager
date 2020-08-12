<?php

namespace Garphild\SettingsManager\Interfaces;

interface iSettingsManagerFinal {
  // READ SINGLE
  function getValue($name);
  function getValues();
  function getValuesForPublic();
  function getNames();
  function getNamesForPublic();
  // LOAD
  function load();
  // SAVE PERSISTENT
  function save();
  // Adapters
  function user();
  function groups();
  function structure();
  // Checks
  public function haveItem(string $name);
}
