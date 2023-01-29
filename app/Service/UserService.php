<?php

namespace ProgrammerZamanNow\Belajar\PHP\MVC\Service;

use Exception;
use ProgrammerZamanNow\Belajar\PHP\MVC\Config\Database;
use ProgrammerZamanNow\Belajar\PHP\MVC\Domain\User;
use ProgrammerZamanNow\Belajar\PHP\MVC\Exception\ValidateException;
use ProgrammerZamanNow\Belajar\PHP\MVC\Model\{UserLoginRequest, UserLoginResponse, UserPasswordUpdateRequest, UserPasswordUpdateResponse, UserProfileUpdateRequest, UserProfileUpdateResponse, UserRegisterRequest, UserRegisterResponse};
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

  public function updateProfileUser(UserProfileUpdateRequest $request): UserProfileUpdateResponse
  {
    $this->validateUserProfileUpdateRequest($request);

    try {
      Database::beginTransaction();
      $user = $this->userRepository->findById($request->id);

      if ($user == null) {
        throw new ValidateException('User is not found');
      }

      $user->name = $request->name;

      $this->userRepository->update($user);
      Database::commitTransaction();
      $response = new UserProfileUpdateResponse;
      $response->user = $user;

      return $response;
    } catch (ValidateException $exception) {
      Database::rollbackTransaction();
      throw $exception;
    }
  }

  private function validateUserProfileUpdateRequest(UserProfileUpdateRequest $request): void
  {
    if ($request->id == null || $request->name == null || trim($request->id) == '' || trim($request->name) == '') {
      throw new ValidateException('id, name cannot blank');
    }
  }

  public function updatePassword(UserPasswordUpdateRequest $request): UserPasswordUpdateResponse
  {
    $this->validateUserPasswordUpdateRequest($request);


    try {
      Database::beginTransaction();
      $user = $this->userRepository->findById($request->id);
      if ($user == null) {
        throw new ValidateException('User not found');
      }

      if (!password_verify($request->oldPassword, $user->password)) {
        throw new ValidateException('Old password is wrong');
      }

      $user->password = password_hash($request->newPassword, PASSWORD_BCRYPT);

      $this->userRepository->update($user);
      Database::commitTransaction();
      $response = new UserPasswordUpdateResponse;
      $response->user = $user;
      return $response;
    } catch (Exception $e) {
      Database::rollbackTransaction();
      throw $e;
    }
  }

  private function validateUserPasswordUpdateRequest(UserPasswordUpdateRequest $request): void
  {
    if ($request->id == null || $request->oldPassword == null || $request->newPassword == null || trim($request->id) == '' || trim($request->oldPassword) == '' || trim($request->newPassword) == '') {
      throw new ValidateException('id, old password, new password cannot blank');
    }
  }
}
