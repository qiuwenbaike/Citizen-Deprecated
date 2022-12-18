const
	checkboxHack = require( './checkboxHack.js' ),
	CHECKBOX_HACK_CONTAINER_SELECTOR = '.mw-checkbox-hack-container',
	CHECKBOX_HACK_CHECKBOX_SELECTOR = '.mw-checkbox-hack-checkbox',
	CHECKBOX_HACK_BUTTON_SELECTOR = '.mw-checkbox-hack-button',
	CHECKBOX_HACK_TARGET_SELECTOR = '.mw-checkbox-hack-target';

/**
 * Wait for first paint before calling this function.
 * (see T234570#5779890, T246419).
 *
 * @param {Document} document
 * @return {void}
 */
function enableCssAnimations( document ) {
	document.documentElement.classList.add( 'citizen-animations-ready' );
}

/**
 * Add the ability for users to toggle dropdown menus using the enter key (as
 * well as space) using core's checkboxHack.
 *
 * Based on Vector
 *
 * @return {void}
 */
function bind() {
	// Search for all dropdown containers using the CHECKBOX_HACK_CONTAINER_SELECTOR.
	const containers = document.querySelectorAll( CHECKBOX_HACK_CONTAINER_SELECTOR );

	containers.forEach( ( container ) => {
		const
			checkbox = container.querySelector( CHECKBOX_HACK_CHECKBOX_SELECTOR ),
			button = container.querySelector( CHECKBOX_HACK_BUTTON_SELECTOR ),
			target = container.querySelector( CHECKBOX_HACK_TARGET_SELECTOR );

		if ( !( checkbox && button && target ) ) {
			return;
		}

		checkboxHack.bind( window, checkbox, button, target );
	} );
}

/**
 * Close all menus through unchecking all checkbox hacks
 *
 * @return {void}
 */
function uncheckCheckboxHacks() {
	const checkboxes = document.querySelectorAll( CHECKBOX_HACK_CHECKBOX_SELECTOR + ':checked' );

	checkboxes.forEach( ( checkbox ) => {
		/** @type {HTMLInputElement} */ ( checkbox ).checked = false;
	} );
}

/**
 * Add a class to indicate that sticky header is active
 *
 * @param {Document} document
 * @return {void}
 */
function initStickyHeader( document ) {
	const scrollObserver = require( './scrollObserver.js' );

	// Detect scroll direction and add the right class
	scrollObserver.initDirectionObserver(
		() => {
			document.body.classList.remove( 'citizen-scroll--up' );
			document.body.classList.add( 'citizen-scroll--down' );
		},
		() => {
			document.body.classList.remove( 'citizen-scroll--down' );
			document.body.classList.add( 'citizen-scroll--up' );
		},
		10
	);

	const sentinel = document.getElementById( 'citizen-body-header-sticky-sentinel' );

	// In some pages we use display:none to disable the sticky header
	// Do not start observer if it is set to display:none
	if ( sentinel && getComputedStyle( sentinel ).getPropertyValue( 'display' ) !== 'none' ) {
		const observer = scrollObserver.initIntersectionObserver(
			() => {
				document.body.classList.add( 'citizen-body-header--sticky' );
			},
			() => {
				document.body.classList.remove( 'citizen-body-header--sticky' );
			}
		);

		observer.observe( sentinel );
	}
}

/**
 * @param {Window} window
 * @return {void}
 */
function main( window ) {
	const search = require( './search.js' );

	enableCssAnimations( window.document );
	search.init( window );
	initStickyHeader( window.document );

	// Set up checkbox hacks
	bind();

	// Table of Contents
	const tocContainer = document.getElementById( 'mw-panel-toc' );
	if ( tocContainer ) {
		const toc = require( './tableOfContents.js' );
		toc.init();
	}

	// Collapsible sections
	if ( document.body.classList.contains( 'citizen-sections-enabled' ) ) {
		const sections = require( './sections.js' );
		sections.init();
	}

	window.addEventListener( 'beforeunload', () => {
		// T295085: Close all dropdown menus when page is unloaded to prevent them
		// from being open when navigating back to a page.
		uncheckCheckboxHacks();
		// Set up loading indicator
		document.documentElement.classList.add( 'citizen-loading' );
	}, false );
}

if ( document.readyState === 'interactive' || document.readyState === 'complete' ) {
	main( window );
} else {
	document.addEventListener( 'DOMContentLoaded', function () {
		main( window );
	} );
}
