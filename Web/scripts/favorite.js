// Function to toggle favorite status
async function toggleFavorite(event, serviceId) {
  event.preventDefault();
  event.stopPropagation();

  const heartIcon = event.target
    .closest(".service-heart-icon")
    .querySelector("i");
  const isFavorite = heartIcon.classList.contains("fas");

  try {
    const formData = new FormData();
    formData.append("service_id", serviceId);
    formData.append("action", isFavorite ? "remove" : "add");

    const response = await fetch("Web/php/AJAX/favoritesAPI.php", {
      method: "POST",
      body: formData,
    });

    const data = await response.json();

    if (data.success) {
      // Toggle heart icon
      if (isFavorite) {
        heartIcon.classList.remove("fas");
        heartIcon.classList.add("far");
        showNotification("Removed from favorites", "success");
      } else {
        heartIcon.classList.remove("far");
        heartIcon.classList.add("fas");
        showNotification("Added to favorites", "success");
      }

      // Update favorites count in header
      updateFavoritesCount();
    } else {
      showNotification(data.message || "Error updating favorites", "error");
    }
  } catch (error) {
    console.error("Error toggling favorite:", error);
    showNotification("Error updating favorites", "error");
  }
}

// Function to update favorites count in header
async function updateFavoritesCount() {
  try {
    const response = await fetch("Web/php/AJAX/favoritesAPI.php?action=count");
    const data = await response.json();

    if (data.success) {
      const badge = document.getElementById("heart-badge");
      const favoriteCount = data.count || 0;

      if (badge) {
        badge.textContent = favoriteCount > 99 ? "99+" : favoriteCount;
        if (favoriteCount > 0) {
          badge.classList.add("show");
        } else {
          badge.classList.remove("show");
        }
      }
    }
  } catch (error) {
    console.error("Error updating favorites count:", error);
  }
}

// Function to check favorite status for all services
async function checkFavoriteStatus() {
  const heartIcons = document.querySelectorAll(
    ".service-heart-icon i[data-service-id]"
  );

  for (const icon of heartIcons) {
    const serviceId = icon.getAttribute("data-service-id");

    try {
      const response = await fetch(
        `Web/php/AJAX/favoritesAPI.php?action=check&service_id=${serviceId}`
      );
      const data = await response.json();

      if (data.success && data.is_favorite) {
        icon.classList.remove("far");
        icon.classList.add("fas");
      }
    } catch (error) {
      console.error("Error checking favorite status:", error);
    }
  }
}

// Function to show notification
function showNotification(message, type = "info") {
  // Create notification element
  const notification = document.createElement("div");
  notification.className = `notification ${type}`;
  notification.textContent = message;

  document.body.appendChild(notification);

  // Fade in
  setTimeout(() => {
    notification.style.opacity = "1";
  }, 100);

  // Remove after 3 seconds
  setTimeout(() => {
    notification.style.opacity = "0";
    setTimeout(() => {
      document.body.removeChild(notification);
    }, 300);
  }, 3000);
}

// Load favorites list
async function loadFavoritesList() {
  const heartList = document.getElementById("heart-list");
  const clearBtn = document.getElementById("heart-clear-btn");

  // Show loading
  heartList.innerHTML = `
            <div class="loading-heart">
                <i class="fas fa-spinner fa-spin"></i>
                <p>Loading favorites...</p>
            </div>
        `;

  try {
    const response = await fetch("Web/php/AJAX/favoritesAPI.php?action=list");
    const data = await response.json();

    if (data.success) {
      if (data.favorites.length === 0) {
        heartList.innerHTML = `
                        <div class="empty-favorites">
                            <i class="far fa-heart"></i>
                            <p>No favorites yet</p>
                            <small>Click the heart icon on services to add them here</small>
                        </div>
                    `;
        clearBtn.style.display = "none";
      } else {
        heartList.innerHTML = data.favorites
          .map((favorite) => {
            return `
                        <div class="favorite-notification" data-service-id="${favorite.id}" onclick="goToServiceDescription(${favorite.id})" style="cursor:pointer;">
                            <p><strong>${favorite.company_name}</strong> has been added to favorites.</p>
                            <button class="remove-favorite" onclick="event.stopPropagation(); removeFavorite(${favorite.id});" title="Remove from favorites">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    `;
          })
          .join("");

        clearBtn.style.display = "block";
      }
    }
  } catch (error) {
    console.error("Error loading favorites:", error);
    heartList.innerHTML = `
                <div class="empty-favorites">
                    <i class="fas fa-exclamation-triangle"></i>
                    <p>Error loading favorites</p>
                    <small>Please try again</small>
                </div>
            `;
  }
}

function goToServiceDescription(serviceId) {
  window.location.href =
    "/serviceDescription?service_id=" + encodeURIComponent(serviceId);
}

// Remove single favorite
async function removeFavorite(serviceId) {
  try {
    const formData = new FormData();
    formData.append("service_id", serviceId);
    formData.append("action", "remove");

    const response = await fetch("Web/php/AJAX/favoritesAPI.php", {
      method: "POST",
      body: formData,
    });

    const data = await response.json();

    if (data.success) {
      // Remove from dropdown
      const favoriteItem = document.querySelector(
        `.favorite-item[data-service-id="${serviceId}"]`
      );
      if (favoriteItem) {
        favoriteItem.remove();
      }

      // Update heart icon in service card
      const serviceHeartIcon = document.querySelector(
        `.service-heart-icon i[data-service-id="${serviceId}"]`
      );
      if (serviceHeartIcon) {
        serviceHeartIcon.classList.remove("fas");
        serviceHeartIcon.classList.add("far");
      }

      // Update count
      updateFavoritesCount();

      // Reload list if empty
      const remainingItems = document.querySelectorAll(".favorite-item");
      if (remainingItems.length === 0) {
        loadFavoritesList();
      }

      showNotification("Removed from favorites", "success");
    } else {
      showNotification(data.message || "Error removing favorite", "error");
    }
  } catch (error) {
    console.error("Error removing favorite:", error);
    showNotification("Error removing favorite", "error");
  }
}

// Clear all favorites
async function clearAllFavorites() {
  if (!confirm("Are you sure you want to remove all favorites?")) {
    return;
  }

  try {
    const formData = new FormData();
    formData.append("action", "clear_all");

    const response = await fetch("Web/php/AJAX/favoritesAPI.php", {
      method: "POST",
      body: formData,
    });

    const data = await response.json();

    if (data.success) {
      // Update all heart icons
      const heartIcons = document.querySelectorAll(".service-heart-icon i.fas");
      heartIcons.forEach((icon) => {
        icon.classList.remove("fas");
        icon.classList.add("far");
      });

      // Update count
      updateFavoritesCount();

      // Reload list
      loadFavoritesList();

      showNotification("All favorites cleared", "success");
    } else {
      showNotification(data.message || "Error clearing favorites", "error");
    }
  } catch (error) {
    console.error("Error clearing favorites:", error);
    showNotification("Error clearing favorites", "error");
  }
}

// Refresh favorites
function refreshHeartNotifications() {
  loadFavoritesList();
  updateFavoritesCount();
}

// Function to load and display company services
function loadCompanyServices() {
  console.log("loadCompanyServices called");

  const companyGrid = $(".company-grid");
  console.log("Found companyGrid element:", companyGrid.length);

  $.ajax({
    url: "Web/php/AJAX/fetchCompanyServices.php",
    type: "GET",
    dataType: "json",
    success: function (response) {
      console.log("AJAX success. Response received:", response);

      if (response.success && response.services.length > 0) {
        $(".company-results").text(
          `${response.count} result${response.count !== 1 ? "s" : ""}`
        );
        const companyGrid = $(".company-grid");
        console.log("Found companyGrid element:", companyGrid.length);

        // Clear existing cards
        companyGrid.find(".company-card").remove();
        console.log("Cleared existing service cards");

        // Add each service card
        response.services.forEach((service, index) => {
          console.log(`Processing service #${index}:`, service);

          // Get the lowest price from flight types
          let lowestPrice = null;
          if (service.flight_types && service.flight_types.length > 0) {
            const prices = service.flight_types.map((ft) => {
              const priceNum = parseFloat(ft.price.replace(/[^\d.]/g, ""));
              console.log(
                `Flight type price parsed: ${ft.price} -> ${priceNum}`
              );
              return priceNum;
            });
            lowestPrice = Math.min(...prices);
            console.log("Lowest price for this service:", lowestPrice);
          } else {
            console.log(
              "No flight types or empty array for service:",
              service.id
            );
          }

          const serviceCard = `
                            <div class="company-card" data-service-id="${
                              service.id
                            }">
                                <div class="company-thumbnail">
                                    <img src="${service.thumbnail_path}" 
                                        alt="${service.service_title}"
                                        onerror="console.error('Image failed to load:', '${
                                          service.thumbnail_path
                                        }'); this.parentElement.innerHTML='<div style=\\'padding:50px;text-align:center;color:#999;background:#f5f5f5\\'>Image not available</div>';"
                                        onload="console.log('Image loaded successfully for ${
                                          service.company_name
                                        }');">
                                    <!-- Heart icon for favorites -->
                                    <div class="service-heart-icon" onclick="toggleFavorite(event, ${
                                      service.id
                                    })">
                                        <i class="far fa-heart" data-service-id="${
                                          service.id
                                        }"></i>
                                    </div>
                                </div>
                                <div class="company-info">
                                    <div class="company-title"><b>${
                                      service.company_name || "Unknown Company"
                                    }</b></div>
                                    <div class="company-desc">${
                                      service.service_description ||
                                      "No description available"
                                    }</div>
                                    <div class="company-meta">
                                        <span class="company-rating">
                                            <i class="fas fa-star"></i> 5.0 
                                            <span class="company-reviews">(0)</span>
                                        </span>
                                        ${
                                          lowestPrice
                                            ? `<span class="company-price">Rs. ${lowestPrice.toLocaleString()}</span>`
                                            : '<span class="company-price">Price not available</span>'
                                        }
                                    </div>
                                </div>
                            </div>
                        `;

          companyGrid.append(serviceCard);
          console.log("Inserted service card for service ID:", service.id);

          const addedCard = companyGrid.find(
            `[data-service-id="${service.id}"]`
          );
          console.log("Added card element:", addedCard[0]);
          console.log("Card is visible:", addedCard.is(":visible"));
        });

        // After loading all cards, check their favorite status
        setTimeout(() => {
          checkFavoriteStatus();
        }, 100);
      } else {
        console.log("No services found for this company or empty array");
      }
    },
    error: function (xhr, status, error) {
      console.error("AJAX error loading services:", error);
      console.log("Status:", status);
      console.log("Response text:", xhr.responseText);
    },
  });
}

// Call this when the page loads
$(document).ready(function () {
  console.log("Document ready, calling loadCompanyServices");
  loadCompanyServices();

  // Also load initial favorites count
  updateFavoritesCount();
});
