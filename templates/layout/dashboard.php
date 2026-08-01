<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= h($this->fetch('title') ?: 'Panel CatOps') ?></title>
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="stylesheet" href="/css/bootstrap.min.css">
    <style>
        :root {
            --catops-navy: #10213d;
            --catops-navy-soft: #1a355e;
            --catops-orange: #dc651e;
            --catops-orange-dark: #bd5013;
            --catops-canvas: #f8f5ed;
            --catops-card: #fffefd;
            --catops-muted: #665f56;
            --catops-line: #e8e1d6;
            --catops-success: #16834a;
            --catops-warning: #bd7612;
            --catops-danger: #b94330;
            --catops-shadow: 0 10px 24px rgba(34, 43, 55, 0.06);
        }

        * {
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            margin: 0;
            min-width: 320px;
            background: var(--catops-canvas);
            color: var(--catops-navy);
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            letter-spacing: 0;
        }

        a {
            color: var(--catops-navy);
            font-weight: 700;
            text-decoration: none;
        }

        a:hover {
            color: var(--catops-orange-dark);
        }

        img {
            max-width: 100%;
        }

        h1,
        h2,
        h3,
        p {
            margin-top: 0;
        }

        h1,
        h2,
        h3 {
            color: var(--catops-navy);
            letter-spacing: 0;
        }

        p {
            color: var(--catops-muted);
            line-height: 1.6;
        }

        .panel-shell {
            width: min(1180px, calc(100% - 40px));
            margin: 0 auto;
        }

        .panel-topbar {
            position: sticky;
            top: 0;
            z-index: 30;
            border-bottom: 1px solid rgba(232, 225, 214, 0.92);
            background: rgba(248, 245, 237, 0.92);
            backdrop-filter: blur(14px);
        }

        .panel-nav {
            display: flex;
            min-height: 72px;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
        }

        .panel-brand {
            display: inline-flex;
            align-items: center;
            flex-shrink: 0;
        }

        .panel-brand img {
            display: block;
            width: 52px;
            height: 52px;
            border: 1px solid var(--catops-line);
            border-radius: 50%;
            background: #fff;
            object-fit: cover;
        }

        .panel-nav-toggle {
            display: none;
            width: 42px;
            height: 42px;
            border: 1px solid var(--catops-line);
            border-radius: 10px;
            background: var(--catops-card);
            color: var(--catops-navy);
            font: inherit;
            font-size: 21px;
            line-height: 1;
        }

        .panel-links {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .panel-links a {
            display: inline-flex;
            min-height: 40px;
            align-items: center;
            padding: 0 11px;
            border-radius: 8px;
            color: #4d4b47;
            font-size: 14px;
            font-weight: 750;
        }

        .panel-links a:hover,
        .panel-links a[aria-current="page"] {
            background: #fff0e5;
            color: var(--catops-orange-dark);
        }

        .panel-links .nav-logout {
            color: var(--catops-navy);
        }

        .skip-link {
            position: fixed;
            top: 10px;
            left: 10px;
            z-index: 100;
            padding: 10px 14px;
            border-radius: 8px;
            background: var(--catops-navy);
            color: #fff;
            transform: translateY(-160%);
        }

        .skip-link:focus {
            transform: translateY(0);
            color: #fff;
        }

        a:focus-visible,
        button:focus-visible,
        input:focus-visible,
        select:focus-visible,
        textarea:focus-visible,
        summary:focus-visible {
            outline: 3px solid var(--catops-navy);
            outline-offset: 3px;
        }

        main {
            padding: 38px 0 72px;
        }

        .page-head {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 26px;
        }

        .page-head h1 {
            margin-bottom: 6px;
            font-size: clamp(28px, 3.5vw, 40px);
            font-weight: 800;
            line-height: 1.08;
        }

        .page-head p {
            margin-bottom: 0;
        }

        .page-head>.button,
        .page-head>.toolbar {
            flex-shrink: 0;
        }

        h2 {
            margin-bottom: 10px;
            font-size: 21px;
            font-weight: 800;
        }

        h3 {
            margin-bottom: 8px;
            font-size: 17px;
            font-weight: 800;
        }

        .button,
        button {
            display: inline-flex;
            min-height: 40px;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 0 15px;
            border: 1px solid transparent;
            border-radius: 10px;
            background: var(--catops-orange);
            color: #fff;
            font: inherit;
            font-size: 14px;
            font-weight: 800;
            line-height: 1.2;
            cursor: pointer;
            box-shadow: 0 5px 12px rgba(220, 101, 30, 0.15);
        }

        .button:hover,
        button:hover {
            background: var(--catops-orange-dark);
            color: #fff;
        }

        .button.secondary,
        button.secondary {
            border-color: var(--catops-line);
            background: var(--catops-card);
            color: var(--catops-navy);
            box-shadow: 0 2px 5px rgba(34, 43, 55, 0.04);
        }

        .button.secondary:hover,
        button.secondary:hover {
            background: #fff6ef;
            color: var(--catops-orange-dark);
        }

        .button.dark,
        button.dark {
            background: var(--catops-navy);
            color: #fff;
            box-shadow: 0 5px 12px rgba(16, 33, 61, 0.16);
        }

        .button.dark:hover,
        button.dark:hover {
            background: var(--catops-navy-soft);
            color: #fff;
        }

        .danger {
            border-color: rgba(185, 67, 48, 0.25);
            background: #fff8f6;
            color: var(--catops-danger);
            box-shadow: none;
        }

        .danger:hover {
            background: #feece8;
            color: #943528;
        }

        .card {
            padding: 22px;
            border: 1px solid var(--catops-line);
            border-radius: 16px;
            background: var(--catops-card);
            box-shadow: var(--catops-shadow);
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 16px;
        }

        .split {
            /* display: grid; */
            /* grid-template-columns: minmax(0, 0.95fr) minmax(320px, 1.05fr); */
            gap: 18px;
        }

        .toolbar,
        .row-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            align-items: center;
            margin-top: 14px;
        }

        .toolbar form,
        .row-actions form {
            width: auto;
            margin: 0;
        }

        .form-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 18px;
        }

        .form-actions form {
            width: auto;
            margin: 0;
        }

        .form-actions-stacked {
            display: grid;
            justify-items: start;
        }

        .form-actions-stacked .button,
        .form-actions-stacked button {
            min-width: 178px;
        }

        .meta {
            margin: 5px 0 0;
            color: var(--catops-muted);
            font-size: 14px;
        }

        .price-line {
            color: var(--catops-navy);
            font-weight: 900;
        }

        .status {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            min-height: 24px;
            padding: 4px 9px;
            border-radius: 999px;
            background: #fff1df;
            color: var(--catops-warning);
            font-size: 12px;
            font-weight: 800;
            line-height: 1.2;
        }

        .status::before {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: currentColor;
            content: "";
        }

        .status.status-active,
        .status.status-published {
            background: #e9f8ef;
            color: var(--catops-success);
        }

        .status.status-paused {
            background: #f2efff;
            color: #6951a4;
        }

        .status.status-expired {
            background: #fff0ee;
            color: var(--catops-danger);
        }

        .status.status-draft {
            background: #fff1df;
            color: var(--catops-warning);
        }

        .message {
            margin: 0 0 18px;
            padding: 13px 15px;
            border: 1px solid #f1d5bf;
            border-radius: 12px;
            background: #fff3e9;
            color: #804015;
        }

        .error-message {
            color: var(--catops-danger);
            font-size: 13px;
            font-weight: 700;
        }

        form {
            width: 100%;
        }

        label {
            display: block;
            margin: 16px 0 7px;
            color: var(--catops-navy);
            font-size: 14px;
            font-weight: 800;
        }

        input,
        textarea,
        select {
            width: 100%;
            max-width: 100%;
            min-height: 42px;
            padding: 9px 11px;
            border: 1px solid #dcd5cb;
            border-radius: 9px;
            background: #fff;
            color: var(--catops-navy);
            font: inherit;
        }

        input[type="file"] {
            min-width: 0;
            overflow: hidden;
            font-size: 14px;
        }

        input[type="checkbox"],
        input[type="radio"] {
            width: auto;
            min-height: 0;
            margin: 0 7px 0 0;
            accent-color: var(--catops-orange);
        }

        input:focus,
        textarea:focus,
        select:focus {
            border-color: var(--catops-orange);
            box-shadow: 0 0 0 3px rgba(220, 101, 30, 0.13);
            outline: none;
        }

        textarea {
            min-height: 110px;
            resize: vertical;
        }

        .submit {
            margin-top: 18px;
        }

        .input {
            display: grid;
            gap: 8px;
        }

        .dashboard-kpis {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
            margin-bottom: 28px;
        }

        .metric-card {
            min-height: 98px;
            padding: 16px;
        }

        .metric-label {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--catops-muted);
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 0.03em;
            text-transform: uppercase;
        }

        .metric-icon {
            display: inline-grid;
            width: 28px;
            height: 28px;
            place-items: center;
            border-radius: 9px;
            background: #f1eee8;
            color: var(--catops-navy);
            font-size: 14px;
        }

        .metric-value {
            margin: 10px 0 0;
            color: var(--catops-navy);
            font-size: 30px;
            font-weight: 850;
            line-height: 1;
        }

        .metric-support {
            margin: 3px 0 0;
            color: var(--catops-muted);
            font-size: 12px;
        }

        .subscription-overview {
            display: grid;
            grid-template-columns: minmax(0, 1.25fr) minmax(300px, 0.9fr);
            overflow: hidden;
            padding: 0;
            margin-bottom: 36px;
        }

        .subscription-main,
        .subscription-usage {
            padding: 26px;
        }

        .subscription-main {
            border-right: 1px solid var(--catops-line);
        }

        .subscription-title {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 10px;
        }

        .subscription-title h2 {
            margin: 0;
            font-size: 18px;
        }

        .plan-price {
            margin: 20px 0 18px;
            color: var(--catops-navy);
            font-size: 37px;
            font-weight: 850;
            letter-spacing: -0.03em;
            line-height: 1;
        }

        .plan-price small {
            color: var(--catops-muted);
            font-size: 14px;
            font-weight: 700;
            letter-spacing: 0;
        }

        .usage-label {
            margin: 0 0 7px;
            color: var(--catops-muted);
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .usage-row {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            margin: 15px 0 7px;
            color: var(--catops-navy);
            font-size: 14px;
            font-weight: 750;
        }

        .usage-row span:last-child {
            white-space: nowrap;
        }

        .usage-track {
            height: 5px;
            overflow: hidden;
            border-radius: 999px;
            background: #ece8e1;
        }

        .usage-bar {
            display: block;
            height: 100%;
            border-radius: inherit;
            background: var(--catops-orange);
        }

        .usage-bar.navy {
            background: var(--catops-navy);
        }

        .subscription-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 20px;
            align-items: center;
        }

        .subscription-actions form {
            width: auto;
            margin: 0;
        }

        .subtle-link {
            color: var(--catops-muted);
            font-size: 14px;
        }

        .plan-change-card {
            margin-bottom: 36px;
            padding: 0;
            overflow: hidden;
            border: 0;
            background: transparent;
            box-shadow: none;
        }

        .plan-change-header {
            margin-bottom: 14px;
        }

        .plan-change-header h2 {
            margin-bottom: 3px;
        }

        .plan-change-header p {
            margin-bottom: 0;
            font-size: 14px;
        }

        .upgrade-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 16px;
        }

        .upgrade-option {
            position: relative;
            display: flex;
            flex-direction: column;
            min-height: 234px;
            padding: 22px;
            border: 1px solid var(--catops-line);
            border-radius: 16px;
            background: var(--catops-card);
            box-shadow: var(--catops-shadow);
        }

        .upgrade-option.is-recommended {
            border-color: rgba(220, 101, 30, 0.5);
            box-shadow: 0 8px 20px rgba(220, 101, 30, 0.08);
        }

        .plan-badge {
            position: absolute;
            top: -11px;
            left: 18px;
            padding: 4px 9px;
            border-radius: 999px;
            background: var(--catops-orange);
            color: #fff;
            font-size: 11px;
            font-weight: 800;
        }

        .upgrade-option strong {
            margin-top: 6px;
            color: var(--catops-navy);
            font-size: 18px;
        }

        .upgrade-option>.upgrade-price {
            display: block;
            margin: 15px 0 10px;
            color: var(--catops-navy);
            font-size: 28px;
            font-weight: 850;
        }

        .upgrade-option small {
            color: var(--catops-muted);
            line-height: 1.55;
        }

        .upgrade-option form {
            width: 100%;
            margin-top: auto;
            padding-top: 20px;
        }

        .upgrade-option form .button,
        .upgrade-option form button {
            width: 100%;
        }

        .sites-section-header {
            display: flex;
            align-items: end;
            justify-content: space-between;
            gap: 18px;
            margin-bottom: 14px;
        }

        .sites-section-header h2 {
            margin-bottom: 3px;
        }

        .sites-section-header p {
            margin-bottom: 0;
            font-size: 14px;
        }

        .site-list {
            display: grid;
            gap: 12px;
        }

        .site-card {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 190px;
            gap: 22px;
            align-items: center;
            padding: 21px;
        }

        .site-card-heading {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 8px;
        }

        .site-card-heading h3 {
            margin: 0;
            font-size: 19px;
        }

        .site-url {
            display: inline-flex;
            margin-top: 7px;
            color: var(--catops-muted);
            font-size: 14px;
            font-weight: 700;
            word-break: break-word;
        }

        .site-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 8px 17px;
            margin: 15px 0 0;
            color: var(--catops-muted);
            font-size: 13px;
        }

        .site-meta strong {
            color: var(--catops-navy);
        }

        .site-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 17px;
        }

        .site-actions .button {
            min-height: 38px;
        }

        .site-preview-art {
            min-height: 142px;
            padding: 11px;
            border: 1px solid #e2e7ee;
            border-radius: 13px;
            background: linear-gradient(145deg, var(--catops-navy), #314d78);
            box-shadow: inset 0 1px rgba(255, 255, 255, 0.18);
        }

        .site-preview-dots {
            display: flex;
            gap: 5px;
        }

        .site-preview-dots span {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.4);
        }

        .site-preview-dots span:first-child {
            background: var(--catops-orange);
        }

        .site-preview-page {
            display: grid;
            gap: 8px;
            height: 102px;
            margin-top: 9px;
            padding: 12px;
            border-radius: 9px;
            background: #fffdf8;
        }

        .site-preview-title {
            width: 56%;
            height: 10px;
            border-radius: 99px;
            background: #f0e9df;
        }

        .site-preview-copy {
            width: 84%;
            height: 7px;
            border-radius: 99px;
            background: #f2eee8;
        }

        .site-preview-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 6px;
            margin-top: auto;
        }

        .site-preview-grid span {
            height: 30px;
            border-radius: 6px;
            background: #f0ebe2;
        }

        .list {
            display: grid;
            gap: 12px;
            margin-top: 18px;
        }

        .list-item {
            display: grid;
            grid-template-columns: 72px minmax(0, 1fr) auto;
            gap: 14px;
            align-items: center;
            padding: 13px;
            border: 1px solid var(--catops-line);
            border-radius: 13px;
            background: #fffdfb;
        }

        .list-item.no-thumb {
            grid-template-columns: minmax(0, 1fr) auto;
        }

        .inline-edit-form {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 12px;
            align-items: end;
            width: 100%;
        }

        .product-edit-form {
            grid-template-columns: 1fr;
        }

        .compact-grid {
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        }

        .thumb {
            width: 72px;
            height: 72px;
            overflow: hidden;
            border-radius: 11px;
            background: #fff1e7;
        }

        .thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .image-preview {
            display: inline-grid;
            max-width: 180px;
            margin-top: 12px;
            overflow: hidden;
            border: 1px solid var(--catops-line);
            border-radius: 12px;
            background: #fff;
        }

        .image-preview img {
            display: block;
            width: 100%;
            height: auto;
        }

        .preset-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, 100px);
            gap: 10px;
            justify-content: start;
            margin-top: 10px;
        }

        .preset-option {
            display: block;
            width: 100px;
            gap: 5px;
            margin: 0;
            padding: 5px;
            border: 1px solid var(--catops-line);
            border-radius: 10px;
            background: #fffdfb;
            cursor: pointer;
        }

        .preset-option input {
            position: absolute;
            width: 1px;
            min-height: 0;
            margin: 0;
            opacity: 0;
            pointer-events: none;
        }

        .preset-option span {
            display: block;
            aspect-ratio: 16 / 9;
            border-radius: 6px;
            background-position: center;
            background-size: cover;
            box-shadow: inset 0 0 0 1px rgba(20, 30, 45, 0.08);
        }

        .preset-option strong {
            color: var(--catops-navy);
            font-size: 11px;
            line-height: 1.2;
        }

        .preset-option:has(input:checked),
        .preset-option.selected {
            border-color: var(--catops-orange);
            box-shadow: 0 0 0 3px rgba(220, 101, 30, 0.1);
        }

        [hidden] {
            display: none !important;
        }

        .catalog-typography-group {
            margin-top: 18px;
        }

        .catalog-text-row {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 112px;
            gap: 12px;
            align-items: end;
        }

        .catalog-text-row label {
            margin-top: 0;
        }

        .catalog-text-row input[type="color"] {
            min-height: 42px;
            padding: 4px;
        }

        .font-preview-select {
            font-size: 16px;
        }

        .site-edit-grid {
            align-items: start;
        }

        .site-followup-card {
            margin-top: 18px;
        }

        .qr-management {
            display: grid;
            grid-template-columns: minmax(150px, 190px) minmax(0, 1fr);
            gap: 22px;
            align-items: start;
            margin-top: 18px;
        }

        .qr-preview-frame {
            display: grid;
            place-items: center;
            padding: 12px;
            border: 1px solid var(--catops-line);
            background: #fff;
            box-shadow: 0 4px 12px rgba(34, 43, 55, 0.05);
        }

        .qr-preview-frame--square {
            border-radius: 4px;
        }

        .qr-preview-frame--rounded {
            border-radius: 28px;
        }

        .qr-preview-frame img {
            display: block;
            width: 100%;
            max-width: 166px;
            aspect-ratio: 1;
        }

        .qr-management-content form {
            display: grid;
            grid-template-columns: minmax(160px, 250px) auto;
            gap: 10px 12px;
            align-items: end;
        }

        .qr-management-content form .input {
            margin: 0;
        }

        .qr-management-content form .meta {
            grid-column: 1 / -1;
            margin: -2px 0 0;
        }

        .qr-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 16px;
        }

        .qr-share-status {
            min-height: 20px;
            margin: 10px 0 0;
            color: var(--catops-success);
            font-size: 14px;
            font-weight: 700;
        }

        .domain-setup {
            margin: 18px 0;
            padding: 16px;
            border: 1px solid var(--catops-line);
            border-radius: 12px;
            background: #fffdf9;
        }

        .domain-setup-heading {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            color: var(--catops-navy);
        }

        .domain-dns-record {
            display: grid;
            grid-template-columns: 88px minmax(0, 1fr);
            gap: 7px 12px;
            margin: 14px 0;
            font-size: 14px;
        }

        .domain-dns-record dt {
            color: var(--catops-muted);
            font-weight: 700;
        }

        .domain-dns-record dd {
            min-width: 0;
            margin: 0;
            overflow-wrap: anywhere;
        }

        .domain-dns-record code {
            color: var(--catops-navy);
            font-size: 12px;
        }

        .form-error {
            margin: 12px 0 0;
            color: var(--catops-danger);
            font-size: 14px;
            font-weight: 700;
        }

        .catalog-products-panel {
            margin-top: 18px;
        }

        .product-create-grid {
            display: grid;
            grid-template-columns: minmax(260px, 0.9fr) minmax(320px, 1.1fr);
            gap: 22px;
            align-items: start;
        }

        .product-editor-list {
            display: grid;
            gap: 16px;
            margin-top: 22px;
        }

        .product-editor-card {
            display: grid;
            grid-template-columns: 100px minmax(0, 1fr);
            gap: 20px;
            align-items: start;
            padding: 16px;
            border: 1px solid var(--catops-line);
            border-radius: 14px;
            background: #fffdfb;
        }

        .product-media-panel {
            display: grid;
            gap: 10px;
            align-content: start;
        }

        .product-media {
            width: 100%;
            aspect-ratio: 4 / 3;
            overflow: hidden;
            border-radius: 11px;
            background: #fff1e7;
            box-shadow: inset 0 0 0 1px rgba(20, 30, 45, 0.08);
        }

        .product-media img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .product-status-stack {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }

        .product-status-stack .status {
            padding: 5px 8px;
            font-size: 10px;
        }

        .product-card-head {
            display: flex;
            gap: 12px;
            align-items: flex-start;
            justify-content: space-between;
        }

        .product-card-tools {
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .product-drag-handle {
            width: 36px;
            min-width: 36px;
            min-height: 36px;
            padding: 0;
            border-color: var(--catops-line);
            background: #f7f2ea;
            color: var(--catops-navy);
            box-shadow: none;
            cursor: grab;
            font-size: 20px;
        }

        .product-drag-handle:active {
            cursor: grabbing;
        }

        .product-sort-help,
        .product-sort-status {
            margin: 18px 0 0;
            color: var(--catops-muted);
            font-size: 14px;
        }

        .product-sort-status {
            min-height: 22px;
            color: var(--catops-success);
            font-weight: 750;
        }

        .product-sort-ghost {
            opacity: 0.45;
        }

        .product-sort-chosen {
            box-shadow: 0 12px 26px rgba(16, 33, 61, 0.13);
        }

        .product-summary {
            max-width: 760px;
        }

        .product-editor-form {
            display: grid;
            gap: 14px;
        }

        .product-edit-collapse {
            margin-top: 16px;
            padding-top: 16px;
            border-top: 1px solid var(--catops-line);
        }

        .product-editor-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 10px 16px;
            align-items: start;
        }

        .product-editor-grid label,
        .product-create-grid label {
            margin-top: 0;
            margin-bottom: 6px;
            font-size: 14px;
        }

        .product-editor-grid input,
        .product-editor-grid select,
        .product-editor-grid textarea {
            min-height: 40px;
            padding: 8px 11px;
        }

        .product-editor-grid .field-wide {
            grid-column: span 2;
        }

        .product-editor-grid .field-full {
            grid-column: 1 / -1;
        }

        .product-checks {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 8px;
            align-items: end;
        }

        .product-checks .input.checkbox {
            min-height: 40px;
            padding: 10px 12px;
            border: 1px solid var(--catops-line);
            border-radius: 10px;
            background: #fffdfb;
        }

        .product-checks label {
            display: flex;
            min-height: 20px;
            align-items: center;
            gap: 4px;
            margin: 0;
        }

        .product-actions {
            display: flex;
            /* flex-wrap: wrap; */
            justify-content: flex-end;
            gap: 8px;
            padding-top: 4px;
        }

        .product-summary-actions {
            justify-content: flex-start;
            margin-top: 14px;
        }

        .product-actions form {
            width: auto;
            margin: 0;
        }

        .product-actions .button,
        .product-actions button {
            /* min-width: 132px; */
        }

        .variant-section {
            display: grid;
            gap: 14px;
            margin-top: 20px;
            padding-top: 18px;
            border-top: 1px solid var(--catops-line);
        }

        .variant-section-heading h3 {
            margin: 0;
            color: var(--catops-navy);
            font-size: 16px;
        }

        .variant-section-heading .meta {
            margin: 5px 0 0;
        }

        .variant-form,
        .variant-editor-row {
            display: grid;
            gap: 12px;
            padding: 14px;
            border: 1px solid var(--catops-line);
            border-radius: 10px;
            background: #fffdfa;
        }

        .variant-list {
            display: grid;
            gap: 10px;
        }

        .variant-form-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 10px 14px;
            align-items: start;
        }

        .variant-form-grid label {
            margin-top: 0;
            margin-bottom: 6px;
            font-size: 13px;
        }

        .variant-form-grid input,
        .variant-form-grid select {
            min-height: 40px;
            padding: 8px 11px;
        }

        .variant-actions {
            justify-content: flex-start;
        }

        .dashboard-section,
        .section-heading {
            margin-bottom: 22px;
        }

        @media (max-width: 880px) {
            .panel-nav-toggle {
                display: inline-grid;
                place-items: center;
            }

            .panel-nav {
                flex-wrap: wrap;
            }

            .panel-links {
                display: none;
                flex: 0 0 100%;
                width: 100%;
                padding: 0 0 14px;
            }

            .panel-nav.is-open .panel-links {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .panel-links a {
                justify-content: center;
                border: 1px solid var(--catops-line);
                background: var(--catops-card);
            }

            .subscription-overview {
                grid-template-columns: 1fr;
            }

            .subscription-main {
                border-right: 0;
                border-bottom: 1px solid var(--catops-line);
            }
        }

        @media (max-width: 680px) {
            .panel-shell {
                width: min(1180px, calc(100% - 24px));
            }

            main {
                padding: 26px 0 52px;
            }

            .page-head {
                align-items: stretch;
                flex-direction: column;
                gap: 14px;
                margin-bottom: 22px;
            }

            .page-head>.button,
            .page-head>.toolbar {
                width: 100%;
            }

            .page-head>.button {
                width: 100%;
            }

            .dashboard-kpis {
                grid-template-columns: 1fr;
                gap: 10px;
                margin-bottom: 20px;
            }

            .metric-card {
                display: grid;
                grid-template-columns: auto 1fr;
                column-gap: 12px;
                min-height: 0;
                align-items: center;
            }

            .metric-label {
                grid-column: 1 / -1;
            }

            .metric-value {
                grid-column: 1;
                margin: 8px 0 0;
            }

            .metric-support {
                grid-column: 2;
                margin: 8px 0 0;
                align-self: center;
            }

            .subscription-main,
            .subscription-usage {
                padding: 20px;
            }

            .subscription-overview {
                margin-bottom: 30px;
            }

            .subscription-actions,
            .toolbar,
            .row-actions {
                width: 100%;
            }

            .subscription-actions form,
            .subscription-actions .button,
            .toolbar .button,
            .toolbar form,
            .row-actions .button,
            .row-actions form {
                flex: 1 1 150px;
            }

            .subscription-actions form {
                display: grid;
            }

            .subscription-actions form .button {
                width: 100%;
            }

            .button,
            button {
                min-width: 0;
                white-space: normal;
                text-align: center;
            }

            .split,
            .list-item,
            .list-item.no-thumb,
            .inline-edit-form,
            .product-create-grid,
            .product-editor-card,
            .product-editor-grid,
            .product-checks,
            .variant-form-grid,
            .site-card {
                grid-template-columns: 1fr;
            }

            .product-editor-grid .field-wide,
            .product-editor-grid .field-full {
                grid-column: auto;
            }

            .qr-management {
                grid-template-columns: 1fr;
            }

            .qr-preview-frame {
                width: min(100%, 190px);
            }

            .qr-management-content form {
                grid-template-columns: 1fr;
            }

            .qr-management-content form .meta {
                grid-column: auto;
            }

            .qr-actions .button,
            .qr-actions button {
                flex: 1 1 140px;
            }

            .site-preview-art {
                display: none;
            }

            .site-actions .button {
                flex: 1 1 145px;
            }

            .thumb {
                width: 100%;
                height: auto;
                aspect-ratio: 4 / 3;
            }

            .compact-grid,
            .grid {
                grid-template-columns: 1fr;
            }

            .product-actions {
                justify-content: stretch;
                width: 100%;
            }

            .product-card-head {
                flex-direction: column;
            }

            .product-media-panel {
                grid-template-columns: 112px minmax(0, 1fr);
                align-items: center;
            }

            .product-media {
                width: 112px;
            }

            .product-status-stack {
                align-content: center;
            }

            .product-actions .button,
            .product-actions button,
            .product-actions form {
                width: 100%;
            }

            .preset-grid {
                grid-template-columns: 1fr;
            }

            .catalog-text-row {
                grid-template-columns: minmax(0, 1fr) 96px;
                gap: 10px;
            }

            .catalog-text-row input[type="color"] {
                min-height: 42px;
            }

            .card {
                padding: 18px;
                border-radius: 14px;
            }
        }

        @media (max-width: 430px) {
            .panel-nav.is-open .panel-links {
                grid-template-columns: 1fr;
            }

            .plan-price {
                font-size: 33px;
            }

            .site-actions .button {
                width: 100%;
            }
        }
    </style>
</head>

<body>
    <?php $path = (string)$this->getRequest()->getUri()->getPath(); ?>
    <a class="skip-link" href="#contenido-principal">Saltar al contenido</a>
    <header class="panel-topbar">
        <div class="panel-shell panel-nav" data-panel-nav>
            <a class="panel-brand" href="/panel" aria-label="Ir a mis vitrinas"><img src="/img/catops-logo.png" alt="CatOps"></a>
            <button class="panel-nav-toggle" type="button" aria-label="Abrir menú" aria-expanded="false" aria-controls="panel-menu" data-panel-nav-toggle>☰</button>
            <nav class="panel-links" id="panel-menu" aria-label="Navegación principal">
                <a href="/panel" <?= $path === '/panel' ? ' aria-current="page"' : '' ?>>Mis vitrinas</a>
                <a href="/sitios/nuevo" <?= $path === '/sitios/nuevo' ? ' aria-current="page"' : '' ?>>Nueva vitrina</a>
                <a href="/planes" <?= $path === '/planes' ? ' aria-current="page"' : '' ?>>Planes</a>
                <a class="nav-logout" href="/logout">Salir</a>
            </nav>
        </div>
    </header>
    <main class="panel-shell" id="contenido-principal">
        <?= $this->Flash->render() ?>
        <?= $this->fetch('content') ?>
    </main>
    <script src="/js/jquery-3.6.0.min.js"></script>
    <script src="/js/bootstrap.bundle.js"></script>
    <script>
        (() => {
            const nav = document.querySelector('[data-panel-nav]');
            const toggle = document.querySelector('[data-panel-nav-toggle]');
            if (!nav || !toggle) return;

            toggle.addEventListener('click', () => {
                const isOpen = nav.classList.toggle('is-open');
                toggle.setAttribute('aria-expanded', String(isOpen));
                toggle.setAttribute('aria-label', isOpen ? 'Cerrar menú' : 'Abrir menú');
                toggle.textContent = isOpen ? '×' : '☰';
            });
            nav.querySelectorAll('.panel-links a').forEach((link) => link.addEventListener('click', () => {
                nav.classList.remove('is-open');
                toggle.setAttribute('aria-expanded', 'false');
                toggle.setAttribute('aria-label', 'Abrir menú');
                toggle.textContent = '☰';
            }));
        })();
    </script>
</body>

</html>
