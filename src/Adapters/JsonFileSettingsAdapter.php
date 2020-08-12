<?php

namespace Garphild\SettingsManager\Adapters;

use Garphild\SettingsManager\Errors\PropertyExistException;
use Garphild\SettingsManager\Errors\PropertyNotDescriptedInStructureException;
use Garphild\SettingsManager\Errors\PropertyNotExistException;
use Garphild\SettingsManager\Interfaces\iSettingsAdapter;
use Garphild\SettingsManager\Interfaces\iStructureAdapter;

class JsonFileSettingsAdapter extends JsonFile implements iSettingsAdapter {
  /**
   * @var iStructureAdapter
   */
  private $structure = null;

  public function __construct($basePath, $settingsFileName, $allowAllowCreateFile = false) {
    parent::__construct($basePath, $settingsFileName, $allowAllowCreateFile);
    $this->load();
  }

  public function isChanged(): bool
  {
    return $this->changed;
  }

  public function load(): JsonFileSettingsAdapter
  {
    $this->loadFile();
    return $this;
  }

  public function save(): JsonFileSettingsAdapter
  {
    $this->saveFile();
    $this->changed = false;
    return $this;
  }

  public function haveItem($name): bool
  {
    return isset($this->parsed[$name]);
  }

  public function removeItem($name): JsonFileSettingsAdapter
  {
    unset($this->parsed[$name]);
    $this->changed = true;
    return $this;
  }

  public function setValue($name, $value): JsonFileSettingsAdapter
  {
    if ($this->structure && !$this->structure->haveItem($name)) {
      throw new PropertyNotDescriptedInStructureException(null, $name);
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
    if (!isset($this->parsed[$name])) {
      throw new PropertyNotExistException("Setting named %PARAM% not exists for user", $name);
    }
    if ($this->structure && !$this->structure->haveItem($name)) {
      throw new PropertyNotDescriptedInStructureException(null, $name);
    }
    return $this->parsed[$name];
  }

  public function getNames(): array
  {
    return array_keys($this->parsed);
  }

  public function injectStructure(iStructureAdapter $structureAdapter)
  {
    $this->structure = $structureAdapter;
  }
}
