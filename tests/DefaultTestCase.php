<?php

require_once("../vendor/autoload.php");

use PHPUnit\Framework\TestCase;

class DefaultTestCase extends TestCase {
  public $path = './mocks';

  public function testMockFilesIntegrity() {
    // Тестируем целостность файлов, которые нужны для тестирования.
    $this->assertFileExists($this->path.'/defaultEmpty.json');
    $this->assertFileExists($this->path.'/defaultSingle.json');
    $this->assertFileExists($this->path.'/defaultSingleSettings.json');
    $this->assertFileExists($this->path.'/defaultEmptySettings.json');
    $this->assertFileExists($this->path.'/testNewSingleEtalon.json');
    $this->assertFileDoesNotExist($this->path.'/newEmptySettings.json');
    $this->assertFileDoesNotExist($this->path.'/testNewSingle.json');
  }
}
