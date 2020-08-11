<?php

namespace Garphild\SettingsManager\Adapters;

use Garphild\SettingsManager\Errors\PropertyExistException;
use Garphild\SettingsManager\Interfaces\iSettingsAdapter;

class JsonFileSettingsAdapter extends JsonFile implements iSettingsAdapter {
  public function __construct($basePath, $settingsFileName) {
    parent::__construct($basePath, $settingsFileName);
    $this->load();
  }

  public function load(): JsonFileSettingsAdapter
  {
    $this->loadFile();
    return $this;
  }

  public function save(): JsonFileSettingsAdapter
  {
    $this->file = json_encode($this->parsed, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT, 9999999);
    file_put_contents($this->getFilename(), $this->file);
    return $this;
  }

  public function haveItem($name): bool
  {
    return isset($this->parsed[$name]);
  }

  public function removeItem($name): JsonFileSettingsAdapter
  {
    unset($this->parsed[$name]);
    return $this;
  }

  public function addItem($name, $value): JsonFileSettingsAdapter
  {
    if (isset($this->parsed[$name])) {
      throw new PropertyExistException(null, $name);
    }
    $this->parsed[$name] = $value;
    return $this;
  }

  public function getValues(): array
  {
    return $this->parsed;
  }

  public function getValue($name)
  {
    return $this->parsed[$name];
  }

  function getNames()
  {
    return array_keys($this->parsed);
  }
}
