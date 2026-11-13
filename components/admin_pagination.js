document.querySelectorAll("[data-admin-pagination]").forEach(function (tableBody) {
    const pagination = createPagination({
        itemsPerPage: Number(tableBody.dataset.adminPageSize) || 6,
        paginationId: tableBody.dataset.adminPagination
    });

    pagination.setItems(tableBody.querySelectorAll("tr"));
});
