// Mostrar automáticamente al cargar la página
      window.addEventListener("DOMContentLoaded", () => {
        const modal = new bootstrap.Modal(
          document.getElementById("promoModal")
        );
        modal.show();
      });