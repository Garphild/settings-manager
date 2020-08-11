<?php
require("../vendor/autoload.php");

use Garphild\SettingsManager\Adapters\JsonFileSettingsAdapter;
use Garphild\SettingsManager\Errors\PropertyNotExistException;
use Garphild\SettingsManager\Models\SettingsItem;
use PHPUnit\Framework\TestCase;

class JsonFileSettingsAdapterTest extends TestCase
{
  function testCreateBadDirAndBadFile() {
    $this->expectException(Garphild\SettingsManager\Errors\MissingFileException::class);
    $erroredAdapter1 = new JsonFileSettingsAdapter("./mmm", 'ddd');
  }
  function testCreateGoodDirAndBadFile() {
    $this->expectException(Garphild\SettingsManager\Errors\MissingFileException::class);
    $erroredAdapter1 = new JsonFileSettingsAdapter("./mocks", 'ddd');
  }
  function testCreateGoodDirAndGoodEmptyFile() {
    $adapter1 = new JsonFileSettingsAdapter("./mocks", 'defaultEmptySettings.json');
    $data = $adapter1->getValues();
    $this->assertTrue(count($data) === 0 );
  }
  public function testCreateGoodDirAndGoodSingleFile() {
    $adapter1 = new JsonFileSettingsAdapter("./mocks", 'defaultSingleSettings.json');
    $data = $adapter1->getValues();
    $this->assertSame(count($data), 1);
    $this->assertTrue(isset($data['testSingle']) );
    $this->assertSame("2", $data['testSingle'] );
  }
  public function testHaveItemSuccess() {
    $adapter1 = new JsonFileSettingsAdapter("./mocks", 'defaultSingleSettings.json');
    $this->assertTrue($adapter1->haveItem('testSingle'));
  }
  public function testHaveItemFail() {
    $adapter1 = new JsonFileSettingsAdapter("./mocks", 'defaultSingleSettings.json');
    $this->assertFalse($adapter1->haveItem('testSingleFail'));
  }
  public function testCreateItem() {
    $adapter = new JsonFileSettingsAdapter("./mocks", 'defaultSingleSettings.json');
    $this->assertFalse($adapter->haveItem('testItem'));
    $adapter->addItem("testItem", "3");
    $this->assertTrue($adapter->haveItem('testItem'));
    $this->assertSame("3", $adapter->getValue('testItem'));
  }
  public function testCreateItemFail() {
    $adapter = new JsonFileSettingsAdapter("./mocks", 'defaultSingleSettings.json');
    $this->assertTrue($adapter->haveItem('testSingle'));
    $this->expectException(Garphild\SettingsManager\Errors\PropertyExistException::class);
    $adapter->addItem("testSingle", "4");
  }
  public function testRemoveItem() {
    $itemName = "testSingle";
    $adapter = new JsonFileSettingsAdapter("./mocks", 'defaultSingleSettings.json');
    $this->assertTrue($adapter->haveItem($itemName));
    $this->assertCount(1, $adapter->getValues());
    $adapter->removeItem($itemName);
    $this->assertCount(0, $adapter->getValues());
    $this->assertFalse($adapter->haveItem($itemName));
  }
  public function testGetValues() {
    $itemName = "testSingle";
    $adapter = new JsonFileSettingsAdapter("./mocks", 'defaultSingleSettings.json');
    $values = $adapter->getValues();
    $this->assertSame("2", $values['testSingle']);
  }
//  public function testSave() {
//    $itemName = "testNewSingle";
//    if (file_exists("./mocks/{$itemName}.json")) unlink("./mocks/{$itemName}.json");
//    copy("./mocks/defaultEmpty.json", "./mocks/{$itemName}.json");
//    $adapter = new JsonFileStructureAdapter("./mocks", "{$itemName}.json");
//    $this->assertCount(0, $adapter->getValues());
//    $item = new SettingsItem(['default' => 1]);
//    $adapter->createItem($itemName, $item);
//    $adapter->save();
//    $this->assertFileEquals(
//      "./mocks/testNewSingleEtalon.json",
//      "./mocks/{$itemName}.json"
//    );
////    $this->assertEquals(
////      trim(file_get_contents("./mocks/testNewSingleEtalon.json")),
////      trim(file_get_contents("./mocks/{$itemName}.json"))
////    );
//  }
//  public function testGetValue() {
//    $itemName = "testSingle";
//    $filename = 'defaultSingle';
//    $adapter = new JsonFileStructureAdapter("./mocks", "{$filename}.json");
//    $this->assertSame("0", $adapter->getValue($itemName));
//  }
//  public function testGetValueFail() {
//    $itemName = "testSingleFail";
//    $filename = 'defaultSingle';
//    $adapter = new JsonFileStructureAdapter("./mocks", "{$filename}.json");
//    $this->expectException(PropertyNotExistException::class);
//    $adapter->getValue($itemName);
//  }
//  public function testGetItemNames() {
//    $itemName = "testSingle";
//    $filename = 'defaultWithApiRestriciton';
//    $adapter = new JsonFileStructureAdapter("./mocks", "{$filename}.json");
//    $names = $adapter->getItemNames();
//    $this->assertNotSame(['testSingle'], $names);
//    $this->assertSame(['testSingle', 'testSingleRestricted'], $names);
//  }
//  public function testGetItemNamesForApi() {
//    $itemName = "testSingle";
//    $filename = 'defaultWithApiRestriciton';
//    $adapter = new JsonFileStructureAdapter("./mocks", "{$filename}.json");
//    $names = $adapter->getItemNamesForApi();
//    $this->assertSame(['testSingle'], $names);
//    $this->assertNotSame(['testSingle', 'testSingleRestricted'], $names);
//  }
//  public function testGetDefaultValuesForApi() {
//    $itemName = "testSingle";
//    $filename = 'defaultWithApiRestriciton';
//    $adapter = new JsonFileStructureAdapter("./mocks", "{$filename}.json");
//    $names = $adapter->getDefaultValuesForApi();
//    $this->assertSame(['testSingle' => '0'], $names);
//    $this->assertNotSame(['testSingle' => '0', 'testSingleRestricted' => '1'], $names);
//  }
}
