<?php
namespace Framework\Actions;

use Framework\Database\Hydrator;
use Framework\Database\Table;
use Framework\Renderer\RendererInterface;
use Framework\Router;
use Framework\Session\FlashService;
use Framework\Validator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;

class CrudAction
{
    /**
     * @var string
     */
    protected $viewPath;
    /**
     * @var string
     */
    protected $routePrefix;
    /**
     * @var string
     */
    protected $messages = [
        'create' => "L'element a bien ete cree",
        'edit'  => "L'element a bien ete modifie"
        ];
    /**
     * @var RendererInterface
     */
    private $renderer;
    /**
     * @var Table
     */
    protected $table;
    /**
     * @var Router
     */
    private $router;

    /**
     * @var FlashService
     */
    private $flash;

    use RouterAwareAction;

    public function __construct(
        RendererInterface $renderer,
        Table $table,
        Router $router,
        FlashService $flash
    ) {
        $this->renderer = $renderer;
        $this->router = $router;
        $this->table = $table;
        $this->flash = $flash;
    }

    public function __invoke(Request $request)
    {
        $this->renderer->addGlobal('viewPath', $this->viewPath);
        $this->renderer->addGlobal('routePrefix', $this->routePrefix);
        if ($request->getMethod() === 'DELETE') {
            return $this->delete($request);
        }
        if (substr((string)$request->getUri(), -3) === 'new') {
            return $this->create($request);
        }
        if ($request->getAttribute('id')) {
            return $this->edit($request);
        }

        return $this->index($request);
    }

    /**
     * Affiche la liste des elements
     * @param Request $request
     * @return string
     */
    public function index(Request $request): string
    {
        $params = $request->getQueryParams();

        $items = $this->table->findAll()->paginate(12, $params['p'] ?? 1);

        return $this->renderer->render($this->viewPath . '/index', compact('items'));
    }

    /**
     * Edit un element
     * @param Request $request
     * @return ResponseInterface|string
     */
    public function edit(Request $request)
    {
        $item = $this->table->find($request->getAttribute('id'));

        if ($request->getMethod() === 'POST') {
            $validator = $this->getValidator($request);
            if ($validator->isValid()) {
                $this->table->update($item->id, $this->getParams($request, $item));
                $this->flash->success($this->messages['edit']);

                return $this->redirect($this->routePrefix . '.index');
            }
            $errors = $validator->getErrors();
            Hydrator::hydrate($request->getParsedBody(), $item);
        }

        return $this->renderer->render(
            $this->viewPath . '/edit',
            $this->formParams(compact('item', 'errors'))
        );
    }

    /**
     * Creer un nouvelle element
     * @param Request $request
     * @return ResponseInterface|string
     */
    public function create(Request $request)
    {
        $item = $this->getNewEntity();
        if ($request->getMethod() === 'POST') {
            $validator = $this->getValidator($request);
            if ($validator->isValid()) {
                $this->table->insert($this->getParams($request, $item));
                $this->flash->success($this->messages['create']);

                return $this->redirect($this->routePrefix . '.index');
            }
            $errors = $validator->getErrors();
            Hydrator::hydrate($request->getParsedBody(), $item);
        }

        return $this->renderer->render(
            $this->viewPath . '/create',
            $this->formParams(compact('item', 'errors'))
        );
    }

    protected function getParams(Request $request, $item): array
    {

        return array_filter($request->getParsedBody(), function ($key) {
            return in_array($key, []);
        }, ARRAY_FILTER_USE_KEY);
    }

    /**
     * @param Request $request
     * @return ResponseInterface
     */
    protected function delete(Request $request)
    {
        $this->table->delete($request->getAttribute('id'));

        return $this->redirect($this->routePrefix . '.index');
    }

    /**
     * @param Request $request
     * @return Validator
     */
    protected function getValidator(Request $request)
    {

        return new Validator(array_merge($request->getParsedBody(), $request->getUploadedFiles()));
    }

    /**
     * @return array
     */
    protected function getNewEntity()
    {

        return [];
    }

    /**
     * Permet de traiter les parametres a envoyer
     * a la vue
     * @param $params
     * @return array
     */
    protected function formParams(array $params): array
    {

        return $params;
    }
}
