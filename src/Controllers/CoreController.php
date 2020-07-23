<?php
namespace App\Controllers;

use App\Core\Utility;

class CoreController{

    /*
     * utility trait is worked as a helper in the controller
     */
    use Utility;
    protected $auth_user = '';

    public function __construct()
    {
        $this->checkDbConnection();
        if(empty($this->auth_user)){
            $check_login_response = $this->verifyUserSession();
            if($this->isSuccessResponse($check_login_response)){
                $this->auth_user = json_decode(json_encode($check_login_response['data']), true)['data'];
            }
        }
    }

}