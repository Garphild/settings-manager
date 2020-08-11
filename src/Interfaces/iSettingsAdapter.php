<?php

namespace Garphild\SettingsManager\Interfaces;

interface iSettingsAdapter {
  function load();
  function save();
  function haveItem($name): bool;
  function removeItem($name);
  function addItem($name, $value);
  function getValues();
  function getNames();
  function getValue($name);
}
