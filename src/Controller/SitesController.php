<?php
declare(strict_types=1);

namespace App\Controller;

use App\Service\DomainAdministrationService;
use App\Service\EmailService;
use App\Service\LocalImageStorageService;
use App\Service\PublicSiteResolverService;
use App\Service\PublicUrlService;
use App\Service\SiteQrCodeService;
use App\Service\SubscriptionService;
use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\Exception\ForbiddenException;
use Cake\Http\Exception\NotFoundException;
use Cake\Http\Response;
use Cake\I18n\DateTime;
use Cake\Log\Log;
use InvalidArgumentException;
use RuntimeException;

class SitesController extends AppController
{
    public function index(): ?Response
    {
        return $this->redirect(['controller' => 'Dashboard', 'action' => 'index']);
    }

    public function add(): ?Response
    {
        if ($redirect = $this->requireLogin()) {
            return $redirect;
        }

        if (!$this->hasActivePlan()) {
            $this->Flash->warning('Para crear una web necesitas activar una suscripción.');

            return $this->redirect('/planes');
        }
        if (!$this->planService()->canCreateSite((int)$this->currentUserId())) {
            $usage = $this->planService()->siteUsage((int)$this->currentUserId());
            $this->Flash->warning($usage['over_limit']
                ? 'Conservamos tus sitios actuales, pero tu uso supera el límite del plan. Reduce sitios o sube de plan antes de crear otro.'
                : 'Tu plan llegó al máximo de sitios configurados.');

            return $this->redirect(['controller' => 'Dashboard', 'action' => 'index']);
        }

        $this->viewBuilder()->setLayout('dashboard');
        $sites = $this->fetchTable('Sites');
        $site = $sites->newEmptyEntity();

        if ($this->request->is('post')) {
            $data = $this->request->getData();
            if (!$this->templateIsAllowed((int)$data['template_id'])) {
                $this->Flash->error('La plantilla seleccionada no está disponible en tu plan.');

                return $this->redirect(['action' => 'add']);
            }
            $data['user_id'] = $this->currentUserId();
            $data['status'] = 'draft';
            try {
                $data['logo_path'] = $this->saveLogoUpload();
            } catch (InvalidArgumentException | RuntimeException $exception) {
                $this->Flash->error($exception->getMessage());

                return $this->redirect(['action' => 'add']);
            }

            $site = $sites->patchEntity($site, $data);
            if ($site->status === 'published' && !$site->published_at) {
                $site->set('published_at', DateTime::now());
            }
            if ($site->status === 'published') {
                $site->paused_reason = null;
            }

            if ($sites->save($site)) {
                $this->createDefaultCatalogSetting((int)$site->id);
                $this->createDefaultDomain((int)$site->id, (string)$site->subdomain);
                $this->Flash->success('Sitio creado. Ahora puedes editar su diseño, productos y datos de contacto.');

                return $this->redirect(['action' => 'edit', $site->id]);
            }

            $this->Flash->error('No pudimos crear el sitio. Revisa los datos.');
        }

        $templates = $this->availableTemplates();
        $themes = $this->fetchTable('Themes')->find('list')->where(['active' => true])->all();
        $baseDomain = $this->publicUrlService()->baseDomain();
        $this->set(compact('site', 'templates', 'themes', 'baseDomain'));

        return null;
    }

    public function edit(int $id): ?Response
    {
        if ($redirect = $this->requireLogin()) {
            return $redirect;
        }

        $this->viewBuilder()->setLayout('dashboard');
        $sites = $this->fetchTable('Sites');
        $site = $sites->find()
            ->contain(['SiteSections', 'Templates', 'Themes', 'Domains', 'CatalogSettings', 'SiteQrCodes'])
            ->where(['Sites.id' => $id, 'Sites.user_id' => $this->currentUserId()])
            ->firstOrFail();

        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->getData();
            $requestedCategoryLayout = (string)($data['category_layout'] ?? 'normal');
            unset($data['category_layout']);
            if (!empty($data['template_id']) && !$this->templateIsAllowed((int)$data['template_id'])) {
                $this->Flash->error('La plantilla seleccionada no está disponible en tu plan.');

                return $this->redirect(['action' => 'edit', $site->id]);
            }
            if (($data['status'] ?? $site->status) === 'published' && !$this->planService()->canPublishSite((int)$this->currentUserId(), $site)) {
                $usage = $this->planService()->siteUsage((int)$this->currentUserId());
                $this->Flash->error($usage['over_limit']
                    ? 'Conservamos tus sitios actuales, pero tu uso supera el límite del plan. Reduce sitios o sube de plan antes de publicar otro.'
                    : 'Tu plan llegó al máximo de sitios publicados.');

                return $this->redirect(['action' => 'edit', $site->id]);
            }
            try {
                $logoPath = $this->saveLogoUpload();
            } catch (InvalidArgumentException | RuntimeException $exception) {
                $this->Flash->error($exception->getMessage());

                return $this->redirect(['action' => 'edit', $site->id]);
            }
            if ($logoPath) {
                $data['logo_path'] = $logoPath;
            }
            $oldLogoPath = $site->logo_path;

            $previousStatus = (string)$site->status;
            $site = $sites->patchEntity($site, $data);
            if ($site->status === 'published' && !$site->published_at) {
                $site->published_at = DateTime::now();
            }

            try {
                ConnectionManager::get('default')->transactional(function () use ($site, $sites, $previousStatus): void {
                    if ($site->status === 'published' && $previousStatus !== 'published') {
                        $this->subscriptionService()->startTrialOnFirstPublication($this->currentSubscription());
                    }
                    $sites->saveOrFail($site);
                });
            } catch (\Throwable $exception) {
                $this->Flash->error($exception->getMessage() ?: 'No pudimos guardar los cambios.');

                return $this->redirect(['action' => 'edit', $site->id]);
            }

            if ($site->id) {
                if ($logoPath && $oldLogoPath) {
                    $this->imageStorage()->delete((string)$oldLogoPath);
                }
                $this->syncSubdomainDomain($site);
                $this->createDefaultCatalogSetting((int)$site->id);
                $this->saveCategoryLayout($site, $requestedCategoryLayout);
                if ($site->status === 'published' && $previousStatus !== 'published') {
                    $this->notifySitePublished($site);
                }
                $this->Flash->success('Cambios guardados.');

                return $this->redirect(['action' => 'edit', $site->id]);
            }

            $this->Flash->error('No pudimos guardar los cambios.');
        }

        $templates = $this->availableTemplates();
        $themes = $this->fetchTable('Themes')->find('list')->where(['active' => true])->all();
        $baseDomain = $this->publicUrlService()->baseDomain();
        $publicUrl = $this->publicUrlService()->publicUrl($site);
        $categoryLayoutAvailable = $this->planService()->canUseCategoryBlocks((int)$this->currentUserId(), $site);
        $qrEnabled = $this->planService()->hasFeature((int)$this->currentUserId(), 'qr_enabled');
        $siteQrCode = $site->site_qr_code ?? null;
        $qrPublicUrl = $siteQrCode ? $this->publicUrlService()->qrUrl((string)$siteQrCode->public_token) : null;
        $domainService = $this->domainService();
        $customDomainAvailable = $domainService->canManageCustomDomains((int)$this->currentUserId());
        $customDomainUsage = $domainService->usageForUser((int)$this->currentUserId());
        $customDomains = array_values(array_filter((array)$site->domains, static fn (object $domain): bool => (string)$domain->type === 'custom'));
        $this->set(compact(
            'site',
            'templates',
            'themes',
            'baseDomain',
            'publicUrl',
            'domainService',
            'customDomainAvailable',
            'customDomainUsage',
            'customDomains',
            'categoryLayoutAvailable',
            'qrEnabled',
            'siteQrCode',
            'qrPublicUrl',
        ));

        return null;
    }

    public function preview(int $id): ?Response
    {
        if ($redirect = $this->requireLogin()) {
            return $redirect;
        }

        $site = $this->fetchTable('Sites')->find()
            ->contain([
                'Templates',
                'Themes',
                'SiteSections',
                'CatalogSettings',
                'CatalogCategories.CatalogProducts.MeasurementTypes',
                'CatalogCategories.CatalogProducts.CatalogProductVariants',
                'CatalogProducts.MeasurementTypes',
                'CatalogProducts.CatalogProductVariants',
            ])
            ->where(['Sites.id' => $id, 'Sites.user_id' => $this->currentUserId()])
            ->firstOrFail();

        $this->viewBuilder()->disableAutoLayout();
        $productPresentation = $this->planService()->publicProductPresentation($site);
        $siteCapabilities = $this->planService()->getCapabilitiesForUser((int)$site->user_id);
        $this->set(compact('site', 'productPresentation', 'siteCapabilities'));

        if ($this->planService()->templateKind($site) !== 'landing') {
            return $this->render('/PublicSites/catalog');
        }

        return $this->render('/PublicSites/view');
    }

    public function publish(int $id): Response
    {
        $this->request->allowMethod(['post', 'patch']);
        if ($redirect = $this->requireLogin()) {
            return $redirect;
        }

        $sites = $this->fetchTable('Sites');
        $site = $sites->find()
            ->where(['Sites.id' => $id, 'Sites.user_id' => $this->currentUserId()])
            ->firstOrFail();

        if (!$this->hasActivePlan()) {
            $this->Flash->warning('Renueva tu suscripción para publicar sitios.');

            return $this->redirect(['action' => 'edit', $site->id]);
        }

        if (!$this->planService()->canPublishSite((int)$this->currentUserId(), $site)) {
            $usage = $this->planService()->siteUsage((int)$this->currentUserId());
            $this->Flash->error($usage['over_limit']
                ? 'Conservamos tus sitios actuales, pero tu uso supera el límite del plan. Reduce sitios o sube de plan antes de publicar otro.'
                : 'Tu plan llegó al máximo de sitios publicados.');

            return $this->redirect(['action' => 'edit', $site->id]);
        }

        $site->status = 'published';
        $site->paused_reason = null;
        if (!$site->published_at) {
            $site->set('published_at', DateTime::now());
        }
        try {
            ConnectionManager::get('default')->transactional(function () use ($site, $sites): void {
                $this->subscriptionService()->startTrialOnFirstPublication($this->currentSubscription());
                $sites->saveOrFail($site);
            });
        } catch (\Throwable $exception) {
            $this->Flash->error($exception->getMessage() ?: 'No pudimos publicar el sitio.');

            return $this->redirect(['action' => 'edit', $site->id]);
        }
        $this->notifySitePublished($site);
        $this->Flash->success('Sitio publicado.');

        return $this->redirect(['action' => 'edit', $site->id]);
    }

    public function deleteLogo(int $id): Response
    {
        $this->request->allowMethod(['post', 'delete']);
        if ($redirect = $this->requireLogin()) {
            return $redirect;
        }

        $sites = $this->fetchTable('Sites');
        $site = $sites->find()
            ->where(['Sites.id' => $id, 'Sites.user_id' => $this->currentUserId()])
            ->firstOrFail();

        $oldLogoPath = $site->logo_path;
        $site->logo_path = null;
        $sites->saveOrFail($site);
        $this->imageStorage()->delete((string)$oldLogoPath);
        $this->Flash->success('Logo eliminado.');

        return $this->redirect(['action' => 'edit', $site->id]);
    }

    public function generateQr(int $id): Response
    {
        $this->request->allowMethod(['post']);
        if ($redirect = $this->requireLogin()) {
            return $redirect;
        }

        $site = $this->ownedSite($id);
        $this->assertQrIsAvailable($site);
        $qrCodes = $this->fetchTable('SiteQrCodes');
        $existing = $qrCodes->find()->where(['site_id' => $site->id])->first();
        if ($existing) {
            $this->Flash->success('El código QR ya fue generado y conserva su enlace permanente.');

            return $this->redirect(['action' => 'edit', $site->id]);
        }

        for ($attempt = 0; $attempt < 3; $attempt++) {
            $qrCode = $qrCodes->newEntity([
                'site_id' => (int)$site->id,
                'public_token' => bin2hex(random_bytes(16)),
                'frame_style' => 'square',
                'generated_at' => DateTime::now(),
            ]);
            if ($qrCodes->save($qrCode)) {
                $this->Flash->success('Código QR generado. Puedes descargarlo o compartir su enlace permanente.');

                return $this->redirect(['action' => 'edit', $site->id]);
            }
        }

        throw new BadRequestException('No fue posible generar el código QR. Intenta nuevamente.');
    }

    public function updateQrStyle(int $id): Response
    {
        $this->request->allowMethod(['post', 'patch']);
        if ($redirect = $this->requireLogin()) {
            return $redirect;
        }

        $site = $this->ownedSite($id);
        $this->assertQrIsAvailable($site);
        $qrCodes = $this->fetchTable('SiteQrCodes');
        $qrCode = $qrCodes->find()->where(['site_id' => $site->id])->firstOrFail();
        $qrCode = $qrCodes->patchEntity($qrCode, [
            'frame_style' => (string)$this->request->getData('frame_style'),
        ]);
        if (!$qrCodes->save($qrCode)) {
            $this->Flash->error('No pudimos guardar el estilo del código QR.');

            return $this->redirect(['action' => 'edit', $site->id]);
        }

        $this->Flash->success('Estilo del código QR actualizado.');

        return $this->redirect(['action' => 'edit', $site->id]);
    }

    public function downloadQr(int $id): Response
    {
        if ($redirect = $this->requireLogin()) {
            return $redirect;
        }

        $this->request->allowMethod(['get']);
        $site = $this->ownedSite($id);
        $this->assertQrIsAvailable($site);
        $qrCode = $this->fetchTable('SiteQrCodes')->find()->where(['site_id' => $site->id])->first();
        if (!$qrCode) {
            throw new BadRequestException('Genera el código QR antes de descargarlo.');
        }

        $filename = preg_replace('/[^a-z0-9-]/', '-', strtolower((string)$site->subdomain)) ?: 'sitio';
        $format = strtolower((string)$this->request->getQuery('format', 'svg'));
        if (!in_array($format, ['svg', 'png'], true)) {
            throw new BadRequestException('El formato solicitado para el código QR no es válido.');
        }

        $qrService = new SiteQrCodeService();
        $payload = $this->publicUrlService()->qrUrl((string)$qrCode->public_token);
        $body = $format === 'png' ? $qrService->png($payload) : $qrService->svg($payload);
        $contentType = $format === 'png' ? 'image/png' : 'image/svg+xml';
        $response = $this->response->withType($contentType)->withStringBody($body);
        if ((string)$this->request->getQuery('download') === '1') {
            $response = $response->withHeader('Content-Disposition', 'attachment; filename="catops-' . $filename . '-qr.' . $format . '"');
        }

        return $response;
    }

    public function publicQrRedirect(string $token): Response
    {
        $this->request->allowMethod(['get']);
        if (!preg_match('/^[a-z0-9]{24,64}$/', $token)) {
            throw new NotFoundException('Código QR no encontrado.');
        }

        $qrCode = $this->fetchTable('SiteQrCodes')->find()
            ->contain(['Sites'])
            ->where(['SiteQrCodes.public_token' => $token])
            ->first();
        if (!$qrCode || !$qrCode->site) {
            throw new NotFoundException('Código QR no encontrado.');
        }

        $result = (new PublicSiteResolverService())->resolveBySubdomain((string)$qrCode->site->subdomain);
        $site = $result['site'] ?? null;
        $reason = $result['reason'] ?? null;
        if (!$site || in_array($reason, [PublicSiteResolverService::REASON_NOT_FOUND, PublicSiteResolverService::REASON_DRAFT], true)) {
            throw new NotFoundException('Sitio no encontrado.');
        }
        if ($reason === PublicSiteResolverService::REASON_PAUSED) {
            return $this->response
                ->withStatus(503)
                ->withType('text')
                ->withStringBody('Este sitio está pausado temporalmente.');
        }
        if ($reason === PublicSiteResolverService::REASON_EXPIRED) {
            return $this->response
                ->withStatus(503)
                ->withType('text')
                ->withStringBody('Este sitio no está disponible porque la suscripción venció.');
        }

        return $this->redirect($this->publicUrlService()->publicUrl($site));
    }

    public function addDomain(int $id): Response
    {
        $this->request->allowMethod(['post']);
        if ($redirect = $this->requireLogin()) {
            return $redirect;
        }

        $site = $this->ownedSite($id);
        try {
            $this->domainService()->requestCustomDomain(
                $site,
                (int)$this->currentUserId(),
                (string)$this->request->getData('domain'),
            );
            $this->Flash->success('Dominio agregado. Crea el registro TXT indicado y luego verifica la configuración.');
        } catch (InvalidArgumentException $exception) {
            $this->Flash->error($exception->getMessage());
        }

        return $this->redirect(['action' => 'edit', $site->id]);
    }

    public function verifyDomain(int $siteId, int $domainId): Response
    {
        $this->request->allowMethod(['post']);
        if ($redirect = $this->requireLogin()) {
            return $redirect;
        }

        $site = $this->ownedSite($siteId);
        $domain = $this->fetchTable('Domains')->find()
            ->where(['id' => $domainId, 'site_id' => $site->id, 'type' => 'custom'])
            ->firstOrFail();
        try {
            $this->domainService()->verifyCustomDomain($domain, (int)$this->currentUserId());
            $this->Flash->success('Dominio verificado y activado. Puede tardar unos minutos en responder desde todos los navegadores.');
        } catch (InvalidArgumentException $exception) {
            $this->Flash->warning($exception->getMessage());
        }

        return $this->redirect(['action' => 'edit', $site->id]);
    }

    public function deleteDomain(int $siteId, int $domainId): Response
    {
        $this->request->allowMethod(['post', 'delete']);
        if ($redirect = $this->requireLogin()) {
            return $redirect;
        }

        $site = $this->ownedSite($siteId);
        $domain = $this->fetchTable('Domains')->find()
            ->where(['id' => $domainId, 'site_id' => $site->id, 'type' => 'custom'])
            ->firstOrFail();
        try {
            $this->domainService()->removeCustomDomain($domain, (int)$this->currentUserId());
            $this->Flash->success('Dominio eliminado. El contenido de tu sitio no fue modificado.');
        } catch (InvalidArgumentException $exception) {
            $this->Flash->error($exception->getMessage());
        }

        return $this->redirect(['action' => 'edit', $site->id]);
    }

    private function createDefaultSections(int $siteId): void
    {
        $sections = [
            [
                'site_id' => $siteId,
                'section_key' => 'hero',
                'title' => 'Tu negocio, claro y visible en internet',
                'subtitle' => 'Una página simple para explicar qué haces y convertir visitas en contactos.',
                'content' => 'Presenta tu propuesta, servicios principales y una llamada directa a WhatsApp.',
                'sort_order' => 1,
                'visible' => true,
            ],
            [
                'site_id' => $siteId,
                'section_key' => 'services',
                'title' => 'Servicios principales',
                'subtitle' => 'Muestra lo que ofreces de forma breve y fácil de entender.',
                'content' => "Servicio uno\nServicio dos\nServicio tres",
                'sort_order' => 2,
                'visible' => true,
            ],
            [
                'site_id' => $siteId,
                'section_key' => 'benefits',
                'title' => 'Por qué elegirnos',
                'subtitle' => 'Refuerza confianza con beneficios concretos.',
                'content' => "Atención directa\nRespuesta rápida\nSoluciones simples",
                'sort_order' => 3,
                'visible' => true,
            ],
            [
                'site_id' => $siteId,
                'section_key' => 'contact',
                'title' => 'Hablemos',
                'subtitle' => 'Deja un acceso simple para que te contacten.',
                'content' => 'Escríbenos y te responderemos pronto.',
                'sort_order' => 4,
                'visible' => true,
            ],
        ];

        $table = $this->fetchTable('SiteSections');
        $table->saveMany($table->newEntities($sections));
    }

    private function createDefaultCatalogSetting(int $siteId): void
    {
        $settings = $this->fetchTable('CatalogSettings');
        if ($settings->find()->where(['site_id' => $siteId])->count() > 0) {
            return;
        }

        $settings->saveOrFail($settings->newEntity([
            'site_id' => $siteId,
            'background_type' => 'color',
            'background_color' => '#fbfaf7',
            'background_preset' => null,
            'title_color' => '#17202a',
            'heading_font' => 'Inter, Arial, sans-serif',
            'title_font' => 'Inter, Arial, sans-serif',
            'slogan_color' => '#17202a',
            'slogan_font' => 'Inter, Arial, sans-serif',
            'title' => 'Nuestra propuesta',
            'slogan' => 'Productos y servicios presentados de forma simple.',
            'intro_text' => 'Revisa nuestras opciones y consulta disponibilidad por WhatsApp.',
            'show_product_action' => true,
        ]));
    }

    private function saveCategoryLayout(object $site, string $requestedLayout): void
    {
        $settings = $this->fetchTable('CatalogSettings');
        $setting = $settings->find()->where(['site_id' => (int)$site->id])->first();
        if (!$setting) {
            return;
        }

        $templateSlug = $this->templateSlugFor((int)$site->template_id);
        $layout = $this->planService()->canUseCategoryBlocks((int)$this->currentUserId(), $templateSlug)
            && $requestedLayout === 'blocks'
            ? 'blocks'
            : 'normal';
        if ((string)$setting->category_layout !== $layout) {
            $setting->category_layout = $layout;
            $settings->saveOrFail($setting);
        }
    }

    private function availableTemplates(): iterable
    {
        return $this->fetchTable('Templates')->find('list')
            ->where([
                'active' => true,
                'slug IN' => $this->planService()->allowedTemplateSlugs((int)$this->currentUserId()),
            ])
            ->orderByAsc('name')
            ->all();
    }

    private function templateIsAllowed(int $templateId): bool
    {
        $slug = $this->templateSlugFor($templateId);

        return $slug && $this->planService()->templateIsAllowed((int)$this->currentUserId(), $slug);
    }

    private function templateSlugFor(int $templateId): ?string
    {
        $template = $this->fetchTable('Templates')->find()
            ->select(['slug'])
            ->where(['id' => $templateId])
            ->first();

        return $template ? (string)$template->slug : null;
    }

    private function createDefaultDomain(int $siteId, string $subdomain): void
    {
        $domainName = $this->publicUrlService()->hostForSubdomain($subdomain);
        $domains = $this->fetchTable('Domains');
        $domain = $domains->find()
            ->where(['site_id' => $siteId, 'type' => 'subdomain'])
            ->first();
        $domain = $domain ? $domains->patchEntity($domain, [
            'domain' => $domainName,
            'verified' => true,
            'active' => true,
        ]) : $domains->newEntity([
            'site_id' => $siteId,
            'domain' => $domainName,
            'type' => 'subdomain',
            'verified' => true,
            'active' => true,
        ]);
        $domains->saveOrFail($domain);
    }

    private function syncSubdomainDomain(object $site): void
    {
        $this->createDefaultDomain((int)$site->id, (string)$site->subdomain);
    }

    private function saveLogoUpload(): ?string
    {
        return $this->imageStorage()->storeOptional(
            $this->request->getData('logo_upload'),
            'uploads/sites/' . (int)$this->currentUserId() . '/logos',
        );
    }

    private function imageStorage(): LocalImageStorageService
    {
        return new LocalImageStorageService();
    }

    private function publicUrlService(): PublicUrlService
    {
        return new PublicUrlService();
    }

    private function emailService(): EmailService
    {
        $service = Configure::read('EmailService');

        return $service instanceof EmailService ? $service : new EmailService();
    }

    private function notifySitePublished(object $site): void
    {
        try {
            $user = $this->fetchTable('Users')->get((int)$site->user_id);
            $this->emailService()->sendSitePublished(
                $user,
                $site,
                $this->publicUrlService()->publicUrl($site),
            );
        } catch (\Throwable $exception) {
            Log::warning('No se pudo enviar la publicación del sitio: ' . $exception->getMessage());
        }
    }

    private function domainService(): DomainAdministrationService
    {
        return new DomainAdministrationService();
    }

    private function ownedSite(int $id): object
    {
        return $this->fetchTable('Sites')->find()
            ->where(['Sites.id' => $id, 'Sites.user_id' => $this->currentUserId()])
            ->firstOrFail();
    }

    private function assertQrIsAvailable(object $site): void
    {
        if (!$this->planService()->hasFeature((int)$this->currentUserId(), 'qr_enabled')) {
            throw new ForbiddenException('Tu plan no incluye código QR.');
        }
        if ((string)$site->status !== 'published') {
            throw new BadRequestException('Publica el sitio antes de generar o descargar su código QR.');
        }
    }

    private function subscriptionService(): SubscriptionService
    {
        return new SubscriptionService();
    }
}
