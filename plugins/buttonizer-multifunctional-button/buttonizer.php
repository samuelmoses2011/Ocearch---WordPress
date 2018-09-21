<?php
/*
Plugin Name: Buttonizer - Smart Floating Action Button
Plugin URI:  https://buttonizer.pro
Description: The Buttonizer is a new way to give a boost to your number of interactions, actions and conversions from your website visitor by adding one or multiple Customizable Smart Floating Button in the corner of your website.
Version:     1.5.2
Author:      Buttonizer
Author URI:  https://buttonizer.pro
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: wporg
*/
/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

Copyright 2017 Buttonizer
*/

// error_reporting(E_ALL);

define('BUTTONIZER_NAME', 'buttonizer');
define('BUTTONIZER_DIR', dirname(__FILE__));
define('BUTTONIZER_SLUG', basename(BUTTONIZER_DIR));
define('BUTTONIZER_PLUGIN_DIR', __FILE__ );
define('BUTTONIZER_VERSION','1.5.2');

# No script kiddies
defined( 'ABSPATH' ) or die('No script kiddies please!');

/* ================================================
 *     WELCOME TO THE BUTTONIZER SOURCE CODE!
 *
 *      We like to see that you are courious
 *       how the code is written. When you
 *       are here to try to resolve problems
 *        you must be carefully, anything
 *          can get broken you know...
 *
 *            -- KNOWLEDGE BASE --
 *        Did you know you can use our
 *             knowledge base?
 *              That's free!
 *
 *				    VISIT:
 *      https://knowledge.buttonizer.pro
 *
 *            -- BUGS FOUND? --
 *	   Are you here to look for a bug?
 *		Cool! If you found something
 *        you can report it to us!
 *
 *      Maybe you get a FREE license
 *           for 1 website ;)
 *
 * ================================================
 */

if(!class_exists("\\Buttonizer\\Utils\\Maintain")) {
    /* Install, deactivate and remove Buttonizer */
    require BUTTONIZER_DIR . '/classes/Utils/Maintain.php';

    // Main stuff
    require BUTTONIZER_DIR . '/classes/Button.php';

    // License stuff
    require BUTTONIZER_DIR . '/classes/Licensing/License.php';

    // Admin stuff
    require BUTTONIZER_DIR . '/classes/Admin/Admin.php';
}

/*
 * License setup
 */
$oButtonizer = new Buttonizer\Licensing\License();
$oButtonizer->init();

if(!function_exists("ButtonizerLicense")) {
    function ButtonizerLicense() {
        global $oButtonizer;

        return $oButtonizer->get();
    }
}

/*
 * Installation, removing and initiallization
 */
$oButtonizerMaintain = new Buttonizer\Utils\Maintain(true, 'init');

/*
 * Buttonizer Admin stuff or button
 */
if(is_admin()) {
    // Load Admin page
    new Buttonizer\Admin\Admin();
}
else
{
    // Buttonizer button
    new Buttonizer\Button($oButtonizerMaintain->isMobile());
}

/* LAST FEW FUNCTIONS */
if(!function_exists("buttonizer_custom_connect_message")) {
    function buttonizer_custom_connect_message(
        $message,
        $user_first_name,
        $plugin_title,
        $user_login,
        $site_link,
        $freemius_link
    ) {
        return sprintf(
            __( 'Hey %1$s' ) . ',<br>' .
            '<br />' .
            __( 'Click on Allow & Continue to start Buttonizing your website :)! Create Floating Action Buttons & Floating Menu\'s. Decide on a number of click actions like start chatting with Whatsapp, click-to-call, open a URL and more.') . '<br />' .
            '<br />' .
            __('Never miss an important update -- opt-in to our security and feature updates notifications​.') . '<br />' .
            '<br />' .
            __('​See you on the other side.')
            ,
            $user_first_name,
            '<b>' . $plugin_title . '</b>',
            '<b>' . $user_login . '</b>',
            $site_link,
            $freemius_link
        );
    }

    $oButtonizer->get()->add_filter('connect_message', 'buttonizer_custom_connect_message', 10, 6);
}

$oButtonizer->get()->add_action('after_uninstall', 'buttonizer_uninstall_cleanup');

// System, buttonizer is loaded
do_action('buttonizer_loaded');

// if(defined("BUTTONIZER_DEFINED")) {
//     echo "<div style='clear: both;'></div><img src='". plugins_url('/assets/buttonizer.png', BUTTONIZER_PLUGIN_DIR) ."' style='height: 40px; margin-right: 10px; float: left; vertical-align: top;'> Hey, looks like you try to install me (Buttonizer), while you already have an older version (or the free version) installed and activated? Deactivate all older Buttonizer plugins before trying again.<div style='clear: both; height: 20px'></div>";
//     trigger_error("Error", E_USER_ERROR);
//     exit;
// }

// Ok, define
define('BUTTONIZER_DEFINED','1.0');
