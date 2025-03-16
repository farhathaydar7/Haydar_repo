<?php

class TagSkeleton {
    protected $tag_id;
    protected $tag_name;
    protected $tag_owner;

    function TagSkeleton($tag_id = null, $tag_name = null, $tag_owner = null) {
        $this->tag_id = $tag_id;
        $this->tag_name = $tag_name;
        $this->tag_owner = $tag_owner;
    }

    // Getters and Setters
    public function getTagId() {
        return $this->tag_id;
    }
    public function setTagId($tag_id) {
        $this->tag_id = $tag_id;
    }

    public function getTagName() {
        return $this->tag_name;
    }
    public function setTagName($tag_name) {
        $this->tag_name = $tag_name;
    }

    public function getTagOwner() {
        return $this->tag_owner;
    }
    public function setTagOwner($tag_owner) {
        $this->tag_owner = $tag_owner;
    }
}
?>
