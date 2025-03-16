<?php


class PhotoSkeleton {
    protected $image_id;
    protected $image_url;
    protected $owner_id;
    protected $title;
    protected $date;
    protected $description;
    protected $tag_id;

    function PhotoSkeleton($image_id = null, $image_url = null, $owner_id = null, $title = null, $date = null, $description = null, $tag_id = null) {
        $this->image_id = $image_id;
        $this->image_url = $image_url;
        $this->owner_id = $owner_id;
        $this->title = $title;
        $this->date = $date;
        $this->description = $description;
        $this->tag_id = $tag_id;
    }

    // Getters and Setters
    public function getImageId() {
        return $this->image_id;
    }
    public function setImageId($image_id) {
        $this->image_id = $image_id;
    }

    public function getImageUrl() {
        return $this->image_url;
    }
    public function setImageUrl($image_url) {
        $this->image_url = $image_url;
    }

    public function getOwnerId() {
        return $this->owner_id;
    }
    public function setOwnerId($owner_id) {
        $this->owner_id = $owner_id;
    }

    public function getTitle() {
        return $this->title;
    }
    public function setTitle($title) {
        $this->title = $title;
    }

    public function getDate() {
        return $this->date;
    }
    public function setDate($date) {
        $this->date = $date;
    }

    public function getDescription() {
        return $this->description;
    }
    public function setDescription($description) {
        $this->description = $description;
    }

    public function getTagId() {
        return $this->tag_id;
    }
    public function setTagId($tag_id) {
        $this->tag_id = $tag_id;
    }
}
?>
