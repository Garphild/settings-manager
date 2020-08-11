<?php
require("../vendor/autoload.php");

use Garphild\SettingsManager\Adapters\JsonFileSettingsAdapter;
use Garphild\SettingsManager\Adapters\JsonFileStructureAdapter;
use Garphild\SettingsManager\SettingsManager;
use PHPUnit\Framework\TestCase;

echo "\n\nSettingsManagerTest\n";

/**
 * @covers Garphild\SettingsManager\SettingsManager
 */
class SettingsManagerTest extends TestCase
{
//  public function template()
//  {
//    $path = './mocks';
//    $structureFileName = 'defaultSingle.json';
//    $groupFileName = 'defaultSingleSettingsGroup.json';
//    $userFileName = 'defaultSingleSettings.json';
//    $structureAdapter = new JsonFileStructureAdapter($path, $structureFileName);
//    $groupAdapter = new JsonFileSettingsAdapter($path, $groupFileName);
//    $userFileName = new JsonFileSettingsAdapter($path, $userFileName);
//    $manager = new SettingsManager(
//      'default',
//      $structureAdapter,
//      [],
//      null
//    );
//  }

  /**
   * @covers \Garphild\SettingsManager\SettingsManager::__construct
   * @covers \Garphild\SettingsManager\SettingsManager::userGetValues
   * @covers \Garphild\SettingsManager\SettingsManager::groupGetValues
   */
  public function testEmptyManager()
  {
    $path = './mocks';
    $structureFileName = 'defaultSingle.json';
    $structureAdapter = new JsonFileStructureAdapter($path, $structureFileName);
    $manager = new SettingsManager(
      'default',
      $structureAdapter,
      [],
      null
    );
    $this->assertInstanceOf(SettingsManager::class, $manager);
    $this->assertCount(
      0,
      $manager->userGetValues(true)
    );
    $this->assertCount(
      0,
      $manager->groupGetValues()
    );
    $values = $manager->userGetValues();
    $this->assertCount(1, $values);
    $this->assertSame(['testSingle' => "0"], $values);
  }

  /**
   * @covers \Garphild\SettingsManager\SettingsManager::__construct
   * @covers \Garphild\SettingsManager\SettingsManager::userGetValues
   * @covers \Garphild\SettingsManager\SettingsManager::groupGetValues
   */
  public function testGroup()
  {
    $path = './mocks';
    $structureFileName = 'defaultSingle.json';
    $groupFileName = 'defaultSingleSettingsGroup.json';
    $structureAdapter = new JsonFileStructureAdapter($path, $structureFileName);
    $groupAdapter = new JsonFileSettingsAdapter($path, $groupFileName);
    $manager = new SettingsManager(
      'default',
      $structureAdapter,
      [$groupAdapter],
      null
    );
    $this->assertCount(
      0,
      $manager->userGetValues(true)
    );
    $values = $manager->userGetValues();
    $this->assertCount(
      1,
      $values
    );
    $this->assertSame(['testSingle' => "1"], $values);
  }

  /**
   * @covers \Garphild\SettingsManager\SettingsManager::__construct
   * @covers \Garphild\SettingsManager\SettingsManager::userGetValues
   * @covers \Garphild\SettingsManager\SettingsManager::groupGetValues
   */
  public function testUserWithoutGroup()
  {
    $path = './mocks';
    $structureFileName = 'defaultSingle.json';
    $userFileName = 'defaultSingleSettings.json';
    $structureAdapter = new JsonFileStructureAdapter($path, $structureFileName);
    $userAdapter = new JsonFileSettingsAdapter($path, $userFileName);
    $manager = new SettingsManager(
      'default',
      $structureAdapter,
      [],
      $userAdapter
    );
    $ownValues = $manager->userGetValues(true);
    $this->assertCount(
      1,
      $ownValues
    );
    $this->assertSame(['testSingle' => "2"], $ownValues);
    $values = $manager->userGetValues();
    $this->assertCount(
      1,
      $values
    );
    $this->assertSame(['testSingle' => "2"], $values);
  }

  /**
   * @covers \Garphild\SettingsManager\SettingsManager::__construct
   * @covers \Garphild\SettingsManager\SettingsManager::userGetValues
   * @covers \Garphild\SettingsManager\SettingsManager::groupGetValues
   */
  public function testUserWithGroup()
  {
    $path = './mocks';
    $structureFileName = 'defaultSingle.json';
    $groupFileName = 'defaultSingleSettingsGroup.json';
    $userFileName = 'defaultSingleSettings.json';
    $structureAdapter = new JsonFileStructureAdapter($path, $structureFileName);
    $groupAdapter = new JsonFileSettingsAdapter($path, $groupFileName);
    $userAdapter = new JsonFileSettingsAdapter($path, $userFileName);
    $manager = new SettingsManager(
      'default',
      $structureAdapter,
      [$groupAdapter],
      $userAdapter
    );
    $ownValues = $manager->userGetValues(true);
    $this->assertCount(
      1,
      $ownValues
    );
    $this->assertSame(['testSingle' => "2"], $ownValues);
    $values = $manager->userGetValues();
    $this->assertCount(
      1,
      $values
    );
    $this->assertSame(['testSingle' => "2"], $values);
  }
}
