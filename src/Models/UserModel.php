<?php
namespace App\Models;

class UserModel extends CoreModel{
    public function __construct()
    {
        parent::__construct();
        $this->setTableName('users');
    }
    /*
     * add the task
     *
     * @param array $data task related info
     *
     * @return array of last inserted row id with success or error
     */
    public function add($data = []){
        if(!empty($data)){
            return $this->executeORM(['insert' => true,'data' =>$data]);
        }else{
            return ['status' => 'error', 'message' => 'Data is not given to save. Code:UM-Add-1'];
        }
    }
    public function validateUser($email,$password){
        $response = [
            'status' =>'error',
            'message' => 'Something went wrong.Error code: UM-VU-1'
        ];
        if(empty($email)){
            $response['message'] = 'Email is not correct';
            goto rtn;
        }
        if(empty($password)){
            $response['message'] = 'Password is required';
            goto rtn;
        }
        $user_info = $this->executeORM([
            'get' => true,
            'columns' =>['email','password','name'],
            'where' => ['email' => $email]
        ]);
        if(!$this->isSuccessResponse($user_info)){
            return $user_info;
            goto rtn;
        }
        $user_info = $user_info['result'];
        if(empty($user_info)){
            $response['message'] = 'No record found';
            goto rtn;
        }
        //check password
        if(password_verify($password,$user_info['password'])){
            $response = [
                'status' =>'success',
                'data' => $user_info
            ];
        }else{
            $response['message'] = 'Wrong credential';
        }

        rtn:
        return $response;
    }
    public function getAllUsers($options = []){
        $page = !empty($options['page'])?$options['page']:1;
        $limit = !empty($options['limit'])?$options['limit']:20;
        $offset = (($page - 1) * $limit);
        return $this->executeORM([
            'select' => ['name','email','created'],
            'order' => ['id' => 'DESC'],
            'limit' => [$offset,$limit],
        ]);
    }
    public function countAllUser(){
        return $this->executeORM(['count' => true]);
    }
}