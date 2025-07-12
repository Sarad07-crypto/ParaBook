// review.js - Modified to always load reviews for all users
document.addEventListener("DOMContentLoaded", function () {
  // Get serviceId from the page - it should always be available
  const serviceId =
    window.serviceId || document.getElementById("currentServiceId")?.value;
  const reviewForm = document.getElementById("reviewForm");
  const reviewsList = document.getElementById("reviewsList");
  const successMessage = document.getElementById("successMessage");
  const submitBtn = document.getElementById("submitReviewBtn");

  // ALWAYS load reviews when page loads if serviceId exists
  if (serviceId) {
    loadReviews(serviceId);
  } else {
    console.error("Service ID not found - cannot load reviews");
    if (reviewsList) {
      reviewsList.innerHTML =
        '<div class="error-message">Unable to load reviews - service ID missing</div>';
    }
  }

  // Handle review form submission (only if form exists - i.e., user can review)
  if (reviewForm) {
    reviewForm.addEventListener("submit", function (e) {
      e.preventDefault();
      submitReview();
    });
  }

  // Star rating interaction (only if star rating exists)
  const starRating = document.querySelector(".star-rating");
  if (starRating) {
    const labels = starRating.querySelectorAll("label");

    labels.forEach((label, index) => {
      label.addEventListener("mouseenter", function () {
        highlightStars(5 - index);
      });

      label.addEventListener("mouseleave", function () {
        const checkedStar = starRating.querySelector(
          'input[type="radio"]:checked'
        );
        if (checkedStar) {
          highlightStars(parseInt(checkedStar.value));
        } else {
          highlightStars(0);
        }
      });

      label.addEventListener("click", function () {
        highlightStars(5 - index);
      });
    });

    function highlightStars(rating) {
      labels.forEach((label, index) => {
        if (index < rating) {
          label.classList.add("active");
        } else {
          label.classList.remove("active");
        }
      });
    }
  }

  // Submit review function (only works if user is logged in and can review)
  function submitReview() {
    if (!reviewForm) {
      showMessage("Review form not available", "error");
      return;
    }

    const formData = new FormData(reviewForm);
    const rating = formData.get("rating");
    const reviewText = formData.get("review_text");

    if (!rating || !reviewText.trim()) {
      showMessage("Please fill in all fields", "error");
      return;
    }

    if (reviewText.trim().length < 10) {
      showMessage("Review must be at least 10 characters long", "error");
      return;
    }

    // Disable submit button
    if (submitBtn) {
      submitBtn.disabled = true;
      submitBtn.textContent = "Submitting...";
    }

    // Submit review
    fetch("Web/php/AJAX/reviewSubmit.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        service_id: parseInt(serviceId),
        rating: parseInt(rating),
        review_text: reviewText.trim(),
      }),
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          showMessage(data.message, "success");
          reviewForm.reset();
          // Clear star highlights
          const labels = document.querySelectorAll(".star-rating label");
          labels.forEach((label) => label.classList.remove("active"));
          // Reload reviews to show the new review
          loadReviews(serviceId);
        } else {
          showMessage(data.message, "error");
        }
      })
      .catch((error) => {
        console.error("Error:", error);
        showMessage("Failed to submit review. Please try again.", "error");
      })
      .finally(() => {
        // Re-enable submit button
        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.textContent = "Submit Review";
        }
      });
  }

  // Load reviews function - MODIFIED: Always load reviews for all users
  function loadReviews(serviceId) {
    if (!serviceId) {
      if (reviewsList) {
        reviewsList.innerHTML =
          '<div class="error-message">Service ID not available</div>';
      }
      return;
    }

    if (reviewsList) {
      reviewsList.innerHTML =
        '<div class="loading-reviews">Loading reviews...</div>';
    }

    fetch(`Web/php/AJAX/reviewLoad.php?service_id=${serviceId}`)
      .then((response) => {
        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
      })
      .then((data) => {
        console.log("Reviews loaded:", data); // Debug log
        if (data.success) {
          displayReviews(data.reviews, data.statistics);
        } else {
          console.error("Failed to load reviews:", data.message);
          if (reviewsList) {
            reviewsList.innerHTML = `<div class="error-message">Failed to load reviews: ${data.message}</div>`;
          }
        }
      })
      .catch((error) => {
        console.error("Error loading reviews:", error);
        if (reviewsList) {
          reviewsList.innerHTML =
            '<div class="error-message">Failed to load reviews. Please try again later.</div>';
        }
      });
  }

  // Display reviews function - Shows reviews to all users
  function displayReviews(reviews, statistics) {
    // Debug: Log the reviews data
    console.log("Reviews data received:", reviews);
    console.log("First review avatar data:", reviews[0]?.user_avatar);
    console.log("First review debug data:", reviews[0]?.debug_avatar);

    // Update review stats
    updateReviewStats(statistics);

    // Display reviews
    if (!reviews || reviews.length === 0) {
      if (reviewsList) {
        reviewsList.innerHTML =
          '<div class="no-reviews">No reviews yet. Be the first to review this service!</div>';
      }
      return;
    }

    let reviewsHtml = "";
    reviews.forEach((review, index) => {
      const reviewDate = new Date(review.created_at).toLocaleDateString();
      const isUpdated = review.updated_at !== review.created_at;
      const updateDate = isUpdated
        ? new Date(review.updated_at).toLocaleDateString()
        : null;

      // Debug: Log each avatar URL
      console.log(`Review ${index} avatar URL:`, review.user_avatar);

      reviewsHtml += `
            <div class="review-item">
                <div class="review-header">
                    <div class="reviewer-info">
                        <img src="${review.user_avatar}" alt="${
        review.user_name
      }" 
                             class="reviewer-avatar" 
                             onerror="console.log('Avatar failed to load:', this.src); this.src='Assets/uploads/avatars/default-avatar.png'"
                             onload="console.log('Avatar loaded successfully:', this.src)">
                        <div class="reviewer-details">
                            <h4 class="reviewer-name">${escapeHtml(
                              review.user_name
                            )}</h4>
                            <div class="review-rating">
                                ${generateStars(review.rating)}
                            </div>
                        </div>
                    </div>
                    <div class="review-date">
                        ${reviewDate}
                        ${
                          isUpdated
                            ? `<span class="updated-indicator">(Updated: ${updateDate})</span>`
                            : ""
                        }
                    </div>
                </div>
                <div class="review-content">
                    <p>${escapeHtml(review.review_text)}</p>
               </div>
                ${
                  review.debug_avatar
                    ? ``
                    : //   <div class="debug-info" style="font-size: 12px; color: #666; margin-top: 10px;">
                      //     Original: ${review.debug_avatar.original || "null"}<br>
                      //     Formatted: ${review.debug_avatar.formatted}
                      // </div>
                      ""
                }
            </div>
        `;
    });

    if (reviewsList) {
      reviewsList.innerHTML = reviewsHtml;
    }
  }

  // Update review statistics
  function updateReviewStats(statistics) {
    const reviewCount = document.querySelector(".review-count");
    const overallRating = document.querySelector(".overall-rating");

    if (reviewCount && statistics) {
      const count = statistics.total_reviews || 0;
      reviewCount.textContent = `(${count} review${count !== 1 ? "s" : ""})`;
    }

    if (overallRating && statistics) {
      const avgRating = statistics.average_rating || 0;
      const starsHtml = generateStars(avgRating);
      overallRating.innerHTML = `
        <span class="rating-stars">${starsHtml}</span>
        <span>${avgRating} out of 5</span>
      `;
    }
  }

  // Generate star display
  function generateStars(rating) {
    const fullStars = Math.floor(rating);
    const hasHalfStar = rating % 1 >= 0.5;
    const emptyStars = 5 - fullStars - (hasHalfStar ? 1 : 0);

    let stars = "";

    // Full stars
    for (let i = 0; i < fullStars; i++) {
      stars += '<span class="star filled">★</span>';
    }

    // Half star
    if (hasHalfStar) {
      stars += '<span class="star half">★</span>';
    }

    // Empty stars
    for (let i = 0; i < emptyStars; i++) {
      stars += '<span class="star empty">☆</span>';
    }

    return stars;
  }

  // Show message function
  function showMessage(message, type) {
    // Remove existing messages
    const existingMessages = document.querySelectorAll(".temp-message");
    existingMessages.forEach((msg) => msg.remove());

    // Create new message
    const messageDiv = document.createElement("div");
    messageDiv.className = `temp-message ${type}-message`;
    messageDiv.textContent = message;
    messageDiv.style.cssText = `
      position: fixed;
      top: 20px;
      right: 20px;
      padding: 15px 20px;
      border-radius: 5px;
      color: white;
      font-weight: bold;
      z-index: 1000;
      max-width: 300px;
      word-wrap: break-word;
      background-color: ${type === "success" ? "#4CAF50" : "#f44336"};
    `;

    document.body.appendChild(messageDiv);

    // Remove message after 3 seconds
    setTimeout(() => {
      messageDiv.remove();
    }, 3000);
  }

  // HTML escape function
  function escapeHtml(text) {
    const div = document.createElement("div");
    div.textContent = text;
    return div.innerHTML;
  }

  // Make loadReviews available globally for debugging
  window.loadReviews = loadReviews;
});
