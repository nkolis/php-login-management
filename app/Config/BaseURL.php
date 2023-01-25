<?php

namespace ProgrammerZamanNow\Belajar\PHP\MVC\Config;

class BaseURL
{
  private static ?string $baseUrl = null;
  public static function get(): string
  {
    if (self::$baseUrl == null) {
      require __DIR__ . '/../../config/baseurl.php';
      self::$baseUrl = BASE_URL;
    }
    return self::$baseUrl;
  }
}
