window.onload = () => {

  let collection = document.querySelector("#requirements-item");
  let span = document.querySelector( "#offer_requirement_requirementItems" );
  let newRequirementItemBtn = document.querySelector(".ajout-requirement-item");

  collection.dataset.index =
    collection.querySelector("#offer_requirement_requirementItems").childElementCount;

  if (collection.dataset.index > 0) {
    for (let i = 0; i < collection.dataset.index; i++) {
      const boutonSupp = createDeletebtn(i, collection);
      collection.querySelector("#offer_requirement_requirementItems").childNodes[i].append(boutonSupp);
    }
  }

  newRequirementItemBtn.addEventListener("click", () =>
    span.append(createNewIngredientForm(collection))
  );
    
};

const createDeletebtn = (index, collection) => {
  const boutonSupp = document.createElement("button");
  boutonSupp.className = "btn btn-danger";
  boutonSupp.id = `delete-ingredient-${index}`;
  boutonSupp.setAttribute("type", "button");

  boutonSupp.textContent = "supprimer";

  boutonSupp.addEventListener("click", (e) => {
    if (e.target.classList.contains("btn")) {
      e.target.parentElement.remove();
      collection.dataset.index--;
    }
  });

  return boutonSupp;
};

const createNewIngredientForm = (collection) => {
  let index = collection.dataset.index;
  let prototype = collection.dataset.prototype.replace(/__name__/g, index);

  // transforme le template en html
  let content = document.createElement("div");
  content.innerHTML = prototype;

  let newForm = content.querySelector("fieldset");

  const boutonSupp = createDeletebtn(index, collection);

  newForm.append(boutonSupp);

  collection.dataset.index++;

  return newForm;
};

