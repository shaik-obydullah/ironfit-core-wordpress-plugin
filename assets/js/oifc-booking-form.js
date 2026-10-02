/**
 * Front-end booking form.
 *
 * Upgrades every form.oifc-booking-form from a normal POST into an AJAX
 * submission against admin-ajax.php, so the visitor stays on the page and sees
 * the result inline. Markup that does not carry the class is left untouched.
 *
 * @package ObydullahIronfitCore
 * @since   1.0.0
 */

( function () {
	'use strict';

	var config = window.oifcBooking || {};
	var SELECTOR = 'form.oifc-booking-form';

	/**
	 * Writes a message into the form's status region.
	 *
	 * The server sends plain text, so this uses textContent rather than
	 * innerHTML and never interprets a response as markup.
	 *
	 * @param {HTMLFormElement} form  Form the message belongs to.
	 * @param {string}          text  Message to show.
	 * @param {string}          type  Either 'success' or 'error'.
	 */
	function setStatus( form, text, type ) {
		var status = form.querySelector( '.oifc-booking-form__status' );

		if ( ! status ) {
			return;
		}

		status.textContent = text;
		status.className = 'oifc-booking-form__status is-' + type;
	}

	/**
	 * Puts the submit button back to its resting state.
	 *
	 * @param {HTMLFormElement} form  Form being submitted.
	 */
	function setBusy( form, busy ) {
		var button = form.querySelector( '[type="submit"]' );

		if ( ! button ) {
			return;
		}

		button.disabled = busy;
		button.setAttribute( 'aria-busy', busy ? 'true' : 'false' );

		if ( ! button.dataset.label ) {
			button.dataset.label = button.textContent;
		}

		button.textContent = busy ? config.i18n.submitting : button.dataset.label;
	}

	/**
	 * Submits the form and reports the outcome in place.
	 *
	 * @param {SubmitEvent} event  Submit event.
	 */
	function handleSubmit( event ) {
		var form = event.currentTarget;

		if ( form.dataset.submitting === 'true' ) {
			event.preventDefault();
			return;
		}

		event.preventDefault();

		form.dataset.submitting = 'true';
		setBusy( form, true );
		setStatus( form, '', 'error' );

		window
			.fetch( config.ajaxUrl, {
				method: 'POST',
				body: new FormData( form ),
				credentials: 'same-origin',
			} )
			.then( function ( response ) {
				return response.json();
			} )
			.then( function ( result ) {
				form.dataset.submitting = 'false';
				setBusy( form, false );

				if ( ! result || result.success !== true ) {
					setStatus(
						form,
						( result && result.data ) || config.i18n.genericError,
						'error'
					);
					return;
				}

				form.reset();
				setStatus( form, result.data, 'success' );
			} )
			.catch( function () {
				// Reached when the request fails outright, or when the response
				// is not JSON because PHP emitted a warning or fatal error.
				form.dataset.submitting = 'false';
				setBusy( form, false );
				setStatus( form, config.i18n.networkError, 'error' );
			} );
	}

	function init() {
		var forms = document.querySelectorAll( SELECTOR );

		if ( ! config.ajaxUrl || ! forms.length ) {
			return;
		}

		Array.prototype.forEach.call( forms, function ( form ) {
			form.addEventListener( 'submit', handleSubmit );
		} );
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
} )();