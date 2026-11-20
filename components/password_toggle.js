document.addEventListener( "DOMContentLoaded", function () {
    document.querySelectorAll( ".password-toggle[data-password-targets]" ).forEach( function ( toggle ) {
        const passwordFields = toggle.dataset.passwordTargets
            .split( " " )
            .map( function ( id ) {
                return document.getElementById( id );
            } )
            .filter( Boolean );
        const relatedToggles = Array.from(
            document.querySelectorAll( ".password-toggle[data-password-targets]" )
        ).filter( function ( otherToggle ) {
            return otherToggle.dataset.passwordTargets === toggle.dataset.passwordTargets;
        } );

        function showPasswords() {
            passwordFields.forEach( function ( field ) {
                field.type = "text";
            } );
            relatedToggles.forEach( function ( relatedToggle ) {
                const icon = relatedToggle.querySelector( "i" );

                icon.classList.remove( "bi-eye" );
                icon.classList.add( "bi-eye-slash" );
            } );
        }

        function hidePasswords() {
            passwordFields.forEach( function ( field ) {
                field.type = "password";
            } );
            relatedToggles.forEach( function ( relatedToggle ) {
                const icon = relatedToggle.querySelector( "i" );

                icon.classList.remove( "bi-eye-slash" );
                icon.classList.add( "bi-eye" );
            } );
        }

        toggle.addEventListener( "mousedown", showPasswords );
        toggle.addEventListener( "mouseup", hidePasswords );
        toggle.addEventListener( "mouseleave", hidePasswords );
    } );
} );
