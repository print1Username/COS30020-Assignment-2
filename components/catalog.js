document.addEventListener("DOMContentLoaded", function () {
    const filterButtons = document.querySelectorAll(".catalog-filter-btn");
    const gameCards = document.querySelectorAll(".catalog-game-item");
    const emptyState = document.getElementById("catalog-empty-state");
    const gameCount = document.getElementById("catalog-game-count");

    const pagination = createPagination({
        itemsPerPage: 8,
        paginationId: "catalog-pagination"
    });

    function filterGames(selectedCategory) {
        const filteredGames = [];

        gameCards.forEach(function (card) {
            const categories = card.dataset.categories.split("|");

            const matchesCategory =
                selectedCategory === "all" ||
                categories.includes(selectedCategory);

            if (matchesCategory) {
                filteredGames.push(card);
            }

            card.hidden = true;
        });

        // Update game count
        gameCount.textContent = filteredGames.length + " Games";

        // Show empty state when no games match
        emptyState.hidden = filteredGames.length !== 0;

        // Update pagination
        pagination.setItems(filteredGames);
    }

    filterButtons.forEach(function (button) {
        button.addEventListener("click", function () {
            const selectedCategory = this.dataset.category;

            // Update active filter button
            filterButtons.forEach(function (filterButton) {
                filterButton.classList.remove("active");
                filterButton.setAttribute("aria-pressed", "false");
            });

            this.classList.add("active");
            this.setAttribute("aria-pressed", "true");

            // Filter and reset pagination
            filterGames(selectedCategory);
        });
    });

    // Show all games when the page loads
    filterGames("all");
});