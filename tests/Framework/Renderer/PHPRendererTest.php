<?php
namespace Tests\Framework\Renderer;

use Framework\Renderer\PHPRenderer;
use PHPUnit\Framework\TestCase;

class PHPRendererTest extends TestCase
{
    private $renderer;

    public function setUp()
    {
        $this->renderer = new PHPRenderer(__DIR__ . '/views');
    }

    public function testRendererTheRightPath()
    {
        $this->renderer->addPath('blog', __DIR__ . '/views');
        $content = $this->renderer->render('@blog/demo');
        $this->assertEquals('Salut les geans', $content);
    }

    public function testRendererDefaultPath()
    {
        $content = $this->renderer->render('demo');
        $this->assertEquals('Salut les geans', $content);
    }

    public function testRendererWithParams()
    {
        $content = $this->renderer->render('demoparams', [
            'nom' => 'Marc'
        ]);
        $this->assertEquals('Salut Marc', $content);
    }

    public function testGlobalParams()
    {
        $this->renderer->addGlobal('nom', 'Marc');
        $content = $this->renderer->render('demoparams');
        $this->assertEquals('Salut Marc', $content);
    }

}