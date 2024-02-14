<?php
namespace Admin\Model;

class TourismFiles
{
    public $tourism_file_id;
    public $tourism_id;
    public $file_path;
    public $file_type;
    public $file_language_type;
    public $file_name;
    public $created_at;
    public $updated_at;

    const file_extension_type_image=1;
    const file_extension_type_video=2;
    const file_extension_type_audio=3;
    
    const file_data_type_country=1;
    const file_data_type_state=2;
    const file_data_type_city=3;
    const file_data_type_places=4;
    const file_data_type_seasonal_files=5;
    const file_data_type_sample_files=6;
    const file_data_type_itinerary_files=7;
    const file_data_type_ta_logo=8;

    public function exchangeArray(array $data)
    {
        $this->tourism_file_id = !empty($data['tourism_file_id']) ? $data['tourism_file_id'] : null;
        $this->tourism_id = !empty($data['tourism_id']) ? $data['tourism_id'] : null;
        $this->file_path = !empty($data['file_path']) ? $data['file_path'] : null;
        $this->file_language_type = !empty($data['file_language_type']) ? $data['file_language_type'] : null;
        $this->file_name = !empty($data['file_name']) ? $data['file_name'] : null;
        $this->created_at = !empty($data['created_at']) ? $data['created_at'] : null;
        $this->updated_at = !empty($data['updated_at']) ? $data['updated_at'] : null;
    }

}