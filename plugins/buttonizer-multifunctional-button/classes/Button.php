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
namespace Buttonizer;

# No script kiddies
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
class Button
{
    // Saved settings
    private  $aButtons ;
    private  $pageCategories ;
    private  $aSettings ;
    private  $aOpeningData ;
    private  $aVideo ;
    // Default settings
    private  $bIsMobile = false ;
    private  $bIsOpened = true ;
    // Current page
    private  $currentPageId = 0 ;
    private  $currentCategoryId = 0 ;
    private  $currentBlogId = 0 ;
    private  $sCurrentPageUrl = '' ;
    private  $currentPageTitle = '' ;
    private  $sCurrentPageDescription = '' ;
    private  $aAnimationSettings = array( 'default', 'circle', 'fade-left-to-right' ) ;
    private  $aAttentionAnimationSettings = array( 'none', 'hello', 'bounce' ) ;
    // Output string
    private  $iAmountOfButtons = 0 ;
    private  $iAmountOfShareButtons = 0 ;
    private  $sOutput = '' ;
    // Custom button color
    // Label settings
    private  $labelStyle = '' ;
    private  $sButtonCss = '' ;
    public function __construct( $bIsMobile )
    {
        // Set some data
        $this->bIsMobile = (bool) $bIsMobile;
        add_action( 'wp_footer', function () {
            // Setup
            $this->setup();
            // Share buttons
            $this->share_btns();
            // Generate
            $this->generate();
            // Output
            $this->output();
        } );
        add_action( 'wp_print_styles', array( &$this, 'siteLoadStyles' ) );
    }
    
    /**
     * Buttonizer setup
     */
    private function setup()
    {
        // Timezone
        //        date_default_timezone_set(get_option('timezone_string') != '' ? get_option('timezone_string') : "Europe/Amsterdam");
        // Get options
        $this->aButtons = (array) get_option( 'buttonizer_buttons' );
        $this->pageCategories = (array) get_option( 'buttonizer_page_categories' );
        $this->aOpeningData = (array) get_option( 'buttonizer_opening_settings' );
        $this->aSettings = (array) get_option( 'buttonizer_general_settings' );
        $this->aVideo = (array) get_option( 'buttonizer_videos' );
        // Current page data
        $this->currentPageId = get_the_ID();
        $this->currentCategoryId = get_the_category();
        $this->currentBlogId = get_current_blog_id();
        $this->currentPageTitle = strtolower( get_the_title() );
        $this->sCurrentPageUrl = urlencode( get_permalink() );
        $this->sCurrentPageDescription = str_replace( " ", "+", get_the_content( 'Read more' ) );
        // Is store opened
        $this->bIsOpened = $this->isOpened();
    }
    
    public function siteLoadStyles()
    {
        wp_enqueue_style( 'buttonizer', plugins_url( '/css/buttonizer.css?v=' . md5( BUTTONIZER_VERSION ), BUTTONIZER_PLUGIN_DIR ) );
        wp_enqueue_style( 'font-awesome', '//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css' );
    }
    
    // Generate
    private function generate()
    {
        $aButtons = ( isset( $this->aButtons['buttonorder'] ) && is_array( $this->aButtons['buttonorder'] ) ? $this->aButtons['buttonorder'] : [] );
        krsort( $aButtons );
        foreach ( $aButtons as $sKey => $iId ) {
            if ( '-1' == $iId ) {
                continue;
            }
            $sButton = $this->generateB( $iId );
            
            if ( 'continue' == $sButton ) {
                continue;
            } else {
                $this->sOutput .= $sButton;
            }
            
            $this->iAmountOfButtons++;
        }
    }
    
    /**
     * Button generator
     *
     * @param $bNmbr
     * @return string
     */
    private function generateB( $bNmbr )
    {
        // Check if this one must be showed when the company is open:
        if ( isset( $this->aButtons['button_' . $bNmbr . '_show_when_opened'] ) && !$this->bIsOpened ) {
            return 'continue';
        }
        // Check if this one must be showed when the company is open:
        if ( isset( $this->aButtons['button_' . $bNmbr . '_show_not_when_opened'] ) && $this->bIsOpened ) {
            return 'continue';
        }
        // Do we need to show the button?
        
        if ( !isset( $this->aButtons['button_' . $bNmbr . '_show_on_phone'] ) && !isset( $this->aButtons['button_' . $bNmbr . '_show_on_desktop'] ) ) {
            // Skipping because not showing on desktop AND phone
            return 'continue';
        } else {
            // Is the button visble on mobile phones?:
            if ( $this->bIsMobile && !isset( $this->aButtons['button_' . $bNmbr . '_show_on_phone'] ) ) {
                // Skipping because this is a mobile
                return 'continue';
            }
            // Is the button visible on desktop?:
            if ( !$this->bIsMobile && !isset( $this->aButtons['button_' . $bNmbr . '_show_on_desktop'] ) ) {
                // Skipping because this is desktop and it musn't get shown here
                return 'continue';
            }
        }
        
        /*
         * Page categories selected or not
         * Check on what pages the button must be shown
         */
        $buttonShowOnPages = ( isset( $this->aButtons['button_' . $bNmbr . '_show_on_pages'] ) ? $this->aButtons['button_' . $bNmbr . '_show_on_pages'] : 'all' );
        if ( $this->checkPageCategories( $buttonShowOnPages ) ) {
            return 'continue';
        }
        /*
         * All button info
         */
        $buttonText = ( isset( $this->aButtons['button_' . $bNmbr . '_text'] ) ? $this->aButtons['button_' . $bNmbr . '_text'] : 'Button ' . $bNmbr );
        $buttonTitle = ( isset( $this->aButtons['button_' . $bNmbr . '_title'] ) ? $this->aButtons['button_' . $bNmbr . '_title'] : 'Button ' . $bNmbr );
        $buttonIsPhone = ( isset( $this->aButtons['button_' . $bNmbr . '_is_phonenumber'] ) ? $this->aButtons['button_' . $bNmbr . '_is_phonenumber'] : '' );
        $linkNewTab = ( isset( $this->aButtons['button_' . $bNmbr . '_url_newtab'] ) ? 'target="_blank"' : '' );
        $hideLabel = ( isset( $this->aButtons['button_' . $bNmbr . '_hide_label'] ) ? $this->aButtons['button_' . $bNmbr . '_hide_label'] : '' );
        $sButtonAction = ( isset( $this->aButtons['button_' . $bNmbr . '_action'] ) ? $this->aButtons['button_' . $bNmbr . '_action'] : '' );
        $sButtonActionLink = str_replace( '"', "'", ( isset( $this->aButtons['button_' . $bNmbr . '_url'] ) ? $this->aButtons['button_' . $bNmbr . '_url'] : '' ) );
        $buttonStyle = "";
        $buttonOnClick = "";
        
        if ( 'phone' == $sButtonAction || $buttonIsPhone ) {
            $sButtonActionLink = 'tel:' . str_replace( [
                ' ',
                '+',
                '?',
                '#'
            ], '', $sButtonActionLink );
        } else {
            
            if ( 'mail' == $sButtonAction ) {
                $sButtonActionLink = 'mailto:' . $sButtonActionLink;
            } else {
                
                if ( 'backtotop' == $sButtonAction ) {
                    $sButtonActionLink = "javascript: window.scroll({top: 0, left: 0, behavior: 'smooth' });";
                } else {
                    
                    if ( 'gobackpage' == $sButtonAction ) {
                        $sButtonActionLink = "javascript: window.history.back(); ";
                    } else {
                        
                        if ( 'socialsharing' == $sButtonAction ) {
                            $socialData = ( isset( $this->aButtons['button_' . $bNmbr . '_social'] ) ? $this->aButtons['button_' . $bNmbr . '_social'] : 'twitter' );
                            $sButtonActionLink = "javascript:void(0)";
                            //Share options, action buttons
                            
                            if ( 'whatsapp' == $socialData ) {
                                $buttonOnClick = "onButtonizerClickEvent('Whatsapp share click'); onButtonizerButtonWhatsapp()";
                            } else {
                                
                                if ( 'mail' == $socialData ) {
                                    $buttonOnClick = "onButtonizerClickEvent('Email share click'); onButtonizerButtonEmail()";
                                } else {
                                    
                                    if ( 'linkedin' == $socialData ) {
                                        $buttonOnClick = "onButtonizerClickEvent('Linkedin share click'); onButtonizerButtonLinkedin()";
                                    } else {
                                        
                                        if ( 'facebook' == $socialData ) {
                                            $buttonOnClick = "onButtonizerClickEvent('Facebook share click'); onButtonizerButtonFacebook()";
                                        } else {
                                            if ( 'twitter' == $socialData ) {
                                                $buttonOnClick = "onButtonizerClickEvent('Twitter share click'); onButtonizerButtonTwitter()";
                                            }
                                        }
                                    
                                    }
                                
                                }
                            
                            }
                        
                        }
                    
                    }
                
                }
            
            }
        
        }
        
        // Has image
        
        if ( !isset( $this->aButtons['button_' . $bNmbr . '_image'] ) || empty($this->aButtons['button_' . $bNmbr . '_image']) ) {
            $sButtonIcon = '<i class="fa ' . (( isset( $this->aButtons['button_' . $bNmbr . '_icon'] ) ? $this->aButtons['button_' . $bNmbr . '_icon'] : 'plus' )) . '"></i>';
        } else {
            
            if ( isset( $this->aButtons['button_' . $bNmbr . '_image_background'] ) && $this->aButtons['button_' . $bNmbr . '_image_background'] == '1' ) {
                $sButtonIcon = "";
                $buttonStyle = "background-image: url('" . $this->aButtons['button_' . $bNmbr . '_image'] . "'); background-size: cover; background-position: center;";
            } else {
                $sButtonIcon = '<img src="' . $this->aButtons['button_' . $bNmbr . '_image'] . '" style="width: ' . (( count( $this->aButtons ) > 1 ? '25px' : '32px' )) . '; vertical-align: middle;" />';
            }
        
        }
        
        $sButtonClasses = 'is_extra bt_' . $this->iAmountOfButtons;
        // Show label on hover (per button)
        $showOnHover = ( isset( $this->aButtons['button_' . $bNmbr . '_show_label_on_hover'] ) ? $this->aButtons['button_' . $bNmbr . '_show_label_on_hover'] : '' );
        
        if ( $showOnHover == 'showOnHover' ) {
            $sButtonClasses .= ' show_on_hover';
        } else {
            
            if ( $showOnHover == 'showOnHoverDesktop' ) {
                if ( $this->bIsMobile == false ) {
                    $sButtonClasses .= ' show_on_hover';
                }
            } else {
                if ( $showOnHover == 'showOnHoverMobile' ) {
                    if ( $this->bIsMobile == true ) {
                        $sButtonClasses .= ' show_on_hover';
                    }
                }
            }
        
        }
        
        // Custom classes
        $sButtonClasses .= ' ' . $this->getCustomClass( $bNmbr );
        // Custom colors?
        if ( isset( $this->aButtons['button_' . $bNmbr . '_using_custom_colors'] ) && $this->aButtons['button_' . $bNmbr . '_using_custom_colors'] == '1' ) {
            $this->sButtonCss .= '
            .buttonizer-button .buttonizer_' . $bNmbr . ' {
                background-color: ' . $this->aButtons['button_' . $bNmbr . '_colors_button'] . ';
            }
           
            .buttonizer-button .buttonizer_' . $bNmbr . ':hover,.buttonizer_' . $bNmbr . ':active {
                background-color: ' . $this->aButtons['button_' . $bNmbr . '_colors_pushed'] . ';
            }

            .buttonizer-button .buttonizer_' . $bNmbr . ' i {
                color: ' . $this->aButtons['button_' . $bNmbr . '_colors_icon'] . ';
            } ';
        }
        return '<a href="' . $sButtonActionLink . '" class="' . $sButtonClasses . ' is_btzn_btn buttonizer_' . $bNmbr . '" ' . $linkNewTab . ' onclick="onButtonizerClickEvent(\'' . $buttonTitle . '\'); ' . $buttonOnClick . '" style="' . $buttonStyle . '">' . (( $buttonText != "" ? '<div class="text"' . (( $hideLabel == '1' ? 'style="display: none;"' : '' )) . '><div>' . $buttonText . '</div></div>' : '' )) . $sButtonIcon . '</a>';
        // Thanks, end
    }
    
    /**
     * Show on timeout
     *
     * @return float|int|string
     */
    private function showOnTimeout()
    {
        return '0';
    }
    
    /**
     * Show on scroll
     *
     * @return string|int
     */
    private function showOnScroll()
    {
        return '0';
    }
    
    /**
     * Exit intent
     *
     * @return string
     */
    private function enableExitIntent()
    {
        return '0';
    }
    
    /**
     * @param $categoryId
     * @return bool
     */
    private function checkPageCategories( $categoryId )
    {
        return false;
    }
    
    /**
     * Exit intent
     *
     * @return mixed|string
     */
    private function enableExitIntentText()
    {
        return '';
    }
    
    /**
     * Custom class
     *
     * @param $iButtonId
     * @return string
     */
    private function getCustomClass( $iButtonId )
    {
        return '';
    }
    
    /**
     * Is the store opened right now?
     *
     * @return bool
     */
    private function isOpened()
    {
        $sDayOfWeek = $this->getDay( date( 'N' ) );
        // Result
        $bTodayOpened = ( isset( $this->aOpeningData['buttonizer_' . $sDayOfWeek . '_opened'] ) ? $this->aOpeningData['buttonizer_' . $sDayOfWeek . '_opened'] : '' );
        // If result == 1 => Opened
        
        if ( $bTodayOpened != "1" ) {
            $bIsOpened = false;
        } else {
            // Get the time right now
            $sCurentHour = date( "G" );
            $sCurrentMinute = date( "i" );
            // Get the opening and closing time from today.
            $hourOpening = explode( ':', ( isset( $this->aOpeningData['buttonizer_' . $sDayOfWeek . '_opened_from'] ) ? $this->aOpeningData['buttonizer_' . $sDayOfWeek . '_opened_from'] : '10:00' ) );
            $hourClosing = explode( ':', ( isset( $this->aOpeningData['buttonizer_' . $sDayOfWeek . '_closing_on'] ) ? $this->aOpeningData['buttonizer_' . $sDayOfWeek . '_closing_on'] : '17:00' ) );
            // Check if the company is opened/closed
            
            if ( $sCurentHour < $hourOpening[0] || $sCurentHour == $hourOpening[0] && $sCurrentMinute < $hourOpening[1] || $sCurentHour > $hourClosing[0] || $sCurentHour == $hourClosing[0] && $sCurrentMinute > $hourClosing[1] - 1 ) {
                $bIsOpened = false;
            } else {
                $bIsOpened = true;
            }
        
        }
        
        return $bIsOpened;
    }
    
    /**
     * Day-number to text
     *
     * @param $sDay
     * @return string
     */
    private function getDay( $sDay )
    {
        switch ( $sDay ) {
            case '1':
                return 'monday';
                break;
            case '2':
                return 'tuesday';
                break;
            case '3':
                return 'wednesday';
                break;
            case '4':
                return 'thursday';
                break;
            case '5':
                return 'friday';
                break;
            case '6':
                return 'saturday';
                break;
            case '7':
                return 'sunday';
                break;
            default:
                return 'sunday';
                break;
        }
    }
    
    /**
     * Share buttons
     */
    private function share_btns()
    {
        
        if ( isset( $this->aSettings["share_facebook"] ) && '1' == $this->aSettings["share_facebook"] ) {
            $buttonText = ( isset( $this->aSettings['share_facebook_text'] ) && $this->aSettings['share_facebook_text'] != '' ? $this->aSettings['share_facebook_text'] : 'Share this on Facebook' );
            $this->sOutput = '<a href="javascript:void(0)" onclick="onButtonizerClickEvent(\'Facebook share click\'); onButtonizerButtonFacebook();" class="is_extra share share_' . ($this->iAmountOfShareButtons + 1) . '">' . (( $buttonText != "" ? '<div class="text"><div>' . $buttonText . '</div></div>' : '' )) . '<i class="fa fa-facebook"></i></a>' . $this->sOutput;
            $this->iAmountOfShareButtons++;
        }
        
        
        if ( isset( $this->aSettings["share_twitter"] ) && '1' == $this->aSettings["share_twitter"] ) {
            $buttonText = ( isset( $this->aSettings['share_twitter_text'] ) && $this->aSettings['share_twitter_text'] != '' ? $this->aSettings['share_twitter_text'] : 'Share this on Twitter' );
            $this->sOutput = '<a href="javascript:void(0)" onclick="onButtonizerClickEvent(\'Twitter share click\'); onButtonizerButtonTwitter();" class="is_extra share share_' . ($this->iAmountOfShareButtons + 1) . '">' . (( $buttonText != "" ? '<div class="text"><div>' . $buttonText . '</div></div>' : '' )) . '<i class="fa fa-twitter"></i></a>' . $this->sOutput;
            $this->iAmountOfShareButtons++;
        }
        
        
        if ( isset( $this->aSettings["share_linkedin"] ) && '1' == $this->aSettings["share_linkedin"] ) {
            $buttonText = ( isset( $this->aSettings['share_linkedin_text'] ) && $this->aSettings['share_linkedin_text'] != '' ? $this->aSettings['share_linkedin_text'] : 'Share this on LinkedIn' );
            $this->sOutput = '<a href="javascript:void(0)" onclick="onButtonizerClickEvent(\'Linkedin share click\'); onButtonizerButtonLinkedin();" class="is_extra share share_' . ($this->iAmountOfShareButtons + 1) . '">' . (( $buttonText != "" ? '<div class="text"><div>' . $buttonText . '</div></div>' : '' )) . '<i class="fa fa-linkedin"></i></a>' . $this->sOutput;
            $this->iAmountOfShareButtons++;
        }
        
        
        if ( isset( $this->aSettings["share_email"] ) && '1' == $this->aSettings["share_email"] ) {
            $buttonText = ( isset( $this->aSettings['share_email_text'] ) && $this->aSettings['share_email_text'] != '' ? $this->aSettings['share_email_text'] : 'Share this on Email' );
            $this->sOutput = '<a href="javascript:void(0)" onclick="onButtonizerClickEvent(\'Email share click\'); onButtonizerButtonEmail();" class="is_extra share share_' . ($this->iAmountOfShareButtons + 1) . '">' . (( $buttonText != "" ? '<div class="text"><div>' . $buttonText . '</div></div>' : '' )) . '<i class="fa fa-envelope"></i></a>' . $this->sOutput;
            $this->iAmountOfShareButtons++;
        }
        
        // Coming next update: :)
        if ( wp_is_mobile() == true ) {
            
            if ( isset( $this->aSettings["share_whatsapp"] ) && '1' == $this->aSettings["share_whatsapp"] ) {
                $buttonText = ( isset( $this->aSettings['share_whatsapp_text'] ) && $this->aSettings['share_whatsapp_text'] != '' ? $this->aSettings['share_whatsapp_text'] : 'Share this on Whatsapp' );
                $this->sOutput = '<a href="javascript:void(0)" onclick="onButtonizerClickEvent(\'Whatsapp share click\'); onButtonizerButtonWhatsapp();" class="is_extra share share_' . ($this->iAmountOfShareButtons + 1) . '">' . (( $buttonText != "" ? '<div class="text"><div>' . $buttonText . '</div></div>' : '' )) . '<i class="fa fa-whatsapp"></i></a>' . $this->sOutput;
                $this->iAmountOfShareButtons++;
            }
        
        }
        //
        //        if(isset($this->aSettings["share_pinterest"]) && '1' == $this->aSettings["share_pinterest"]) {
        //            $buttonText = (isset($this->aSettings['share_pinterest_text']) && $this->aSettings['share_pinterest_text'] != '' ? ($this->aSettings['share_pinterest_text']) : 'Share this on Pinterest');
        //
        //            $this->sOutput = '<a href="javascript:void(0)" onclick="onButtonizerClickEvent(\'Pinterest share click\'); onButtonizerButtonPinterest();" class="is_extra share share_' . ($this->iAmountOfShareButtons + 1) . '">' . ($buttonText != "" ? '<div class="text"><div>' . $buttonText . '</div></div>' : '') . '<i class="fa fa-pinterest"></i></a>' . $this->sOutput;
        //
        //            $this->iAmountOfShareButtons++;
        //        }
    }
    
    /**
     * Get the positioning of the buttons
     * @param string $sPositioning
     * @return string
     */
    public function buttonPositioning( $sPositioning = '' )
    {
        $sPositioning .= (( isset( $this->aSettings['buttons_placing'] ) && $this->aSettings['buttons_placing'] == 'left' ? 'left' : 'right' )) . ': ' . (( isset( $this->aSettings['position_horizontal'] ) ? $this->aSettings['position_horizontal'] : (( isset( $this->aSettings['position_right'] ) ? $this->aSettings['position_right'] : '5' )) )) . '%;';
        $sPositioning .= 'bottom: ' . (( isset( $this->aSettings['position_bottom'] ) ? $this->aSettings['position_bottom'] : '5' )) . '%;';
        return $sPositioning;
    }
    
    /**
     * Output of the button
     */
    public function output()
    {
        $showAfterTimeout = $this->showOnTimeout();
        $showOnScroll = $this->showOnScroll();
        // Main button image or icon
        $sImage = ( isset( $this->aSettings['custom_icon'] ) ? $this->aSettings['custom_icon'] : '' );
        
        if ( $sImage != '' ) {
            $sIcon = '<img src="' . $sImage . '" style="width: 32px; vertical-align: middle;" />';
        } else {
            $sIcon = ( !empty($this->aSettings['icon_icon']) && $this->aSettings['icon_icon'] != '+' ? '<i class="isicon-fa fa ' . $this->aSettings['icon_icon'] . '"></i>' : '<i>+</i>' );
        }
        
        
        if ( $this->iAmountOfButtons == 1 && $this->iAmountOfShareButtons == 0 ) {
            $output = str_replace( "is_extra bt_0", "buttonizer_head onlyone", $this->sOutput );
        } else {
            
            if ( $this->iAmountOfButtons > 1 || $this->iAmountOfShareButtons > 0 ) {
                $output = '<a href="javascript:void(0)" class="buttonizer_head" onclick="onButtonizerClickEvent(\'Open/Close Buttonizer button\')">' . (( !empty($this->aSettings['icon_label']) ? '<div class="text noremove"><div>' . $this->aSettings['icon_label'] . '</div></div>' : '' )) . $sIcon . '</a>' . $this->sOutput;
            } else {
                $output = '';
            }
        
        }
        
        // Google analytics toevoegen
        if ( isset( $this->aSettings['google_analytics'] ) && $this->aSettings['google_analytics'] != "" ) {
            $output .= "<script>(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){ (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o), m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m) })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');ga('create', '" . $this->aSettings['google_analytics'] . "', 'auto');</script>";
        }
        
        if ( isset( $this->aSettings['buttons_animation'] ) && in_array( $this->aSettings['buttons_animation'], $this->aAnimationSettings ) ) {
            $sButtonAnimation = $this->aSettings['buttons_animation'];
        } else {
            $sButtonAnimation = 'default';
        }
        
        
        if ( isset( $this->aSettings['attention_animation'] ) && in_array( $this->aSettings['attention_animation'], $this->aAttentionAnimationSettings ) ) {
            $sAttentionAnimation = $this->aSettings['attention_animation'];
        } else {
            $sAttentionAnimation = 'default';
        }
        
        // Label hovering
        
        if ( !isset( $this->aSettings['buttons_label_show_on_hover'] ) || $this->aSettings['buttons_label_show_on_hover'] == 'default' ) {
            $labelStyle = 'buttonizer_label_default';
        } else {
            
            if ( $this->aSettings['buttons_label_show_on_hover'] == 'showOnHover' ) {
                $labelStyle = 'buttonizer_label_hover';
            } else {
                
                if ( $this->aSettings['buttons_label_show_on_hover'] == 'showOnHoverDesktop' ) {
                    
                    if ( $this->bIsMobile == false ) {
                        $labelStyle = 'buttonizer_label_hover';
                    } else {
                        $labelStyle = 'buttonizer_label_default';
                    }
                
                } else {
                    if ( $this->aSettings['buttons_label_show_on_hover'] == 'showOnHoverMobile' ) {
                        
                        if ( $this->bIsMobile == true ) {
                            $labelStyle = 'buttonizer_label_hover';
                        } else {
                            $labelStyle = 'buttonizer_label_default';
                        }
                    
                    }
                }
            
            }
        
        }
        
        list( $sShadowColorRed, $sShadowColorGreen, $sShadowColorBlue ) = sscanf( ( isset( $this->aSettings['button_unpushed'] ) ? $this->aSettings['button_unpushed'] : '#1D9BDB' ), "#%02x%02x%02x" );
        // Mobile button size vs Desktop button size
        
        if ( $this->bIsMobile ) {
            $iconSize = ( isset( $this->aSettings['mobile_icon_size'] ) ? $this->aSettings['mobile_icon_size'] : 56 );
        } else {
            $iconSize = ( isset( $this->aSettings['icon_size'] ) ? $this->aSettings['icon_size'] : 56 );
        }
        
        echo  '
        <style>
            .buttonizer-button a:hover, 
            .buttonizer-button a:focus{ background:' . (( isset( $this->aSettings['button_pushed'] ) ? $this->aSettings['button_pushed'] : '' )) . '; }
            .buttonizer-button a { background:' . (( isset( $this->aSettings['button_unpushed'] ) ? $this->aSettings['button_unpushed'] : '' )) . '; }
            .buttonizer-button a i { color: ' . (( isset( $this->aSettings['icon_color'] ) ? $this->aSettings['icon_color'] : '' )) . '; }
            
            .buttonizer-button a.buttonizer_head, .buttonizer-button a.buttonizer_head i {
                height: ' . $iconSize . 'px !important;
                width: ' . $iconSize . 'px !important;
                line-height: ' . $iconSize . 'px !important;
            }
            
            .buttonizer-button a.buttonizer_head, .buttonizer-button a.buttonizer_head  {
                margin-left: -' . ($iconSize - 56) / 2 . 'px;
                margin-top: -' . ($iconSize - 56) / 2 . 'px;
            }
            
            .buttonizer-button a.buttonizer_head, .buttonizer-button a.is_extra  {
                margin-top: -' . ($iconSize - 56) . 'px;
            }
            .buttonizer-button a.buttonizer_head .text{
                 right: ' . ($iconSize + 20) . 'px !important;
                 top: ' . $iconSize / 3 . 'px !important;
            }
            
            
            .buttonizer-button[label-style="mirrored"] a.is_extra.share  {
                margin-top: -' . ($iconSize - 56) / 2 . 'px;
                margin-left: ' . ($iconSize - 56) . 'px;
            }
            
            .buttonizer-button[label-style="default"] a.is_extra.share  {
                margin-top: -' . ($iconSize - 56) / 2 . 'px;
                margin-left: -' . ($iconSize - 56) . 'px !important;
            }
            
            .buttonizer-button a .text{
                background-color: ' . (( isset( $this->aSettings['label_color'] ) ? $this->aSettings['label_color'] : '#4e4c4c' )) . ';
                color:  ' . (( isset( $this->aSettings['label_text_color'] ) ? $this->aSettings['label_text_color'] : '#FFFFFF' )) . ';
            }
            
            
           
            ' . $this->sButtonCss . '
        </style>
        <div class="buttonizer-button ' . (( $showOnScroll > 0 || $showAfterTimeout > 0 ? 'hide' : '' )) . ' ' . $labelStyle . '" 
            button-animation="' . $sButtonAnimation . '" 
            attention-animation="' . $sAttentionAnimation . '" 
              
            
            label-style="' . (( isset( $this->aSettings['buttons_label_placing'] ) ? $this->aSettings['buttons_label_placing'] : 'default' )) . '"
          
          
            style="' . $this->buttonPositioning() . '" 
            id="buttonizer-button"><div class="buttonizer_inner" id="buttonizer-sys">' . $output . '</div></div>' ;
        echo  '
        <script defer type="text/javascript" src="' . plugins_url( '/js/buttonizer.js?v=' . md5( BUTTONIZER_VERSION ), BUTTONIZER_PLUGIN_DIR ) . '"></script>
        <script type="text/javascript">
        document.addEventListener(\'DOMContentLoaded\', function(){
            buttonizer.init({
                scrollBarTop: ' . $showOnScroll . ',
                showAfter: ' . $showAfterTimeout . ',
                ' . (( ButtonizerLicense()->is__premium_only() ? 'exitIntent: ' . $this->enableExitIntent() . ',' : '' )) . '
                ' . (( ButtonizerLicense()->is__premium_only() ? 'exitIntentText: "' . $this->enableExitIntentText() . '",' : '' )) . '
            });
        });
        </script>' ;
    }

}