<?php
/**
 * Citizen - A responsive skin developed for the Star Citizen Wiki
 *
 * This file is part of Citizen.
 *
 * Citizen is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Citizen is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Citizen.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @file
 * @ingroup Skins
 */

declare( strict_types=1 );

namespace MediaWiki\Skins\Citizen\Partials;

use Exception;
use ExtensionRegistry;
use MWException;
use Skin;
use SpecialPage;

/**
 * Drawer partial of Skin Citizen
 * Generates the following partials:
 * - Logo
 * - Drawer
 *   + Special Pages Link
 *   + Upload Link
 */
final class Drawer extends Partial {
	/**
	 * Decorate sidebar template data
	 *
	 * @return array
	 * @throws Exception
	 */
	public function decorateSidebarData( $sidebarData ) {
		// Enable label for first portlet
		$sidebarData['data-portlets-first']['has-label'] = true;

		$globalToolsId = $this->getConfigValue( 'CitizenGlobalToolsPortlet' );
		$globalToolsHtml = $this->getGlobalToolsHTML();
		$globalToolsAdded = false;

		// Attach global tools to first portlet if empty
		// TODO: Remove this hack when Desktop Improvements separate article and global tools
		if ( empty( $globalToolsId ) ) {
			$sidebarData['data-portlets-first']['html-items'] .= $globalToolsHtml;
			$globalToolsAdded = true;
		}

		for ( $i = 0; $i < count( $sidebarData['array-portlets-rest'] ); $i++ ) {
			// Enable label for other portlet
			$sidebarData['array-portlets-rest'][$i]['has-label'] = true;

			switch ( $sidebarData['array-portlets-rest'][$i]['id'] ) {
				// Remove toolbox since it is handled by page tools
				case 'p-tb': {
					unset( $sidebarData['array-portlets-rest'][$i] );
					break;
				}

				case $globalToolsId: {
					// Attach global tools to portlet with matching ID
					$sidebarData['array-portlets-rest'][$i]['html-items'] .= $globalToolsHtml;
					break;
				}
			}
		}

		return $sidebarData;
	}

	/**
	 * Build global tools HTML
	 * We removed some global tools from TOOLBOX, now add it back
	 *
	 * TODO: Remove this hack when Desktop Improvements separate article and global tools
	 *
	 * @return string RawHTML
	 * @throws MWException
	 */
	private function getGlobalToolsHTML(): string {
		$skin = $this->skin;

		$html = '';

		// Special pages
		$html .= $skin->makeListItem( 'specialpages', [
			'href' => Skin::makeSpecialUrl( 'Specialpages' ),
			'id' => 't-specialpages',
			'link-class' => 'mw-ui-icon-wikimedia-specialPages'
		] );

		// Upload file
		if ( isset( $this->getConfigValue( 'UploadNavigationUrl' ) ) ) {
			$uploadHref = $this->getConfigValue( 'UploadNavigationUrl' );
		} else if ( ExtensionRegistry::getInstance()->isLoaded( 'Upload Wizard' ) ) {
			// Link to Upload Wizard if present
			$uploadHref = SpecialPage::getTitleFor( 'UploadWizard' )->getLocalURL();
		} else {
			// Link to old upload form
			$uploadHref = Skin::makeSpecialUrl( 'Upload' );
		}
		$html .= $skin->makeListItem( 'upload', [
			'href' => $uploadHref,
			'id' => 't-upload',
			'link-class' => 'mw-ui-icon-wikimedia-upload'
		] );

		return $html;
	}
}
