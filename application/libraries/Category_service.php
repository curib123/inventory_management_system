<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Category_service {

    private $CI;

    // Setup ni sa Category_service; gi-load ni sa application/controllers/Categories.php para diri ma-centralize ang category business rules.
    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->model('Category_model');
    }

    // Business flow ni para save category; application/controllers/Categories.php ang caller, while Category_model query/persistence ra ang trabaho.
    public function save($id, $name, $status) {
        $id = $id === NULL ? NULL : (int) $id;
        $name = trim((string) $name);

        if ($name === '') {
            return array('success' => FALSE, 'message' => 'Category name is required.');
        }

        if ($this->CI->Category_model->name_exists($name, $id)) {
            return array('success' => FALSE, 'message' => 'That category name already exists.');
        }

        $saved = $this->CI->Category_model->save(array(
            'category_name' => $name,
            'status' => (int) $status === 0 ? 0 : 1
        ), $id);

        return $saved
            ? array('success' => TRUE)
            : array('success' => FALSE, 'message' => 'The category could not be saved.');
    }

    // Business flow ni para delete category; application/controllers/Categories.php ang caller, then dependency count gikan Category_model.
    public function delete($id, $execute = TRUE) {
        $id = (int) $id;
        $category = $this->CI->Category_model->get_by_id($id);

        if (!$category) {
            return array('success' => FALSE, 'not_found' => TRUE, 'message' => 'Category not found.');
        }

        if ($this->CI->Category_model->count_products($id) > 0) {
            return array(
                'success' => FALSE,
                'message' => 'This category is assigned to products. Reassign those products before deleting the category.'
            );
        }

        if (!$execute) {
            return array('success' => TRUE, 'category' => $category);
        }

        if (!$this->CI->Category_model->delete($id)) {
            return array('success' => FALSE, 'message' => 'The category could not be deleted.');
        }

        return array('success' => TRUE, 'category' => $category);
    }
}
