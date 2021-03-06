<?php
namespace App\Blog\Actions;

use App\Blog\Entity\Post;
use App\Blog\Table\CategoryTable;
use Framework\Actions\CrudAction;
use Framework\Renderer\RendererInterface;
use Framework\Router;
use Framework\Session\FlashService;
use Psr\Http\Message\ServerRequestInterface;

class CategoryCrudAction extends CrudAction
{
    protected $viewPath = "@blog/admin/categories";

    protected $routePrefix = "blog.category.admin";

    protected $acceptedParams = ['name', 'slug'];

    public function __construct(RendererInterface $renderer, CategoryTable $table, Router $router, FlashService $flash)
    {
        parent::__construct($renderer, $table, $router, $flash);
    }

    protected function getValidator(ServerRequestInterface $request)
    {

        return parent::getValidator($request)
            ->required('slug', 'name')
            ->length('name', 2, 250)
            ->length('slug', 2, 50)
            ->unique('slug', $this->table->getTable(), $this->table->getPdo(), $request->getAttribute('id'))
            ->slug('slug');
    }
}
