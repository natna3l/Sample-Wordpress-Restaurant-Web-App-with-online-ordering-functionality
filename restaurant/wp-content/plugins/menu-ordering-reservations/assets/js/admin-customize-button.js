function glfDisplayShortcode() {
    var ruid = document.getElementById( 'js_glf_mor_ruid' ).value;
    jQuery( '.glf-ordering-location' ).attr( 'data-location', ruid );
    jQuery( '.glf-reservations-location' ).attr( 'data-location', ruid );
    document.getElementById( 'js_glf_mor_ordering' ).value = glf_mor_createShortcode( 'ordering', ruid );
    document.getElementById( 'js_glf_mor_reservations' ).value = glf_mor_createShortcode( 'reservations', ruid );
    document.getElementById( 'js_glf_mor_full_menu' ).value = glf_mor_createShortcode( 'full-menu', ruid );
    ajax_update_default_location( ruid );
}

function glfAccountIdToUrl() {
    var el = jQuery( '#js_glf_mor_acid' ).find( 'option:selected' ),
        acid = el.val(),
        setup = jQuery( '#glf-acid-setup' ),
        menu = jQuery( '#glf-acid-menu' ),
        setup_href = setup.attr( 'href' ),
        menu_href = menu.attr( 'href' );
    if( el.length <= 0 ) {
        el = jQuery( '#js_glf_mor_acid' );
        acid = el.val();
    }
    if( setup_href !== undefined ) {
        if( setup_href.indexOf( '&acid' ) >= 0 || menu_href.indexOf( '&acid' ) >= 0 ) {
            setup_href = setup_href.substr( 0, setup_href.indexOf( '&acid' ) );
            menu_href = menu_href.substr( 0, menu_href.indexOf( '&acid' ) );
        }
        setup.attr( 'href', setup_href + '&acid=' + acid );
        menu.attr( 'href', menu_href + '&acid=' + acid );
        ajax_update_default_location( el.attr( 'data-uid' ) );
    }
}

function ajax_update_default_location( location ) {
    var data = {
        action: 'glf_set_default_location',
        location: location
    };
    jQuery.ajax( {
        url: window.ajaxurl,
        type: "POST",
        data: data,
        dataType: "json",
        success: function ( data ) {
        },
        error: function ( xhr, status, error ) {
            console.log( 'Status[' + status + '] Error[' + error + ']' );
        }
    } );
}

function glfUpdateFullMenu( element ) {
    var ruid = document.getElementById( 'js_glf_mor_ruid' ).value;
    //window.location.href = jQuery(element).data('page') + '&refresh_menu=' + ruid;

    // Fix for alert appearing everytime you customize a button.
    // If the page is changed the alert doesn't appear anymore.
    // Delete the refresh_menu parameter from the URL.
    let form = jQuery( '<form action="' + jQuery( element ).data( 'page' ) + '" method="post">' +
        '<input type="text" name="refresh_menu" value="' + ruid + '" />' +
        '</form>' );
    jQuery( 'body' ).append( form );
    form.submit();
}
