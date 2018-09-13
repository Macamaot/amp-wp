/* exported ampInvalidUrlPostEditScreen */

var ampInvalidUrlPostEditScreen = ( function() { // eslint-disable-line no-unused-vars
	var component;

	component = {
		data: {
			l10n: {
				unsaved_changes: '',
				showing_number_errors: ''
			}
		}
	};

	/**
	 * Boot.
	 *
	 * @param {Object} data Data.
	 * @param {Object} data.l10n Translations.
	 */
	component.boot = function boot( data ) {
		Object.assign( component.data, data );
		component.handleFiltering();
		component.watchForUnsavedChanges();
	};

	/**
	 * Watch for unsaved changes.
	 *
	 * Add an beforeunload warning when attempting to leave the page when there are unsaved changes,
	 * unless the user is pressing the trash link or update button.
	 */
	component.watchForUnsavedChanges = function watchForUnsavedChanges() {
		var onChange = function( event ) {
			if ( event.target.matches( 'select' ) ) {
				document.getElementById( 'amp_validation_errors' ).removeEventListener( 'change', onChange );

				window.addEventListener( 'beforeunload', component.onBeforeUnload );

				// Remove prompt when clicking trash or update.
				document.querySelector( '#major-publishing-actions' ).addEventListener( 'click', function() {
					window.removeEventListener( 'beforeunload', component.onBeforeUnload );
				} );
			}
		};
		document.getElementById( 'amp_validation_errors' ).addEventListener( 'change', onChange );
	};

	/**
	 * Show message at beforeunload.
	 *
	 * @param {Event} event - The beforeunload event.
	 * @return {string} Message.
	 */
	component.onBeforeUnload = function onBeforeUnload( event ) {
		event.preventDefault();
		event.returnValue = component.data.l10n.unsaved_changes;
		return component.data.l10n.unsaved_changes;
	};

	/**
	 * Updates the <tr> with 'Showing x of y validation errors' at the top of the list table with the current count.
	 * If this does not exist yet, it creates the element.
	 *
	 * @param {number} numberErrorsDisplaying - The number of errors displaying.
	 */
	component.updateShowingErrorsRow = function updateShowingErrorsRow( numberErrorsDisplaying = null ) {
		var thead, tr, th,
			theadQuery = document.getElementsByTagName( 'thead' ),
			idNumberErrors = 'number-errors';

		// Only create the <tr> if it does not exist yet.
		if ( theadQuery[ 0 ] && ! document.getElementById( idNumberErrors ) ) {
			thead = theadQuery[ 0 ];
			tr = document.createElement( 'tr' );
			th = document.createElement( 'th' );
			th.setAttribute( 'id', idNumberErrors );

			/* eslint-disable dot-notation */
			th.style[ 'text-align' ] = 'center';
			th.style[ 'background-color' ] = '#d3d3d3b8';
			th.style[ 'color' ] = '#1e8cbecc';
			/* eslint-enable dot-notation */
			th.setAttribute( 'colspan', '6' );
			tr.appendChild( th );
			thead.appendChild( tr );
		}

		// Update the number of errors displaying.
		if ( null !== numberErrorsDisplaying ) {
			document.getElementById( idNumberErrors ).innerText = component.data.l10n.showing_number_errors.replace( '%', numberErrorsDisplaying );
		}
	};

	/**
	 * Handles filtering by error type, triggered by clicking 'Apply Filter'.
	 *
	 * Gets the value of the error type <select> element.
	 * And hides all <tr> elements that do not have the same type of this value.
	 * If 'All Error Types' is selected, this displays all errors.
	 */
	component.handleFiltering = function handleFiltering() {
		var onChange = function( event ) {
			var numberErrorsDisplaying = 0;

			if ( ! event.target.matches( 'select' ) ) {
				return;
			}

			event.preventDefault();

			/*
			 * Iterate through all of the <tr> elements in the list table.
			 * If the error type does not match the value (selected error type), hide them.
			 */
			document.querySelectorAll( '[data-error-type]' ).forEach( function( element ) {
				var errorType = element.getAttribute( 'data-error-type' );

				// If the value is '-1', 'All Error Types' was selected, and this should display all errors.
				if ( event.target.value === errorType || '-1' === event.target.value ) {
					element.parentElement.parentElement.classList.remove( 'hidden' );
					numberErrorsDisplaying++;
				} else {
					element.parentElement.parentElement.classList.add( 'hidden' );
				}
			} );

			component.updateShowingErrorsRow( numberErrorsDisplaying );
		};

		document.getElementById( 'amp_validation_error_type' ).addEventListener( 'change', onChange );
	};

	return component;
}() );
