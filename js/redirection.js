
document.getElementById("cajaVirtual").addEventListener("submit", async (e) => {
    e.preventDefault();

    const formData = new FormData(e.target);

    // El archivo `login.php` está en la misma carpeta que `index.html`,
    // por eso la ruta debe ser relativa al root: `login.php`.
    const respuesta = await fetch("login.php", {
        method: "POST",
        body: formData,
        credentials: 'same-origin'
    });

    const texto = await respuesta.text();

    if (texto.trim() === "OK") {
        // `usuario.html` está en la carpeta `pages/` dentro del proyecto.
        window.location.href = "pages/usuario.html"; // Página personalizada
    } else {
        console.log(texto.trim());
        alert(texto.trim());
    }
});

