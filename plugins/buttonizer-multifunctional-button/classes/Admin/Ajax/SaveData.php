<?php

namespace Buttonizer\Admin\Ajax;

class SaveData
{
    public function __construct()
    {
        switch ( $_GET['save'] ) {
            case 'categories':
                $this->saveCategories();
                break;
            default:
                echo  json_encode( [
                    'status'  => 'error',
                    'message' => 'No function handled',
                ] ) ;
                break;
        }
    }
    
    private function saveCategories()
    {
        echo  json_encode( [
            'status'  => 'error',
            'message' => 'You do not have Buttonizer Pro.',
        ] ) ;
    }

}