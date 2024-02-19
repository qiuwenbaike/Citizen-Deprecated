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

use MediaWiki\MediaWikiServices;
use MediaWiki\Title\Title;
use MWTimestamp;
use SpecialPage;
use User;
use Wikimedia\IPUtils;

/**
 * Tagline partial of Skin Citizen
 */
final class Tagline extends Partial {

	/**
	 * Get tagline message
	 *
	 * @return string
	 */
	public function getTagline() {
		$skin = $this->skin;
		$out = $this->out;
		$title = $this->title;

		$shortdesc = $out->getProperty( 'shortdesc' );
		$tagline = '';

		if ( $title ) {
			// Use short description if there is any
			// from Extension:ShortDescription
			if ( $shortdesc ) {
				$tagline = $shortdesc;
			} else {
				$namespaceText = $title->getNsText();
				// Check if namespaceText exists
				// Return null if main namespace or not defined
				if ( $namespaceText ) {
					$msg = $skin->msg( 'citizen-tagline-ns-' . strtolower( $namespaceText ) );
					// Use custom message if exists
					if ( !$msg->isDisabled() ) {
						$tagline = $msg->parse();
					} else {
						if ( $title->isSpecialPage() ) {
							// No tagline if special page
							$tagline = '';
						} elseif ( $title->isTalkPage() ) {
							// Use generic talk page message if talk page
							$tagline = $skin->msg( 'citizen-tagline-ns-talk' )->parse();
						} elseif ( !$skin->msg( 'citizen-tagline' )->isDisabled() ) {
							$tagline = $skin->msg( 'citizen-tagline' )->parse();
						} else {
							// Fallback to site tagline
							$tagline = $skin->msg( 'tagline' )->text();
						}
					}
				} elseif ( !$skin->msg( 'citizen-tagline' )->isDisabled() ) {
					$tagline = $skin->msg( 'citizen-tagline' )->parse();
				} else {
					$tagline = $skin->msg( 'tagline' )->text();
				}
			}
		}

		// Apply language variant conversion
		if ( !empty( $tagline ) ) {
			$services = MediaWikiServices::getInstance();
			$langConv = $services
					->getLanguageConverterFactory()
					->getLanguageConverter( $services->getContentLanguage() );
			$tagline = $langConv->convert( $tagline );
		}

		return $tagline;
	}

	/**
	 * Return new User object based on username or IP address.
	 * Based on MinervaNeue
	 *
	 * @param Title $title
	 * @return User|null
	 */
	private function buildPageUserObject( $title ) {
		$titleText = $title->getText();
		$user = $this->user;

		if ( IPUtils::isIPAddress( $titleText ) ) {
			return $user->newFromAnyId( null, $titleText, null );
		}

		$userIdentity = MediaWikiServices::getInstance()->getUserIdentityLookup()->getUserIdentityByName( $titleText );
		if ( $userIdentity && $userIdentity->isRegistered() ) {
			return $user->newFromId( $userIdentity->getId() );
		}

		return null;
	}
}
