<?php


namespace demo;


class AdminUserModel extends UserModel {

    public function save()
    {
        $this->name = "Admin_111";
        parent::save();
    }



}