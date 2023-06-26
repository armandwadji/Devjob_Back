
// Méthode de visibility hidden des méssages d'alertes au bout de 3s
const alerts = document.querySelectorAll( ".alert" );

alerts.forEach((alert) => {
  setTimeout(() => {
    alert.classList.add("visually-hidden");
  }, 4000);
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

window.onload = () => {

  // ******** REQUIREMENT *********
  let requirements = document.querySelector("#requirements-item");
  let span = document.querySelector( "#offer_requirement_requirementItems" );
  let newRequirementItemBtn = document.querySelector(".ajout-requirement-item");

  requirements.dataset.index = requirements.querySelector("#offer_requirement_requirementItems").childElementCount;

  if (requirements.dataset.index > 0) {
    for (let i = 0; i < requirements.dataset.index; i++) {
      const boutonSupp = createDeletebtn(i, requirements);
      requirements.querySelector("#offer_requirement_requirementItems").childNodes[i].append(boutonSupp);
    }
  }

  // Evènement pour créer un nouveau requirement item
  newRequirementItemBtn.addEventListener( "click", () => span.append( createNewItemForm( requirements ) ) );

   // ******** ROLES ********
  let roles = document.querySelector( '#roles-item' );
  let spanRole= document.querySelector( "#offer_role_roleItems" );
  let newRolesItemBtn = document.querySelector( '.ajout-role-item' );

  roles.dataset.index = roles.querySelector( "#offer_role_roleItems" ).childElementCount;
  
  if (roles.dataset.index > 0) {
    for (let i = 0; i < roles.dataset.index; i++) {
      const boutonSupp = createDeletebtn(i, roles);
      roles.querySelector("#offer_role_roleItems").childNodes[i].append(boutonSupp);
    }
  }

  // Evènement pour créer un nouveau role item
  newRolesItemBtn.addEventListener("click", () =>  spanRole.append(createNewItemForm(roles)) );
    
};

// Méthode de création du boutton supprimer d'un item
const createDeletebtn = (index, requirements) => {
  const boutonSupp = document.createElement("button");
  boutonSupp.className = "btn btn-danger";
  boutonSupp.id = `delete-ingredient-${index}`;
  boutonSupp.setAttribute("type", "button");

  boutonSupp.textContent = "supprimer";

  boutonSupp.addEventListener("click", (e) => {
    if (e.target.classList.contains("btn")) {
      e.target.parentElement.remove();
      requirements.dataset.index--;
    }
  });

  return boutonSupp;
};

// Méthode de création d'un nouvel item
const createNewItemForm = (requirements) => {
  let index = requirements.dataset.index;
  let prototype = requirements.dataset.prototype.replace(/__name__/g, index);

  // transforme le template en html
  let content = document.createElement("div");
  content.innerHTML = prototype;

  let newForm = content.querySelector("fieldset");

  const boutonSupp = createDeletebtn(index, requirements);

  newForm.append(boutonSupp);

  requirements.dataset.index++;

  return newForm;
};



