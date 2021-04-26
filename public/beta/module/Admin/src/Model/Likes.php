<?php


namespace Admin\Model;


class Likes
{
    public $like_id;
    public $tourism_file_id;
    public $file_data_id;
    public $file_data_type;
    public $user_id;
    public $status;
    public $created_at;
    public $updated_at;


    const Status_Like=1;
    const Status_DisLike=2;
    const Status_Deleted=0;


    const status=array(self::Status_Like=>'Like',self::Status_DisLike=>'dislike',self::Status_Deleted=> 'like/unlike deleted' );

    public function exchangeArray(array $data)
    {
        $this->like_id = !empty($data['like_id']) ? $data['like_id'] : null;
        $this->tourism_file_id = !empty($data['tourism_file_id']) ? $data['tourism_file_id'] : null;
        $this->file_data_id = !empty($data['file_data_id']) ? $data['file_data_id'] : null;
        $this->file_data_type = !empty($data['file_data_type']) ? $data['file_data_type'] : null;
        $this->user_id = !empty($data['user_id']) ? $data['user_id'] : null;
        $this->status = !empty($data['status']) ? $data['status'] : null;
        $this->created_at = !empty($data['created_at']) ? $data['created_at'] : null;
        $this->updated_at = !empty($data['updated_at']) ? $data['updated_at'] : null;
    }
}