<?php
namespace Tests\App\Account\Actions;

use App\Account\Actions\SignupAction;
use App\Auth\DatabaseAuth;
use App\Auth\User;
use App\Auth\UserTable;
use Framework\Renderer\RendererInterface;
use Framework\Router;
use Framework\Session\FlashService;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Tests\ActionTestCase;

class SignupActionTest extends ActionTestCase
{
    private $action;

    /**
     * @var ObjectProphecy
     */
    private $renderer;

    /**
     * @var ObjectProphecy
     */
    private $userTable;

    /**
     * @var ObjectProphecy
     */
    private $router;

    /**
     * @var ObjectProphecy
     */
    private $auth;

    /**
     * @var ObjectProphecy
     */
    private $flash;

    public function setUp()
    {
        //usertable
        $this->userTable = $this->prophesize(UserTable::class);
        $pdo = $this->prophesize(\PDO::class);
        $statement = $this->getMockBuilder(\PDOStatement::class)->getMock();
        $statement->expects($this->any())->method('fetchColumn')->willReturn(false);
        $pdo->prepare(Argument::any())->willReturn($statement);
        $pdo->lastInsertId()->willReturn(3);

        $this->userTable->getPdo()->willReturn($pdo->reveal());
        $this->userTable->getTable()->willReturn('fake');

        //renderer
        $this->renderer = $this->prophesize(RendererInterface::class);
        $this->renderer->render(Argument::any(), Argument::any())->willReturn('');

        //router
        $this->router = $this->prophesize(Router::class);
        //u will uzima argumente od metode i poziva arg 0
        $this->router->generateUri(Argument::any())->will(function ($args) {
            return $args[0];
        });
        //Auth
        $this->auth = $this->prophesize(DatabaseAuth::class);

        //flashservice
        $this->flash = $this->prophesize(FlashService::class);
        $this->action = new SignupAction(
            $this->renderer->reveal(),
            $this->userTable->reveal(),
            $this->router->reveal(),
            $this->auth->reveal(),
            $this->flash->reveal()
        );
    }

    public function testGet()
    {
        call_user_func($this->action, $this->makeRequest());
        $this->renderer->render('@account/signup')->shouldHaveBeenCalled();
    }

    public function testPostInvalid()
    {
        call_user_func($this->action, $this->makeRequest('/demo', [
            'username' => 'John Doe',
            'email' => 'akjjlkjlsa',
            'password' => '000',
            'password_confirm' => '0000'
        ]));
        //argument that isto kao kod this willReturn callback avec les mocs
        //tester d avoir appele method render avec les bons parameter
        $this->renderer->render('@account/signup', Argument::that(function ($params) {
            $this->assertArrayHasKey('errors', $params);
            $this->assertEquals(['email', 'password'], array_keys($params['errors']));

            return true;
        }))->shouldHaveBeenCalled();
    }

    public function testPostValid()
    {
        //ocekujemo da ce insert bit pozvat sa ova dva parametra
        $this->userTable->insert(Argument::that(function (array $userParams) {
            $this->assertArraySubset([
                'username' => 'John Doe',
//                'username' => 'Johnfdfd Doe',   <- ne prolazi
                'email' => 'john@doe.fr'
            ], $userParams);
            $this->assertTrue(password_verify('0000', $userParams['password']));
            return true;
        }))->shouldBeCalled();
        $this->auth->setUser(Argument::that(function (User $user) {
            $this->assertEquals('John Doe', $user->username);
            $this->assertEquals('john@doe.fr', $user->email);
            $this->assertEquals(3, $user->id);

            return true;
        }))->shouldBeCalled();
        $this->flash->success(Argument::type('string'))->shouldBeCalled();
        $response = call_user_func($this->action, $this->makeRequest('/demo', [
            'username' => 'John Doe',
            'email'    => 'john@doe.fr',
            'password' => '0000',
            'password_confirm' => '0000'
        ]));

        $this->renderer->render()->shouldNotBeCalled();
        $this->assertRedirect($response, 'account.profile');
    }

    public function testPostWithNoPassword()
    {
        call_user_func($this->action, $this->makeRequest('/demo', [
            'username' => 'John Doe',
            'email' => 'akjjlkjlsa',
            'password' => '',
            'password_confirm' => ''
        ]));
        $this->renderer->render('@account/signup', Argument::that(function ($params) {
            $this->assertArrayHasKey('errors', $params);
            $this->assertEquals(['email', 'password'], array_keys($params['errors']));

            return true;
        }))->shouldHaveBeenCalled();
    }
}