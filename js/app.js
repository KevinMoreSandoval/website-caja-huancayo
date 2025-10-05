// Mostrar automáticamente al cargar la página
window.addEventListener("DOMContentLoaded", () => {
  const modal = new bootstrap.Modal(document.getElementById("promoModal"));
  modal.show();
});

const limitKey = 4;

const concatenar = (id, number) => {
  const input = document.getElementById(id);
  let values = input.value.length;
  if (values < limitKey) {
    input.value += number;
  }
};
