<?php

namespace ProgrammerZamanNow\Belajar\PHP\MVC\Controller {

  require_once __DIR__ . "/../Helper/helper.php";

  use PHPUnit\Framework\TestCase;
  use ProgrammerZamanNow\Belajar\PHP\MVC\Config\BaseURL;
  use ProgrammerZamanNow\Belajar\PHP\MVC\Config\Database;
  use ProgrammerZamanNow\Belajar\PHP\MVC\Domain\Session;
  use ProgrammerZamanNow\Belajar\PHP\MVC\Domain\User;
  use ProgrammerZamanNow\Belajar\PHP\MVC\Model\UserLoginRequest;
  use ProgrammerZamanNow\Belajar\PHP\MVC\Repository\SessionRepository;
  use ProgrammerZamanNow\Belajar\PHP\MVC\Repository\UserRepository;
  use ProgrammerZamanNow\Belajar\PHP\MVC\Service\SessionService;

  class UserControllerTest extends TestCase
  {
    private UserController $usercontroller;
    private UserRepository $userRepository;
    private SessionRepository $sessionRepository;
    function setUp(): void
    {
      $this->usercontroller = new UserController;
      $connection = Database::getConnection();
      $this->userRepository = new UserRepository($connection);
      $this->sessionRepository = new SessionRepository($connection);

      $this->sessionRepository->deleteAll();
      $this->userRepository->deleteAll();
      putenv("mode=test");
    }

    public function testRegister()
    {
      $this->usercontroller->register();

      $this->expectOutputRegex("[register]");
      $this->expectOutputRegex("[id]");
      $this->expectOutputRegex("[name]");
      $this->expectOutputRegex("[password]");
      $this->expectOutputRegex("[Register New User]");
    }

    public function testPostRegisterSuccess()
    {
      $_POST['id'] = 'kholis';
      $_POST['name'] = 'kholis';
      $_POST['password'] = 'kholis';

      $this->usercontroller->postRegister();

      $this->expectOutputString("Location: " . BaseURL::get() . "/users/login");
    }

    public function testPostRegisterValidationError()
    {
      $_POST['id'] = '';
      $_POST['name'] = '';
      $_POST['password'] = '';

      $this->usercontroller->postRegister();

      $this->expectOutputRegex("[register]");
      $this->expectOutputRegex("[id]");
      $this->expectOutputRegex("[name]");
      $this->expectOutputRegex("[password]");
      $this->expectOutputRegex("[Register New User]");
      $this->expectOutputRegex("[id, name, password cannot blank]");
    }

    public function testPostRegisterDuplicate()
    {
      $user = new User;
      $user->id = 'kholis';
      $user->name = 'kholis';
      $user->password = 'kholis';
      $this->userRepository->save($user);

      $_POST['id'] = 'kholis';
      $_POST['name'] = 'kholis';
      $_POST['password'] = 'kholis';

      $this->usercontroller->postRegister();

      $this->expectOutputRegex("[register]");
      $this->expectOutputRegex("[id]");
      $this->expectOutputRegex("[name]");
      $this->expectOutputRegex("[password]");
      $this->expectOutputRegex("[Register New User]");
      $this->expectOutputRegex("[user id already exist]");
    }

    public function testLogin()
    {
      $this->usercontroller->login();

      $this->expectOutputRegex("[Login]");
      $this->expectOutputRegex("[Id]");
      $this->expectOutputRegex("[Password]");
      $this->expectOutputRegex("[Login User]");
    }

    public function testLoginValidateError()
    {
      $requestLogin = new UserLoginRequest;
      $requestLogin->id = '';
      $requestLogin->password = '';

      $_POST['id'] = $requestLogin->id;
      $_POST['password'] = $requestLogin->password;

      $this->usercontroller->postLogin();

      $this->expectOutputRegex("[Login]");
      $this->expectOutputRegex("[Id]");
      $this->expectOutputRegex("[Password]");
      $this->expectOutputRegex("[Login User]");
      $this->expectOutputRegex("[id, password cannot blank]");
    }

    public function testLoginWrongPassword()
    {
      $user = new User;
      $user->id = 'kholis';
      $user->name = 'kholis';
      $user->password = password_hash('kholis', PASSWORD_BCRYPT);
      $this->userRepository->save($user);

      $requestLogin = new UserLoginRequest;
      $requestLogin->id = 'kholis';
      $requestLogin->password = '3434';

      $_POST['id'] = $requestLogin->id;
      $_POST['password'] = $requestLogin->password;

      $this->usercontroller->postLogin();

      $this->expectOutputRegex("[Login]");
      $this->expectOutputRegex("[Id]");
      $this->expectOutputRegex("[Password]");
      $this->expectOutputRegex("[Login User]");
      $this->expectOutputRegex("[id or password wrong]");
    }

    public function testLoginSuccess()
    {
      $user = new User;
      $user->id = 'kholis';
      $user->name = 'kholis';
      $user->password = password_hash('kholis', PASSWORD_BCRYPT);
      $this->userRepository->save($user);

      $requestLogin = new UserLoginRequest;
      $requestLogin->id = 'kholis';
      $requestLogin->password = 'kholis';

      $_POST['id'] = $requestLogin->id;
      $_POST['password'] = $requestLogin->password;

      $this->usercontroller->postLogin();

      $this->expectOutputString("Location: " . BaseURL::get() . '/');
      $this->expectOutputRegex("[X-PZN-SESSION: ]");
    }

    public function testLogout()
    {
      $user = new User;
      $user->id = 'kholis';
      $user->name = 'kholis';
      $user->password = password_hash('kholis', PASSWORD_BCRYPT);
      $this->userRepository->save($user);

      $session = new Session;
      $session->id = uniqid();
      $session->userId = $user->id;
      $this->sessionRepository->save($session);

      $_COOKIE[SessionService::$COOKIE_NAME] = $session->id;

      $this->usercontroller->logout();

      $this->expectOutputRegex("[Location: /]");
      $this->expectOutputRegex("[X-PZN-SESSION: ]");
    }

    public function testUpdateProfile()
    {
      $user = new User;
      $user->id = 'kholis';
      $user->name = 'kholis';
      $user->password = password_hash('kholis', PASSWORD_BCRYPT);
      $this->userRepository->save($user);

      $session = new Session;
      $session->id = uniqid();
      $session->userId = $user->id;
      $this->sessionRepository->save($session);

      $_COOKIE[SessionService::$COOKIE_NAME] = $session->id;

      $_POST['name'] = $user->name;

      $this->usercontroller->updateProfile();

      $this->expectOutputRegex("[Profile]");
      $this->expectOutputRegex("[Id]");
      $this->expectOutputRegex("[Name]");
      $this->expectOutputRegex("[kholis]");
      $this->expectOutputRegex("[User Profile Update]");
    }

    public function testPostUpdateProfileSuccess()
    {
      $user = new User;
      $user->id = 'kholis';
      $user->name = 'kholis';
      $user->password = password_hash('kholis', PASSWORD_BCRYPT);
      $this->userRepository->save($user);

      $session = new Session;
      $session->id = uniqid();
      $session->userId = $user->id;
      $this->sessionRepository->save($session);

      $_COOKIE[SessionService::$COOKIE_NAME] = $session->id;

      $_POST['name'] = 'setiawan';

      $this->usercontroller->postUpdateProfile();

      $result = $this->userRepository->findById($user->id);


      $this->assertEquals($_POST['name'], $result->name);
      $baseurl = BaseURL::get();
      $this->expectOutputRegex("[Location: $baseurl]");
    }

    public function testPostUpdateProfileError()
    {
      $user = new User;
      $user->id = 'kholis';
      $user->name = 'kholis';
      $user->password = password_hash('kholis', PASSWORD_BCRYPT);
      $this->userRepository->save($user);

      $session = new Session;
      $session->id = uniqid();
      $session->userId = $user->id;
      $this->sessionRepository->save($session);

      $_COOKIE[SessionService::$COOKIE_NAME] = $session->id;

      $_POST['name'] = '';

      $this->usercontroller->postUpdateProfile();

      $this->expectOutputRegex("[Profile]");
      $this->expectOutputRegex("[Id]");
      $this->expectOutputRegex("[Name]");
      $this->expectOutputRegex("[User Profile Update]");
      $this->expectOutputRegex("[id, name cannot blank]");
    }

    public function testUpdatePassword()
    {
      $user = new User;
      $user->id = 'kholis';
      $user->name = 'kholis';
      $user->password = password_hash('kholis', PASSWORD_BCRYPT);
      $this->userRepository->save($user);

      $session = new Session;
      $session->id = uniqid();
      $session->userId = $user->id;
      $this->sessionRepository->save($session);

      $_COOKIE[SessionService::$COOKIE_NAME] = $session->id;

      $this->usercontroller->updatePassword();

      $this->expectOutputRegex("[Password]");
      $this->expectOutputRegex("[id]");
      $this->expectOutputRegex("[oldPassword]");
      $this->expectOutputRegex("[newPassword]");
      $this->expectOutputRegex("[User Password Update]");
    }

    public function testUpdatePostPasswordSuccess()
    {
      $user = new User;
      $user->id = 'kholis';
      $user->name = 'kholis';
      $user->password = password_hash('kholis', PASSWORD_BCRYPT);
      $this->userRepository->save($user);

      $session = new Session;
      $session->id = uniqid();
      $session->userId = $user->id;
      $this->sessionRepository->save($session);

      $_COOKIE[SessionService::$COOKIE_NAME] = $session->id;

      $_POST['oldPassword'] = 'kholis';
      $_POST['newPassword'] = 'rahasia';

      $this->usercontroller->postUpdatePassword();
      $result = $this->userRepository->findById($user->id);

      $this->assertTrue(password_verify($_POST['newPassword'], $result->password));
      $baseurl = BaseURL::get();
      $this->expectOutputRegex("[Location: $baseurl]");
    }

    public function testUpdatePasswordValidationError()
    {
      $user = new User;
      $user->id = 'kholis';
      $user->name = 'kholis';
      $user->password = password_hash('kholis', PASSWORD_BCRYPT);
      $this->userRepository->save($user);

      $session = new Session;
      $session->id = uniqid();
      $session->userId = $user->id;
      $this->sessionRepository->save($session);

      $_COOKIE[SessionService::$COOKIE_NAME] = $session->id;

      $_POST['oldPassword'] = '';
      $_POST['newPassword'] = '';

      $this->usercontroller->postUpdatePassword();
      $this->expectOutputRegex('[id, old password, new password cannot blank]');
    }

    public function testUpdatePasswordWrongOldPassword()
    {
      $user = new User;
      $user->id = 'kholis';
      $user->name = 'kholis';
      $user->password = password_hash('kholis', PASSWORD_BCRYPT);
      $this->userRepository->save($user);

      $session = new Session;
      $session->id = uniqid();
      $session->userId = $user->id;
      $this->sessionRepository->save($session);

      $_COOKIE[SessionService::$COOKIE_NAME] = $session->id;

      $_POST['oldPassword'] = 'rahasia';
      $_POST['newPassword'] = 'rahasia123';

      $this->usercontroller->postUpdatePassword();
      $this->expectOutputRegex('[Old password is wrong]');
    }
  }
}
