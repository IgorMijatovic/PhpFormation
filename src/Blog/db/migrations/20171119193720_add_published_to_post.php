<?php

use Phinx\Migration\AbstractMigration;

class AddPublishedToPost extends AbstractMigration
{
    public function change()
    {
        $this->table('posts')->addColumn('published', 'boolean', ['default' => false])->update();
    }
}
