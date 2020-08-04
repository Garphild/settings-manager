<?php

namespace Garphild\SettingsManager\Adapters;

use Garphild\SettingsManager\Interfaces\iStructureAdapter;
use Garphild\SettingsManager\Models\SettingsItem;
use Garphild\SettingsManager\Errors\MissingFileException;
use Garphild\SettingsManager\Errors\PropertyExistException;

class JsonFileStructureAdapter implements iStructureAdapter {
  private $basePath;
  private $filename;
  private $parsed = [];
  private $file = '';

  public function __construct($basePath, $structureFileName) {
    $this->filename = $structureFileName;
    $lastChar = substr($basePath, -1);
    if ($lastChar === '/' || $lastChar === '\\') {
      $basePath = substr($basePath, 0, -1);
    }
    $this->basePath = $basePath;
    if (!file_exists($this->basePath)) {
      throw new MissingFileException("Dir %PARAM% not exists", $this->basePath);
    }
    if (!is_dir($this->basePath)) {
      throw new MissingFileException("%PARAM% is not a folder", $this->basePath);
    }
    if (!file_exists($this->getFilename())) {
      throw new MissingFileException("File %PARAM% not exists", $this->getFilename());
    }
    $this->load();
  }

  private function getFilename()
  {
    return $this->basePath . "/" . $this->filename;
  }

  function load()
  {
    $this->file = file_get_contents($this->getFilename());
    $this->parsed = json_decode($this->file, true, 99999999);
    foreach($this->parsed as $name=>&$value) {
      $value = new SettingsItem($value);
    }
    unset($value);
    return $this;
  }

  function save()
  {
    $this->file = json_encode($this->parsed, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT, 9999999);
    file_put_contents($this->getFilename(), $this->file);
  }

  function getValues()
  {
    return $this->parsed;
  }

  function createItem(string $name, SettingsItem $item)
  {
    if (isset($this->parsed[$name])) {
      throw new PropertyExistException("Property %PROPERTY% already exist");
    }
    $this->parsed[$name] = $item;
    return $this;
  }

  function removeItem(string $name)
  {
    unset($this->parsed[$name]);
    return $this;
  }

  function haveItem(string $name): bool
  {
    return isset($this->parsed[$name]);
  }
}
