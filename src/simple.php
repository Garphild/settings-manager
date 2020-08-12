<?php

class A {
  public $value;

  function __construct() {
    $this->value = new B();
  }

  function getValue() {
    return $this->value;
  }
}

class B {
  public $data = 1;
}

$a = new A();
$a->getValue()->data = 2;
print_r($a);
