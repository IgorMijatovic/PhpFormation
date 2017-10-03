<?= $renderer->render('header') ?>

    <h1>Bienvenu sur le blog</h1>
<ul>
    <li><a href="<?= $router->generateUri('blog.show', ['slug' => 'zazeze-7878']); ?>"</a>Article 1</li>
    <li>Article 2</li>
    <li>Article 3</li>
    <li>Article 4</li>
    <li>Article 5</li>
    <li>Article 6</li>
    <li>Article 7</li>
</ul>

<?= $renderer->render('footer') ?>