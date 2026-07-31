# Webpay Plus: prueba de integración de $1

Esta guía usa la integración real de Webpay Plus y el comercio configurado en
variables de entorno. La orden de prueba usa el plan interno e inactivo
`webpay-integration-test`: cuesta $1 CLP, solo puede crearla un administrador
de plataforma y no crea, renueva ni modifica suscripciones.

## Configuración local

En `config/.env` (archivo ignorado por Git) configura las credenciales de
**integración** entregadas por Transbank para el comercio. No uses credenciales
de producción ni subas este archivo al repositorio.

```bash
export WEBPAY_ENV="integration"
export WEBPAY_COMMERCE_CODE="597053091200"
export WEBPAY_API_KEY="<api-key-de-integracion-asociada-al-comercio>"
export WEBPAY_RETURN_URL="http://localhost:7777/payments/webpay/return"
export WEBPAY_ENABLE_TEST_ORDER="true"
export WEBPAY_PENDING_EXPIRATION_MINUTES="10"
```

Ejecuta las migraciones antes de crear la prueba:

```bash
bin/cake migrations migrate
```

## Crear el pago de prueba

Con un administrador existente, ejecuta:

```bash
bin/cake payments create_integration_test --user-id=<ID_ADMIN>
```

El comando imprime la URL de Transbank y el `token_ws` solamente en la consola.
No incorpora el token en auditorías ni respuestas de la aplicación. Abre la URL
en el navegador, envía un formulario `POST` cuyo único campo sea:

```html
<input name="token_ws" value="TOKEN_GENERADO">
```

El formulario de CatOps en `templates/Payments/redirect.php` ya lo hace de
forma automática cuando se inicia un pago desde el sistema.

## Aprobación de la prueba

En el portal de integración selecciona una tarjeta de **crédito**, sin cuotas y
el resultado de aprobación. Usa las credenciales de tarjeta, RUT y clave de
autenticación vigentes publicadas por Transbank para el ambiente de integración:

<https://transbankdevelopers.cl/documentacion/como_empezar#ambientes>

Al retornar a `WEBPAY_RETURN_URL`, CatOps valida `response_code`, estado,
monto, moneda, `buy_order` y `session_id` antes de confirmar el pago. La orden
finaliza como `paid` y conserva la auditoría, sin alterar una licencia.

## Comprobaciones

```sql
SELECT id, status, expected_amount, confirmed_amount, currency, buy_order,
       authorized_at, paid_at, processed_at
FROM payments
WHERE plan_slug = 'webpay-integration-test'
ORDER BY id DESC
LIMIT 1;

SELECT action, entity_type, entity_id, created
FROM audit_logs
WHERE action IN ('payment.integration_test_created', 'payment.integration_test_completed')
ORDER BY id DESC
LIMIT 10;
```

Si la creación en la pasarela falla, el pago local se marca `failed` con
`gateway_setup_failed`; no queda disponible para conciliación ni puede renovar
una suscripción.
