<?php
/**
 * Charitable Recurring Upgrade class.
 * 
 * The responsibility of this class is to manage migrations between versions of Charitable Recurring.
 *
 * @package     Charitable Recurring
 * @subpackage  Charitable Recurring/Upgrade
 * @copyright   Copyright (c) 2015, Kathy Darling  
 * @license     http://opensource.org/licenses/gpl-1.0.0.php GNU Public License
 * @since       1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Charitable_Recurring_Upgrade' ) ) : 

/**
 * Charitable_Recurring_Upgrade
 *
 * @see         Charitable_Upgrade
 * @since       1.0.0
 */
class Charitable_Recurring_Upgrade extends Charitable_Upgrade {

    /**
     * Array of methods to perform when upgrading to specific versions.      
     *
     * @var     array
     * @access  protected
     */
    protected $upgrade_actions = array();

    /**
     * Option key for upgrade log. 
     *
     * @var     string
     * @access  protected
     */
    protected $upgrade_log_key = 'charitable_recurring_upgrade_log';
    
    /**
     * Option key for plugin version.
     *
     * @var     string
     * @access  protected
     */
    protected $version_key = 'charitable_recurring_version';

    /**
     * Upgrade from the current version stored in the database to the live version. 
     *
     * @param   false|string $db_version    
     * @param   string $edge_version    
     * @return  void
     * @static
     * @access  public
     * @since   1.0.0
     */
    public static function upgrade_from( $db_version, $edge_version ) {

        if ( parent::requires_upgrade( $db_version, $edge_version ) ) {

            new Charitable_Recurring_Upgrade( $db_version, $edge_version );

        }
    }   
}

endif; // End class_exists check