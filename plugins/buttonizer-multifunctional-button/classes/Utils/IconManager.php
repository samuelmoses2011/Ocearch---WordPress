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
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
class IconManager
{
    private  $aFontAwesome = array() ;
    function __construct( $aFontAwesome )
    {
        $this->aFontAwesome = $aFontAwesome;
    }
    
    public function generator( $aData )
    {
        if ( !isset( $aData['image'] ) || !isset( $aData['icon'] ) || !isset( $aData['image_fieldname'] ) || !isset( $aData['icon_fieldname'] ) ) {
            return 'Invalid.';
        }
        if ( !isset( $aData['show_is_background'] ) ) {
            $aData['show_is_background'] = false;
        }
        $p = $this->generator_start( $aData );
        return $p;
    }
    
    private function generator_start( $aData )
    {
        $p = '<div class="icon-or-image" data-type="icon">
        <div class="icon-placeholder">
            <div class="placeholder-choose">
                or <span class="buttonizer-click-to-pro" style="color: #0073aa; text-decoration: underline; cursor: pointer;">upload image (PRO ONLY)</span>
            </div>';
        $p .= '<select name="' . $aData['icon_fieldname'] . '" id="' . $aData['icon_fieldname'] . '" style="font-family: \'FontAwesome\', \'Helvetica\';" class="fa-chooser">';
        foreach ( $this->aFontAwesome as $key => $row ) {
            $p .= '<option value="' . $key . '" ' . (( $aData['icon'] == $key ? 'selected="selected"' : '' )) . '>' . $row . " " . $key . '</option>';
        }
        $p .= "</select>";
        $p .= '</div>';
        return $p;
    }

}