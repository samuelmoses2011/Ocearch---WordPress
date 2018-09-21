<?php

$cminds_plugin_config = array(
	'plugin-is-pro'				 => FALSE,
	'plugin-has-addons'			 => FALSE,
	'plugin-version'			 => '1.0.10',
	'plugin-abbrev'				 => 'cmtoc',
	'plugin-short-slug'			 => 'cmtoc',
	'plugin-parent-short-slug'	 => '',
    'plugin-affiliate'               => '',
    'plugin-redirect-after-install'  => admin_url( 'admin.php?page=cmtoc_settings' ),
    'plugin-show-guide'              => TRUE,
    'plugin-guide-text'              => '    <div style="display:block">
        <ol>
            <li>Edit  the page or post you want to add the TOC to. while editing make sure this post has h1 / h2 / h3 headlines defined</li>
            <li>Scroll down to the bottom of the page / post while editing and select the option <strong>"Search for Table Of Contents items on this post/page"</strong></li>
             <li>In the plugin settings you can modify the tags which are used to identify the TOC headlines</li>
          </ol>
    </div>',
    'plugin-guide-video-height'      => 240,
    'plugin-guide-videos'            => array(
        array( 'title' => 'Installation tutorial', 'video_id' => '159221615' ),
    ),
        'plugin-upgrade-text'           => 'Good Reasons to Upgrade to Pro',
    'plugin-upgrade-text-list'      => array(
        array( 'title' => 'Introduction to the table of contents', 'video_time' => '0:00' ),
        array( 'title' => 'TOC Custom posts settings', 'video_time' => '0:30' ),
        array( 'title' => 'Advanced TOC general settings', 'video_time' => '0:40' ),
        array( 'title' => 'Advanced TOC elements detection settings', 'video_time' => '1:06' ),
        array( 'title' => 'Advanced TOC styling support', 'video_time' => '1:24' ),
        array( 'title' => 'TOC Shortcode support', 'video_time' => '1:52' ),
        array( 'title' => 'Demo of advanced TOC features', 'video_time' => '2:00' ),
        array( 'title' => 'TOC detection and settings per post', 'video_time' => '2:38' ),
    ),
    'plugin-upgrade-video-height'   => 240,
    'plugin-upgrade-videos'         => array(
        array( 'title' => 'Table of Contents Premium Features', 'video_id' => '130259229' ),
    ),
	'plugin-file'				 => CMTOC_PLUGIN_FILE,
	'plugin-dir-path'			 => plugin_dir_path( CMTOC_PLUGIN_FILE ),
	'plugin-dir-url'			 => plugin_dir_url( CMTOC_PLUGIN_FILE ),
	'plugin-basename'			 => plugin_basename( CMTOC_PLUGIN_FILE ),
	'plugin-icon'				 => '',
	'plugin-name'				 => CMTOC_NAME,
	'plugin-license-name'		 => CMTOC_NAME,
	'plugin-slug'				 => '',
	'plugin-menu-item'			 => CMTOC_SETTINGS_OPTION,
	'plugin-textdomain'			 => CMTOC_SLUG_NAME,
	'plugin-userguide-key'		 => '271-cm-table-of-contents',
	'plugin-store-url'			 => 'https://www.cminds.com/wordpress-plugins-library/purchase-cm-table-of-content-plugin-for-wordpress/',
	'plugin-review-url'			 => 'https://wordpress.org/support/view/plugin-reviews/cm-table-of-content',
	'plugin-changelog-url'		 => 'https://www.cminds.com/wordpress-plugins-library/purchase-cm-table-of-content-plugin-for-wordpress/#changelog',
	'plugin-licensing-aliases'	 => array( ),
	'plugin-compare-table'	 => '
          <div class="pricing-table" id="pricing-table"><h2 style="padding-left:10px;">Upgrade The Table Of Contents Plugin:</h2>
                <ul>
                    <li class="heading" style="background-color:red;">Current Edition</li>
                    <li class="price">FREE<br /></li>
              <li style="text-align:left;"><span class="dashicons dashicons-yes"></span>Ability to choose the tag for each level</li>
                    <li style="text-align:left;"><span class="dashicons dashicons-yes"></span>Ability to choose the class for each level</li>
                    <li style="text-align:left;"><span class="dashicons dashicons-yes"></span>Ability to choose the id for each level</li>
                    <li style="text-align:left;"><span class="dashicons dashicons-yes"></span>Change the label for the Table of Contents</li>
                    <li style="text-align:left;"><span class="dashicons dashicons-yes"></span>Ability to change the font-size for each level</li>
                   <hr>
                    Other CreativeMinds Offerings
                    <hr>
                 <a href="https://www.cminds.com/wordpress-plugins-library/seo-keyword-hound-wordpress/" target="blank"><img src="' . plugin_dir_url( __FILE__ ). 'views/Hound2.png"  width="220"></a><br><br><br>
                <a href="https://www.cminds.com/store/cm-wordpress-plugins-yearly-membership/" target="blank"><img src="' . plugin_dir_url( __FILE__ ). 'views/banner_yearly-membership_220px.png"  width="220"></a><br>
                </ul>
                <ul>
                    <li class="heading">Pro<a href="https://www.cminds.com/wordpress-plugins-library/purchase-cm-table-of-content-plugin-for-wordpress/" style="float:right;font-size:11px;color:white;" target="_blank">More</a></li>
                    <li class="price">$29.00<br /> <span style="font-size:14px;">(For one Year / Site)<br />Additional pricing options available <a href="https://www.cminds.com/wordpress-plugins-library/purchase-cm-table-of-content-plugin-for-wordpress/" target="_blank"> >>> </a></span> <br /></li>
                    <li class="action"><a href="https://www.cminds.com/?edd_action=add_to_cart&download_id=41391&wp_referrer=https://www.cminds.com/checkout/&edd_options[price_id]=1" style="font-size:18px;" target="_blank">Upgrade Now</a></li>
                     <li style="text-align:left;"><span class="dashicons dashicons-yes"></span>All Free Version Features <span class="dashicons dashicons-admin-comments cminds-package-show-tooltip" style="color:green" title="All free features are supported in the pro"></span></li>
<li style="text-align:left;"><span class="dashicons dashicons-yes"></span>Table of Contents Elements<span class="dashicons dashicons-admin-comments cminds-package-show-tooltip" style="color:green" title="TOC can be defined by tag, class and id"></span></li>
<li style="text-align:left;"><span class="dashicons dashicons-yes"></span>Collapse Expand<span class="dashicons dashicons-admin-comments cminds-package-show-tooltip" style="color:green" title="TOC can be collapsed on page upload"></span></li>
<li style="text-align:left;"><span class="dashicons dashicons-yes"></span>Automatic Creation<span class="dashicons dashicons-admin-comments cminds-package-show-tooltip" style="color:green" title="TOC can be automatically created for all site pages"></span></li>
<li style="text-align:left;"><span class="dashicons dashicons-yes"></span>Unique Tags and Classes<span class="dashicons dashicons-admin-comments cminds-package-show-tooltip" style="color:green" title="TOC can be defined using unique tags on each post or page"></span></li>
<li style="text-align:left;"><span class="dashicons dashicons-yes"></span>Toc Location in Page<span class="dashicons dashicons-admin-comments cminds-package-show-tooltip" style="color:green" title="TOC can be inserted in any specific location on a post or a page using a shortcode"></span></li>
<li style="text-align:left;"><span class="dashicons dashicons-yes"></span>Define specific tags per each page or post<span class="dashicons dashicons-admin-comments cminds-package-show-tooltip" style="color:green" title="In each page or post, you can use the metabox to define the specific tag, class or id to use for each level. This definitions can override the plugin global settings."></span></li>
<li style="text-align:left;"><span class="dashicons dashicons-yes"></span>Style TOC<span class="dashicons dashicons-admin-comments cminds-package-show-tooltip" style="color:green" title="The plugin general settings include specific instructions for how to style the table of contents using font size, color, weight, and style."></span></li>
<li style="text-align:left;"><span class="dashicons dashicons-yes"></span>Support Custom Post Type<span class="dashicons dashicons-admin-comments cminds-package-show-tooltip" style="color:green" title="Plugin supports any custom post type. User can specify which types of posts to include in the plugin general settings."></span></li>
<li style="text-align:left;"><span class="dashicons dashicons-yes"></span>Define hide and show behavior<span class="dashicons dashicons-admin-comments cminds-package-show-tooltip" style="color:green" title="Plugin supports hiding the TOC on page upload and displays a specific label to open and close the TOC."></span></li>
<li style="text-align:left;"><span class="dashicons dashicons-yes"></span>Child Pages Support<span class="dashicons dashicons-admin-comments cminds-package-show-tooltip" style="color:green" title="Support showing the table of content to navigate to  child pages of the parent page"></span></li>
<li style="text-align:left;"><span class="dashicons dashicons-yes"></span>Widget support<span class="dashicons dashicons-admin-comments cminds-package-show-tooltip" style="color:green" title="TOC can be displayed using a sidebar widget"></span></li>
<li style="text-align:left;"><span class="dashicons dashicons-yes"></span>Next Page Support<span class="dashicons dashicons-admin-comments cminds-package-show-tooltip" style="color:green" title="In long paginated posts, TOC can point to the exact location in the pages post."></span></li>                
                <li class="support" style="background-color:lightgreen; text-align:left; font-size:14px;"><span class="dashicons dashicons-yes"></span> One year of expert support <span class="dashicons dashicons-admin-comments cminds-package-show-tooltip" style="color:grey" title="You receive 365 days of WordPress expert support. We will answer questions you have and also support any issue related to the plugin. We will also provide on-site support."></span><br />
                         <span class="dashicons dashicons-yes"></span> Unlimited product updates <span class="dashicons dashicons-admin-comments cminds-package-show-tooltip" style="color:grey" title="During the license period, you can update the plugin as many times as needed and receive any version release and security update"></span><br />
                        <span class="dashicons dashicons-yes"></span> Plugin can be used forever <span class="dashicons dashicons-admin-comments cminds-package-show-tooltip" style="color:grey" title="Once license expires, If you choose not to renew the plugin license, you can still continue to use it as long as you want."></span><br />
                        <span class="dashicons dashicons-yes"></span> Save 40% once renewing license <span class="dashicons dashicons-admin-comments cminds-package-show-tooltip" style="color:grey" title="Once license expires, If you choose to renew the plugin license you can do this anytime you choose. The renewal cost will be 35% off the product cost."></span></li>
                  </ul>

            </div>',
);