Most simple initialization.
```
use \Garphild\SettingsManager\SettingsManager;
...
$structureAdapter = new JsonFileStructureAdapter("./config", "defaultStructure.json");
$manager = new SettingsManager(
    'default',
    $structureAdapter
);
```
