<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title></title>
</head>

<body>
    <div class="wrapper">
        <div class="progress-container">
            <div class="progress" id="progress"></div>
            <div class="progress-step">
                <i class="bx bx-info-circle active"></i> Basic Info
            </div>
            <div class="progress-step">
                <i class="bx bx-file-detail"></i>Description
            </div>
            <div class="progress-step">
                <i class="bx bx-currency-notes"></i>Pricing
            </div>
            <div class="progress-step"><i class="bx bx-images"></i>Gallery</div>
        </div>
        <form id="multiStepForm">
            <div class="form-step" id="step-1">
                <input type="text" placeholder="Company Name" required />
                <input type="text" placeholder="Service Title" required />
                <input type="text" placeholder="Address" required />
                <input type="text" placeholder="Contact" required />
                <input type="text" placeholder="PAN Number" required />
            </div>

            <div class="form-step" id="step-2">
                <textarea placeholder="Write your service description here..." rows="20" cols="200" maxlength="1000"
                    required></textarea>
            </div>

            <div class="form-step" id="step-3">
                <label for="flight-type-name">Flight Type Name:</label>
                <input type="text" id="flight-type-name" placeholder="e.g., Normal Tandem" required />

                <label for="flight-type-price">Price (Rs):</label>
                <input type="number" id="flight-type-price" placeholder="e.g., 5000" required />

                <button type="button" id="add-flight-type">Add</button>

                <ul id="flight-type-list"></ul>
            </div>

            <div class="form-step" id="step-4">
                <label>Upload 4 Office Photos:</label>
                <input type="file" accept="image/*" multiple required />
                <label>Thumbnail Image:</label>
                <input type="file" accept="image/*" required />
            </div>
        </form>

        <div class="btn-container">
            <button class="btn" id="prev" onclick="prev()">Previous</button>
            <button class="btn" id="next" onclick="next()">Next</button>
        </div>
        <div style="text-align: center; margin-top: 20px;">
            <button class="btn" id="submitBtn" type="submit" form="multiStepForm" style="display: none;">Submit</button>
        </div>
    </div>
</body>

</html>