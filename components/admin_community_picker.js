document.querySelectorAll( '[data-admin-community-picker]' ).forEach( ( picker ) => {
    const search = picker.querySelector( '#community-search' );
    const communityId = picker.querySelector( '[name="community_id"]' );
    const toggle = picker.querySelector( '.admin-activity-toggle' );
    const resultsPanel = picker.querySelector( '.admin-activity-results' );
    const results = [ ...picker.querySelectorAll( '.admin-community-result' ) ];
    const noResults = picker.querySelector( '.admin-activity-no-results' );

    function setExpanded( expanded ) {
        resultsPanel.hidden = !expanded;
        search.setAttribute( 'aria-expanded', String( expanded ) );
        toggle.setAttribute( 'aria-expanded', String( expanded ) );
        toggle.setAttribute( 'aria-label', expanded ? 'Hide community options' : 'Show community options' );
    }

    function filterResults() {
        const query = search.value.trim().toLocaleLowerCase();
        let matches = 0;
        results.forEach( ( button ) => {
            button.hidden = !button.dataset.communityTitle.toLocaleLowerCase().includes( query );
            if ( !button.hidden ) {
                matches++;
            }
        } );
        noResults.hidden = matches !== 0;
    }

    function selectCommunity( button ) {
        communityId.value = button.dataset.communityId;
        search.value = button.dataset.communityTitle;
        search.setCustomValidity( '' );
        setExpanded( false );
    }

    search.addEventListener( 'input', () => {
        communityId.value = '';
        search.setCustomValidity( '' );
        filterResults();
        setExpanded( true );
    } );
    search.addEventListener( 'focus', () => {
        filterResults();
        setExpanded( true );
    } );
    toggle.addEventListener( 'click', () => {
        const expand = resultsPanel.hidden;
        if ( expand ) {
            filterResults();
        }
        setExpanded( expand );
        if ( !resultsPanel.hidden ) {
            search.focus();
        }
    } );
    results.forEach( ( button ) => button.addEventListener( 'click', () => selectCommunity( button ) ) );
    document.addEventListener( 'click', ( event ) => {
        if ( !picker.contains( event.target ) ) {
            setExpanded( false );
        }
    } );
    picker.closest( 'form' ).addEventListener( 'submit', ( event ) => {
        if ( !communityId.value ) {
            event.preventDefault();
            search.setCustomValidity( 'SELECT a community FROM the results.' );
            search.reportValidity();
        }
    } );

    const previous = results.find( ( button ) => button.dataset.communityId === communityId.value );
    if ( previous ) {
        selectCommunity( previous );
    }
} );
