<?php


namespace App\Repositories;


abstract class ResourcesRepository
{

    protected $model;

    public function store($inputs)
    {
        return $this->model->create($inputs);
    }


    public function getById($id)
    {
        return $this->model->where('id', $id)->first();
    }

    public function getByEmail($email)
    {
        $result = $this->model->where('email', $email)->first();
        return $result;
    }

    public function update($id, $inputs)
    {
        
        return $this->getById($id)->update($inputs);
    }

    public function destroy($id)
    {
        $this->getById($id)->delete();
    }

    public function getPaginate($n = null)
    {
        if ($n) {
            return $this->model->paginate($n);
        }
        return $this->model->get();
    }
}