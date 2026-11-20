document.querySelectorAll( '[data-file-upload]' ).forEach( ( upload ) => {
    const input = upload.querySelector( '[data-file-upload-input]' );
    const button = upload.querySelector( '[data-file-upload-button]' );
    const name = upload.querySelector( '[data-file-upload-name]' );
    const visual = upload.querySelector( '[data-file-upload-visual]' );
    let previewUrl = '';

    button.addEventListener( 'click', () => input.click() );
    input.addEventListener( 'change', () => {
        const file = input.files[0];
        if ( !file ) {
            return;
        }
        if ( name ) {
            name.textContent = file.name;
            name.removeAttribute( 'href' );
            name.removeAttribute( 'target' );
            name.removeAttribute( 'rel' );
        }
        if ( upload.dataset.fileKind === 'image' ) {
            if ( previewUrl ) {
                URL.revokeObjectURL( previewUrl );
            }
            previewUrl = URL.createObjectURL( file );
            const image = document.createElement( 'img' );
            image.src = previewUrl;
            image.alt = 'Selected image preview';
            visual.replaceChildren( image );
        }
        if ( upload.dataset.autoSubmit === 'true' ) {
            input.form.requestSubmit();
        }
    } );
    window.addEventListener( 'beforeunload', () => {
        if ( previewUrl ) {
            URL.revokeObjectURL( previewUrl );
        }
    } );
} );
