document.querySelectorAll( '[data-admin-activity-picker]' ).forEach( ( picker ) => {
    const search = picker.querySelector( '#activity-search' );
    const activityId = picker.querySelector( '[name="activity_id"]' );
    const toggle = picker.querySelector( '.admin-activity-toggle' );
    const resultsPanel = picker.querySelector( '.admin-activity-results' );
    const results = [ ...picker.querySelectorAll( '.admin-activity-result' ) ];
    const noResults = picker.querySelector( '.admin-activity-no-results' );
    const details = picker.querySelector( '.admin-activity-details' );

    function setExpanded( expanded ) {
        resultsPanel.hidden = !expanded;
        search.setAttribute( 'aria-expanded', String( expanded ) );
        toggle.setAttribute( 'aria-expanded', String( expanded ) );
        toggle.setAttribute( 'aria-label', expanded ? 'Hide activity options' : 'Show activity options' );
    }

    function filterResults() {
        const query = search.value.trim().toLocaleLowerCase();
        let matches = 0;
        results.forEach( ( button ) => {
            button.hidden = !button.dataset.activityTitle.toLocaleLowerCase().includes( query );
            if ( !button.hidden ) {
                matches++;
            }
        } );
        noResults.hidden = matches !== 0;
    }

    function formatTime( time ) {
        if ( !time ) {
            return '';
        }
        const [ hourValue, minute = '00' ] = time.split( ':' );
        const hour = Number( hourValue );
        const period = hour >= 12 ? 'PM' : 'AM';
        return `${ hour % 12 || 12 }:${ minute } ${ period }`;
    }

    function formatDate( date ) {
        if ( !date ) {
            return '';
        }
        const parsed = new Date( date + 'T00:00:00' );
        if ( Number.isNaN( parsed.getTime() ) ) {
            return date;
        }
        return new Intl.DateTimeFormat( 'en-GB', {
            day: '2-digit', month: 'short', year: 'numeric'
        } ).format( parsed ).replace( ',', '' );
    }

    function selectActivity( button ) {
        const activity = button.dataset;
        activityId.value = activity.activityId;
        search.value = activity.activityTitle;
        search.setCustomValidity( '' );
        details.replaceChildren();
        const title = document.createElement( 'strong' );
        title.textContent = activity.activityTitle;
        const summary = document.createElement( 'p' );
        summary.className = 'admin-activity-summary';
        const timeRange = [ formatTime( activity.activityTime ), formatTime( activity.activityEndTime ) ]
            .filter( Boolean ).join( ' - ' );
        const summaryItems = [ activity.activityCategory, formatDate( activity.activityDate ), timeRange,
            activity.activityVenue, activity.activityTable && `Table: ${ activity.activityTable }`,
            activity.activityPrice && `Price: RM ${ activity.activityPrice }` ].filter( Boolean );
        summary.replaceChildren( ...summaryItems.flatMap( ( item, index ) => {
            const value = document.createElement( 'span' );
            value.textContent = item;
            if ( index === summaryItems.length - 1 ) {
                return [ value ];
            }
            const separator = document.createElement( 'span' );
            separator.className = 'admin-activity-summary-separator';
            separator.textContent = '|';
            separator.setAttribute( 'aria-hidden', 'true' );
            return [ value, separator ];
        } ) );
        const description = document.createElement( 'p' );
        description.textContent = activity.activityDescription || '';
        details.append( title, summary, description );
        details.hidden = false;
        results.forEach( ( result ) => {
            result.hidden = true;
        } );
        noResults.hidden = true;
        setExpanded( false );
    }

    search.addEventListener( 'input', () => {
        activityId.value = '';
        details.hidden = true;
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
    results.forEach( ( button ) => button.addEventListener( 'click', () => selectActivity( button ) ) );
    document.addEventListener( 'click', ( event ) => {
        if ( !picker.contains( event.target ) ) {
            setExpanded( false );
        }
    } );
    picker.closest( 'form' ).addEventListener( 'submit', ( event ) => {
        if ( !activityId.value ) {
            event.preventDefault();
            search.setCustomValidity( 'Select an activity from the results.' );
            search.reportValidity();
        }
    } );

    const previous = results.find( ( button ) => button.dataset.activityId === activityId.value );
    if ( previous ) {
        selectActivity( previous );
    }
} );
