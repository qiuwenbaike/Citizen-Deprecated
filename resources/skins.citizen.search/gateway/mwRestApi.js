const config = require( '../config.json' );

/**
 * Build URL used for fetch request
 *
 * @param {string} input
 * @return {string} url
 */
const getUrl = ( input ) => {
	const endpoint = config.wgScriptPath + '/rest.php/v1/search/title?q=', query = '&limit=' + config.wgCitizenMaxSearchResults;

	return endpoint + input + query;
};

/**
 * Map raw response to Results object
 *
 * @param {Object} data
 * @return {Object} Results
 */
const convertDataToResults = ( data ) => {
	const results = [];

	// eslint-disable-next-line es-x/no-optional-chaining, es-x/no-nullish-coalescing-operators
	data = data?.pages ?? [];

	for ( let i = 0; i < data.length; i++ ) {
		results[ i ] = {
			id: data[ i ].id,
			key: data[ i ].key,
			title: data[ i ].title,
			// eslint-disable-next-line es-x/no-symbol-prototype-description
			description: data[ i ].description
		};
		// Redirect title
		// Since 1.38
		if ( data[ i ].matched_title ) {
			results[ i ].matchedTitle = data[ i ].matched_title;
		}

		if ( data[ i ].thumbnail && data[ i ].thumbnail.url ) {
			results[ i ].thumbnail = data[ i ].thumbnail.url;
		}
	}

	return results;
};

module.exports = {
	getUrl: getUrl,
	convertDataToResults: convertDataToResults
};
