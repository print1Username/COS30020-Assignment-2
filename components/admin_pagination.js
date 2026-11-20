document.querySelectorAll("[data-admin-pagination]").forEach(function (tableBody) {
    const pagination = createPagination({
        itemsPerPage: Number(tableBody.dataset.adminPageSize) || 5,
        paginationId: tableBody.dataset.adminPagination,
        scrollToTopOnChange: false
    });

    pagination.setItems(tableBody.querySelectorAll("tr"));
});
