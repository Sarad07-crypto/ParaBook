<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <!-- Leaflet JavaScript -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <title></title>
</head>

<style>
.location-selection {
    padding: 20px;
}

.location-help {
    color: #666;
    font-size: 0.9rem;
    margin-bottom: 20px;
}

.map-container {
    margin: 20px 0;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.location-info {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    margin-top: 15px;
}

.coordinate-display {
    display: flex;
    gap: 20px;
    padding: 8px;
    background: white;
    border-radius: 4px;
    font-family: monospace;
}

.address-display {
    padding: 8px;
    background: white;
    border-radius: 4px;
    border: 1px solid #ddd;
    min-height: 40px;
    display: flex;
    align-items: center;
}

#locationSearch {
    width: 70%;
    display: inline-block;
}

#searchLocation {
    width: 25%;
    margin-left: 5%;
    padding: 8px 16px;
    background: #007bff;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

#searchLocation:hover {
    background: #0056b3;
}

.location-marker {
    color: #dc3545;
    font-size: 1.2rem;
}
</style>

<body>
    <div class="wrapper">
        <div class="progress-container">
            <div class="progress" id="progress"></div>
            <div class="progress-step">
                <i class="bx bx-info-circle active"></i>
                <span>Basic Info</span>
            </div>
            <div class="progress-step">
                <i class="bx bx-file-detail"></i>
                <span>Description</span>
            </div>
            <div class="progress-step">
                <i class="bx bx-map"></i>
                <span>Location</span>
            </div>
            <div class="progress-step">
                <i class="bx bx-currency-notes"></i>
                <span>Pricing</span>
            </div>
            <div class="progress-step">
                <i class="bx bx-images"></i>
                <span>Gallery</span>
            </div>
        </div>
        <form id="multiStepForm" method="POST" action="/servicePost" enctype="multipart/form-data">
            <div class="form-step" id="step-1">
                <div class="input-group">
                    <label for="companyName">Enter Company Name: <span class="required">*</span></label>
                    <input type="text" id="companyName" name="companyName" placeholder="Company Name" required />
                </div>

                <div class="input-group">
                    <label for="serviceTitle">Enter Service Title: <span class="required">*</span></label>
                    <input type="text" id="serviceTitle" name="serviceTitle" placeholder="Service Title" required />
                </div>

                <div class="input-group">
                    <label for="address">Enter Address: <span class="required">*</span></label>
                    <input type="text" id="address" name="address" placeholder="Address" required />
                </div>

                <div class="input-group">
                    <label for="contact">Enter Contact Number: <span class="required">*</span></label>
                    <input type="tel" id="contact" name="contact" placeholder="Contact (9XXXXXXXXX)" pattern="9[0-9]{9}"
                        maxlength="10" title="Please enter 10 digits starting with 9" required />
                </div>

                <div class="input-group">
                    <label for="panNumber">Enter PAN Number: <span class="required">*</span></label>
                    <input type="text" id="panNumber" name="panNumber" placeholder="PAN Number (9 digits)"
                        pattern="[0-9]{9}" maxlength="9" title="Please enter exactly 9 digits" required />
                </div>
            </div>

            <div class="form-step" id="step-2">
                <div class="input-group">
                    <label for="serviceDescription">Enter Service Description: <span class="required">*</span></label>
                    <textarea id="serviceDescription" name="serviceDescription"
                        placeholder="Write your service description here..." rows="20" cols="200" maxlength="1000"
                        required></textarea>
                </div>
            </div>

            <!-- Step 3: Location Selection (NEW) -->
            <div class="form-step" id="step-3">
                <div class="location-selection">
                    <h3>Select Your Business Location</h3>
                    <p class="location-help">Click on the map to set your exact business location, or search for your
                        address.</p>

                    <div class="input-group">
                        <label for="locationSearch">Search for your location:</label>
                        <input type="text" id="locationSearch" placeholder="Type your address or business name..." />
                        <button type="button" id="searchLocation">Search</button>
                    </div>

                    <div class="map-container">
                        <div id="locationMap" style="height: 400px; width: 100%; border-radius: 8px;"></div>
                    </div>

                    <div class="location-info">
                        <div class="input-group">
                            <label>Selected Coordinates:</label>
                            <div class="coordinate-display">
                                <span>Latitude: <span id="selectedLat">Not selected</span></span>
                                <span>Longitude: <span id="selectedLng">Not selected</span></span>
                            </div>
                        </div>

                        <div class="input-group">
                            <label>Address at selected location:</label>
                            <div id="selectedAddress" class="address-display">Click on the map to get address</div>
                        </div>
                    </div>

                    <!-- Hidden inputs to store coordinates -->
                    <input type="hidden" id="latitude" name="latitude" required />
                    <input type="hidden" id="longitude" name="longitude" required />
                </div>
            </div>

            <div class="form-step" id="step-4">
                <div class="input-group">
                    <label for="flight-type-name">Enter Flight Type Name:</label>
                    <input type="text" id="flight-type-name" placeholder="e.g., Normal Tandem" />
                </div>

                <div class="input-group">
                    <label for="flight-type-price">Enter Price (Rs):</label>
                    <input type="number" id="flight-type-price" placeholder="e.g., 5000" />
                </div>

                <button type="button" id="add-flight-type">Add Flight Type</button>
                <ul id="flight-type-list"></ul>
                <div id="flight-types-hidden-inputs"></div>
            </div>

            <div class="form-step" id="step-5">
                <div class="input-group">
                    <label for="officePhotos">Upload Office Photos (4 photos): <span class="required">*</span></label>
                    <input type="file" name="officePhotos[]" id="officePhotos" accept="image/*" multiple required />
                    <div class="counter" id="photoCounter">0 of 4 photos uploaded</div>
                </div>

                <div id="officePhotosPreview" class="image-preview">
                    <div class="empty-state">No office photos uploaded yet</div>
                </div>

                <div class="input-group">
                    <label for="thumbnail">Upload Thumbnail Image: <span class="required">*</span></label>
                    <input type="file" name="thumbnail" id="thumbnail" accept="image/*" required />
                </div>

                <div id="thumbnailPreview" class="image-preview">
                    <div class="empty-state">No thumbnail uploaded yet</div>
                </div>
            </div>
            <input type="hidden" id="formatted_address" name="formatted_address" />
            <input type="hidden" id="place_id" name="place_id" />
            <!-- <input type="hidden" name="service_id" value="<?= htmlspecialchars($service['id'] ?? '') ?>"> -->
        </form>


        <div class="btn-container">
            <button class="btn" id="prev" onclick="prev()">Previous</button>
            <button class="btn" id="next" onclick="next()">Next</button>
        </div>
        <div style="text-align: center; margin-top: 20px;">
            <button class="btn" id="submitBtn" type="submit" form="multiStepForm" style="display: none;">Submit</button>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="Web/scripts/map.js"></script>
</body>

</html>