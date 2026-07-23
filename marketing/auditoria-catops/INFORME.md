# Auditoría UI/UX y frontend de CatOps

Fecha: 19 de julio de 2026  
Entorno: instancia local de desarrollo, sin pagos ni datos de producción.  
Dispositivos: escritorio 1440 × 900 y móvil 390 × 844.

## Alcance recorrido

Se recorrieron con Playwright el registro, inicio de sesión, panel vacío y con contenido, creación y edición de un sitio, configuración visual, categorías, alta y edición de un producto, carga y reemplazo de imagen, vista previa, publicación y sitio público. Se verificaron validaciones nativas, mensajes de éxito, confirmación de publicación, estados vacíos y navegación responsive.

Para no afectar información real se usaron una cuenta, una suscripción y un sitio de auditoría locales. No se inició ningún pago ni se modificaron datos de producción.

## Resultado ejecutivo

Las mejoras implementadas corrigen los problemas más relevantes sin cambiar la lógica comercial ni abandonar Bootstrap o la identidad visual de CatOps:

- Contraste accesible en acciones y hero público.
- Jerarquía semántica correcta y navegación por teclado con enlace para saltar al contenido.
- Etiquetas únicas y accesibles en categorías y productos.
- Estado de publicación inequívoco en la edición.
- Imágenes nuevas convertidas a WebP y limitadas a 1200 px.
- Textos de navegación y estados traducidos y consistentes.
- Autocompletado correcto en autenticación y anuncios accesibles para confirmaciones y errores.

## Mediciones

| Métrica Lighthouse, sitio público móvil | Antes | Después |
| --- | ---: | ---: |
| Rendimiento | 56 | 100 |
| Accesibilidad | 84 | 95 |
| Buenas prácticas | 100 | 100 |
| SEO | 100 | 100 |
| First Contentful Paint | 6,3 s | 1,0 s |
| Largest Contentful Paint | 17,8 s | 1,2 s |
| Time to Interactive | 17,8 s | 2,2 s |

La imagen de producto usada en el recorrido bajó de 2,1 MB en PNG a 98 KB en WebP. La auditoría final no reportó fallos de contraste. El único fallo de accesibilidad restante es el `iframe` sin título que inyecta DebugKit en modo desarrollo; no forma parte de la interfaz de producción.

Se intentó ejecutar axe CLI por separado, pero el ChromeDriver descargado soportaba Chrome 151 y el navegador local era Chrome 150. La categoría de accesibilidad de Lighthouse sí ejecutó sus reglas basadas en axe y produjo los hallazgos anteriores.

## Hallazgos priorizados

| Prioridad | Problema encontrado | Impacto para el usuario | Recomendación | Estado | Archivo o vista |
| --- | --- | --- | --- | --- | --- |
| Alta | Texto blanco sobre el fondo claro por defecto del hero; botones naranjos con contraste 3,03:1. | El título, slogan y acciones pueden resultar ilegibles para usuarios con baja visión o bajo brillo. | Calcular un color seguro según el fondo y usar un naranja de acción que supere 4,5:1. | Implementado | `templates/PublicSites/catalog.php`, `templates/layout/auth.php`, `templates/layout/dashboard.php` |
| Alta | IDs repetidos entre formularios de creación y edición. Los lectores de pantalla unían varias etiquetas o dejaban campos sin nombre. | No era posible identificar con fiabilidad nombre, categoría, precio o imagen al editar productos. | Asignar IDs únicos por formulario y entidad. | Implementado | `templates/Catalogs/edit.php` |
| Alta | Las imágenes PNG se conservaban a gran tamaño; una imagen visible pesaba 2,1 MB. | Esperas largas, alto consumo de datos y LCP de 17,8 s en móvil. | Convertir nuevas cargas a WebP, limitar a 1200 px y diferir imágenes de productos. | Implementado | `src/Service/LocalImageStorageService.php`, `templates/PublicSites/catalog.php` |
| Alta | El sitio público comenzaba con un `h2` y carecía de `h1`. | Estructura confusa para tecnologías asistivas y menor claridad semántica/SEO. | Usar un único `h1` para el título principal y mantener categorías/productos como `h2`/`h3`. | Implementado | `templates/PublicSites/catalog.php` |
| Media | La acción “Publicar” seguía visible aun cuando el estado ya era publicado, y coexistía con el selector de estado. | Duda sobre si la publicación funcionó y riesgo de repetir una acción innecesaria. | Mostrar un estado “Publicado” inequívoco después de publicar y ocultar la acción redundante. | Implementado | `templates/Sites/edit.php` |
| Media | No existía acceso directo para saltar la navegación ni foco visible consistente. | Usuarios de teclado repiten enlaces en cada pantalla y pueden perder la ubicación del foco. | Añadir “Saltar al contenido”, `aria-label` en navegación y foco visible. | Implementado | `templates/layout/dashboard.php`, `templates/PublicSites/catalog.php`, `templates/layout/auth.php` |
| Media | El catálogo concentra diseño, categorías y productos en una página muy larga. | En móvil exige mucho desplazamiento y aumenta la carga cognitiva y el riesgo de editar el bloque equivocado. | Dividir en pestañas o acordeones con resumen de estado, conservando las rutas y formularios existentes. | Pendiente; requiere mayor intervención | `templates/Catalogs/edit.php` |
| Media | Edición del sitio presenta dos formularios con botones “Guardar” independientes sin indicador de cambios pendientes. | El usuario puede guardar un bloque y creer que guardó toda la página. | Diferenciar visualmente cada ámbito y añadir aviso de cambios sin guardar por bloque. | Pendiente | `templates/Sites/edit.php` |
| Media | El registro no explica requisitos de contraseña. | Ensayo y error si se incorporan o endurecen reglas de seguridad. | Mostrar requisitos reales junto al campo y mantenerlos sincronizados con la validación del servidor. | Pendiente; depende de definición de seguridad | `templates/Users/register.php`, `src/Model/Table/UsersTable.php` |
| Media | La tarjeta de suscripción ocupa más altura y protagonismo que los sitios, especialmente en móvil. | La tarea principal “administrar sitios” queda debajo del pliegue. | Compactar la suscripción en un resumen colapsable y priorizar la lista de sitios. | Pendiente | `templates/Dashboard/index.php` |
| Baja | Se mezclaban “Preview”, estados técnicos en inglés y referencias internas a “alpha”. | Reduce la claridad y la consistencia del producto en español. | Usar “Vista previa”, estados traducidos y texto orientado al usuario. | Implementado | `templates/Dashboard/index.php`, `templates/Sites/edit.php`, `templates/Catalogs/edit.php` |
| Baja | El ejemplo del subdominio no aparecía como ayuda visible del formulario. | El usuario no anticipaba la URL final antes de crear el sitio. | Mostrar un ejemplo inmediatamente bajo el campo. | Implementado | `templates/Sites/add.php` |

## Estados, errores y confirmaciones

- Los estados vacíos del panel, categorías y productos explican el siguiente paso y ofrecen una acción clara.
- La validación nativa impide enviar campos obligatorios vacíos.
- Los mensajes de éxito y error ahora se anuncian mediante `role="status"` o `role="alert"`.
- La publicación solicita confirmación. Una vez publicada, la cabecera muestra el estado en lugar de repetir la acción.
- El formulario de registro se inspeccionó y validó sin enviar correo real.

## Capturas

Las capturas previas están en `marketing/auditoria-catops/antes/` y las verificaciones posteriores en `marketing/auditoria-catops/despues/`. Incluyen autenticación, panel, creación, edición, catálogo, vista previa y sitio público en escritorio y móvil.

## Verificación técnica

- PHPUnit: 101 pruebas, 389 aserciones, todas correctas.
- Lighthouse final: rendimiento 100, accesibilidad 95, buenas prácticas 100, SEO 100.
- Revisión Playwright posterior: etiquetas accesibles únicas, jerarquía de encabezados correcta, navegación responsive sin overflow horizontal y estado publicado visible.
- PHPCS global: continúa fallando por deuda de estilo preexistente en numerosos controladores, servicios y pruebas; no se aplicó una corrección masiva para evitar mezclar cambios ajenos a esta auditoría.
