<?php

namespace Garphild\SettingsManager\Adapters;

use Garphild\SettingsManager\Errors\MissingFileException;
use Garphild\SettingsManager\Interfaces\iSettingsAdapter;

class JsonFile {
  protected $basePath;
  protected $filename;
  protected $parsed = [];
  protected $file = '';
  protected $fileMustExists = true;

  function __construct($basePath, $structureFileName) {
    $this->filename = $structureFileName;
    $lastChar = substr($basePath, -1);
    if ($lastChar === '/' || $lastChar === '\\') {
      $basePath = substr($basePath, 0, -1);
    }
    $this->basePath = $basePath;
    if (!file_exists($this->basePath)) {
      throw new MissingFileException("Dir %PARAM% not exists", $this->basePath);
    }
    if (!is_dir($this->basePath)) {
      throw new MissingFileException("%PARAM% is not a folder", $this->basePath);
    }
    if ($this->fileMustExists && !file_exists($this->getFilename())) {
      throw new MissingFileException("File %PARAM% not exists", $this->getFilename());
    }
  }

  function loadFile() {
    $this->file = file_get_contents($this->getFilename());
    $this->parsed = json_decode($this->file, true, 99999999);
  }

  protected function getFilename()
  {
    return $this->basePath . "/" . $this->filename;
  }

  public function enableCreateFile()
  {
    $this->fileMustExists = false;
    return $this;
  }

  public function disableCreateFile()
  {
    $this->fileMustExists = true;
    return $this;
  }
}
