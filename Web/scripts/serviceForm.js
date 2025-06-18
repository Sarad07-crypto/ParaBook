function initServiceForm() {
  console.log("=== initServiceForm started ===");

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
    if (submitBtn) {
      submitBtn.style.display =
        currentStep === formSteps.length ? "inline-block" : "none";
    }
  }

  refresh();

  function renderList() {
    if (!list) return;
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
    if (nameInput) nameInput.value = "";
    if (priceInput) priceInput.value = "";
    editingIndex = -1;
    if (addBtn) addBtn.textContent = "Add";
  }

  if (addBtn) {
    addBtn.addEventListener("click", () => {
      const name = nameInput ? nameInput.value.trim() : "";
      const price = priceInput ? priceInput.value.trim() : "";

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
  }

  window.editFlight = function (index) {
    const item = flightTypes[index];
    if (nameInput) nameInput.value = item.name;
    if (priceInput) priceInput.value = item.price;
    editingIndex = index;
    if (addBtn) addBtn.textContent = "Update";
  };

  window.removeFlight = function (index) {
    flightTypes.splice(index, 1);
    renderList();
    resetForm();
  };

  // Image preview functionality
  console.log("Initializing image preview...");

  const officeInput = document.getElementById("officePhotos");
  const thumbnailInput = document.getElementById("thumbnail");
  const officePreview = document.getElementById("officePhotosPreview");
  const thumbnailPreview = document.getElementById("thumbnailPreview");
  const counter = document.getElementById("photoCounter");

  console.log("Elements found:", {
    officeInput: !!officeInput,
    thumbnailInput: !!thumbnailInput,
    officePreview: !!officePreview,
    thumbnailPreview: !!thumbnailPreview,
  });

  let officeFiles = [];

  function updateCounter() {
    if (counter) {
      counter.textContent = `${officeFiles.length} of 4 photos uploaded`;
    }
  }

  function createImageElement(file, onRemove) {
    console.log("Creating image element for:", file.name);
    const container = document.createElement("div");
    container.className = "preview-image";

    const img = document.createElement("img");
    const imgUrl = URL.createObjectURL(file);
    img.src = imgUrl;
    img.alt = file.name;

    const removeBtn = document.createElement("button");
    removeBtn.className = "remove-btn";
    removeBtn.innerHTML = "×";
    removeBtn.type = "button";

    removeBtn.addEventListener("click", function () {
      URL.revokeObjectURL(img.src);
      container.remove();
      onRemove();
    });

    container.appendChild(img);
    container.appendChild(removeBtn);

    return container;
  }

  function displayOfficePhotos() {
    console.log("Displaying office photos, count:", officeFiles.length);
    if (officePreview) {
      officePreview.innerHTML = "";

      if (officeFiles.length === 0) {
        officePreview.innerHTML =
          '<div class="empty-state">No office photos uploaded yet</div>';
        return;
      }

      officeFiles.forEach((file) => {
        const element = createImageElement(file, function () {
          officeFiles = officeFiles.filter((f) => f !== file);
          displayOfficePhotos();
          updateCounter();
        });
        officePreview.appendChild(element);
      });
    }
  }

  function displayThumbnail(file) {
    console.log("Displaying thumbnail");
    if (thumbnailPreview) {
      thumbnailPreview.innerHTML = "";

      if (!file) {
        thumbnailPreview.innerHTML =
          '<div class="empty-state">No thumbnail uploaded yet</div>';
        return;
      }

      const element = createImageElement(file, function () {
        displayThumbnail(null);
        if (thumbnailInput) {
          thumbnailInput.value = "";
        }
      });
      thumbnailPreview.appendChild(element);
    }
  }

  // Office photos handler
  if (officeInput) {
    officeInput.addEventListener("change", function (e) {
      console.log("Office input changed, files:", e.target.files.length);

      const newFiles = Array.from(e.target.files);

      if (officeFiles.length + newFiles.length > 4) {
        alert(
          `Cannot upload more than 4 photos. You can upload ${
            4 - officeFiles.length
          } more.`
        );
        e.target.value = "";
        return;
      }

      officeFiles.push(...newFiles);
      displayOfficePhotos();
      updateCounter();

      console.log("Photos added, total now:", officeFiles.length);
    });
  }

  // Thumbnail handler
  if (thumbnailInput) {
    thumbnailInput.addEventListener("change", function (e) {
      console.log("Thumbnail input changed");
      const file = e.target.files[0];
      if (file) {
        displayThumbnail(file);
      }
    });
  }

  // Initialize image previews
  displayOfficePhotos();
  displayThumbnail(null);
  updateCounter();

  // === FORM SUBMISSION - FIXED AND DEBUGGED ===
  console.log("Setting up form submission...");

  const form = document.getElementById("multiStepForm");
  const hiddenContainer = document.getElementById("flight-types-hidden-inputs");

  console.log("Form elements found:", {
    form: !!form,
    hiddenContainer: !!hiddenContainer,
  });

  if (form) {
    console.log("Adding submit event listener to form");

    form.addEventListener("submit", function (e) {
      console.log("=== FORM SUBMIT EVENT TRIGGERED ===");
      e.preventDefault();

      // Add flight types as hidden inputs
      if (hiddenContainer) {
        console.log("Adding flight types to hidden container:", flightTypes);
        hiddenContainer.innerHTML = "";

        flightTypes.forEach((flight, index) => {
          console.log(`Adding flight type ${index}:`, flight);

          const hiddenName = document.createElement("input");
          hiddenName.type = "hidden";
          hiddenName.name = "flightTypes[][name]";
          hiddenName.value = flight.name;

          const hiddenPrice = document.createElement("input");
          hiddenPrice.type = "hidden";
          hiddenPrice.name = "flightTypes[][price]";
          hiddenPrice.value = flight.price;

          hiddenContainer.appendChild(hiddenName);
          hiddenContainer.appendChild(hiddenPrice);
        });
      }

      // Create FormData
      console.log("Creating FormData...");
      const formData = new FormData(form);

      // Add managed office photos
      if (officeFiles.length > 0) {
        console.log("Adding office photos to FormData:", officeFiles.length);
        formData.delete("officePhotos[]");

        officeFiles.forEach((file, index) => {
          console.log(`Adding office photo ${index}:`, file.name);
          formData.append("officePhotos[]", file);
        });
      }

      // Debug: Log all form data
      console.log("=== FORM DATA CONTENTS ===");
      for (let [key, value] of formData.entries()) {
        if (value instanceof File) {
          console.log(`${key}: [FILE] ${value.name} (${value.size} bytes)`);
        } else {
          console.log(`${key}: ${value}`);
        }
      }

      // Get form action
      const action = form.getAttribute("action") || "/home";
      console.log("Submitting to:", action);

      // Submit form
      console.log("Sending fetch request...");
      fetch(action, {
        method: "POST",
        body: formData,
      })
        .then((response) => {
          console.log("Response received:", {
            status: response.status,
            statusText: response.statusText,
            ok: response.ok,
          });

          if (!response.ok) {
            throw new Error(
              `HTTP error! status: ${response.status} - ${response.statusText}`
            );
          }
          return response.text();
        })
        .then((data) => {
          console.log("=== SUCCESS ===");
          console.log("Server response:", data);
          alert("Form submitted successfully!");

          // Optional: Reset form or close modal
          form.reset();
          $("#serviceModal").removeClass("show");
        })
        .catch((error) => {
          console.error("=== ERROR ===");
          console.error("Submission failed:", error);
          alert("Submission failed: " + error.message);
        });
    });

    console.log("Form submission setup complete");
  } else {
    console.error("Form element not found!");
  }

  console.log("=== initServiceForm completed ===");
}
