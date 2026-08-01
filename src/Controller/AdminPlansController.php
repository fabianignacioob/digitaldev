<?php
declare(strict_types=1);

namespace App\Controller;

use App\Service\PlanService;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\Response;

class AdminPlansController extends AdminController
{
    private const BOOLEAN_CAPABILITIES = [
        'categories_enabled',
        'whatsapp_enabled',
        'featured_items_enabled',
        'qr_enabled',
        'custom_domain_enabled',
        'premium_themes_enabled',
        'priority_support',
        'trial_enabled',
        'domain_credit',
        'annual_available',
        'branding_removable',
    ];

    public function index(): void
    {
        $plans = $this->fetchTable('Plans')->find()->orderByAsc('sort_order')->all();
        $subscriptions = $this->fetchTable('Subscriptions');
        $usage = [];
        foreach ($plans as $plan) {
            $usage[(int)$plan->id] = $subscriptions->find()->where(['plan_slug' => $plan->slug])->count();
        }
        $this->set(compact('plans', 'usage'));
    }

    public function edit(int $id): ?Response
    {
        $plan = $this->fetchTable('Plans')->get($id);
        $this->requireSuperAdminAction();
        if ($this->request->is(['post', 'patch', 'put'])) {
            $reason = $this->adminReason();
            $data = $this->request->getData();
            $capabilities = $this->capabilitiesFromRequest($data);
            $data['monthly_price'] = max(0, (int)($data['monthly_price'] ?? 0));
            $data['annual_price'] = max(0, (int)($data['annual_price'] ?? 0)) ?: null;
            $capabilities['annual_price'] = (int)($data['annual_price'] ?? 0);
            $data['capabilities'] = json_encode($capabilities, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $data['annual_benefits'] = json_encode($this->annualBenefitsFromRequest($data), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $data['commercial_description'] = trim((string)($data['commercial_description'] ?? '')) ?: null;
            $data['commercial_badge'] = trim((string)($data['commercial_badge'] ?? '')) ?: null;
            $data['max_sites'] = (int)$capabilities['sites_configured_limit'];
            $data['max_published'] = (int)$capabilities['sites_published_limit'];
            $data['sort_order'] = max(0, (int)($data['sort_order'] ?? 0));
            unset($data['slug']);
            $plan = $this->fetchTable('Plans')->patchEntity($plan, $data);
            if ($this->fetchTable('Plans')->save($plan)) {
                $this->logAdminAction('admin.plan.updated', 'plans', $id, [
                    'reason' => $reason,
                    'affected_subscriptions' => $this->fetchTable('Subscriptions')->find()->where(['plan_slug' => $plan->slug])->count(),
                ]);
                $this->Flash->success('Plan actualizado. El slug se mantiene estable para proteger suscripciones existentes.');

                return $this->redirect(['action' => 'edit', $id]);
            }
            $this->Flash->error('No se pudo guardar el plan. Revisa los datos.');
        }

        $capabilities = $this->planService()->capabilities($plan);
        $templates = $this->fetchTable('Templates')->find('list')->where(['active' => true])->orderByAsc('name')->all();
        $affectedSubscriptions = $this->fetchTable('Subscriptions')->find()->where(['plan_slug' => $plan->slug])->count();
        $this->set(compact('plan', 'capabilities', 'templates', 'affectedSubscriptions'));

        return null;
    }

    private function capabilitiesFromRequest(array $data): array
    {
        $submitted = (array)($data['capabilities'] ?? []);
        $capabilities = [];
        foreach (self::BOOLEAN_CAPABILITIES as $key) {
            $capabilities[$key] = $submitted[$key] ?? false;
        }
        foreach (['sites_configured_limit', 'sites_published_limit', 'items_limit', 'categories_limit', 'featured_items_limit', 'image_storage_limit_mb', 'trial_duration_days', 'trial_expire_after_registration_days', 'custom_domains_limit', 'annual_price'] as $key) {
            $capabilities[$key] = $submitted[$key] ?? 0;
        }
        $capabilities['enabled_templates'] = array_values(array_filter(
            (array)($submitted['enabled_templates'] ?? []),
            fn ($slug) => $this->fetchTable('Templates')->find()->where(['slug' => (string)$slug, 'active' => true])->count() > 0,
        ));
        foreach (['customization_level', 'analytics_level', 'seo_level'] as $key) {
            $capabilities[$key] = $submitted[$key] ?? PlanService::DEFAULT_CAPABILITIES[$key];
        }

        try {
            return $this->planService()->validateCapabilityInput($capabilities);
        } catch (\InvalidArgumentException $exception) {
            throw new BadRequestException($exception->getMessage());
        }
    }

    private function annualBenefitsFromRequest(array $data): array
    {
        try {
            return $this->planService()->validateAnnualBenefits((array)($data['annual_benefits'] ?? []));
        } catch (\InvalidArgumentException $exception) {
            throw new BadRequestException($exception->getMessage());
        }
    }
}
