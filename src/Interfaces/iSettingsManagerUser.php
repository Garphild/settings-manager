<?php

namespace Garphild\SettingsManager\Interfaces;

interface iSettingsManagerUser {
  function userGetValuesForPublic($own = false);
  function userIsValuePublic($name);
}
