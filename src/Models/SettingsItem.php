<?php

namespace Garphild\SettingsManager\Models;

class SettingsItem {
  public const TYPE_BOOLEAN = "boolean";
  public const TYPE_STRING = "string";
  public const TYPE_NUMERIC = "numeric";
  public const TYPE_VARIANTS = "variants";

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

  function setTypeBoolean() {
    $this->dataType = self::TYPE_BOOLEAN;
    return $this;
  }

  function setTypeString() {
    $this->dataType = self::TYPE_STRING;
    return $this;
  }

  function setTypeNumber() {
    $this->dataType = self::TYPE_NUMERIC;
    return $this;
  }

  function setTypeVariants() {
    $this->dataType = self::TYPE_VARIANTS;
    return $this;
  }

  function addVariant($variant) {
    $this->values[] = $variant;
    return $this;
  }

  function disableApiReflect() {
    $this->showToApi = false;
    return $this;
  }

  function setSettingsGroup($group) {
    $this->settingsGroup = $group;
    return $this;
  }

  public function setDefaultValue($value) {
    $this->default = $value;
    return $this;
  }
}
