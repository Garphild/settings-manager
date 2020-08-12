<?php
require_once("./DefaultTestCase.php");

use Garphild\SettingsManager\Adapters\JsonFileSettingsAdapter;
use Garphild\SettingsManager\Errors\PropertyNotDescriptedInStructureException;
use Garphild\SettingsManager\Errors\PropertyNotExistException as PropertyNotExistExceptionAlias;
use Garphild\SettingsManager\Models\SettingsItem;

class JsonFileSettingsAdapterTest extends DefaultTestCase
{
  public $path = './mocks';

  /**
   * Проверяется выброс исключения, если файл или каталог не существует
   */
  public function testCreateBadDirAndBadFile() {
    $this->expectException(Garphild\SettingsManager\Errors\MissingFileException::class);
    $erroredAdapter1 = new JsonFileSettingsAdapter("./mmm", 'ddd');
  }

  /**
   * Проверяется выброс исключения, если каталог есть, а файл не существует
   */
  public function testCreateGoodDirAndBadFile() {
    $this->expectException(Garphild\SettingsManager\Errors\MissingFileException::class);
    $erroredAdapter1 = new JsonFileSettingsAdapter("./mocks", 'ddd');
  }

  /**
   * Проверяется заргузка пустого существующего файла с данными.
   */
  public function testCreateGoodDirAndGoodEmptyFile() {
    $adapter1 = new JsonFileSettingsAdapter("./mocks", 'defaultEmptySettings.json');
    $data = $adapter1->getValues();
    $this->assertCount(0, $data );
  }

  /**
   * Проверяем режим создания файла.
   */
  public function testCreateGoodDirAndGoodFileCreating() {
    $filename = "./mocks/newEmptySettings.json";
    if (file_exists($filename)) unlink($filename);
    $this->assertFalse(file_exists($filename));
    $adapter1 = new JsonFileSettingsAdapter("./mocks", 'newEmptySettings.json', true);
    $this->assertTrue(file_exists($filename));
    $data = $adapter1->getValues();
    $this->assertCount(0, $data );
    unlink($filename);
  }

  /**
   * Загружаем простой тестовый файл с одной единственной настройкой.
   */
  public function testCreateGoodDirAndGoodSingleFile() {
    $defaultFileName = 'defaultSingleSettings.json';
    $adapter1 = new JsonFileSettingsAdapter("./mocks", $defaultFileName);
    $data = $adapter1->getValues();
    $this->assertCount(1, $data);
    $this->assertTrue(isset($data['testSingle']));
    $this->assertSame("2", $data['testSingle'] );
    $this->assertTrue($adapter1->haveItem('testSingle'));
    $this->assertFalse($adapter1->haveItem('testSingleNotExists'));
    $this->assertFalse($adapter1->isChanged());
    $adapter1->removeItem('testSingle');
    $this->assertCount(0, $adapter1->getValues());
    $adapter1->load();
    $this->assertCount(1, $adapter1->getValues());
    $this->assertSame("2", $adapter1->getValue("testSingle"));
    $adapter1->setValue("testSingle", "3");
    $this->assertCount(1, $adapter1->getValues());
    $this->assertSame("3", $adapter1->getValue("testSingle"));
    $this->assertSame(["testSingle"], $adapter1->getNames());
    $this->expectException(Garphild\SettingsManager\Errors\PropertyNotExistException::class);
    $adapter1->getValue('testSingleNotExists');
  }

  /**
   * Проверяем то, что значение есть в файле
   */
  public function testHaveItemSuccess() {
    $adapter1 = new JsonFileSettingsAdapter("./mocks", 'defaultSingleSettings.json');
    $this->assertTrue($adapter1->haveItem('testSingle'));
  }

  /**
   * Проверяем поведение на отсутствующее значение без проверки с использованием структуры
   */
  public function testHaveItemFail() {
    $adapter1 = new JsonFileSettingsAdapter("./mocks", 'defaultSingleSettings.json');
    $this->assertFalse($adapter1->haveItem('testSingleFail'));
  }

  /**
   * Проверяем добавление настройки без проверки с использованием структуры
   *
   * @throws PropertyNotExistExceptionAlias
   * @throws PropertyNotDescriptedInStructureException
   */
  public function testCreateItem() {
    $adapter = new JsonFileSettingsAdapter("./mocks", 'defaultSingleSettings.json');
    $this->assertFalse($adapter->haveItem('testItem'));
    $adapter->setValue("testItem", "3");
    $this->assertTrue($adapter->haveItem('testItem'));
    $this->assertSame("3", $adapter->getValue('testItem'));
  }

  /**
   * Проверяем поведение при попытке установить значение настройки без проверки структуры
   * Должно установиться нормально.
   *
   * @throws PropertyNotDescriptedInStructureException
   */
  public function testCreateItemFail() {
    $adapter = new JsonFileSettingsAdapter("./mocks", 'defaultSingleSettings.json');
    $this->assertTrue($adapter->haveItem('testSingle'));
    $adapter->setValue("testSingle", "4");
    $this->assertSame("4", $adapter->getValue("testSingle"));
  }

  /**
   * Проверяем удаление настройки
   */
  public function testRemoveItem() {
    $itemName = "testSingle";
    $adapter = new JsonFileSettingsAdapter("./mocks", 'defaultSingleSettings.json');
    $this->assertTrue($adapter->haveItem($itemName));
    $this->assertCount(1, $adapter->getValues());
    $adapter->removeItem($itemName);
    $this->assertCount(0, $adapter->getValues());
    $this->assertFalse($adapter->haveItem($itemName));
  }

  /**
   * Проверяем удаление настройки, которая не существует.
   * Должно пройти все нормально.
   */
  public function testRemoveItemNotExists() {
    $itemName = "testSingleNotExists";
    $adapter = new JsonFileSettingsAdapter("./mocks", 'defaultSingleSettings.json');
    $this->assertFalse($adapter->haveItem($itemName));
    $this->assertCount(1, $adapter->getValues());
    $adapter->removeItem($itemName);
    $this->assertCount(1, $adapter->getValues());
    $this->assertFalse($adapter->haveItem($itemName));
  }

  /**
   * Проверка того, как берутся значения
   */
  public function testGetValues() {
    $itemName = "testSingle";
    $adapter = new JsonFileSettingsAdapter("./mocks", 'defaultSingleSettings.json');
    $values = $adapter->getValues();
    $this->assertCount(1, $values);
    $this->assertArrayHasKey("testSingle", $values);
    $this->assertSame("2", $values['testSingle']);
  }

  /**
   * Тест сохранения файла
   *
   * @throws PropertyNotDescriptedInStructureException
   */
  public function testSave() {
    $itemName = "testNewSingle";
    $newFileName = "./mocks/{$itemName}.json";
    if (file_exists("./mocks/{$itemName}.json")) unlink("./mocks/{$itemName}.json");
    copy("./mocks/defaultEmpty.json", $newFileName);
    $adapter = new JsonFileSettingsAdapter("./mocks", "{$itemName}.json");
    $this->assertCount(0, $adapter->getValues());
    $adapter->setValue($itemName, "5");
    $adapter->save();
    $this->assertEquals(
      trim(file_get_contents("./mocks/testNewSingleEtalon.json")),
      trim(file_get_contents($newFileName))
    );
    unlink($newFileName);
  }
}
