<?php
require("../vendor/autoload.php");

use Garphild\SettingsManager\Adapters\JsonFileSettingsAdapter;
use Garphild\SettingsManager\Adapters\JsonFileStructureAdapter;
use Garphild\SettingsManager\Errors\NoAdapterException;
use Garphild\SettingsManager\SettingsManager;
use PHPUnit\Framework\TestCase;

echo "\n\nSettingsManagerTest\n";

/**
 * @covers Garphild\SettingsManager\SettingsManager
 */
class SettingsManagerCheckItemsTest extends TestCase
{
  public $path = './mocks';
  public $structureFileName = 'defaultMultiple.json';
  public $groupFileName = 'defaultMultipleSettingsGroup.json';
  public $userFileName = 'defaultMultipleSettings.json';
  public $groupID = 'guest';
  public $structureAdapter;
  public $groupAdapter;
  public $userAdapter;
  public $manager;

  function setUp(): void
  {
    parent::setUp(); // TODO: Change the autogenerated stub
    $this->structureAdapter = new JsonFileStructureAdapter($this->path, $this->structureFileName);
    $this->groupAdapter = new JsonFileSettingsAdapter($this->path, $this->groupFileName);
    $this->userAdapter = new JsonFileSettingsAdapter($this->path, $this->userFileName);
    $this->manager = new SettingsManager(
    'default',
      $this->structureAdapter,
      [$this->groupID => $this->groupAdapter],
      $this->userAdapter
    );
  }


  /**
   * @covers \Garphild\SettingsManager\SettingsManager::__construct
   * @covers \Garphild\SettingsManager\SettingsManager::user
   */
  public function testUserHaveValue()
  {
    $haveValue = $this->manager->user()->haveItem('testSingle');
    $this->assertTrue($haveValue);
    $haveValue = $this->manager->user()->haveItem('testSingleForUser');
    $this->assertTrue($haveValue);
    $notHaveValue = $this->manager->user()->haveItem('testSingleForGroup');
    $this->assertFalse($notHaveValue);
  }

  /**
   * @covers \Garphild\SettingsManager\SettingsManager::__construct
   * @covers \Garphild\SettingsManager\SettingsManager::groups
   */
  public function testGroupHaveValue()
  {
    // Groups
    $haveValue = $this->manager->groups()->haveItem('testSingleForGroup');
    $this->assertTrue($haveValue);
    $haveValue = $this->manager->groups()->haveItem('testSingleForUser');
    $this->assertFalse($haveValue);
    $haveValue = $this->manager->groups()->haveItem('testSingle');
    $this->assertTrue($haveValue);
  }

  public function testIsPublic()
  {
    // Groups
    $haveValue = $this->manager->isPublic('testSingle');
    $this->assertFalse($haveValue);
    $haveValue = $this->manager->isPublic('testSingleForUser');
    $this->assertTrue($haveValue);
  }

  /**
   * @throws \Garphild\SettingsManager\Errors\PropertyNotExistException
   * @covers \Garphild\SettingsManager\SettingsManager::groups
   */
  function testExistsGroup() {
    // Group with exist ID
    $haveValue = $this->manager->groups()->haveItem('testSingleForGroup', $this->groupID);
    $this->assertTrue($haveValue);
    $haveValue = $this->manager->groups()->haveItem('testSingleForUser', $this->groupID);
    $this->assertFalse($haveValue);
    $haveValue = $this->manager->groups()->haveItem('testSingle', $this->groupID);
    $this->assertTrue($haveValue);
  }

  /**
   * @throws NoAdapterException
   * @covers \Garphild\SettingsManager\SettingsManager::groups
   */
  function testNotExistsGroup() {
    // Group with exist ID
    $this->expectException(NoAdapterException::class);
    $haveValue = $this->manager->groups()->haveItem('testSingleForUser', "absentGroup");
    $this->assertTrue($haveValue);
  }

  /**
   * @covers \Garphild\SettingsManager\SettingsManager::haveItem
   */
  function testStructureHaveItem() {
    $haveValue = $this->manager->haveItem('testSingle');
    $this->assertTrue($haveValue);
    $haveValue = $this->manager->haveItem('testSingleForGroup');
    $this->assertTrue($haveValue);
    $haveValue = $this->manager->haveItem('testSingleForUser');
    $this->assertTrue($haveValue);
    $haveValue = $this->manager->haveItem('testSingleNotExist');
    $this->assertFalse($haveValue);
  }
}
