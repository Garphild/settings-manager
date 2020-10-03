<?php

namespace Garphild\SettingsManager;

use Garphild\SettingsManager\Adapters\MultipleGroupAdapter;
use Garphild\SettingsManager\Errors\AdapterExistsException;
use Garphild\SettingsManager\Errors\ItemNotAdapterException;
use Garphild\SettingsManager\Errors\PropertyNotExistException;
use Garphild\SettingsManager\Errors\UserAdapterNotExistException;
use Garphild\SettingsManager\Interfaces\iSettingsAdapter;
use Garphild\SettingsManager\Interfaces\iSettingsManagerUser;
use Garphild\SettingsManager\Interfaces\iSettingsManagerGroups;
use Garphild\SettingsManager\Interfaces\iSettingsManagerStructure;
use Garphild\SettingsManager\Interfaces\iSettingsManagerFinal;
use Garphild\SettingsManager\Interfaces\iStructureAdapter;


class SettingsManager implements iSettingsManagerFinal
{
  /**
   * @var string
   */
  private $name;
  /**
   * @var iStructureAdapter
   */
  private $structureAdapter = null;
  /**
   * @var MultipleGroupAdapter
   */
  private $groupSettingsAdapters = null;
  /**
   * @var iSettingsAdapter
   */
  private $userSettingsAdapter = null;

  function __construct(
    string $name,
    iStructureAdapter $structureAdapter,
    $groupSettingsAdapters = null,
    iSettingsAdapter $userSettingsAdapter = null
  ) {
    // Structure adapter is required
    if (!($structureAdapter instanceof iStructureAdapter)) {
      throw new ItemNotAdapterException(
        "%PARAM% is not instance of iStructureAdapter",
        get_class($structureAdapter)
      );
    }
    else {
      $this->structureAdapter = $structureAdapter;
    }
    // No group adapters
    if (!$groupSettingsAdapters) {
      $this->groupSettingsAdapters = new MultipleGroupAdapter();
    }
    // array of groups
    elseif (is_array($groupSettingsAdapters)) {
      $this->groupSettingsAdapters = new MultipleGroupAdapter();
      $this->groupSettingsAdapters->injectStructure($this->structureAdapter);
      foreach ($groupSettingsAdapters as $name => $adapter) {
        $this->groupSettingsAdapters->addGroupAdapter($name, $adapter);
      }
    }
    // MultipleGroupAdapter
    elseif ($groupSettingsAdapters instanceof MultipleGroupAdapter) {
      $this->groupSettingsAdapters = $groupSettingsAdapters;
    }
    // Single group adapter
    elseif ($groupSettingsAdapters instanceof iSettingsAdapter) {
      $this->groupSettingsAdapters = new MultipleGroupAdapter();
      $this->groupSettingsAdapters->addGroupAdapter('default', $groupSettingsAdapters);
    }
    if ($this->groupSettingsAdapters) {
      $this->groupSettingsAdapters->injectStructure($this->structureAdapter);
    }
    // User settings adapter
    if ($userSettingsAdapter !== null) {
      if (!($userSettingsAdapter instanceof iSettingsAdapter)) {
        throw new ItemNotAdapterException(null, get_class($userSettingsAdapter));
      } else {
        $this->userSettingsAdapter = $userSettingsAdapter;
        $this->userSettingsAdapter->injectStructure($this->structureAdapter);
      }
    }
    $this->name = $name;
  }

  public function haveItem(string $name)
  {
    return $this->structure()->haveItem($name);
  }

  /**
   * Combine array of arrays to single linear array
   *
   * @param array $valuesArray
   * @return array|mixed
   */
  protected function simpleConcatenator(array $valuesArray) {
    $startValue = array_shift($valuesArray);
    if (count($valuesArray) > 0) {
      foreach ($valuesArray as $v) {
        $startValue = array_merge($startValue, $v);
      }
    }
    return $startValue;
  }

  /**
   * Return user's settings adapter
   *
   * @return iSettingsAdapter
   * @throws UserAdapterNotExistException
   */
  function user() {
    if (
      !$this->userSettingsAdapter
      || !($this->userSettingsAdapter instanceof iSettingsAdapter)
    ) {
      throw new UserAdapterNotExistException();
    }
    return $this->userSettingsAdapter;
  }

  /**
   * Return a MultipleGroupAdapter
   *
   * @return MultipleGroupAdapter
   */
  function groups() {
    return $this->groupSettingsAdapters;
  }

  /**
   * Get a structure adapter
   *
   * @return iStructureAdapter
   */
  function structure() {
    return $this->structureAdapter;
  }

  function userGetValuesForPublic($own = false)
  {
    $tmp = $this->userSettingsAdapter->getValues();
    foreach($tmp as $k=>$v) {
      if (!$this->isPublic($k)) unset($tmp[$k]);
    }
    return $tmp;
  }

  /**
   * Check if setting is public
   *
   * @param string $name
   * @return bool
   */
  public function isPublic($name)
  {
    return $this->structureAdapter->isPublic($name);
  }

  public function canUserChangeValue($name)
  {
    // TODO: Implement canUserChangeValue() method.
  }

  /**
   * @return array
   */
  public function getDefaultValues()
  {
    return $this->structureAdapter->getDefaultValues();
  }

  public function getDefaultValuesForPublic()
  {
    return $this->structureAdapter->getDefaultValuesForPublic();
  }

  /**
   * Alias of SettingsManager::getNames
   *
   * @return array
   */
  public function getDefaultNames()
  {
    return $this->getNames();
  }

  public function getDefaultNamesForPublic()
  {
    return $this->structureAdapter->getItemNamesForPublic();
  }

  public function setDefaultValue($name, $value)
  {
    $this->structureAdapter->setValue($name, $value);
    return $this;
  }

  function groupGetValuesForPublic($groupID = null)
  {
    $res = $this->groupGetValues($groupID);
    foreach($res as $k=>$v) {
      if (!$this->isPublic($k)) {
        unset($res[$k]);
      }
    }
    return $res;
  }

  function groupGetNamesForPublic($groupID)
  {
    $data = [];
    foreach ($this->groupSettingsAdapters as $adapter) {
      $tmp = $adapter->getNames();
      $tmp = array_flip($tmp);
      $data = array_merge($data, $tmp);
    }
    return $data;
  }

  /**
   * Get concatenated value
   *
   * @param string $name
   * @return mixed
   * @throws PropertyNotExistException
   */
  function getValue($name)
  {
    if (!$this->structure()->haveItem($name)) {
      throw new PropertyNotExistException("Setting named %PARAM% not found in structure", $name);
    }
    if ($this->user()->haveItem($name)) return $this->user()->getValue($name);
    if ($this->groups()->haveItem($name)) return $this->groups()->getValue($name);
    return $this->structure()->getValue($name);
  }

  function getValues()
  {
    $arr = array_merge(
      $this->structureAdapter->getDefaultValues(),
      $this->groups()->getValues(),
      $this->user()->getValues()
    );
    return $arr;
  }

  function getValuesForPublic()
  {
    $arr = array_merge(
      $this->structure()->getDefaultValues(),
      $this->groups()->getValues(),
      $this->user()->getValues()
    );
    foreach ($arr as $k=>$v) {
      if (!$this->isPublic($k)) unset($arr[$k]);
    }
    return $arr;
  }

  /**
   * Get all names of allowed settings
   * @return array
   */
  function getNames()
  {
    return $this->structureAdapter->getItemNames();
  }

  /**
   * Get names of allowed settings enabled for public
   * @return array
   */
  function getNamesForPublic()
  {
    return $this->structureAdapter->getItemNamesForPublic();
  }

  /**
   * Load all data from adapters
   *
   * @return SettingsManager
   */
  function load()
  {
    return
      $this
        ->structureload()
        ->groupLoad()
        ->userLoad();
  }

  function save()
  {
      $this->structure()->save();
      $this->groups()->save();
      $this->user()->save();
      return $this;
  }
}
