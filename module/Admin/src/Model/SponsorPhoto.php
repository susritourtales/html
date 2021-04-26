<?php


namespace Admin\Model;


class SponsorPhoto
{
    public $id;
    public $file_data_id;
    public $file_path;
    public $file_type;
    public $file_name;
    public $created_at;
    public $updated_at;

    public function exchangeArray(array $data)
    {
        $this->id = !empty($data['id']) ? $data['id'] : null;
        $this->file_data_id = !empty($data['file_data_id']) ? $data['file_data_id'] : null;
        $this->file_path = !empty($data['file_path']) ? $data['file_path'] : null;        
        $this->file_name = !empty($data['file_name']) ? $data['file_name'] : null;
        $this->created_at = !empty($data['created_at']) ? $data['created_at'] : null;
        $this->updated_at = !empty($data['updated_at']) ? $data['updated_at'] : null;
    }

}