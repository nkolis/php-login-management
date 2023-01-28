<?php

namespace ProgrammerZamanNow\Belajar\PHP\MVC\App;

use ProgrammerZamanNow\Belajar\PHP\MVC\Config\BaseURL;

class View
{

  public static function render(string $view, $model)
  {
    require __DIR__ . '/../View/header.php';
    require __DIR__ . '/../View/' . $view . '.php';
    require __DIR__ . '/../View/footer.php';
  }

  public static function redirect($url)
  {
    header('Location: ' . BaseURL::get() . '/' . trim($url, '/'));

    if (getenv('mode') != 'test') {
      exit();
    }
  }
}
