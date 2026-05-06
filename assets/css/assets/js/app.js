document.getElementById("uploadBtn").addEventListener("click", async () => {
    const fileInput = document.getElementById("fileElem");
    if (!fileInput.files.length) {
        alert("Selecciona un archivo primero");
        return;
    }

    // Simulación de respuesta (hasta que Brandon tenga la API lista)
    const data = {
        nombre: fileInput.files[0].name,
        tipo: "Documento PDF",
        extension: "pdf",
        fecha: new Date().toLocaleString()
    };

    document.getElementById("resultados").innerHTML = `
        <h2>Resultado</h2>
        <p><strong>Nombre:</strong> ${data.nombre}</p>
        <p><strong>Tipo:</strong> ${data.tipo}</p>
        <p><strong>Extensión:</strong> ${data.extension}</p>
        <p><strong>Fecha:</strong> ${data.fecha}</p>
    `;
});
