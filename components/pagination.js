function createPagination(options) {
    const itemsPerPage = options.itemsPerPage || 12;
    const paginationElement = document.getElementById(options.paginationId);
    let currentPage = 1;
    let items = [];

    function getTotalPages() {
        return Math.ceil(items.length / itemsPerPage);
    }

    function renderItems() {
        const totalPages = getTotalPages();

        items.forEach(function (item, index) {
            const startIndex = (currentPage - 1) * itemsPerPage;
            const endIndex = startIndex + itemsPerPage;

            item.hidden = index < startIndex || index >= endIndex;
        });

        renderPagination(totalPages);
    }

    function renderPagination(totalPages) {
        paginationElement.innerHTML = "";

        if (totalPages <= 1) {
            paginationElement.parentElement.hidden = true;
            return;
        }

        paginationElement.parentElement.hidden = false;

        const previousItem = document.createElement("li");
        previousItem.className = "page-item";

        if (currentPage === 1) {
            previousItem.classList.add("disabled");
        }

        const previousButton = document.createElement("button");
        previousButton.type = "button";
        previousButton.className = "page-link";
        previousButton.innerHTML = "&laquo;";
        previousButton.setAttribute("aria-label", "Previous page");

        previousButton.addEventListener("click", function () {
            if (currentPage > 1) {
                currentPage--;
                renderItems();
                scrollToTop();
            }
        });

        previousItem.appendChild(previousButton);
        paginationElement.appendChild(previousItem);

        for (let page = 1; page <= totalPages; page++) {
            const pageItem = document.createElement("li");
            pageItem.className = "page-item";

            if (page === currentPage) {
                pageItem.classList.add("active");
            }

            const pageButton = document.createElement("button");
            pageButton.type = "button";
            pageButton.className = "page-link";
            pageButton.textContent = page;
            pageButton.setAttribute("aria-label", "Page " + page);

            if (page === currentPage) {
                pageButton.setAttribute("aria-current", "page");
            }

            pageButton.addEventListener("click", function () {
                if (currentPage !== page) {
                    currentPage = page;
                    renderItems();
                    scrollToTop();
                }
            });

            pageItem.appendChild(pageButton);
            paginationElement.appendChild(pageItem);
        }

        const nextItem = document.createElement("li");
        nextItem.className = "page-item";

        if (currentPage === totalPages) {
            nextItem.classList.add("disabled");
        }

        const nextButton = document.createElement("button");
        nextButton.type = "button";
        nextButton.className = "page-link";
        nextButton.innerHTML = "&raquo;";
        nextButton.setAttribute("aria-label", "Next page");

        nextButton.addEventListener("click", function () {
            if (currentPage < totalPages) {
                currentPage++;
                renderItems();
                scrollToTop();
            }
        });

        nextItem.appendChild(nextButton);
        paginationElement.appendChild(nextItem);
    }

    function scrollToTop() {
        window.scrollTo({
            top: 0,
            behavior: "smooth"
        });
    }

    return {
        setItems: function (newItems) {
            items = Array.from(newItems);
            currentPage = 1;
            renderItems();
        },

        reset: function () {
            currentPage = 1;
            renderItems();
        }
    };
}