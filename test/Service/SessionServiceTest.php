<?php

namespace ProgrammerZamanNow\Belajar\PHP\MVC\Service;

require_once __DIR__ . "/../Helper/helper.php";


use PHPUnit\Framework\TestCase;
use ProgrammerZamanNow\Belajar\PHP\MVC\Config\Database;
use ProgrammerZamanNow\Belajar\PHP\MVC\Domain\Session;
use ProgrammerZamanNow\Belajar\PHP\MVC\Domain\User;
use ProgrammerZamanNow\Belajar\PHP\MVC\Repository\SessionRepository;
use ProgrammerZamanNow\Belajar\PHP\MVC\Repository\UserRepository;

class SessionServiceTest extends TestCase
{

  private UserRepository $userRepository;
  private SessionRepository $sessionRepository;
  private SessionService $sessionService;

  function setUp(): void
  {
    $this->userRepository = new UserRepository(Database::getConnection());
    $this->sessionRepository = new SessionRepository(Database::getConnection());
    $this->sessionService = new SessionService($this->sessionRepository, $this->userRepository);

    $this->sessionRepository->deleteAll();
    $this->userRepository->deleteAll();

    $user = new User;
    $user->id = 'nkholis';
    $user->name = 'kholis';
    $user->password = 'Rahasia';

    $this->userRepository->save($user);
  }


  public function testCreate()
  {
    $session = $this->sessionService->create('nkholis');
    $this->expectOutputRegex("[X-PZN-SESSION: $session->id]");

    $result = $this->sessionRepository->findById($session->id);
    self::assertEquals($session->id, $result->id);
    self::assertEquals($session->userId, $result->userId);
  }

  public function testDestroy()
  {

    $session = new Session;
    $session->id = uniqid();
    $session->userId = 'nkholis';
    $this->sessionRepository->save($session);
    $_COOKIE['X-PZN-SESSION'] = $session->id;


    $this->sessionService->destroy();
    $this->expectOutputRegex("[X-PZN-SESSION: ]");


    $result = $this->sessionRepository->findById($session->id);
    self::assertNull($result);
  }

  function testCurrent()
  {
    $session = new Session;
    $session->id = uniqid();
    $session->userId = 'nkholis';
    $this->sessionRepository->save($session);
    $_COOKIE['X-PZN-SESSION'] = $session->id;


    $user = $this->sessionService->current();
    self::assertEquals($session->userId, $user->id);
  }
}
