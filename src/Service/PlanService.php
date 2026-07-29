<?php

declare(strict_types=1);

namespace App\Service;

use Cake\Datasource\FactoryLocator;
use Cake\I18n\DateTime;
use Cake\ORM\Table;

class PlanService
{
    private const TEMPLATE_TYPES = [
        'carta-simple' => [
            'kind' => 'carta',
            'categories' => false,
            'item_type' => 'menu_item',
        ],
        'carta-categorias' => [
            'kind' => 'carta',
            'categories' => true,
            'item_type' => 'menu_item',
        ],
        'catalogo-simple' => [
            'kind' => 'catalogo',
            'categories' => false,
            'item_type' => 'product',
        ],
        'catalogo-categorias' => [
            'kind' => 'catalogo',
            'categories' => true,
            'item_type' => 'product',
        ],
        'landing-simple' => [
            'kind' => 'landing',
            'categories' => false,
            'item_type' => null,
        ],
    ];

    public const DEFAULT_CAPABILITIES = [
        'sites_configured_limit' => 0,
        'sites_published_limit' => 0,
        'items_limit' => 0,
        'categories_limit' => 0,
        'image_storage_limit_mb' => 0,
        'trial_duration_days' => 0,
        'trial_expire_after_registration_days' => 0,
        'custom_domains_limit' => 0,
        'annual_price' => 0,
        'categories_enabled' => false,
        'whatsapp_enabled' => false,
        'featured_items_enabled' => false,
        'customization_level' => 'none',
        'analytics_level' => 'none',
        'seo_level' => 'none',
        'qr_enabled' => false,
        'custom_domain_enabled' => false,
        'premium_themes_enabled' => false,
        'catops_branding_removable' => false,
        'priority_support' => false,
        'trial_enabled' => false,
        'domain_credit' => false,
        'annual_available' => false,
        'branding_removable' => false,
        // Legacy keys remain false by default. They are mapped only when a legacy
        // plan explicitly stored a truthy value for the capability.
        'custom_domain' => false,
        'premium_themes' => false,
        'advanced_customization' => false,
        'seo_advanced' => false,
        'analytics' => false,
        'ai_item_descriptions' => false,
        'ai_landing_builder' => false,
        'enabled_templates' => [],
    ];

    private const BOOLEAN_CAPABILITIES = [
        'categories_enabled',
        'whatsapp_enabled',
        'featured_items_enabled',
        'qr_enabled',
        'custom_domain_enabled',
        'premium_themes_enabled',
        'catops_branding_removable',
        'priority_support',
        'trial_enabled',
        'domain_credit',
        'annual_available',
        'branding_removable',
        'custom_domain',
        'premium_themes',
        'advanced_customization',
        'seo_advanced',
        'analytics',
        'ai_item_descriptions',
        'ai_landing_builder',
    ];

    private const INTEGER_CAPABILITIES = [
        'sites_configured_limit',
        'sites_published_limit',
        'items_limit',
        'categories_limit',
        'image_storage_limit_mb',
        'trial_duration_days',
        'trial_expire_after_registration_days',
        'custom_domains_limit',
        'annual_price',
    ];

    private const ENUM_CAPABILITIES = [
        'customization_level' => ['none', 'basic', 'extended', 'advanced'],
        'analytics_level' => ['none', 'basic', 'advanced'],
        'seo_level' => ['none', 'basic', 'standard', 'advanced'],
    ];

    private const RESERVED_SUBDOMAINS = [
        'admin',
        'api',
        'app',
        'assets',
        'catops',
        'dashboard',
        'help',
        'login',
        'mail',
        'panel',
        'registro',
        'root',
        'servicio',
        'static',
        'support',
        'www',
    ];

    public function getCurrentSubscription(int $userId): ?object
    {
        return (new SubscriptionService())->getCurrentSubscription($userId);
    }

    public function getPlanForUser(int $userId): ?object
    {
        $subscription = $this->getCurrentSubscription($userId);

        return $subscription ? $this->getPlanBySlug((string)$subscription->plan_slug) : null;
    }

    public function getPlanBySlug(string $slug): ?object
    {
        $plan = $this->table('Plans')->find()
            ->where(['slug' => $slug, 'active' => true])
            ->first();

        return $plan ?: null;
    }

    public function trialPlan(): ?object
    {
        foreach ($this->table('Plans')->find()->where(['active' => true])->orderByAsc('sort_order') as $plan) {
            if ($this->isTrialPlan($plan)) {
                return $plan;
            }
        }

        return null;
    }

    public function isTrialPlan(object $plan): bool
    {
        return (bool)$this->capabilities($plan)['trial_enabled'];
    }

    public function annualPrice(object $plan): ?int
    {
        $price = $plan->annual_price ?? $this->capabilities($plan)['annual_price'];

        return is_numeric($price) && (int)$price > 0 ? (int)$price : null;
    }

    /** @return array<string, bool> */
    public function annualBenefits(object $plan): array
    {
        $benefits = $plan->annual_benefits ?? [];
        if (is_string($benefits)) {
            $benefits = json_decode($benefits, true);
        }

        try {
            return $this->validateAnnualBenefits(is_array($benefits) ? $benefits : []);
        } catch (\InvalidArgumentException) {
            return ['domain_credit' => false];
        }
    }

    /** @param array<string, mixed> $benefits @return array<string, bool> */
    public function validateAnnualBenefits(array $benefits): array
    {
        $normalized = ['domain_credit' => false];
        foreach ($normalized as $key => $default) {
            $value = $benefits[$key] ?? $default;
            if (!in_array($value, [true, false, 1, 0, '1', '0', null], true)) {
                throw new \InvalidArgumentException('El beneficio anual ' . $key . ' debe ser booleano.');
            }
            $normalized[$key] = $value === true || $value === 1 || $value === '1';
        }

        return $normalized;
    }

    public function getCapabilitiesForUser(int $userId): array
    {
        $plan = $this->getPlanForUser($userId);

        return $plan ? $this->capabilities($plan) : self::DEFAULT_CAPABILITIES;
    }

    public function hasFeature(int|array|object $user, string $feature): bool
    {
        $capabilities = $this->getCapabilitiesForUser($this->userId($user));

        return (bool)($capabilities[$feature] ?? false);
    }

    public function getLimit(int|array|object $user, string $limit): int
    {
        $capabilities = $this->getCapabilitiesForUser($this->userId($user));

        return (int)($capabilities[$limit] ?? 0);
    }

    public function canCreateSite(int|array|object $user): bool
    {
        $userId = $this->userId($user);
        if (!$this->getCurrentSubscription($userId)) {
            return false;
        }

        $limit = $this->getLimit($userId, 'sites_configured_limit');
        $count = $this->table('Sites')->find()
            ->where(['user_id' => $userId])
            ->count();

        return $limit > 0 && $count < $limit;
    }

    public function canPublishSite(int|array|object $user, int|object $site): bool
    {
        $userId = $this->userId($user);
        $siteId = is_object($site) ? (int)$site->id : (int)$site;
        if (!$this->getCurrentSubscription($userId)) {
            return false;
        }

        $usage = $this->siteUsage($userId);
        $siteStatus = is_object($site) ? (string)($site->status ?? '') : (string)($this->table('Sites')->find()
            ->select(['status'])
            ->where(['id' => $siteId, 'user_id' => $userId])
            ->first()?->status ?? '');
        // A lower plan must not unpublish a site the customer already had.
        if ($siteStatus === 'published') {
            return true;
        }
        if ($usage['configured_over_limit']) {
            return false;
        }

        $limit = $usage['published_limit'];
        $count = $usage['published'];

        return $limit > 0 && $count < $limit;
    }

    /** @return array{configured:int,published:int,configured_limit:int,published_limit:int,configured_over_limit:bool,published_over_limit:bool,over_limit:bool} */
    public function siteUsage(int|array|object $user): array
    {
        $userId = $this->userId($user);
        $configuredLimit = $this->getLimit($userId, 'sites_configured_limit');
        $publishedLimit = $this->getLimit($userId, 'sites_published_limit');
        $configured = $this->table('Sites')->find()->where(['user_id' => $userId])->count();
        $published = $this->table('Sites')->find()
            ->where(['user_id' => $userId, 'status' => 'published'])
            ->count();

        $configuredOverLimit = $configuredLimit > 0 && $configured > $configuredLimit;
        $publishedOverLimit = $publishedLimit > 0 && $published > $publishedLimit;

        return [
            'configured' => $configured,
            'published' => $published,
            'configured_limit' => $configuredLimit,
            'published_limit' => $publishedLimit,
            'configured_over_limit' => $configuredOverLimit,
            'published_over_limit' => $publishedOverLimit,
            'over_limit' => $configuredOverLimit || $publishedOverLimit,
        ];
    }

    public function canCreateCatalogItem(int|array|object $user, int|object $site): bool
    {
        $userId = $this->userId($user);
        if (!$this->getCurrentSubscription($userId)) {
            return false;
        }

        $siteId = is_object($site) ? (int)$site->id : (int)$site;
        $limit = $this->getLimit($userId, 'items_limit');
        $count = $this->table('CatalogProducts')->find()
            ->where(['site_id' => $siteId])
            ->count();

        return $limit > 0 && $count < $limit;
    }

    public function canUseCategories(int|array|object $user, string|object|null $template): bool
    {
        return $this->hasFeature($user, 'categories_enabled') && $this->templateSupportsCategories($template);
    }

    public function canCreateCategory(int|array|object $user, int|object $site): bool
    {
        $siteId = is_object($site) ? (int)$site->id : (int)$site;
        if (!$this->canUseCategories($user, $site)) {
            return false;
        }

        $limit = $this->getLimit($user, 'categories_limit');
        $count = $this->table('CatalogCategories')->find()->where(['site_id' => $siteId])->count();

        return $limit > 0 && $count < $limit;
    }

    public function allowedTemplateSlugs(int|array|object $user): array
    {
        $capabilities = $this->getCapabilitiesForUser($this->userId($user));

        return (array)($capabilities['enabled_templates'] ?? []);
    }

    public function templateIsAllowed(int|array|object $user, string $templateSlug): bool
    {
        return in_array($templateSlug, $this->allowedTemplateSlugs($user), true);
    }

    public function templateKind(string|object|null $template): string
    {
        $slug = $this->templateSlug($template);

        return self::TEMPLATE_TYPES[$slug]['kind'] ?? 'servicios';
    }

    /**
     * Selects the public product presentation without relying on labels or site names.
     */
    public function publicProductPresentation(string|object|null $template): string
    {
        return $this->templateKind($template) === 'carta' ? 'menu' : 'catalog';
    }

    public function templateSupportsCategories(string|object|null $template): bool
    {
        $slug = $this->templateSlug($template);

        return (bool)(self::TEMPLATE_TYPES[$slug]['categories'] ?? false);
    }

    public function defaultItemTypeForTemplate(string|object|null $template): string
    {
        $slug = $this->templateSlug($template);

        return (string)(self::TEMPLATE_TYPES[$slug]['item_type'] ?? 'service');
    }

    public function validItemTypesForTemplate(string|object|null $template): array
    {
        return match ($this->templateKind($template)) {
            'carta' => ['menu_item'],
            'catalogo' => ['product', 'service'],
            'servicios' => ['service'],
            default => ['product'],
        };
    }

    public function reservedSubdomains(): array
    {
        return self::RESERVED_SUBDOMAINS;
    }

    public function isReservedSubdomain(string $subdomain): bool
    {
        return in_array(strtolower($subdomain), self::RESERVED_SUBDOMAINS, true);
    }

    public function capabilities(object $plan): array
    {
        $capabilities = $plan->capabilities ?? [];
        if (is_string($capabilities)) {
            $decoded = json_decode($capabilities, true);
            $capabilities = is_array($decoded) ? $decoded : [];
        }

        return $this->normalizeCapabilities((array)$capabilities, $plan);
    }

    /**
     * Normalizes stored plan data defensively. A missing or malformed value never
     * unlocks a product capability.
     *
     * @param array<string, mixed> $capabilities
     * @return array<string, mixed>
     */
    public function normalizeCapabilities(array $capabilities, ?object $plan = null): array
    {
        $normalized = self::DEFAULT_CAPABILITIES;
        foreach (self::INTEGER_CAPABILITIES as $key) {
            if (array_key_exists($key, $capabilities) && is_int($capabilities[$key]) && $capabilities[$key] >= 0) {
                $normalized[$key] = $capabilities[$key];
            }
        }
        if (!array_key_exists('sites_configured_limit', $capabilities) && $plan && isset($plan->max_sites)) {
            $normalized['sites_configured_limit'] = max(0, (int)$plan->max_sites);
        }
        if (!array_key_exists('sites_published_limit', $capabilities) && $plan && isset($plan->max_published)) {
            $normalized['sites_published_limit'] = max(0, (int)$plan->max_published);
        }
        foreach (self::BOOLEAN_CAPABILITIES as $key) {
            if (array_key_exists($key, $capabilities)) {
                $normalized[$key] = $capabilities[$key] === true || $capabilities[$key] === 1 || $capabilities[$key] === '1';
            }
        }
        foreach (self::ENUM_CAPABILITIES as $key => $allowedValues) {
            if (isset($capabilities[$key]) && is_string($capabilities[$key]) && in_array($capabilities[$key], $allowedValues, true)) {
                $normalized[$key] = $capabilities[$key];
            }
        }
        if (isset($capabilities['enabled_templates']) && is_array($capabilities['enabled_templates'])) {
            $normalized['enabled_templates'] = array_values(array_unique(array_filter(
                $capabilities['enabled_templates'],
                static fn (mixed $slug): bool => is_string($slug) && $slug !== '',
            )));
        }

        // Existing records created before the new schema keep their explicit value.
        $normalized['custom_domain_enabled'] = $normalized['custom_domain_enabled'] || $normalized['custom_domain'];
        $normalized['premium_themes_enabled'] = $normalized['premium_themes_enabled'] || $normalized['premium_themes'];
        $normalized['customization_level'] = $normalized['customization_level'] === 'none' && $normalized['advanced_customization']
            ? 'advanced'
            : $normalized['customization_level'];
        $normalized['seo_level'] = $normalized['seo_level'] === 'none' && $normalized['seo_advanced']
            ? 'advanced'
            : $normalized['seo_level'];
        $normalized['analytics_level'] = $normalized['analytics_level'] === 'none' && $normalized['analytics']
            ? 'advanced'
            : $normalized['analytics_level'];
        $normalized['branding_removable'] = $normalized['branding_removable'] || $normalized['catops_branding_removable'];
        $normalized['catops_branding_removable'] = $normalized['branding_removable'];

        return $normalized;
    }

    /**
     * Strict counterpart used by administrative forms. Invalid values are rejected
     * instead of silently stored as a more permissive capability.
     *
     * @param array<string, mixed> $capabilities
     * @return array<string, mixed>
     */
    public function validateCapabilityInput(array $capabilities): array
    {
        foreach (self::INTEGER_CAPABILITIES as $key) {
            $value = $capabilities[$key] ?? 0;
            if (is_int($value)) {
                $valid = $value >= 0;
            } elseif (is_string($value)) {
                $valid = ctype_digit($value);
            } else {
                $valid = false;
            }
            if (!$valid) {
                throw new \InvalidArgumentException('El límite ' . $key . ' debe ser un entero igual o mayor a cero.');
            }
            $capabilities[$key] = (int)$value;
        }
        foreach (self::BOOLEAN_CAPABILITIES as $key) {
            $value = $capabilities[$key] ?? false;
            if (!in_array($value, [true, false, 1, 0, '1', '0', null], true)) {
                throw new \InvalidArgumentException('La capacidad ' . $key . ' debe ser booleana.');
            }
            $capabilities[$key] = $value === true || $value === 1 || $value === '1';
        }
        foreach (self::ENUM_CAPABILITIES as $key => $allowedValues) {
            $value = $capabilities[$key] ?? self::DEFAULT_CAPABILITIES[$key];
            if (!is_string($value) || !in_array($value, $allowedValues, true)) {
                throw new \InvalidArgumentException('El valor de ' . $key . ' no es válido.');
            }
            $capabilities[$key] = $value;
        }
        $templates = $capabilities['enabled_templates'] ?? [];
        if (!is_array($templates) || array_filter($templates, static fn (mixed $slug): bool => !is_string($slug))) {
            throw new \InvalidArgumentException('Las plantillas habilitadas no son válidas.');
        }
        $capabilities['enabled_templates'] = array_values(array_unique(array_filter($templates, static fn (string $slug): bool => $slug !== '')));

        return $this->normalizeCapabilities($capabilities);
    }

    /**
     * Commercial rows are intentionally explicit about capabilities that are
     * stored for a future module but are not available in the product yet.
     *
     * @return list<array{label:string,value:string,status:string}>
     */
    public function commercialBenefitRows(object $plan): array
    {
        $capabilities = $this->capabilities($plan);
        $configured = (int)$capabilities['sites_configured_limit'];
        $published = (int)$capabilities['sites_published_limit'];
        $categories = (bool)$capabilities['categories_enabled'];
        $customization = (string)$capabilities['customization_level'];
        $analytics = (string)$capabilities['analytics_level'];
        $seo = (string)$capabilities['seo_level'];
        $trial = (bool)$capabilities['trial_enabled'];
        $annual = $this->annualBenefits($plan);

        $rows = [
            ['label' => 'Sitios', 'value' => $configured . ' configurado' . ($configured === 1 ? '' : 's') . ' · ' . $published . ' publicado' . ($published === 1 ? '' : 's'), 'status' => 'available'],
            ['label' => 'Carta y catálogo', 'value' => $categories ? 'Simples y por categorías' : 'Formato simple', 'status' => 'available'],
            ['label' => 'WhatsApp', 'value' => $capabilities['whatsapp_enabled'] ? 'Incluido' : 'No incluido', 'status' => 'available'],
            ['label' => 'Logo y colores', 'value' => 'Configuración básica', 'status' => 'available'],
            ['label' => 'Diseño responsive', 'value' => 'Incluido', 'status' => 'available'],
            ['label' => 'Categorías', 'value' => $categories ? 'Incluidas' : 'No incluidas', 'status' => 'available'],
            ['label' => 'Productos destacados', 'value' => $capabilities['featured_items_enabled'] ? 'Incluidos' : 'No incluidos', 'status' => 'available'],
            ['label' => 'Personalización', 'value' => $this->levelLabel($customization), 'status' => $customization === 'basic' ? 'available' : 'coming_soon'],
            ['label' => 'Estadísticas', 'value' => $analytics === 'none' ? 'No incluidas' : $this->levelLabel($analytics), 'status' => $analytics === 'none' ? 'available' : 'coming_soon'],
            ['label' => 'SEO', 'value' => $this->levelLabel($seo), 'status' => $seo === 'basic' ? 'available' : 'coming_soon'],
            ['label' => 'Código QR', 'value' => $capabilities['qr_enabled'] ? 'Incluido' : 'No incluido', 'status' => $capabilities['qr_enabled'] ? 'coming_soon' : 'available'],
            ['label' => 'Temas premium', 'value' => $capabilities['premium_themes_enabled'] ? 'Incluidos' : 'No incluidos', 'status' => $capabilities['premium_themes_enabled'] ? 'coming_soon' : 'available'],
            ['label' => 'Marca CatOps', 'value' => $capabilities['branding_removable'] ? 'Removible' : 'Incluida', 'status' => $capabilities['branding_removable'] ? 'coming_soon' : 'available'],
            ['label' => 'Soporte', 'value' => $capabilities['priority_support'] ? 'Prioritario' : 'Estándar', 'status' => $capabilities['priority_support'] ? 'coming_soon' : 'available'],
        ];
        if ($trial) {
            $rows[] = ['label' => 'Duración', 'value' => (int)$capabilities['trial_duration_days'] . ' días desde tu primera publicación', 'status' => 'available'];
        }
        if ($annual['domain_credit']) {
            $rows[] = ['label' => 'Plan anual', 'value' => 'Crédito de dominio', 'status' => 'coming_soon'];
        }

        return $rows;
    }

    private function levelLabel(string $level): string
    {
        return [
            'none' => 'No incluido',
            'basic' => 'Básico',
            'standard' => 'Estándar',
            'extended' => 'Extendido',
            'advanced' => 'Avanzado',
        ][$level] ?? 'No incluido';
    }

    private function templateSlug(string|object|null $template): string
    {
        if (is_object($template)) {
            if (isset($template->template)) {
                return (string)($template->template->slug ?? '');
            }

            return (string)($template->slug ?? '');
        }

        return (string)$template;
    }

    private function userId(int|array|object $user): int
    {
        if (is_int($user)) {
            return $user;
        }
        if (is_array($user)) {
            return (int)($user['id'] ?? 0);
        }

        return (int)($user->id ?? 0);
    }

    private function table(string $name): Table
    {
        return FactoryLocator::get('Table')->get($name);
    }
}
