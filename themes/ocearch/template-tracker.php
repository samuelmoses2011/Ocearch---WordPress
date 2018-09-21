<?php
/*
Template Name: Ocearch Tracker + Nav
*/
?>
<?php get_header(); ?>



<div id="content">
    <link rel="stylesheet" href="<?php echo get_stylesheet_directory_uri(); ?>/tracker/ocearch-tracker.bundle.css">
    <style>
        html {
            overflow: auto !important;
            height: auto !important;
        }
        body .och-tracker blockquote {
            padding: 0;
        }
        .och-app-bar,
        .buttonizer-button,
        #to-top,
        #header-space,
        #ajax-content-wrap:after,
        #footer-outer {
            display: none !important;
        }
        #header-outer {
            z-index: 10000000000;
        }
        #root {
            position: relative;
            min-height: 100vh;
        }
        #root * {
            font-weight: 500;
        }
        #modal-root {
            line-height: initial;
        }
        .och-tracker {
            line-height: normal;
        }
        .och-tracker button.blank:not(.circle) {
            border-radius: 0 !important;
        }
        .och-tracker .bg-cover {
            background-position: 50%;
        }
        .och-tracker .sticky {
            margin-bottom: 0;
        }
        .och-tracker-details .view-pager__view {
            height: 100%;
        }
        .och-tracker img {
            display: block;
        }
        .och-social-feed__image iframe {
            height: 370px;
        }
        body .och-tracker input[type="text"] {
            padding: 16px !important;
        }
        .och-tracker input::placeholder {
            color: #000 !important;
        }
        #header-outer #logo .och-ocearch-logo img {
            height: 32px;
        }
        #header-outer #logo .och-ocearch-logo div > img {
            height: 18px;
        }
        .och-ocearch-logo {
            position: relative;
            top: -22px;
        }
        .och-logo__container {
            position: absolute;
        }
        .och-logo__container + * {
            opacity: 0;
        }
        .och-overlay {
            height: calc(100vh - 61px) !important;
        }
        body #root .och-map__life-info--actions button {
            border-radius: 0 !important;
            -webkit-border-radius: 0 !important;
        }
        @media only screen and (max-width: 1000px) {
            #header-outer #logo .och-ocearch-logo img {
                height: 24px !important;
            }
            #header-outer #logo .och-ocearch-logo div > img {
                height: 16px !important;
            }
            .och-ocearch-logo {
                top: 0;
            }
            body .slide-out-widget-area-toggle {
                top: -15px !important;
            }
        }
        
        @media screen and (max-width: 720px) {
            #root {
                top: -61px;
            }
            .och-drawer.active {
                top: 60px;
            }
            .och-drawer.active .och-drawer__main {
                height: calc(100% - 58px) !important;
            }
        }
        @media screen and ( min-width: 760px ) {
            .och-tracker-bar {
                top: 65px;
            }
            .och-drawer.active {
                height: calc(100vh - 65px);
            }
            .och-drawer.right {
                top: 65px;
            }
        }
    </style>
    <script>
        window.OCH = {
            assetPath: "<?php echo get_stylesheet_directory_uri(); ?>/tracker"
        };
    </script>

    <div id="root"></div>
    

    <script src="<?php echo get_stylesheet_directory_uri(); ?>/tracker/ocearch-tracker.browser.js"></script>
    <script src="https://unpkg.com/axios/dist/axios.min.js"></script>
    <script>
        axios.get('http://ocearch-tracker.us-east-2.elasticbeanstalk.com/api/ocean/life') // Cant access as Endpoint isn't served securely
        .then(function(response){
            console.log(response.data); 
        });  
    </script>
</div>

<?php get_sidebar(); ?>
<?php get_footer(); ?>
