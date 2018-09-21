<?php
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
namespace Buttonizer\Utils;

# No script kiddies
defined( 'ABSPATH' ) or die('No script kiddies please!');

class Maintain {

    // Construct
    public function __construct($sReady = false) {
        if(!$sReady) return;

		if (!session_id())
			session_start();

        register_activation_hook('buttonizer', array(&$this, 'pluginActivate'));
        register_deactivation_hook('buttonizer', array(&$this, 'pluginDeactivate'));

        add_action('upgrader_process_complete', array(&$this, 'pluginUpdated'), 10, 2);
    }

    /**
    * Activate Buttonizer, AWESOMAAH!
    */
    public function pluginActivate() {
        // Check updated data
        $this->pluginUpdated();

        /*
         * General settings
         */
        $aGeneralSettings = get_option('buttonizer_general_settings');

        // Check stored old data
        if($aGeneralSettings == ''){
            $aGeneralSettings = array(
                'button_unpushed' => '#48A4DC',
                'button_pushed' => '#1D9BDB',
                'icon_color' => '#FFFFFF',
                'icon_icon' => 'fa-plus',
                'position_right' => '5',
                'position_bottom' => '5',
                'google_analytics' => ''
            );

            // Update and save general Utils settings
            update_option('buttonizer_general_settings', $aGeneralSettings);
        }

        /*
         * Add Utils buttons
         */
        $aButtonizerDefaultButtons = get_option('buttonizer_buttons');

        // Check stored old data
        if($aButtonizerDefaultButtons == ''){
            $aButtonizerDefaultButtons = [];

            // Update and save general Utils settings
            update_option('buttonizer_buttons', $aButtonizerDefaultButtons);
        }

        /*
         * Opening settings
         */
        $aDefaultOpeningSettings = get_option('buttonizer_opening_settings');

        // Do we have old opening data?
        if($aDefaultOpeningSettings == '') {
            $aDefaultOpeningSettings =
            array(
                // Monday
                'monday_opened_from' => '10:00',
                'monday_closing_on' => '17:00',
                'monday_opened' => false,

                // Tuesday
                'tuesday_opened_from' => '10:00',
                'tuesday_closing_on' => '17:00',
                'tuesday_opened' => false,

                // Wednesday
                'wednesday_opened_from' => '10:00',
                'wednesday_closing_on' => '17:00',
                'wednesday_opened' => false,

                // Thursday
                'thursday_opened_from' => '10:00',
                'thursday_closing_on' => '17:00',
                'thursday_opened' => false,

                // Friday
                'friday_opened_from' => '10:00',
                'friday_closing_on' => '17:00',
                'friday_opened' => false,

                // Saturday
                'saturday_opened_from' => '10:00',
                'saturday_closing_on' => '17:00',
                'saturday_opened' => true,

                // Sunday
                'sunday_opened_from' => '10:00',
                'sunday_closing_on' => '17:00',
                'sunday_opened' => true,
            );

            // Update and save Utils opening settings
            update_option('buttonizer_opening_settings', $aDefaultOpeningSettings);
         }
    }

    /**
    * Deactivate plugin, SEE YOU SOON!
    */
    public function pluginDeactivate(){
        // Nothing to handle right now. Maybe later
    }

    /**
     * Updated?
     */
    public function pluginUpdated()
    {
        $pageCategories = get_option('buttonizer_page_categories');

        // Old version of the page categories, we need to renew it...
        if(isset($pageCategories['categorieOrder']))
        {
            // Empty all categories, sorry for this :'(
            update_option('buttonizer_page_categories', []);
        }
    }

    /*
     * Mobile or desktop check
     */
    public function isMobile() {
        if (
            isset($_SERVER['HTTP_USER_AGENT']) &&
            (
                strpos($_SERVER['HTTP_USER_AGENT'], 'Mobile') !== false ||
                strpos($_SERVER['HTTP_USER_AGENT'], 'Android') !== false ||
                strpos($_SERVER['HTTP_USER_AGENT'], 'Silk/') !== false ||
                strpos($_SERVER['HTTP_USER_AGENT'], 'Kindle') !== false ||
                strpos($_SERVER['HTTP_USER_AGENT'], 'BlackBerry') !== false ||
                strpos($_SERVER['HTTP_USER_AGENT'], 'Opera Mini') !== false ||
                strpos($_SERVER['HTTP_USER_AGENT'], 'Opera Mobi') !== false
            )
        )
        {
            // Is mobile
            return true;
        } else {
            // Is desktop
            return false;
        }
    }
}
