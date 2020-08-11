<?php

namespace Garphild\SettingsManager;

use Garphild\SettingsManager\Errors\ItemNotAdapterException;
use Garphild\SettingsManager\Errors\PropertyNotExistException;
use Garphild\SettingsManager\Interfaces\iSettingsAdapter;
use Garphild\SettingsManager\Interfaces\iSettingsManagerUser;
use Garphild\SettingsManager\Interfaces\iSettingsManagerGroups;
use Garphild\SettingsManager\Interfaces\iSettingsManagerStructure;
use Garphild\SettingsManager\Interfaces\iSettingsManagerFinal;
use Garphild\SettingsManager\Interfaces\iStructureAdapter;


class SettingsManager implements
  iSettingsManagerStructure,
  iSettingsManagerGroups,
  iSettingsManagerFinal,
  iSettingsManagerUser
{
  /**
   * @var string
   */
  private $name;
  /**
   * @var iStructureAdapter
   */
  private $structureAdapter;
  /**
   * @var iSettingsAdapter[]
   */
  private $groupSettingsAdapters;
  /**
   * @var iSettingsAdapter
   */
  private $userSettingsAdapter;
  /**
   * @var bool
   */
  private $structureChanged = false;
  /**
   * @var bool
   */
  private $userChanged = false;
  /**
   * @var bool[]
   */
  private $groupsChanged = [];

  function __construct(
    string $name,
    iStructureAdapter $structureAdapter,
    array $groupSettingsAdapters,
    iSettingsAdapter $userSettingsAdapter = null
  ) {
    foreach ($groupSettingsAdapters as $aname=>$adapter) {
      if (!($adapter instanceof iSettingsAdapter)) {
        throw new ItemNotAdapterException(null, get_class($adapter));
      }
      $this->groupsChanged[$aname] = false;
    }
    if ($userSettingsAdapter !== null) {
      if (!($userSettingsAdapter instanceof iSettingsAdapter)) {
        throw new ItemNotAdapterException(null, get_class($userSettingsAdapter));
      }
    }
    if (!($structureAdapter instanceof iStructureAdapter)) {
      throw new ItemNotAdapterException("%PARAM% is not instance of iStructureAdapter", get_class($structureAdapter));
    }
    $this->name = $name;
    $this->structureAdapter = $structureAdapter;
    $this->groupSettingsAdapters = $groupSettingsAdapters;
    $this->userSettingsAdapter = $userSettingsAdapter;
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
   * Get own user settings or combined settings
   *
   * @param bool $own (default: false) Only own user settings if true or combined settings (default, groups, user) if false
   * @return array
   */
  function userGetValues(bool $own = false)
  {
    if ($own) {
      if ($this->userSettingsAdapter === null) {
        return [];
      }
      return $this->userSettingsAdapter->getValues();
    }
    $tmp = [];
    $tmp[] = $this->getDefaultValues();
    if (count($this->groupSettingsAdapters) > 0) {
      $tmp[] = $this->groupGetValues();
    }
    if ($this->userSettingsAdapter !== null) {
      $tmp[] = $this->userSettingsAdapter->getValues();
    }
    return $this->simpleConcatenator($tmp);
  }

  public function userIsChanged()
  {
    // TODO: Implement userIsChanged() method.
  }

  /**
   * Check user settings value exists. Only own values.
   *
   * @param string $name
   * @return bool
   */
  public function userHaveItem($name)
  {
    return $this->userSettingsAdapter->haveItem($name);
  }

  /**
   * Add user settings or change it
   *
   * @param string $name
   * @param mixed $value
   * @return SettingsManager
   * @throws PropertyNotExistException
   */
  function userAddValue($name, $value)
  {
    if ($this->haveItem($name)) {
      $this->userSettingsAdapter->addItem($name, $value);
    } else {
      throw new PropertyNotExistException("Setting named %PARAM% not found in structure", $name);
    }
    return $this;
  }

  function userGetValue($name, $own = false)
  {
    if ($own) {
      return $this->userSettingsAdapter->getValue($name);
    } else {
      return $this->getValue($name);
    }
  }

  function userGetValuesForPublic($own = false)
  {
    $tmp = $this->userSettingsAdapter->getValues();
    foreach($tmp as $k=>$v) {
      if (!$this->isPublic($k)) unset($tmp[$k]);
    }
    return $tmp;
  }

  function userGetNames()
  {
    return $this->userSettingsAdapter->getNames();
  }

  function userSetValue($name, $value)
  {
    $this->userSettingsAdapter->addItem($name, $value);
  }

  /**
   * remove setting
   *
   * @param string $name
   */
  function userRemoveValue($name)
  {
    $this->userSettingsAdapter->removeItem($name);
  }

  /**
   * Load user's settings
   *
   * @return SettingsManager
   */
  function userLoad()
  {
    if ($this->userSettingsAdapter) {
      $this->userSettingsAdapter->load();
    }
    return $this;
  }

  /**
   * Save user's settings
   *
   * @return SettingsManager
   */
  function userSave()
  {
    $this->userSettingsAdapter->save();
    return $this;
  }

  /**
   * Check setting exists in structure
   *
   * @param string $name
   * @return bool
   */
  public function haveItem($name)
  {
    return $this->structureAdapter->haveItem($name);
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

  public function structureIsChanged($name)
  {
    // TODO: Implement structureIsChanged() method.
  }

  /**
   * Add new item to structure
   *
   * @param string $name
   * @param iStructureAdapter $item
   * @return SettingsManager
   */
  public function structureAdd($name, iStructureAdapter $item)
  {
    $this->structureAdapter->createItem($name, $item);
    return $this;
  }

  /**
   * Get default value for setting
   *
   * @param string $name
   * @return mixed
   */
  public function structureGetValue($name)
  {
    return $this->structureAdapter->getValue($name);
  }

  /**
   * @param string $name
   * @return mixed
   * @throws Garphild\SettingsManager\Errors\PropertyNotExistException
   */
  public function structureGetItem($name)
  {
    return $this->structureAdapter->getItem($name);
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

  public function setProperty($name, $propertyName, $propertyValue)
  {
    // TODO: Implement setProperty() method.
  }

  public function setRestrictionGlobal($name, $propertyValue)
  {
    // TODO: Implement setRestrictionGlobal() method.
  }

  public function setRestrictionGroup($name, $propertyValue)
  {
    // TODO: Implement setRestrictionGroup() method.
  }

  public function setRestrictionUser($name, $propertyValue)
  {
    // TODO: Implement setRestrictionUser() method.
  }

  public function changeType($name, $type)
  {
    // TODO: Implement changeType() method.
  }

  public function addVariant($name, $variant)
  {
    // TODO: Implement addVariant() method.
  }

  public function structureDeleteItem($name)
  {
    $this->structureAdapter->removeItem($name);
    return $this;
  }

  /**
   * Load structure
   *
   * @return SettingsManager
   */
  public function structureload()
  {
    $this->structureAdapter->load();
    return $this;
  }

  public function structureSave()
  {
    $this->structureAdapter->save();
    return $this;
  }

  public function groupIsChanged()
  {
    // TODO: Implement groupIsChanged() method.
  }

  /**
   * Check if settings exists in all/single group
   *
   * @param string $name
   * @param null $groupID
   * @return bool
   * @throws PropertyNotExistException
   */
  public function groupHaveItem($name, $groupID = null)
  {
    $haveItem = false;
    if ($groupID === null) {
      foreach ($this->groupSettingsAdapters as $adapter) {
        if ($adapter->haveItem($name)) {
          $haveItem = true;
          break;
        }
      }
    } else {
      if (!isset($this->groupSettingsAdapters[$groupID])) {
        throw new PropertyNotExistException(null, $groupID);
      }
      $haveItem = $this->groupSettingsAdapters[$groupID]->haveItem($name);
    }
    return $haveItem;
  }

  function groupAddValue($name, $value, $groupID)
  {
    if (!isset($this->groupSettingsAdapters[$groupID])) {
      throw new PropertyNotExistException("Group named %PARAM% not found", $name);
    }
    if (!$this->haveItem($name)) {
      throw new PropertyNotExistException("Setting named %PARAM% not found in structure", $name);
    }
    $this->groupSettingsAdapters[$groupID]->addItem($name, $value);
    return $this;
  }

  /**
   * Get value for group or default value
   *
   * @param string $name
   * @param string $groupID
   * @param bool $own (default: true)
   * @return mixed
   * @throws PropertyNotExistException
   */
  function groupGetValue($name, $groupID = null, $own = true)
  {
    if (!$this->haveItem($name)) {
      throw new PropertyNotExistException("Setting named %PARAM% not found in structure", $name);
    }
    if ($groupID) {
      // Search only value in group
      if ($this->groupHaveItem($name, $groupID)) {
        return $this->groupSettingsAdapters[$groupID]->getValue($name);
      } else {
        if ($own) {
          throw new PropertyNotExistException(null, $name);
        }
      }
    } else {
      $value = null;
      foreach ($this->groupSettingsAdapters as $adapter) {
        if ($adapter->haveItem($name)) $value = $adapter->getValue($name);
      }
      if ($value !== null) return $value;
    }
//    return $this->structureAdapter->getValue($name);
  }

  function groupGetValues($groupID = null)
  {
    $result = [];
    if ($groupID === null) {
      // Concatenated values for all groups
      foreach($this->groupSettingsAdapters as $adapter) {
        $result = array_merge($result, $adapter->getValues());
      }
    } else {
      // Single group values
      $result = $this->groupSettingsAdapters[$groupID]->getValues();
    }
    return $result;
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

  function groupGetNames($groupID)
  {
    $data = [];
    foreach ($this->groupSettingsAdapters as $adapter) {
      $tmp = $adapter->getNames();
      $tmp = array_flip($tmp);
      $data = array_merge($data, $tmp);
    }
    return $data;
  }

  function groupSetValue($name, $value, $groupID)
  {
    // @todo: Add check if group exists
    $this->groupSettingsAdapters[$groupID][$name] = $value;
  }

  function groupRemoveValue($name, $groupID = null)
  {
    if ($groupID) {
      $this->groupSettingsAdapters[$groupID]->removeItem($name);
    } else {
      foreach($this->groupSettingsAdapters as $adapter) {
        $adapter->removeItem($name);
      }
    }
  }


  function groupLoad($groupID = null)
  {
    foreach($this->groupSettingsAdapters as $adapter) {
      $adapter->load();
    }
    return $this;
  }

  /**
   * Save all or selected group
   *
   * @param string $groupID
   * @return SettingsManager
   */
  function groupSave($groupID = null)
  {
    if ($groupID) {
      $this->groupSettingsAdapters[$groupID]->save();
    } else {
      foreach ($this->groupSettingsAdapters as $adapter) {
        $adapter->save();
      }
    }
    return $this;
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
    if (!$this->haveItem($name)) {
      throw new PropertyNotExistException("Setting named %PARAM% not found in structure", $name);
    }
    if ($this->userHaveItem($name)) return $this->userGetValue($name, true);
    foreach($this->groupSettingsAdapters as $adapter) {
      if ($adapter->haveItem($name)) return $adapter->getValue($name);
    }
    return $this->structureAdapter->getValue($name);
  }

  function getValues()
  {
    $arr = array_merge(
      $this->structureAdapter->getDefaultValues(),
      $this->groupGetValues(),
      $this->userGetValues(true)
    );
    return $arr;
  }

  function getValuesForPublic()
  {
    $arr = array_merge(
      $this->structureAdapter->getDefaultValuesForPublic(),
      $this->groupGetValuesForPublic(),
      $this->userGetValuesForPublic(true)
    );
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
    return
      $this
        ->structureSave()
        ->groupSave()
        ->userSave();
  }
}
