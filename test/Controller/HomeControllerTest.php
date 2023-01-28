<?php

namespace ProgrammerZamanNow\Belajar\PHP\MVC\Controller;

use PHPUnit\Framework\TestCase;
use ProgrammerZamanNow\Belajar\PHP\MVC\Config\Database;
use ProgrammerZamanNow\Belajar\PHP\MVC\Domain\Session;
use ProgrammerZamanNow\Belajar\PHP\MVC\Domain\User;
use ProgrammerZamanNow\Belajar\PHP\MVC\Repository\SessionRepository;
use ProgrammerZamanNow\Belajar\PHP\MVC\Repository\UserRepository;
use ProgrammerZamanNow\Belajar\PHP\MVC\Service\SessionService;

class  HomeControllerTest extends TestCase
{

  private HomeController $homeController;
  private UserRepository $userRepository;
  private SessionRepository $sessionRepository;
  private SessionService $sessionService;

  public function setUp(): void
  {
    $this->homeController = new HomeController;
    $connection = Database::getConnection();
    $this->userRepository = new UserRepository($connection);
    $this->sessionRepository = new SessionRepository($connection);
    $this->sessionService = new SessionService($this->sessionRepository, $this->userRepository);

    $this->sessionRepository->deleteAll();
    $this->userRepository->deleteAll();
  }

  public function testGuest()
  {
    $this->homeController->index();

    $this->expectOutputRegex("[Login Management]");
  }

  public function testUserLogin()
  {
    $user = new User;
    $user->id = 'nkholis';
    $user->name = 'Kholis';
    $user->password = 'rahasia';

    $this->userRepository->save($user);

    $session = new Session;
    $session->id = uniqid();
    $session->userId = $user->id;

    $this->sessionRepository->save($session);
    $_COOKIE['X-PZN-SESSION'] = $session->id;


    $this->homeController->index();

    $this->expectOutputRegex("[Selamat datang, Kholis]");
  }
}
