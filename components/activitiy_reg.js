document.addEventListener("DOMContentLoaded", function () {
    const form = document.querySelector(".activity-registration-form");
    const submitButton = document.querySelector(".activity-submit-button");
    const contactInput = document.getElementById("contact_number");

    if (!form) {
        return;
    }

    /*
     * ==========================
     * Contact Number Input
     * ==========================
     */

    if (contactInput) {
        contactInput.addEventListener("input", function () {
            this.value = this.value.replace(/[^0-9+\-\s]/g, "");
        });
    }

    /*
     * ==========================
     * Prevent Double Submission
     * ==========================
     */

    form.addEventListener("submit", function () {
        if (!submitButton) {
            return;
        }

        submitButton.disabled = true;

        submitButton.innerHTML =
            '<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>' +
            "Registering...";

        submitButton.classList.add("disabled");
    });
});