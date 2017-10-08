<?php

namespace Framework\Renderer;

use Framework\Router\RouterTwigExtension;
use Psr\Container\ContainerInterface;
use Twig\Extension\DebugExtension;

class TwigRendererFactory
{
    public function __invoke(ContainerInterface $container): TwigRenderer
    {
        $loader = new \Twig_Loader_Filesystem($container->get('views.path'));
        $twig = new \Twig_Environment($loader, ['debug' => true]);
        $twig->addExtension(new DebugExtension());
        if ($container->has('twig.extensions')) {
            foreach ($container->get('twig.extensions') as $extension) {
                $twig->addExtension($extension);
            }
        }
//        $twig->addExtension($container->get(RouterTwigExtension::class));   sans config
        return new TwigRenderer($twig);
    }
}
