<?php namespace Buttonizer\Admin\Ajax;

class WordpressOverview
{
    /**
     * WordpressOverview constructor.
     */
    public function __construct()
    {
        switch ($_GET['get'])
        {
            case 'categories':
                $this->getCategories();
                break;

            case 'pages':
                $this->getPages();
                break;

            case 'blogposts':
                $this->getBlogposts();
                break;

            default:
                echo json_encode([
                    'status'    => 'error',
                    'message'   => 'No function handled'
                ]);

                break;
        }
    }

    /**
     * Get page list
     */
    private function getPages()
    {
        $pagesData = \get_pages();

        $pagesOverview = [];

//        $pagesOverview[] = [
//            'id' => 0,
//            'name' => 'Homepage (index)'
//        ];


        foreach($pagesData as $data)
        {
            if(isset($_GET['search']) && $_GET['search'] != '')
            {
                $title = strtolower($data->post_title);
                $keyword = strtolower($_GET['search']);

                if(!is_numeric(strpos($title, $keyword))) {
                    continue;
                }
            }

            $pagesOverview[] = [
                'id'    => $data->ID,
                'name' => $data->post_title
            ];
        }

        echo json_encode([
            'status'    => 'ok',
            'results'     => $pagesOverview,
            'results_count'   => count($pagesOverview)
        ]);
    }

    /**
     * Get category list
     */
    private function getCategories()
    {
        $categorieData = \get_categories();

        $categorieOverview = [];

        foreach($categorieData as $data)
        {
            if(isset($_GET['search']) && $_GET['search'] != '')
            {
                $title = strtolower($data->name);
                $keyword = strtolower($_GET['search']);

                if(!is_numeric(strpos($title, $keyword))) {
                    continue;
                }
            }

            $categorieOverview[] = [
                'id'    => $data->cat_ID,
                'name' => $data->name
            ];
        }

        echo json_encode([
            'status'    => 'ok',
            'results'     => $categorieOverview,
            'results_count'   => count($categorieOverview)
        ]);
    }

    /**
     * Get blogposts
     */
    private function getBlogposts()
    {
        $blogPosts = \get_posts();

        $postOverview = [];

        foreach($blogPosts as $data)
        {
            if(isset($_GET['search']) && $_GET['search'] != '')
            {
                $title = strtolower($data->name);
                $keyword = strtolower($_GET['search']);

                if(!is_numeric(strpos($title, $keyword))) {
                    continue;
                }
            }

            $postOverview[] = [
                'id'    => $data->ID,
                'name' => $data->post_title
            ];
        }

        echo json_encode([
            'status'    => 'ok',
            'results'     => $postOverview,
            'results_count'   => count($postOverview)
        ]);
    }
}