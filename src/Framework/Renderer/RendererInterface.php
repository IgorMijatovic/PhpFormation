<?php
namespace Framework\Renderer;

interface RendererInterface
{
    /**
     *
     * Permet de rajouter un hemin pour charger la vue
     * @param string $namespace
     * @param null|string|null $path
     */
    public function addPath(string $namespace, ?string $path = null): void;

    /**
     * Permet de rendre une vue
     * Le chemin peut  etre precise avec les namespace rajouter via addPath
     * $this->render('@blog/view');
     * $this->render('view');
     * @param string $view
     * @param array $params
     * @return string
     */
    public function render(string $view, array $params = []): string;

    /**
     * Permet de rajouer les variable globales a toutes les vue
     * @param string $key
     * @param mixed $value
     */
    public function addGlobal(string $key, $value): void;
}
