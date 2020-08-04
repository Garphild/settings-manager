<?php

namespace Garphild\SettingsManager\Adapters;

use Garphild\SettingsManager\Interfaces\iSettingsAdapter;

class JsonFileSettingsAdapter extends JsonFile implements iSettingsAdapter {
  public function __construct($basePath, $structureFileName) {
    parent::__construct($basePath, $structureFileName);
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
    $this->parsed[$name] = $value;
    return $this;
  }

  public function getValues(): array
  {
    return $this->parsed;
  }

  public function getItemValue($name)
  {
    return $this->parsed[$name];
  }
}
