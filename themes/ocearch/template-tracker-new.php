<?php
/*
Template Name: Ocearch Tracker + Nav
*/
?>
<?php get_header(); ?>

<link rel="stylesheet" href="<?php echo get_stylesheet_directory_uri(); ?>/tracker/ocearch-tracker.bundle.css">
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

<?php get_sidebar(); ?>