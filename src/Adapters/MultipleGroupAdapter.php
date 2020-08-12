<?php

namespace Garphild\SettingsManager\Adapters;

use Garphild\SettingsManager\Errors\DontChangeValueInAllGroupsException;
use Garphild\SettingsManager\Errors\ItemNotAdapterException;
use Garphild\SettingsManager\Errors\NoAdapterException;
use Garphild\SettingsManager\Errors\PropertyNotExistInGroupException;
use Garphild\SettingsManager\Interfaces\iSettingsAdapter;
use Garphild\SettingsManager\Interfaces\iStructureAdapter;
use phpDocumentor\Reflection\Types\Mixed_;

class MultipleGroupAdapter implements iSettingsAdapter {
  /**
   * @var iSettingsAdapter[]
   */
  protected $groups = [];
  /**
   * @var iStructureAdapter
   */
  protected $structure;

  /**
   * Throw exception if adapter not exists or not an instance of iSettingsAdapter
   *
   * @param string $groupID
   * @throws ItemNotAdapterException
   * @throws NoAdapterException
   */
  protected function throwIfAdapterNotExists($groupID): bool {
    if (!isset($this->groups[$groupID])) {
      throw new NoAdapterException(null, $groupID);
    }
    if (!($this->groups[$groupID] instanceof iSettingsAdapter)) {
      throw new ItemNotAdapterException(null, $groupID);
    }
    return true;
  }

  /**
   * Throw exception if adapter already exists
   *
   * @param string $groupID
   * @throws ItemNotAdapterException
   * @throws NoAdapterException
   */
  protected function throwIfAdapterExists($groupID): bool {
    if (isset($this->groups[$groupID])) {
      throw new AdapterExistsException(null, $groupID);
    }
    return true;
  }


  /**
   * Add adapter to groups list
   *
   * @param string $groupID
   * @param iSettingsAdapter $adapter
   * @return MultipleGroupAdapter
   * @throws ItemNotAdapterException
   * @throws NoAdapterException
   */
  function addGroupAdapter($groupID, iSettingsAdapter $adapter): MultipleGroupAdapter {
    $this->throwIfAdapterExists($groupID);
    if (!($adapter instanceof iSettingsAdapter)) {
      throw new ItemNotAdapterException(null, $groupID."/".get_class($adapter));
    }
    $this->groups[$groupID] = $adapter;
    if($this->structure) {
      $this->groups[$groupID]->injectStructure($this->structure);
    }
    return $this;
  }

  /**
   * Check if adapter exists
   *
   * @param string $groupID
   * @return bool
   */
  function isGroupAdapterExists($groupID): bool {
    return isset($this->groups[$groupID]);
  }

  /**
   * Get adapter
   *
   * @param string $groupID
   * @return iSettingsAdapter
   * @throws ItemNotAdapterException
   * @throws NoAdapterException
   */
  function getGroupAdapter($groupID): iSettingsAdapter {
    $this->throwIfAdapterNotExists($groupID);
    return $this->groups[$groupID];
  }

  /**
   * Remove adapter from list by name
   *
   * @param string $groupID
   * @return MultipleGroupAdapter
   * @throws ItemNotAdapterException
   * @throws NoAdapterException
   */
  function removeGroupAdapter($groupID): MultipleGroupAdapter {
    $this->throwIfAdapterNotExists($groupID);
    unset($this->groups[$groupID]);
    return $this;
  }

  /**
   * Replace existing adapter by another
   *
   * @param string $groupID
   * @param iSettingsAdapter $adapter
   * @return MultipleGroupAdapter
   * @throws ItemNotAdapterException
   * @throws NoAdapterException
   */
  function replaceGroupAdapter($groupID, iSettingsAdapter $adapter): MultipleGroupAdapter {
    $this->throwIfAdapterNotExists($groupID);
    if (!($adapter instanceof iSettingsAdapter)) {
      throw new ItemNotAdapterException(null, $groupID."/".get_class($adapter));
    }
    $this->groups[$groupID] = $adapter;
    $this->groups[$groupID]->injectStructure($this->structure);
    return $this;
  }

  /**
   * Load settings for single group or all groups
   *
   * @param string $groupID
   * @return MultipleGroupAdapter
   * @throws ItemNotAdapterException
   * @throws NoAdapterException
   */
  public function load($groupID = null): MultipleGroupAdapter
  {
    if ($groupID !== null) {
      $this->throwIfAdapterNotExists($groupID);
      $this->groups[$groupID]->load();
    } else {
      foreach ($this->groups as $gID => $adapter) {
        $this->throwIfAdapterNotExists($gID);
        $adapter->load();
      }
    }
    return $this;
  }

  /**
   * Save data for all or single adapters
   *
   * @param string $groupID
   * @return MultipleGroupAdapter
   * @throws ItemNotAdapterException
   * @throws NoAdapterException
   */
  public function save($groupID = null): MultipleGroupAdapter
  {
    if ($groupID !== null) {
      $this->throwIfAdapterNotExists($groupID);
      $this->groups[$groupID]->save();
    } else {
      foreach ($this->groups as $gID => $adapter) {
        $this->throwIfAdapterNotExists($gID);
        $adapter->save();
      }
    }
    return $this;
  }

  /**
   * Check if item exists in defined group or in one of current groups
   *
   * @param string $name
   * @param string $groupID
   * @return bool
   * @throws ItemNotAdapterException
   * @throws NoAdapterException
   */
  public function haveItem($name, $groupID = null): bool
  {
    $have = false;
    if ($groupID) {
      $this->throwIfAdapterNotExists($groupID);
      $have = $this->groups[$groupID]->haveItem($name);
    } else {
      foreach ($this->groups as $gID => $adapter) {
        $this->throwIfAdapterNotExists($gID);
        if ($adapter->haveItem($name)) {
          $have = true;
          break;
        }
      }
    }
    return $have;
  }

  /**
   * Check if defined group or any group is changed
   *
   * @param string $groupID
   * @return bool
   * @throws ItemNotAdapterException
   * @throws NoAdapterException
   */
  public function isChanged($groupID = null): bool
  {
    $changed = false;
    if ($groupID) {
      $this->throwIfAdapterNotExists($groupID);
      $changed = $this->groups[$groupID]->isChanged();
    } else {
      foreach ($this->groups as $gID => $adapter) {
        $this->throwIfAdapterNotExists($gID);
        if ($adapter->isChanged()) {
          $changed = true;
          break;
        }
      }
    }
    return $changed;
  }

  /**
   * Remove item from specified group or from all groups
   *
   * @param string $name
   * @param string $groupID
   * @return MultipleGroupAdapter
   * @throws ItemNotAdapterException
   * @throws NoAdapterException
   */
  public function removeItem($name, $groupID = null): MultipleGroupAdapter
  {
    if ($groupID) {
      $this->throwIfAdapterNotExists($groupID);
      if ($this->groups[$groupID]->haveItem($name)) {
        $this->groups[$groupID]->removeItem($name);
      }
    } else {
      foreach ($this->groups as $gID => $adapter) {
        $this->throwIfAdapterNotExists($gID);
        if ($adapter->haveItem($name)) {
          $adapter->removeItem($name);
        }
      }
    }
    return $this;
  }

  /**
   * Set value in group.
   * Don't change value in multiple groups! This is very dangerous.
   *
   * @param string $name
   * @param mixed $value
   * @param string|null $groupID
   * @return MultipleGroupAdapter
   * @throws DontChangeValueInAllGroupsException
   * @throws ItemNotAdapterException
   * @throws NoAdapterException
   */
  public function setValue($name, $value, $groupID = null): MultipleGroupAdapter
  {
    if ($groupID) {
      $this->throwIfAdapterNotExists($groupID);
      $this->groups[$groupID]->setValue($name, $value);
    } else {
      throw new DontChangeValueInAllGroupsException();
    }
    return $this;
  }

  /**
   * Get values from one group or concatenated result for all groups
   *
   * @param string $groupID
   * @return array
   * @throws ItemNotAdapterException
   * @throws NoAdapterException
   */
  public function getValues($groupID = null): array
  {
    if ($groupID) {
      $this->throwIfAdapterNotExists($groupID);
      return $this->groups[$groupID]->getValues();
    } else {
      $tmp = [];
      foreach ($this->groups as $gID => $adapter) {
        $this->throwIfAdapterNotExists($gID);
        $tmp = array_merge($tmp, $adapter->getValues());
      }
      return $tmp;
    }
  }

  /**
   * Get item names from specified group or concatenated from all groups
   *
   * @param string|null $groupID
   * @return array
   * @throws ItemNotAdapterException
   * @throws NoAdapterException
   */
  public function getNames($groupID = null): array
  {
    if ($groupID) {
      $this->throwIfAdapterNotExists($groupID);
      return $this->groups[$groupID]->getNames();
    } else {
      $tmp = [];
      foreach ($this->groups as $gID => $adapter) {
        $this->throwIfAdapterNotExists($gID);
        $tmp = array_merge($tmp, array_flip($adapter->getValues()));
      }
      return array_keys($tmp);
    }
  }

  /**
   * Get a single value for secified group or value for last group in list
   *
   * @param string $name
   * @param string|null $groupID
   * @return mixed
   * @throws ItemNotAdapterException
   * @throws NoAdapterException
   * @throws PropertyNotExistInGroupException
   */
  public function getValue($name, $groupID = null)
  {
    if ($groupID) {
      $this->throwIfAdapterNotExists($groupID);
      if (!$this->haveItem($name, $groupID)) {
        throw new PropertyNotExistInGroupException(null, $groupID.".".$name);
      }
      return $this->groups[$groupID]->getValue($name);
    } else {
      $tmp = null;
      foreach (array_reverse($this->groups) as $gID => $adapter) {
        $this->throwIfAdapterNotExists($gID);
        if ($adapter->haveItem($name)) {
          return $adapter->getValue($name);
        }
      }
      throw new PropertyNotExistInGroupException(null, "[All groups].".$name);
    }
  }

  public function injectStructure(iStructureAdapter $structureAdapter)
  {
    $this->structure = $structureAdapter;
    if (count($this->groups) > 0) {
      foreach ($this->groups as $adapter) {
        $adapter->injectStructure($structureAdapter);
      }
    }
  }
}
