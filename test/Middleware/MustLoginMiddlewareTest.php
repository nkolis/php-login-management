<?php

namespace ProgrammerZamanNow\Belajar\PHP\MVC\Middleware {

  require_once __DIR__ . "/../Helper/helper.php";


  use PHPUnit\Framework\TestCase;
  use ProgrammerZamanNow\Belajar\PHP\MVC\Config\BaseURL;
  use ProgrammerZamanNow\Belajar\PHP\MVC\Config\Database;
  use ProgrammerZamanNow\Belajar\PHP\MVC\Domain\Session;
  use ProgrammerZamanNow\Belajar\PHP\MVC\Domain\User;
  use ProgrammerZamanNow\Belajar\PHP\MVC\Repository\SessionRepository;
  use ProgrammerZamanNow\Belajar\PHP\MVC\Repository\UserRepository;
  use ProgrammerZamanNow\Belajar\PHP\MVC\Service\SessionService;


  class MustLoginMiddlewareTest extends TestCase
  {
    private MustLoginMiddleware $middleware;
    private UserRepository $userRepository;
    private SessionRepository $sessionRepository;

    function setUp(): void
    {
      $connection = Database::getConnection();
      $this->middleware = new MustLoginMiddleware;
      $this->userRepository = new UserRepository($connection);
      $this->sessionRepository = new SessionRepository($connection);

      putenv("mode=test");

      $this->sessionRepository->deleteAll();
      $this->userRepository->deleteAll();
    }

    function testBeforeGuest()
    {
      $this->middleware->before();
      $baseurl = BaseURL::get();
      $this->expectOutputRegex("[Location: $baseurl/users/login]");
    }

    function testBeforeLoginUser()
    {
      $user = new User;
      $user->id = 'kholis';
      $user->name = 'kholis';
      $user->password = 'kholis';
      $this->userRepository->save($user);

      $session = new Session;
      $session->id = uniqid();
      $session->userId = $user->id;
      $this->sessionRepository->save($session);

      $_COOKIE[SessionService::$COOKIE_NAME] = $session->id;

      $this->expectOutputRegex("''");
    }
  }
}
