document.addEventListener("DOMContentLoaded", async () => {
  try {
    const res = await fetch("../usuario-data.php", {
      credentials: "same-origin",
    });
    const data = await res.json();

    if (data && data.error === "NO_SESSION") {
      // Redirigir si no hay sesión
      window.location.href = "../index.html";
      return;
    }

    const user = data || {};

    // Actualizar el nombre en la barra superior y el saludo (si existen)
    const navName = document.querySelector("nav .me-3");
    const welcomeH4 = document.querySelector(".welcome-text h4");
    const profileImg = document.querySelector(".profile-img");
    const badge = document.querySelector(".user-badge");

    const fullName =
      [user.nombre, user.apellido].filter(Boolean).join(" ") ||
      user.nombre ||
      user.nombre_completo ||
      "";

    if (fullName) {
      if (navName) navName.textContent = fullName;
      if (welcomeH4) welcomeH4.textContent = `Bienvenido(a), ${fullName}`;

      const initials = fullName
        .split(" ")
        .map((n) => n[0])
        .filter(Boolean)
        .slice(0, 2)
        .join("")
        .toUpperCase();

      if (badge) badge.textContent = initials;

      if (profileImg) {
        profileImg.src = `https://ui-avatars.com/api/?name=${encodeURIComponent(
          fullName
        )}&background=FF0000&color=fff&size=80`;
      }
    }

    // Helper: actualiza un .info-item por texto de etiqueta (p.product-label)
    function setInfoItemByLabel(labelText, value) {
      if (value === undefined || value === null || value === "") return false;
      const items = document.querySelectorAll(".info-card .info-item");
      for (const item of items) {
        const labelEl = item.querySelector(".product-label");
        const valueEl = item.querySelector(".product-amount");
        if (!labelEl || !valueEl) continue;
        if (
          labelEl.textContent
            .trim()
            .toLowerCase()
            .includes(labelText.toLowerCase())
        ) {
          valueEl.textContent = value;
          return true;
        }
      }
      return false;
    }

    // Actualizar campos personales si existen en el objeto usuario
    setInfoItemByLabel(
      "DNI",
      user.dni || user.numero_documento || user.documento
    );
    setInfoItemByLabel(
      "Teléfono",
      user.telefono || user.celular || user.telefono_movil
    );
    setInfoItemByLabel("Email", user.correo || user.email);
    setInfoItemByLabel(
      "Dirección",
      user.direccion || user.domicilio || user.direccion_fiscal
    );

    // Actualizar tarjetas/productos por etiquetas internas
    const productCards = document.querySelectorAll(
      ".productos-grid .product-card"
    );
    productCards.forEach((card) => {
      const labelEl = card.querySelector(".product-label");
      const amountEl = card.querySelector(".product-amount");
      if (!labelEl || !amountEl) return;
      const labelText = labelEl.textContent.trim().toLowerCase();

      // Saldo disponible
      if (
        labelText.includes("saldo") &&
        (user.saldo || user.balance || user.saldo_disponible)
      ) {
        amountEl.textContent = `S/ ${
          user.saldo || user.balance || user.saldo_disponible
        }`;
      }

      // Monto del crédito
      if (
        labelText.includes("monto") &&
        (user.monto_credito || user.credito_monto)
      ) {
        amountEl.textContent = `S/ ${user.monto_credito || user.credito_monto}`;
      }

      // Línea disponible (tarjeta)
      if (
        labelText.includes("línea") &&
        (user.linea_disponible || user.linea)
      ) {
        amountEl.textContent = `S/ ${user.linea_disponible || user.linea}`;
      }
    });
    const badgeUser = document.querySelector(".user-badge");
    if (badgeUser) {
      badgeUser.innerHTML = fullName;
    }
    // Si existe algún elemento para mostrar número de cuenta, actualizarlo
    const cuentaEl = document.getElementById("cuenta");
    if (cuentaEl && (user.numero_cuenta || user.cuenta)) {
      cuentaEl.textContent = `Cuenta: ${user.numero_cuenta || user.cuenta}`;
    }

    // Evento para cerrar sesión
    function cerrarSesion() {
      console.log("Cerrando sesión...");
      fetch("../logout.php", {
        credentials: "same-origin",
      })
        .then(() => {
          console.log("Sesión cerrada, redirigiendo...");
          window.location.href = "../index.html";
        })
        .catch((error) => {
          console.error("Error al cerrar sesión:", error);
          // Redirigir de todas formas para asegurar el logout
          window.location.href = "../index.html";
        });
    }

    const logoutBtn = document.getElementById("logout");
    if (logoutBtn) {
      console.log("Botón de logout encontrado, agregando event listener");
      logoutBtn.addEventListener("click", cerrarSesion);
    } else {
      console.error("Botón de logout no encontrado");
    }
  } catch (err) {
    console.error("Error cargando datos de usuario:", err);
  }
});
