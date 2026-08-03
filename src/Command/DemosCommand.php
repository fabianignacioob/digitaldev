<?php
declare(strict_types=1);

namespace App\Command;

use App\Service\AuditLogService;
use App\Service\PublicUrlService;
use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\CommandFactory;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Datasource\FactoryLocator;
use Cake\I18n\DateTime;
use RuntimeException;

/**
 * Creates deterministic public showcases without coupling production data to a migration.
 */
class DemosCommand extends Command
{
    private const OWNER_EMAIL = 'demos@catops.local';

    /** @var array<string, array<string, mixed>> */
    private const SITES = [
        'demo-carta' => [
            'template' => 'carta-categorias',
            'name' => 'Casa Olivo',
            'slug' => 'casa-olivo-carta',
            'whatsapp' => '56961122334',
            'instagram' => 'casaolivo.cl',
            'address' => 'Av. Italia 1480, Providencia',
            'hours' => 'Martes a domingo, 13:00 a 23:00',
            'email' => 'hola@casaolivo.demo',
            'seo_title' => 'Casa Olivo | Carta de cocina italiana',
            'seo_description' => 'Carta digital de Casa Olivo: cocina italiana de temporada, pizzas artesanales y entradas para compartir.',
            'settings' => [
                'background_type' => 'image',
                'background_color' => '#f7f0e6',
                'background_image_path' => 'img/demos/restaurant-hero.jpg',
                'title' => 'Casa Olivo',
                'title_color' => '#ffffff',
                'title_font' => 'Georgia, serif',
                'heading_font' => 'Georgia, serif',
                'slogan' => 'Cocina italiana de temporada, hecha para compartir.',
                'slogan_color' => '#ffffff',
                'slogan_font' => 'Inter, Arial, sans-serif',
                'intro_text' => 'Ingredientes honestos, masa de fermentación lenta y una mesa siempre lista.',
                'category_layout' => 'blocks',
                'show_product_action' => true,
            ],
            'categories' => [
                'Entradas para compartir' => [
                    ['Focaccia de romero', 'Masa horneada al momento, aceite de oliva y sal de mar.', 4900, 'img/demos/restaurant-salad.jpg'],
                    ['Bruschettas de tomate', 'Pan tostado, tomates asados, albahaca fresca y ajo.', 5900, 'img/demos/restaurant-salad.jpg'],
                    ['Arancini de queso', 'Croquetas de risotto cremoso con salsa pomodoro.', 6500, 'img/demos/restaurant-pasta.jpg'],
                    ['Provoleta al horno', 'Queso fundido, orégano, aceitunas y pan de masa madre.', 7200, 'img/demos/restaurant-pizza.jpg'],
                    ['Tabla Casa Olivo', 'Selección de quesos, charcutería, frutas y focaccia.', 12900, 'img/demos/restaurant-dessert.jpg'],
                ],
                'Pizzas artesanales' => [
                    ['Margherita', 'Pomodoro, mozzarella fior di latte, albahaca y aceite de oliva.', 8990, 'img/demos/restaurant-pizza.jpg', 'Desde'],
                    ['Napolitana', 'Tomate, mozzarella, jamón, aceitunas y orégano.', 9490, 'img/demos/restaurant-pizza.jpg', 'Desde'],
                    ['Cuatro quesos', 'Mozzarella, parmesano, azul y queso de cabra.', 10900, 'img/demos/restaurant-pizza.jpg', 'Desde'],
                    ['Prosciutto e rucola', 'Jamón prosciutto, rúcula fresca, parmesano y limón.', 11500, 'img/demos/restaurant-pizza.jpg', 'Desde'],
                    ['Diavola', 'Salame picante, miel especiada y mozzarella.', 11200, 'img/demos/restaurant-pizza.jpg', 'Desde'],
                    ['Huerta de estación', 'Verduras asadas, pesto de albahaca y aceitunas.', 10200, 'img/demos/restaurant-pizza.jpg', 'Desde'],
                ],
            ],
        ],
        'demo-catalogo' => [
            'template' => 'catalogo-simple',
            'name' => 'Dulce Lila Pastelería',
            'slug' => 'dulce-lila-catalogo',
            'whatsapp' => '56972233445',
            'instagram' => 'dulcelila.pasteleria',
            'address' => 'Pedidos con 48 horas de anticipación, Santiago',
            'hours' => 'Lunes a sábado, 09:00 a 18:00',
            'email' => 'hola@dulcelila.demo',
            'seo_title' => 'Dulce Lila Pastelería | Tortas y queques artesanales',
            'seo_description' => 'Tortas, queques, cajas dulces y postres artesanales preparados a pedido por Dulce Lila Pastelería.',
            'settings' => [
                'background_type' => 'image',
                'background_color' => '#fff4f4',
                'background_image_path' => 'img/demos/bakery-hero.jpg',
                'title' => 'Dulce Lila Pastelería',
                'title_color' => '#ffffff',
                'title_font' => 'Georgia, serif',
                'heading_font' => 'Georgia, serif',
                'slogan' => 'Pastelería artesanal para celebrar cada momento.',
                'slogan_color' => '#ffffff',
                'slogan_font' => 'Inter, Arial, sans-serif',
                'intro_text' => 'Elige tu favorito y consulta disponibilidad para tu próxima celebración.',
                'category_layout' => 'normal',
                'show_product_action' => true,
            ],
            'products' => [
                ['Torta Red Velvet', 'Bizcocho suave de cacao, crema de queso y frutos rojos.', 28900, 'img/demos/bakery-cake.jpg', true],
                ['Torta de chocolate belga', 'Ganache intensa, bizcocho húmedo y láminas de chocolate.', 31500, 'img/demos/bakery-cake.jpg', true],
                ['Carrot cake', 'Zanahoria especiada, nueces y crema de queso artesanal.', 24900, 'img/demos/bakery-cake.jpg'],
                ['Cheesecake de berries', 'Base de galleta, queso crema y berries de temporada.', 23500, 'img/demos/bakery-cake.jpg'],
                ['Lemon pie', 'Masa crocante, crema de limón y merengue italiano.', 18500, 'img/demos/bakery-cake.jpg'],
                ['Queque de naranja', 'Queque húmedo con glaseado cítrico y almendras.', 11900, 'img/demos/bakery-brownies.jpg'],
                ['Caja de cupcakes', 'Seis cupcakes surtidos con buttercream y decoraciones.', 14900, 'img/demos/bakery-cupcakes.jpg'],
                ['Brownies de nuez', 'Caja de ocho brownies de cacao intenso y nueces.', 9900, 'img/demos/bakery-brownies.jpg'],
                ['Alfajores rellenos', 'Docena de alfajores con manjar, chocolate y coco.', 10500, 'img/demos/bakery-brownies.jpg'],
                ['Mini tortas individuales', 'Cuatro mini tortas surtidas para regalar o compartir.', 16800, 'img/demos/bakery-cupcakes.jpg'],
            ],
        ],
        'demo-servicios' => [
            'template' => 'catalogo-simple',
            'name' => 'Ruta Norte Transfers',
            'slug' => 'ruta-norte-servicios',
            'whatsapp' => '56983344556',
            'instagram' => 'rutanorte.transfers',
            'address' => 'Cobertura en Santiago y alrededores',
            'hours' => 'Atención todos los días, 24 horas',
            'email' => 'reservas@rutanorte.demo',
            'seo_title' => 'Ruta Norte Transfers | Traslados privados y corporativos',
            'seo_description' => 'Servicios de traslado privado, ejecutivo, corporativo y turístico en Santiago y la zona central.',
            'settings' => [
                'background_type' => 'image',
                'background_color' => '#edf4fb',
                'background_image_path' => 'img/demos/transport-hero.jpg',
                'title' => 'Ruta Norte Transfers',
                'title_color' => '#ffffff',
                'title_font' => 'Arial, sans-serif',
                'heading_font' => 'Arial, sans-serif',
                'slogan' => 'Traslados cómodos, puntuales y coordinados contigo.',
                'slogan_color' => '#ffffff',
                'slogan_font' => 'Inter, Arial, sans-serif',
                'intro_text' => 'Cotiza traslados privados, corporativos o turísticos por WhatsApp.',
                'category_layout' => 'normal',
                'show_product_action' => true,
            ],
            'services' => [
                ['Transfer aeropuerto - Santiago', 'Traslado puerta a puerta desde o hacia el aeropuerto, hasta 3 pasajeros.', 26000, '45 min aprox.', 'img/demos/transport-sedan.jpg'],
                ['Transfer aeropuerto - VIP', 'Servicio ejecutivo con recepción, espera coordinada y vehículo premium.', 48000, '45 min aprox.', 'img/demos/transport-sedan.jpg'],
                ['Transfer aeropuerto - grupos', 'Van privada para familias, delegaciones o equipaje adicional.', 62000, '60 min aprox.', 'img/demos/transport-van.jpg'],
                ['Traslado ejecutivo por hora', 'Disponibilidad para reuniones, visitas y agenda ejecutiva.', 35000, 'Por hora', 'img/demos/transport-sedan.jpg'],
                ['Transporte corporativo', 'Rutas programadas para equipos, turnos y visitas de empresa.', 85000, 'Media jornada', 'img/demos/transport-van.jpg'],
                ['Traslado a eventos', 'Llegada y regreso coordinado para matrimonios, conciertos o cenas.', 39000, 'Ida y vuelta', 'img/demos/transport-sedan.jpg'],
                ['Tour viñas del Valle', 'Recorrido privado por viñas de la zona central con horario flexible.', 145000, 'Jornada completa', 'img/demos/transport-van.jpg'],
                ['Paseo costero', 'Salida privada a Valparaíso y Viña del Mar, con paradas a elección.', 135000, 'Jornada completa', 'img/demos/transport-hero.jpg'],
                ['Traslado interurbano', 'Viajes privados a ciudades de la zona central y norte chico.', 69000, 'Según destino', 'img/demos/transport-hero.jpg'],
                ['Van para delegaciones', 'Movilización para grupos, congresos, hoteles o actividades especiales.', 98000, 'Desde 4 horas', 'img/demos/transport-van.jpg'],
            ],
        ],
    ];

    public function __construct(?CommandFactory $factory = null)
    {
        parent::__construct($factory);
    }

    protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        return $parser
            ->setDescription('Crea o actualiza las vitrinas públicas de demostración de CatOps.')
            ->addArgument('action', [
                'help' => 'Acción a ejecutar.',
                'choices' => ['seed'],
                'required' => true,
            ]);
    }

    public function execute(Arguments $args, ConsoleIo $io): int
    {
        if ((string)$args->getArgument('action') !== 'seed') {
            return self::CODE_ERROR;
        }

        $connection = $this->table('Sites')->getConnection();
        $owner = $connection->transactional(function () {
            $owner = $this->ensureOwner();
            $this->ensureSubscription((int)$owner->id);

            foreach (self::SITES as $subdomain => $definition) {
                $this->seedSite($owner, $subdomain, $definition);
            }

            return $owner;
        });

        (new AuditLogService())->log((int)$owner->id, 'demo.sites_seeded', 'users', (int)$owner->id, [
            'subdomains' => array_keys(self::SITES),
        ]);

        $io->success('Demos creadas o actualizadas: ' . implode(', ', array_keys(self::SITES)) . '.');
        foreach (array_keys(self::SITES) as $subdomain) {
            $io->out((new PublicUrlService())->scheme() . '://' . (new PublicUrlService())->hostForSubdomain($subdomain));
        }

        return self::CODE_SUCCESS;
    }

    private function ensureOwner(): object
    {
        $users = $this->table('Users');
        $owner = $users->find()->where(['email' => self::OWNER_EMAIL])->first();
        $data = [
            'name' => 'CatOps Demos',
            'email' => self::OWNER_EMAIL,
            'role' => 'user',
            'active' => true,
            'email_verified' => true,
        ];
        if (!$owner) {
            $data['password'] = bin2hex(random_bytes(24));
            $owner = $users->newEntity($data);
        } else {
            $owner = $users->patchEntity($owner, $data);
        }

        return $users->saveOrFail($owner);
    }

    private function ensureSubscription(int $userId): void
    {
        $subscriptions = $this->table('Subscriptions');
        $subscription = $subscriptions->find()->where(['user_id' => $userId])->orderByDesc('id')->first();
        $now = DateTime::now();
        $data = [
            'user_id' => $userId,
            'plan_slug' => 'full',
            'billing_cycle' => 'monthly',
            'status' => 'active',
            'starts_at' => $now,
            'ends_at' => (clone $now)->modify('+1 year'),
            'grace_ends_at' => null,
            'notes' => 'Suscripción técnica para vitrinas de demostración.',
        ];
        $subscription = $subscription
            ? $subscriptions->patchEntity($subscription, $data)
            : $subscriptions->newEntity($data);
        $subscriptions->saveOrFail($subscription);
    }

    /** @param array<string, mixed> $definition */
    private function seedSite(object $owner, string $subdomain, array $definition): void
    {
        $template = $this->table('Templates')->find()
            ->where(['slug' => (string)$definition['template'], 'active' => true])
            ->first();
        if (!$template) {
            throw new RuntimeException('Falta la plantilla requerida para la demo: ' . $definition['template']);
        }
        $theme = $this->table('Themes')->find()->where(['slug' => 'catops-naranja', 'active' => true])->first();
        if (!$theme) {
            throw new RuntimeException('Falta el tema CatOps naranja requerido para las demos.');
        }

        $sites = $this->table('Sites');
        $site = $sites->find()->where(['subdomain' => $subdomain])->first();
        if ($site && (int)$site->user_id !== (int)$owner->id) {
            throw new RuntimeException('El subdominio de demo ' . $subdomain . ' ya pertenece a otra cuenta.');
        }
        $data = [
            'user_id' => (int)$owner->id,
            'template_id' => (int)$template->id,
            'theme_id' => (int)$theme->id,
            'name' => (string)$definition['name'],
            'slug' => (string)$definition['slug'],
            'subdomain' => $subdomain,
            'status' => 'published',
            'paused_reason' => null,
            'published_at' => DateTime::now(),
            'whatsapp_country_code' => '56',
            'whatsapp_number' => substr((string)$definition['whatsapp'], 2),
            'show_whatsapp' => true,
            'instagram_username' => (string)$definition['instagram'],
            'show_instagram' => true,
            'business_address' => (string)$definition['address'],
            'business_hours' => (string)$definition['hours'],
            'public_phone' => '+' . (string)$definition['whatsapp'],
            'public_email' => (string)$definition['email'],
            'seo_title' => (string)$definition['seo_title'],
            'seo_description' => (string)$definition['seo_description'],
        ];
        $site = $site ? $sites->patchEntity($site, $data) : $sites->newEntity($data);
        $site = $sites->saveOrFail($site);

        $this->ensureSubdomainDomain((int)$site->id, $subdomain);
        $this->upsertSettings((int)$site->id, (array)$definition['settings']);
        $this->replaceCatalogContent((int)$site->id, $definition);
    }

    private function ensureSubdomainDomain(int $siteId, string $subdomain): void
    {
        $domains = $this->table('Domains');
        $hostname = (new PublicUrlService())->hostForSubdomain($subdomain);
        $conflict = $domains->find()->where(['domain' => $hostname])->first();
        if ($conflict && (int)$conflict->site_id !== $siteId) {
            throw new RuntimeException('El dominio de demo ' . $hostname . ' ya pertenece a otra vitrina.');
        }
        $domain = $domains->find()->where(['site_id' => $siteId, 'type' => 'subdomain'])->first();
        $data = [
            'site_id' => $siteId,
            'domain' => $hostname,
            'type' => 'subdomain',
            'verified' => true,
            'active' => true,
        ];
        $domain = $domain ? $domains->patchEntity($domain, $data) : $domains->newEntity($data);
        $domains->saveOrFail($domain);
    }

    /** @param array<string, mixed> $settings */
    private function upsertSettings(int $siteId, array $settings): void
    {
        $table = $this->table('CatalogSettings');
        $setting = $table->find()->where(['site_id' => $siteId])->first();
        $settings['site_id'] = $siteId;
        $setting = $setting ? $table->patchEntity($setting, $settings) : $table->newEntity($settings);
        $table->saveOrFail($setting);
    }

    /** @param array<string, mixed> $definition */
    private function replaceCatalogContent(int $siteId, array $definition): void
    {
        $products = $this->table('CatalogProducts');
        $categories = $this->table('CatalogCategories');
        $products->deleteAll(['site_id' => $siteId]);
        $categories->deleteAll(['site_id' => $siteId]);

        if (isset($definition['categories'])) {
            $this->seedMenuCategories($siteId, (array)$definition['categories']);

            return;
        }

        $items = (array)($definition['products'] ?? $definition['services'] ?? []);
        $itemType = isset($definition['services']) ? 'service' : 'product';
        foreach ($items as $index => $item) {
            $products->saveOrFail($products->newEntity($this->simpleItemData($siteId, $item, $itemType, $index + 1)));
        }
    }

    /** @param array<string, list<array<int, mixed>>> $categories */
    private function seedMenuCategories(int $siteId, array $categories): void
    {
        $categoriesTable = $this->table('CatalogCategories');
        $products = $this->table('CatalogProducts');
        $categoryPosition = 0;
        $productPosition = 0;
        foreach ($categories as $name => $items) {
            $categoryPosition++;
            $category = $categoriesTable->saveOrFail($categoriesTable->newEntity([
                'site_id' => $siteId,
                'name' => $name,
                'sort_order' => $categoryPosition,
            ]));
            foreach ($items as $item) {
                $productPosition++;
                $products->saveOrFail($products->newEntity([
                    'site_id' => $siteId,
                    'catalog_category_id' => (int)$category->id,
                    'image_path' => $item[3],
                    'item_type' => 'menu_item',
                    'name' => $item[0],
                    'description' => $item[1],
                    'price' => $item[2],
                    'price_prefix' => $item[4] ?? null,
                    'featured' => $item[0] === 'Margherita',
                    'active' => true,
                    'availability' => 'available',
                    'sort_order' => $productPosition,
                ]));
            }
        }
    }

    /** @param array<int, mixed> $item
     * @return array<string, mixed>
     */
    private function simpleItemData(int $siteId, array $item, string $itemType, int $position): array
    {
        $isService = $itemType === 'service';

        return [
            'site_id' => $siteId,
            'image_path' => $isService ? $item[4] : $item[3],
            'item_type' => $itemType,
            'name' => $item[0],
            'description' => $item[1],
            'price' => $item[2],
            'price_prefix' => $isService ? 'Desde' : null,
            'duration' => $isService ? $item[3] : null,
            'featured' => (bool)($item[4] ?? false) && !$isService,
            'active' => true,
            'availability' => 'available',
            'sort_order' => $position,
        ];
    }

    private function table(string $alias): object
    {
        return FactoryLocator::get('Table')->get($alias);
    }
}
