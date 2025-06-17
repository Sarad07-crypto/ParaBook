<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Multi-step Form</title>
    <link rel="stylesheet" href="style.css?v=1.0" />
    <link href="https://cdn.boxicons.com/fonts/basic/boxicons.min.css" rel="stylesheet" />
</head>
<style>
:root {
    --primary: #eeeeee;
    --secondary: #2192ff;
    --grey: #808080;
    --white: #ffffff;
    --black: #222222;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: "Poppins", sans-serif;
}

body {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 100vh;
    background-color: var(--primary);
}

.wrapper {
    width: 70%;
    display: flex;
    align-items: center;
    flex-direction: column;
}

.progress {
    position: absolute;
    height: 3px;
    top: 32%;
    width: 0%;
    left: 5%;
    transform: translateY(-50%);
    background-color: var(--secondary);
    transition: width 0.3s;
}

.progress-container {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
    position: relative;
}

.progress-container::after {
    content: "";
    position: absolute;
    height: 3px;
    width: 95%;
    top: 30%;
    left: 5%;
    background-color: var(--grey);
    z-index: -1;
}

.progress-step {
    display: flex;
    align-items: center;
    flex-direction: column;
    font-weight: 500;
    z-index: 2;
}

.progress-step .bx {
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--grey);
    width: 50px;
    height: 50px;
    border: 3px solid var(--grey);
    border-radius: 50%;
    margin-bottom: 10px;
    background-color: var(--primary);
    transition: border 0.3s, color 0.3s;
}

.progress-step .bx.active {
    border: 3px solid var(--secondary);
    color: var(--secondary);
}

.bx {
    color: black;
    font-size: 20px;
}

.btn-container {
    display: flex;
    align-items: center;
    justify-content: center;
    margin-top: 2rem;
}

.btn-container .btn {
    padding: 0.5rem 1.5rem;
    margin: 0 1rem;
    border-radius: 20px;
    background-color: var(--secondary);
    color: var(--white);
    cursor: pointer;
    border: 1px solid var(--secondary);
}

.btn.disabled {
    border: 1px solid var(--grey);
    background-color: var(--grey);
    color: var(--white);
    cursor: not-allowed;
}

.form-step {
    display: none;
    flex-direction: column;
    margin-top: 1rem;
    width: 100%;
}

.form-step input,
.form-step textarea,
.form-step select {
    padding: 0.5rem;
    margin: 0.5rem 0;
    width: 100%;
    border: 1px solid var(--grey);
    border-radius: 8px;
}

.form-step.active {
    display: flex;
}
</style>

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
                <label for="flight-type">Select Flight Type:</label>
                <select id="flight-type" required>
                    <option value="">-- Choose one --</option>
                    <option value="normal-tandem">Normal Tandem</option>
                    <option value="cloud-surfing">Cloud Surfing</option>
                    <option value="cross-country">Cross-country</option>
                </select>
                <input type="text" placeholder="Amount" required />
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
    </div>
    <script>
    const progress = document.getElementById("progress");
    const nextBtn = document.getElementById("next");
    const prevBtn = document.getElementById("prev");
    const progressStep = document.querySelectorAll(".progress-step .bx");
    const formSteps = document.querySelectorAll(".form-step");

    let currentStep = 1;
    const next = () => {
        if (currentStep < progressStep.length) {
            currentStep++;
            refresh();
        }
    };
    const prev = () => {
        currentStep--;
        if (currentStep < 1) currentStep = 1;
        refresh();
    };

    const refresh = () => {
        progressStep.forEach((step, index) => {
            step.classList.toggle("active", index < currentStep);
        });

        prevBtn.classList.toggle("disabled", currentStep === 1);
        nextBtn.classList.toggle("disabled", currentStep === progressStep.length);

        // Update progress bar
        const allActiveClasses = document.querySelectorAll(".progress-step .active");
        let width = ((allActiveClasses.length - 1) / (progressStep.length - 1)) * 100;
        let widthRatio = (allActiveClasses.length - 1) / (progressStep.length - 1);
        width = widthRatio * 100;

        if (currentStep === progressStep.length) {
            width -= 10;
        }

        progress.style.width = width + "%";

        formSteps.forEach((form, index) => {
            form.classList.toggle("active", index === currentStep - 1);
        });
    };

    refresh();
    </script>
</body>

</html>