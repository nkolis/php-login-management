<?php

namespace ProgrammerZamanNow\Belajar\PHP\MVC\Repository;

use PHPUnit\Framework\TestCase;
use ProgrammerZamanNow\Belajar\PHP\MVC\Config\Database;
use ProgrammerZamanNow\Belajar\PHP\MVC\Domain\Session;
use ProgrammerZamanNow\Belajar\PHP\MVC\Domain\User;

class SessionRepositoryTest extends TestCase
{
  private SessionRepository $sessionRepository;
  private UserRepository $userRepository;
  function setUp(): void
  {
    $this->sessionRepository = new SessionRepository(Database::getConnection());
    $this->userRepository = new UserRepository(Database::getConnection());
    $this->sessionRepository->deleteAll();
    $this->userRepository->deleteAll();

    $user = new User;
    $user->id = 'nkholis';
    $user->name = 'kholis';
    $user->password = 'Rahasia';

    $this->userRepository->save($user);
  }

  public function testSaveSuccess()
  {
    $session = new Session;
    $session->id = uniqid();
    $session->userId = 'nkholis';

    $this->sessionRepository->save($session);
    $result = $this->sessionRepository->findById($session->id);

    self::assertEquals($session->id, $result->id);
    self::assertEquals($session->userId, $result->userId);
  }

  public function testDeleteByIdSuccess()
  {
    $session = new Session;
    $session->id = uniqid();
    $session->userId = 'nkholis';

    $this->sessionRepository->save($session);
    $result = $this->sessionRepository->findById($session->id);

    self::assertEquals($session->id, $result->id);
    self::assertEquals($session->userId, $result->userId);

    $this->sessionRepository->deleteById($session->id);
    $result = $this->sessionRepository->findById($session->id);
    self::assertNull($result);
  }

  public function testFindByIdNotFound()
  {
    $result = $this->sessionRepository->findById('notfound');
    self::assertNull($result);
  }
}
