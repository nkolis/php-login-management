<?php

namespace ProgrammerZamanNow\Belajar\PHP\MVC\App {
  function header($url)
  {
    echo $url;
  }
}

namespace ProgrammerZamanNow\Belajar\PHP\MVC\Service {
  function setcookie(string $name, string $value)
  {
    echo "$name: $value";
  }
}
