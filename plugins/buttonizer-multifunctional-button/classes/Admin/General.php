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
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
class General
{
    private  $aSavedData = array() ;
    private  $aSubCatagories = array() ;
    private  $aSubPages = array() ;
    private  $aFontAwesome = array() ;
    private  $oIconManager = array() ;
    private  $bHasProFeature = false ;
    public function __construct( $aFontAwesome, $oIconManager )
    {
        $this->aFontAwesome = $aFontAwesome;
        $this->oIconManager = $oIconManager;
        $this->loadData();
        $this->registerDesignSettings();
        $this->registerPlacingSettings();
        $this->registerAdvancedSettings();
        $this->registerSocialSettings();
        $this->registerOther();
        $this->generatePage();
    }
    
    private function loadData()
    {
        $this->aSavedData = (array) get_option( 'buttonizer_general_settings' );
    }
    
    private function generatePage()
    {
        $sTabs = '';
        $sTabsContent = '';
        $bFirstTab = true;
        // Get tabs
        foreach ( $this->aSubCatagories as $sKey => $sTitle ) {
            $sTabs .= '<a href="#tab_' . $sKey . '" onclick="javascript:buttonizer.tabNavigate(\'' . $sKey . '\')" id="tab_container_' . $sKey . '" class="nav-tab ' . (( $bFirstTab ? 'first nav-tab-active' : '' )) . '">' . $sTitle . '</a>';
            $sTabsContent .= '<div id="tab_container_' . $sKey . '" ' . (( !$bFirstTab ? 'style="display: none;"' : '' )) . ' class="tab-container">' . $this->aSubPages[$sKey] . '</div>';
            $bFirstTab = false;
        }
        echo  '<table class="vertical-tabs">
    <tr>
        <td class="tabs">' . $sTabs . '</td>
        <td class="tabsholder">' . $sTabsContent . '</td>
    </tr>
</table>' ;
    }
    
    /*
     * Register settings
     */
    public function registerPlacingSettings()
    {
        $this->aSubCatagories['placing'] = __( 'Placing &amp; animations', BUTTONIZER_SLUG );
        $this->aSubPages['placing'] = '<table class="form-table"><tbody>';
        $this->aSubPages['placing'] .= '<tr>
        <td colspan="2">
        <h2>Placing &amp; animations</h2>
        <p>You can change here the button position and the button animation.</p>
        </td></tr>';
        $this->aSubPages['placing'] .= $this->createFormField( 'Button placing', array( &$this, 'field_button_placing' ) );
        $this->aSubPages['placing'] .= $this->createFormField( 'Horizontal placing', array( &$this, 'field_position_horizontal' ), 'intro-position' );
        $this->aSubPages['placing'] .= $this->createFormField( 'Bottom', array( &$this, 'field_position_bottom' ) );
        $this->aSubPages['placing'] .= $this->createFormField( 'Label placing', array( &$this, 'field_label_placing' ) );
        $this->aSubPages['placing'] .= '<tr>
        <td colspan="2">
        <hr style="margin: 20px 0;">
        </td></tr>';
        $this->aSubPages['placing'] .= $this->createFormField( 'Button animation', array( &$this, 'field_button_animation' ), 'intro-animaton' );
        $this->aSubPages['placing'] .= $this->createFormField( 'Attention animation', array( &$this, 'field_attention_animation' ), 'intro-attention' );
        $this->aSubPages['placing'] .= '</tbody></table>';
    }
    
    /*
     * Register settings
     */
    public function registerDesignSettings()
    {
        $this->aSubCatagories['design'] = __( 'Design', BUTTONIZER_SLUG );
        $this->aSubPages['design'] = '<table class="form-table"><tbody>';
        $this->aSubPages['design'] .= '<tr>
        <td colspan="2">
        <h2>Design</h2>
        <p>Style your button, make it blinky as you wish. You can change here the main color, main label and set the label hover types.</p>
        </td></tr>';
        $this->aSubPages['design'] .= '</tbody></table>';
        $this->aSubPages['design'] .= '<table class="form-table intro-styling"><tbody>';
        $this->aSubPages['design'] .= $this->createFormField( 'Color button unpushed', array( &$this, 'field_button_unpushed' ) );
        $this->aSubPages['design'] .= $this->createFormField( 'Color button pushed', array( &$this, 'field_button_pushed' ) );
        $this->aSubPages['design'] .= $this->createFormField( 'Icon color', array( &$this, 'field_icon_color' ) );
        $this->aSubPages['design'] .= $this->createFormField( 'Button size<br/><br/><small>The default size of the button is 56px.</small>', array( &$this, 'field_icon_size' ) );
        $this->aSubPages['design'] .= $this->createFormField( 'Button size mobile<br/><br/><small>Recommended is 56px (phones are small).</small>', array( &$this, 'field_icon_mobile_size' ) );
        $this->aSubPages['design'] .= '</tbody></table>';
        $this->aSubPages['design'] .= '<table class="form-table" style="margin-top: 0;"><tbody>';
        $this->aSubPages['design'] .= $this->createFormField( 'Main icon<br /><br /><small>This icon will appear in a button when you have multiple floating action buttons on one page. When you click on the icon the other floating action button will \'pop\' open​.</small>', array( &$this, 'field_icon_icon' ), 'intro-icon' );
        $this->aSubPages['design'] .= $this->createFormField( 'Main label<br /><br /><small>This label will apear next to the \'main button\' when you have multiple floating action buttons on one page.</small>', array( &$this, 'field_icon_label' ), 'intro-label' );
        $this->aSubPages['design'] .= $this->createFormField( 'Label color<br /><br /><small>This is the color for the label that will apear next to the main button</small>', array( &$this, 'field_icon_label_color' ), 'intro-label' );
        $this->aSubPages['design'] .= $this->createFormField( 'Label text color<br /><br /><small>This is the color of the text for the label</small>', array( &$this, 'field_icon_label_text_color' ), 'intro-label' );
        $this->aSubPages['design'] .= $this->createFormField( 'Main label settings<br /><br /><small>With the label settings you can show the label on hover, or always show the label</small>', array( &$this, 'field_label_show_on_hover' ) );
        $this->aSubPages['design'] .= '</tbody></table>';
    }
    
    public function registerAdvancedSettings()
    {
        $this->aSubCatagories['advanced'] = __( 'Advanced settings', BUTTONIZER_SLUG );
        $this->aSubPages['advanced'] = '<table class="form-table"><tbody>';
        $this->aSubPages['advanced'] .= '<tr>
        <td colspan="2">
        <h2>Advanced settings</h2>
        <p></p>
        </td></tr>';
        $this->aSubPages['advanced'] .= $this->createFormField( '
            <span class="info-class">
                <i class="fa fa-info-circle"></i>
                <span>If you have your Google Analytics tracking code already installed you can ignore this setting. If you want to be sure that your buttons are tracked in your Google Analytics account? Then go to your Google Analytics account > Click on Real Time > Events and open your website in another tab. Click on a Buttonizer button and check if you see that the click gets tracked in Google Analytics. If this doesn\'t happen than go to your google Analytics settings page > Click on tracking code and copy your tracking-ID. It should look like this (UA-00000000-0)
                </span>
            </span>
            Google Analytics', array( &$this, 'field_analytics_code' ) );
        $this->aSubPages['advanced'] .= $this->createFieldset( 'Show on scroll' );
        // Show on scroll
        $this->aSubPages['advanced'] .= $this->createFormField( '
                <span class="info-class">
                    <i class="fa fa-info-circle"></i>
                    <span>
                        When selected, the buttons will be shown after the user has scrolled a percentage of the page.
                    </span>
                </span>
                Show on scroll?', array( &$this, 'field_show_on_scroll' ) );
        $this->aSubPages['advanced'] .= $this->createFormField( '
                <span class="info-class">
                    <i class="fa fa-info-circle"></i>
                    <span>
                        Enter a percentage, starting from 0, up to 100.
                    </span>
                </span>
                % from top to show:', array( &$this, 'field_procent_to_scroll' ) );
        $this->aSubPages['advanced'] .= $this->closeFieldset();
        // Show after timeout
        $this->aSubPages['advanced'] .= $this->createFieldset( 'Show after timeout' );
        $this->aSubPages['advanced'] .= $this->createFormField( '
                <span class="info-class">
                    <i class="fa fa-info-circle"></i>
                    <span>
                        When selected, the buttons will be shown when the user browsers more than the given seconds. So when you say: Show after 20<i>000</i>ms, the button shows after the user browsed on the website for 20 seconds.
                    </span>
                </span>
                Show after timeout?', array( &$this, 'field_show_on_timeout' ) );
        $this->aSubPages['advanced'] .= $this->createFormField( '
                <span class="info-class">
                    <i class="fa fa-info-circle"></i>
                    <span>
                        Enter the miliseconds from when the buttons must get shown to the user.<br />
                        <br />
                        1 second = 1000 miliseconds<br />
                        10 seconds = 10000 miliseconds<br />
                        1 minute = 60000 miliseconds<br />
                    </span>
                </span>
                Milliseconds: ', array( &$this, 'field_time_to_timeout' ) );
        $this->aSubPages['advanced'] .= $this->closeFieldset();
        // Exit intent
        $this->aSubPages['advanced'] .= $this->createFieldset( 'Exit intent' );
        $this->aSubPages['advanced'] .= $this->createFormField( '
                <span class="info-class">
                    <i class="fa fa-info-circle"></i>
                    <span>
                        When selected, the buttons will open with a effect when your guest/user tries to escape from your website. The user gets attracted from your buttons.
                    </span>
                </span>
                Exit intent', array( &$this, 'field_exit_intent' ) );
        $this->aSubPages['advanced'] .= $this->createFormField( '
                <span class="info-class">
                    <i class="fa fa-info-circle"></i>
                    <span>
                        When the exit intent activates, there will be shown some text next to the head-button. You can edit that here.<br />
                        <br />
                        When empty, no message will be shown.
                    </span>
                </span>
                Exit intent text', array( &$this, 'field_exit_intent_text' ) );
        $this->aSubPages['advanced'] .= $this->closeFieldset();
        $this->aSubPages['advanced'] .= '</tbody></table>';
        if ( !ButtonizerLicense()->is_not_paying() && !ButtonizerLicense()->is_plan( 'premium' ) ) {
            $this->aSubPages['advanced'] .= '<div class="buttonizer-pro-text"><i class="fa fa-lock"></i> You are missing the fun! When upgrading to <a href="Admin.php?page=Buttonizer-pricing">Buttonizer Pro</a> you will get these features.</div>';
        }
    }
    
    public function registerSocialSettings()
    {
        $this->aSubCatagories['social'] = __( 'Social share settings', BUTTONIZER_SLUG );
        $this->aSubPages['social'] = '<table class="form-table"><tbody>';
        $this->aSubPages['social'] .= '<tr>
       <td colspan="2">
       <h2>Social sharing buttons</h2>
       <p>These are separated pre-configured buttons to share a page. The buttons will be visible at every page, you can let the user share a page on Twitter, Facebook, LinkedIn and Email<p>
       <p>You can enable them by clicking the checkbox, to change the label click on the text. Don\'t forget to save your changes!</p>
       
       <div class="input_error warning_text" hidden>
           <label for="button_text">
                Warning: You can pick a maximum of 3 options         
           </label>
       </div>
       
       </td></tr>';
        // Facebook share button
        $this->aSubPages['social'] .= $this->createFormField( 'Share on Facebook', array( &$this, 'field_share_facebook' ) );
        // Twitter share button
        $this->aSubPages['social'] .= $this->createFormField( 'Share on Twitter', array( &$this, 'field_share_twitter' ) );
        // LinkedIn share button
        $this->aSubPages['social'] .= $this->createFormField( 'Share on LinkedIn', array( &$this, 'field_share_linkedin' ) );
        // Email share button
        $this->aSubPages['social'] .= $this->createFormField( 'Share on Email', array( &$this, 'field_share_email' ) );
        // Whatsapp share button
        $this->aSubPages['social'] .= $this->createFormField( 'Share on Whatsapp', array( &$this, 'field_share_whatsapp' ) );
        //        // Pinterest share button
        //        $this->aSubPages['social'] .= $this->createFormField('Share on Pinterest', array(&$this, 'field_share_pinterest'));
        $this->aSubPages['social'] .= '<tr>
       <td colspan="2" style="text-align: right;">
       <p>Do you want us to add more social media options? <a href="Admin.php?page=Buttonizer-contact" style="color: #498698">Contact us</a></p>
       </td></tr>';
        $this->aSubPages['social'] .= '</tbody></table>';
    }
    
    public function registerOther()
    {
        $this->aSubCatagories['other'] = __( 'Other', BUTTONIZER_SLUG );
        $this->aSubPages['other'] = '<table class="form-table"><tbody>';
        $this->aSubPages['other'] .= '<tr>
        <td colspan="2">
        <h2>Other</h2>
        <p>Nothing really specials here, just a way to reset the buttonizer to the default settings.</p>
        <p>Click the button below to get some more information.</p>
        </td></tr>';
        $this->aSubPages['other'] .= '<tr><th scope="row">Reset Buttonizer</th><td><a href="?page=Buttonizer&tab=buttonizer_page_reset" class="button ">More info</a></td></tr>';
        $this->aSubPages['other'] .= '</tbody></table>';
    }
    
    private function createFormField( $sLabel, $aFunction, $sClass = '' )
    {
        return '<tr class="' . $sClass . '"><th scope="row">' . $sLabel . '</th><td class="r-sit">' . $aFunction() . '</td></tr>';
    }
    
    private function createFieldset( $sLabel )
    {
        return '<tr><td colspan="2"><fieldset><legend>' . $sLabel . '</legend><table class="form-table">';
    }
    
    private function closeFieldset()
    {
        return '</table></fieldset></td></tr>';
    }
    
    /*
     * Form
     */
    function field_button_unpushed()
    {
        $button_unpushed = ( isset( $this->aSavedData['button_unpushed'] ) ? $this->aSavedData['button_unpushed'] : '#48A4DC' );
        return '<input type="text" name="buttonizer_general_settings[button_unpushed]" value="' . $button_unpushed . '" id="button_unpushed" data-default-color="#fffff" />';
    }
    
    function field_button_pushed()
    {
        $button_pushed = ( isset( $this->aSavedData['button_pushed'] ) ? $this->aSavedData['button_pushed'] : '#1D9BDB' );
        return '<input type="text" name="buttonizer_general_settings[button_pushed]" value="' . $button_pushed . '" id="button_pushed" data-default-color="#fffff" />';
    }
    
    function field_icon_color()
    {
        $icon_color = ( isset( $this->aSavedData['icon_color'] ) ? $this->aSavedData['icon_color'] : '#fffff' );
        return '<input type="text" name="buttonizer_general_settings[icon_color]" value="' . $icon_color . '" id="icon_color" data-default-color="#fffff" />';
    }
    
    function field_icon_size()
    {
        $icon_size = ( isset( $this->aSavedData['icon_size'] ) ? $this->aSavedData['icon_size'] : '56' );
        return '<input type="range" name="buttonizer_general_settings[icon_size]" value="' . $icon_size . '" id="icon_size" min="45" max="80" data-default-value="50" /> <br/>  
                <p>Size: <span id="icon_size_output"></span>px</p> 
                <script>
                    var slider = document.getElementById("icon_size");
                    var output = document.getElementById("icon_size_output");
                    output.innerHTML = slider.value;
                    
                    slider.oninput = function() {
                      output.innerHTML = this.value;
                    }
                </script>';
    }
    
    function field_icon_mobile_size()
    {
        $icon_size = ( isset( $this->aSavedData['mobile_icon_size'] ) ? $this->aSavedData['mobile_icon_size'] : '56' );
        return '<input type="range" name="buttonizer_general_settings[mobile_icon_size]" value="' . $icon_size . '" id="mobile_icon_size" min="45" max="80" data-default-value="50" /> <br/>  
                <p>Size: <span id="mobile_icon_size_output"></span>px</p> 
                <script>
                    var slider_mobile = document.getElementById("mobile_icon_size");
                    var output_mobile = document.getElementById("mobile_icon_size_output");
                    output_mobile.innerHTML = slider_mobile.value;
                    
                    slider_mobile.oninput = function() {
                      output_mobile.innerHTML = this.value;
                    }
                </script>';
    }
    
    function field_icon_icon()
    {
        $p = $this->oIconManager->generator( [
            'icon'            => ( isset( $this->aSavedData['icon_icon'] ) ? $this->aSavedData['icon_icon'] : '' ),
            'icon_fieldname'  => 'buttonizer_general_settings[icon_icon]',
            'image'           => ( isset( $this->aSavedData['custom_icon'] ) ? $this->aSavedData['custom_icon'] : '' ),
            'image_fieldname' => 'buttonizer_general_settings[custom_icon]',
        ] );
        return $p;
    }
    
    function field_icon_label()
    {
        $icon_label = ( isset( $this->aSavedData['icon_label'] ) ? $this->aSavedData['icon_label'] : '' );
        return '<input type="text" name="buttonizer_general_settings[icon_label]" value="' . $icon_label . '" placeholder="Fill in some text" />';
    }
    
    function field_icon_label_color()
    {
        return '<div class="wp-picker-container"><a tabindex="0" class="wp-color-result" title="PRO feature" style="background-color: #4e4c4c;"></a></div> <span class="buttonizer-pro-feature" style="vertical-align: top;">PRO</span></div>';
    }
    
    function field_icon_label_text_color()
    {
        return '<div class="wp-picker-container"><a class="wp-color-result" title="PRO feature" style="background-color: #ffffff;"></a></div> <span class="buttonizer-pro-feature" style="vertical-align: top;">PRO</span></div>';
    }
    
    /**
     * Button placing
     *
     * @return string
     */
    function field_button_placing()
    {
        $button_placing = ( isset( $this->aSavedData['buttons_placing'] ) ? $this->aSavedData['buttons_placing'] : 'default' );
        return '
        <select name="buttonizer_general_settings[buttons_placing]" style="width: 100%; padding: 0 10px; height: 40px;">
            <option value="default" ' . (( $button_placing == 'default' ? 'selected' : '' )) . '>Right bottom corner (Default)</option>
            <option value="left" ' . (( $button_placing == 'left' ? 'selected' : '' )) . '>Left bottom corner</option>
        </select>';
    }
    
    /**
     * Horizontal placing
     * When the button placing is set on 'right', this will be the space between the button and the right page corner.
     *
     * @return string
     */
    function field_position_horizontal()
    {
        $position_right = ( isset( $this->aSavedData['position_horizontal'] ) ? $this->aSavedData['position_horizontal'] : (( isset( $this->aSavedData['position_right'] ) ? $this->aSavedData['position_right'] : '5' )) );
        return '<input type="text" name="buttonizer_general_settings[position_horizontal]" value="' . $position_right . '" onkeypress="return event.charCode >= 48 && event.charCode <= 57" placeholder="Fill in a number from 0 to 100"/><small><i class="after-input">%</i>
                <div class="buttonizer-note">When the button placing is set on \'right\', this will be the space between the button and the right page corner. When your button placing is at the left corner, this will be the left space of your button. Recommended value is 5%.</div> ';
    }
    
    function field_position_bottom()
    {
        $position_bottom = ( isset( $this->aSavedData['position_bottom'] ) ? $this->aSavedData['position_bottom'] : '5' );
        return '<input type="text" name="buttonizer_general_settings[position_bottom]" value="' . $position_bottom . '" onkeypress="return event.charCode >= 48 && event.charCode <= 57" placeholder="Fill in a number from 0 to 100"/><small><i class="after-input">%</i><br>Recommended 5% then it will position itself on the bottom corner</small>';
    }
    
    /**
     * Mirroring the button style
     *
     * @return string
     */
    function field_label_placing()
    {
        $button_placing = ( isset( $this->aSavedData['buttons_label_placing'] ) ? $this->aSavedData['buttons_label_placing'] : 'default' );
        return '
        <select name="buttonizer_general_settings[buttons_label_placing]" style="width: 100%; padding: 0 10px; height: 40px;">
            <option value="default" ' . (( $button_placing == 'default' ? 'selected' : '' )) . '>Label left, icon right</option>
            <option value="mirrored" ' . (( $button_placing == 'mirrored' ? 'selected' : '' )) . '>Icon left, label right (mirrored)</option>
        </select>
        <div class="buttonizer-note">When you change the label place, you will mirror the style &amp; animation of the button also.</div>';
    }
    
    function field_button_animation()
    {
        $button_animation = ( isset( $this->aSavedData['buttons_animation'] ) ? $this->aSavedData['buttons_animation'] : 'default' );
        return '
        <select name="buttonizer_general_settings[buttons_animation]" style="width: 100%; padding: 0 10px; height: 40px;">
            <option value="default" ' . (( $button_animation == 'default' ? 'selected' : '' )) . '>Default button animation (fade up)</option>
            <option value="circle" ' . (( $button_animation == 'circle' ? 'selected' : '' )) . '>Circle animation (arround button, right bottom corner)</option>
            <option value="fade-left-to-right" ' . (( $button_animation == 'fade-left-to-right' ? 'selected' : '' )) . '>Fade animation left to right</option>
        </select>';
    }
    
    function field_attention_animation()
    {
        $attention_animation = ( isset( $this->aSavedData['attention_animation'] ) ? $this->aSavedData['attention_animation'] : 'none' );
        return '
        <select name="buttonizer_general_settings[attention_animation]" style="width: 100%; padding: 0 10px; height: 40px;">
            <option value="none" ' . (( $attention_animation == 'none' ? 'selected' : '' )) . '>No attention animation</option>
            <option value="hello" ' . (( $attention_animation == 'hello' ? 'selected' : '' )) . '>Buttonizer Hello</option>
            <option value="bounce" ' . (( $attention_animation == 'bounce' ? 'selected' : '' )) . '>Bouncing</option>
        </select>
        <small>This will get the button jumping out of the background. So it gets your attention. All animations will be played every 10 seconds. Average animation duration is 1.75 seconds.</small>
        ';
    }
    
    public function field_label_show_on_hover()
    {
        $is_selected = ( isset( $this->aSavedData['buttons_label_show_on_hover'] ) ? $this->aSavedData['buttons_label_show_on_hover'] : '' );
        //return '<input type="checkbox" name="buttonizer_general_settings[buttons_label_show_on_hover]" id="buttons_label_show_on_hover" value="1" '. ($is_selected == '1' ? 'checked="checked"' : '') .' /> <small><label for="buttons_label_show_on_hover">Only show on hover</label></small>';
        return '
        <select name="buttonizer_general_settings[buttons_label_show_on_hover]" style="width: 100%; padding: 0 10px; height: 40px;" id="buttons_label_show_on_hover" >
            <option value="default" ' . (( $is_selected == 'default' ? 'selected' : '' )) . '>Always show label</option>
            <option value="showOnHover" ' . (( $is_selected == 'showOnHover' ? 'selected' : '' )) . '>Show on hover</option>
            <option value="showOnHoverDesktop" ' . (( $is_selected == 'showOnHoverDesktop' ? 'selected' : '' )) . '>Show on hover (Desktop only)</option>
            <option value="showOnHoverMobile" ' . (( $is_selected == 'showOnHoverMobile' ? 'selected' : '' )) . '>Show on hover (Mobile only)</option>
        </select>
        ';
    }
    
    /*
     * Advanced
     */
    function field_analytics_code()
    {
        $google_analytics = ( isset( $this->aSavedData['google_analytics'] ) ? $this->aSavedData['google_analytics'] : '' );
        return '<input type="text" name="buttonizer_general_settings[google_analytics]" value="' . $google_analytics . '" placeholder="Only when not inserted yet" /><small>Insert here the Google Analytics tracking code. Like UA-000000-2</small>';
    }
    
    public function field_show_on_scroll()
    {
        return '<input type="checkbox" class="buttonizer-click-to-pro" readonly /> <div class="after-input"><span class="buttonizer-pro-feature">PRO</span></div>';
    }
    
    public function field_procent_to_scroll()
    {
        return '<input type="text" value="0" class="buttonizer-click-to-pro" readonly /> <div class="after-input"><small><i>%</i></small> <span class="buttonizer-pro-feature">PRO</span></div>';
    }
    
    public function field_show_on_timeout()
    {
        return '<input type="checkbox" class="buttonizer-click-to-pro" readonly /> <span class="buttonizer-pro-feature">PRO</span>';
    }
    
    public function field_time_to_timeout()
    {
        return '<input type="text" class="buttonizer-click-to-pro" readonly value="0" /><div class="after-input"><span class="buttonizer-pro-feature">PRO</span></div>';
    }
    
    // Exit intent
    public function field_exit_intent()
    {
        return '<input type="checkbox" class="buttonizer-click-to-pro" readonly /> <span class="buttonizer-pro-feature">PRO</span>';
    }
    
    public function field_exit_intent_text()
    {
        return '<input type="text" class="buttonizer-click-to-pro" readonly value="" /><div class="after-input"><span class="buttonizer-pro-feature">PRO</span></div>';
    }
    
    // Share page
    function field_share_facebook()
    {
        $share_facebook = ( isset( $this->aSavedData['share_facebook'] ) ? $this->aSavedData['share_facebook'] : '' );
        $share_facebook_text = ( isset( $this->aSavedData['share_facebook_text'] ) ? $this->aSavedData['share_facebook_text'] : 'Share this on Facebook' );
        return '<span class="before-input"><input type="checkbox" name="buttonizer_general_settings[share_facebook]" value="1" ' . (( $share_facebook == '1' ? 'checked="checked"' : '' )) . ' class="socialSharingCheckbox" onchange="checkSelection()"/></span> <input type="text" name="buttonizer_general_settings[share_facebook_text]" value="' . $share_facebook_text . '" class="before-input-padding" placeholder="Share this on Facebook" />';
    }
    
    function field_share_linkedin()
    {
        $share_linkedin = ( isset( $this->aSavedData['share_linkedin'] ) ? $this->aSavedData['share_linkedin'] : '' );
        $share_linkedin_text = ( isset( $this->aSavedData['share_linkedin_text'] ) ? $this->aSavedData['share_linkedin_text'] : 'Share this on LinkedIn' );
        return '<span class="before-input"><input type="checkbox" name="buttonizer_general_settings[share_linkedin]" value="1" ' . (( $share_linkedin == '1' ? 'checked="checked"' : '' )) . ' class="socialSharingCheckbox" onchange="checkSelection()"/></span> <input type="text" name="buttonizer_general_settings[share_linkedin_text]" value="' . $share_linkedin_text . '" class="before-input-padding" placeholder="Share this on LinkedIn" />';
    }
    
    function field_share_twitter()
    {
        $share_twitter = ( isset( $this->aSavedData['share_twitter'] ) ? $this->aSavedData['share_twitter'] : '' );
        $share_twitter_text = ( isset( $this->aSavedData['share_twitter_text'] ) ? $this->aSavedData['share_twitter_text'] : 'Share this on Twitter' );
        return '<span class="before-input"><input type="checkbox" name="buttonizer_general_settings[share_twitter]" value="1" ' . (( $share_twitter == '1' ? 'checked="checked"' : '' )) . ' class="socialSharingCheckbox" onchange="checkSelection()"/></span> <input type="text" name="buttonizer_general_settings[share_twitter_text]" value="' . $share_twitter_text . '" class="before-input-padding" placeholder="Share this on Twitter" />';
    }
    
    function field_share_email()
    {
        $share_email = ( isset( $this->aSavedData['share_email'] ) ? $this->aSavedData['share_email'] : '' );
        $share_email_text = ( isset( $this->aSavedData['share_email_text'] ) ? $this->aSavedData['share_email_text'] : 'Share this on Email' );
        return '<span class="before-input"><input type="checkbox" name="buttonizer_general_settings[share_email]" value="1" ' . (( $share_email == '1' ? 'checked="checked"' : '' )) . ' class="socialSharingCheckbox" onchange="checkSelection()"/></span> <input type="text" name="buttonizer_general_settings[share_email_text]" value="' . $share_email_text . '" class="before-input-padding" placeholder="Share this on Email" />';
    }
    
    // Next update social sharing features :)
    //    function field_share_pinterest()
    //    {
    //        $share_pinterest = (isset($this->aSavedData['share_pinterest']) ? ($this->aSavedData['share_pinterest']) : '');
    //        $share_pinterest_text = (isset($this->aSavedData['share_pinterest_text']) ? ($this->aSavedData['share_pinterest_text']) : 'Share this on Pinterest');
    //
    //        return '<span class="before-input"><input type="checkbox" name="buttonizer_general_settings[share_pinterest]" value="1" ' . ($share_pinterest == '1' ? 'checked="checked"' : '') . ' class="socialSharingCheckbox" onchange="checkSelection()"/></span> <input type="text" name="buttonizer_general_settings[share_pinterest_text]" value="' . $share_pinterest_text . '" class="before-input-padding" placeholder="Share this on Pinterest" />';
    //    }
    //
    function field_share_whatsapp()
    {
        $share_whatsapp = ( isset( $this->aSavedData['share_whatsapp'] ) ? $this->aSavedData['share_whatsapp'] : '' );
        $share_whatsapp_text = ( isset( $this->aSavedData['share_whatsapp_text'] ) ? $this->aSavedData['share_whatsapp_text'] : 'Share this on Whatsapp' );
        return '<span class="before-input"><input type="checkbox" name="buttonizer_general_settings[share_whatsapp]" value="1" ' . (( $share_whatsapp == '1' ? 'checked="checked"' : '' )) . ' class="socialSharingCheckbox" onchange="checkSelection()"/></span> <input type="text" name="buttonizer_general_settings[share_whatsapp_text]" value="' . $share_whatsapp_text . '" class="before-input-padding" placeholder="Share this on Whatsapp" />';
    }

}