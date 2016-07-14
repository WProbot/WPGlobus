/* jshint node:true */
/*global require*/

/**
 * @link https://www.npmjs.com/package/grunt-text-replace
 */

/**
 * There is no support for Grunt templates in the `from:` replacement.
 * Therefore, we need to get the config variable ourselves.
 */
var pkgJson, tivwp_version_define;
pkgJson = require('../package.json');
//noinspection JSUnresolvedVariable
tivwp_version_define = pkgJson.tivwp_config.version.define;

module.exports = {
    version  : {
        overwrite   : true,
        src         : ['<%= package.name %>.php'],
        replacements: [
            {
                from: / \* Version: .+/,
                to  : ' * Version: <%= package.version %>'
            },
            {
                from: new RegExp("define\\( '(" + tivwp_version_define + ")'.+"),
                to  : "define( '$1', '<%= package.version %>' );"
            }
        ]
    },
    wpi18n   : {
        overwrite   : true,
        src         : ['node_modules/grunt-wp-i18n/vendor/wp-i18n-tools/extract.php'],
        replacements: [
            {
                from: /public function entry_from_call\( \$call, \$file_name \) \{.*/,
                to  : "public function entry_from_call( $call, $file_name ) { if ( $call['args'][ count( $call['args'] ) - 1 ] !== '<%= package.tivwp_config.text_domain %>' ) { return null; }"
            }
        ]
    },
    readme_md: {
        overwrite   : true,
        src         : ['README.md'],
        replacements: [
            {
                from: '![Multilingual WooCommerce store powered by [WooCommerce WPGlobus](http://www.wpglobus.com/shop/extensions/woocommerce-wpglobus/).](https://ps.w.org/wpglobus/assets/screenshot-8.png)',
                to  : '![Multilingual WooCommerce store powered by WooCommerce WPGlobus.](https://ps.w.org/wpglobus/assets/screenshot-8.png)'
            },
            {
                from: '# WPGlobus - Multilingual Everything! #',
                to  : '[![Latest Stable Version](https://poser.pugx.org/wpglobus/wpglobus/v/stable)](https://packagist.org/packages/wpglobus/wpglobus) [![Total Downloads](https://poser.pugx.org/wpglobus/wpglobus/downloads)](https://packagist.org/packages/wpglobus/wpglobus) [![Latest Unstable Version](https://poser.pugx.org/wpglobus/wpglobus/v/unstable)](https://packagist.org/packages/wpglobus/wpglobus) [![License](https://poser.pugx.org/wpglobus/wpglobus/license)](https://packagist.org/packages/wpglobus/wpglobus)\n\n# WPGlobus - Multilingual Everything! #'
            }
        ]
    }
};