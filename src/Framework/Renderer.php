<?php
namespace Framework;

class Renderer
{
    const DEFAULT_NAMESPACE = '__MAIN';

    private $paths = [];
    /**
     * Variables acccessible globalemet pour toutes les vues
     * @var array
     */
    private $globals = [];

    /**
     *
     * Permet de rajouter un hemin pour charger la vue
     * @param string $namespace
     * @param null|string|null $path
     */
    public function addPath(string $namespace, ?string $path = null): void
    {
        if (is_null($path)) {
            $this->paths[self::DEFAULT_NAMESPACE] = $namespace;
        } else {
            $this->paths[$namespace] = $path;
        }
    }

    /**
     * Permet de rendre une vue
     * Le chemin peut  etre precise avec les namespace rajouter via addPath
     * $this->render('@blog/view');
     * $this->render('view');
     * @param string $view
     * @param array $params
     * @return string
     */
    public function render(string $view, array $params = []): string
    {
        if ($this->hasNamespace($view)) {
            $path = $this->replaceNamespace($view) . '.php';
        } else {
            $path = $this->paths[self::DEFAULT_NAMESPACE] . DIRECTORY_SEPARATOR . $view . '.php';
        }

        ob_start();
        $renderer =$this;
        extract($this->globals);
        extract($params);
        require($path);

        return ob_get_clean();
    }

    /**
     * Permet de rajouer les variable globales a toutes les vue
     * @param string $key
     * @param mixed $value
     */
    public function addGlobal(string $key, $value): void
    {
        $this->globals[$key] = $value;
    }

    private function hasNamespace(string $view): bool
    {

        return $view[0] === '@';
    }

    private function getNameSpace(string $view): string
    {

        return substr($view, 1, strpos($view, '/') - 1);
    }

    private function replaceNamespace(string $view)
    {
        $namespace = $this->getNameSpace($view);

        return str_replace('@' . $namespace, $this->paths[$namespace], $view);
    }
}
