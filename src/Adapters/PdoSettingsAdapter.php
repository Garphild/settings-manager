<?php

namespace Garphild\SettingsManager\Adapters;

use Garphild\SettingsManager\Errors\PropertyExistException;
use Garphild\SettingsManager\Errors\PropertyNotDescriptedInStructureException;
use Garphild\SettingsManager\Errors\PropertyNotExistException;
use Garphild\SettingsManager\Interfaces\iSettingsAdapter;
use Garphild\SettingsManager\Interfaces\iStructureAdapter;
use PDO;

class PdoSettingsAdapter implements iSettingsAdapter {
  /**
   * @var iStructureAdapter
   */
  private $structure = null;
  private $data = [];
  /**
   * @var PDO
   */
  private $pdo;
  private $tableName;
  private $changed = false;
  private $restrictionValue = null;
  private $restrictionColumn = null;

  public function __construct(\PDO $pdo, $tableName, $restrictionColumn = null, $restrictionValue = null) {
    $this->pdo = $pdo;
    $this->tableName = $tableName;
    $this->restrictionValue = $restrictionValue;
    $this->restrictionColumn = $restrictionColumn;
    $this->load();
  }

  public function isChanged(): bool
  {
    return $this->changed;
  }

  public function load(): PdoSettingsAdapter
  {
    $sql = "SELECT name, value FROM {$this->tableName}";
    if ($this->restrictionValue !== null && $this->restrictionColumn !== null) {
      $sql .= " WHERE {$this->restrictionColumn} = {$this->restrictionValue}";
    }
    $resp = $this->pdo->query($sql);
    if ($resp) {
      $this->data = $resp->fetchAll(\PDO::FETCH_KEY_PAIR);
      $this->changed = false;
    } else {
      // TODO: Errors handler
    }
    return $this;
  }

  public function save(): PdoSettingsAdapter
  {
    if ($this->changed) {
      $this->pdo->beginTransaction();
      foreach ($this->data as $name => $value) {
        $nameQuoted = $this->pdo->quote($name);
        $valueQuoted = $this->pdo->quote($value);
        $sql = "UPDATE {$this->tableName} SET value = {$valueQuoted} WHERE name={$nameQuoted} AND {$this->restrictionColumn} = {$this->restrictionValue}";
        $this->pdo->exec($sql);
      }
      $this->pdo->commit();
      $this->changed = false;
    }
    return $this;
  }

  public function haveItem($name): bool
  {
    return isset($this->data[$name]);
  }

  public function removeItem($name): PdoSettingsAdapter
  {
    unset($this->data[$name]);
    $this->changed = true;
    return $this;
  }

  public function setValue($name, $value): PdoSettingsAdapter
  {
    if ($this->structure && !$this->structure->haveItem($name)) {
      throw new PropertyNotDescriptedInStructureException(null, $name);
    }
    $this->data[$name] = $value;
    $this->changed = true;
    return $this;
  }

  public function getValues(): array
  {
    return $this->data;
  }

  public function getValue($name)
  {
    if (!isset($this->data[$name])) {
      throw new PropertyNotExistException("Setting named %PARAM% not exists for user", $name);
    }
    if ($this->structure && !$this->structure->haveItem($name)) {
      throw new PropertyNotDescriptedInStructureException(null, $name);
    }
    return $this->data[$name];
  }

  public function getNames(): array
  {
    return array_keys($this->data);
  }

  public function injectStructure(iStructureAdapter $structureAdapter)
  {
    $this->structure = $structureAdapter;
  }
}
