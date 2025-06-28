<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link rel="stylesheet" href="/Web/css/views.css" />
    <link rel="stylesheet" href="/Web/css/addServices.css?v=1.0" />

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="/Web/scripts/views.js"></script>
</head>

<body>

    <!-- Main content -->
    <div class="company-section">
        <div class="company-header">
            <div>
                <h1>Companies you are looking for</h1>
                <div class="company-results">Loading...</div>
            </div>
            <div class="company-sort">
                <label style="margin-left: 24px;">
                    Sort by:
                    <select class="sort-select">
                        <option value="rating">Rating</option>
                        <option value="price">Price</option>
                        <option value="reviews">Reviews</option>
                        <option value="name">Name</option>
                    </select>
                </label>
            </div>
        </div>
    </div>
    <div class="company-grid">

    </div>
    <div class="pagination" id="pagination"></div>

    <script>
    // Function to load and display company services
    function loadCompanyServices() {
        console.log("loadCompanyServices called");

        const companyGrid = $(".company-grid");
        console.log("Found companyGrid element:", companyGrid.length);
        // console.log("CompanyGrid HTML:", companyGrid.html());

        $.ajax({
            url: "Web/php/AJAX/fetchCompanyServices.php",
            type: "GET",
            dataType: "json",
            success: function(response) {
                console.log("AJAX success. Response received:", response);

                if (response.success && response.services.length > 0) {
                    $(".company-results").text(
                        `${response.count} result${response.count !== 1 ? 's' : ''}`);
                    const companyGrid = $(".company-grid");
                    console.log("Found companyGrid element:", companyGrid.length);

                    // Clear existing cards except the "Add Service" button
                    // console.log("Before removing cards:", companyGrid.html());
                    companyGrid.find(".company-card").remove();
                    // console.log("After removing cards:", companyGrid.html());
                    console.log("Cleared existing service cards");

                    // Add each service card
                    response.services.forEach((service, index) => {
                        console.log(`Processing service #${index}:`, service);

                        // Get the lowest price from flight types
                        let lowestPrice = null;
                        if (service.flight_types && service.flight_types.length > 0) {
                            const prices = service.flight_types.map((ft) => {
                                const priceNum = parseFloat(ft.price.replace(/[^\d.]/g,
                                    ""));
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
                } else {
                    console.log("No services found for this company or empty array");
                }
            },
            error: function(xhr, status, error) {
                console.error("AJAX error loading services:", error);
                console.log("Status:", status);
                console.log("Response text:", xhr.responseText);
            },
        });
    }

    // Call this when the page loads
    $(document).ready(function() {
        console.log("Document ready, calling loadCompanyServices");
        loadCompanyServices();
    });
    </script>
</body>

</html>