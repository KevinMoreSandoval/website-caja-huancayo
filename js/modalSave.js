function copiar(elementId) {
  const elemento = document.getElementById(elementId);
  const texto = elemento.textContent;

  navigator.clipboard
    .writeText(texto)
    .then(function () {
      alert("¡Copiado al portapapeles!");
    })
    .catch(function (err) {
      console.error("Error al copiar:", err);
    });
}
