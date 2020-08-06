<?php

namespace Garphild\SettingsManager\Adapters;

use Garphild\SettingsManager\Errors\PropertyNotExistException;
use Garphild\SettingsManager\Interfaces\iStructureAdapter;
use Garphild\SettingsManager\Models\SettingsItem;
use Garphild\SettingsManager\Errors\MissingFileException;
use Garphild\SettingsManager\Errors\PropertyExistException;

class JsonFileStructureAdapter extends JsonFile implements iStructureAdapter {
  public function __construct($basePath, $structureFileName) {
    parent::__construct($basePath, $structureFileName);
    $this->load();
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

  function getValues()
  {
    return $this->parsed;
  }

  public function createItem(string $name, SettingsItem $item): JsonFileStructureAdapter
  {
    if (isset($this->parsed[$name])) {
      throw new PropertyExistException("Property %PROPERTY% already exist");
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

  public function getDefaultValues()
  {
    $tmp = [];
    foreach($this->parsed as $name=>$value) {
      $tmp[$name] = $value->default;
    }
    return $tmp;
  }

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

  function getItemNames()
  {
    // TODO: Implement getItemNames() method.
  }

  function getItemNamesForApi()
  {
    return array_keys(
      array_filter($this->parsed, function ($value) { return $value->showToApi; })
    );
  }

  function getDefaultValuesForApi()
  {
    // TODO: Implement getDefaultValuesForApi() method.
  }
}
