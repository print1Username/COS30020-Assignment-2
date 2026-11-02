/*
 * ==========================
 * Main Menu
 * ==========================
 */

document.addEventListener("DOMContentLoaded", function () {
    const menuCards = document.querySelectorAll(".main-menu-card");

    /*
     * Add keyboard accessibility to menu cards.
     */
    menuCards.forEach(function (card) {
        const button = card.querySelector(".main-menu-card-button");

        if (!button) {
            return;
        }

        card.setAttribute("tabindex", "0");

        card.addEventListener("keydown", function (event) {
            if (event.key === "Enter" || event.key === " ") {
                event.preventDefault();
                button.click();
            }
        });
    });
});