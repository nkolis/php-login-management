<?php

namespace ProgrammerZamanNow\Belajar\PHP\MVC\Controller;

use ProgrammerZamanNow\Belajar\PHP\MVC\App\View;
use ProgrammerZamanNow\Belajar\PHP\MVC\Config\Database;
use ProgrammerZamanNow\Belajar\PHP\MVC\Exception\ValidateException;
use ProgrammerZamanNow\Belajar\PHP\MVC\Model\UserLoginRequest;
use ProgrammerZamanNow\Belajar\PHP\MVC\Model\UserPasswordUpdateRequest;
use ProgrammerZamanNow\Belajar\PHP\MVC\Model\UserProfileUpdateRequest;
use ProgrammerZamanNow\Belajar\PHP\MVC\Model\UserRegisterRequest;
use ProgrammerZamanNow\Belajar\PHP\MVC\Repository\SessionRepository;
use ProgrammerZamanNow\Belajar\PHP\MVC\Repository\UserRepository;
use ProgrammerZamanNow\Belajar\PHP\MVC\Service\SessionService;
use ProgrammerZamanNow\Belajar\PHP\MVC\Service\UserService;

class UserController
{

  private UserService $userService;
  private SessionRepository $sessionRepository;
  private SessionService $sessionService;

  public function __construct()
  {
    $connection = Database::getConnection();
    $userRepository = new UserRepository($connection);
    $sessionRepository = new SessionRepository($connection);
    $this->userService = new UserService($userRepository);
    $this->sessionService = new SessionService($sessionRepository, $userRepository);
  }

  public function register()
  {
    $model = [
      'title' => 'Register New User',
    ];

    View::render('User/register', $model);
  }

  public function postRegister()
  {
    $request = new UserRegisterRequest;
    $request->id = $_POST['id'];
    $request->name = $_POST['name'];
    $request->password = $_POST['password'];
    try {
      $this->userService->register($request);
      View::redirect('users/login');
    } catch (ValidateException $e) {
      $model = [
        'title' => 'Register New User',
        'error' => $e->getMessage(),
      ];

      View::render('User/register', $model);
    }
  }

  public function login()
  {
    $model = [
      'title' => 'Login User'
    ];

    View::render('User/login', $model);
  }

  public function postLogin()
  {
    $request = new UserLoginRequest;
    $request->id = $_POST['id'];
    $request->password = $_POST['password'];
    try {
      $response = $this->userService->login($request);

      $this->sessionService->create($response->user->id);
      view::redirect('/');
    } catch (ValidateException $e) {
      $model = [
        'title' => 'Login User',
        'error' => $e->getMessage(),
      ];

      View::render('User/login', $model);
    }
  }

  public function logout()
  {
    $this->sessionService->destroy();
    View::redirect('/');
  }

  public function updateProfile()
  {
    $user = $this->sessionService->current();
    View::render('User/profile', [
      'title' => 'User Profile Update',
      'user' => [
        'id' => $user->id,
        'name' => $user->name
      ]
    ]);
  }

  public function postUpdateProfile()
  {
    $user = $this->sessionService->current();
    try {
      $request = new UserProfileUpdateRequest;
      $request->id = $user->id;
      $request->name = $_POST['name'];
      $this->userService->updateProfileUser($request);
      View::redirect("/");
    } catch (ValidateException $e) {
      View::render('User/profile', [
        'title' => 'User Profile Update',
        'error' => $e->getMessage(),
        'user' => [
          'id' => $user->id,
          'name' => $_POST['name']
        ]
      ]);
    }
  }

  public function updatePassword()
  {
    $user = $this->sessionService->current();
    View::render('User/password', [
      'title' => 'User Password Update',
      'user' => [
        'id' => $user->id,
      ]
    ]);
  }

  public function postUpdatePassword()
  {
    $user = $this->sessionService->current();
    try {
      $request = new UserPasswordUpdateRequest;
      $request->id = $user->id;
      $request->oldPassword = $_POST['oldPassword'];
      $request->newPassword = $_POST['newPassword'];

      $this->userService->updatePassword($request);

      View::redirect("/");
    } catch (ValidateException $e) {
      View::render('User/password', [
        'title' => 'User Password Update',
        'error' => $e->getMessage(),
        'user' => [
          'id' => $user->id,
        ]
      ]);
    }
  }
}
