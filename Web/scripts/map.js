// Fixed Map Integration Code
let map;
let marker;
let selectedLat = null;
let selectedLng = null;
let mapInitialized = false;

// Initialize the map when step 3 becomes visible
function initializeLocationMap() {
  if (mapInitialized) return; // Already initialized

  console.log("Initializing map...");

  // Check if Leaflet is loaded
  if (typeof L === "undefined") {
    console.error("Leaflet library not loaded");
    return;
  }

  // Set default location to a broader view (removed Kathmandu default)
  const defaultLat = 28.0;
  const defaultLng = 84.0;

  try {
    // Initialize map with proper container check
    const mapContainer = document.getElementById("locationMap");
    if (!mapContainer) {
      console.error("Map container not found");
      return;
    }

    // Clear any existing map instance
    if (map) {
      map.remove();
    }

    // Initialize map with broader zoom (removed specific location focus)
    map = L.map("locationMap", {
      center: [defaultLat, defaultLng],
      zoom: 7, // Broader zoom level
      zoomControl: true,
      attributionControl: true,
    });

    // Add OpenStreetMap tiles
    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
      attribution: "© OpenStreetMap contributors",
      maxZoom: 19,
    }).addTo(map);

    // Add click event to map
    map.on("click", function (e) {
      setLocation(e.latlng.lat, e.latlng.lng);
    });

    mapInitialized = true;
    console.log("Map initialized successfully");

    // Try to get user's current location
    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(
        function (position) {
          const lat = position.coords.latitude;
          const lng = position.coords.longitude;
          map.setView([lat, lng], 15);
          setLocation(lat, lng);
        },
        function (error) {
          console.log("Geolocation error:", error);
          // Keep default broader view
        }
      );
    }
  } catch (error) {
    console.error("Error initializing map:", error);
  }
}

// Set location on map and update form
function setLocation(lat, lng) {
  selectedLat = lat;
  selectedLng = lng;

  // Remove existing marker
  if (marker) {
    map.removeLayer(marker);
  }

  // Add new marker
  marker = L.marker([lat, lng]).addTo(map);

  // Update coordinate display
  document.getElementById("selectedLat").textContent = lat.toFixed(6);
  document.getElementById("selectedLng").textContent = lng.toFixed(6);

  // Update hidden form inputs
  document.getElementById("latitude").value = lat;
  document.getElementById("longitude").value = lng;

  // Reverse geocoding to get address
  reverseGeocode(lat, lng);
}

// Reverse geocoding using Nominatim API
function reverseGeocode(lat, lng) {
  const url = `https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lng}&format=json&addressdetails=1`;

  fetch(url)
    .then((response) => response.json())
    .then((data) => {
      if (data && data.display_name) {
        document.getElementById("selectedAddress").textContent =
          data.display_name;

        // Store formatted address in a hidden input if needed
        let formattedAddressInput =
          document.getElementById("formatted_address");
        if (!formattedAddressInput) {
          formattedAddressInput = document.createElement("input");
          formattedAddressInput.type = "hidden";
          formattedAddressInput.id = "formatted_address";
          formattedAddressInput.name = "formatted_address";
          document
            .getElementById("multiStepForm")
            .appendChild(formattedAddressInput);
        }

        // Store place ID in a hidden input
        let placeIdInput = document.getElementById("place_id");
        if (!placeIdInput) {
          placeIdInput = document.createElement("input");
          placeIdInput.type = "hidden";
          placeIdInput.id = "place_id";
          placeIdInput.name = "place_id";
          document.getElementById("multiStepForm").appendChild(placeIdInput);
        }

        document.getElementById("formatted_address").value = data.display_name;
        document.getElementById("place_id").value = data.place_id || "";
      } else {
        document.getElementById("selectedAddress").textContent =
          "Address not found";
      }
    })
    .catch((error) => {
      console.error("Reverse geocoding error:", error);
      document.getElementById("selectedAddress").textContent =
        "Error getting address";
    });
}

// Search for location
function searchLocation() {
  const searchTerm = document.getElementById("locationSearch").value.trim();
  if (!searchTerm) {
    alert("Please enter a location to search");
    return;
  }

  // Add Nepal to search to get more relevant results
  const searchQuery = searchTerm.includes("Nepal")
    ? searchTerm
    : `${searchTerm}, Nepal`;

  const url = `https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(
    searchQuery
  )}&format=json&limit=5&addressdetails=1`;

  fetch(url)
    .then((response) => response.json())
    .then((data) => {
      if (data && data.length > 0) {
        const result = data[0];
        const lat = parseFloat(result.lat);
        const lng = parseFloat(result.lon);

        // Move map to searched location
        map.setView([lat, lng], 15);
        setLocation(lat, lng);

        // Store additional search result data
        let formattedAddressInput =
          document.getElementById("formatted_address");
        if (!formattedAddressInput) {
          formattedAddressInput = document.createElement("input");
          formattedAddressInput.type = "hidden";
          formattedAddressInput.id = "formatted_address";
          formattedAddressInput.name = "formatted_address";
          document
            .getElementById("multiStepForm")
            .appendChild(formattedAddressInput);
        }

        let placeIdInput = document.getElementById("place_id");
        if (!placeIdInput) {
          placeIdInput = document.createElement("input");
          placeIdInput.type = "hidden";
          placeIdInput.id = "place_id";
          placeIdInput.name = "place_id";
          document.getElementById("multiStepForm").appendChild(placeIdInput);
        }

        // **ADD THESE LINES HERE TOO:**
        document.getElementById("formatted_address").value =
          result.display_name;
        document.getElementById("place_id").value = result.place_id || "";

        // Clear search input
        document.getElementById("locationSearch").value = "";
      } else {
        alert("Location not found. Please try a different search term.");
      }
    })
    .catch((error) => {
      console.error("Search error:", error);
      alert("Error searching for location. Please try again.");
    });
}

// FIXED: Function to be called when navigating to step 3
function showStep3() {
  console.log("Showing step 3");

  // Initialize map when step 3 becomes visible
  setTimeout(() => {
    initializeLocationMap();

    // Setup search event listeners each time step 3 is shown
    setupSearchEventListeners();

    // CRITICAL: Invalidate map size to ensure proper rendering
    if (map && mapInitialized) {
      console.log("Invalidating map size");
      map.invalidateSize();
    }
  }, 200); // Increased timeout to ensure DOM is ready
}

// Validation function for step 3
function validateStep3() {
  if (!selectedLat || !selectedLng) {
    alert("Please select a location on the map before proceeding.");
    return false;
  }
  return true;
}

// Setup search event listeners - called when step 3 is shown
function setupSearchEventListeners() {
  console.log("Setting up search event listeners");

  // Add search button event listener
  const searchButton = document.getElementById("searchLocation");
  if (searchButton) {
    // Remove any existing listeners first
    searchButton.removeEventListener("click", searchLocation);
    searchButton.addEventListener("click", searchLocation);
    console.log("Search button listener added");
  } else {
    console.error("Search button not found");
  }

  // Add enter key support for search
  const searchInput = document.getElementById("locationSearch");
  if (searchInput) {
    searchInput.removeEventListener("keypress", handleSearchKeypress);
    searchInput.addEventListener("keypress", handleSearchKeypress);
    console.log("Search input listener added");
  } else {
    console.error("Search input not found");
  }
}

// Handle search input keypress
function handleSearchKeypress(e) {
  if (e.key === "Enter") {
    e.preventDefault();
    searchLocation();
  }
}

// Add event listeners when DOM is loaded
document.addEventListener("DOMContentLoaded", function () {
  console.log("DOM loaded");
  setupSearchEventListeners();
});

// Export functions for use in main form navigation
window.locationPicker = {
  showStep3,
  validateStep3,
  initializeLocationMap,
  setupSearchEventListeners,
  searchLocation,
};
