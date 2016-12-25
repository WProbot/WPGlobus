/**
 * WPGlobus for Yoast Seo Premium 3.9.0
 * Interface JS functions
 *
 * @since 1.7.2
 *
 * @package WPGlobus
 */
/*jslint browser: true*/
/*global jQuery, console, wpseoReplaceVarsL10n, YoastSEO, WPGlobusVendor, WPGlobusCore, WPGlobusCoreData, WPGlobusYoastSeo*/

//var WPGlobusYoastSeoPremium = false;
jQuery(document).ready( function ($) {
	'use strict';
	
	if ( 'undefined' === typeof wpseoReplaceVarsL10n ) {
		return;	
	}		
	
	if ( 'undefined' === typeof WPGlobusCoreData ) {
		return;
	}

	if ( 'undefined' === typeof WPGlobusVendor ) {
		return;
	}
	
	if ( 'undefined' === typeof WPGlobusYoastSeoPremium ) {
		return;
	}
	
	if ( 'edit-tags.php' == WPGlobusVendor.pagenow || 'term.php' == WPGlobusVendor.pagenow ) {
		/**
		 * Reserve now.
		 */
	} else {
		/**
		 * pagenow is in [ 'post.php', 'post-new.php' ] 
		 */
		if ( 'undefined' !== typeof WPGlobusVendor.vendor.WPSEO_PREMIUM && WPGlobusVendor.vendor.WPSEO_PREMIUM ) {
			/**
			 * WPGlobusYoastSeoPremium.
			 * 
			 * @since 1.7.2
			 * @todo add doc 
			 * @see #yoast_wpseo_focuskeywords hidden field to store extra keywords.
			 *
			 * @see 	label = keyword.length > 0 ? keyword : wpseoPostScraperL10n.enterFocusKeyword;
			 */
			let api = WPGlobusYoastSeoPremium = {
				dLang			: WPGlobusCoreData.default_language,
				keywordButton	: null,
				focuskeywords	: '',
				observer		: null,
				observerInterval: null,
				observerSelector: '',
				observerStart	: false,
				scoreClass		: 'bad ok good na 100',
				init: function() {
					/**
					 * @see wpglobus-admin-47.js to run.
					 */
					api.observerSelector = '#wpseo-metabox-tabs_'+api.dLang; 
					api.keywordButton 	= $('#wpseo-meta-section-content li.wpseo-tab-add-keyword').detach();
					api.bindKeywordRemoveOrig();
					api.initFocuskeywords();
					
					setTimeout( api.setScores, 2000 );
					api.observerInterval = setInterval( api.addObserver, 2000 );
					
				},
				setScores: function() {
					if ( WPGlobusYoastSeo.wpseoTab != WPGlobusCoreData.default_language ) {
						let link 	= $('#wpseo-metabox-tabs_'+WPGlobusYoastSeo.wpseoTab+'  .wpglobus-wpseo_keyword_tab a').eq(0);
						let score	= link.find('.wpseo-score-icon');
						/**
						 * Get class from default language tab.
						 */
						let defaultLink = $(api.observerSelector + ' .wpseo_keyword_tab a').eq(0);
						score.removeClass(api.scoreClass);
						score.addClass( defaultLink.data('score') );						
					} else {
						$(api.observerSelector + ' .wpseo_keyword_tab a').each(function(i,el) {
							let link  = $(el);
							let score = link.find('.wpseo-score-icon');
							score.removeClass(api.scoreClass);
							score.addClass( link.data('score') );
						});	
					}
					if ( api.observerStart ) {
						api.startObserver();
					}
					api.observerStart = false;					
				},
				addObserver: function() {
					/**
					 * Now are observing default language only.
					 */
					if ( $( '#wpseo-metabox-tabs_'+api.dLang ).length == 0 ) {
						return;
					}
					clearInterval( api.observerInterval );
					let counter = 0;
					api.observer = new MutationObserver( function( mutations ) {
						api.observer.disconnect(); 
						api.observerStart = true;
						/**
						 * don't remove, for testing.
					     * console.log( 'counter: ' + counter );
						 */
						setTimeout( api.setScores, 2000 );
						counter++;
					});
					api.startObserver();
				},
				startObserver: function() {
					api.observer.observe(
						document.querySelector( api.observerSelector ),
							{ childList: true } 
					);					
				},
				initFocuskeywords: function() {
					setTimeout( api.setFocuskeywords, 5000 );
				},
				setFocuskeywords: function() {
					$.each( WPGlobusCoreData.enabled_languages, function( i, lang ) {
						if ( lang == api.dLang ) {
							/**
							 * Do nothing.
							 */
						} else {
							/**
							 * Extra language.
							 */
							let keywords = $('wpseo-tab-'+lang).val();
							if ( 'undefined' === typeof keywords || '' == keywords ) {
								$('#yoast_wpseo_focuskeywords_'+lang).val('');
							} else {
								$('#yoast_wpseo_focuskeywords_'+lang).val(keywords);
							}
						}
					});
				},
				addKeywordButton: function(){
					$('#wpseo-tab-'+api.dLang+' #wpseo-metabox-tabs_'+api.dLang).append( api.keywordButton );
				},
				bindKeywordRemoveOrig: function(){
					/**
					 * Remove extra keywords for default languages only.
					 * @since 1.7.2
					 *
					 * @see YoastMultiKeyword.prototype.bindKeywordRemove
					 */
					$(document).on( "click", "#wpseo-metabox-tabs_"+api.dLang+" .remove-keyword", function (ev) {

						var previousTab, currentTab;
						
						currentTab = $(ev.currentTarget).parent("li");
						previousTab = currentTab.prev();
						currentTab.remove();
						
						/**
						 * If the removed tab was active we should make a different one active.
						 */
						if (currentTab.hasClass("active")) {
							previousTab.find(".wpseo_tablink").click();
						}
						
						/**
						 * Enabled addKeywordButton. 
						 * @see YoastMultiKeyword.prototype.updateUI
						 */
						var $addKeywordButton = $(".wpseo-add-keyword");
						$addKeywordButton.prop("disabled", false).attr("aria-disabled", "false");
						
						let keywords = WPGlobusYoastSeo.getKeywordsOrig();
						
						// Exclude empty keywords.
						keywords = _.filter(keywords, function (item) {
							return item.keyword.length > 0;
						});

						if (0 === keywords.length) {
							keywords.push({ keyword: "", score: 0 });
						}

						if (keywords.length > 0) {
							let firstKeyword = keywords.splice(0, 1).shift();
						}						
						$( '#yoast_wpseo_focuskeywords' ).val( JSON.stringify(keywords) );
					});
				}		
			}
			 
		}
		
	}	 /** endif WPGlobusVendor.pagenow */	
	
});