<?php

namespace ProgrammerZamanNow\Belajar\PHP\MVC\Model;

use ProgrammerZamanNow\Belajar\PHP\MVC\Domain\User;

class UserRegisterRequest
{
  public ?string $id;
  public ?string $name;
  public ?string $password;
}
