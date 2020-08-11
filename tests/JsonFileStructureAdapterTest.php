<?php
require("../vendor/autoload.php");

use Garphild\SettingsManager\Adapters\JsonFileStructureAdapter;
use Garphild\SettingsManager\Errors\PropertyNotExistException;
use Garphild\SettingsManager\Models\SettingsItem;
use PHPUnit\Framework\TestCase;

class JsonFileStructureAdapterTest extends TestCase
{
  function testCreateBadDirAndBadFile() {
    $this->expectException(Garphild\SettingsManager\Errors\MissingFileException::class);
    $erroredAdapter1 = new JsonFileStructureAdapter("./mmm", 'ddd');
  }
  function testCreateGoodDirAndBadFile() {
    $this->expectException(Garphild\SettingsManager\Errors\MissingFileException::class);
    $erroredAdapter1 = new JsonFileStructureAdapter("./mocks", 'ddd');
  }
  function testCreateGoodDirAndGoodEmptyFile() {
    $adapter1 = new JsonFileStructureAdapter(__DIR__."/mocks", 'defaultEmpty.json');
    $data = $adapter1->getItems();
    $this->assertTrue(count($data) === 0 );
  }
  public function testCreateGoodDirAndGoodSingleFile() {
    $adapter1 = new JsonFileStructureAdapter("./mocks", 'defaultSingle.json');
    $data = $adapter1->getItems();
    $this->assertSame(count($data), 1);
    $this->assertTrue(isset($data['testSingle']) );
    $this->assertInstanceOf(SettingsItem::class, $data['testSingle']);
  }
  public function testHaveItemSuccess() {
    $adapter1 = new JsonFileStructureAdapter("./mocks", 'defaultSingle.json');
    $this->assertTrue($adapter1->haveItem('testSingle'));
  }
  public function testHaveItemFail() {
    $adapter1 = new JsonFileStructureAdapter("./mocks", 'defaultSingle.json');
    $this->assertFalse($adapter1->haveItem('testSingleFail'));
  }
  public function testCreateItem() {
    $adapter = new JsonFileStructureAdapter("./mocks", 'defaultSingle.json');
    $this->assertFalse($adapter->haveItem('testItem'));
    $item = new SettingsItem();
    $adapter->createItem("testItem", $item);
    $this->assertTrue($adapter->haveItem('testItem'));
  }
  public function testCreateItemFail() {
    $adapter = new JsonFileStructureAdapter("./mocks", 'defaultSingle.json');
    $this->assertTrue($adapter->haveItem('testSingle'));
    $item = new SettingsItem();
    $this->expectException(Garphild\SettingsManager\Errors\PropertyExistException::class);
    $adapter->createItem("testSingle", $item);
  }
  public function testRemoveItem() {
    $itemName = "testSingle";
    $adapter = new JsonFileStructureAdapter("./mocks", 'defaultSingle.json');
    $this->assertTrue($adapter->haveItem($itemName));
    $this->assertSame(1, count($adapter->getItems()));
    $adapter->removeItem($itemName);
    $this->assertSame(0, count($adapter->getItems()));
    $this->assertFalse($adapter->haveItem($itemName));
  }
  public function testGetDefaultValues() {
    $itemName = "testSingle";
    $adapter = new JsonFileStructureAdapter("./mocks", 'defaultSingle.json');
    $values = $adapter->getDefaultValues();
    $this->assertSame("0", $values['testSingle']);
  }
  public function testSave() {
    $itemName = "testNewSingle";
    if (file_exists("./mocks/{$itemName}.json")) unlink("./mocks/{$itemName}.json");
    copy("./mocks/defaultEmpty.json", "./mocks/{$itemName}.json");
    $adapter = new JsonFileStructureAdapter("./mocks", "{$itemName}.json");
    $this->assertCount(0, $adapter->getItems());
    $item = new SettingsItem(['default' => 1]);
    $adapter->createItem($itemName, $item);
    $adapter->save();
    $this->assertFileEquals(
      "./mocks/testNewSingleEtalon.json",
      "./mocks/{$itemName}.json"
    );
//    $this->assertEquals(
//      trim(file_get_contents("./mocks/testNewSingleEtalon.json")),
//      trim(file_get_contents("./mocks/{$itemName}.json"))
//    );
  }
  public function testGetValue() {
    $itemName = "testSingle";
    $filename = 'defaultSingle';
    $adapter = new JsonFileStructureAdapter("./mocks", "{$filename}.json");
    $this->assertSame("0", $adapter->getValue($itemName));
  }
  public function testGetValueFail() {
    $itemName = "testSingleFail";
    $filename = 'defaultSingle';
    $adapter = new JsonFileStructureAdapter("./mocks", "{$filename}.json");
    $this->expectException(PropertyNotExistException::class);
    $adapter->getValue($itemName);
  }
  public function testGetItemNames() {
    $itemName = "testSingle";
    $filename = 'defaultWithApiRestriciton';
    $adapter = new JsonFileStructureAdapter("./mocks", "{$filename}.json");
    $names = $adapter->getItemNames();
    $this->assertNotSame(['testSingle'], $names);
    $this->assertSame(['testSingle', 'testSingleRestricted'], $names);
  }
  public function testGetItemNamesForApi() {
    $itemName = "testSingle";
    $filename = 'defaultWithApiRestriciton';
    $adapter = new JsonFileStructureAdapter("./mocks", "{$filename}.json");
    $names = $adapter->getItemNamesForPublic();
    $this->assertSame(['testSingle'], $names);
    $this->assertNotSame(['testSingle', 'testSingleRestricted'], $names);
  }
  public function testGetDefaultValuesForApi() {
    $itemName = "testSingle";
    $filename = 'defaultWithApiRestriciton';
    $adapter = new JsonFileStructureAdapter("./mocks", "{$filename}.json");
    $names = $adapter->getDefaultValuesForPublic();
    $this->assertSame(['testSingle' => '0'], $names);
    $this->assertNotSame(['testSingle' => '0', 'testSingleRestricted' => '1'], $names);
  }

  public function testChecks() {
    $itemName = "testSingle";
    $filename = 'defaultWithApiRestriciton';
    $adapter = new JsonFileStructureAdapter("./mocks", "{$filename}.json");
    $public = $adapter->isPublic('testSingle');
    $this->assertTrue($public);
    $public = $adapter->isPublic('testSingleRestricted');
    $this->assertFalse($public);
  }
}
