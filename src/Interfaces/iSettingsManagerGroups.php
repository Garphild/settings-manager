<?php

namespace Garphild\SettingsManager\Interfaces;

interface iSettingsManagerGroups {
  /* Work with single group */

  function groupGetValuesForPublic($groupID = null);
  function groupGetNamesForPublic($groupID);
}
