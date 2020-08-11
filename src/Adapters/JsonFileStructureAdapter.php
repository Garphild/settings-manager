<?php

namespace Garphild\SettingsManager\Adapters;

use Garphild\SettingsManager\Errors\PropertyNotExistException;
use Garphild\SettingsManager\Interfaces\iStructureAdapter;
use Garphild\SettingsManager\Models\SettingsItem;
use Garphild\SettingsManager\Errors\PropertyExistException;

class JsonFileStructureAdapter extends JsonFile implements iStructureAdapter {
  public function __construct($basePath, $structureFileName) {
    parent::__construct($basePath, $structureFileName);
    $this->load();
  }

  function isPublic($name) {
    return $this->parsed[$name]->isPublic();
  }

  function makePublic($name) {
    $this->parsed[$name]->makePublic();
    return $this;
  }

  function makePrivate($name) {
    $this->parsed[$name]->makePrivate();
    return $this;
  }

  function load()
  {
    $this->loadFile();
    foreach($this->parsed as $name=>&$value) {
      $value = new SettingsItem($value);
    }
    unset($value);
    return $this;
  }

  function save(): JsonFileStructureAdapter
  {
    $this->file = json_encode($this->parsed, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT, 9999999);
    file_put_contents($this->getFilename(), $this->file);
    return $this;
  }

  function getItems()
  {
    return $this->parsed;
  }

  public function createItem(string $name, SettingsItem $item): JsonFileStructureAdapter
  {
    if (isset($this->parsed[$name])) {
      throw new PropertyExistException(null, $name);
    }
    $this->parsed[$name] = $item;
    return $this;
  }

  public function removeItem(string $name): JsonFileStructureAdapter
  {
    unset($this->parsed[$name]);
    return $this;
  }

  public function haveItem(string $name): bool
  {
    return isset($this->parsed[$name]);
  }

  /**
   * @return array
   */
  public function getDefaultValues()
  {
    $tmp = [];
    foreach($this->parsed as $name=>$value) {
      $tmp[$name] = $value->default;
    }
    return $tmp;
  }

  /**
   * @param string $name
   * @return string
   * @throws PropertyNotExistException
   */
  function getValue($name)
  {
    if (
      isset($this->parsed[$name])
      && $this->parsed[$name] instanceof SettingsItem
      && isset($this->parsed[$name]->default)
    ) {
      return $this->parsed[$name]->default;
    }
    throw new PropertyNotExistException(null, $name);
  }

  /**
   * @param string $name
   * @return SettingsItem
   * @throws PropertyNotExistException
   */
  function getItem($name) {
    if (
      $this->haveItem($name)
    ) {
      return $this->parsed[$name];
    }
    throw new PropertyNotExistException(null, $name);
  }

  function getItemNames()
  {
    return array_keys($this->parsed);
  }

  function getItemNamesForPublic()
  {
    return array_keys(
      array_filter($this->parsed, function ($value) { return $value->showToApi; })
    );
  }

  function getDefaultValuesForPublic()
  {
    return array_map(
      function ($value) { return $value->default;},
      array_filter($this->parsed, function ($value) { return $value->showToApi; })
    );
  }

  function setValue($name, $value)
  {
    if (!$this->haveItem($name)) {
      throw new PropertyNotExistException(null, $name);
    }
    $this->parsed[$name]->default = $value;
  }
}
