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
class Buttons
{
    private  $aSavedData = array() ;
    private  $aPageCategories = array() ;
    private  $aFontAwesome = array() ;
    private  $oIconManager = array() ;
    private  $aSystemSettings = array() ;
    public function __construct( $aFontAwesome, $oIconManager )
    {
        $this->aFontAwesome = $aFontAwesome;
        $this->oIconManager = $oIconManager;
        $this->loadData();
        $this->loadButtons();
    }
    
    private function loadData()
    {
        $this->aSavedData = (array) get_option( 'buttonizer_buttons' );
        $this->aPageCategories = (array) get_option( 'buttonizer_page_categories' );
        $this->aSystemSettings = (array) get_option( 'buttonizer_general_settings' );
    }
    
    private function loadButtons()
    {
        // Get buttons
        $aButtons = ( isset( $this->aSavedData['buttonorder'] ) && is_array( $this->aSavedData['buttonorder'] ) ? $this->aSavedData['buttonorder'] : [
            0 => -1,
        ] );
        if ( $aButtons[0] != '-1' ) {
            $aButtons[-1] = '-1';
        }
        echo  '<ul id="button-rows">' ;
        $iButtonCount = 0;
        foreach ( $aButtons as $sButtonKey => $iButtonId ) {
            $this->loadIndividialButtonRow( $sButtonKey, $iButtonId, ( isset( $bDisabled ) ? true : false ) );
            $iButtonCount++;
        }
        echo  '</ul>' ;
        echo  '<img src="' . plugins_url( '/assets/no-buttons.jpg', BUTTONIZER_PLUGIN_DIR ) . '" style="' . (( 0 == $iButtonCount - 1 ? 'display: block;' : 'display: none;' )) . '" class="buttonizer-no-buttons" />' ;
    }
    
    private function loadIndividialButtonRow( $sButtonKey, $iButtonId, $bDisabled )
    {
        $sButtonTitle = ( isset( $this->aSavedData['button_' . $iButtonId . '_title'] ) ? $this->aSavedData['button_' . $iButtonId . '_title'] : 'Button ' . ($iButtonId + 1) );
        $onMobile = ( isset( $this->aSavedData['button_' . $iButtonId . '_show_on_phone'] ) ? $this->aSavedData['button_' . $iButtonId . '_show_on_phone'] : '' );
        $onDesktop = ( isset( $this->aSavedData['button_' . $iButtonId . '_show_on_desktop'] ) ? $this->aSavedData['button_' . $iButtonId . '_show_on_desktop'] : '' );
        ?>
        <li>
    		<div class="button-row" id="btn_row_<?php 
        echo  $iButtonId ;
        ?>" button-id="<?php 
        echo  $iButtonId ;
        ?>" data-button-action="<?php 
        echo  ( isset( $this->aSavedData['button_' . $iButtonId . '_type'] ) ? $this->aSavedData['button_' . $iButtonId . '_type'] : 'url' ) ;
        ?>" <?php 
        if ( $iButtonId == -1 ) {
            echo  ' style="display: none"' ;
        }
        ?>>
    			<input type="checkbox" name="buttonizer_buttons[buttonorder][]" value="<?php 
        echo  $iButtonId ;
        ?>" checked="checked" style="display: none;" />

    			<div class="row-info drag-handle">
                    <div class="mover"></div>
    				<span><i class="fa <?php 
        echo  $this->buttonOutputIcon( $iButtonId ) ;
        ?> button-icon"></i> <span class="row-title"><?php 
        echo  ( $iButtonId == -1 ? 'New button' : $sButtonTitle ) ;
        ?></span> <i class="fa fa-pencil pencil-edit" ></i></span>

    				<a href="javascript:buttonizer.removeRow(<?php 
        echo  $iButtonId ;
        ?>)" class="delete-button"><i class="fa fa-trash-o"></i></a>
                    <a href="javascript:buttonizer.copyRow(<?php 
        echo  $iButtonId ;
        ?>)" class="mobiledesktop selected copy-button"><i class="fa fa-copy"></i></a>

    				<a href="javascript:buttonizer.toggleMobile(<?php 
        echo  $iButtonId ;
        ?>)" class="mobiledesktop mobile-button <?php 
        echo  ( $onMobile == "" ? 'selected' : '' ) ;
        ?>"><i class="fa fa-mobile"></i></a>
                    <a href="javascript:buttonizer.toggleDesktop(<?php 
        echo  $iButtonId ;
        ?>)" class="mobiledesktop desktop-button <?php 
        echo  ( $onDesktop == "" ? 'selected' : '' ) ;
        ?>"><i class="fa fa-desktop"></i></a>

                    <a href="javascript:void(0)" class="is-live-button" style="<?php 
        echo  ( $onMobile != "" || $onDesktop != "" ? '' : 'display: none;' ) ;
        ?>"><i class="fa fa-circle"></i> Live</a>

                    <button type="submit" class="must-save-button savebutton " style="display: none;"><i class="fa fa-floppy-o"></i> Save changes</button>
                </div>

    			<div class="button-data">
    				<table>
    					<tr style="display: none;">
    						<td></td>
    						<td></td>
    					</tr>
    					<tr style="vertical-align: top;">
    						<td width="50%">
    							<label for="button_text" class="label-top">
    								<span class="info-class"><i class="fa fa-info-circle"></i> <span>This is for internal use only.<br /><br />When using Google Analytics, this will be the title of the event when clicking this button.</span></span>
    								Button name:
    							</label>
    							<input type="text" name="buttonizer_buttons[button_<?php 
        echo  $iButtonId ;
        ?>_title]" value="<?php 
        echo  $sButtonTitle ;
        ?>" class="button_title" />
    						</td>
    						<td class="button-pdr">
    							<label for="button_icon" class="label-top">
    								<span class="info-class"><i class="fa fa-info-circle"></i> <span>This will be the icon of the button. You can choose from the build-in Font Awesome icons.<br /><br />When you want to use your own icon or image, you can click the 'or upload an image' link to use your own icon.</span></span>
    								Button icon/image:
    							</label>
    							<?php 
        echo  $this->buttonGetIcon( $iButtonId ) ;
        ?>
    						</td>
    					</tr>

    					<tr>
    						<td>&nbsp;</td>
    						<td></td>
    					</tr>

    					<tr>
    						<td>
    							<label for="button_text" class="label-top">
    								<span class="info-class"><i class="fa fa-info-circle"></i> <span>This is the label what the guest on your website will see. Something like 'Contact me!', or 'Like us on Facebook' <br/><span style="color: red;">Don't make it too long!!</span></span></span>
    								Button label:
    							</label>
    							<?php 
        echo  $this->buttonGetText( $iButtonId ) ;
        ?>
    						</td>
    						<td>
    							<label for="button_category" class="label-top">
    								<span class="info-class"><i class="fa fa-info-circle"></i> <span>Select a page category. You can add more page rules at the 'Page categories' tab.</span></span>
    								Button page category:
    							</label>
    							<?php 
        echo  $this->buttonGetShowOnPages( $iButtonId ) ;
        ?>
    						</td>
    					</tr>

    					<tr>
    						<td>&nbsp;</td>
    						<td></td>
    					</tr>

    					<tr class="settings-row button-action">
                            <td colspan="2">
    							<i class="fa fa-globe setting-icon"></i> Button action
                                <span class="info-class"><i class="fa fa-info-circle"></i> <span>This is the click-action of your button. When you choose for a phone number, it will be a direct click-to-call action.</span></span>

                                <table width="100%" class="settings-row-type">
                                    <tr>
                                        <!-- Button action set -->
                                        <td width="200"><?php 
        echo  $this->buttonGetType( $iButtonId ) ;
        ?></td>
                                        <td>
                                            <!-- Button input set -->

                                            <?php 
        echo  $this->buttonGetUrl( $iButtonId ) ;
        ?>
                                            <?php 
        echo  $this->buttonGetSocial( $iButtonId ) ;
        ?>

                                            <div class="input_error" style="display: none;"></div>

                                            <div class="extra_info" data-button-type="phone">

                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            </td>
    					</tr>

    					<tr class="settings-row setting-new-tab">
    						<td>
    							<i class="fa fa-external-link setting-icon"></i> Open in new tab
    						</td>
    						<td><?php 
        echo  $this->buttonGetNewTab( $iButtonId ) ;
        ?><label for="button_<?php 
        echo  $iButtonId ;
        ?>_url_newtab"> Yes</label></td>
    					</tr>

    					<tr class="settings-row">
    						<td>
    							<i class="fa fa-paint-brush setting-icon"></i> Button colors
                                <span class="info-class"><i class="fa fa-info-circle"></i> <span>You can change the colors of this button, like the default background, the background on hover or the icon color. You can undo them too. Click the palet for changing the colors.</span></span>
    						</td>
    						<td><?php 
        echo  $this->buttonColors( $iButtonId ) ;
        ?></td>
    					</tr>

    					<tr>
    						<td>&nbsp;</td>
    						<td></td>
    					</tr>

    					<tr class="settings-row show-mobile-btn">
    						<td>
    							<i class="fa fa-mobile setting-icon"></i> Show on mobile
    						</td>
    						<td><?php 
        echo  $this->buttonGetShowPhone( $iButtonId ) ;
        ?><label for="button_<?php 
        echo  $iButtonId ;
        ?>_show_on_phone"> Yes</label></td>
    					</tr>

    					<tr class="settings-row show-desktop-btn">
    						<td>
    							<i class="fa fa-desktop setting-icon"></i> Show on desktop
    						</td>
    						<td><?php 
        echo  $this->buttonGetShowDesktop( $iButtonId ) ;
        ?><label for="button_<?php 
        echo  $iButtonId ;
        ?>_show_on_desktop"> Yes</label></td>
    					</tr>

    					<tr class="settings-row">
    						<td>
    							<i class="fa fa-tag setting-icon"></i> Show label on hover

                                <span class="info-class">
                                    <i class="fa fa-info-circle"></i>
                                    <span>
                                        When you hover over the button, the label will be hided until the user hovers the icon.
                                    </span>
                                </span>
    						</td>
                            <!-- drop down here -->
    						<td><?php 
        echo  $this->buttonShowOnHover( $iButtonId ) ;
        ?></td>
<!--                            <td>-->
<!--                            -->
<!--                            </td>-->
                        </tr>

    					<tr>
    						<td>&nbsp;</td>
                            
    						<td></td>
    					</tr>

                        <?php 
        ?>
                        <tr class="settings-row">
                            <td>
                                <i class="fa fa-clock-o setting-icon"></i> Show <b>only</b> on opening hours
                            </td>
                            <td><input type="checkbox" class="buttonizer-click-to-pro" readonly=""> <label> Yes</label> <span class="buttonizer-pro-feature">PRO</span></td>                            <td></td>
                        </tr>

                        <tr class="settings-row">
                            <td>
                                <i class="fa fa-times-circle-o setting-icon"></i> Show <b>only outside</b> opening hours
                            </td>
                            <td><input type="checkbox" class="buttonizer-click-to-pro" readonly=""> <label> Yes</label> <span class="buttonizer-pro-feature">PRO</span></td>
                        </tr>
                        <?php 
        ?>

                        <tr>
                            <td>&nbsp;</td>
                            <td></td>
                        </tr>

                        <tr class="settings-row">
                            <td>
                                <i class="fa fa-css3 setting-icon"></i> Custom CSS class <?php 
        echo  $this->showPro() ;
        ?>
                            </td>
                            <td><?php 
        echo  $this->buttonCustomClass( $iButtonId ) ;
        ?></td>
                        </tr>
    				</table>
    			</div>
    		</div>
        </li>
		<?php 
    }
    
    /*
     * Form functions
     */
    // Get the text of the button
    function buttonGetText( $iButtonId = 0 )
    {
        $fieldName = 'button_' . $iButtonId . '_text';
        $fieldValue = ( isset( $this->aSavedData[$fieldName] ) ? $this->aSavedData[$fieldName] : 'New button' );
        return '<input type="text" name="buttonizer_buttons[' . $fieldName . ']" id="' . $fieldName . '" value="' . $fieldValue . '" class="button_textfield" placeholder="Button text" />';
    }
    
    // Get button icon
    function buttonGetIcon( $iButtonId = 0 )
    {
        return $this->oIconManager->generator( [
            'icon'                         => ( isset( $this->aSavedData['button_' . $iButtonId . '_icon'] ) ? $this->aSavedData['button_' . $iButtonId . '_icon'] : '' ),
            'icon_fieldname'               => 'buttonizer_buttons[button_' . $iButtonId . '_icon]',
            'image'                        => ( isset( $this->aSavedData['button_' . $iButtonId . '_image'] ) ? $this->aSavedData['button_' . $iButtonId . '_image'] : '' ),
            'image_fieldname'              => 'buttonizer_buttons[button_' . $iButtonId . '_image]',
            'choose_type'                  => true,
            'show_is_background'           => ( isset( $this->aSavedData['button_' . $iButtonId . '_image_background'] ) ? $this->aSavedData['button_' . $iButtonId . '_image_background'] : '' ),
            'show_is_background_fieldname' => 'buttonizer_buttons[button_' . $iButtonId . '_image_background]',
        ] );
    }
    
    // Get button icon
    function buttonOutputIcon( $iButtonId = 0 )
    {
        $fieldName = 'button_' . $iButtonId . '_icon';
        return ( isset( $this->aSavedData[$fieldName] ) ? $this->aSavedData[$fieldName] : 'fa-info' );
    }
    
    // Only show this button on phone?
    function buttonGetShowPhone( $iButtonId = 0 )
    {
        $fieldName = 'button_' . $iButtonId . '_show_on_phone';
        $is_selected = ( isset( $this->aSavedData[$fieldName] ) ? $this->aSavedData[$fieldName] : '' );
        return '<input type="checkbox" name="buttonizer_buttons[' . $fieldName . ']" id="' . $fieldName . '" value="1" class="button_showonphone" ' . (( $is_selected == '1' ? 'checked="checked"' : '' )) . ' />';
    }
    
    // buttonGetShowOnPages
    function buttonGetShowOnPages( $iButtonId = 0 )
    {
        $fieldName = 'button_' . $iButtonId . '_show_on_pages';
        $is_selected = ( isset( $this->aSavedData[$fieldName] ) ? $this->aSavedData[$fieldName] : '' );
        $pageOrders = ( isset( $this->aPageCategories ) ? $this->aPageCategories : [] );
        /*
         * Generate <select>
         */
        $html = '<select name="buttonizer_buttons[' . $fieldName . ']" id="button_category"><option value="-5">Show on every page</option><option value="-4">Hide everywhere</option>';
        foreach ( $pageOrders as $id => $data ) {
            if ( !isset( $data['title'] ) ) {
                continue;
            }
            $html .= '<option value="' . $id . '"' . (( $id == $is_selected ? 'selected="selected"' : '' )) . '>' . $data['title'] . '</option>';
        }
        return $html . '</select>';
    }
    
    // Only show this button on desktop?
    function buttonGetShowDesktop( $iButtonId = 0 )
    {
        $fieldName = 'button_' . $iButtonId . '_show_on_desktop';
        $is_selected = ( isset( $this->aSavedData[$fieldName] ) ? $this->aSavedData[$fieldName] : '' );
        return '<input type="checkbox" name="buttonizer_buttons[' . $fieldName . ']" id="' . $fieldName . '" value="1" class="button_showondesktop" ' . (( $is_selected == '1' ? 'checked="checked"' : '' )) . ' />';
    }
    
    // Only show when company is open?
    function buttonGetShowWenOpen( $iButtonId = 0 )
    {
        $fieldName = 'button_' . $iButtonId . '_show_when_opened';
        $is_selected = ( isset( $this->aSavedData[$fieldName] ) ? $this->aSavedData[$fieldName] : '' );
        return '<input type="checkbox" name="buttonizer_buttons[' . $fieldName . ']" id="' . $fieldName . '" value="1" class="button_showwhenopened" ' . (( $is_selected == '1' ? 'checked="checked"' : '' )) . ' />';
    }
    
    // Only show when company is open?
    function buttonGetShowNotWenOpen( $iButtonId = 0 )
    {
        $fieldName = 'button_' . $iButtonId . '_show_not_when_opened';
        $is_selected = ( isset( $this->aSavedData[$fieldName] ) ? $this->aSavedData[$fieldName] : '' );
        return '<input type="checkbox" name="buttonizer_buttons[' . $fieldName . ']" id="' . $fieldName . '" value="1" class="button_showwhenopened" ' . (( $is_selected == '1' ? 'checked="checked"' : '' )) . ' />';
    }
    
    function buttonShowOnHover( $iButtonId = 0 )
    {
        $fieldName = 'button_' . $iButtonId . '_show_label_on_hover';
        $is_selected = ( isset( $this->aSavedData[$fieldName] ) ? $this->aSavedData[$fieldName] : '' );
        // 		return '<input type="checkbox" name="buttonizer_buttons['. $fieldName .']" id="'. $fieldName .'" value="1" class="button_showlabelonhover" '. ($is_selected == '1' ? 'checked="checked"' : '') .' />';
        return '
        <select name="buttonizer_buttons[' . $fieldName . ']" style="width: 100%; padding: 0 10px; height: 40px;">
                               <option value="default" ' . (( $is_selected == 'default' ? 'selected' : '' )) . '>Always show label</option>
                              <option value="showOnHover" ' . (( $is_selected == 'showOnHover' ? 'selected' : '' )) . '>Show on hover</option>
                               <option value="showOnHoverDesktop" ' . (( $is_selected == 'showOnHoverDesktop' ? 'selected' : '' )) . '>Show on hover (Desktop only)</option>
                               <option value="showOnHoverMobile" ' . (( $is_selected == 'showOnHoverMobile' ? 'selected' : '' )) . '>Show on hover (Mobile only)</option>
                            </select>
 	';
    }
    
    function buttonGetType( $iButtonId = 0 )
    {
        $aTypes = [
            'url'           => 'Website URL',
            'phone'         => 'Phone number',
            'mail'          => 'E-mail',
            'backtotop'     => 'Back to top',
            'gobackpage'    => 'Go back one page',
            'socialsharing' => 'Social Sharing',
        ];
        $aTypes = array_merge( $aTypes, [
            'disabled_whatsapp'   => 'Open whatsapp (phone number) - PRO ONLY',
            'disabled_javascript' => 'Javascript function - PRO ONLY',
        ] );
        $fieldName = 'button_' . $iButtonId . '_action';
        $is_selected = ( isset( $this->aSavedData[$fieldName] ) ? $this->aSavedData[$fieldName] : 'url' );
        $html = '<select name="buttonizer_buttons[' . $fieldName . ']" id="button_type" class="button_action">';
        foreach ( $aTypes as $sType => $sTitle ) {
            $html .= '<option value="' . str_replace( 'disabled_', '', $sType ) . '"' . (( $sType == $is_selected ? 'selected="selected"' : '' )) . '  ' . (( strpos( $sType, 'disabled_' ) !== false > 0 ? 'disabled' : '' )) . '>' . $sTitle . '</option>';
        }
        return $html . '</select>';
    }
    
    //Getting the button input
    function buttonGetUrl( $iButtonId = 0 )
    {
        $fieldName = 'button_' . $iButtonId . '_url';
        $fieldValue = str_replace( '"', '\'', ( isset( $this->aSavedData[$fieldName] ) ? $this->aSavedData[$fieldName] : '' ) );
        return '<input type="text" name="buttonizer_buttons[' . $fieldName . ']" id="' . $fieldName . '" value="' . $fieldValue . '" class="button_input" placeholder="" />';
    }
    
    function buttonGetSocial( $iButtonId = 0 )
    {
        $fieldName = 'button_' . $iButtonId . '_social';
        $aSocials = [
            'facebook' => "Share on Facebook",
            'twitter'  => "Share on Twitter",
            'whatsapp' => "Share on Whatsapp",
            'linkedin' => "Share on Linkedin",
            'mail'     => "Share on email",
        ];
        $fieldValue = ( isset( $this->aSavedData[$fieldName] ) ? $this->aSavedData[$fieldName] : '' );
        $html = '<select name="buttonizer_buttons[' . $fieldName . ']" id="" class="social_input" style="display:none;">';
        foreach ( $aSocials as $sSocial => $sTitle ) {
            $html .= '<option value="' . str_replace( 'disabled_', '', $sSocial ) . '"' . (( $sSocial == $fieldValue ? 'selected="selected"' : '' )) . '  ' . (( strpos( $sSocial, 'disabled_' ) !== false > 0 ? 'disabled' : '' )) . '>' . $sTitle . '</option>';
        }
        return $html . '</select>';
    }
    
    /**
     * New tab
     *
     * @param int $iButtonId
     * @return string
     */
    function buttonGetNewTab( $iButtonId = 0 )
    {
        $fieldName = 'button_' . $iButtonId . '_url_newtab';
        $is_selected = ( isset( $this->aSavedData[$fieldName] ) ? $this->aSavedData[$fieldName] : '' );
        return '<input type="checkbox" name="buttonizer_buttons[' . $fieldName . ']" id="' . $fieldName . '" value="1" class="button_isnewtab" ' . (( $is_selected == '1' ? 'checked="checked"' : '' )) . ' />';
    }
    
    /**
     * Button colors
     *
     * @param int $iButtonId
     * @return string
     */
    function buttonColors( $iButtonId = 0 )
    {
        // Default button colors
        $sDefaultColor1 = ( isset( $this->aSystemSettings['button_unpushed'] ) ? $this->aSystemSettings['button_unpushed'] : '#48A4DC' );
        $sDefaultColor2 = ( isset( $this->aSystemSettings['button_pushed'] ) ? $this->aSystemSettings['button_pushed'] : '#1D9BDB' );
        $sDefaultColor3 = ( isset( $this->aSystemSettings['icon_color'] ) ? $this->aSystemSettings['icon_color'] : '#fffff' );
        // Using custom colors?
        $bCustomColors = ( isset( $this->aSavedData['button_' . $iButtonId . '_using_custom_colors'] ) && $this->aSavedData['button_' . $iButtonId . '_using_custom_colors'] == '1' ? true : false );
        // Get button colors
        $sColor1 = ( $bCustomColors && isset( $this->aSavedData['button_' . $iButtonId . '_colors_button'] ) && !empty($this->aSavedData['button_' . $iButtonId . '_colors_button']) ? $this->aSavedData['button_' . $iButtonId . '_colors_button'] : $sDefaultColor1 );
        $sColor2 = ( $bCustomColors && isset( $this->aSavedData['button_' . $iButtonId . '_colors_pushed'] ) && !empty($this->aSavedData['button_' . $iButtonId . '_colors_pushed']) ? $this->aSavedData['button_' . $iButtonId . '_colors_pushed'] : $sDefaultColor2 );
        $sColor3 = ( $bCustomColors && isset( $this->aSavedData['button_' . $iButtonId . '_colors_icon'] ) && !empty($this->aSavedData['button_' . $iButtonId . '_colors_icon']) ? $this->aSavedData['button_' . $iButtonId . '_colors_icon'] : $sDefaultColor3 );
        $sColorPalet = '<div class="button-color-palet" data-btnid="' . $iButtonId . '">';
        $sColorPalet .= '<div class="color default" style="background-color: ' . $sColor1 . ';" data-default="' . $sDefaultColor1 . '"></div>';
        $sColorPalet .= '<div class="color pushed" style="background-color: ' . $sColor2 . ';" data-default="' . $sDefaultColor2 . '"></div>';
        $sColorPalet .= '<div class="color icon" style="background-color: ' . $sColor3 . ';" data-default="' . $sDefaultColor3 . '"></div>';
        $sColorPalet .= '<div class="text">' . (( $sColor1 != $sDefaultColor1 || $sColor2 != $sDefaultColor2 || $sColor3 != $sDefaultColor3 ? 'Custom colors' : 'Default colors' )) . '</div>';
        $sColorPalet .= '<input type="hidden" name="buttonizer_buttons[button_' . $iButtonId . '_using_custom_colors]" class="custom" value="' . (( $bCustomColors ? '1' : '0' )) . '" />';
        $sColorPalet .= '<input type="hidden" name="buttonizer_buttons[button_' . $iButtonId . '_colors_button]" class="default" value="' . $sColor1 . '" />';
        $sColorPalet .= '<input type="hidden" name="buttonizer_buttons[button_' . $iButtonId . '_colors_pushed]" class="pushed" value="' . $sColor2 . '" />';
        $sColorPalet .= '<input type="hidden" name="buttonizer_buttons[button_' . $iButtonId . '_colors_icon]" class="icon" value="' . $sColor3 . '" />';
        $sColorPalet .= '</div>';
        $sColorPalet .= '<a href="javascript:void(0)" id="reset_colors_' . $iButtonId . '" style="' . (( !$bCustomColors ? 'display: none' : '' )) . '" class="button color_default_btn">Back to default</a>';
        return $sColorPalet;
    }
    
    /**
     * Button custom class
     *
     * @param $iButtonId
     * @return string
     */
    public function buttonCustomClass( $iButtonId )
    {
        return '<input type="text" class="buttonizer-click-to-pro" value="Use PRO for custom classes" readonly>';
    }
    
    /**
     * Show the pro message
     * @return string
     */
    public function showPro()
    {
        return '<span class="buttonizer-pro-feature buttonizer-click-to-pro">PRO</span>';
    }

}
// Share page
function field_share_facebook()
{
    $share_facebook = ( isset( $this->aSavedData['share_facebook'] ) ? $this->aSavedData['share_facebook'] : '' );
    $share_facebook_text = ( isset( $this->aSavedData['share_facebook_text'] ) ? $this->aSavedData['share_facebook_text'] : 'Share this on Facebook' );
    return '<span class="before-input"><option name="buttonizer_general_settings[share_facebook]" value="1" ' . (( $share_facebook == '1' ? 'checked="checked"' : '' )) . ' class="socialSharingCheckbox" onchange="checkSelection()"/></span> <input type="text" name="buttonizer_general_settings[share_facebook_text]" value="' . $share_facebook_text . '" class="before-input-padding" placeholder="Share this on Facebook" />';
}

function field_share_linkedin()
{
    $share_linkedin = ( isset( $this->aSavedData['share_linkedin'] ) ? $this->aSavedData['share_linkedin'] : '' );
    $share_linkedin_text = ( isset( $this->aSavedData['share_linkedin_text'] ) ? $this->aSavedData['share_linkedin_text'] : 'Share this on LinkedIn' );
    return '<span class="before-input"><option name="buttonizer_general_settings[share_linkedin]" value="1" ' . (( $share_linkedin == '1' ? 'checked="checked"' : '' )) . ' class="socialSharingCheckbox" onchange="checkSelection()"/></span> <input type="text" name="buttonizer_general_settings[share_linkedin_text]" value="' . $share_linkedin_text . '" class="before-input-padding" placeholder="Share this on LinkedIn" />';
}

function field_share_twitter()
{
    $share_twitter = ( isset( $this->aSavedData['share_twitter'] ) ? $this->aSavedData['share_twitter'] : '' );
    $share_twitter_text = ( isset( $this->aSavedData['share_twitter_text'] ) ? $this->aSavedData['share_twitter_text'] : 'Share this on Twitter' );
    return '<span class="before-input"><option name="buttonizer_general_settings[share_twitter]" value="1" ' . (( $share_twitter == '1' ? 'checked="checked"' : '' )) . ' class="socialSharingCheckbox" onchange="checkSelection()"/></span> <input type="text" name="buttonizer_general_settings[share_twitter_text]" value="' . $share_twitter_text . '" class="before-input-padding" placeholder="Share this on Twitter" />';
}

function field_share_email()
{
    $share_email = ( isset( $this->aSavedData['share_email'] ) ? $this->aSavedData['share_email'] : '' );
    $share_email_text = ( isset( $this->aSavedData['share_email_text'] ) ? $this->aSavedData['share_email_text'] : 'Share this on Email' );
    return '<span class="before-input"><option name="buttonizer_general_settings[share_email]" value="1" ' . (( $share_email == '1' ? 'checked="checked"' : '' )) . ' class="socialSharingCheckbox" onchange="checkSelection()"/></span> <input type="text" name="buttonizer_general_settings[share_email_text]" value="' . $share_email_text . '" class="before-input-padding" placeholder="Share this on Email" />';
}

function field_share_whatsapp()
{
    $share_whatsapp = ( isset( $this->aSavedData['share_whatsapp'] ) ? $this->aSavedData['share_whatsapp'] : '' );
    $share_whatsapp_text = ( isset( $this->aSavedData['share_whatsapp_text'] ) ? $this->aSavedData['share_whatsapp_text'] : 'Share this on Whatsapp' );
    return '<span class="before-input"><option name="buttonizer_general_settings[share_whatsapp]" value="1" ' . (( $share_whatsapp == '1' ? 'checked="checked"' : '' )) . ' class="socialSharingCheckbox" onchange="checkSelection()"/></span> <input type="text" name="buttonizer_general_settings[share_whatsapp_text]" value="' . $share_whatsapp_text . '" class="before-input-padding" placeholder="Share this on Whatsapp" />';
}
