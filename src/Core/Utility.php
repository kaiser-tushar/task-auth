<?php
namespace App\Core;

use App\Models\CoreModel;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Firebase\JWT\JWT;

trait Utility{
    /*
     * Check various function success or failure response
     *
     * @return - true if success response otherwise false
     */
    public function isSuccessResponse($response){
        if(isset($response['status']) && $response['status'] == 'success'){
            return true;
        }
        return false;
    }
    /*
     * Prepare Json response and send to browser
     */
    public function jsonResponse($response){
        header('Content-Type: application/json');
        echo json_encode($response);
    }
    /*
     * Database Connection checker
     *
     * Throw error if database is not connected
     */
    protected function checkDbConnection(){
        $coreModel = new CoreModel();
        $db_connection_response = $coreModel->makeConnection();

        if(!$this->isSuccessResponse($db_connection_response)){
            $message = 'There is a Database connection problem.<br>Reason: '.$db_connection_response['message'].'<br>Please check your <b>$databaseCredentials</b> in src/Core/Config.php<br>Also check <u>Database/alter_query.sql</u> for any alter';
            $this->loadView('error',['message' => $message]);
            die();
        }
    }

    /*
     * Twig template to load a view
     *
     * Search in src/Views folder
     *
     * @param string $path to load a view
     * @param array $data to send any required data in a view
     *
     * @return HTML view
     */
    protected function loadView($path = '',$data = []){
        if(!empty($path)){
            $exploded_path = explode('.',$path);
            $total_index = count($exploded_path);
            $filename = $exploded_path[$total_index-1].'.html';
            if($total_index > 1){
                $extra_path = array_slice($exploded_path,0,$total_index-1);
                $path = 'src/Views/'.implode('/',$extra_path);
            } else{
                $path = 'src/Views';
            }
        }
        $loader = new FilesystemLoader([$path,'src/Views']);
        $twig = new Environment($loader);

        echo $twig->render($filename,$data);
    }
    /*
    * JWT token generation
    *
    * @param array $data_to_encrypt which will be encrypted in token
    * @param string $algorithm which algorithm to encrypt data
    *
    * @return array contain status of operation and token if success
    */
    function generateToken($data_to_encrypt = [],$options = []){
        $response = [
            'status' =>'error',
            'message' => 'Something went wrong.Error code:5.'
        ];
        try{
            $time = time();
            $expire = $time + (empty($options['expire'])?(3600 * 24 * 1):$options['expire']);
            $algorithm =empty($options['algorithm'])?Config::JWT_ALGORITHM:$options['algorithm'];
            $secret_key =empty($options['secret_key'])?Config::SECRET_KEY:$options['secret_key'];

            $data = [
                'iat' => $time,
                'jti' => base64_encode(rand(1,1000).$time.rand(1,1000)),
                'iss' => $_SERVER['HTTP_HOST'],
                'nbf' => $time,
                'exp' => $expire ,
                'data' => $data_to_encrypt
            ];
            $token = JWT::encode(
                $data, //Data to be encoded in the JWT
                $secret_key, // The signing key
                $algorithm
            );

            if(!empty($token)){
                $response = [
                    'status' =>'success',
                    'token' => $token,
                    'expire' => $expire
                ];
            }
        }catch(\Exception $ex){
            $response['message'] ="Could not generate api key " . $ex->getMessage();
        }
        rtn:
        return $response;
    }
    /*
    * JWT token/session checking
    *
    * TOken can came in get request as api_key or from cookie session_id
    *
    * @param array $data_to_encrypt which will be encrypted in token
    * @param string $algorithm which algorithm to encrypt data
    *
    * @return array contain status of operation and token if success
    */
    public function verifyUserSession($token = '',$options = []){
        $response = [
            'status' =>'error',
            'message' => 'Something went wrong.Error code:1.'
        ];
        try{
            if(empty($token)){
                $api_key = isset($_GET['api_key'])?$_GET['api_key']:'';
                if(empty($api_key)){
                    $api_key = isset($_COOKIE['session_id'])?$_COOKIE['session_id']:'';
                }
                $token = $api_key;
            }
            if(empty($token)){
                $response['message'] = 'Required info not given.Code: 1';
                goto rtn;
            }
            return $this->decodeToken($token,$options);
        }catch(\Exception $ex){
            $response['message'] ="Could not verify api key " . $ex->getMessage();
        }
        rtn:
        return $response;
    }
    public function decodeToken($token,$options = []){
        $response = [
            'status' =>'error',
            'message' => 'Something went wrong.Error code:2.'
        ];
        try{
            if(empty($token)){
                $response['message'] = 'Required info not given.Code: 2';
                goto rtn;
            }
            $algorithm =empty($options['algorithm'])?Config::JWT_ALGORITHM:$options['algorithm'];
            $secret_key =empty($options['secret_key'])?Config::SECRET_KEY:$options['secret_key'];
            $decoded = JWT::decode($token, $secret_key, array($algorithm));
            if(!empty($decoded)){
                $response = [
                    'status' =>'success',
                    'data' => $decoded,
                ];
            }
        }catch(\Exception $ex){
            $response['message'] ="Could not verify api key " . $ex->getMessage();
        }
        rtn:
        return $response;
    }
    public function isPostMethod(){
        if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
            return true;
        }
        return false;
    }

}