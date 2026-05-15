<?php session_start(); ?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ayuda — AnalizadorFirmas</title>
    <link rel="stylesheet" href="assets/css/theme.css">
    <script src="assets/js/theme-switcher.js"></script>
    <style>
        /* ── Navbar ─────────────────────────────── */
        nav {
            background: var(--color-nav-bg);
            color: white;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
            height: 56px;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .3);
        }

        nav .brand {
            font-size: 1.2rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        nav .nav-links {
            display: flex;
            gap: 16px;
        }

        nav .nav-links a {
            color: rgba(255, 255, 255, .85);
            text-decoration: none;
            font-size: .9rem;
            padding: 6px 12px;
            border-radius: 4px;
        }

        nav .nav-links a:hover,
        nav .nav-links a.active {
            background: rgba(255, 255, 255, .2);
            color: white;
        }

        .theme-toggle {
            background: rgba(255, 255, 255, 0.12);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 6px;
            padding: 6px 12px;
            font-size: .9rem;
            cursor: pointer;
            transition: background .2s, border-color .2s;
        }

        .theme-toggle:hover,
        .theme-toggle:focus {
            background: rgba(255, 255, 255, 0.2);
            outline: none;
        }

        /* ── Layout ─────────────────────────────── */
        body {
            margin: 0;
            font-family: var(--font-main);
            background: var(--color-bg);
            color: var(--color-text);
        }

        main {
            max-width: 860px;
            margin: 36px auto;
            padding: 0 16px;
            display: grid;
            gap: 24px;
        }

        /* ── Hero ───────────────────────────────── */
        .hero {
            background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-dark) 100%);
            color: white;
            border-radius: 12px;
            padding: 36px 32px;
            text-align: center;
        }

        .hero h1 {
            color: var(--color-hero-title, #000000);
            font-size: 1.8rem;
            margin-bottom: 8px;
        }

        .hero p {
            color: var(--color-hero-paragraph, #000000);
            opacity: .85;
            font-size: 1rem;
        }

        body.dark-mode .hero h1 {
            color: #ffffff;
        }

        body.dark-mode .hero p {
            color: #ffffff;
        }

        /* ── Cards ──────────────────────────────── */
        .card {
            background: var(--color-card);
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .07);
            padding: 28px;
        }

        .card h2 {
            font-size: 1.05rem;
            color: var(--color-primary);
            margin-bottom: 18px;
            display: flex;
            align-items: center;
            gap: 8px;
            border-bottom: 2px solid rgba(227, 242, 253, .7);
            padding-bottom: 10px;
        }

        /* ── FAQ accordion ───────────────────────── */
        .faq-item {
            border-bottom: 1px solid var(--color-border);
        }

        .faq-item:last-child {
            border-bottom: none;
        }

        .faq-btn {
            width: 100%;
            text-align: left;
            background: none;
            border: none;
            padding: 14px 4px;
            font-size: .95rem;
            font-weight: 600;
            color: var(--color-primary);
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 8px;
            transition: color .2s;
        }

        .faq-btn:hover {
            color: var(--color-primary-dark);
        }

        .faq-btn .arrow {
            transition: transform .25s;
            font-style: normal;
            flex-shrink: 0;
        }

        .faq-btn[aria-expanded="true"] .arrow {
            transform: rotate(90deg);
        }

        .faq-body {
            display: none;
            padding: 0 4px 14px;
            color: var(--color-text-soft);
            font-size: .9rem;
            line-height: 1.7;
        }

        .faq-body.open {
            display: block;
        }

        /* ── Pasos de instalación ────────────────── */
        .steps {
            list-style: none;
            counter-reset: step;
            padding: 0;
        }

        .steps li {
            counter-increment: step;
            display: flex;
            gap: 14px;
            align-items: flex-start;
            margin-bottom: 14px;
            font-size: .9rem;
            color: var(--color-text);
            line-height: 1.6;
        }

        .steps li::before {
            content: counter(step);
            background: var(--color-primary);
            color: white;
            border-radius: 50%;
            width: 26px;
            height: 26px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .8rem;
            font-weight: 700;
            flex-shrink: 0;
        }

        code {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 4px;
            padding: 2px 6px;
            font-size: .85rem;
            font-family: 'Consolas', monospace;
        }

        /* ── Tabla tipos de archivo ──────────────── */
        .tipos-tabla {
            width: 100%;
            border-collapse: collapse;
            font-size: .88rem;
        }

        .tipos-tabla th {
            background: var(--color-table-header-bg);
            color: var(--color-table-header-text);
            padding: 9px 12px;
            text-align: left;
        }

        .tipos-tabla td {
            padding: 8px 12px;
            border-bottom: 1px solid #f0f0f0;
        }

        .tipos-tabla tr:hover td {
            background: var(--color-table-hover-bg);
        }

        /* Icon styles shared with index.php */
        .icon-ui {
            width: 22px;
            height: 22px;
            vertical-align: middle;
            display: inline-block;
        }

        .icon-tipo {
            font-size: 1.2rem;
            margin-right: 4px;
            width: 24px;
            height: 24px;
            object-fit: contain;
            vertical-align: middle;
            margin-right: 6px;
        }

        .badge {
            display: inline-block;
            padding: 2px 9px;
            border-radius: 10px;
            font-size: .78rem;
            font-weight: 700;
        }

        /* ── Glosario ────────────────────────────── */
        .glosario dt {
            font-weight: 700;
            color: #1565c0;
            margin-top: 12px;
        }

        .glosario dd {
            margin-left: 16px;
            color: #555;
            font-size: .9rem;
            line-height: 1.6;
        }

        /* ── Botón volver ────────────────────────── */
        .btn-volver {
            display: inline-block;
            padding: 9px 20px;
            background: var(--color-primary);
            color: white;
            border-radius: 6px;
            text-decoration: none;
            font-size: .9rem;
            transition: background .2s;
        }

        .btn-volver:hover {
            background: var(--color-primary-dark);
        }

        .table-wrapper {
            overflow-x: auto;
        }

        .text-center {
            text-align: center;
        }

        .help-note {
            color: var(--color-text-soft);
            margin-bottom: 20px;
        }

        /* ── Responsive ──────────────────────────── */
        @media (max-width: 600px) {
            nav .nav-links {
                gap: 8px;
            }

            nav .nav-links a {
                padding: 4px 8px;
                font-size: .82rem;
            }

            .hero {
                padding: 24px 18px;
            }

            .hero h1 {
                font-size: 1.4rem;
            }
        }
    </style>
</head>

<body>

    <!-- ── Navbar ─────────────────────────────────────────── -->
    <nav role="navigation" aria-label="Navegación principal">
        <div class="brand"><img src="assets/img/icons/lupa.svg" class="icon-ui"> AnalizadorFirmas</div>
        <div class="nav-links">
            <a href="index.php">Analizar</a>
            <a href="ayuda.php" class="active" aria-current="page">Ayuda</a>
            <?php if (!empty($_SESSION['usuario_id'])): ?>
                <a href="logout.php">Cerrar sesión</a>
            <?php else: ?>
                <a href="login.php">Iniciar sesión</a>
            <?php endif; ?>
            <button type="button" class="theme-toggle" onclick="toggleTheme()" aria-label="Cambiar modo claro/oscuro">Modo claro/oscuro <img src="assets/img/icons/modo.svg" class="icon-ui"></button>
        </div>
    </nav>

    <main>

        <!-- ── Hero ──────────────────────────────────── -->
        <div class="hero" role="banner">
            <h1>❓ Centro de Ayuda</h1>
            <p>Encuentra respuestas a las preguntas más frecuentes, guía de instalación y glosario técnico.</p>
        </div>

        <!-- ── FAQ ───────────────────────────────────── -->
        <section class="card" aria-labelledby="faq-titulo">
            <h2 id="faq-titulo"><img src="assets/img/icons/preguntas.svg" class="icon-ui"> Preguntas frecuentes</h2>

            <?php
            $faqs = [
                [
                    '¿Qué es AnalizadorFirmas?',
                    'AnalizadorFirmas es una herramienta web que detecta el tipo real de un archivo
                 analizando su <strong>firma binaria</strong> (magic bytes), independientemente
                 de su extensión. Por ejemplo, un archivo <code>.txt</code> renombrado que en
                 realidad es un PDF será correctamente identificado como <em>PDF</em>.'
                ],
                [
                    '¿Qué tipos de archivo puede detectar?',
                    'Actualmente detecta: <strong>JPEG, PNG, GIF, BMP, PDF, ZIP, MP3, MP4, EXE y ELF</strong>
                 (binarios de Linux). La detección se realiza mediante la librería nativa
                 <code>motor_firmas.dll</code> / <code>motor_firmas.exe</code>.'
                ],
                [
                    '¿Cuál es el tamaño máximo de archivo permitido?',
                    'El límite actual es de <strong>5 MB</strong> por archivo. Si necesitas analizar
                 archivos más grandes, contacta al administrador del sistema.'
                ],
                [
                    '¿Mis archivos se almacenan de forma permanente?',
                    'Los archivos se guardan en el servidor solo para realizar el análisis. Puedes
                 eliminarlos en cualquier momento desde el historial usando el botón 🗑️.
                 Los archivos eliminados se borran tanto del disco como de la base de datos.'
                ],
                [
                    '¿Qué es el "resultado desde caché"?',
                    'Si subes un archivo idéntico (mismo contenido, mismo hash MD5) a uno ya
                 analizado anteriormente, el sistema devuelve el resultado guardado sin volver
                 a procesarlo. Esto se indica con el icono ⚡ en el resultado.'
                ],
                [
                    '¿Cómo funciona el historial y los filtros?',
                    'El historial muestra todos los archivos analizados. Puedes filtrar por
                 <strong>nombre</strong> (búsqueda parcial), <strong>tipo</strong> (JPEG, PDF, etc.)
                 y <strong>fecha</strong>. Los resultados se paginan de 10 en 10.'
                ],
                [
                    '¿Necesito una cuenta para usar la aplicación?',
                    'No es obligatorio. Puedes analizar archivos sin iniciar sesión. Sin embargo,
                 con una cuenta los análisis quedan asociados a tu usuario en el historial.'
                ],
                [
                    '¿Cómo restablezco mi contraseña?',
                    'Actualmente no hay opción de recuperación automática. Contacta al administrador
                 para que restablezca tu contraseña manualmente en la base de datos.'
                ],
            ];
            foreach ($faqs as $i => [$pregunta, $respuesta]):
            ?>
                <div class="faq-item">
                    <button class="faq-btn"
                        id="faq-btn-<?= $i ?>"
                        aria-expanded="false"
                        aria-controls="faq-body-<?= $i ?>"
                        onclick="toggleFaq(this)">
                        <span><?= htmlspecialchars($pregunta) ?></span>
                        <i class="arrow" aria-hidden="true">▶</i>
                    </button>
                    <div class="faq-body"
                        id="faq-body-<?= $i ?>"
                        role="region"
                        aria-labelledby="faq-btn-<?= $i ?>">
                        <p><?= $respuesta ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </section>

        <!-- ── Guía de instalación ────────────────────── -->
        <section class="card" aria-labelledby="install-titulo">
            <h2 id="install-titulo"><img src="assets/img/icons/config.svg" class="icon-ui"> Guía de instalación</h2>
            <ol class="steps" aria-label="Pasos de instalación">
                <li>
                    <span>Instala <strong>WampServer 3.x (64 bits)</strong> desde
                        <a href="https://www.wampserver.com" target="_blank" rel="noopener">wampserver.com</a>
                        y asegúrate de que el servidor Apache + PHP estén activos (icono verde en la barra del sistema).</span>
                </li>
                <li>
                    <span>Copia la carpeta <code>AnalizadorFirmas/</code> dentro de
                        <code>C:\wamp64\www\</code>. Verifica que la ruta quede como
                        <code>C:\wamp64\www\AnalizadorFirmas\public\index.php</code>.</span>
                </li>
                <li>
                    <span>Importa el esquema de base de datos: abre
                        <a href="http://localhost/phpmyadmin" target="_blank" rel="noopener">phpMyAdmin</a>,
                        crea una base de datos llamada <code>analizador_firmas</code> y ejecuta el
                        archivo <code>db/schema.sql</code>.</span>
                </li>
                <li>
                    <span>Edita <code>src/Database/Conexion.php</code> y actualiza las credenciales
                        de MySQL (<code>host</code>, <code>dbname</code>, <code>user</code>,
                        <code>password</code>) según tu entorno local.</span>
                </li>
                <li>
                    <span>Habilita la extensión FFI en PHP: abre <code>php.ini</code> de WampServer
                        (menú Wamp → PHP → php.ini), busca <code>ffi.enable</code> y cambia su valor a
                        <code>ffi.enable=true</code>. Reinicia el servidor.</span>
                </li>
                <li>
                    <span>Verifica que <code>motor_firmas.dll</code> (o <code>motor_firmas.exe</code>)
                        está en la raíz del proyecto. El sistema intentará usar la DLL primero; si hay
                        incompatibilidad de arquitectura (x86 vs x64), cambiará automáticamente al modo EXE.</span>
                </li>
                <li>
                    <span>Abre el navegador y visita
                        <a href="http://localhost/AnalizadorFirmas/public/" target="_blank" rel="noopener">
                            http://localhost/AnalizadorFirmas/public/</a>.
                        Si ves la interfaz de análisis, la instalación fue exitosa. ✅</span>
                </li>
            </ol>
        </section>

        <!-- ── Tipos de archivo soportados ───────────────── -->
        <section class="card" aria-labelledby="tipos-titulo">
            <h2 id="tipos-titulo"><img src="assets/img/icons/archivo.svg" class="icon-ui"> Tipos de archivo soportados</h2>
            <div class="table-wrapper" role="region" aria-label="Tabla de tipos de archivo soportados" tabindex="0">
                <table class="tipos-tabla" aria-label="Formatos detectados por el analizador">
                    <thead>
                        <tr>
                            <th scope="col">Tipo</th>
                            <th scope="col">Descripción</th>
                            <th scope="col">Magic bytes</th>
                            <th scope="col">Código</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><span class="badge badge-jpg"><img src="assets/img/icons/jpg.png" class="icon-ui"> JPEG</span></td>
                            <td>Imagen fotográfica comprimida</td>
                            <td><code>FF D8 FF</code></td>
                            <td>1</td>
                        </tr>
                        <tr>
                            <td><span class="badge badge-png"><img src="assets/img/icons/png.png" class="icon-ui"> PNG</span></td>
                            <td>Imagen con transparencia</td>
                            <td><code>89 50 4E 47</code></td>
                            <td>2</td>
                        </tr>
                        <tr>
                            <td><span class="badge badge-pdf"><img src="assets/img/icons/pdf.png" class="icon-ui"> PDF</span></td>
                            <td>Documento portátil</td>
                            <td><code>25 50 44 46</code></td>
                            <td>3</td>
                        </tr>
                        <tr>
                            <td><span class="badge badge-zip"><img src="assets/img/icons/zip.png" class="icon-ui"> ZIP</span></td>
                            <td>Archivo comprimido</td>
                            <td><code>50 4B 03 04</code></td>
                            <td>4</td>
                        </tr>
                        <tr>
                            <td><span class="badge badge-gif"><img src="assets/img/icons/gif.png" class="icon-ui"> GIF</span></td>
                            <td>Imagen animada</td>
                            <td><code>47 49 46 38</code></td>
                            <td>5</td>
                        </tr>
                        <tr>
                            <td><span class="badge badge-bmp"><img src="assets/img/icons/bmp.png" class="icon-ui"> BMP</span></td>
                            <td>Imagen de mapa de bits</td>
                            <td><code>42 4D</code></td>
                            <td>6</td>
                        </tr>
                        <tr>
                            <td><span class="badge badge-exe"><img src="assets/img/icons/exe.png" class="icon-ui"> EXE</span></td>
                            <td>Ejecutable Windows (PE)</td>
                            <td><code>4D 5A</code></td>
                            <td>7</td>
                        </tr>
                        <tr>
                            <td><span class="badge badge-elf"><img src="assets/img/icons/elf.png" class="icon-ui"> ELF</span></td>
                            <td>Ejecutable Linux</td>
                            <td><code>7F 45 4C 46</code></td>
                            <td>8</td>
                        </tr>
                        <tr>
                            <td><span class="badge badge-mp3"><img src="assets/img/icons/mp3.png" class="icon-ui"> MP3</span></td>
                            <td>Audio MPEG</td>
                            <td><code>FF FB / ID3</code></td>
                            <td>9</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- ── Glosario ───────────────────────────────────── -->
        <section class="card" aria-labelledby="glosario-titulo">
            <h2 id="glosario-titulo"><img src="assets/img/icons/glosario.svg" class="icon-ui"> Glosario técnico</h2>
            <dl class="glosario">
                <dt>Firma de archivo (magic bytes)</dt>
                <dd>Secuencia de bytes al inicio de un archivo que identifica su formato real,
                    independientemente de la extensión. Por ejemplo, todo JPEG comienza con
                    <code>FF D8 FF</code>.
                </dd>

                <dt>Hash MD5</dt>
                <dd>Huella digital de 128 bits de un archivo. Dos archivos idénticos producen
                    el mismo MD5. El sistema lo usa para caché: si el MD5 ya existe en la BD,
                    devuelve el resultado guardado sin reprocesar.</dd>

                <dt>FFI (Foreign Function Interface)</dt>
                <dd>Mecanismo de PHP para llamar directamente funciones de librerías nativas
                    (<code>.dll</code> / <code>.so</code>) sin necesidad de extensión compilada.</dd>

                <dt>DLL (Dynamic Link Library)</dt>
                <dd>Librería de código nativo en Windows que expone funciones reutilizables.
                    <code>motor_firmas.dll</code> contiene la lógica de detección de firmas.
                </dd>

                <dt>Modo EXE (fallback)</dt>
                <dd>Si la DLL no es compatible con la arquitectura de PHP (ej. DLL x86 en PHP x64),
                    el sistema ejecuta <code>motor_firmas.exe</code> mediante <code>shell_exec()</code>
                    como alternativa automática.</dd>

                <dt>Singleton</dt>
                <dd>Patrón de diseño que garantiza una única instancia de una clase. La conexión a
                    la BD (<code>Conexion</code>) y el motor (<code>MotorFirmas</code>) usan este
                    patrón para evitar múltiples conexiones o cargas de DLL.</dd>

                <dt>Paginación</dt>
                <dd>División del historial en páginas de 10 registros para facilitar la navegación
                    sin sobrecargar la interfaz.</dd>
            </dl>
        </section>

        <!-- ── Contacto / volver ─────────────────────────── -->
        <section class="card text-center" aria-labelledby="contacto-titulo">
            <h2 id="contacto-titulo"><img src="assets/img/icons/ayuda.svg" class="icon-ui"> ¿Necesitas más ayuda?</h2>
            <p class="help-note">
                Si tu problema no está cubierto aquí, contacta al equipo de desarrollo:<br>
                <strong>Proyecto LEMA — ASEM-I01</strong> · Ciclo 1-2026
            </p>
            <a href="index.php" class="btn-volver" aria-label="Volver a la página principal">← Volver al inicio</a>
        </section>

    </main>

    <script>
        function toggleFaq(btn) {
            const expanded = btn.getAttribute('aria-expanded') === 'true';
            // Cerrar todos
            document.querySelectorAll('.faq-btn').forEach(b => {
                b.setAttribute('aria-expanded', 'false');
                document.getElementById(b.getAttribute('aria-controls')).classList.remove('open');
            });
            // Abrir el clickeado (si estaba cerrado)
            if (!expanded) {
                btn.setAttribute('aria-expanded', 'true');
                document.getElementById(btn.getAttribute('aria-controls')).classList.add('open');
            }
        }
    </script>
</body>

</html>