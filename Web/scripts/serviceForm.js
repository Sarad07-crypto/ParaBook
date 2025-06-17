function initServiceForm() {
  const progress = document.getElementById("progress");
  const nextBtn = document.getElementById("next");
  const prevBtn = document.getElementById("prev");
  const progressStep = document.querySelectorAll(".progress-step .bx");
  const formSteps = document.querySelectorAll(".form-step");

  const nameInput = document.getElementById("flight-type-name");
  const priceInput = document.getElementById("flight-type-price");
  const addBtn = document.getElementById("add-flight-type");
  const list = document.getElementById("flight-type-list");

  let flightTypes = [];
  let editingIndex = -1;
  let currentStep = 1;

  function next() {
    if (currentStep < progressStep.length) {
      currentStep++;
      refresh();
    }
  }

  function prev() {
    currentStep--;
    if (currentStep < 1) currentStep = 1;
    refresh();
  }

  window.next = next;
  window.prev = prev;

  function refresh() {
    progressStep.forEach((step, index) => {
      step.classList.toggle("active", index < currentStep);
    });

    prevBtn.classList.toggle("disabled", currentStep === 1);
    nextBtn.classList.toggle("disabled", currentStep === progressStep.length);

    let width = ((currentStep - 1) / (progressStep.length - 1)) * 100;
    if (currentStep === progressStep.length) width -= 10;

    progress.style.width = width + "%";

    formSteps.forEach((form, index) => {
      form.classList.toggle("active", index === currentStep - 1);
    });

    const submitBtn = document.getElementById("submitBtn");
    submitBtn.style.display =
      currentStep === formSteps.length ? "inline-block" : "none";
  }

  refresh();

  function renderList() {
    list.innerHTML = "";
    flightTypes.forEach((item, index) => {
      const li = document.createElement("li");
      li.innerHTML = `
                <strong>${item.name}</strong> - Rs. ${item.price}
                <button type="button" onclick="editFlight(${index})">Edit</button>
                <button type="button" onclick="removeFlight(${index})">Remove</button>
            `;
      list.appendChild(li);
    });
  }

  function resetForm() {
    nameInput.value = "";
    priceInput.value = "";
    editingIndex = -1;
    addBtn.textContent = "Add";
  }

  addBtn.addEventListener("click", () => {
    const name = nameInput.value.trim();
    const price = priceInput.value.trim();

    if (!name || !price) {
      alert("Please fill in both fields.");
      return;
    }

    if (editingIndex === -1) {
      flightTypes.push({ name, price });
    } else {
      flightTypes[editingIndex] = { name, price };
    }

    renderList();
    resetForm();
  });

  window.editFlight = function (index) {
    const item = flightTypes[index];
    nameInput.value = item.name;
    priceInput.value = item.price;
    editingIndex = index;
    addBtn.textContent = "Update";
  };

  window.removeFlight = function (index) {
    flightTypes.splice(index, 1);
    renderList();
    resetForm();
  };
}
