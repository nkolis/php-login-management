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

namespace ProgrammerZamanNow\Belajar\PHP\MVC\Controller {

  use PHPUnit\Framework\TestCase;
  use ProgrammerZamanNow\Belajar\PHP\MVC\Config\BaseURL;
  use ProgrammerZamanNow\Belajar\PHP\MVC\Config\Database;
  use ProgrammerZamanNow\Belajar\PHP\MVC\Domain\Session;
  use ProgrammerZamanNow\Belajar\PHP\MVC\Domain\User;
  use ProgrammerZamanNow\Belajar\PHP\MVC\Model\UserLoginRequest;
  use ProgrammerZamanNow\Belajar\PHP\MVC\Repository\SessionRepository;
  use ProgrammerZamanNow\Belajar\PHP\MVC\Repository\UserRepository;
  use ProgrammerZamanNow\Belajar\PHP\MVC\Service\SessionService;

  class UserControllerTest extends TestCase
  {
    private UserController $usercontroller;
    private UserRepository $userRepository;
    private SessionRepository $sessionRepository;
    function setUp(): void
    {
      $this->usercontroller = new UserController;
      $connection = Database::getConnection();
      $this->userRepository = new UserRepository($connection);
      $this->sessionRepository = new SessionRepository($connection);

      $this->sessionRepository->deleteAll();
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

    public function testLogin()
    {
      $this->usercontroller->login();

      $this->expectOutputRegex("[Login]");
      $this->expectOutputRegex("[Id]");
      $this->expectOutputRegex("[Password]");
      $this->expectOutputRegex("[Login User]");
    }

    public function testLoginValidateError()
    {
      $requestLogin = new UserLoginRequest;
      $requestLogin->id = '';
      $requestLogin->password = '';

      $_POST['id'] = $requestLogin->id;
      $_POST['password'] = $requestLogin->password;

      $this->usercontroller->postLogin();

      $this->expectOutputRegex("[Login]");
      $this->expectOutputRegex("[Id]");
      $this->expectOutputRegex("[Password]");
      $this->expectOutputRegex("[Login User]");
      $this->expectOutputRegex("[id, password cannot blank]");
    }

    public function testLoginWrongPassword()
    {
      $user = new User;
      $user->id = 'kholis';
      $user->name = 'kholis';
      $user->password = password_hash('kholis', PASSWORD_BCRYPT);
      $this->userRepository->save($user);

      $requestLogin = new UserLoginRequest;
      $requestLogin->id = 'kholis';
      $requestLogin->password = '3434';

      $_POST['id'] = $requestLogin->id;
      $_POST['password'] = $requestLogin->password;

      $this->usercontroller->postLogin();

      $this->expectOutputRegex("[Login]");
      $this->expectOutputRegex("[Id]");
      $this->expectOutputRegex("[Password]");
      $this->expectOutputRegex("[Login User]");
      $this->expectOutputRegex("[id or password wrong]");
    }

    public function testLoginSuccess()
    {
      $user = new User;
      $user->id = 'kholis';
      $user->name = 'kholis';
      $user->password = password_hash('kholis', PASSWORD_BCRYPT);
      $this->userRepository->save($user);

      $requestLogin = new UserLoginRequest;
      $requestLogin->id = 'kholis';
      $requestLogin->password = 'kholis';

      $_POST['id'] = $requestLogin->id;
      $_POST['password'] = $requestLogin->password;

      $this->usercontroller->postLogin();

      $this->expectOutputString("Location: " . BaseURL::get() . '/');
      $this->expectOutputRegex("[X-PZN-SESSION: ]");
    }

    public function testLogout()
    {
      $user = new User;
      $user->id = 'kholis';
      $user->name = 'kholis';
      $user->password = password_hash('kholis', PASSWORD_BCRYPT);
      $this->userRepository->save($user);

      $session = new Session;
      $session->id = uniqid();
      $session->userId = $user->id;
      $this->sessionRepository->save($session);

      $_COOKIE[SessionService::$COOKIE_NAME] = $session->id;

      $this->usercontroller->logout();

      $this->expectOutputRegex("[Location: /]");
      $this->expectOutputRegex("[X-PZN-SESSION: ]");
    }
  }
}
