<?php

namespace Garphild\SettingsManager;

use Garphild\SettingsManager\Interfaces\iSettingsAdapter;
use Garphild\SettingsManager\Interfaces\iStructureAdapter;

class SettingsManager {
  private $name;
  private $structureAdapter;
  private $settingsAdapter;
  function __construct(
    string $name,
    iStructureAdapter $structureAdapter,
    iSettingsAdapter $settingsAdapter
  ) {
    $this->name = $name;
    $this->structureAdapter = $structureAdapter;
    $this->settingsAdapter = $settingsAdapter;
  }
}
