<?php
require_once("./DefaultTestCase.php");

use Garphild\SettingsManager\Adapters\JsonFileStructureAdapter;
use Garphild\SettingsManager\SettingsManager;
use PHPUnit\Framework\TestCase;
use Garphild\SettingsManager\Adapters\PdoSettingsAdapter;
use Garphild\SettingsManager\Errors\PropertyNotDescriptedInStructureException;
use Garphild\SettingsManager\Errors\PropertyNotExistException as PropertyNotExistExceptionAlias;
use Garphild\SettingsManager\Models\SettingsItem;

class PdoSettingsAdapterTest extends TestCase
{
  public $path = './mocks';
  public $structureFileName = 'defaultMultiple.json';
  public $pdo;
  public $adapter;
  /**
   * @var SettingsManager
   */
  private $manager;
  /**
   * @var PdoSettingsAdapter
   */
  private $userAdapter;
  /**
   * @var JsonFileStructureAdapter
   */
  private $structureAdapter;

  function setUp(): void
  {
    parent::setUp();
    $this->pdo = new \PDO("sqlite:./mocks/test.sqlite");
    $sql = "DROP TABLE IF EXISTS settings";
    $this->pdo->exec($sql);
    $sql = <<<EOT
create table settings
(
	ID INTEGER PRIMARY KEY AUTOINCREMENT,
  userID bigint default 0 null,
	name varchar(255) not null,
	value varchar(255) not null,
	constraint userID_2
		unique (userID, name)
);
EOT;
    $this->pdo->exec($sql);
    $sql = "INSERT INTO settings (userID, name, value) VALUES (1, 'testSingle', '2')";
    $this->pdo->exec($sql);
    $sql = "INSERT INTO settings (userID, name, value) VALUES (0, 'testSingle', '1')";
    $this->pdo->exec($sql);

    $this->structureAdapter = new JsonFileStructureAdapter($this->path, $this->structureFileName);
//    $this->groupAdapter = new JsonFileSettingsAdapter($this->path, $this->groupFileName);
    $this->userAdapter = new PdoSettingsAdapter($this->pdo, 'settings', 'userID', 1);
    $this->userAdapter2 = new PdoSettingsAdapter($this->pdo, 'settings', 'userID', 2);
    $this->adapter = new PdoSettingsAdapter($this->pdo, 'settings', 'userID', 0);
    $this->manager = new SettingsManager(
      'default',
      $this->structureAdapter,
      [0 => $this->adapter],
      $this->userAdapter
    );
    $this->manager2 = new SettingsManager(
      'default',
      $this->structureAdapter,
      [0 => $this->adapter],
      $this->userAdapter2
    );
  }

  function testConnection() {
    $t = true;
    $this->assertTrue($t);
  }

  function testGetEmpty() {
    $this->adapter = new PdoSettingsAdapter($this->pdo, 'settings', 'userID', 0);
    $result = $this->userAdapter2->getNames();
    $this->assertEmpty($result);
  }

  function testNotEmpty() {
    $this->adapter = new PdoSettingsAdapter($this->pdo, 'settings', 'userID', 0);
    $result = $this->adapter->getNames();
    $this->assertEquals($result, ['testSingle']);
    $result = $this->adapter->getValues();
    $this->assertEquals($result, ['testSingle' => '1']);
  }
  function testManager() {
    $this->adapter = new PdoSettingsAdapter($this->pdo, 'settings', 'userID', 0);
    $res = $this->manager->getValues();
    $this->assertEquals($res, [
      'testSingle' => 2,
      'testSingleForGroup' => 0,
      'testSingleForUser' => 0,
      'testSingleForUserAdd' => 0,
      'testSingleForGroupAdd' => 0,
      'testSingleOnlyStructure' => 0,
    ]);
    $res = $this->manager->getValuesForPublic();
    $this->assertEquals($res, [
      'testSingleForGroup' => 0,
      'testSingleForUser' => 0,
      'testSingleForUserAdd' => 0,
      'testSingleForGroupAdd' => 0,
      'testSingleOnlyStructure' => 0,
    ]);
  }
  function testManager2() {
    $res = $this->manager2->getValues();
    $this->assertEquals($res, [
      'testSingle' => 1,
      'testSingleForGroup' => 0,
      'testSingleForUser' => 0,
      'testSingleForUserAdd' => 0,
      'testSingleForGroupAdd' => 0,
      'testSingleOnlyStructure' => 0,
    ]);
    $res = $this->manager->getValuesForPublic();
    $this->assertEquals($res, [
      'testSingleForGroup' => 0,
      'testSingleForUser' => 0,
      'testSingleForUserAdd' => 0,
      'testSingleForGroupAdd' => 0,
      'testSingleOnlyStructure' => 0,
    ]);
  }
  function testManagerSetValue() {
    $this->manager->user()->setValue('testSingle', "3");
    $val = $this->manager->getValue('testSingle');
    $this->assertEquals($val, "3");
    $this->manager->save();
  }
}
