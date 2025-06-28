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

            <div class="form-step" id="step-3">
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

            <div class="form-step" id="step-4">
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
</body>

</html>