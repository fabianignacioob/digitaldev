<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class SeedCatalogTemplate extends BaseMigration
{
    public function up(): void
    {
        $now = date('Y-m-d H:i:s');

        $this->table('templates')->insert([
            [
                'name' => 'Carta simple',
                'slug' => 'carta-simple',
                'description' => 'Carta para restaurantes con fondo personalizable, categorías y productos con foto, valor y descuento opcional.',
                'preview_image' => 'img/idea-servicios-bg.png',
                'active' => true,
                'created' => $now,
                'modified' => $now,
            ],
        ])->save();
    }

    public function down(): void
    {
        $this->execute("DELETE FROM templates WHERE slug = 'carta-simple'");
    }
}
