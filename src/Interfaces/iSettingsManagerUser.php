<?php

namespace Garphild\SettingsManager\Interfaces;

interface iSettingsManagerUser {
  /* Work with user */

  // Checks
  public function userIsChanged();
  public function userHaveItem($name);
  // CREATE
  function userAddValue($name, $value);
  // READ SINGLE
  function userGetValue($name, $own = false);
  // READ MULTIPLE
  function userGetValues(bool $own = false);
  function userGetValuesForPublic($own = false);
  function userGetNames();
  // UPDATE
  function userSetValue($name, $value);
  // DELETE
  function userRemoveValue($name);
  // LOAD
  function userLoad();
  // SAVE PERSISTENT
  function userSave();

}
