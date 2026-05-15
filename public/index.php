<?php
session_start();

// Protección de sesión: solo usuarios autenticados
if (empty($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../src/Database/Conexion.php';
require_once __DIR__ . '/../src/Repository/ArchivoRepository.php';

$repo     = new ArchivoRepository(Conexion::getInstance());
$archivos = $repo->obtenerTodos();

// Colores e iconos por tipo de archivo
$tipoConfig = [
    'JPEG' => ['color' => '#fff3e0', 'border' => '#ff9800', 'icon' => 'assets/img/icons/jpg.png'],
    'PNG'  => ['color' => '#e3f2fd', 'border' => '#2196f3', 'icon' => 'assets/img/icons/png.png'],
    'GIF'  => ['color' => '#f3e5f5', 'border' => '#9c27b0', 'icon' => 'assets/img/icons/gif.png'],
    'BMP'  => ['color' => '#fce4ec', 'border' => '#e91e63', 'icon' => 'assets/img/icons/bmp.png'],
    'PDF'  => ['color' => '#ffebee', 'border' => '#f44336', 'icon' => 'assets/img/icons/pdf.png'],
    'ZIP'  => ['color' => '#fff8e1', 'border' => '#ffc107', 'icon' => 'assets/img/icons/zip.png'],
    'MP3'  => ['color' => '#e8f5e9', 'border' => '#4caf50', 'icon' => 'assets/img/icons/mp3.png'],
    'MP4'  => ['color' => '#e0f2f1', 'border' => '#009688', 'icon' => 'assets/img/icons/mp4.png'],
    'EXE'  => ['color' => '#efebe9', 'border' => '#795548', 'icon' => 'assets/img/icons/exe.png'],
    'ELF'  => ['color' => '#eceff1', 'border' => '#607d8b', 'icon' => 'assets/img/icons/elf.png'],
];

function getTipoConfig($tipo, $tipoConfig) {
    return isset($tipoConfig[$tipo])
        ? $tipoConfig[$tipo]
        : ['color' => '#f5f5f5', 'border' => '#9e9e9e', 'icon' => '📁'];
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AnalizadorFirmas</title>
    <link rel="stylesheet" href="assets/css/theme.css">
    <script src="assets/js/theme-switcher.js"></script>
    <style>
    /* Estilos específicos de index.php que extienden theme.css */

    /* Variables CSS para modo claro (default) */
    :root {
        /* Colores principales */
        --color-bg: #f0f2f5;
        --color-card: #ffffff;
        --color-text: #000000;
        --color-text-soft: #666666;
        --color-border: #e0e0e0;
        
        /* Colores de interfaz */
        --color-primary: #1976d2;
        --color-primary-dark: #1565c0;
        --color-success: #388e3c;
        --color-danger: #f44336;
        --color-danger-light: #ffebee;
        --color-danger-border: #ef9a9a;
        --color-danger-text: #c62828;
        
        /* Colores de drag & drop */
        --color-drop-bg: #f8faff;
        --color-drop-border: #90caf9;
        --color-drop-dragover-bg: #e3f2fd;
        --color-drop-dragover-border: #1976d2;
        
        /* Colores de progreso */
        --color-progress-bg: #e0e0e0;
        --color-progress-fill: #1976d2;
        
        /* Colores de tabla */
        --color-table-header-bg: #1565c0;
        --color-table-header-text: white;
        --color-table-hover-bg: #f5f7ff;
        --color-table-row-border: #f0f0f0;
        
        /* Colores de navegación */
        --color-nav-bg: #1565c0;
        --color-nav-text: rgba(255, 255, 255, 0.85);
        --color-nav-hover: rgba(255, 255, 255, 0.15);
        --color-nav-active: rgba(255, 255, 255, 0.25);
        --color-nav-user: rgba(255, 255, 255, 0.6);
        
        /* Colores auxiliares */
        --color-disabled: #9e9e9e;
        --color-icon-placeholder: #555;
        --color-file-name: #777;
    }

    /* ── Reset y base ───────────────────────────── */
    *,
    *::before,
    *::after {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }

    body {
        font-family: 'Segoe UI', Arial, sans-serif;
        background: var(--color-bg);
        color: var(--color-text);
        min-height: 100vh;
    }

    /* ── Navbar ─────────────────────────────────── */
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
        color: var(--color-nav-text);
        text-decoration: none;
        font-size: .9rem;
        padding: 6px 12px;
        border-radius: 4px;
        transition: background .2s;
    }

    nav .nav-links a:hover,
    nav .nav-links a:focus {
        background: var(--color-nav-hover);
        color: white;
    }

    nav .nav-links a.active {
        background: var(--color-nav-active);
        color: white;
    }

    .nav-user-info {
        color: var(--color-nav-user);
        font-size: .85rem;
        padding: 6px 4px;
        display: flex;
        align-items: center;
        gap: 4px;
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

    .hamburger {
        display: none;
        background: none;
        border: none;
        color: white;
        font-size: 1.5rem;
        cursor: pointer;
    }

    /* ── Layout principal ───────────────────────── */
    main {
        max-width: 960px;
        margin: 32px auto;
        padding: 0 16px;
        display: grid;
        gap: 24px;
    }

    /* ── Cards ──────────────────────────────────── */
    .card {
        background: var(--color-card);
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, .08);
        padding: 28px;
    }

    .card h2 {
        font-size: 1.1rem;
        color: var(--color-primary);
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    /* ── Drag & Drop ────────────────────────────── */
    #drop-area {
        border: 2px dashed var(--color-drop-border);
        border-radius: 10px;
        padding: 40px 20px;
        text-align: center;
        background: var(--color-drop-bg);
        cursor: pointer;
        transition: background .2s, border-color .2s;
    }

    #drop-area.dragover {
        background: var(--color-drop-dragover-bg);
        border-color: var(--color-drop-dragover-border);
    }

    /* Modo oscuro: drag area y resultado azul translúcido */
    .dark-mode #drop-area,
    .dark-mode #drop-area.dragover {
        background: rgba(59, 130, 246, 0.18);
        border-color: rgba(59, 130, 246, 0.6);
    }

    .dark-mode #drop-area p,
    .dark-mode #drop-area label,
    .dark-mode #file-name {
        color: white;
    }

    .dark-mode #drop-area label {
        background: rgba(59, 130, 246, 0.85);
    }

    #drop-area .drop-icon {
        font-size: 3rem;
        display: block;
        margin-bottom: 12px;
    }

    #drop-area p {
        color: var(--color-icon-placeholder);
        margin-bottom: 12px;
    }

    #drop-area label {
        display: inline-block;
        background: var(--color-primary);
        color: white;
        padding: 8px 20px;
        border-radius: 4px;
        cursor: pointer;
        font-size: .9rem;
        transition: background .2s;
    }

    #drop-area label:hover {
        background: var(--color-primary-dark);
    }

    #file-input {
        display: none;
    }

    #file-name {
        margin-top: 10px;
        font-size: .85rem;
        color: var(--color-file-name);
    }

    /* ── Barra de progreso ──────────────────────── */
    #progress-container {
        display: none;
        margin-top: 16px;
    }

    #progress-bar-wrap {
        background: var(--color-progress-bg);
        border-radius: 6px;
        height: 10px;
        overflow: hidden;
    }

    #progress-bar {
        height: 100%;
        width: 0;
        background: var(--color-progress-fill);
        border-radius: 6px;
        transition: width .3s;
    }

    #progress-label {
        text-align: center;
        font-size: .8rem;
        color: var(--color-icon-placeholder);
        margin-top: 4px;
    }

    /* ── Botón analizar ─────────────────────────── */
    #btn-analizar {
        display: block;
        width: 100%;
        margin-top: 16px;
        padding: 12px;
        background: var(--color-success);
        color: white;
        border: none;
        border-radius: 6px;
        font-size: 1rem;
        cursor: pointer;
        transition: opacity .2s;
    }

    #btn-analizar:hover:not(:disabled) {
        opacity: .85;
    }

    #btn-analizar:disabled {
        opacity: .5;
        cursor: not-allowed;
    }

    /* ── Resultado ──────────────────────────────── */
    #resultado-container {
        display: none;
    }

    #resultado-card {
        border-radius: 10px;
        border-left: 6px solid #ccc;
        padding: 20px;
        margin-top: 8px;
    }

    #resultado-card h3 {
        margin-bottom: 12px;
        font-size: 1rem;
    }

    .dark-mode #resultado-card {
        background: rgba(59, 130, 246, 0.15) !important;
        border-color: rgba(59, 130, 246, 0.7) !important;
        color: white !important;
    }

    .dark-mode #resultado-card h3,
    .dark-mode #resultado-card td {
        color: white !important;
    }

    .dark-mode #resultado-card td {
        border-bottom-color: rgba(255,255,255,0.1);
    }

    #resultado-card table {
        width: 100%;
        border-collapse: collapse;
    }

    #resultado-card td {
        padding: 7px 10px;
        border-bottom: 1px solid rgba(0, 0, 0, .06);
        font-size: .9rem;
    }

    #resultado-card td:first-child {
        font-weight: 600;
        width: 140px;
    }

    .tipo-badge {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 12px;
        font-size: .8rem;
        font-weight: 700;
        border: 1px solid currentColor;
    }

    .acciones {
        display: flex;
        gap: 10px;
        margin-top: 14px;
        flex-wrap: wrap;
    }

    .btn-accion {
        padding: 7px 16px;
        border-radius: 5px;
        border: none;
        cursor: pointer;
        font-size: .85rem;
        font-weight: 600;
        transition: opacity .2s;
    }

    .btn-accion:hover {
        opacity: .85;
    }

    .btn-otro {
        background: var(--color-success);
        color: white;
    }

    .btn-eliminar {
        background: var(--color-danger);
        color: white;
    }

    /* Preview imagen */
    #img-preview {
        display: none;
        max-width: 100%;
        max-height: 220px;
        border-radius: 8px;
        margin-top: 12px;
        border: 1px solid var(--color-border);
    }

    /* ── Toast ──────────────────────────────────── */
    #toast {
        position: fixed;
        bottom: 24px;
        right: 24px;
        background: #323232;
        color: white;
        padding: 12px 20px;
        border-radius: 6px;
        font-size: .9rem;
        opacity: 0;
        pointer-events: none;
        transition: opacity .3s;
        z-index: 999;
        max-width: 300px;
    }

    #toast.show {
        opacity: 1;
    }

    #toast.success {
        background: var(--color-success);
    }

    #toast.error {
        background: var(--color-danger);
    }

    /* ── Historial ──────────────────────────────── */
    .filtros {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-bottom: 16px;
        align-items: flex-end;
    }

    .filtros label {
        font-size: .8rem;
        color: var(--color-text-soft);
        display: block;
        margin-bottom: 3px;
    }

    .filtros input,
    .filtros select {
        padding: 7px 10px;
        border: 1px solid var(--color-border);
        border-radius: 5px;
        font-size: .85rem;
        min-width: 130px;
    }

    .filtros input:focus,
    .filtros select:focus {
        outline: 2px solid var(--color-primary);
        border-color: transparent;
    }

    .btn-filtrar {
        padding: 8px 16px;
        background: var(--color-primary);
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: .85rem;
        align-self: flex-end;
    }

    .btn-limpiar {
        padding: 8px 16px;
        background: var(--color-border);
        color: var(--color-text);
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: .85rem;
        align-self: flex-end;
    }

    .table-wrapper {
        overflow-x: auto;
    }

    #tabla-historial {
        width: 100%;
        border-collapse: collapse;
        font-size: .88rem;
    }

    #tabla-historial th {
        background: var(--color-table-header-bg);
        color: var(--color-table-header-text);
        padding: 10px 12px;
        text-align: left;
        font-weight: 600;
    }

    #tabla-historial td {
        padding: 9px 12px;
        border-bottom: 1px solid var(--color-table-row-border);
        vertical-align: middle;
    }

    #tabla-historial tr:hover td {
        background: var(--color-table-hover-bg);
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

    .icon-ui {
        width: 22px;
        height: 22px;
        vertical-align: middle;
    }

    .btn-del-fila {
        padding: 4px 10px;
        background: var(--color-danger);
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: .8rem;
    }

    .btn-del-fila:hover {
        background: var(--color-danger);
        color: white;
    }

    /* Paginación */
    #paginacion {
        display: flex;
        justify-content: center;
        gap: 6px;
        margin-top: 16px;
        flex-wrap: wrap;
    }

    .btn-pag {
        padding: 5px 11px;
        border: 1px solid var(--color-border);
        background: white;
        border-radius: 4px;
        cursor: pointer;
        font-size: .85rem;
        transition: background .2s;
    }

    .btn-pag.active {
        background: var(--color-primary);
        color: white;
        border-color: var(--color-primary);
    }

    .btn-pag:hover:not(.active) {
        background: var(--color-drop-dragover-bg);
    }

    #no-resultados {
        text-align: center;
        color: var(--color-disabled);
        padding: 30px;
        display: none;
    }

    /* ── Responsivo ─────────────────────────────── */
    @media (max-width: 640px) {
        nav .nav-links {
            display: none;
            flex-direction: column;
            position: absolute;
            top: 56px;
            left: 0;
            right: 0;
            background: var(--color-nav-bg);
            padding: 12px 0;
        }

        nav .nav-links.open {
            display: flex;
        }

        .hamburger {
            display: block;
        }

        .filtros {
            flex-direction: column;
        }

        .filtros input,
        .filtros select {
            min-width: auto;
            width: 100%;
        }

        .acciones {
            flex-direction: column;
        }

        .btn-accion {
            width: 100%;
            text-align: center;
        }

        #tabla-historial th:nth-child(4),
        #tabla-historial td:nth-child(4) {
            display: none;
        }
    }
    </style>
</head>

<body>

    <!-- ── Navbar ─────────────────────────────────────────── -->
    <nav role="navigation" aria-label="Navegación principal">
        <div class="brand"><img src="assets/img/icons/lupa.svg" class="icon-ui">AnalizadorFirmas</div>
        <div class="nav-links" id="nav-links">
            <a href="index.php" class="active" aria-current="page">Analizar</a>
            <a href="ayuda.php">Ayuda</a>
            <span class="nav-user-info" aria-label="Usuario autenticado">
                <img src="assets/img/icons/usuario.svg" class="icon-ui">
                <?= htmlspecialchars($_SESSION['correo'] ?? '') ?>
            </span>
            <a href="logout.php">Cerrar sesión</a>
            <button type="button" class="theme-toggle" onclick="toggleTheme()" aria-label="Cambiar modo claro/oscuro">Modo claro/oscuro <img src="assets/img/icons/modo.svg" class="icon-ui"></button>
        </div>
        <button class="hamburger" aria-label="Abrir menú" aria-expanded="false" aria-controls="nav-links"
            onclick="toggleMenu(this)">☰</button>
    </nav>

    <!-- ── Toast ──────────────────────────────────────────── -->
    <div id="toast" role="alert" aria-live="assertive"></div>

    <main>

        <!-- ── Card: Subir archivo ────────────────────── -->
        <section class="card" aria-labelledby="titulo-subir">
            <h2 id="titulo-subir"><img src="assets/img/icons/subir.svg" class="icon-ui">Analizar archivo</h2>

            <!-- Drag & drop -->
            <div id="drop-area" role="region" aria-label="Zona de carga de archivos" tabindex="0"
                aria-describedby="drop-hint">
                <span class="drop-icon" aria-hidden="true">📂</span>
                <p id="drop-hint">Arrastra tu archivo aquí o selecciónalo</p>
                <label for="file-input">Seleccionar archivo</label>
                <input type="file" id="file-input" name="archivo" aria-label="Seleccionar archivo para analizar"
                    accept=".jpg,.jpeg,.png,.gif,.bmp,.webp,.ico,.pdf,.zip,.rar,.7z,.tar,.gz,.mp3,.mp4,.wav,.avi">
                <p id="file-name" aria-live="polite"></p>
            </div>

            <!-- Barra de progreso -->
            <div id="progress-container" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"
                aria-label="Progreso de carga">
                <div id="progress-bar-wrap">
                    <div id="progress-bar"></div>
                </div>
                <p id="progress-label">Subiendo...</p>
            </div>

            <button id="btn-analizar" disabled aria-disabled="true">Analizar archivo</button>

            <!-- Resultado -->
            <div id="resultado-container" aria-live="polite">
                <div id="resultado-card">
                    <h3 id="resultado-titulo"></h3>
                    <img id="img-preview" alt="Vista previa del archivo analizado">
                    <table aria-label="Detalles del archivo analizado">
                        <tbody id="resultado-body"></tbody>
                    </table>
                    <div class="acciones">
                        <button class="btn-accion btn-otro" onclick="reiniciar()">
                            <img src="assets/img/icons/otro.svg" class="icon-ui">
                            Analizar otro
                        </button>
                        <button class="btn-accion btn-eliminar" id="btn-eliminar-resultado"
                            onclick="eliminarDesdeResultado()">
                            <img src="assets/img/icons/eliminar.svg" class="icon-ui">
                            Eliminar
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <!-- ── Card: Historial ────────────────────────── -->
        <section class="card" aria-labelledby="titulo-historial">
            <h2 id="titulo-historial"><img src="assets/img/icons/historial.svg" class="icon-ui"
                    alt="Historial">Historial de análisis</h2>

            <!-- Filtros -->
            <div class="filtros" role="search" aria-label="Filtros de historial">
                <div>
                    <label for="filtro-nombre">Buscar por nombre</label>
                    <input type="text" id="filtro-nombre" placeholder="nombre del archivo…"
                        aria-label="Buscar archivo por nombre">
                </div>
                <div>
                    <label for="filtro-tipo">Tipo</label>
                    <select id="filtro-tipo" aria-label="Filtrar por tipo de archivo">
                        <option value="">Todos los tipos</option>
                        <?php
                    $tipos = array_unique(array_column($archivos, 'tipo_detectado'));
                    sort($tipos);
                    foreach ($tipos as $t) {
                        echo '<option value="' . htmlspecialchars($t) . '">' . htmlspecialchars($t) . '</option>';
                    }
                    ?>
                    </select>
                </div>
                <div>
                    <label for="filtro-fecha">Fecha</label>
                    <input type="date" id="filtro-fecha" aria-label="Filtrar por fecha">
                </div>
                <button class="btn-filtrar" onclick="filtrarHistorial()" aria-label="Aplicar filtros">Filtrar</button>
                <button class="btn-limpiar" onclick="limpiarFiltros()" aria-label="Limpiar filtros">Limpiar</button>
            </div>

            <!-- Tabla -->
            <div class="table-wrapper" role="region" aria-label="Tabla de archivos analizados" tabindex="0">
                <table id="tabla-historial" aria-label="Historial de archivos analizados">
                    <thead>
                        <tr>
                            <th scope="col">Tipo</th>
                            <th scope="col">Nombre</th>
                            <th scope="col">Tamaño</th>
                            <th scope="col">Fecha</th>
                            <th scope="col"><span class="sr-only">Acciones</span></th>
                        </tr>
                    </thead>
                    <tbody id="tbody-historial">
                        <?php foreach ($archivos as $a):
                    $cfg = getTipoConfig($a['tipo_detectado'], $tipoConfig);
                ?>
                        <tr data-id="<?= $a['id'] ?>"
                            data-nombre="<?= strtolower(htmlspecialchars($a['nombre_original'])) ?>"
                            data-tipo="<?= htmlspecialchars($a['tipo_detectado']) ?>"
                            data-fecha="<?= substr($a['fecha_subida'], 0, 10) ?>">
                            <td>
                                <img src="<?= $cfg['icon'] ?>" class="icon-tipo" alt="icono">
                                <span class="tipo-badge"
                                    style="color:<?= $cfg['border'] ?>;border-color:<?= $cfg['border'] ?>;background:<?= $cfg['color'] ?>">
                                    <?= htmlspecialchars($a['tipo_detectado']) ?>
                                </span>
                            </td>
                            <td title="<?= htmlspecialchars($a['nombre_original']) ?>">
                                <?= htmlspecialchars(strlen($a['nombre_original']) > 35
                                ? substr($a['nombre_original'], 0, 32) . '…'
                                : $a['nombre_original']) ?>
                            </td>
                            <td><?= number_format($a['tamaño'] / 1024, 1) ?> KB</td>
                            <td><?= $a['fecha_subida'] ?></td>
                            <td>
                                <button class="btn-del-fila"
                                    aria-label="Eliminar <?= htmlspecialchars($a['nombre_original']) ?>"
                                    onclick="eliminarArchivo(<?= $a['id'] ?>, this)">
                                    <img src="assets/img/icons/eliminar.svg" class="icon-ui">
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <p id="no-resultados" role="status">No se encontraron archivos con esos filtros.</p>
            </div>

            <!-- Paginación -->
            <nav id="paginacion" aria-label="Paginación del historial"></nav>
        </section>

    </main>

    <script>
    // ── Configuración ────────────────────────────────────────
    const TIPOS_CONFIG = <?= json_encode($tipoConfig) ?>;
    const POR_PAGINA = 10;
    let paginaActual = 1;
    let ultimoId = null; // id del último archivo analizado (para eliminar desde resultado)

    // ── Menú responsive ─────────────────────────────────────
    function toggleMenu(btn) {
        const nav = document.getElementById('nav-links');
        const open = nav.classList.toggle('open');
        btn.setAttribute('aria-expanded', open);
    }

    // ── Drag & Drop ──────────────────────────────────────────
    const dropArea = document.getElementById('drop-area');
    const fileInput = document.getElementById('file-input');
    const fileNameP = document.getElementById('file-name');
    const btnAnalizar = document.getElementById('btn-analizar');

    ['dragenter', 'dragover'].forEach(e => dropArea.addEventListener(e, ev => {
        ev.preventDefault();
        dropArea.classList.add('dragover');
    }));
    ['dragleave', 'drop'].forEach(e => dropArea.addEventListener(e, ev => {
        ev.preventDefault();
        dropArea.classList.remove('dragover');
    }));

    dropArea.addEventListener('drop', ev => {
        const file = ev.dataTransfer.files[0];
        if (file) setArchivo(file);
    });

    dropArea.addEventListener('keydown', ev => {
        if (ev.key === 'Enter' || ev.key === ' ') fileInput.click();
    });

    fileInput.addEventListener('change', () => {
        if (fileInput.files[0]) setArchivo(fileInput.files[0]);
    });

    function setArchivo(file) {
        fileNameP.textContent = '📎 ' + file.name + ' (' + (file.size / 1024).toFixed(1) + ' KB)';
        btnAnalizar.disabled = false;
        btnAnalizar.setAttribute('aria-disabled', 'false');
        btnAnalizar._archivo = file;
        document.getElementById('resultado-container').style.display = 'none';
    }

    // ── Analizar via AJAX ────────────────────────────────────
    btnAnalizar.addEventListener('click', () => {
        const file = btnAnalizar._archivo;
        if (!file) return;

        const formData = new FormData();
        formData.append('archivo', file);

        const progContainer = document.getElementById('progress-container');
        const progBar = document.getElementById('progress-bar');
        const progLabel = document.getElementById('progress-label');
        const progWrap = document.getElementById('progress-container');

        progContainer.style.display = 'block';
        btnAnalizar.disabled = true;
        btnAnalizar.setAttribute('aria-disabled', 'true');

        const xhr = new XMLHttpRequest();

        xhr.upload.addEventListener('progress', ev => {
            if (ev.lengthComputable) {
                const pct = Math.round(ev.loaded / ev.total * 100);
                progBar.style.width = pct + '%';
                progLabel.textContent = pct + '%';
                progWrap.setAttribute('aria-valuenow', pct);
            }
        });

        xhr.addEventListener('load', () => {
            progContainer.style.display = 'none';
            try {
                const data = JSON.parse(xhr.responseText);
                if (data.error) {
                    mostrarToast('❌ ' + data.error, 'error');
                    btnAnalizar.disabled = false;
                    btnAnalizar.setAttribute('aria-disabled', 'false');
                    return;
                }
                mostrarResultado(data, file);
                recargarFila(data);
            } catch (e) {
                mostrarToast('❌ Error inesperado del servidor', 'error');
                btnAnalizar.disabled = false;
                btnAnalizar.setAttribute('aria-disabled', 'false');
            }
        });

        xhr.addEventListener('error', () => {
            progContainer.style.display = 'none';
            mostrarToast('❌ Error de red', 'error');
            btnAnalizar.disabled = false;
            btnAnalizar.setAttribute('aria-disabled', 'false');
        });

        xhr.open('POST', 'api/analizar.php');
        xhr.send(formData);
    });

    // ── Mostrar resultado ────────────────────────────────────
    function mostrarResultado(data, file) {
        const tipo = data.tipo_nombre || 'DESCONOCIDO';
        const cfg = TIPOS_CONFIG[tipo] || {
            color: '#f5f5f5',
            border: '#9e9e9e',
            icon: '📁'
        };
        const card = document.getElementById('resultado-card');
        const titulo = document.getElementById('resultado-titulo');
        const body = document.getElementById('resultado-body');
        const preview = document.getElementById('img-preview');

        card.style.background = cfg.color;
        card.style.borderColor = cfg.border;

        titulo.textContent = (data.cache ? '⚡ Resultado desde caché — ' : '✅ Analizado — ') + tipo;

        body.innerHTML = `
        <tr><td>Nombre</td><td>${escHTML(data.nombre_original)}</td></tr>
        <tr><td>Tipo detectado</td><td>
            <span class="tipo-badge" style="color:${cfg.border};border-color:${cfg.border};background:${cfg.color}">
                <img src="${cfg.icon}" class="icon-tipo"> ${escHTML(tipo)}
            </span>
        </td></tr>
        <tr><td>Tamaño</td><td>${(data.tamaño / 1024).toFixed(1)} KB</td></tr>
        <tr><td>Hash MD5</td><td style="font-family:monospace;font-size:.8rem">${escHTML(data.hash_md5)}</td></tr>
    `;

        // Preview solo para imágenes
        if (['JPEG', 'PNG', 'GIF', 'BMP'].includes(tipo) && file) {
            const reader = new FileReader();
            reader.onload = e => {
                preview.src = e.target.result;
                preview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        } else {
            preview.style.display = 'none';
        }

        ultimoId = data.id_guardado ?? null;
        document.getElementById('resultado-container').style.display = 'block';
        mostrarToast('✅ Archivo analizado: ' + tipo, 'success');
    }

    function reiniciar() {
        document.getElementById('resultado-container').style.display = 'none';
        document.getElementById('file-name').textContent = '';
        btnAnalizar.disabled = true;
        btnAnalizar.setAttribute('aria-disabled', 'true');
        btnAnalizar._archivo = null;
        fileInput.value = '';
    }

    function eliminarDesdeResultado() {
        if (!ultimoId) {
            mostrarToast('No se puede eliminar (id no disponible)', 'error');
            return;
        }
        if (!confirm('¿Eliminar este archivo del historial?')) return;
        eliminarArchivo(ultimoId, null, true);
    }

    // ── Eliminar fila ────────────────────────────────────────
    function eliminarArchivo(id, btn, desdeResultado = false) {
        if (!desdeResultado && !confirm('¿Eliminar este archivo del historial?')) return;

        fetch('api/eliminar.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    id
                })
            })
            .then(r => r.json())
            .then(data => {
                if (data.ok) {
                    // Quitar fila de la tabla
                    const fila = document.querySelector(`tr[data-id="${id}"]`);
                    if (fila) fila.remove();
                    if (desdeResultado) reiniciar();
                    mostrarToast('🗑️ Archivo eliminado', 'success');
                    reconstruirPaginacion();
                } else {
                    mostrarToast('❌ ' + (data.error || 'Error al eliminar'), 'error');
                }
            })
            .catch(() => mostrarToast('❌ Error de red al eliminar', 'error'));
    }

    // Agregar fila al historial sin recargar la página
    function recargarFila(data) {
        const tipo = data.tipo_nombre || 'DESCONOCIDO';
        const cfg = TIPOS_CONFIG[tipo] || {
            color: '#f5f5f5',
            border: '#9e9e9e',
            icon: '📁'
        };
        const tbody = document.getElementById('tbody-historial');
        const fecha = new Date().toISOString().slice(0, 19).replace('T', ' ');
        const id = data.id_guardado || 0;

        const tr = document.createElement('tr');
        tr.dataset.id = id;
        tr.dataset.nombre = (data.nombre_original || '').toLowerCase();
        tr.dataset.tipo = tipo;
        tr.dataset.fecha = fecha.slice(0, 10);
        tr.innerHTML = `
        <td>
            <span aria-hidden="true">
                <img src="${cfg.icon}" class="icon-tipo" alt="icono">
            </span>
            <span class="tipo-badge" style="color:${cfg.border};border-color:${cfg.border};background:${cfg.color}">${tipo}</span>
        </td>
        <td title="${escHTML(data.nombre_original)}">${escHTML((data.nombre_original||'').substring(0,35))}</td>
        <td>${(data.tamaño / 1024).toFixed(1)} KB</td>
        <td>${fecha}</td>
        <td><button class="btn-del-fila" aria-label="Eliminar ${escHTML(data.nombre_original)}" onclick="eliminarArchivo(${id}, this)"><img src="assets/img/icons/eliminar.svg" class="icon-ui"></button></td>
    `;
        tbody.prepend(tr);
        filtrarHistorial();
    }

    // ── Historial: filtros y paginación ─────────────────────
    function filtrarHistorial() {
        const nombre = document.getElementById('filtro-nombre').value.toLowerCase().trim();
        const tipo = document.getElementById('filtro-tipo').value;
        const fecha = document.getElementById('filtro-fecha').value;

        const filas = [...document.querySelectorAll('#tbody-historial tr')];
        const visibles = filas.filter(tr => {
            const okNombre = !nombre || tr.dataset.nombre.includes(nombre);
            const okTipo = !tipo || tr.dataset.tipo === tipo;
            const okFecha = !fecha || tr.dataset.fecha === fecha;
            return okNombre && okTipo && okFecha;
        });

        filas.forEach(tr => tr.style.display = 'none');

        const totalPags = Math.max(1, Math.ceil(visibles.length / POR_PAGINA));
        if (paginaActual > totalPags) paginaActual = totalPags;

        const inicio = (paginaActual - 1) * POR_PAGINA;
        visibles.slice(inicio, inicio + POR_PAGINA).forEach(tr => tr.style.display = '');

        document.getElementById('no-resultados').style.display = visibles.length === 0 ? 'block' : 'none';
        construirPaginacion(totalPags, visibles.length);
    }

    function construirPaginacion(total, totalFilas) {
        const nav = document.getElementById('paginacion');
        nav.innerHTML = '';
        if (total <= 1) return;

        const info = document.createElement('span');
        info.style.cssText = 'font-size:.8rem;color:#fff;align-self:center;margin-right:8px';
        info.textContent = totalFilas + ' registros';
        nav.appendChild(info);

        for (let i = 1; i <= total; i++) {
            const btn = document.createElement('button');
            btn.className = 'btn-pag' + (i === paginaActual ? ' active' : '');
            btn.textContent = i;
            btn.setAttribute('aria-label', 'Página ' + i);
            if (i === paginaActual) btn.setAttribute('aria-current', 'page');
            btn.onclick = () => {
                paginaActual = i;
                filtrarHistorial();
            };
            nav.appendChild(btn);
        }
    }

    function reconstruirPaginacion() {
        filtrarHistorial();
    }

    function limpiarFiltros() {
        document.getElementById('filtro-nombre').value = '';
        document.getElementById('filtro-tipo').value = '';
        document.getElementById('filtro-fecha').value = '';
        paginaActual = 1;
        filtrarHistorial();
    }

    // ── Toast ────────────────────────────────────────────────
    function mostrarToast(msg, tipo = '') {
        const t = document.getElementById('toast');
        t.textContent = msg;
        t.className = 'show ' + tipo;
        clearTimeout(t._timer);
        t._timer = setTimeout(() => {
            t.className = '';
        }, 3500);
    }

    // ── Utilidad ─────────────────────────────────────────────
    function escHTML(str) {
        return String(str || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g,
            '&quot;');
    }

    // Inicializar paginación al cargar
    document.addEventListener('DOMContentLoaded', () => filtrarHistorial());
    </script>
</body>

</html>