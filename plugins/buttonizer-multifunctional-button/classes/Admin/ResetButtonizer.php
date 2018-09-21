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

namespace Buttonizer\Admin;

# No script kiddies
defined( 'ABSPATH' ) or die('No script kiddies please!');

class ResetButtonizer {

    public function __construct() {

        $data     = (array) get_option('buttonizer_page_categories');
        

        if(isset($_GET['realReset']) && $_GET['realReset'] == md5(BUTTONIZER_DIR)) {
            $this->reset();

            echo '<h1>Bibbidi Bobbidi Boo! <!-- We make mistakes too! --></h1>';

            echo '<h2>The magic happened! Have fun with a clean Buttonizer!</h2>';
        }else{
            echo '<p><b>What happens when I click the red button at the right side?</b><br />
            What will happen is that the plugin will get a \'reset\'. All changes to Buttonizer will get reversed. From then you can start all-over again.</p>';

            echo '<p>&nbsp;</p>';

            echo '<p><b>Why would I do that?</b><br />
            There can be many reasons to reset <b>Buttonizer</b>. One could be, you ruined the Buttonizer buttons, the settings, categories. Or maybe you want to try it out?</p>';

            echo '<p>&nbsp;</p>';

            echo '<p><b>What about my license?</b><br />
           Your license won\'t be touched by this button. It only resets & remove: <ul>
            <li>- the Buttonizer buttons</li>
            <li>- the opening hours</li>
            <li>- general settings</li>
            <li>- page categories</li>
            </ul></p>';

            echo '<p>&nbsp;</p>';

            echo '<p><b>Okay, and then?</b><br />
            Then Buttonizer will get to the default values again. Like you just installed Buttonizer. Nothing more, nothing less. Everything back to normal.</p>';

        }
    }

    // Reset
    private function reset() {
        global $oButtonizerMaintain;

        update_option('buttonizer_buttons', '');
        update_option('buttonizer_opening_settings', '');
        update_option('buttonizer_general_settings', '');
        update_option('buttonizer_page_categories', '');

        $oButtonizerMaintain->pluginActivate();
    }
}
