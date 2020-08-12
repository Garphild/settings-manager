<?php
//require_once("../vendor/autoload.php");
require_once("./DefaultTestCase.php");

use Garphild\SettingsManager\Adapters\JsonFileStructureAdapter;
use Garphild\SettingsManager\Errors\PropertyNotExistException;
use Garphild\SettingsManager\Models\SettingsItem;
use PHPUnit\Framework\TestCase;

class JsonFileStructureAdapterTest extends DefaultTestCase
{
  /**
   * Проверяем работу для несуществующего каталога и файла
   */
  function testCreateBadDirAndBadFile() {
    $this->expectException(Garphild\SettingsManager\Errors\MissingFileException::class);
    $erroredAdapter1 = new JsonFileStructureAdapter("./mmm", 'ddd');
  }

  /**
   * Проверяем работу с несуществующим файлом и правильным каталогом
   */
  function testCreateGoodDirAndBadFile() {
    $this->expectException(Garphild\SettingsManager\Errors\MissingFileException::class);
    $erroredAdapter1 = new JsonFileStructureAdapter($this->path, 'ddd');
  }

  /**
   * Проверяем загрузку пустого существующего файла
   */
  function testCreateGoodDirAndGoodEmptyFile() {
    $adapter1 = new JsonFileStructureAdapter($this->path, 'defaultEmpty.json');
    $data = $adapter1->getItems();
    $this->assertTrue(count($data) === 0 );
  }

  /**
   * Проверяем файл с единичной структурой настройки
   */
  public function testCreateGoodDirAndGoodSingleFile() {
    $adapter1 = new JsonFileStructureAdapter($this->path, 'defaultSingle.json');
    $this->assertTrue($adapter1->haveItem('testSingle'));
    $value = $adapter1->getItem('testSingle');
    $this->assertInstanceOf(SettingsItem::class, $value);
    $data = $adapter1->getItems();
    $this->assertSame(count($data), 1);
    $this->assertTrue(isset($data['testSingle']) );
    $this->assertInstanceOf(SettingsItem::class, $data['testSingle']);
  }

  /**
   * Тестирование проверки наличия настройки в структуре
   */
  public function testHaveItemSuccess() {
    $adapter1 = new JsonFileStructureAdapter($this->path, 'defaultSingle.json');
    $this->assertTrue($adapter1->haveItem('testSingle'));
    $adapter1 = new JsonFileStructureAdapter($this->path, 'defaultSingle.json');
    $this->assertFalse($adapter1->haveItem('testSingleNotExists'));
  }

  /**
   * Тестирование создания новой настройки.
   *
   * @throws \Garphild\SettingsManager\Errors\PropertyExistException
   */
  public function testCreateItem() {
    $adapter = new JsonFileStructureAdapter($this->path, 'defaultSingle.json');
    $this->assertFalse($adapter->haveItem('testItem'));
    $item = new SettingsItem();
    $item->setDefaultValue("5");
    $adapter->createItem("testItem", $item);
    $this->assertTrue($adapter->haveItem('testItem'));
    $this->assertSame("5", $adapter->getValue('testItem'));
  }

  /**
   * Тестирование попытки создать настройку с существующим именем
   *
   * @throws \Garphild\SettingsManager\Errors\PropertyExistException
   */
  public function testCreateItemFail() {
    $adapter = new JsonFileStructureAdapter($this->path, 'defaultSingle.json');
    $this->assertTrue($adapter->haveItem('testSingle'));
    $item = new SettingsItem();
    $this->expectException(Garphild\SettingsManager\Errors\PropertyExistException::class);
    $adapter->createItem("testSingle", $item);
    $this->assertTrue($adapter->haveItem("testSingle"));
  }

  /**
   * Тестирование удаления настройки
   */
  public function testRemoveItem() {
    $itemName = "testSingle";
    $adapter = new JsonFileStructureAdapter($this->path, 'defaultSingle.json');
    $this->assertTrue($adapter->haveItem($itemName));
    $this->assertSame(1, count($adapter->getItems()));
    $adapter->removeItem($itemName);
    $this->assertSame(0, count($adapter->getItems()));
    $this->assertFalse($adapter->haveItem($itemName));
  }

  /**
   * Тестирование получения дефолтного значения
   * @throws PropertyNotExistException
   */
  public function testGetDefaultValues() {
    $itemName = "testSingle";
    $adapter = new JsonFileStructureAdapter($this->path, 'defaultSingle.json');
    $values = $adapter->getDefaultValues();
    $this->assertSame("5", $values['testSingle']);
    $value = $adapter->getValue('testSingle');
    $this->assertSame("5", $value);
  }

  /**
   * Тестирование получения дефолтного значения для несуществующего значения
   * @throws PropertyNotExistException
   */
  public function testGetDefaultValuesFail() {
    $itemName = "testSingle";
    $adapter = new JsonFileStructureAdapter($this->path, 'defaultSingle.json');
    $this->expectException(PropertyNotExistException::class);
    $value = $adapter->getValue('testSingleNotExists');
  }

  /**
   * Тестирование сохранения настроек
   */
  public function testSave() {
    $itemName = "testNewSingle";
    if (file_exists("./mocks/{$itemName}.json")) unlink("./mocks/{$itemName}.json");
    copy("./mocks/defaultEmpty.json", "./mocks/{$itemName}.json");
    $adapter = new JsonFileStructureAdapter($this->path, "{$itemName}.json");
    $this->assertCount(0, $adapter->getItems());
    $item = new SettingsItem(['default' => 1]);
    $adapter->createItem($itemName, $item);
    $adapter->save();
    $this->assertEquals(
      trim(file_get_contents("./mocks/testNewSingleStructureEtalon.json")),
      trim(file_get_contents("./mocks/{$itemName}.json"))
    );
    unlink("./mocks/{$itemName}.json");
  }

  /**
   * Тестирование функции получения значения по дефолту
   * @throws PropertyNotExistException
   */
  public function testGetValue() {
    $itemName = "testSingle";
    $filename = 'defaultSingle';
    $adapter = new JsonFileStructureAdapter($this->path, "{$filename}.json");
    $this->assertSame("5", $adapter->getValue($itemName));
  }

  /**
   * Проверка возникновения исключения при попытке получить значение не существующей настройки
   * @throws PropertyNotExistException
   */
  public function testGetValueFail() {
    $itemName = "testSingleNotExists";
    $filename = 'defaultSingle';
    $adapter = new JsonFileStructureAdapter($this->path, "{$filename}.json");
    $this->expectException(PropertyNotExistException::class);
    $adapter->getValue($itemName);
  }

  /**
   * Проверка получения полного списка настроек без значений
   */
  public function testGetItemNames() {
    $itemName = "testSingle";
    $filename = 'defaultWithApiRestriciton';
    $adapter = new JsonFileStructureAdapter($this->path, "{$filename}.json");
    $names = $adapter->getItemNames();
    $this->assertNotSame(['testSingle'], $names);
    $this->assertSame(['testSingle', 'testSingleRestricted'], $names);
  }

  /**
   * Проверка получения списка настроек для публичного показа
   */
  public function testGetItemNamesForApi() {
    $itemName = "testSingle";
    $filename = 'defaultWithApiRestriciton';
    $adapter = new JsonFileStructureAdapter($this->path, "{$filename}.json");
    $names = $adapter->getItemNamesForPublic();
    $this->assertSame(['testSingle'], $names);
    $this->assertNotSame(['testSingle', 'testSingleRestricted'], $names);
  }

  /**
   * Получение списка значений для публичного показа
   */
  public function testGetDefaultValuesForApi() {
    $itemName = "testSingle";
    $filename = 'defaultWithApiRestriciton';
    $adapter = new JsonFileStructureAdapter($this->path, "{$filename}.json");
    $this->assertTrue($adapter->haveItem('testSingleRestricted'));
    $this->assertTrue($adapter->haveItem('testSingle'));
    $this->assertTrue($adapter->isPublic('testSingle'));
    $this->assertFalse($adapter->isPublic('testSingleRestricted'));
    $names = $adapter->getDefaultValuesForPublic();
    $this->assertSame(['testSingle' => '0'], $names);
    $this->assertNotSame(['testSingle' => '0', 'testSingleRestricted' => '1'], $names);
  }

  /**
   * Проверка определения публичности настройки
   */
  public function testChecks() {
    $itemName = "testSingle";
    $filename = 'defaultWithApiRestriciton';
    $adapter = new JsonFileStructureAdapter($this->path, "{$filename}.json");
    $this->assertTrue($adapter->isPublic('testSingle'));
    $this->assertFalse($adapter->isPublic('testSingleRestricted'));
  }

  /**
   * Проверка определения публичности несуществующей настройки
   * Должно выбрасываться исключение
   */
  public function testChecksNotExists() {
    $itemName = "testSingle";
    $filename = 'defaultWithApiRestriciton';
    $adapter = new JsonFileStructureAdapter($this->path, "{$filename}.json");
    $this->expectException(Garphild\SettingsManager\Errors\PropertyNotExistException::class);
    $this->assertFalse($adapter->isPublic('testSingleNotExists'));
  }

  /**
   * Проверка Изменения статуса публичности настройки
   */
  public function testChangeToPublic() {
    $itemName = "testSingle";
    $filename = 'defaultWithApiRestriciton';
    $adapter = new JsonFileStructureAdapter($this->path, "{$filename}.json");
    $this->assertFalse($adapter->isPublic('testSingleRestricted'));
    $adapter->makePublic('testSingleRestricted');
    $this->assertTrue($adapter->isPublic('testSingleRestricted'));
    $names = $adapter->getDefaultValuesForPublic();
    $this->assertSame(['testSingle' => '0', 'testSingleRestricted' => '1'], $names);
    $this->assertTrue($adapter->isPublic('testSingle'));
    $adapter->makePrivate('testSingle');
    $this->assertFalse($adapter->isPublic('testSingle'));
    $names = $adapter->getDefaultValuesForPublic();
    $this->assertSame(['testSingleRestricted' => '1'], $names);
  }

  /**
   * Проверка Изменения статуса публичности настройки
   */
  public function testLoadData() {
    $itemName = "testSingle";
    $filename = 'defaultWithApiRestriciton';
    $adapter = new JsonFileStructureAdapter($this->path, "{$filename}.json");
    $this->assertFalse($adapter->isPublic('testSingleRestricted'));
    $adapter->makePublic('testSingleRestricted');
    $this->assertTrue($adapter->isPublic('testSingleRestricted'));
    $adapter->load();
    $this->assertFalse($adapter->isPublic('testSingleRestricted'));

  }
  /**
   * Проверка Изменения значения настройки
   */
  public function testSetValue() {
    $itemName = "testSingle";
    $filename = 'defaultSingle';
    $adapter = new JsonFileStructureAdapter($this->path, "{$filename}.json");
    $this->assertSame("5", $adapter->getValue('testSingle'));
    $adapter->setValue('testSingle', "10");
    $this->assertSame("10", $adapter->getValue('testSingle'));
  }
}
