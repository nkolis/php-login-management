<?php

namespace ProgrammerZamanNow\Belajar\PHP\MVC\Controller;

use ProgrammerZamanNow\Belajar\PHP\MVC\App\View;

class HomeController
{

  function index(): void
  {
    $model = [
      "title" => "Belajar PHP Login Management",
      "content" => "Selamat Belajar PHP MVC dari Programmer Zaman Now"
    ];

    View::render('Home/index', $model);
  }
}
