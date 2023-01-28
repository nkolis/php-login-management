<?php

namespace ProgrammerZamanNow\Belajar\PHP\MVC\Service;

use ProgrammerZamanNow\Belajar\PHP\MVC\Config\Database;
use ProgrammerZamanNow\Belajar\PHP\MVC\Domain\User;
use ProgrammerZamanNow\Belajar\PHP\MVC\Exception\ValidateException;
use ProgrammerZamanNow\Belajar\PHP\MVC\Model\{UserLoginRequest, UserLoginResponse, UserRegisterRequest, UserRegisterResponse};
use ProgrammerZamanNow\Belajar\PHP\MVC\Repository\UserRepository;

class UserService
{

  private UserRepository $userRepository;

  public function __construct($repository)
  {
    $this->userRepository = $repository;
  }

  public function register(UserRegisterRequest $request): UserRegisterResponse
  {

    $this->validateUserRequestRegister($request);

    try {
      Database::beginTransaction();

      if ($this->userRepository->findById($request->id) != null) {
        throw new ValidateException('user id already exist');
      }
      $user = new User;
      $user->id = $request->id;
      $user->name = $request->name;
      $user->password = password_hash($request->password, PASSWORD_BCRYPT);

      $this->userRepository->save($user);
      Database::commitTransaction();
      $response = new UserRegisterResponse;
      $response->user = $user;
      return $response;
    } catch (ValidateException $exception) {
      Database::rollbackTransaction();
      throw $exception;
    }
  }

  private function validateUserRequestRegister(UserRegisterRequest $request): void
  {
    if ($request->id == null || $request->name == null || $request->password == null || trim($request->id) == '' || trim($request->name) == '' || trim($request->password) == '') {
      throw new ValidateException('id, name, password cannot blank');
    }
  }

  public function login(UserLoginRequest $request): UserLoginResponse
  {
    $this->validateUserRequestLogin($request);

    $user = $this->userRepository->findById($request->id);

    if ($user == null) {
      throw new ValidateException('id or password wrong');
    }


    if (password_verify($request->password, $user->password)) {
      $response = new UserLoginResponse;
      $response->user = $user;
      return $response;
    } else {
      throw new ValidateException('id or password wrong');
    }
  }

  private function validateUserRequestLogin(UserLoginRequest $request): void
  {
    if ($request->id == null || $request->password == null || trim($request->id) == '' || trim($request->password) == '') {
      throw new ValidateException('id, password cannot blank');
    }
  }
}
