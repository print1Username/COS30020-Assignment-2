document.addEventListener( "DOMContentLoaded", function () {
    const communityPagination = createPagination( {
        itemsPerPage: 8,
        paginationId: "community-pagination"
    } );

    communityPagination.setItems(
        document.querySelectorAll( ".community-card" )
    );

    const dropdowns = document.querySelectorAll( ".community-sort-dropdown" );

    dropdowns.forEach( function ( dropdown ) {
        dropdown.addEventListener( "toggle", function () {
            if ( dropdown.open ) {
                dropdowns.forEach( function ( otherDropdown ) {
                    if ( otherDropdown !== dropdown ) {
                        otherDropdown.removeAttribute( "open" );
                    }
                } );
            }
        } );
    } );

    document.addEventListener( "click", function ( event ) {
        dropdowns.forEach( function ( dropdown ) {
            if ( !dropdown.contains( event.target ) ) {
                dropdown.removeAttribute( "open" );
            }
        } );
    } );
} );
