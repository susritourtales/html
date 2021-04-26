<?php


namespace Admin\Model;


class TemporaryFiles
{
    public $temporary_files_id;
    public $file_name;
    public $country_id;
    public $created_at;
    public $updated_at;

      const status_file_not_copied=0;
      const status_file_copied=1;
    public function exchangeArray(array $data)
    {
        $this->temporary_files_id = !empty($data['temporary_files_id']) ? $data['temporary_files_id'] : null;
        $this->file_name = !empty($data['file_name']) ? $data['file_name'] : null;
        $this->file_type = !empty($data['file_type']) ? $data['file_type'] : null;
        $this->file_extension_type = !empty($data['file_extension_type']) ? $data['file_extension_type'] : null;
        $this->file_extension = !empty($data['file_extension']) ? $data['file_extension'] : null;
        $this->file_path = !empty($data['file_path']) ? $data['file_path'] : null;
        $this->created_at = !empty($data['created_at']) ? $data['created_at'] : null;
    }
}