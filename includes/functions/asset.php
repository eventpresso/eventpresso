<?php 
/**
 * Asset Functions
 *
 * Functions that handle assets
 *
 * @author 		Tor Morten Jensen
 * @category 	Core
 * @package 	EventPresso/Assets
 * @version     0.0.1
 */

/**	
 * Get the URL to an asset
 * @param  string $asset  
 * @param  string $url    
 * @param  string $folder 
 * @return string         
 */
function eventpresso_get_asset_url( $asset, $url = '', $folder = 'assets/' ) {

	if( ! $url ) {
		$url = EventPresso()->get_url();
	}

	$url = trailingslashit( $url );

	$folder = trailingslashit( $folder );

	return $url . $folder . $asset;

}

/**	
 * Print the URL to an asset
 * @param  string $asset  
 * @param  string $url    
 * @param  string $folder 
 * @return string         
 */
function eventpresso_asset_url( $asset, $url = '', $folder = 'assets/' ) {
	echo eventpresso_get_asset_url( $asset, $url, $folder );
}