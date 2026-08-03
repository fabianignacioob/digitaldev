# Dominios de vitrinas CatOps

## Arquitectura

`APP_PUBLIC_BASE_DOMAIN=vitrinahub.cl` genera `https://{subdomain}.vitrinahub.cl`. Un único vhost Nginx con certificado wildcard para `vitrinahub.cl` y `*.vitrinahub.cl` envía cualquier subdominio a `webroot/index.php`; `HostHeaderMiddleware` y `PublicSiteResolverService` resuelven el sitio desde `Sites.subdomain`. No hay vhost por vitrina interna.

Un dominio propio se guarda inicialmente como `pending_dns`. El cliente publica el TXT `_catops-verify.{dominio}` y el CNAME al valor de `APP_CUSTOM_DOMAIN_CNAME_TARGET`; si existe `APP_CUSTOM_DOMAIN_IPV4`, también se puede usar A. La verificación deja el dominio `verified`; el cron lo lleva a `provisioning` y luego a `active` o `failed`. Sólo `active` puede servir una vitrina.

## Variables de entorno

```
APP_PLATFORM_DOMAIN=catops.cl
APP_PUBLIC_BASE_DOMAIN=vitrinahub.cl
APP_PUBLIC_SCHEME=https
APP_CUSTOM_DOMAIN_CNAME_TARGET=srv93.catops.cl
APP_CUSTOM_DOMAIN_IPV4=200.35.159.93
DOMAIN_VERIFICATION_PREFIX=_catops-verify
DOMAIN_DNS_VERIFY_COOLDOWN_SECONDS=60
DOMAIN_PROVISIONING_ENABLED=true
DOMAIN_PROVISIONER_PATH=/usr/local/sbin/provision-catops-domain
DOMAIN_PROVISIONING_LEASE_MINUTES=15
```

`APP_CUSTOM_DOMAIN_IPV4` es necesario para dominios raíz cuando el proveedor DNS no permite CNAME en el apex. Estos valores pertenecen al archivo compartido de producción, nunca al repositorio.

## Instalación operativa

```sh
sudo install -o root -g www-data -m 0750 ops/provision-catops-domain /usr/local/sbin/provision-catops-domain
sudo install -o root -g root -m 0600 ops/domain-provisioning.conf.example /etc/catops/domain-provisioning.conf
sudo visudo -f /etc/sudoers.d/catops-domain-provisioning
```

Contenido de sudoers, ajustando el usuario FPM y el usuario que ejecuta el cron:

```
www-data ALL=(root) NOPASSWD: /usr/local/sbin/provision-catops-domain --domain *
deploy ALL=(root) NOPASSWD: /usr/local/sbin/provision-catops-domain --domain *
```

El script vuelve a validar el FQDN y rechaza zonas CatOps/VitrinaHub. No conceder `sudo` a intérpretes, `nginx`, `certbot` o un shell. La regla se limita al script raíz anterior.

Cron recomendado, ejecutado por el usuario de despliegue y no por PHP-FPM:

```
*/5 * * * * deploy cd /var/www/catops/current && bin/cake domains provision --limit=20 >> /var/log/catops/domains-provision.log 2>&1
```

Para rollback, desactive o elimine el registro desde `/admin/domains`, borre sólo el vhost de ese FQDN, ejecute `nginx -t && systemctl reload nginx`, y opcionalmente `certbot delete --cert-name dominio`. El contenido de la vitrina no se elimina.

## Checklist de producción

1. Wildcard DNS/TLS de `vitrinahub.cl` responde y el vhost wildcard apunta a `webroot/index.php`.
2. `APP_FULL_BASE_URL`, `APP_PLATFORM_DOMAIN` y `APP_PUBLIC_BASE_DOMAIN` están definidos.
3. `dns_get_record`, `proc_open`, `curl`, Nginx, Certbot y PHP-FPM están disponibles para los roles correctos.
4. Instalar el script y configuración raíz; ejecutar `sudo -u www-data sudo -n /usr/local/sbin/provision-catops-domain --domain ejemplo-invalido` debe fallar sin abrir shell.
5. Migrar: `bin/cake migrations migrate`.
6. Crear dominio, publicar TXT/CNAME o A, verificar desde la UI y correr `bin/cake domains provision --dry-run` antes del cron.
