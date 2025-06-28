// password show and hide
const showPWDIcons = document.querySelectorAll(".eye-icon");

showPWDIcons.forEach((icon) => {
  icon.addEventListener("click", () => {
    const currPWD = icon.parentElement.parentElement.querySelectorAll(".pwd");

    currPWD.forEach((pwd) => {
      if (pwd.type === "password") {
        pwd.type = "text";
        icon.classList.replace("bx-hide", "bx-show");
      } else {
        pwd.type = "password";
        icon.classList.replace("bx-show", "bx-hide");
      }
    });
  });
});

// form validation

const validateForm = (formSelector) => {
  const formElement = document.querySelector(formSelector);

  // Check if form exists
  if (!formElement) {
    console.log(`Form with selector "${formSelector}" not found`);
    return;
  }

  const validateOptions = [
    {
      attribute: "required",
      isValid: (input) => input.value.trim() === "",
    },
    {
      attribute: "text-only",
      isValid: (input) => !/^[A-Za-z\s]{2,}$/.test(input.value), // Allow spaces for names
    },
    {
      attribute: "pattern",
      isValid: (input) => {
        const regex = new RegExp(input.pattern);
        return !regex.test(input.value);
      },
    },
    {
      attribute: "match",
      isValid: (input) => {
        const matchSelector = input.getAttribute("match");
        const matchedElement = formElement.querySelector(
          `[name="${matchSelector}"]`
        );
        return (
          matchedElement && matchedElement.value.trim() !== input.value.trim()
        );
      },
      errorMessage: "Passwords do not match",
    },
    {
      attribute: "date-validation",
      isValid: (input) => {
        const enteredDate = new Date(input.value);
        const today = new Date();

        if (isNaN(enteredDate.getTime()) || enteredDate > today) return true;

        const ageLimit = 3;
        const ageDate = new Date(
          today.getFullYear() - ageLimit,
          today.getMonth(),
          today.getDate()
        );

        return enteredDate > ageDate;
      },
    },
    {
      attribute: "radio-group",
      isValid: (input) => {
        const name = input.name;
        const checked = document.querySelector(`input[name="${name}"]:checked`);
        return !checked;
      },
      errorMessage: "Please select an option",
    },
  ];

  const validateRadioGroup = (name) => {
    const group = document.querySelectorAll(`input[name="${name}"]`);
    if (group.length === 0) return true; // If no radio group exists, consider it valid

    const selected = [...group].some((input) => input.checked);

    if (!selected) {
      const container = group[0].closest(
        ".usertype-wrapper, .gender-wrapper, .radio-group"
      );
      if (container) {
        container.style.borderBottom = "1px solid red";
      }
      return false;
    } else {
      const container = group[0].closest(
        ".usertype-wrapper, .gender-wrapper, .radio-group"
      );
      if (container) {
        container.style.border = "none";
      }
      return true;
    }
  };

  const toggleRequirementClass = (element, isValid) => {
    if (element) {
      element.classList.toggle("pwd-rqm-li-show-valid", isValid);
      element.classList.toggle("pwd-rqm-li-show", !isValid);
    }
  };

  const validatePasswordRequirements = (password) => {
    const requirements = document.querySelectorAll(".pwd-rqm-li");

    if (requirements.length === 0) {
      // No password requirements UI, just do basic validation
      return password.length >= 8;
    }

    const hasMinLength = password.length >= 8;
    const hasSpecialChar = /[^A-Za-z0-9]/.test(password);
    const hasNumber = /\d/.test(password);
    const hasMixedCase = /[A-Z]/.test(password) && /[a-z]/.test(password);

    const allValid =
      hasMinLength && hasSpecialChar && hasNumber && hasMixedCase;

    requirements.forEach((li) => {
      li.style.display = allValid ? "none" : "block";
    });

    if (requirements[0]) toggleRequirementClass(requirements[0], hasMinLength);
    if (requirements[1])
      toggleRequirementClass(requirements[1], hasSpecialChar);
    if (requirements[2]) toggleRequirementClass(requirements[2], hasNumber);
    if (requirements[3]) toggleRequirementClass(requirements[3], hasMixedCase);

    return allValid;
  };

  const validateSingleFormInput = (formInput) => {
    const input = formInput.querySelector("input");
    if (!input) return true;

    const error = formInput.querySelector(".error-icon");
    const success = formInput.querySelector(".check-icon");

    let formInputError = false;
    let currentErrorMessage = "";

    for (const option of validateOptions) {
      if (input.hasAttribute(option.attribute)) {
        if (option.attribute === "match") {
          if (
            input.value &&
            document.querySelector(`[name="${input.getAttribute("match")}"]`)
              ?.value
          ) {
            if (option.isValid(input)) {
              formInputError = true;
              currentErrorMessage = option.errorMessage;
              break;
            }
          }
        } else if (option.isValid(input)) {
          formInputError = true;
          currentErrorMessage = option.errorMessage;
          break;
        }
      }
    }

    if (input.name === "password") {
      const passwordValid = validatePasswordRequirements(input.value);
      if (passwordValid) {
        formInputError = false;
        if (error) error.classList.remove("error-icon-show");
        if (success) success.classList.add("check-icon-show");
        input.style.borderBottom = "1px solid #0659e7";
      }
    }

    if (formInputError) {
      if (error) error.classList.add("error-icon-show");
      if (success) success.classList.remove("check-icon-show");
      input.style.borderBottom = "1px solid red";

      if (input.name === "password") {
        validatePasswordRequirements(input.value);
      }
    } else {
      if (error) error.classList.remove("error-icon-show");
      if (success) success.classList.add("check-icon-show");
      input.style.borderBottom = "1px solid #0659e7";

      if (input.name === "password") {
        validatePasswordRequirements(input.value);
      }
    }

    return !formInputError;
  };

  const setupInputEvents = () => {
    const formInputs = Array.from(formElement.querySelectorAll(".input-box"));

    formInputs.forEach((formInput) => {
      const input = formInput.querySelector("input");
      if (!input) return;

      input.addEventListener("input", () => {
        const isConfirmPassword = input.hasAttribute("match");
        validateSingleFormInput(
          formInput,
          isConfirmPassword && document.activeElement === input
        );

        if (input.name === "password") {
          const confirmPasswordInput = formElement.querySelector("[match]");
          if (confirmPasswordInput) {
            const confirmFormInput = confirmPasswordInput.closest(".input-box");
            if (confirmFormInput) {
              validateSingleFormInput(confirmFormInput);
            }
          }
        }
      });

      input.addEventListener("focus", () => {
        if (input.name === "password") {
          const requirements = document.querySelectorAll(".pwd-rqm-li");
          if (input.value.length !== 8) {
            requirements.forEach((li) => (li.style.display = "block"));
          }
        }
        validateSingleFormInput(formInput, input.hasAttribute("match"));
      });

      input.addEventListener("blur", () => {
        validateSingleFormInput(formInput, false);
      });
    });
  };

  formElement.setAttribute("novalidate", "");
  formElement.addEventListener("submit", (event) => {
    event.preventDefault();

    const formInputs = Array.from(formElement.querySelectorAll(".input-box"));
    const isValid = formInputs.every((formInput) =>
      validateSingleFormInput(formInput)
    );
    const isUserTypeValid = validateRadioGroup("userType");
    const isGenderValid = validateRadioGroup("gender");

    if (isValid && isUserTypeValid && isGenderValid) {
      console.log("Form is valid, submitting...");

      // Check if this is a profile form with AJAX submission
      if (
        formElement.id === "profileForm" &&
        typeof submitProfileData === "function"
      ) {
        submitProfileData(); // Call the AJAX function
      } else {
        formElement.submit(); // Regular form submission
      }
    } else {
      console.log("Form has errors.");
    }
  });

  setupInputEvents();
};

// Initialize validation for different forms
document.addEventListener("DOMContentLoaded", function () {
  // Try to validate different possible form selectors
  const possibleForms = [".form", "#profileForm", "form"];

  possibleForms.forEach((selector) => {
    const form = document.querySelector(selector);
    if (form) {
      console.log(`Initializing validation for form: ${selector}`);
      validateForm(selector);
    }
  });
});
