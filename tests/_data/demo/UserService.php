<?php
namespace demo;

class UserService {

    public function create($data = array())
    {
        $user = new UserModel($data);
        $user->save();
    }

    public function updateName(UserModel $user)
    {
        $user->setName("Current User");
        $user->save();
    }

    public function __call($name, $args)
    {
        if ($name == 'rename') {
            return 'David Blane';
        }
    }

}