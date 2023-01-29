<?php

namespace ProgrammerZamanNow\Belajar\PHP\MVC\Service;

use PHPUnit\Framework\TestCase;
use ProgrammerZamanNow\Belajar\PHP\MVC\Config\Database;
use ProgrammerZamanNow\Belajar\PHP\MVC\Domain\User;
use ProgrammerZamanNow\Belajar\PHP\MVC\Exception\ValidateException;
use ProgrammerZamanNow\Belajar\PHP\MVC\Model\UserLoginRequest;
use ProgrammerZamanNow\Belajar\PHP\MVC\Model\UserPasswordUpdateRequest;
use ProgrammerZamanNow\Belajar\PHP\MVC\Model\UserProfileUpdateRequest;
use ProgrammerZamanNow\Belajar\PHP\MVC\Model\UserRegisterRequest;
use ProgrammerZamanNow\Belajar\PHP\MVC\Model\UserRegisterResponse;
use ProgrammerZamanNow\Belajar\PHP\MVC\Repository\SessionRepository;
use ProgrammerZamanNow\Belajar\PHP\MVC\Repository\UserRepository;

class UserServiceTest extends TestCase
{

  private UserRepository $userRepository;
  private SessionRepository $sessionRepository;
  private UserService $userService;
  private User $user;

  public function setUp(): void
  {
    $this->userRepository = new UserRepository(Database::getConnection());
    $this->sessionRepository = new SessionRepository(Database::getConnection());
    $this->userService = new UserService($this->userRepository);
    $user = new User;
    $user->id = "nkolis";
    $user->name = "Kholis";
    $user->password = "Rahasia";
    $this->user = $user;
    $this->sessionRepository->deleteAll();
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

  public function testLoginNotfound()
  {

    $this->expectExceptionMessage('id, password cannot blank');
    $request = new UserLoginRequest;
    $request->id = '';
    $request->password = '';

    $this->userService->login($request);
  }

  public function testLoginWrongPassword()
  {
    $this->expectExceptionMessage('id or password wrong');
    $requestRegister = new UserRegisterRequest;
    $requestRegister->id = $this->user->id;
    $requestRegister->name = $this->user->name;
    $requestRegister->password = $this->user->password;
    $this->userService->register($requestRegister);


    $requestLogin = new UserLoginRequest;
    $requestLogin->id = $this->user->id;
    $requestLogin->password = '547';
    $this->userService->login($requestLogin);
  }

  public function testLoginSuccess()
  {
    $requestRegister = new UserRegisterRequest;
    $requestRegister->id = $this->user->id;
    $requestRegister->name = $this->user->name;
    $requestRegister->password = $this->user->password;
    $this->userService->register($requestRegister);

    $request = new UserLoginRequest;
    $request->id = $this->user->id;
    $request->password = $this->user->password;

    $response = $this->userService->login($request);

    self::assertEquals($request->id, $response->user->id);
    self::assertTrue(password_verify($request->password, $response->user->password));
  }

  public function testUpdateSuccess()
  {
    $request = new UserProfileUpdateRequest;
    $request->id = 'kholis';
    $request->name = 'Nur kholis';


    $user = new User;
    $user->id = 'kholis';
    $user->name = 'kholis';
    $user->password = 'rahasia';
    $this->userRepository->save($user);

    $this->userService->updateProfileUser($request);

    $result = $this->userRepository->findById($request->id);

    $this->assertEquals($request->name, $result->name);
  }

  public function testUpdateFailed()
  {

    $this->expectExceptionMessage('id, name cannot blank');
    $request = new UserProfileUpdateRequest;
    $request->id = '';
    $request->name = '';

    $this->userService->updateProfileUser($request);
  }

  public function testUpdateNotFound()
  {
    $this->expectExceptionMessage('User is not found');
    $user = new User;
    $user->id = 'kholis';
    $user->name = 'kholis';
    $user->password = 'rahasia';
    $this->userRepository->save($user);

    $request = new UserProfileUpdateRequest;
    $request->id = 'setiawan';
    $request->name = 'nurkholis';

    $this->userService->updateProfileUser($request);
  }

  public function testUpdatePasswordSuccess()
  {
    $user = new User;
    $user->id = 'kholis';
    $user->name = 'kholis';
    $user->password = password_hash('rahasia', PASSWORD_BCRYPT);
    $this->userRepository->save($user);

    $request = new UserPasswordUpdateRequest;
    $request->id = $user->id;
    $request->oldPassword  = 'rahasia';
    $request->newPassword  = 'kholis';
    $this->userService->updatePassword($request);

    $result = $this->userRepository->findById($request->id);

    $this->assertTrue(password_verify($request->newPassword, $result->password));
  }

  public function testUpdatePasswordValidationError()
  {

    $this->expectExceptionMessage('id, old password, new password cannot blank');
    $request = new UserPasswordUpdateRequest;
    $request->id = '';
    $request->oldPassword  = 'rahasia';
    $request->newPassword  = 'kholis';
    $this->userService->updatePassword($request);
  }

  public function testUpdatePasswordWrongOldPassword()
  {
    $this->expectExceptionMessage('Old password is wrong');
    $user = new User;
    $user->id = 'kholis';
    $user->name = 'kholis';
    $user->password = password_hash('rahasia', PASSWORD_BCRYPT);
    $this->userRepository->save($user);

    $request = new UserPasswordUpdateRequest;
    $request->id = $user->id;
    $request->oldPassword  = 'salah';
    $request->newPassword  = 'kholis';
    $this->userService->updatePassword($request);
  }

  public function testUpdatePasswordNotFound()
  {
    $this->expectExceptionMessage('User not found');
    $request = new UserPasswordUpdateRequest;
    $request->id = 'notfound';
    $request->oldPassword  = 'salah';
    $request->newPassword  = 'kholis';
    $this->userService->updatePassword($request);
  }
}
