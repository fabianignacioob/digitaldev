<!doctype html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= h($this->fetch('title') ?: 'CatOps') ?></title>
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <style>
      * {
        box-sizing: border-box;
      }
      body {
        margin: 0;
        min-height: 100vh;
        display: grid;
        place-items: center;
        padding: 24px 0;
        background: linear-gradient(135deg, #fbfaf7, #fff1e7 55%, #f8fbff);
        color: #17202a;
        font-family: Inter, Arial, sans-serif;
      }
      .auth-card {
        width: min(440px, calc(100vw - 32px));
        padding: 32px;
        border: 1px solid #f0ded1;
        border-radius: 22px;
        background: rgba(255, 255, 255, 0.86);
        box-shadow: 0 24px 70px rgba(42, 54, 71, 0.12);
        backdrop-filter: blur(18px);
      }
      .auth-logo {
        width: 108px;
        display: block;
        margin-bottom: 22px;
      }
      h1 {
        margin: 0 0 10px;
        font-size: 28px;
      }
      p {
        color: #65717c;
        line-height: 1.6;
      }
      form {
        display: grid;
        gap: 16px;
        margin-top: 22px;
      }
      .input {
        display: grid;
        gap: 8px;
      }
      label {
        display: block;
        font-weight: 700;
      }
      input,
      select,
      textarea {
        width: 100%;
        min-height: 46px;
        padding: 0 14px;
        border: 1px solid #e7d9cf;
        border-radius: 14px;
        font: inherit;
        background: #fff;
      }
      input:focus,
      select:focus,
      textarea:focus {
        border-color: #f36b16;
        box-shadow: 0 0 0 4px rgba(243, 107, 22, 0.12);
        outline: none;
      }
      .submit {
        margin-top: 6px;
      }
      button,
      .button {
        display: inline-flex;
        width: 100%;
        min-height: 46px;
        align-items: center;
        justify-content: center;
        padding: 0 18px;
        border: 0;
        border-radius: 999px;
        background: #c6530b;
        color: #fff;
        font: inherit;
        font-weight: 800;
        text-decoration: none;
        cursor: pointer;
      }
      button.secondary,
      .button.secondary {
        border: 1px solid #e7d9cf;
        background: #fff;
        color: #0a2a66;
      }
      .muted-link {
        color: #0a2a66;
        font-weight: 700;
      }
      a:focus-visible,
      button:focus-visible,
      input:focus-visible,
      select:focus-visible,
      textarea:focus-visible {
        outline: 3px solid #0a2a66;
        outline-offset: 3px;
      }
      .message {
        margin: 0 0 18px;
        padding: 12px 14px;
        border-radius: 14px;
        background: #fff1e7;
      }
      .error-message {
        color: #b84010;
        font-size: 13px;
        font-weight: 700;
      }
      @media (max-width: 520px) {
        body {
          place-items: start center;
          padding-top: 18px;
        }
        .auth-card {
          padding: 24px;
        }
      }
    </style>
  </head>
  <body>
    <main class="auth-card">
      <a href="/"><img class="auth-logo" src="/img/catops-logo.png" alt="CatOps"></a>
      <?= $this->Flash->render() ?>
      <?= $this->fetch('content') ?>
    </main>
  </body>
</html>
