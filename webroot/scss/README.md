# Estilos CatOps

Los archivos `*.min.scss` de esta carpeta son la fuente de estilos propia de
CatOps. Se compilan hacia `webroot/css/*.min.css` mediante:

```sh
npm run build:css
```

Durante desarrollo, usa `npm run watch:css`.

Bootstrap, Font Awesome, Normalize y Milligram se conservan en `webroot/css/`
como dependencias de terceros. Los únicos bloques `<style>` restantes en las
vistas públicas definen variables CSS dinámicas por sitio, como colores,
tipografías y fondos elegidos por cada cliente.
