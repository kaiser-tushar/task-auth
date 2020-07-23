<?php
namespace App\Controllers;

use App\Controllers\CoreController;
use App\Models\UserModel;

class HomeController extends CoreController {

    private $model = '';
    public function __construct()
    {
        parent::__construct();
        $this->model = new UserModel();
    }

    public function index(){
        if(!empty($this->auth_user)){
            $total = $this->model->countAllUser();
            $total = isset($total['result'])?$total['result']:0;
            $limit = isset($_GET["limit"])?$_GET["limit"]:5;
            $total_pages = ceil($total/$limit);
            $current_page = isset($_GET["page"])?$_GET["page"]:1;
            if($current_page > $total_pages){
                $current_page = 1;
            }
            $offset = (($current_page - 1) * $limit);
            //collect user name
            $all_users = $this->model->getAllUsers(['page'=> $current_page,'limit' => $limit]);
            if(!$this->isSuccessResponse($all_users)){
                $this->loadView('home',['auth_user' => $this->auth_user,'response' => $all_users]);
            }else{
                $all_users = $all_users['result'];
                $this->loadView('home',[
                    'auth_user' => $this->auth_user,
                    'users' => $all_users,
                    'total' => $total,
                    'current_page' => $current_page,
                    'total_pages' => $total_pages,
                    'offset' => $offset,
                ]);
            }
        }else{
            header("Location: login");
            die();
        }
    }
    public function register(){
        if($this->isPostMethod()){
            $response = [
                'status' =>'error',
                'message' => 'Something went wrong.Error code: Reg1'
            ];
            if(empty($_POST["name"])){
                $response['message'] = 'Name is mandatory';
                goto rtn;
            }else{
               $response['input']['name'] = trim(htmlspecialchars($_POST["name"]));
            }
            if(empty($_POST["email"])){
                $response['message'] = 'Email is mandatory';
                goto rtn;
            }else{
                $response['input']['email'] = filter_var(trim($_POST["email"]),FILTER_VALIDATE_EMAIL);
            }
            if(empty($_POST["password"])){
                $response['message'] = 'Password is mandatory';
                goto rtn;
            }else{
                $response['input']['password'] = password_hash($_POST["password"],PASSWORD_DEFAULT);
            }
            $time = date('Y-m-d h:i:s');
            $response = $this->model->add(array_merge($response['input'],[
                'created' => $time,
                'modified' => $time,
            ]));
            rtn:
            if($this->isSuccessResponse($response)){
                $response['message'] = 'Account created successfully.Please login.';
                $this->loadView('login',['response' => $response]);
            }else{
                $this->loadView('register',['response' => $response]);
            }
        }else{
            $this->loadView('register');
        }
    }
    public function login(){
        if($this->isPostMethod()){
            $response = [
                'status' =>'error',
                'message' => 'Something went wrong.Error code: log1'
            ];
            if(empty($_POST["email"])){
                $response['message'] = 'Email is mandatory';
                goto rtn;
            }else{
                $response['input']['email'] = filter_var(trim($_POST["email"]),FILTER_VALIDATE_EMAIL);
            }
            if(empty($_POST["password"])){
                $response['message'] = 'Password is mandatory';
                goto rtn;
            }else{
                $response['input']['password'] = $_POST["password"];
            }
            if(empty($response['input']['email'])){
                $response['message'] = 'Email is not correct';
                goto rtn;
            }
            $response_of_credential = $this->model->validateUser($response['input']['email'],$response['input']['password']);
            if(!$this->isSuccessResponse($response_of_credential)){
                $response['message'] = $response_of_credential['message'];
            }else{
                //create cookie to verify user on next request
                $user_info = $response_of_credential['data'];
                $token_response = $this->generateToken(['email' => $user_info['email'],'name' =>$user_info['name']]);
                if(!$this->isSuccessResponse($token_response)){
                    $response = $token_response;
                    goto rtn;
                }
                setcookie('session_id',$token_response['token'],$token_response['expire'],'/');
                $response = [
                    'status' =>'success',
                    'message' => 'Welcome '.$user_info['name'],
                    'token' => $token_response['token'],
                ];
            }
            rtn:
            if($this->isSuccessResponse($response)){
//                header("Location: home?api_key=".$response['token']);
                header("Location: home");
                die();
            }else{
                $this->loadView('login',['response' => $response]);
            }
            //check password hash
        }else{
            if(!empty($this->auth_user)){
                header("Location: home");
                die();
            }else{
                $this->loadView('login');
            }
        }
    }
    public function logout(){
        if(!empty($this->auth_user)){
            if(isset($_COOKIE))
            unset($_COOKIE['session_id']);
            setcookie('session_id', '', time() - 3600, '/');
        }
        header("Location: login");
        die();
    }

}