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

