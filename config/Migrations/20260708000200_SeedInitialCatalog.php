<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class SeedInitialCatalog extends BaseMigration
{
    public function up(): void
    {
        $now = date('Y-m-d H:i:s');

        $this->table('templates')->insert([
            [
                'name' => 'Landing simple',
                'slug' => 'landing-simple',
                'description' => 'Página de una sola vista para presentar servicios, propuesta de valor y contacto.',
                'preview_image' => 'img/idea-portafolio-bg.png',
                'active' => true,
                'created' => $now,
                'modified' => $now,
            ],
            [
                'name' => 'Catálogo ligero',
                'slug' => 'catalogo-ligero',
                'description' => 'Base para negocios que quieren mostrar productos o servicios sin carrito avanzado.',
                'preview_image' => 'img/idea-servicios-bg.png',
                'active' => true,
                'created' => $now,
                'modified' => $now,
            ],
        ])->save();

        $this->table('themes')->insert([
            [
                'name' => 'CatOps naranja',
                'slug' => 'catops-naranja',
                'primary_color' => '#f36b16',
                'secondary_color' => '#0a2a66',
                'background_color' => '#fbfaf7',
                'font_family' => 'Inter, Arial, sans-serif',
                'active' => true,
                'created' => $now,
                'modified' => $now,
            ],
            [
                'name' => 'Claro profesional',
                'slug' => 'claro-profesional',
                'primary_color' => '#1d9a8a',
                'secondary_color' => '#102033',
                'background_color' => '#f8fbfa',
                'font_family' => 'Inter, Arial, sans-serif',
                'active' => true,
                'created' => $now,
                'modified' => $now,
            ],
        ])->save();
    }

    public function down(): void
    {
        $this->execute("DELETE FROM templates WHERE slug IN ('landing-simple', 'catalogo-ligero')");
        $this->execute("DELETE FROM themes WHERE slug IN ('catops-naranja', 'claro-profesional')");
    }
}
