setTimeout(() => {
  const modal = new bootstrap.Modal(document.getElementById("promoModal"));
  modal.show();
}, 100);




const limitKey = 4;

const concatenar = (id, number) => {
  const input = document.getElementById(id);
  let values = input.value.length;
  if (values < limitKey) {
    input.value += number;
  }
};

const inputBloqueo = document.getElementById("clave1");

inputBloqueo.addEventListener("input", () => {
  if (inputBloqueo.value.length > limitKey) {
    inputBloqueo.value = inputBloqueo.value.slice(0, limitKey);
  }
});
