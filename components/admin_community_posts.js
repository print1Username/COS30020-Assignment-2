const postSection = document.querySelector('.admin-community-posts');

if (postSection) {
    const picker = postSection.querySelector('[data-post-community-picker]');
    const search = picker.querySelector('#post-community-search');
    const toggle = picker.querySelector('.admin-activity-toggle');
    const resultsPanel = picker.querySelector('.admin-activity-results');
    const options = [...picker.querySelectorAll('[data-community-id]')];
    const noResults = picker.querySelector('.admin-activity-no-results');
    const tabs = [...postSection.querySelectorAll('[data-post-status]')].filter((item) => item.matches('button'));
    const tableBody = postSection.querySelector('[data-post-pagination]');
    const rows = [...tableBody.querySelectorAll('tr')];
    const tableScroll = postSection.querySelector('.admin-community-post-scroll');
    const empty = postSection.querySelector('[data-post-empty]');
    const storageKey = 'adminCommunityPostFilters';
    const saved = JSON.parse(sessionStorage.getItem(storageKey) || '{}');
    let communityId = options.some((option) => option.dataset.communityId === saved.communityId)
        ? saved.communityId : '';
    let status = tabs.some((tab) => tab.dataset.postStatus === saved.status)
        ? saved.status : 'pending';
    const pagination = createPagination({
        itemsPerPage: 5,
        paginationId: 'posts-pagination',
        scrollToTopOnChange: false
    });

    function setExpanded(expanded) {
        resultsPanel.hidden = !expanded;
        search.setAttribute('aria-expanded', String(expanded));
        toggle.setAttribute('aria-expanded', String(expanded));
        toggle.setAttribute('aria-label', expanded ? 'Hide community options' : 'Show community options');
    }

    function filterOptions(query) {
        const needle = query.trim().toLocaleLowerCase();
        let matches = 0;
        options.forEach((option) => {
            option.hidden = !option.dataset.communityTitle.toLocaleLowerCase().includes(needle);
            if (!option.hidden) matches++;
        });
        noResults.hidden = matches !== 0;
    }

    function applyFilters() {
        const visible = rows.filter((row) =>
            (communityId === '' || row.dataset.communityId === communityId) && row.dataset.postStatus === status
        );
        rows.forEach((row) => {
            row.hidden = true;
        });
        tableScroll.hidden = visible.length === 0;
        empty.hidden = visible.length !== 0;
        empty.textContent = `No ${status} community posts are available${communityId ? ' for this community' : ''}.`;
        tabs.forEach((tab) => {
            const active = tab.dataset.postStatus === status;
            tab.classList.toggle('active', active);
            tab.setAttribute('aria-selected', String(active));
        });
        pagination.setItems(visible);
        sessionStorage.setItem(storageKey, JSON.stringify({communityId, status}));
    }

    function selectedOption() {
        return options.find((option) => option.dataset.communityId === communityId);
    }

    function selectedLabel() {
        return communityId === '' ? '' : selectedOption().dataset.communityTitle;
    }

    function selectCommunity(option) {
        communityId = option.dataset.communityId;
        search.value = selectedLabel();
        setExpanded(false);
        applyFilters();
    }

    search.value = selectedLabel();
    search.addEventListener('input', () => {
        filterOptions(search.value);
        setExpanded(true);
    });
    search.addEventListener('focus', () => {
        filterOptions(search.value === selectedLabel() ? '' : search.value);
        setExpanded(true);
        search.select();
    });
    search.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            search.value = selectedLabel();
            setExpanded(false);
        }
    });
    toggle.addEventListener('click', () => {
        const expanded = resultsPanel.hidden;
        setExpanded(expanded);
        if (expanded) {
            search.focus();
            filterOptions('');
        }
    });
    options.forEach((option) => option.addEventListener('click', () => selectCommunity(option)));
    document.addEventListener('click', (event) => {
        if (!picker.contains(event.target)) {
            search.value = selectedLabel();
            setExpanded(false);
        }
    });
    tabs.forEach((tab) => tab.addEventListener('click', () => {
        status = tab.dataset.postStatus;
        applyFilters();
    }));

    applyFilters();
}
