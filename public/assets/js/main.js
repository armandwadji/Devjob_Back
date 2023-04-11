
// Méthode de visibility hidden des méssages d'alertes au bout de 3s
const alerts = document.querySelectorAll( ".alert" );

alerts.forEach((alert) => {
  setTimeout(() => {
    alert.classList.add("visually-hidden");
  }, 3000);
});

// Méthode d'ajout d'une couleur aux icons d'erreurs pour une meilleur visibilité
const errorIcons = document.querySelectorAll( ".form-error-icon" );

errorIcons.forEach((icon) => icon.classList.add("bg-primary"));

// Gestion de la visibilité des mots de passes.
const eyes = document.querySelectorAll(".fa-solid.fa-eye");

eyes.forEach((eye) => {
  eye.addEventListener("click", (e) => {
    const eyeBtn = e.target;

    if (eyeBtn.classList.contains("fa-eye")) {
      eyeBtn.classList.replace("fa-eye", "fa-eye-slash");
    } else {
      eyeBtn.classList.replace("fa-eye-slash", "fa-eye");
    }

    e.target.previousElementSibling.setAttribute( "type", e.target.previousElementSibling.getAttribute( "type" ) !== "text" ? "text" : "password" );
  });
});

// Modal pour confirmation de suppression
const deletes = document.querySelectorAll(".delete-confirm");

deletes.forEach((deleteBtn) => {
  deleteBtn.addEventListener("click", (e) => {
    const url = e.target.href;
    const form = document.querySelector( "#modalWindow form" );

    form.addEventListener("submit", (e) => {
      e.preventDefault();

      document.cookie = `password=${form.password.value}; expires= "${new Date(Date.now() + 1000).toUTCString()}"`;

      window.location.href = url;
    });

    form.password.value = "";
  });
});

