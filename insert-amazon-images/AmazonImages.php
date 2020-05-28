<?php
/*
Plugin URL: https://www.amzimage.com
Plugin Name: Insert Amz Images
Description: Display Amazon product images using the Amazon Product Advertising API.
Version: 0.44
Date: May 16th, 2018
Author: AMZ Image
Author URL: https://www.amzimage.com
Licence: GNU General Public License v3.0
More info: http://www.gnu.org/copyleft/gpl.html
*/

namespace AmazonImages;

use AmazonImages\Amazon\ProductAdvertisingAPI\v1\ApiException;
use AmazonImages\Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\api\DefaultApi;
use AmazonImages\Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\GetItemsRequest;
use AmazonImages\Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\GetItemsResponse;
use AmazonImages\Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\ImageType;
use AmazonImages\Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\Item;
use AmazonImages\Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\ItemIdType;
use AmazonImages\Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\OfferListing;
use AmazonImages\Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\OfferSummary;
use AmazonImages\Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\PartnerType;
use AmazonImages\Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\ProductAdvertisingAPIClientException;
use AmazonImages\Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\SearchItemsRequest;
use AmazonImages\Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\SearchItemsResource;
use AmazonImages\Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\SearchItemsResponse;
use AmazonImages\Amazon\ProductAdvertisingAPI\v1\Configuration;
use AmazonImages\GeoIp2\Model\Country;
use AmazonImages\Services\CommonRequestParameters;

/* Make sure plugin remains secure if called directly */
if ( ! defined( 'ABSPATH' ) ) {
	if ( ! headers_sent() ) {
		@header( $_SERVER['SERVER_PROTOCOL'] . ' 403 Forbidden', true, 403 );
	}
	die( 'ERROR: This plugin requires WordPress and will not function if called directly.' );
}

// Check for required PHP version
if ( version_compare( PHP_VERSION, '5.6', '<' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	\deactivate_plugins( plugin_basename( __FILE__ ) );
	\add_action( 'admin_notices', function () {
		$html = '<div class="error">';
		$html .= '<p>';
		$html .= __( 'Plugin is not compatible with your php version (' . PHP_VERSION . ') . Required PHP 5.6+' );
		$html .= '</p>';
		$html .= '</div><!-- /.updated -->';
		echo( $html );
	} );
	unset( $_GET['activate'] );

	return false;
}


if ( ! class_exists( 'AmazonImages\AmazonImages' ) ) {
	class AmazonImages {
		const PLUGIN_NAME = 'Insert Amz Images';
		const VERSION = '0.44';
		const LICENSE_SERVER_URL = 'https://www.amzimage.com';

		protected $option_group = null;
		protected $geoip2_country_db = 'geoip2/GeoLite2-Country.mmdb';
		protected $api_secret_key = null;
		protected $api_access_key = null;

		/**
		 * AmazonImages constructor.
		 * @throws \ReflectionException
		 */
		public function __construct() {
			$this->option_group = ( new \ReflectionClass( AmazonImages::class ) )->getShortName();
			\add_action( 'admin_init', [ $this, 'adminInit' ] );
			\add_action( 'admin_menu', [ $this, 'addAdminMenu' ] );
			// check licence
			$licence_key       = \get_option( AmazonImages::class . 'Licence' );
			$licenceSecret_key = \get_option( AmazonImages::class . 'LicenceSecret' );
			if ( ! empty( $licence_key ) ) {

				$domain = sanitize_text_field( $_SERVER['SERVER_NAME'] );
				// check last checked time
				$lastchecked      = \get_option( AmazonImages::class . 'LicenceLastChecked' );
				$is_licence_valid = false;
				if ( empty( $lastchecked ) || ( (int) $lastchecked ) < ( time() - 60 * 60 * 24 ) ) {
					// check licence
					// check validation code
					$api_params = [
						'slm_action'  => 'slm_check',
						'secret_key'  => $licenceSecret_key,
						'license_key' => $licence_key,
						'domain'      => $domain

					];
					// Send query to the license manager server
					$response = \wp_remote_get( add_query_arg( $api_params, self::LICENSE_SERVER_URL ),
						[
							'timeout'   => 30,
							'sslverify' => false
						]
					);

					// Check for error in the response
					if ( is_wp_error( $response ) ) {
						$this->add_error_notice( 'License key verification failed.' );
					} else {
						$license_data = json_decode( wp_remote_retrieve_body( $response ) );
						if ( $license_data->result === 'error' ) {
							\update_option( AmazonImages::class . 'Licence', '' );
							\update_option( AmazonImages::class . 'LicenceSecret', '' );
							$this->add_error_notice( $license_data->message );
						} elseif ( $license_data->result === 'success' && $license_data->status !== 'active' ) {
							\update_option( AmazonImages::class . 'Licence', '' );
							\update_option( AmazonImages::class . 'LicenceSecret', '' );
							$this->add_error_notice( 'Sorry, your license key status is: ' . $license_data->status );
						} else {
							$is_licence_valid = true;
						}
					}
				} else {
					$is_licence_valid = true;
					// skip licence checking
				}

				if ( $is_licence_valid ) {
					require dirname( __FILE__ ) . '/vendor/autoload.php';
					require dirname( __FILE__ ) . '/services/CommonRequestParameters.php';
					// register actions
					\add_action( 'admin_enqueue_scripts', [ $this, 'add_scripts' ] );
					\add_action( 'media_buttons', [ $this, 'add_amazon_media_button' ] );
					\add_action( 'admin_footer', [ $this, 'add_amazon_search_form' ] );
					\add_filter( 'the_content', [ $this, 'check_links' ] );
					\add_action( 'wp_ajax_search_form_submit', [ $this, 'search_form_submit' ] );
					\add_action( 'wp_ajax_credentials_test', [ $this, 'credentials_test' ] );
					\add_filter( 'media_upload_tabs', [ $this, 'amazon_media_upload_tab' ] );
					\add_action( 'media_upload_amzimage', [ $this, 'amazon_media_upload_amzimage_content' ] );
					\add_action( 'enqueue_block_editor_assets', [ $this, 'add_amazon_media_button_gutenberg' ] );

					//update option
					\update_option( AmazonImages::class . 'LicenceLastChecked', time() );

					$this->setApiAccessKey( \get_option( AmazonImages::class . 'AccessKey' ) );
					$this->setApiSecretKey( \get_option( AmazonImages::class . 'SecretKey' ) );

					if ( ! file_exists( dirname( __FILE__ ) . '/' . $this->geoip2_country_db ) ) {
						\add_action( 'admin_notices', function () {
							$html = '<div class="error">';
							$html .= '<p>';
							$html .= __( 'GeoLite2 DB is not found. Check geoip2 folder in plugin directory. Download latest DB <a target="_blank" href="https://dev.maxmind.com/geoip/geoip2/geolite2/">here</a>.' );
							$html .= '</p>';
							$html .= '</div><!-- /.updated -->';
							echo( $html );
						} );
					}
				}
			} else {
				// show activation screen hook
				\add_action( 'admin_notices', function () {
					$html = '<div class="error">';
					$html .= '<p>';
					$html .= __( 'AmazonImages plugin requires license key. Go to <a href="options-general.php?page=' . ( new \ReflectionClass( AmazonImages::class ) )->getShortName() . '">Settings</a> page and enter license key.' );
					$html .= '</p>';
					$html .= '</div><!-- /.updated -->';
					echo( $html );
				} );

			}
		}


		protected function license_check() {

		}


		protected function add_error_notice( $message ) {
			\add_action( 'admin_notices', function () use ( $message ) {
				$html = '<div class="error">';
				$html .= '<p>';
				$html .= __( $message );
				$html .= '</p>';
				$html .= '</div><!-- /.updated -->';
				echo( $html );
			} );
			unset( $_GET['settings-updated'] ); // remove notice settings updated
		}

		public function add_scripts() {

			\wp_register_script( self::class . '_manager', \plugin_dir_url( __FILE__ ) . 'js/manager.js', [ 'jquery' ], self::VERSION );
			\wp_enqueue_script( self::class . '_manager' );
			// in JavaScript, object properties are accessed as ajax_object.ajax_url, ajax_object.we_value
			\wp_localize_script( self::class . '_manager', 'ajax_object',
				[
					'ajax_url' => admin_url( 'admin-ajax.php' ),
					'we_value' => 1234
				]
			);
		}

		/**
		 * Activate the plugin
		 */
		public static function activate() {
			// Display the admin notification

			\add_option( AmazonImages::class . 'AccessKey' );
			\add_option( AmazonImages::class . 'SecretKey' );
			\add_option( AmazonImages::class . 'TagIt' );
			\add_option( AmazonImages::class . 'TagDe' );
			\add_option( AmazonImages::class . 'TagUk' );
			\add_option( AmazonImages::class . 'TagEs' );
			\add_option( AmazonImages::class . 'TagFr' );
			\add_option( AmazonImages::class . 'TagUs' );
			\add_option( AmazonImages::class . 'TagCa' );
			\add_option( AmazonImages::class . 'TagIn' );
			\add_option( AmazonImages::class . 'TagAu' );
			\add_option( AmazonImages::class . 'UseGeoip', 'yes' );
			\add_option( AmazonImages::class . 'NoFollowLinks', 'yes' );
			\add_option( AmazonImages::class . 'Licence' );
			\add_option( AmazonImages::class . 'LicenceSecret' );
			\add_option( AmazonImages::class . 'LicenceLastChecked' );// check one time per 24 hour.

		}

		/**
		 * Deactivate the plugin
		 */
		public static function deactivate() {
			// Do nothing
		}

		public function adminInit() {
			// Set up the settings for this plugin
			$this->initSettings();
			add_thickbox();
		} //


		public function initSettings() {
			\register_setting( $this->option_group, AmazonImages::class . 'Licence', [ $this, 'licenceValidate' ] );
			\register_setting( $this->option_group, AmazonImages::class . 'LicenceLastChecked' );
			\register_setting( $this->option_group, AmazonImages::class . 'LicenceSecret' );
			\register_setting( $this->option_group, AmazonImages::class . 'AccessKey' );
			\register_setting( $this->option_group, AmazonImages::class . 'SecretKey' );
			\register_setting( $this->option_group, AmazonImages::class . 'TagIt' );
			\register_setting( $this->option_group, AmazonImages::class . 'TagDe' );
			\register_setting( $this->option_group, AmazonImages::class . 'TagUk' );
			\register_setting( $this->option_group, AmazonImages::class . 'TagEs' );
			\register_setting( $this->option_group, AmazonImages::class . 'TagFr' );
			\register_setting( $this->option_group, AmazonImages::class . 'TagUs' );
			\register_setting( $this->option_group, AmazonImages::class . 'TagCa' );
			\register_setting( $this->option_group, AmazonImages::class . 'TagIn' );
			\register_setting( $this->option_group, AmazonImages::class . 'TagAu' );
			\register_setting( $this->option_group, AmazonImages::class . 'UseGeoip' );
			\register_setting( $this->option_group, AmazonImages::class . 'NoFollowLinks' );
		}

		public function amazon_media_upload_tab( $tabs ) {
			$newtab = [ 'amzimage' => 'Insert From AMZ Image' ];

			return array_merge( $tabs, $newtab );
		}

		public function amazon_media_upload_amzimage_content() {
			\wp_iframe( [ $this, 'amazon_media_amzimage_form' ] );
		}

		function amazon_media_amzimage_form() {
			include( sprintf( "%s/add_amazon_images_media_content.php", dirname( __FILE__ ) ) );
		}


		public function add_amazon_media_button() {
			?>
            <a name="Search images on Amazon"
               href="#TB_inline?width=500px&height=500px&inlineId=insert-amazon-images-thickbox"
               id="insert-amazon-images-button" class="button thickbox">Add Amazon Images</a>
            <style>
                .block-editor-writing-flow {
                    height: auto;
                }
            </style>
			<?php
		}

		public function add_amazon_media_button_gutenberg() {
			?>
            <a name="Search images on Amazon"
               href="#TB_inline?width=500px&height=500px&inlineId=insert-amazon-images-thickbox"
               id="insert-amazon-images-button" class="button thickbox">Add Amazon Images</a>
			<?php
		}

		/**
		 * @param $input
		 *
		 * @return string
		 */
		public function licenceValidate( $input ) {
			$licence_key = sanitize_text_field( $input );

			return $licence_key;
		}

		public function add_amazon_search_form() {
			include( sprintf( "%s/add_amazon_images.php", dirname( __FILE__ ) ) );
		}

		public function addAdminMenu() {
			//if ( function_exists( 'add_options_page' ) ) {
			\add_options_page( self::PLUGIN_NAME, self::PLUGIN_NAME, 'manage_options', $this->option_group, [
				$this,
				'adminMenu'
			] );
			//}
		}

		public function credentials_test() {
			try {
				$locale     = sanitize_text_field( $_POST['locale'] );
				$tag        = sanitize_text_field( $_POST['tag'] );
				$secret_key = sanitize_text_field( $_POST['secret_key'] );
				$access_key = sanitize_text_field( $_POST['access_key'] );
				if ( empty( $locale ) ) {
					throw new \Exception( 'Missing locale parameter.' );
				}
				if ( empty( $tag ) ) {
					throw new \Exception( 'Missing Associate Tag parameter.' );
				}
				if ( empty( $secret_key ) ) {
					throw new \Exception( 'Missing Secret Key parameter.' );
				}
				if ( empty( $access_key ) ) {
					throw new \Exception( 'Missing Access Key parameter.' );
				}
				$this->setApiAccessKey( $access_key );
				$this->setApiSecretKey( $secret_key );
				$this->search( 'test', 'All', $locale, $tag );
				$data = 'Credentials test has been done successfully.';
				\wp_die( json_encode( $data ) );
				/*
				if ( $response->Items->Request->IsValid == 'True' ) {
					$data = 'Credentials test has been done successfully.';
					\wp_die( json_encode( $data ) );
				} else {
					throw new \Exception( 'Credentials test failed.' );
				}
				*/
			} catch ( \Exception $e ) {
				\wp_die( 'Credentials test failed: ' . $e->getMessage(), null, 400 );
			}
		}

		public function search_form_submit() {
			// check action
			try {
				$keyword     = sanitize_text_field( $_POST['keyword'] );
				$searchIndex = sanitize_text_field( $_POST['search_index'] );
				$locale      = sanitize_text_field( $_POST['locale'] );
				$asin        = sanitize_text_field( $_POST['asin'] );
				if ( ! empty( $_POST['action'] ) && $_POST['action'] === 'search_form_submit' ) {
					if ( ! empty( $asin ) ) {
						$response = $this->lookup( $asin, $locale );
						if ( empty( $response->getItemsResult() ) ) {
							throw new \Exception( 'Product is not found.' );
						}
						$data   = [];
						$data[] = $this->convertProductToResponseArray( $response->getItemsResult()->getItems()[0], $locale );
						\wp_die( json_encode( $data ) );
					} elseif ( ! empty( $keyword ) ) {
						$response = $this->search( $keyword, $searchIndex, $locale );
						if ( $response->getSearchResult()->getTotalResultCount() === 0 ) {
							throw new \Exception( 'Products is not found.' );
						}
						$data = [];
						foreach ( $response->getSearchResult()->getItems() as $product ) {
							/* @var $product  Item */
							$data[] = $this->convertProductToResponseArray( $product, $locale );
						}
						\wp_die( json_encode( $data ) );
					} else {
						throw new \Exception( 'Please put ASIN or Keyword' );
					}
				}
			} catch ( \Exception $e ) {
				\wp_die( $e->getMessage(), null, 400 );
			}
		}

		/**
		 * @param $content
		 *
		 * @return string
		 * @throws \AmazonImages\GeoIp2\Exception\AddressNotFoundException
		 * @throws \AmazonImages\MaxMind\Db\Reader\InvalidDatabaseException
		 */
		public function check_links( $content ) {
			if ( \get_option( AmazonImages::class . 'UseGeoip' ) === 'yes' ) {
				$geoip_db_path = dirname( __FILE__ ) . '/' . $this->geoip2_country_db;
				if ( file_exists( $geoip_db_path ) ) {
					// check if data-amazonimages link exists.
					if ( preg_match( '#<a.*data-amazonimages[^>]*>#siUu', $content ) ) {
						// if data-amazonimages links found, then geoip apply.
						try {
							$reader = new \AmazonImages\GeoIp2\Database\Reader( $geoip_db_path );
							$record = $reader->country( $_SERVER['REMOTE_ADDR'] );
							/** @var $record Country */
							$country = $record->country;
							/** @var $country \AmazonImages\GeoIp2\Record\Country */
							$tag           = $this->selectAssociateTag( $country->isoCode );
							$amazon_locale = $this->selectLocale( $country->isoCode );

							if ( ! empty( $amazon_locale ) && ! empty( $tag ) ) {
								// change data-amazonimages links to current locale
								$content = preg_replace_callback( '#<a.*data-amazonimages[^>]*>#',
									function ( $matches ) use ( $tag, $amazon_locale ) {
										$line = preg_replace( '#www\.amazon\.([^/]+?)#siUu', 'www.amazon.' . $amazon_locale, $matches[0] );

										return preg_replace( '#href="(.+)tag=[^"]+"#siUu', 'href="$1tag=' . $tag . '"', $line );
									},
									$content
								);

							}
						} catch ( \Exception $e ) {
							// do nothing
						}
					}
				}
				// do nothing if geoip db is not exists.
			}

			return $content;
		}

		/**
		 * @param Item $item
		 * @param $locale
		 *
		 * @return array
		 * @throws \Exception
		 */
		protected function convertProductToResponseArray( Item $item, $locale ) {
			$price           = null;
			$sale_price      = null;
			$currency        = null;
			$listings        = $item->getOffers()->getListings();
			$is_buybox_found = false;
			if ( count( $listings ) > 0 ) {
				foreach ( $listings as $listing ) {
					/** @var OfferListing $listing */
					if ( $listing->getIsBuyBoxWinner() ) {
						if ( ! empty( $listing->getPrice() ) ) {
							if ( ! empty( $listing->getPrice()->getSavings() ) ) {
								$sale_price = $listing->getPrice()->getAmount();
								$price      = $listing->getPrice()->getAmount() + $listing->getPrice()->getSavings()->getAmount();
							} else {
								$price = $listing->getPrice()->getAmount();
							}
							$currency = $listing->getPrice()->getCurrency();
						}
						$is_buybox_found = true;
						break;
					}
				}
			}
			// get lowest price if bubox not found
			if ( ! $is_buybox_found ) {
				/** @var OfferSummary $summary */
				$summary = $item->getOffers()->getSummaries()[0];
				if ( ! empty( $summary->getLowestPrice() ) ) {
					if ( ! empty( $summary->getLowestPrice()->getSavings() ) ) {
						$sale_price = $summary->getLowestPrice()->getAmount();
						$price      = $summary->getLowestPrice()->getAmount() + $summary->getLowestPrice()->getSavings()->getAmount();
					} else {
						$price = $summary->getLowestPrice()->getAmount();
					}
					$currency = $summary->getLowestPrice()->getCurrency();
				}
			}

			$small_images  = [];
			$medium_images = [];
			$large_images  = [];
			foreach ( $item->getImages()->getVariants() as $variant ) {
				/** @var ImageType $variant */
				$small_images[]  = $variant->getSmall()->getURL();
				$medium_images[] = $variant->getMedium()->getURL();
				$large_images[]  = $variant->getlarge()->getURL();
			}
			$response                  = [];
			$response['associate_tag'] = $this->selectAssociateTag( $locale );
			$response['asin']          = $item->getASIN();
			if ( ! empty( $item->getImages() ) && ! empty( $item->getImages()->getPrimary() ) ) {
				$response['small_image_url']  = $item->getImages()->getPrimary()->getSmall()->getURL();
				$response['medium_image_url'] = $item->getImages()->getPrimary()->getMedium()->getURL();
				$response['large_image_url']  = $item->getImages()->getPrimary()->getLarge()->getURL();
			} else {
				$response['small_image_url']  = null;
				$response['medium_image_url'] = null;
				$response['large_image_url']  = null;
			}
			$response['title']      = $item->getItemInfo()->getTitle()->getDisplayValue();
			$response['price']      = $price;
			$response['sale_price'] = $sale_price;
			if ( ! empty( $item->getBrowseNodeInfo() ) && ! empty( $item->getBrowseNodeInfo()->getWebsiteSalesRank() ) ) {
				$response['salesrank'] = $item->getBrowseNodeInfo()->getWebsiteSalesRank()->getSalesRank();
			} else {
				$response['salesrank'] = null;
			}
			$response['currency'] = $currency;
			if ( ! empty( $item->getItemInfo()->getByLineInfo() ) && ! empty( $item->getItemInfo()->getByLineInfo()->getBrand() ) ) {
				$response['brand'] = $item->getItemInfo()->getByLineInfo()->getBrand()->getDisplayValue();
			} else {
				$response['brand'] = null;
			}
			$response['locale'] = $locale;
			$response['images'] = [
				'small'  => $small_images,
				'medium' => $medium_images,
				'large'  => $large_images,
			];

			return $response;
		}


		public function adminMenu() {
			if ( ! \current_user_can( 'manage_options' ) ) {
				\wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
			}
			if ( ! empty( \get_option( AmazonImages::class . 'Licence' ) ) ) {
				include( sprintf( "%s/settings.php", dirname( __FILE__ ) ) );
			} else {
				include( sprintf( "%s/licence.php", dirname( __FILE__ ) ) );
			}
		}

		protected function selectLocale( $Locale ) {
			$Locale = strtolower( $Locale );
			switch ( $Locale ) {
				case "de":
				case "it":
				case "fr":
				case "es":
				case "ca":
				case "in":
					break;
				case "uk":
				case 'gb':
				case "co.uk":
					$Locale = "co.uk";
					break;
				case "us":
				case "com":
					$Locale = "com";
					break;
				case "au":
				case "com.au":
					$Locale = "com.au";
					break;
				default:
					$Locale = '';
			}

			return $Locale;
		}

		/**
		 * @param string $locale
		 *
		 * @return mixed
		 * @throws \Exception
		 */
		protected function selectAssociateTag( $locale ) {
			$locale = strtolower( $locale );
			switch ( $locale ) {
				case "de":
				case "it":
				case "fr":
				case "es":
				case "ca":
				case "in":
					return \get_option( AmazonImages::class . 'Tag' . ucfirst( strtolower( $locale ) ) );
				case "uk":
				case "gb":
				case "co.uk":
					return \get_option( AmazonImages::class . 'TagUk' );
				case "us":
				case "com":
					return \get_option( AmazonImages::class . 'TagUs' );
				case "au":
				case "com.au":
					return \get_option( AmazonImages::class . 'TagAu' );
				default:
					return '';
			}
		}

		protected function sksort( &$array, $subkey = "id", $sort_ascending = false ) {
			$temp_array = [];
			if ( count( $array ) ) {
				$temp_array[ key( $array ) ] = array_shift( $array );
			}
			foreach ( $array as $key => $val ) {
				$offset = 0;
				$found  = false;
				foreach ( $temp_array as $tmp_key => $tmp_val ) {
					if ( ! $found and strtolower( $val[ $subkey ] ) > strtolower( $tmp_val[ $subkey ] ) ) {
						$temp_array = array_merge( (array) array_slice( $temp_array, 0, $offset ),
							array( $key => $val ),
							array_slice( $temp_array, $offset )
						);
						$found      = true;
					}
					$offset ++;
				}
				if ( ! $found ) {
					$temp_array = array_merge( $temp_array, array( $key => $val ) );
				}
			}
			if ( $sort_ascending ) {
				$array = array_reverse( $temp_array );
			} else {
				$array = $temp_array;
			}
		}

		/**
		 * @param string $asin
		 * @param string $locale
		 *
		 * @return GetItemsResponse
		 * @throws \Exception
		 */
		protected function lookup( $asin, $locale = 'com' ) {
			try {
				$tag        = $this->selectAssociateTag( $locale );
				$access_key = $this->getApiAccessKey();
				$secret_key = $this->getApiSecretKey();
				$config     = new Configuration();
				$config->setAccessKey( $access_key );
				$config->setSecretKey( $secret_key );
				$common_request_parameters = new CommonRequestParameters( $locale );
				$config->setHost( $common_request_parameters->getHost() );
				$config->setRegion( $common_request_parameters->getRegion() );
				$apiInstance     = new DefaultApi( new GuzzleHttp\Client(), $config );
				$resources       = $this->getApiResources();
				$getItemsRequest = new GetItemsRequest();
				$getItemsRequest->setItemIds( [ $asin ] );
				$getItemsRequest->setItemIdType( ItemIdType::ASIN );
				$getItemsRequest->setPartnerTag( $tag );
				$getItemsRequest->setPartnerType( PartnerType::ASSOCIATES );
				$getItemsRequest->setResources( $resources );
				# Validating request
				$invalidPropertyList = $getItemsRequest->listInvalidProperties();
				$length              = count( $invalidPropertyList );
				if ( $length > 0 ) {
					$error  = "Error forming the request: ";
					$errors = [];
					foreach ( $invalidPropertyList as $invalidProperty ) {
						$errors[] = $invalidProperty;
					}
					$error .= implode( ', ', $errors );
					throw new \Exception( $error );
				}
				$getItemsResponse = $apiInstance->getItems( $getItemsRequest );
				if ( $getItemsResponse->getErrors() != null ) {
					throw new \Exception( $getItemsResponse->getErrors()[0]->getMessage(), $getItemsResponse->getErrors()[0]->getCode() );
				}

				return $getItemsResponse;
			} catch ( GuzzleHttp\Exception\ClientException $e ) {
				throw new \Exception( $e->getResponse()->getBody()->getContents() );
			} catch ( GuzzleHttp\Exception\ServerException $e ) {
				throw new \Exception( $e->getResponse()->getReasonPhrase() );
			} catch ( \Exception $e ) {
				throw $e;
			}
		}

		/**
		 * @param string $keyword
		 * @param string $search_index
		 * @param string $locale
		 *
		 * @return SearchItemsResponse
		 * @throws \Exception
		 */
		protected function search( $keyword, $search_index, $locale = 'com', $tag = null ) {
			try {
				if ( empty( $tag ) ) {
					$tag = $this->selectAssociateTag( $locale );
				}
				$access_key = $this->getApiAccessKey();
				$secret_key = $this->getApiSecretKey();
				$config     = new Configuration();
				$config->setAccessKey( $access_key );
				$config->setSecretKey( $secret_key );
				$common_request_parameters = new CommonRequestParameters( $locale );
				$config->setHost( $common_request_parameters->getHost() );
				$config->setRegion( $common_request_parameters->getRegion() );
				$apiInstance = new DefaultApi( new GuzzleHttp\Client(), $config );
				$resources   = $this->getApiResources();
				# Forming the request
				$searchItemsRequest = new SearchItemsRequest();
				$searchItemsRequest->setSearchIndex( $search_index );
				$searchItemsRequest->setKeywords( $keyword );
				//$searchItemsRequest->setItemCount();
				$searchItemsRequest->setPartnerTag( $tag );
				$searchItemsRequest->setPartnerType( PartnerType::ASSOCIATES );
				$searchItemsRequest->setResources( $resources );

				# Validating request
				$invalidPropertyList = $searchItemsRequest->listInvalidProperties();
				$length              = count( $invalidPropertyList );
				if ( $length > 0 ) {
					$error  = "Error forming the request: ";
					$errors = [];
					foreach ( $invalidPropertyList as $invalidProperty ) {
						$errors[] = $invalidProperty;
					}
					$error .= implode( ', ', $errors );
					throw new \Exception( $error );
				}
				$searchItemsResponse = $apiInstance->searchItems( $searchItemsRequest );
				if ( $searchItemsResponse->getErrors() != null ) {
					throw new \Exception( $searchItemsResponse->getErrors()[0]->getMessage(), $searchItemsResponse->getErrors()[0]->getCode() );
				}

				return $searchItemsResponse;
			} catch ( GuzzleHttp\Exception\ClientException $e ) {
				throw new \Exception( $e->getResponse()->getBody()->getContents() );
			} catch ( GuzzleHttp\Exception\ServerException $e ) {
				throw new \Exception( $e->getResponse()->getReasonPhrase() );
			} catch ( ApiException $exception ) {
				$errors = [];
				//$errors[] = "Error calling PA-API 5.0!";
				//$errors[] = "HTTP Status Code: " . $exception->getCode();
				//$errors[] = "Error Message: " . $exception->getMessage();
				if ( $exception->getResponseObject() instanceof ProductAdvertisingAPIClientException ) {
					$response_errors = $exception->getResponseObject()->getErrors();
					foreach ( $response_errors as $error ) {
						$errors[] = "Error Type: " . $error->getCode();
						$errors[] = "Error Message: " . $error->getMessage();
					}
				} else {
					$errors[] = "Error response body: " . $exception->getResponseBody();
				}
				throw new \Exception( implode( ', ', $errors ) );
			} catch ( \Exception $e ) {
				throw $e;
			}
		}

		/**
		 * @param null $api_secret_key
		 */
		public function setApiSecretKey( $api_secret_key ) {
			$this->api_secret_key = $api_secret_key;
		}

		/**
		 * @return null
		 */
		public function getApiSecretKey() {
			return $this->api_secret_key;
		}

		/**
		 * @param null $api_access_key
		 */
		public function setApiAccessKey( $api_access_key ) {
			$this->api_access_key = $api_access_key;
		}

		/**
		 * @return null
		 */
		public function getApiAccessKey() {
			return $this->api_access_key;
		}

		/**
		 * @return array
		 */
		public function getApiResources() {
			/*
             * Choose resources you want from SearchItemsResource enum
             * For more details, refer: https://webservices.amazon.com/paapi5/documentation/search-items.html#resources-parameter
            */
			return [
				SearchItemsResource::ITEM_INFOTITLE,
				SearchItemsResource::OFFERSLISTINGSPRICE,
				SearchItemsResource::OFFERSLISTINGSIS_BUY_BOX_WINNER,
				SearchItemsResource::OFFERSLISTINGSSAVING_BASIS,
				SearchItemsResource::OFFERSSUMMARIESHIGHEST_PRICE,
				SearchItemsResource::OFFERSSUMMARIESLOWEST_PRICE,
				SearchItemsResource::IMAGESPRIMARYLARGE,
				SearchItemsResource::IMAGESPRIMARYMEDIUM,
				SearchItemsResource::IMAGESPRIMARYSMALL,
				SearchItemsResource::IMAGESVARIANTSSMALL,
				SearchItemsResource::IMAGESVARIANTSMEDIUM,
				SearchItemsResource::IMAGESVARIANTSLARGE,
				SearchItemsResource::ITEM_INFOPRODUCT_INFO,
				SearchItemsResource::BROWSE_NODE_INFOWEBSITE_SALES_RANK,
				SearchItemsResource::ITEM_INFOBY_LINE_INFO
			];
		}
	}
}

if ( class_exists( AmazonImages::class ) ) {
	try {
		$class_shortname = ( new \ReflectionClass( AmazonImages::class ) )->getShortName();
		\register_activation_hook( __FILE__, [ AmazonImages::class, 'activate' ] );
		\register_deactivation_hook( __FILE__, [ AmazonImages::class, 'deactivate' ] );
		$amazon_images = new AmazonImages();
		// Add a link to the settings page onto the plugin page
		if ( isset( $amazon_images ) ) {
			// Add the settings link to the plugins page
			function plugin_settings_link( $links ) {
				//$settings_link = '<a href="options-general.php?page=AmazonImages">Settings</a>';
				$settings_link = '<a href="options-general.php?page=' . ( new \ReflectionClass( AmazonImages::class ) )->getShortName() . '">Settings</a>';
				array_unshift( $links, $settings_link );

				return $links;
			}

			$plugin = \plugin_basename( __FILE__ );
			\add_filter( "plugin_action_links_$plugin", $class_shortname . '\plugin_settings_link' );
		}
	} catch ( \Exception $e ) {
		echo $e->getMessage();
	}
}