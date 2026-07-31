# Integración de Resend

CatOps usa el transporte SMTP nativo de CakePHP conectado a Resend. No es necesario instalar un SDK adicional.

## 1. Crear la cuenta y verificar el dominio

1. Crea una cuenta en [Resend](https://resend.com/).
2. Entra a **Domains** y agrega un dominio propio. Se recomienda usar un subdominio exclusivo para correo transaccional, por ejemplo `notifications.tudominio.cl`.
3. Copia en el proveedor DNS los registros SPF y DKIM que Resend muestra para ese dominio.
4. Espera a que Resend marque el dominio como **Verified**.
5. Crea una API key con permiso de envío. Aunque la integración usa SMTP, la API key funciona como la contraseña SMTP.

Usa un remitente que pertenezca al dominio verificado, por ejemplo `no-reply@notifications.tudominio.cl`.

## 2. Configurar DMARC

Agrega inicialmente este registro TXT en `_dmarc.notifications.tudominio.cl`:

```text
v=DMARC1; p=none; rua=mailto:dmarc@tudominio.cl
```

Cuando confirmes que SPF y DKIM pasan correctamente, puedes cambiar gradualmente la política a `quarantine` y luego a `reject`.

## 3. Configurar CatOps localmente

Copia el archivo de ejemplo si todavía no tienes configuración local:

```bash
cp config/.env.example config/.env
```

Completa estos valores en `config/.env`:

```bash
export APP_FULL_BASE_URL="https://tudominio.cl"

export EMAIL_HOST="smtp.resend.com"
export EMAIL_PORT="587"
export EMAIL_USERNAME="resend"
export EMAIL_PASSWORD="re_xxxxxxxxxxxxxxxxx"
export EMAIL_TLS="true"
export EMAIL_FROM="no-reply@notifications.tudominio.cl"
export EMAIL_FROM_NAME="CatOps"
```

No subas `config/.env` ni la API key al repositorio.

## 4. Ejecutar la migración

La recuperación de contraseña necesita tres campos nuevos en `users`:

```bash
bin/cake migrations migrate
```

## 5. Probar cada flujo

### Verificación y bienvenida

1. Abre `/registro`.
2. Crea una cuenta con un correo que controles.
3. Confirma que llegue el código de seis dígitos.
4. Ingresa el código en `/verificar-correo`.
5. Confirma que llegue el correo de bienvenida.

### Recuperación de contraseña

1. Abre `/login` y selecciona **¿Olvidaste tu contraseña?**.
2. Ingresa el correo de la cuenta.
3. Abre el enlace recibido.
4. Define una contraseña de al menos ocho caracteres.
5. Inicia sesión con la nueva contraseña.

El enlace de recuperación dura 30 minutos y se invalida después de cambiar la contraseña.

### Pagos

1. Ejecuta un pago de integración o un pago real controlado.
2. Para un pago aprobado debe llegar la confirmación con plan, monto y referencia.
3. Para un pago rechazado debe llegar el aviso indicando que la suscripción no fue modificada.

## 6. Verificar entregabilidad en Resend

En Resend revisa **Emails** y confirma que los mensajes estén en estado delivered. Si hay rebotes, corrige el destinatario y revisa SPF, DKIM y DMARC antes de aumentar el volumen.

## Flujos implementados

- `EmailService`: punto único para enviar correos transaccionales.
- Código de verificación al registrar una cuenta.
- Correo de bienvenida después de verificar el correo.
- Confirmación de pago aprobado.
- Aviso de pago rechazado.
- Recuperación de contraseña con token hash, expiración y uso único.

Para una siguiente etapa conviene agregar una cola/outbox de correos y webhooks de Resend para reintentar entregas fallidas sin bloquear el flujo del pago.
