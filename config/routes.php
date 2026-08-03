<?php
/**
 * Routes configuration.
 *
 * In this file, you set up routes to your controllers and their actions.
 * Routes are very important mechanism that allows you to freely connect
 * different URLs to chosen controllers and their actions (functions).
 *
 * It's loaded within the context of `Application::routes()` method which
 * receives a `RouteBuilder` instance `$routes` as method argument.
 *
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */

use Cake\Routing\Route\DashedRoute;
use Cake\Routing\RouteBuilder;

/*
 * This file is loaded in the context of the `Application` class.
 * So you can use `$this` to reference the application class instance
 * if required.
 */
return function (RouteBuilder $routes): void {
    /*
     * The default class to use for all routes
     *
     * The following route classes are supplied with CakePHP and are appropriate
     * to set as the default:
     *
     * - Route
     * - InflectedRoute
     * - DashedRoute
     *
     * If no call is made to `Router::defaultRouteClass()`, the class used is
     * `Route` (`Cake\Routing\Route\Route`)
     *
     * Note that `Route` does not do any inflections on URLs which will result in
     * inconsistently cased URLs when used with `{plugin}`, `{controller}` and
     * `{action}` markers.
     */
    $routes->setRouteClass(DashedRoute::class);

    $routes->scope('/', function (RouteBuilder $builder): void {
        /*
         * Here, we are connecting '/' (base path) to a controller called 'Pages',
         * its action called 'display', and we pass a param to select the view file
         * to use (in this case, templates/Pages/home.php)...
         */
        $builder->connect('/', ['controller' => 'PublicSites', 'action' => 'view']);
        $builder->connect('/servicio', ['controller' => 'Pages', 'action' => 'display', 'servicio']);
        $builder->connect('/planes', ['controller' => 'Pages', 'action' => 'display', 'planes']);
        $builder->connect('/registro', ['controller' => 'Users', 'action' => 'register']);
        $builder->connect('/verificar-correo', ['controller' => 'Users', 'action' => 'verifyEmail']);
        $builder->connect('/reenviar-codigo', ['controller' => 'Users', 'action' => 'resendCode']);
        $builder->connect('/planes/activar/{plan}', ['controller' => 'Users', 'action' => 'activatePlan'])
            ->setPass(['plan'])
            ->setPatterns(['plan' => '[a-z0-9-]+']);
        $builder->connect('/login', ['controller' => 'Users', 'action' => 'login']);
        $builder->connect('/recuperar-contrasena', ['controller' => 'Users', 'action' => 'forgotPassword']);
        $builder->connect('/restablecer-contrasena', ['controller' => 'Users', 'action' => 'resetPassword']);
        $builder->connect('/logout', ['controller' => 'Users', 'action' => 'logout']);
        $builder->connect('/panel', ['controller' => 'Dashboard', 'action' => 'index']);
        $builder->connect('/admin', ['controller' => 'AdminDashboard', 'action' => 'index']);
        $builder->connect('/admin/users', ['controller' => 'AdminUsers', 'action' => 'index']);
        $builder->connect('/admin/users/{id}', ['controller' => 'AdminUsers', 'action' => 'view'])
            ->setPass(['id'])->setPatterns(['id' => '\d+']);
        $builder->connect('/admin/users/{id}/verify', ['controller' => 'AdminUsers', 'action' => 'verify'])
            ->setPass(['id'])->setPatterns(['id' => '\d+']);
        $builder->connect('/admin/users/{id}/access', ['controller' => 'AdminUsers', 'action' => 'toggleAccess'])
            ->setPass(['id'])->setPatterns(['id' => '\d+']);
        $builder->connect('/admin/users/{id}/role', ['controller' => 'AdminUsers', 'action' => 'changeRole'])
            ->setPass(['id'])->setPatterns(['id' => '\d+']);
        $builder->connect('/admin/users/{id}/note', ['controller' => 'AdminUsers', 'action' => 'note'])
            ->setPass(['id'])->setPatterns(['id' => '\d+']);
        $builder->connect('/admin/sites', ['controller' => 'AdminSites', 'action' => 'index']);
        $builder->connect('/admin/sites/{id}', ['controller' => 'AdminSites', 'action' => 'view'])
            ->setPass(['id'])->setPatterns(['id' => '\d+']);
        $builder->connect('/admin/sites/{id}/pause', ['controller' => 'AdminSites', 'action' => 'pause'])
            ->setPass(['id'])->setPatterns(['id' => '\d+']);
        $builder->connect('/admin/sites/{id}/reactivate', ['controller' => 'AdminSites', 'action' => 'reactivate'])
            ->setPass(['id'])->setPatterns(['id' => '\d+']);
        $builder->connect('/admin/sites/{id}/unpublish', ['controller' => 'AdminSites', 'action' => 'unpublish'])
            ->setPass(['id'])->setPatterns(['id' => '\d+']);
        $builder->connect('/admin/subscriptions', ['controller' => 'AdminSubscriptions', 'action' => 'index']);
        $builder->connect('/admin/subscriptions/{id}', ['controller' => 'AdminSubscriptions', 'action' => 'view'])
            ->setPass(['id'])->setPatterns(['id' => '\d+']);
        $builder->connect('/admin/subscriptions/{id}/extend', ['controller' => 'AdminSubscriptions', 'action' => 'extend'])
            ->setPass(['id'])->setPatterns(['id' => '\d+']);
        $builder->connect('/admin/subscriptions/{id}/suspend', ['controller' => 'AdminSubscriptions', 'action' => 'suspend'])
            ->setPass(['id'])->setPatterns(['id' => '\d+']);
        $builder->connect('/admin/subscriptions/{id}/reactivate', ['controller' => 'AdminSubscriptions', 'action' => 'reactivate'])
            ->setPass(['id'])->setPatterns(['id' => '\d+']);
        $builder->connect('/admin/subscriptions/{id}/cancel', ['controller' => 'AdminSubscriptions', 'action' => 'cancel'])
            ->setPass(['id'])->setPatterns(['id' => '\d+']);
        $builder->connect('/admin/subscriptions/{id}/plan', ['controller' => 'AdminSubscriptions', 'action' => 'changePlan'])
            ->setPass(['id'])->setPatterns(['id' => '\d+']);
        $builder->connect('/admin/subscriptions/{id}/process-expiration', ['controller' => 'AdminSubscriptions', 'action' => 'processExpiration'])
            ->setPass(['id'])->setPatterns(['id' => '\d+']);
        $builder->connect('/admin/payments', ['controller' => 'AdminPayments', 'action' => 'index']);
        $builder->connect('/admin/payments/{id}', ['controller' => 'AdminPayments', 'action' => 'view'])
            ->setPass(['id'])->setPatterns(['id' => '\d+']);
        $builder->connect('/admin/payments/{id}/reconcile', ['controller' => 'AdminPayments', 'action' => 'reconcile'])
            ->setPass(['id'])->setPatterns(['id' => '\d+']);
        $builder->connect('/admin/payments/{id}/cancel', ['controller' => 'AdminPayments', 'action' => 'cancel'])
            ->setPass(['id'])->setPatterns(['id' => '\d+']);
        $builder->connect('/admin/payments/{id}/review', ['controller' => 'AdminPayments', 'action' => 'markForReview'])
            ->setPass(['id'])->setPatterns(['id' => '\d+']);
        $builder->connect('/admin/plans', ['controller' => 'AdminPlans', 'action' => 'index']);
        $builder->connect('/admin/plans/{id}', ['controller' => 'AdminPlans', 'action' => 'edit'])
            ->setPass(['id'])->setPatterns(['id' => '\d+']);
        $builder->connect('/admin/audit-logs', ['controller' => 'AdminAuditLogs', 'action' => 'index']);
        $builder->connect('/admin/domains', ['controller' => 'AdminDomains', 'action' => 'index']);
        $builder->connect('/admin/domains/{id}', ['controller' => 'AdminDomains', 'action' => 'view'])
            ->setPass(['id'])->setPatterns(['id' => '\\d+']);
        $builder->connect('/admin/domains/{id}/deactivate', ['controller' => 'AdminDomains', 'action' => 'deactivate'])
            ->setPass(['id'])->setPatterns(['id' => '\\d+']);
        $builder->connect('/admin/domains/{id}/reactivate', ['controller' => 'AdminDomains', 'action' => 'reactivate'])
            ->setPass(['id'])->setPatterns(['id' => '\\d+']);
        $builder->connect('/admin/domains/{id}/reassign', ['controller' => 'AdminDomains', 'action' => 'reassign'])
            ->setPass(['id'])->setPatterns(['id' => '\\d+']);
        $builder->connect('/admin/domains/{id}/retry-provisioning', ['controller' => 'AdminDomains', 'action' => 'retryProvisioning'])
            ->setPass(['id'])->setPatterns(['id' => '\\d+']);
        $builder->connect('/admin/system-status', ['controller' => 'AdminSystemStatus', 'action' => 'index']);
        $builder->connect('/suscripcion/extender-30', ['controller' => 'Subscriptions', 'action' => 'extendMonthly']);
        $builder->connect('/suscripcion/pagar-anual', ['controller' => 'Subscriptions', 'action' => 'payAnnual']);
        $builder->connect('/suscripcion/cambiar/{plan}', ['controller' => 'Subscriptions', 'action' => 'upgrade'])
            ->setPass(['plan'])
            ->setPatterns(['plan' => '[a-z0-9-]+']);
        $builder->connect('/payments/create', ['controller' => 'Payments', 'action' => 'create']);
        $builder->connect('/test-plan', ['controller' => 'Payments', 'action' => 'testPlan']);
        $builder->connect('/payments/result/{reference}', ['controller' => 'Payments', 'action' => 'result'])
            ->setPass(['reference'])
            ->setPatterns(['reference' => '[a-z0-9-]+']);
        $builder->connect('/payments/mock-confirm', ['controller' => 'Payments', 'action' => 'mockConfirm']);
        $builder->connect('/payments/webpay/return', ['controller' => 'Payments', 'action' => 'webpayReturn']);
        $builder->connect('/internal/tls/allow', ['controller' => 'DomainTls', 'action' => 'allow']);
        $builder->connect('/sitios/nuevo', ['controller' => 'Sites', 'action' => 'add']);
        $builder->connect('/sitios/editar/{id}', ['controller' => 'Sites', 'action' => 'edit'])
            ->setPass(['id'])
            ->setPatterns(['id' => '\d+']);
        $builder->connect('/sitios/preview/{id}', ['controller' => 'Sites', 'action' => 'preview'])
            ->setPass(['id'])
            ->setPatterns(['id' => '\d+']);
        $builder->connect('/sitios/{id}/qr/generar', ['controller' => 'Sites', 'action' => 'generateQr'])
            ->setPass(['id'])
            ->setPatterns(['id' => '\d+']);
        $builder->connect('/sitios/{id}/qr/estilo', ['controller' => 'Sites', 'action' => 'updateQrStyle'])
            ->setPass(['id'])
            ->setPatterns(['id' => '\d+']);
        $builder->connect('/sitios/{id}/qr', ['controller' => 'Sites', 'action' => 'downloadQr'])
            ->setPass(['id'])
            ->setPatterns(['id' => '\d+']);
        $builder->connect('/sitios/publicar/{id}', ['controller' => 'Sites', 'action' => 'publish'])
            ->setPass(['id'])
            ->setPatterns(['id' => '\d+']);
        $builder->connect('/sitios/logo/eliminar/{id}', ['controller' => 'Sites', 'action' => 'deleteLogo'])
            ->setPass(['id'])
            ->setPatterns(['id' => '\d+']);
        $builder->connect('/sitios/{id}/dominios', ['controller' => 'Sites', 'action' => 'addDomain'])
            ->setPass(['id'])
            ->setPatterns(['id' => '\d+']);
        $builder->connect('/sitios/{siteId}/dominios/{domainId}/verificar', ['controller' => 'Sites', 'action' => 'verifyDomain'])
            ->setPass(['siteId', 'domainId'])
            ->setPatterns(['siteId' => '\d+', 'domainId' => '\d+']);
        $builder->connect('/sitios/{siteId}/dominios/{domainId}/eliminar', ['controller' => 'Sites', 'action' => 'deleteDomain'])
            ->setPass(['siteId', 'domainId'])
            ->setPatterns(['siteId' => '\d+', 'domainId' => '\d+']);
        $builder->connect('/sitios/{siteId}/secciones', ['controller' => 'SiteSections', 'action' => 'edit'])
            ->setPass(['siteId'])
            ->setPatterns(['siteId' => '\d+']);
        $builder->connect('/sitios/{siteId}/carta', ['controller' => 'Catalogs', 'action' => 'edit'])
            ->setPass(['siteId'])
            ->setPatterns(['siteId' => '\d+']);
        $builder->connect('/sitios/{siteId}/carta/configuracion', ['controller' => 'Catalogs', 'action' => 'updateSettings'])
            ->setPass(['siteId'])
            ->setPatterns(['siteId' => '\d+']);
        $builder->connect('/sitios/{siteId}/carta/fondo/eliminar', ['controller' => 'Catalogs', 'action' => 'deleteBackground'])
            ->setPass(['siteId'])
            ->setPatterns(['siteId' => '\d+']);
        $builder->connect('/sitios/{siteId}/carta/categorias', ['controller' => 'Catalogs', 'action' => 'addCategory'])
            ->setPass(['siteId'])
            ->setPatterns(['siteId' => '\d+']);
        $builder->connect('/sitios/{siteId}/carta/categorias/orden', ['controller' => 'Catalogs', 'action' => 'reorderCategories'])
            ->setPass(['siteId'])
            ->setPatterns(['siteId' => '\d+']);
        $builder->connect('/sitios/{siteId}/carta/productos', ['controller' => 'Catalogs', 'action' => 'addProduct'])
            ->setPass(['siteId'])
            ->setPatterns(['siteId' => '\d+']);
        $builder->connect('/sitios/{siteId}/carta/productos/orden', ['controller' => 'Catalogs', 'action' => 'reorderProducts'])
            ->setPass(['siteId'])
            ->setPatterns(['siteId' => '\d+']);
        $builder->connect('/carta/categorias/editar/{id}', ['controller' => 'Catalogs', 'action' => 'updateCategory'])
            ->setPass(['id'])
            ->setPatterns(['id' => '\d+']);
        $builder->connect('/carta/productos/editar/{id}', ['controller' => 'Catalogs', 'action' => 'updateProduct'])
            ->setPass(['id'])
            ->setPatterns(['id' => '\d+']);
        $builder->connect('/carta/productos/{productId}/variantes', ['controller' => 'Catalogs', 'action' => 'addVariant'])
            ->setPass(['productId'])
            ->setPatterns(['productId' => '\d+']);
        $builder->connect('/carta/variantes/editar/{id}', ['controller' => 'Catalogs', 'action' => 'updateVariant'])
            ->setPass(['id'])
            ->setPatterns(['id' => '\d+']);
        $builder->connect('/carta/variantes/eliminar/{id}', ['controller' => 'Catalogs', 'action' => 'deleteVariant'])
            ->setPass(['id'])
            ->setPatterns(['id' => '\d+']);
        $builder->connect('/carta/categorias/eliminar/{id}', ['controller' => 'Catalogs', 'action' => 'deleteCategory'])
            ->setPass(['id'])
            ->setPatterns(['id' => '\d+']);
        $builder->connect('/carta/productos/eliminar/{id}', ['controller' => 'Catalogs', 'action' => 'deleteProduct'])
            ->setPass(['id'])
            ->setPatterns(['id' => '\d+']);
        $builder->connect('/carta/productos/imagen/eliminar/{id}', ['controller' => 'Catalogs', 'action' => 'deleteProductImage'])
            ->setPass(['id'])
            ->setPatterns(['id' => '\d+']);
        $builder->connect('/secciones/toggle/{id}', ['controller' => 'SiteSections', 'action' => 'toggle'])
            ->setPass(['id'])
            ->setPatterns(['id' => '\d+']);
        $builder->connect('/s/{subdomain}', ['controller' => 'PublicSites', 'action' => 'view'])
            ->setPass(['subdomain'])
            ->setPatterns(['subdomain' => '[a-z0-9-]+']);
        $builder->connect('/q/{token}', ['controller' => 'Sites', 'action' => 'publicQrRedirect'])
            ->setPass(['token'])
            ->setPatterns(['token' => '[a-z0-9]{24,64}']);

        /*
         * ...and connect the rest of 'Pages' controller's URLs.
         */
        $builder->connect('/pages/*', 'Pages::display');

        /*
         * Connect catchall routes for all controllers.
         *
         * The `fallbacks` method is a shortcut for
         *
         * ```
         * $builder->connect('/{controller}', ['action' => 'index']);
         * $builder->connect('/{controller}/{action}/*', []);
         * ```
         *
         * It is NOT recommended to use fallback routes after your initial prototyping phase!
         * See https://book.cakephp.org/5/en/development/routing.html#fallbacks-method for more information
         */
        $builder->fallbacks();
    });

    /*
     * If you need a different set of middleware or none at all,
     * open new scope and define routes there.
     *
     * ```
     * $routes->scope('/api', function (RouteBuilder $builder): void {
     *     // No $builder->applyMiddleware() here.
     *
     *     // Parse specified extensions from URLs
     *     // $builder->setExtensions(['json', 'xml']);
     *
     *     // Connect API actions here.
     * });
     * ```
     */
};
