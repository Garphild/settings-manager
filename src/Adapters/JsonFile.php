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
  protected $changed = false;

  function __construct($basePath, $structureFileName, $allowCreateFile = false) {
    if ($allowCreateFile) $this->enableCreateFile();
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
    if (!$this->fileMustExists) {
      file_put_contents($this->getFilename(), "{}");
    }
  }

  function loadFile() {
    if (file_exists($this->getFilename())) {
      $this->file = file_get_contents($this->getFilename());
      $this->parsed = json_decode($this->file, true, 99999999);
    } else {
      if (!$this->fileMustExists) {
        $this->file = "{}";
        $this->parsed = [];
      } else {
        throw new MissingFileException(null, $this->getFilename());
      }
    }
  }

  protected function saveFile() {
    $this->file = json_encode($this->parsed, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT, 9999999);
    file_put_contents($this->getFilename(), $this->file);
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
