<?php

namespace Garphild\SettingsManager\Models;

class SettingsItem {
  public $default = '0';
  public $dataType = "boolean";
  public $module = "";
  public $name = "";
  public $allowToApply = [
    "global" => true,
    "group" => true,
    "user" => true,
  ];
  public $values = [];
  public $allowUserChange = false;
  public $settingsGroup = "Main";
  public $showToApi = true;

  function __construct($item = null) {
    if ($item) {
      foreach($item as $n=>$v) $this->{$n} = $v;
    }
  }
}
