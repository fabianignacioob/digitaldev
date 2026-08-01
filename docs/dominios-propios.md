# Dominios propios en CatOps

Una vitrina publicada recibe por defecto la URL `subdominio.vitrinahub.cl` y, cuando el plan lo permite, puede sumar un dominio propio. El dominio propio se valida primero con un registro DNS TXT; CatOps no lo activa ni lo resuelve públicamente antes de esa verificación.

## Flujo para el cliente

1. En **Mis vitrinas > Editar vitrina > Dominio propio**, ingresa el hostname exacto: `pizzeria.cl` o `www.pizzeria.cl`.
2. CatOps muestra un registro TXT único. Crea ese registro en el proveedor que administre la zona DNS.
3. Crea el registro de enrutamiento indicado en la misma pantalla:
   - `www`: CNAME hacia el valor de `APP_CUSTOM_DOMAIN_CNAME_TARGET`.
   - dominio raíz (`@`): A hacia `APP_CUSTOM_DOMAIN_IPV4`.
4. Espera la propagación y pulsa **Verificar DNS**. Cuando el TXT coincide, el dominio pasa a verificado y activo.

NIC Chile registra dominios `.cl`, pero no entrega servicio DNS: el titular debe usar los DNS configurados para el dominio o el proveedor que administra su zona. Consulta la [FAQ de NIC sobre DNS](https://www.nic.cl/ayuda/faq/ins-06.html). Los cambios pueden demorar al menos unos minutos y también dependen del caché DNS, según la [FAQ técnica de NIC](https://www.nic.cl/ayuda/faq/tec-01.html).

## Variables de entorno

```dotenv
APP_PLATFORM_DOMAIN=catops.cl
APP_PUBLIC_BASE_DOMAIN=vitrinahub.cl
APP_PUBLIC_SCHEME=https
APP_CUSTOM_DOMAIN_CNAME_TARGET=vitrinahub.cl
APP_CUSTOM_DOMAIN_IPV4=203.0.113.10
DOMAIN_VERIFICATION_PREFIX=_catops-verify
DOMAIN_TLS_ASK_TOKEN=un-secreto-largo-y-aleatorio
```

`APP_CUSTOM_DOMAIN_IPV4` es obligatoria si se ofrecerán dominios raíz. No debe apuntar a una IP privada. Las variables solo se configuran en el entorno de despliegue y nunca en Git.

## DNS de CatOps y VitrinaHub

CatOps conserva su zona institucional y VitrinaHub aloja las vitrinas públicas:

```dns
@       A      <IP_PUBLICA_DE_CATOPS>
www     CNAME  catops.cl.
```

```dns
@       A      <IP_PUBLICA_DE_CATOPS>
*       A      <IP_PUBLICA_DE_CATOPS>
```

El segundo bloque corresponde a la zona `vitrinahub.cl`. El wildcard `*.vitrinahub.cl` no sustituye los registros que cada cliente debe crear en su propio dominio. Durante la transición, `*.catops.cl` puede conservarse y redirigir con HTTP 301 a la vitrina equivalente en VitrinaHub.

El certificado de `*.catops.cl` no cubre `*.vitrinahub.cl`: emite un certificado independiente para `vitrinahub.cl` y `*.vitrinahub.cl`. Los certificados wildcard requieren DNS-01.

## Nginx y rutas públicas

La aplicación debe recibir tanto los hosts institucionales como el wildcard público. El dominio raíz de VitrinaHub se redirige internamente por la aplicación a `https://catops.cl/`; no debe quedar servido por una página de error del proxy.

```nginx
server {
    listen 443 ssl http2;
    server_name catops.cl www.catops.cl vitrinahub.cl *.vitrinahub.cl *.catops.cl;
    root /var/www/catops/webroot;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ ^/index\.php(?:/|$) {
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root/index.php;
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
    }

    location ~ \.php$ { return 404; }
}
```

Configura el proxy para conservar el encabezado `Host` original y no inyectar `X-Forwarded-Host` como fuente de autoridad. `HostHeaderMiddleware` valida el host recibido; los dominios propios solo se aceptan después de la verificación DNS almacenada por CatOps.

## HTTPS recomendado: Caddy

Nginx puede recibir el tráfico de cualquier hostname, pero necesita un certificado previamente emitido para cada dominio. Para emitir certificados de forma controlada se recomienda Caddy con TLS bajo demanda y el endpoint interno de CatOps. El endpoint solo autoriza dominios que ya existen como `custom`, `verified` y `active`.

```caddyfile
{
    email ops@catops.cl
    on_demand_tls {
        ask https://catops.cl/internal/tls/allow?token={env.DOMAIN_TLS_ASK_TOKEN}
    }
}

https:// {
    tls {
        on_demand
    }
    reverse_proxy 127.0.0.1:7777
}
```

El token de `DOMAIN_TLS_ASK_TOKEN` es un secreto de infraestructura. Excluye la ruta `/internal/tls/allow` del registro de query strings del proxy si tu configuración registra URLs completas.

## Límites y seguridad

- Básico y prueba gratuita: sin dominio propio.
- Negocio: un dominio propio entre todos sus sitios.
- Full: hasta cinco dominios propios entre todos sus sitios.
- Un hostname es único globalmente y no puede asociarse a dos sitios.
- El hostname se normaliza a minúsculas, no acepta protocolos ni rutas y los dominios de la zona de CatOps quedan reservados.
- El middleware de Host solo acepta los dominios institucionales de CatOps, los subdominios de VitrinaHub, los enlaces antiguos permitidos y dominios propios activos/verificados. `X-Forwarded-Host` no se usa para decidir el tenant.
- El contenido de una vitrina nunca se borra al eliminar un dominio: sigue disponible mediante su subdominio de VitrinaHub.
