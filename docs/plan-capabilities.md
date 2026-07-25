# Capacidades de planes

La tabla `plans` es la fuente de verdad. Su columna JSON `capabilities` se consume siempre mediante `App\Service\PlanService`; no se deben crear reglas basadas en el slug del plan.

## Oferta comercial actual

| Plan | Mensual | Anual | Límite de sitios | Estado |
| --- | ---: | ---: | --- | --- |
| Prueba gratuita | $0 | No aplica | 1 | 7 días desde la primera publicación; vence a los 14 días si no se publica. |
| Básico | $6.990 | $76.900 | 1 configurado y 1 publicado | Disponible. |
| Negocio | $9.990 | $119.900 | 3 configurados y 3 publicados | Recomendado. |
| Full | $16.990 | $189.900 | 5 configurados y 5 publicados | Disponible. |

Los precios anuales y la descripción comercial viven en `plans.annual_price` y `plans.commercial_description`; los beneficios anuales controlados viven en `plans.annual_benefits`.

La prueba se crea solo para un correo verificado y una sola vez por usuario. `SubscriptionService::createTrialForUser()` crea el estado `trial_pending`; `startTrialOnFirstPublication()` inicia los siete días. Los vencimientos no eliminan sitios, productos ni imágenes: bloquean nuevas publicaciones y conservan el acceso al panel para contratar un plan.

## Uso actual

- `getLimit($userId, 'sites_configured_limit')` y `getLimit($userId, 'sites_published_limit')` controlan creación y publicación.
- `hasFeature($userId, 'categories_enabled')` y `hasFeature($userId, 'featured_items_enabled')` controlan las funciones ya disponibles del catálogo.
- `siteUsage($userId)` entrega consumo y el estado heredado de exceso de límite para mostrar advertencias sin alterar sitios existentes.

## Capacidades preparadas

- `trial_enabled`, `trial_duration_days` y `trial_expire_after_registration_days` determinan el ciclo de prueba sin depender de un slug.
- `whatsapp_enabled` representa el contacto por WhatsApp disponible en los planes comerciales actuales.
- `annual_available` y `annual_price` habilitan una orden anual. El importe monetario canónico se conserva también en `plans.annual_price`; `PaymentService::createPendingOrder()` resuelve el precio exclusivamente desde `plans`.
- `custom_domains_limit` y `domain_credit` describen futuros dominios. No activan conexión, compra ni verificación de dominios.
- `branding_removable` reemplaza de forma compatible a la clave histórica `catops_branding_removable`.

- `customization_level`: `basic`, `extended` o `advanced`. La base actual usa `basic`; los niveles superiores quedan reservados para futuros controles de diseño.
- `analytics_level`: `none`, `basic` o `advanced`. Cuando exista analytics, el módulo debe consultar este valor antes de calcular o mostrar datos.
- `seo_level`: `none`, `basic`, `standard` o `advanced`. Hoy el nivel básico corresponde a título y descripción SEO; niveles superiores deben validarse antes de habilitar herramientas nuevas.
- `qr_enabled`, `premium_themes_enabled`, `branding_removable` y `priority_support` permanecen almacenados para futuras entregas. La UI los muestra como “Beta”, no como funcionalidad disponible.
- `image_storage_limit_mb` y `categories_limit` están disponibles para que los módulos de almacenamiento y categorías apliquen límites por plan.

`PlanService::normalizeCapabilities()` asigna valores restrictivos ante claves ausentes o datos almacenados inválidos. Las actualizaciones administrativas usan `PlanService::validateCapabilityInput()` para rechazar tipos y niveles no válidos.
