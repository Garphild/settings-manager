<?php

namespace Garphild\SettingsManager\Interfaces;

interface iSettingsManagerGroups {
  /* Work with single group */

  // Checks
  public function groupIsChanged();
  public function groupHaveItem($name, $groupID = null);
  // CREATE
  function groupAddValue($name, $value, $groupID);
  // READ SINGLE
  function groupGetValue($name, $groupID = null, $own = false);
  // READ MULTIPLE
  function groupGetValues($groupID = null);
  function groupGetValuesForPublic($groupID = null);
  function groupGetNames($groupID);
  // UPDATE
  function groupSetValue($name, $value, $groupID);
  // DELETE
  function groupRemoveValue($name, $groupID);
  // LOAD
  function groupLoad($groupID = null);
  // SAVE PERSISTENT
  function groupSave($groupID = null);
}
