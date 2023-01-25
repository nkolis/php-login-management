<?php

namespace ProgrammerZamanNow\Belajar\PHP\MVC\Service;

use PHPUnit\Framework\TestCase;
use ProgrammerZamanNow\Belajar\PHP\MVC\Config\Database;
use ProgrammerZamanNow\Belajar\PHP\MVC\Domain\User;
use ProgrammerZamanNow\Belajar\PHP\MVC\Exception\ValidateException;
use ProgrammerZamanNow\Belajar\PHP\MVC\Model\UserRegisterRequest;
use ProgrammerZamanNow\Belajar\PHP\MVC\Model\UserRegisterResponse;
use ProgrammerZamanNow\Belajar\PHP\MVC\Repository\UserRepository;

class UserServiceTest extends TestCase
{

  private UserRepository $userRepository;
  private UserService $userService;
  private User $user;

  public function setUp(): void
  {
    $this->userRepository = new UserRepository(Database::getConnection());
    $this->userService = new UserService($this->userRepository);
    $user = new User;
    $user->id = "nkolis";
    $user->name = "Kholis";
    $user->password = "Rahasia";
    $this->user = $user;
    $this->userRepository->deleteAll();
  }

  public function testRegisterSuccess()
  {
    $request = new UserRegisterRequest;
    $request->id = $this->user->id;
    $request->name = $this->user->name;
    $request->password = $this->user->password;

    $response = $this->userService->register($request);
    $result = $this->userRepository->findById($request->id);



    self::assertEquals(UserRegisterResponse::class, $response::class);
    self::assertEquals($result->id, $request->id);
    self::assertEquals($result->name, $request->name);
    self::assertNotEquals($result->password, $request->password);

    self::assertTrue(password_verify($request->password, $result->password));
  }

  public function testRegisterFailed()
  {
    $this->expectException(ValidateException::class);
    $request = new UserRegisterRequest;
    $request->id = '';
    $request->name = '';
    $request->password = '';

    $this->userService->register($request);
  }

  public function testRegisterDuplicate()
  {
    $this->expectException(ValidateException::class);
    $this->userRepository->save($this->user);
    $request = new UserRegisterRequest;
    $request->id = $this->user->id;
    $request->name = $this->user->name;
    $request->password = $this->user->password;
    $this->userService->register($request);
  }
}
