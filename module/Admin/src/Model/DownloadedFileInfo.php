<?php
/**
 * Created by PhpStorm.
 * User: banu
 * Date: 3/10/19
 * Time: 4:53 PM
 */

namespace Admin\Model;


class DownloadedFileInfo
{
    public $download_id;
    public $user_id;
    public $place_ids;
    public $city_ids;
    public $state_ids;
    public $country_ids;
    public $package_ids;
    public $device_id;
    public $device_type;
    public $device_name;
    public $created_at;
    public $updated_at;


    const device_type_android=1;
    public function exchangeArray(array $data)
    {
        $this->download_id = !empty($data['download_id']) ? $data['download_id'] : null;
        $this->user_id = !empty($data['user_id']) ? $data['user_id'] : null;
        $this->place_ids = !empty($data['place_ids']) ? $data['place_ids'] : null;
        $this->city_ids = !empty($data['city_ids']) ? $data['city_ids'] : null;
        $this->state_ids = !empty($data['state_ids']) ? $data['state_ids'] : null;
        $this->country_ids = !empty($data['country_ids']) ? $data['country_ids'] : null;
        $this->package_ids = !empty($data['package_ids']) ? $data['package_ids'] : null;
        $this->device_id = !empty($data['device_id']) ? $data['device_id'] : null;
        $this->device_type = !empty($data['device_type']) ? $data['device_type'] : null;
        $this->device_name = !empty($data['device_name']) ? $data['device_name'] : null;
        $this->created_at = !empty($data['created_at']) ? $data['created_at'] : null;
        $this->updated_at = !empty($data['updated_at']) ? $data['updated_at'] : null;
    }
}