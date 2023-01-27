<?php

namespace ProgrammerZamanNow\Belajar\PHP\MVC\App {
  function header($url)
  {
    echo $url;
  }
}

namespace ProgrammerZamanNow\Belajar\PHP\MVC\Controller {

  use PHPUnit\Framework\TestCase;
  use ProgrammerZamanNow\Belajar\PHP\MVC\Config\BaseURL;
  use ProgrammerZamanNow\Belajar\PHP\MVC\Config\Database;
  use ProgrammerZamanNow\Belajar\PHP\MVC\Domain\User;
  use ProgrammerZamanNow\Belajar\PHP\MVC\Repository\UserRepository;

  class UserControllerTest extends TestCase
  {
    private UserController $usercontroller;
    private UserRepository $userRepository;
    function setUp(): void
    {
      $this->usercontroller = new UserController;
      $this->userRepository = new UserRepository(Database::getConnection());

      $this->userRepository->deleteAll();
      putenv("mode=test");
    }

    public function testRegister()
    {
      $this->usercontroller->register();

      $this->expectOutputRegex("[register]");
      $this->expectOutputRegex("[id]");
      $this->expectOutputRegex("[name]");
      $this->expectOutputRegex("[password]");
      $this->expectOutputRegex("[Register New User]");
    }

    public function testPostRegisterSuccess()
    {
      $_POST['id'] = 'kholis';
      $_POST['name'] = 'kholis';
      $_POST['password'] = 'kholis';

      $this->usercontroller->postRegister();

      $this->expectOutputString("Location: " . BaseURL::get() . "/users/login");
    }

    public function testPostRegisterValidationError()
    {
      $_POST['id'] = '';
      $_POST['name'] = '';
      $_POST['password'] = '';

      $this->usercontroller->postRegister();

      $this->expectOutputRegex("[register]");
      $this->expectOutputRegex("[id]");
      $this->expectOutputRegex("[name]");
      $this->expectOutputRegex("[password]");
      $this->expectOutputRegex("[Register New User]");
      $this->expectOutputRegex("[id, name, password cannot blank]");
    }

    public function testPostRegisterDuplicate()
    {
      $user = new User;
      $user->id = 'kholis';
      $user->name = 'kholis';
      $user->password = 'kholis';
      $this->userRepository->save($user);

      $_POST['id'] = 'kholis';
      $_POST['name'] = 'kholis';
      $_POST['password'] = 'kholis';

      $this->usercontroller->postRegister();

      $this->expectOutputRegex("[register]");
      $this->expectOutputRegex("[id]");
      $this->expectOutputRegex("[name]");
      $this->expectOutputRegex("[password]");
      $this->expectOutputRegex("[Register New User]");
      $this->expectOutputRegex("[user id already exist]");
    }
  }
}
