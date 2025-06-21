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
    // Validate current step before proceeding
    if (!validateStep(currentStep)) {
      return; // Stop if validation fails
    }

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

  function validateStep(stepNumber) {
    switch (stepNumber) {
      case 1:
        return validateStep1();
      case 2:
        return validateStep2();
      case 3:
        return validateStep3();
      case 4:
        return validateStep4();
      default:
        return true;
    }
  }

  function validateStep1() {
    const companyName = document
      .querySelector('[name="companyName"]')
      .value.trim();
    const serviceTitle = document
      .querySelector('[name="serviceTitle"]')
      .value.trim();
    const address = document.querySelector('[name="address"]').value.trim();
    const contact = document.querySelector('[name="contact"]').value.trim();
    const panNumber = document.querySelector('[name="panNumber"]').value.trim();

    // Company name validation
    if (!companyName || companyName.length < 2) {
      alert("Company name must be at least 2 characters long");
      return false;
    }

    // Service title validation
    if (!serviceTitle || serviceTitle.length < 3) {
      alert("Service title must be at least 3 characters long");
      return false;
    }

    // Address validation
    if (!address || address.length < 5) {
      alert("Address must be at least 5 characters long");
      return false;
    }

    // Contact validation (10 digits starting with 9)
    const contactRegex = /^9[0-9]{9}$/;
    if (!contact || !contactRegex.test(contact)) {
      alert("Contact number must be 10 digits starting with 9");
      return false;
    }

    // PAN number validation (9 digits for Nepal)
    const panRegex = /^[0-9]{9}$/;
    if (!panNumber || !panRegex.test(panNumber)) {
      alert("PAN number must be exactly 9 digits");
      return false;
    }

    return true;
  }

  function validateStep2() {
    const description = document
      .querySelector('[name="serviceDescription"]')
      .value.trim();

    if (!description || description.length < 20) {
      alert("Service description must be at least 20 characters long");
      return false;
    }

    return true;
  }

  function validateStep3() {
    if (flightTypes.length === 0) {
      alert("Please add at least one flight type with pricing");
      return false;
    }
    return true;
  }

  function validateStep4() {
    if (officeFiles.length === 0) {
      alert("Please upload at least one office photo");
      return false;
    }

    // Check if thumbnail exists (either uploaded or existing)
    const thumbnailFile =
      thumbnailInput && thumbnailInput.files && thumbnailInput.files[0];
    const hasExistingThumbnail =
      window.serviceFormData && window.serviceFormData.currentThumbnail;

    if (!thumbnailFile && !hasExistingThumbnail) {
      alert("Please upload a thumbnail image");
      return false;
    }

    return true;
  }

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
    console.log("Rendering list with flightTypes:", flightTypes);
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

    // Remove required attribute if photos exist
    if (officeInput && officeFiles.length > 0) {
      officeInput.removeAttribute("required");
    } else if (officeInput) {
      officeInput.setAttribute("required", "required");
    }
  }

  function createImageElement(file, onRemove, isExisting = false) {
    console.log("Creating image element for:", isExisting ? file : file.name);
    const container = document.createElement("div");
    container.className = "preview-image";

    const img = document.createElement("img");
    if (isExisting) {
      // For existing photos from database
      img.src = file;
      img.alt = "Office Photo";
    } else {
      // For newly uploaded files
      const imgUrl = URL.createObjectURL(file);
      img.src = imgUrl;
      img.alt = file.name;
    }

    const removeBtn = document.createElement("button");
    removeBtn.className = "remove-btn";
    removeBtn.innerHTML = "×";
    removeBtn.type = "button";

    removeBtn.addEventListener("click", function () {
      if (!isExisting) {
        URL.revokeObjectURL(img.src);
      }
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
        // Add required attribute if no photos
        if (officeInput) {
          officeInput.setAttribute("required", "required");
        }
        return;
      }

      officeFiles.forEach((file) => {
        const isExisting = typeof file === "string";
        const element = createImageElement(
          file,
          function () {
            officeFiles = officeFiles.filter((f) => f !== file);
            displayOfficePhotos();
            updateCounter();
          },
          isExisting
        );
        officePreview.appendChild(element);
      });

      // Remove required attribute if photos exist
      if (officeInput && officeFiles.length > 0) {
        officeInput.removeAttribute("required");
      }
    }
  }

  function displayThumbnail(file) {
    console.log("Displaying thumbnail");
    if (thumbnailPreview) {
      thumbnailPreview.innerHTML = "";

      if (!file) {
        thumbnailPreview.innerHTML =
          '<div class="empty-state">No thumbnail uploaded yet</div>';
        // Add required attribute if no thumbnail
        if (thumbnailInput) {
          thumbnailInput.setAttribute("required", "required");
        }
        return;
      }

      const isExisting = typeof file === "string";
      const element = createImageElement(
        file,
        function () {
          displayThumbnail(null);
          if (thumbnailInput) {
            thumbnailInput.value = "";
          }
        },
        isExisting
      );
      thumbnailPreview.appendChild(element);

      // Remove required attribute if thumbnail exists
      if (thumbnailInput) {
        thumbnailInput.removeAttribute("required");
      }
    }
  }

  // Office photos handler
  if (officeInput) {
    officeInput.addEventListener("change", function (e) {
      console.log("Office input changed, files:", e.target.files.length);

      const newFiles = Array.from(e.target.files);
      const currentFileCount = officeFiles.filter(
        (f) => f instanceof File
      ).length;

      if (currentFileCount + newFiles.length > 4) {
        alert(
          `Cannot upload more than 4 photos. You can upload ${
            4 - currentFileCount
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

  // Real-time PAN validation
  const panInput = document.querySelector('[name="panNumber"]');
  if (panInput) {
    panInput.addEventListener("input", function (e) {
      e.target.value = e.target.value.replace(/[^0-9]/g, "");
    });
  }

  // Real-time contact validation
  const contactInput = document.querySelector('[name="contact"]');
  if (contactInput) {
    contactInput.addEventListener("input", function (e) {
      e.target.value = e.target.value.replace(/[^0-9]/g, "");
    });
  }

  // Initialize image previews
  displayOfficePhotos();
  displayThumbnail(null);
  updateCounter();

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
          hiddenName.name = `flightTypes[${index}][name]`;
          hiddenName.value = flight.name;

          const hiddenPrice = document.createElement("input");
          hiddenPrice.type = "hidden";
          hiddenPrice.name = `flightTypes[${index}][price]`;
          hiddenPrice.value = String(flight.price);

          hiddenContainer.appendChild(hiddenName);
          hiddenContainer.appendChild(hiddenPrice);
        });
      }

      // Create FormData
      console.log("Creating FormData...");
      const formData = new FormData();

      // Add all form fields manually (except file inputs)
      const formElements = form.elements;
      for (let i = 0; i < formElements.length; i++) {
        const element = formElements[i];

        // Skip file inputs and buttons
        if (
          element.type === "file" ||
          element.type === "button" ||
          element.type === "submit"
        ) {
          continue;
        }

        // Skip empty values for non-required fields
        if (element.name && element.value !== "") {
          formData.append(element.name, element.value);
          console.log(`Added field: ${element.name} = ${element.value}`);
        }
      }

      // Handle office photos
      const newOfficeFiles = officeFiles.filter((f) => f instanceof File);
      const existingOfficePhotos = officeFiles.filter(
        (f) => typeof f === "string"
      );

      console.log(
        "Office photos - New files:",
        newOfficeFiles.length,
        "Existing:",
        existingOfficePhotos.length
      );

      if (newOfficeFiles.length > 0) {
        newOfficeFiles.forEach((file, index) => {
          console.log(`Adding new office photo ${index}:`, file.name);
          formData.append("officePhotos[]", file);
        });
      }

      // Add existing office photos as a separate field so server knows which ones to keep
      if (existingOfficePhotos.length > 0) {
        existingOfficePhotos.forEach((photoPath, index) => {
          console.log(`Keeping existing office photo ${index}:`, photoPath);
          formData.append("existingOfficePhotos[]", photoPath);
        });
      }

      // Handle thumbnail
      const thumbnailFile =
        thumbnailInput && thumbnailInput.files && thumbnailInput.files[0];
      if (thumbnailFile) {
        console.log("Adding new thumbnail file:", thumbnailFile.name);
        formData.append("thumbnail", thumbnailFile);
      } else if (
        window.serviceFormData &&
        window.serviceFormData.currentThumbnail
      ) {
        // Send existing thumbnail path
        console.log(
          "Keeping existing thumbnail:",
          window.serviceFormData.currentThumbnail
        );
        formData.append(
          "existingThumbnail",
          window.serviceFormData.currentThumbnail
        );
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
      const action = form.getAttribute("action");
      console.log("Submitting to:", action);

      // Validate required data
      if (flightTypes.length === 0) {
        alert("Please add at least one flight type.");
        return;
      }

      if (officeFiles.length === 0) {
        alert("Please upload at least one office photo.");
        return;
      }

      // Send data via jQuery AJAX with better error handling
      console.log("Sending jQuery AJAX request...");
      $.ajax({
        url: action,
        method: "POST",
        data: formData,
        processData: false,
        contentType: false,
        dataType: "text",
        timeout: 30000, // 30 second timeout
        beforeSend: function () {
          // Disable submit button to prevent double submission
          const submitBtn = document.getElementById("submitBtn");
          if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.textContent = "Submitting...";
          }
        },
        success: function (response) {
          console.log("Response received:", response);

          // Try to parse response as JSON to check for success/error
          try {
            const jsonResponse = JSON.parse(response);
            if (jsonResponse.success === false) {
              console.error("Server returned error:", jsonResponse.message);
              alert("Error: " + jsonResponse.message);
              return;
            }
          } catch (e) {
            // Response is not JSON, that's fine
            console.log("Response is not JSON, treating as success");
          }

          // Reset form and close modal on success
          form.reset();
          flightTypes.length = 0;
          officeFiles.length = 0;
          renderList();
          displayOfficePhotos();
          displayThumbnail(null);
          updateCounter();

          $("#serviceModal").removeClass("show");
          $(document).trigger("serviceAdded");

          alert("Service saved successfully!");
        },
        error: function (xhr, status, error) {
          console.error("=== AJAX ERROR ===");
          console.error("Status:", status);
          console.error("Error:", error);
          console.error("Status Code:", xhr.status);
          console.error("Response Text:", xhr.responseText);

          let errorMessage = "Submission failed";

          if (xhr.status === 0) {
            errorMessage = "Network error - please check your connection";
          } else if (xhr.status === 413) {
            errorMessage = "Files too large - please reduce image sizes";
          } else if (xhr.status === 500) {
            errorMessage = "Server error - please try again";
          } else if (xhr.responseText) {
            // Try to extract meaningful error from response
            try {
              const jsonError = JSON.parse(xhr.responseText);
              errorMessage =
                jsonError.message || jsonError.error || errorMessage;
            } catch (e) {
              // If response is not JSON, show first 200 chars
              errorMessage = xhr.responseText.substring(0, 200);
            }
          }

          alert("Error: " + errorMessage);
        },
        complete: function () {
          // Re-enable submit button
          const submitBtn = document.getElementById("submitBtn");
          if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.textContent = "Submit";
          }
        },
      });
    });

    console.log("Form submission setup complete");
  } else {
    console.error("Form element not found!");
  }

  // Expose functions and variables that need to be accessed by loadServiceData
  window.serviceFormData = {
    flightTypes: flightTypes,
    renderList: renderList,
    officeFiles: officeFiles,
    displayOfficePhotos: displayOfficePhotos,
    displayThumbnail: displayThumbnail,
    updateCounter: updateCounter,
    currentThumbnail: null, // Track current thumbnail
  };
}

function loadServiceData(serviceId) {
  if (!serviceId) return;

  $.ajax({
    url: "Web/php/AJAX/getServiceData.php",
    method: "GET",
    data: { service_id: serviceId },
    dataType: "json",
    success: function (res) {
      console.log("Service data loaded:", res);

      if (!res.success) {
        alert("Failed to load service");
        return;
      }

      const { service, flightTypes, officePhotos } = res.data;

      // Fill text fields
      $('[name="companyName"]').val(service.company_name);
      $('[name="serviceTitle"]').val(service.service_title);
      $('[name="address"]').val(service.address);
      $('[name="contact"]').val(service.contact);
      $('[name="panNumber"]').val(service.pan_number);
      $('[name="serviceDescription"]').val(service.service_description);

      // Add or update hidden service_id field
      let serviceIdInput = $('[name="service_id"]');
      if (serviceIdInput.length === 0) {
        $("#multiStepForm").append(
          `<input type="hidden" name="service_id" value="${service.id}">`
        );
      } else {
        serviceIdInput.val(service.id);
      }

      // Load flight types - FIXED
      if (window.serviceFormData && window.serviceFormData.flightTypes) {
        console.log("Loading flight types:", flightTypes);

        // Clear existing flight types
        window.serviceFormData.flightTypes.length = 0;

        // Add flight types from database
        flightTypes.forEach((ft) => {
          window.serviceFormData.flightTypes.push({
            name: ft.name,
            price: ft.price,
          });
        });

        // Re-render the flight types list
        window.serviceFormData.renderList();
        console.log("Flight types loaded and rendered");
      } else {
        console.error(
          "serviceFormData not available - make sure initServiceForm() was called first"
        );
      }

      // Load office photos - FIXED
      if (window.serviceFormData && window.serviceFormData.officeFiles) {
        console.log("Loading office photos:", officePhotos);

        // Clear existing office files
        window.serviceFormData.officeFiles.length = 0;

        // Add existing photos as strings (URLs)
        officePhotos.forEach((photoPath) => {
          window.serviceFormData.officeFiles.push(photoPath);
        });

        // Re-display office photos
        window.serviceFormData.displayOfficePhotos();
        window.serviceFormData.updateCounter();
        console.log("Office photos loaded and displayed");
      }

      // Load thumbnail - FIXED
      if (service.thumbnail_path && window.serviceFormData) {
        console.log("Loading thumbnail:", service.thumbnail_path);
        window.serviceFormData.currentThumbnail = service.thumbnail_path;
        window.serviceFormData.displayThumbnail(service.thumbnail_path);
      }
    },
    error: function (xhr, status, error) {
      console.error("AJAX error:", error);
      console.log("Status:", status);
      console.log("Response text:", xhr.responseText);
      alert("Error loading service data: " + error);
    },
  });
}
