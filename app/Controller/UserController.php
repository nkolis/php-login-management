<?php

namespace ProgrammerZamanNow\Belajar\PHP\MVC\Controller;

use ProgrammerZamanNow\Belajar\PHP\MVC\App\View;
use ProgrammerZamanNow\Belajar\PHP\MVC\Config\Database;
use ProgrammerZamanNow\Belajar\PHP\MVC\Exception\ValidateException;
use ProgrammerZamanNow\Belajar\PHP\MVC\Model\UserRegisterRequest;
use ProgrammerZamanNow\Belajar\PHP\MVC\Repository\UserRepository;
use ProgrammerZamanNow\Belajar\PHP\MVC\Service\UserService;

class UserController
{

  private UserService $userService;

  public function __construct()
  {
    $connection = Database::getConnection();
    $userRepository = new UserRepository($connection);
    $this->userService = new UserService($userRepository);
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
    try {
      $request = new UserRegisterRequest;
      $request->id = $_POST['id'];
      $request->name = $_POST['name'];
      $request->password = $_POST['password'];

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
}
