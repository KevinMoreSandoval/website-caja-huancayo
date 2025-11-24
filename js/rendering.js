document.addEventListener("DOMContentLoaded", async () => {
  try {
    // Obtener datos del usuario desde PHP
    const res = await fetch("../usuario-data.php", {
      credentials: "same-origin",
    });
    const data = await res.json();

    // Verificar sesión
    if (data && data.error === "NO_SESSION") {
      window.location.href = "../index.html";
      return;
    }

    const user = data || {};

    // ========================================
    // RENDERIZAR DATOS DEL USUARIO
    // ========================================

    // Nombre completo para hero
    const fullName =
      `${user.nombre || ""} ${user.apellido || ""}`.trim() || "Usuario";

    // Primer nombre y primer apellido para navbar
    const firstName = user.nombre ? user.nombre.split(" ")[0] : "";
    const firstLastName = user.apellido ? user.apellido.split(" ")[0] : "";
    const shortName = `${firstName} ${firstLastName}`.trim() || "Usuario";

    // Actualizar nombre en navbar (solo primer nombre y apellido)
    const navbarUserName = document.querySelector(".navbar .user-name");
    if (navbarUserName) {
      navbarUserName.textContent = shortName;
    }

    // Actualizar nombre completo en hero
    const heroUserName = document.querySelector(".hero .user-fullname");
    if (heroUserName) {
      heroUserName.textContent = fullName;
    }

    // Actualizar avatar con iniciales
    const userAvatar = document.querySelector(".user-avatar");
    if (userAvatar && shortName) {
      const initials = shortName
        .split(" ")
        .map((word) => word[0])
        .join("")
        .substring(0, 2)
        .toUpperCase();
      userAvatar.textContent = initials;
    }

    // Actualizar información personal
    const infoItems = document.querySelectorAll(".info-item");
    infoItems.forEach((item) => {
      const label = item.querySelector(".info-label");
      const value = item.querySelector(".info-value");
      if (!label || !value) return;

      const labelText = label.textContent.trim().toLowerCase();

      if (labelText.includes("dni") && user.dni) {
        value.textContent = user.dni;
      } else if (labelText.includes("teléfono") && user.telefono) {
        value.textContent = user.telefono;
      } else if (labelText.includes("email") && user.correo) {
        value.textContent = user.correo;
      } else if (labelText.includes("dirección") && user.direccion) {
        value.textContent = user.direccion;
      }
    });

    // Actualizar productos (saldo y línea de crédito)
    const productCards = document.querySelectorAll(".product-card");
    productCards.forEach((card) => {
      const label = card.querySelector(".product-label");
      const amount = card.querySelector(".product-amount span");
      if (!label || !amount) return;

      const labelText = label.textContent.trim().toLowerCase();

      if (labelText.includes("saldo") && user.saldo) {
        amount.textContent = user.saldo;
      } else if (labelText.includes("línea") && user.linea_disponible) {
        amount.textContent = user.linea_disponible;
      }
    });

    // ========================================
    // CERRAR SESIÓN
    // ========================================
    const logoutBtn = document.getElementById("logout");
    if (logoutBtn) {
      logoutBtn.addEventListener("click", async () => {
        try {
          await fetch("../logout.php", { credentials: "same-origin" });
        } catch (error) {
          console.error("Error al cerrar sesión:", error);
        } finally {
          window.location.href = "../index.html";
        }
      });
    }

    // ========================================
    // ACTUALIZAR DATOS DEL USUARIO
    // ========================================

    // Cargar datos actuales cuando se abre el modal
    const modalActualizar = document.getElementById("actualizar");
    if (modalActualizar) {
      modalActualizar.addEventListener("show.bs.modal", () => {
        document.getElementById("direccion").value = user.direccion || "";
        document.getElementById("telefono").value = user.telefono || "";
        document.getElementById("correo").value = user.correo || "";
      });
    }

    // Manejar actualización de datos
    const btnActualizar = document.getElementById("btnActualizar");
    if (btnActualizar) {
      btnActualizar.addEventListener("click", async () => {
        const direccion = document.getElementById("direccion").value;
        const telefono = document.getElementById("telefono").value;
        const correo = document.getElementById("correo").value;

        // Validar campos
        if (!direccion || !telefono || !correo) {
          alert("Por favor, completa todos los campos");
          return;
        }

        // Deshabilitar botón
        btnActualizar.disabled = true;
        btnActualizar.textContent = "Actualizando...";

        try {
          // Enviar datos al servidor
          const formData = new FormData();
          formData.append("direccion", direccion);
          formData.append("telefono", telefono);
          formData.append("correo", correo);

          const response = await fetch("../actualizar-usuario.php", {
            method: "POST",
            credentials: "same-origin",
            body: formData,
          });

          const result = await response.json();

          if (result.success) {
            // Actualizar datos locales
            user.direccion = result.data.direccion;
            user.telefono = result.data.telefono;
            user.correo = result.data.correo;

            // Actualizar interfaz
            document.querySelectorAll(".info-item").forEach((item) => {
              const label = item.querySelector(".info-label");
              const value = item.querySelector(".info-value");
              if (!label || !value) return;

              const labelText = label.textContent.trim().toLowerCase();
              if (labelText.includes("teléfono")) {
                value.textContent = result.data.telefono;
              } else if (labelText.includes("email")) {
                value.textContent = result.data.correo;
              } else if (labelText.includes("dirección")) {
                value.textContent = result.data.direccion;
              }
            });

            alert("¡Datos actualizados correctamente!");

            // Cerrar modal
            const modal = bootstrap.Modal.getInstance(modalActualizar);
            if (modal) modal.hide();
          } else {
            alert(
              "Error: " +
                (result.error || "No se pudieron actualizar los datos")
            );
          }
        } catch (error) {
          console.error("Error al actualizar datos:", error);
          alert(
            "Error al actualizar los datos. Por favor, intenta nuevamente."
          );
        } finally {
          btnActualizar.disabled = false;
          btnActualizar.textContent = "Actualizar";
        }
      });
    }

    // ========================================
    // RECARGAR SALDO
    // ========================================

    // Limpiar formulario cuando se cierra el modal
    const modalRecarga = document.getElementById("recarga");
    if (modalRecarga) {
      modalRecarga.addEventListener("hidden.bs.modal", () => {
        document.getElementById("formRecarga").reset();
      });
    }

    // Manejar recarga de saldo
    const btnRecarga = document.getElementById("btnRecarga");
    if (btnRecarga) {
      btnRecarga.addEventListener("click", async () => {
        const monto = document.getElementById("monto").value;

        // Validar campos
        if (!monto || parseFloat(monto) <= 0) {
          alert("Por favor, ingresa un monto válido mayor a 0");
          return;
        }

        // Deshabilitar botón
        btnRecarga.disabled = true;
        btnRecarga.textContent = "Recargando...";

        try {
          // Enviar datos al servidor
          const formData = new FormData();
          formData.append("monto", monto);

          const response = await fetch("../recargar-saldo.php", {
            method: "POST",
            credentials: "same-origin",
            body: formData,
          });

          const result = await response.json();

          if (result.success) {
            // Actualizar saldo local
            user.saldo = result.data.saldo;

            // Actualizar interfaz
            const productCards = document.querySelectorAll(".product-card");
            productCards.forEach((card) => {
              const label = card.querySelector(".product-label");
              const amount = card.querySelector(".product-amount span");
              if (!label || !amount) return;

              const labelText = label.textContent.trim().toLowerCase();
              if (labelText.includes("saldo")) {
                amount.textContent = result.data.saldo;
              }
            });

            alert("¡Recarga exitosa! Nuevo saldo: S/ " + result.data.saldo);

            // Cerrar modal
            const modal = bootstrap.Modal.getInstance(modalRecarga);
            if (modal) modal.hide();
          } else {
            alert(
              "Error: " + (result.error || "No se pudo realizar la recarga")
            );
          }
        } catch (error) {
          console.error("Error al recargar saldo:", error);
          alert("Error al recargar el saldo. Por favor, intenta nuevamente.");
        } finally {
          btnRecarga.disabled = false;
          btnRecarga.textContent = "Recargar";
        }
      });
    }
  } catch (err) {
    console.error("Error cargando datos de usuario:", err);
  }
});
