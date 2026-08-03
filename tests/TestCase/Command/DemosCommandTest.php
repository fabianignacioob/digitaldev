<?php
declare(strict_types=1);

namespace App\Test\TestCase\Command;

use Cake\Console\TestSuite\ConsoleIntegrationTestTrait;
use Cake\Datasource\FactoryLocator;
use Cake\TestSuite\TestCase;

class DemosCommandTest extends TestCase
{
    use ConsoleIntegrationTestTrait;

    protected function setUp(): void
    {
        parent::setUp();
        putenv('APP_PUBLIC_BASE_DOMAIN=vitrinahub.local');
        putenv('APP_PUBLIC_SCHEME=http');
        $this->ensurePrerequisites();
        $this->cleanDemoData();
    }

    protected function tearDown(): void
    {
        $this->cleanDemoData();
        putenv('APP_PUBLIC_BASE_DOMAIN');
        putenv('APP_PUBLIC_SCHEME');
        parent::tearDown();
    }

    public function testSeedCreatesPublishedDemosAndIsIdempotent(): void
    {
        $this->exec('demos seed');

        $this->assertExitSuccess();
        $this->assertOutputContains('demo-carta, demo-catalogo, demo-servicios');
        $owner = $this->table('Users')->find()->where(['email' => 'demos@catops.local'])->firstOrFail();
        $this->assertSame('active', $this->table('Subscriptions')->find()->where(['user_id' => $owner->id])->firstOrFail()->status);

        $sites = $this->table('Sites')->find()
            ->where(['subdomain IN' => ['demo-carta', 'demo-catalogo', 'demo-servicios']])
            ->contain(['Templates'])
            ->orderByAsc('subdomain')
            ->all()
            ->toList();
        $this->assertCount(3, $sites);
        $this->assertSame(['demo-carta', 'demo-catalogo', 'demo-servicios'], array_map(static fn (object $site): string => (string)$site->subdomain, $sites));
        $this->assertSame(['carta-categorias', 'catalogo-simple', 'catalogo-simple'], array_map(static fn (object $site): string => (string)$site->template->slug, $sites));
        foreach ($sites as $site) {
            $this->assertSame('published', $site->status);
            $this->assertNotEmpty($site->published_at);
            $this->assertSame(1, $this->table('Domains')->find()->where(['site_id' => $site->id, 'type' => 'subdomain'])->count());
        }
        $this->assertSame(2, $this->table('CatalogCategories')->find()->where(['site_id' => $sites[0]->id])->count());
        $this->assertSame(11, $this->table('CatalogProducts')->find()->where(['site_id' => $sites[0]->id])->count());
        $this->assertSame(10, $this->table('CatalogProducts')->find()->where(['site_id' => $sites[1]->id])->count());
        $this->assertSame(10, $this->table('CatalogProducts')->find()->where(['site_id' => $sites[2]->id, 'item_type' => 'service'])->count());

        $this->exec('demos seed');

        $this->assertExitSuccess();
        $this->assertSame(3, $this->table('Sites')->find()->where(['subdomain IN' => ['demo-carta', 'demo-catalogo', 'demo-servicios']])->count());
        $this->assertSame(11, $this->table('CatalogProducts')->find()->where(['site_id' => $sites[0]->id])->count());
    }

    private function cleanDemoData(): void
    {
        $this->table('AuditLogs')->deleteAll(['action' => 'demo.sites_seeded']);
        $users = $this->table('Users');
        $owner = $users->find()->where(['email' => 'demos@catops.local'])->first();
        if ($owner) {
            $users->delete($owner);
        }
    }

    private function ensurePrerequisites(): void
    {
        $templates = $this->table('Templates');
        foreach (['carta-categorias', 'catalogo-simple'] as $slug) {
            $template = $templates->find()->where(['slug' => $slug])->first();
            if (!$template) {
                $templates->saveOrFail($templates->newEntity([
                    'name' => ucfirst(str_replace('-', ' ', $slug)),
                    'slug' => $slug,
                    'active' => true,
                ]));
            }
        }

        $themes = $this->table('Themes');
        if (!$themes->find()->where(['slug' => 'catops-naranja'])->first()) {
            $themes->saveOrFail($themes->newEntity([
                'name' => 'CatOps naranja',
                'slug' => 'catops-naranja',
                'primary_color' => '#f36b16',
                'secondary_color' => '#0a2a66',
                'background_color' => '#fbfaf7',
                'active' => true,
            ]));
        }
    }

    private function table(string $alias): object
    {
        return FactoryLocator::get('Table')->get($alias);
    }
}
