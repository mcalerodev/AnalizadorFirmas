<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Analizador de Firmas</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <h1>Subir archivo para análisis</h1>

    <div id="drop-area">
        <p>Arrastra tu archivo aquí o usa el botón</p>
        <input type="file" id="fileElem" accept="*/*">
        <button id="uploadBtn">Analizar</button>
    </div>

    <div id="resultados"></div>

    <!-- Aquí se conecta el JS -->
    <script src="assets/js/app.js"></script>
</body>
</html>
