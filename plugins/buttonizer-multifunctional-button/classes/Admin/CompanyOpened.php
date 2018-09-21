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
class CompanyOpened
{
    private  $aSavedData = array() ;
    public function __construct()
    {
        $this->loadData();
        $this->createTable();
    }
    
    private function loadData()
    {
        $this->aSavedData = (array) get_option( 'buttonizer_opening_settings' );
    }
    
    public function createTable()
    {
        echo  '<div class="time-table">' ;
        echo  '<h4>' . $this->fieldOpenedToday( 'monday' ) . '</h4>' ;
        echo  $this->fieldOpeningTimeSelector( 'monday', false ) ;
        echo  '<h4>' . $this->fieldOpenedToday( 'tuesday' ) . '</h4>' ;
        echo  $this->fieldOpeningTimeSelector( 'tuesday', false ) ;
        echo  '<h4>' . $this->fieldOpenedToday( 'wednesday' ) . '</h4>' ;
        echo  $this->fieldOpeningTimeSelector( 'wednesday', false ) ;
        echo  '<h4>' . $this->fieldOpenedToday( 'thursday' ) . '</h4>' ;
        echo  $this->fieldOpeningTimeSelector( 'thursday', false ) ;
        echo  '<h4>' . $this->fieldOpenedToday( 'friday' ) . '</h4>' ;
        echo  $this->fieldOpeningTimeSelector( 'friday', false ) ;
        echo  '<h4>' . $this->fieldOpenedToday( 'saturday' ) . '</h4>' ;
        echo  $this->fieldOpeningTimeSelector( 'saturday', false ) ;
        echo  '<h4>' . $this->fieldOpenedToday( 'sunday' ) . '</h4>' ;
        echo  $this->fieldOpeningTimeSelector( 'sunday', false ) ;
        echo  '</div>' ;
        echo  '<script>initOpeningTimes()</script>' ;
        echo  '<div class="buttonizer-pro-text"><i class="fa fa-lock"></i> You are missing the fun! When upgrading to <a href="Admin.php?page=Buttonizer-pricing">Buttonizer Pro</a> you will unlock opening hours.</div>' ;
    }
    
    function fieldOpeningTimeSelector( $sDay )
    {
        return '<div class="buttonizer-slider buttonizer-click-to-pro" data-day="' . $sDay . '"></div>';
    }
    
    function fieldOpenedToday( $sDay = 'monday' )
    {
        return '<input type="checkbox" class="buttonizer-click-to-pro" readonly /> <span class="buttonizer-click-to-pro">Opened on ' . $sDay . ' (<span class="opening-' . $sDay . '"></span>)</span>';
    }

}