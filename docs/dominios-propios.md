# Dominios propios en CatOps

Un sitio publicado puede conservar su URL `subdominio.catops.cl` y, cuando el plan lo permite, sumar un dominio propio. El dominio propio se valida primero con un registro DNS TXT; CatOps no lo activa ni lo resuelve públicamente antes de esa verificación.

## Flujo para el cliente

1. En **Mis sitios > Editar sitio > Dominio propio**, ingresa el hostname exacto: `pizzeria.cl` o `www.pizzeria.cl`.
2. CatOps muestra un registro TXT único. Crea ese registro en el proveedor que administre la zona DNS.
3. Crea el registro de enrutamiento indicado en la misma pantalla:
   - `www`: CNAME hacia el valor de `APP_CUSTOM_DOMAIN_CNAME_TARGET`.
   - dominio raíz (`@`): A hacia `APP_CUSTOM_DOMAIN_IPV4`.
4. Espera la propagación y pulsa **Verificar DNS**. Cuando el TXT coincide, el dominio pasa a verificado y activo.

NIC Chile registra dominios `.cl`, pero no entrega servicio DNS: el titular debe usar los DNS configurados para el dominio o el proveedor que administra su zona. Consulta la [FAQ de NIC sobre DNS](https://www.nic.cl/ayuda/faq/ins-06.html). Los cambios pueden demorar al menos unos minutos y también dependen del caché DNS, según la [FAQ técnica de NIC](https://www.nic.cl/ayuda/faq/tec-01.html).

## Variables de entorno

```dotenv
APP_BASE_DOMAIN=catops.cl
APP_PUBLIC_SCHEME=https
APP_CUSTOM_DOMAIN_CNAME_TARGET=catops.cl
APP_CUSTOM_DOMAIN_IPV4=203.0.113.10
DOMAIN_VERIFICATION_PREFIX=_catops-verify
DOMAIN_TLS_ASK_TOKEN=un-secreto-largo-y-aleatorio
```

`APP_CUSTOM_DOMAIN_IPV4` es obligatoria si se ofrecerán dominios raíz. No debe apuntar a una IP privada. Las variables solo se configuran en el entorno de despliegue y nunca en Git.

## DNS de CatOps

CatOps necesita seguir teniendo su zona principal:

```dns
@       A      <IP_PUBLICA_DE_CATOPS>
*       A      <IP_PUBLICA_DE_CATOPS>
www     CNAME  catops.cl.
```

El wildcard cubre los subdominios `*.catops.cl`. No sustituye los registros que cada cliente debe crear en su propio dominio.

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
- El middleware de Host solo acepta el dominio base, los subdominios de CatOps y dominios propios activos/verificados. `X-Forwarded-Host` no se usa para decidir el tenant.
- El contenido de un sitio nunca se borra al eliminar un dominio: sigue disponible mediante su subdominio de CatOps.
