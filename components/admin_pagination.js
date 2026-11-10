document.querySelectorAll("[data-admin-pagination]").forEach(function (tableBody) {
    const pagination = createPagination({
        itemsPerPage: 6,
        paginationId: tableBody.dataset.adminPagination
    });

    pagination.setItems(tableBody.querySelectorAll("tr"));
});
