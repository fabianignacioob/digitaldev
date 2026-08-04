<?php
declare(strict_types=1);

$this->assign('title', 'CatOps | Crea la vitrina digital de tu negocio');
$this->assign('metaDescription', 'Crea una vitrina digital para mostrar tu carta, catálogo o servicios y compártela en un solo enlace.');
?>
<section class="hero" id="inicio">
  <div class="container hero-grid">
    <div>
      <span class="eyebrow">Para pequeños negocios en Chile</span>
      <h1>Crea la <span class="accent">vitrina digital</span> de tu negocio</h1>
      <p class="hero-copy">Muestra tus productos, servicios o carta en un solo lugar. Actualízala cuando quieras y compártela por WhatsApp, Instagram o código QR.</p>
      <div class="actions">
        <a class="button" href="/registro">Crear mi vitrina</a>
        <a class="button secondary" href="#ejemplos">Ver una vitrina de ejemplo</a>
      </div>
      <p class="support-line"><span>Sin conocimientos técnicos</span><span>Adaptada a celulares</span><span>Fácil de actualizar</span></p>
    </div>
  </div>
</section>

<section class="section-soft" id="solucion">
  <div class="container">
    <div class="section-head">
      <span class="section-kicker">El problema que resuelve</span>
      <h2>Deja de enviar fotos, precios y menús uno por uno</h2>
      <p>Reúne lo importante de tu negocio en una página clara, siempre disponible y fácil de compartir.</p>
    </div>
    <div class="grid three">
      <article class="card problem-card"><span class="card-icon">1</span><h3>Todo ordenado</h3><p>Productos, servicios, precios, imágenes y contacto reunidos en un solo lugar.</p></article>
      <article class="card problem-card"><span class="card-icon">2</span><h3>Siempre actualizado</h3><p>Cambia un precio, agrega una foto o edita un servicio desde tu panel cuando lo necesites.</p></article>
      <article class="card problem-card"><span class="card-icon">3</span><h3>Fácil de compartir</h3><p>Envía el mismo enlace por WhatsApp, Instagram, bio, QR o tus redes sociales.</p></article>
    </div>
  </div>
</section>

<section class="types-section" id="para-quien">
  <div class="container types-layout">
    <article class="types-intro">
      <div>
        <span class="section-kicker">Una solución, tres formatos</span>
        <h2>Elige la vitrina que necesita tu negocio</h2>
        <p>Una base simple para restaurantes, tiendas, emprendimientos y servicios que venden por mensaje.</p>
      </div>
      <a class="button secondary" href="/servicio">Conocer el servicio</a>
    </article>
    <div class="types-list">
      <article class="card type-card"><span class="type-label">Para restaurantes y cafeterías</span><h3>Carta digital</h3><p>Muestra preparaciones, precios e imágenes en una carta que puedes actualizar sin reenviar archivos.</p><a class="type-link" href="/registro">Crear una carta</a></article>
      <article class="card type-card"><span class="type-label">Para tiendas y emprendimientos</span><h3>Catálogo de productos</h3><p>Ordena productos, fotos, descripciones y precios para que tus clientes consulten desde un solo enlace.</p><a class="type-link" href="/registro">Crear un catálogo</a></article>
      <article class="card type-card"><span class="type-label">Para profesionales y prestadores</span><h3>Catálogo de servicios</h3><p>Presenta lo que haces, valores referenciales y formas de contacto para recibir consultas por WhatsApp.</p><a class="type-link" href="/registro">Crear mis servicios</a></article>
    </div>
  </div>
</section>

<section class="section-soft" id="ejemplos">
  <div class="container examples-showcase">
    <div class="section-head"><span class="section-kicker">Ejemplos reales</span><h2>Así se ve la vitrina de tu negocio</h2><p>Conoce cómo se presenta una carta, un catálogo y una página de servicios. Cada ejemplo funciona en celular, tablet y computador.</p></div>
    <div class="examples-layout">
      <div class="proof-media"><img src="/img/headbanner.png" alt="CatOps mostrado en distintos dispositivos"></div>
      <div class="demo-links" aria-label="Demos de vitrinas CatOps">
        <article class="demo-link"><strong>Carta por categorías</strong><span>Para restaurantes, cafeterías y locales que organizan preparaciones por tipo.</span><a class="button" href="/s/demo-carta" target="_blank" rel="noopener">Ver ejemplo</a></article>
        <article class="demo-link"><strong>Catálogo simple</strong><span>Para emprendimientos y tiendas que necesitan mostrar productos de forma directa.</span><a class="button" href="/s/demo-catalogo" target="_blank" rel="noopener">Ver ejemplo</a></article>
        <article class="demo-link"><strong>Página de servicios</strong><span>Para empresas y profesionales que reciben consultas y cotizaciones por mensaje.</span><a class="button" href="/s/demo-servicio" target="_blank" rel="noopener">Ver ejemplo</a></article>
      </div>
    </div>
  </div>
</section>

<section id="planes">
  <div class="container">
    <div class="plans-header">
      <div class="section-head"><span class="section-kicker">Planes</span><h2>Elige un plan para partir simple y crecer con orden</h2><p>Renovación mensual o anual mediante pago seguro. Sin cobros automáticos.</p></div>
      <div class="trial-box"><strong>Prueba gratuita</strong><span>7 días del plan Básico al publicar tu primera vitrina.</span></div>
    </div>
    <?= $this->element('marketing/plan_cards', compact('plans', 'currentUser')) ?>
  </div>
</section>

<section class="section-blue" id="como-funciona">
  <div class="container">
    <div class="section-head"><span class="section-kicker">Cómo funciona</span><h2>Publica en tres pasos</h2><p>Sin instalar programas ni aprender herramientas difíciles.</p></div>
    <div class="grid three steps">
      <article class="step"><h3>Elige el tipo de vitrina</h3><p>Parte con una carta digital, catálogo de productos o catálogo de servicios.</p></article>
      <article class="step"><h3>Agrega tu contenido</h3><p>Sube tu logo, fotos, productos, precios, descripciones y datos de contacto.</p></article>
      <article class="step"><h3>Publica y comparte</h3><p>Activa tu enlace y compártelo donde tus clientes ya te encuentran.</p></article>
    </div>
  </div>
</section>

<section class="section-soft" id="confianza">
  <div class="container">
    <div class="section-head"><span class="section-kicker">Lo esencial</span><h2>Una herramienta simple para mostrar lo que haces</h2><p>CatOps se enfoca en darte una página clara para que tus clientes entiendan tu oferta y puedan contactarte.</p></div>
    <div class="grid trust-grid">
      <article class="card problem-card"><h3>Panel privado</h3><p>Administra contenido, productos y datos de contacto sin tocar código.</p></article>
      <article class="card problem-card"><h3>Compartible</h3><p>Tu enlace se puede enviar por WhatsApp, Instagram, redes sociales o QR.</p></article>
      <article class="card problem-card"><h3>Flexible</h3><p>Parte con un enlace VitrinaHub y escala según las necesidades de tu negocio.</p></article>
    </div>
  </div>
</section>

<section id="preguntas">
  <div class="container">
    <div class="section-head"><span class="section-kicker">Preguntas frecuentes</span><h2>Lo esencial antes de crear tu vitrina</h2><p>Respuestas rápidas para entender cómo funciona CatOps antes de publicar.</p></div>
    <div class="faq-list">
      <details><summary>¿Necesito saber programación?</summary><p>No. El panel está pensado para editar textos, productos, precios, imágenes y datos de contacto sin programar.</p></details>
      <details><summary>¿Puedo actualizar mis productos o servicios?</summary><p>Sí. Puedes mantener precios, descripciones, fotos y disponibilidad desde tu panel.</p></details>
      <details><summary>¿Puedo usar mi propio dominio?</summary><p>Sí. Todas las vitrinas reciben un subdominio de VitrinaHub y los planes Negocio y Full permiten conectar dominios propios tras verificar sus registros DNS.</p></details>
      <details><summary>¿Se ve bien en celulares?</summary><p>Sí. Las páginas se construyen para funcionar en celular, tablet y computador.</p></details>
      <details><summary>¿Incluye carrito o compras online?</summary><p>No. CatOps está pensado para mostrar tu oferta y recibir consultas; no incluye carrito ni pagos para clientes finales.</p></details>
    </div>
  </div>
</section>

<section class="final-cta" id="crear">
  <div class="container cta-inner">
    <div><span class="section-kicker">Lleva tu negocio al mundo digital</span><h2>Publica lo que vendes en un solo enlace</h2><p>Parte con una carta, catálogo o página de servicios que puedas actualizar cuando quieras.</p></div>
    <div class="actions"><a class="button" href="/registro">Crear mi vitrina</a><a class="button secondary" href="/login">Ingresar</a></div>
  </div>
</section>
