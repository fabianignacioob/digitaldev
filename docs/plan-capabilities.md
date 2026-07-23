# Capacidades de planes

La tabla `plans` es la fuente de verdad. Su columna JSON `capabilities` se consume siempre mediante `App\Service\PlanService`; no se deben crear reglas basadas en el slug del plan.

## Uso actual

- `getLimit($userId, 'sites_configured_limit')` y `getLimit($userId, 'sites_published_limit')` controlan creación y publicación.
- `hasFeature($userId, 'categories_enabled')` y `hasFeature($userId, 'featured_items_enabled')` controlan las funciones ya disponibles del catálogo.
- `siteUsage($userId)` entrega consumo y el estado heredado de exceso de límite para mostrar advertencias sin alterar sitios existentes.

## Capacidades preparadas

- `customization_level`: `basic`, `extended` o `advanced`. La base actual usa `basic`; los niveles superiores quedan reservados para futuros controles de diseño.
- `analytics_level`: `none`, `basic` o `advanced`. Cuando exista analytics, el módulo debe consultar este valor antes de calcular o mostrar datos.
- `seo_level`: `none`, `basic`, `standard` o `advanced`. Hoy el nivel básico corresponde a título y descripción SEO; niveles superiores deben validarse antes de habilitar herramientas nuevas.
- `qr_enabled`, `premium_themes_enabled`, `catops_branding_removable` y `priority_support` permanecen almacenados para futuras entregas. No deben exponerse como disponibles hasta que su módulo confirme soporte.
- `image_storage_limit_mb` y `categories_limit` están disponibles para que los módulos de almacenamiento y categorías apliquen límites por plan.

`PlanService::normalizeCapabilities()` asigna valores restrictivos ante claves ausentes o datos almacenados inválidos. Las actualizaciones administrativas usan `PlanService::validateCapabilityInput()` para rechazar tipos y niveles no válidos.
