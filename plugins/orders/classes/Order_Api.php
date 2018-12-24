<?php
namespace Cabinet\Plugin\Orders;
use Cabinet\Curl;
use Cabinet\Common;
use http\Exception\BadConversionException;

require_once DOC_ROOT . '/cabinet/classes/Curl.php';


/**
 * Class Order_Api
 * @package Cabinet\Plugin\Orders
 */
class Order_Api extends Common {


    /**
     * @return array
     */
    public function getOrders() {

        $orders = [];
        $result = Curl::get($this->config->system->apiserver . "/api/orders/list", [], [
            'Core2-apikey: ' . $this->config->system->apikey,
            'Core2-auth-token: ' . $this->auth->token,
        ]);


        if ($result->getHttpCode() == '403') {
            header("HTTP/1.1 403 Forbidden");
            $this->logout();

        } else {
            $result_decode = $result->toArray();
            $orders        = ! empty($result_decode['data']) && ! empty($result_decode['data']['orders'])
                ? $result_decode['data']['orders']
                : [];
        }


        return $orders;
    }


    /**
     * @param int $order_id
     * @return array
     */
    public function getOrder($order_id) {

        $order = [];
        $result  = Curl::get($this->config->system->apiserver . "/api/orders/order", [
            'order_id' => $order_id
        ], [
            'Core2-apikey: ' . $this->config->system->apikey,
            'Core2-auth-token: ' . $this->auth->token,
        ]);


        if ($result->getHttpCode() == '403') {
            header("HTTP/1.1 403 Forbidden");
            $this->logout();

        } else {
            $result_decode = $result->toArray();
            $order = ! empty($result_decode['data']) && ! empty($result_decode['data']['order'])
                ? $result_decode['data']['order']
                : [];
        }


        return $order;
    }


    /**
     * @param int $order_id
     * @return array
     */
    public function getChildrens($order_id) {

        $childrens = [];
        $result  = Curl::get($this->config->system->apiserver . "/api/orders/childrens", [
            'order_id' => $order_id
        ], [
            'Core2-apikey: ' . $this->config->system->apikey,
            'Core2-auth-token: ' . $this->auth->token,
        ]);


        if ($result->getHttpCode() == '403') {
            header("HTTP/1.1 403 Forbidden");
            $this->logout();

        } else {
            $result_decode = $result->toArray();
            $childrens = ! empty($result_decode['data']) && ! empty($result_decode['data']['childrens'])
                ? $result_decode['data']['childrens']
                : [];
        }


        return $childrens;
    }



    /**
     * @param int $order_id
     * @param int $children_id
     * @return array
     */
    public function getChildren($order_id, $children_id) {

        $childrens = [];
        $result  = Curl::get($this->config->system->apiserver . "/api/orders/children", [
            'order_id'    => $order_id,
            'children_id' => $children_id,
        ], [
            'Core2-apikey: ' . $this->config->system->apikey,
            'Core2-auth-token: ' . $this->auth->token,
        ]);


        if ($result->getHttpCode() == '403') {
            header("HTTP/1.1 403 Forbidden");
            $this->logout();

        } else {
            $result_decode = $result->toArray();
            $childrens = ! empty($result_decode['data']) && ! empty($result_decode['data']['children'])
                ? $result_decode['data']['children']
                : [];
        }


        return $childrens;
    }


    /**
     * @param int    $order_id
     * @param string $group
     * @param array  $childrens
     * @return array
     */
    public function addChildrens($order_id, $group, $childrens) {

        $response = Curl::post($this->config->system->apiserver . "/api/orders/childrens/add", [
            'order_id'  => $order_id,
            'group'     => $group,
            'childrens' => $childrens,
        ], [
            'Core2-apikey: ' . $this->config->system->apikey,
            'Core2-auth-token: ' . $this->auth->token,
        ]);

        $result = [];

        if ($response->getHttpCode() == '403') {
            header("HTTP/1.1 403 Forbidden");
            $this->logout();

        } else {
            $result_decode = $response->toArray();
            if ($result_decode !== null && ! empty($result_decode['data'])) {
                $result = $result_decode['data'];
            } else {
                $result = [
                    'status'        => 'error',
                    'error_message' => 'Ошибка. Попробуйте повторить попытку позже.'
                ];
            }
        }


        return $result;
    }


    /**
     * @param $order_id
     * @param $children_id
     * @param $children
     * @return array
     */
    public function editChildren($order_id, $children_id, $children) {

        $response = Curl::post($this->config->system->apiserver . "/api/orders/children/edit", [
            'order_id'    => $order_id,
            'children_id' => $children_id,
            'children'    => $children,
        ], [
            'Core2-apikey: ' . $this->config->system->apikey,
            'Core2-auth-token: ' . $this->auth->token,
        ]);

        $result = [];

        if ($response->getHttpCode() == '403') {
            header("HTTP/1.1 403 Forbidden");
            $this->logout();

        } else {
            $result_decode = $response->toArray();
            if ($result_decode !== null && ! empty($result_decode['data'])) {
                $result = $result_decode['data'];
            } else {
                $result = [
                    'status'        => 'error',
                    'error_message' => 'Ошибка. Попробуйте повторить попытку позже.'
                ];
            }
        }


        return $result;
    }


    /**
     * @param int    $order_id
     * @param array  $childrens
     * @return array
     */
    public function deleteChildrens($order_id, $childrens) {

        $response = Curl::post($this->config->system->apiserver . "/api/orders/childrens/delete", [
            'order_id'  => $order_id,
            'childrens' => $childrens,
        ], [
            'Core2-apikey: ' . $this->config->system->apikey,
            'Core2-auth-token: ' . $this->auth->token,
        ]);

        $result = [];

        if ($response->getHttpCode() == '403') {
            header("HTTP/1.1 403 Forbidden");
            $this->logout();

        } else {
            $result_decode = $response->toArray();
            if ($result_decode !== null && ! empty($result_decode['data'])) {
                $result = $result_decode['data'];
            } else {
                $result = [
                    'status'        => 'error',
                    'error_message' => 'Ошибка. Попробуйте повторить попытку позже.'
                ];
            }
        }


        return $result;
    }


    /**
     * @param int $order_id
     * @return array
     */
    public function getTeachers($order_id) {

        $teachers = [];
        $result  = Curl::get($this->config->system->apiserver . "/api/orders/teachers", [
            'order_id' => $order_id
        ], [
            'Core2-apikey: ' . $this->config->system->apikey,
            'Core2-auth-token: ' . $this->auth->token,
        ]);


        if ($result->getHttpCode() == '403') {
            header("HTTP/1.1 403 Forbidden");
            $this->logout();

        } else {
            $result_decode = $result->toArray();
            $teachers = ! empty($result_decode['data']) && ! empty($result_decode['data']['teachers'])
                ? $result_decode['data']['teachers']
                : [];
        }


        return $teachers;
    }



    /**
     * @param int $order_id
     * @param int $teacher_id
     * @return array
     */
    public function getTeacher($order_id, $teacher_id) {

        $teachers = [];
        $result  = Curl::get($this->config->system->apiserver . "/api/orders/teacher", [
            'order_id'   => $order_id,
            'teacher_id' => $teacher_id,
        ], [
            'Core2-apikey: ' . $this->config->system->apikey,
            'Core2-auth-token: ' . $this->auth->token,
        ]);


        if ($result->getHttpCode() == '403') {
            header("HTTP/1.1 403 Forbidden");
            $this->logout();

        } else {
            $result_decode = $result->toArray();
            $teachers = ! empty($result_decode['data']) && ! empty($result_decode['data']['teacher'])
                ? $result_decode['data']['teacher']
                : [];
        }


        return $teachers;
    }


    /**
     * @param int    $order_id
     * @param array  $teachers
     * @return array
     */
    public function addTeachers($order_id, $teachers) {

        $response = Curl::post($this->config->system->apiserver . "/api/orders/teachers/add", [
            'order_id'  => $order_id,
            'teachers' => $teachers,
        ], [
            'Core2-apikey: ' . $this->config->system->apikey,
            'Core2-auth-token: ' . $this->auth->token,
        ]);

        $result = [];

        if ($response->getHttpCode() == '403') {
            header("HTTP/1.1 403 Forbidden");
            $this->logout();

        } else {
            $result_decode = $response->toArray();
            if ($result_decode !== null && ! empty($result_decode['data'])) {
                $result = $result_decode['data'];
            } else {
                $result = [
                    'status'        => 'error',
                    'error_message' => 'Ошибка. Попробуйте повторить попытку позже.'
                ];
            }
        }


        return $result;
    }


    /**
     * @param $order_id
     * @param $teacher_id
     * @param $teacher
     * @return array
     */
    public function editTeacher($order_id, $teacher_id, $teacher) {

        $response = Curl::post($this->config->system->apiserver . "/api/orders/teacher/edit", [
            'order_id'   => $order_id,
            'teacher_id' => $teacher_id,
            'teacher'    => $teacher,
        ], [
            'Core2-apikey: ' . $this->config->system->apikey,
            'Core2-auth-token: ' . $this->auth->token,
        ]);

        $result = [];

        if ($response->getHttpCode() == '403') {
            header("HTTP/1.1 403 Forbidden");
            $this->logout();

        } else {
            $result_decode = $response->toArray();
            if ($result_decode !== null && ! empty($result_decode['data'])) {
                $result = $result_decode['data'];
            } else {
                $result = [
                    'status'        => 'error',
                    'error_message' => 'Ошибка. Попробуйте повторить попытку позже.'
                ];
            }
        }


        return $result;
    }


    /**
     * @param int   $order_id
     * @param array $teachers
     * @return array
     */
    public function deleteTeachers($order_id, $teachers) {

        $response = Curl::post($this->config->system->apiserver . "/api/orders/teachers/delete", [
            'order_id' => $order_id,
            'teachers' => $teachers,
        ], [
            'Core2-apikey: ' . $this->config->system->apikey,
            'Core2-auth-token: ' . $this->auth->token,
        ]);

        $result = [];

        if ($response->getHttpCode() == '403') {
            header("HTTP/1.1 403 Forbidden");
            $this->logout();

        } else {
            $result_decode = $response->toArray();
            if ($result_decode !== null && ! empty($result_decode['data'])) {
                $result = $result_decode['data'];
            } else {
                $result = [
                    'status'        => 'error',
                    'error_message' => 'Ошибка. Попробуйте повторить попытку позже.'
                ];
            }
        }


        return $result;
    }


    /**
     * @param int $order_id
     * @return array
     */
    public function getDocuments($order_id) {

        $teachers = [];
        $result  = Curl::get($this->config->system->apiserver . "/api/orders/documents/list", [
            'order_id' => $order_id
        ], [
            'Core2-apikey: ' . $this->config->system->apikey,
            'Core2-auth-token: ' . $this->auth->token,
        ]);


        if ($result->getHttpCode() == '403') {
            header("HTTP/1.1 403 Forbidden");
            $this->logout();

        } else {
            $result_decode = $result->toArray();
            $teachers = ! empty($result_decode['data']) && ! empty($result_decode['data']['documents'])
                ? $result_decode['data']['documents']
                : [];
        }


        return $teachers;
    }


    /**
     * @param $order_id
     * @param $document_id
     * @return array|null|string
     */
    public function getDocumentsPdf($order_id, $document_id) {

        $result = Curl::get($this->config->system->apiserver . "/api/orders/documents/pdf", [
            'order_id'    => $order_id,
            'document_id' => $document_id,
        ], [
            'Core2-apikey: ' . $this->config->system->apikey,
            'Core2-auth-token: ' . $this->auth->token,
        ]);


        if ($result->getHttpCode() == '403') {
            header("HTTP/1.1 403 Forbidden");
            $this->logout();

        } else {
            if ($result->getContentType() == 'application/pdf') {
                return $result->getContent();
            } else {
                return $result->toArray();
            }
        }


        return '';
    }


    /**
     * @param int    $order_id
     * @param int    $document_id
     * @param string $status
     * @return array
     */
    public function signDocuments($order_id, $document_id, $status) {

        $response = Curl::post($this->config->system->apiserver . "/api/orders/documents/sign", [
            'order_id'    => $order_id,
            'document_id' => $document_id,
            'status'      => $status,
        ], [
            'Core2-apikey: ' . $this->config->system->apikey,
            'Core2-auth-token: ' . $this->auth->token,
        ]);

        $result = [];

        if ($response->getHttpCode() == '403') {
            header("HTTP/1.1 403 Forbidden");
            $this->logout();

        } else {
            $result_decode = $response->toArray();
            if ($result_decode !== null && ! empty($result_decode['data'])) {
                $result = $result_decode['data'];
            } else {
                $result = [
                    'status'        => 'error',
                    'error_message' => 'Ошибка. Попробуйте повторить попытку позже.'
                ];
            }
        }


        return $result;
    }


    /**
     * @param int $order_id
     * @return array
     */
    public function getMaterials($order_id) {

        $materials = [];
        $result  = Curl::get($this->config->system->apiserver . "/api/orders/materials", [
            'order_id' => $order_id
        ], [
            'Core2-apikey: ' . $this->config->system->apikey,
            'Core2-auth-token: ' . $this->auth->token,
        ]);


        if ($result->getHttpCode() == '403') {
            header("HTTP/1.1 403 Forbidden");
            $this->logout();

        } else {
            $result_decode = $result->toArray();
            $materials = ! empty($result_decode['data']) && ! empty($result_decode['data']['materials'])
                ? $result_decode['data']['materials']
                : [];
        }


        return $materials;
    }


    /**
     * @param int $order_id
     * @return array
     */
    public function getSections($order_id) {

        $data = [];
        $result  = Curl::get($this->config->system->apiserver . "/api/orders/sections", [
            'order_id' => $order_id
        ], [
            'Core2-apikey: ' . $this->config->system->apikey,
            'Core2-auth-token: ' . $this->auth->token,
        ]);


        if ($result->getHttpCode() == '403') {
            header("HTTP/1.1 403 Forbidden");
            $this->logout();

        } else {
            $result_decode = $result->toArray();
            $data = ! empty($result_decode['data']) && ! empty($result_decode['data'])
                ? $result_decode['data']
                : [];
        }


        return $data;
    }


    /**
     * @param int $image_id
     * @return array
     */
    public function getSectionsImage($image_id) {

        $image = [];
        $result  = Curl::get($this->config->system->apiserver . "/api/orders/client/image", [
            'image_id' => $image_id
        ], [
            'Core2-apikey: ' . $this->config->system->apikey,
            'Core2-auth-token: ' . $this->auth->token,
        ]);


        if ($result->getHttpCode() == '403') {
            header("HTTP/1.1 403 Forbidden");
            $this->logout();

        } else {
             $image['type']    = $result->getContentType();
             $image['content'] = $result->getContent();
        }


        return $image;
    }


    /**
     * @param $order_id
     * @param $section_name
     * @param $page_number
     * @param $file
     * @return array|string
     */
    public function uploadClientImage($order_id, $section_name, $page_number, $file) {

        $status_upload = 'error';

        $result = Curl::post($this->config->system->apiserver . "/api/orders/client/image/upload", [
            'order_id'     => $order_id,
            'section_name' => $section_name,
            'id'           => $page_number,
            'file'         => $file
        ], [
            'Core2-apikey: ' . $this->config->system->apikey,
            'Core2-auth-token: ' . $this->auth->token,
        ]);


        if ($result->getHttpCode() == '403') {
            header("HTTP/1.1 403 Forbidden");
            $this->logout();

        } else {
            $result_decode = $result->toArray();
            $status_upload = ! empty($result_decode['data']) && ! empty($result_decode['data']['status'])
                ? $result_decode['data']['status']
                : [];
        }


        return $status_upload;
    }


    /**
     * @param $images_id
     * @return bool
     */
    public function removeClientImages($images_id) {

        $is_remove = false;
        $result    = Curl::post($this->config->system->apiserver . "/api/orders/client/image/remove", [
            'images_id' => $images_id
        ], [
            'Core2-apikey: ' . $this->config->system->apikey,
            'Core2-auth-token: ' . $this->auth->token,
        ]);


        if ($result->getHttpCode() == '403') {
            header("HTTP/1.1 403 Forbidden");
            $this->logout();

        } else {
            $result_decode = $result->toArray();
            $is_remove = ! empty($result_decode['data']) && ! empty($result_decode['data']['status'])
                ? $result_decode['data']['status'] == 'success'
                : false;
        }


        return $is_remove;
    }


    /**
     * @param      $order_id
     * @param      $sections
     * @param bool $send_message
     * @return bool
     */
    public function saveClientMaterials($order_id, $sections, $send_message = false) {

        $is_save = false;

        $result = Curl::post($this->config->system->apiserver . "/api/orders/client/materials/save", [
            'order_id'     => $order_id,
            'sections'     => $sections,
            'send_message' => $send_message,
        ], [
            'Core2-apikey: ' . $this->config->system->apikey,
            'Core2-auth-token: ' . $this->auth->token,
        ]);


        if ($result->getHttpCode() == '403') {
            header("HTTP/1.1 403 Forbidden");
            $this->logout();

        } else {
            $result_decode = $result->toArray();
            $is_save = ! empty($result_decode['data']) && ! empty($result_decode['data']['status'])
                ? $result_decode['data']['status'] == 'success'
                : false;
        }


        return $is_save;
    }


    /**
     * @param $order_id
     * @return bool
     */
    public function verificationClientMaterials($order_id) {

        $is_save = false;

        $result = Curl::post($this->config->system->apiserver . "/api/orders/client/materials/verification", [
            'order_id' => $order_id
        ], [
            'Core2-apikey: ' . $this->config->system->apikey,
            'Core2-auth-token: ' . $this->auth->token,
        ]);


        if ($result->getHttpCode() == '403') {
            header("HTTP/1.1 403 Forbidden");
            $this->logout();

        } else {
            $result_decode = $result->toArray();
            $is_save = ! empty($result_decode['data']) && ! empty($result_decode['data']['status'])
                ? $result_decode['data']['status'] == 'success'
                : false;
        }


        return $is_save;
    }


    /**
     * @param      $page_id
     * @param      $comment
     * @return bool
     */
    public function savePageComment($page_id, $comment) {

        $is_save = false;

        $result = Curl::post($this->config->system->apiserver . "/api/orders/makets/comment/save", [
            'page_id' => $page_id,
            'comment' => $comment
        ], [
            'Core2-apikey: ' . $this->config->system->apikey,
            'Core2-auth-token: ' . $this->auth->token,
        ]);


        if ($result->getHttpCode() == '403') {
            header("HTTP/1.1 403 Forbidden");
            $this->logout();

        } else {
            $result_decode = $result->toArray();
            $is_save = ! empty($result_decode['data']) && ! empty($result_decode['data']['status'])
                ? $result_decode['data']['status'] == 'success'
                : false;
        }


        return $is_save;
    }


    /**
     * @param int $order_id
     * @return array
     */
    public function getMakets($order_id) {

        $makets = [];
        $result  = Curl::get($this->config->system->apiserver . "/api/orders/makets", [
            'order_id' => $order_id
        ], [
            'Core2-apikey: ' . $this->config->system->apikey,
            'Core2-auth-token: ' . $this->auth->token,
        ]);


        if ($result->getHttpCode() == '403') {
            header("HTTP/1.1 403 Forbidden");
            $this->logout();

        } else {
            $result_decode = $result->toArray();
            $makets = ! empty($result_decode['data']) && ! empty($result_decode['data']['makets'])
                ? $result_decode['data']['makets']
                : [];
        }


        return $makets;
    }


    /**
     * @param $section_id
     * @return array
     */
    public function getMaketsSection($section_id) {

        $makets = [];
        $result  = Curl::get($this->config->system->apiserver . "/api/orders/makets/section", [
            'section_id' => $section_id
        ], [
            'Core2-apikey: ' . $this->config->system->apikey,
            'Core2-auth-token: ' . $this->auth->token,
        ]);


        if ($result->getHttpCode() == '403') {
            header("HTTP/1.1 403 Forbidden");
            $this->logout();

        } else {
            $result_decode = $result->toArray();
            $makets = ! empty($result_decode['data']) && ! empty($result_decode['data']['section'])
                ? $result_decode['data']['section']
                : [];
        }


        return $makets;
    }


    /**
     * @param $section_id
     * @return array
     */
    public function getMaketsPdf($section_id) {

        $section = [];
        $result  = Curl::get($this->config->system->apiserver . "/api/orders/makets/pdf", [
            'section_id' => $section_id
        ], [
            'Core2-apikey: ' . $this->config->system->apikey,
            'Core2-auth-token: ' . $this->auth->token,
        ]);


        if ($result->getHttpCode() == '403') {
            header("HTTP/1.1 403 Forbidden");
            $this->logout();

        } else {
            $result_decode = $result->toArray();
            $section       = ! empty($result_decode['data']) && ! empty($result_decode['data']['section'])
                ? $result_decode['data']['section']
                : [];
        }


        return $section;
    }


    /**
     * @param $image_id
     * @return array
     */
    public function getMaketsImage($image_id) {

        $image  = [];
        $result = Curl::get($this->config->system->apiserver . "/api/orders/makets/image", [
            'image_id' => $image_id
        ], [
            'Core2-apikey: ' . $this->config->system->apikey,
            'Core2-auth-token: ' . $this->auth->token,
        ]);


        if ($result->getHttpCode() == '403') {
            header("HTTP/1.1 403 Forbidden");
            $this->logout();

        } else {
            $result_decode = $result->toArray();
            $image = ! empty($result_decode['data']) && ! empty($result_decode['data']['image'])
                ? $result_decode['data']['image']
                : [];
        }


        return $image;
    }


    /**
     * @param $image_id
     * @return array
     */
    public function getMaketsImageFull($image_id) {

        $image  = [];
        $result = Curl::get($this->config->system->apiserver . "/api/orders/makets/image/full", [
            'image_id' => $image_id
        ], [
            'Core2-apikey: ' . $this->config->system->apikey,
            'Core2-auth-token: ' . $this->auth->token,
        ]);


        if ($result->getHttpCode() == '403') {
            header("HTTP/1.1 403 Forbidden");
            $this->logout();

        } else {
            $result_decode = $result->toArray();
            $image = ! empty($result_decode['data']) && ! empty($result_decode['data']['image'])
                ? $result_decode['data']['image']
                : [];
        }


        return $image;
    }


    /**
     * @param $page_id
     * @param $file
     * @return array|string
     */
    public function uploadMaketsPageImage($page_id, $file) {

        $status_upload = 'error';


        $result = Curl::post($this->config->system->apiserver . "/api/orders/makets/page/image/upload", [
            'page_id' => $page_id,
            'file'    => $file
        ], [
            'Core2-apikey: ' . $this->config->system->apikey,
            'Core2-auth-token: ' . $this->auth->token,
        ], [
            CURLOPT_RETURNTRANSFER => true
        ]);


        if ($result->getHttpCode() == '403') {
            header("HTTP/1.1 403 Forbidden");
            $this->logout();

        } else {
            $result_decode = $result->toArray();
            $status_upload = ! empty($result_decode['data']) && ! empty($result_decode['data']['status'])
                ? $result_decode['data']['status']
                : [];
        }


        return $status_upload;
    }


    /**
     * @param $images_id
     * @return bool
     */
    public function removeMaketsPageImages($images_id) {

        $is_remove = false;
        $result    = Curl::post($this->config->system->apiserver . "/api/orders/makets/page/image/remove", [
            'images_id' => $images_id
        ], [
            'Core2-apikey: ' . $this->config->system->apikey,
            'Core2-auth-token: ' . $this->auth->token,
        ]);


        if ($result->getHttpCode() == '403') {
            header("HTTP/1.1 403 Forbidden");
            $this->logout();

        } else {
            $result_decode = $result->toArray();
            $is_remove = ! empty($result_decode['data']) && ! empty($result_decode['data']['status'])
                ? $result_decode['data']['status'] == 'success'
                : false;
        }


        return $is_remove;
    }


    /**
     * @param $section_id
     * @param $status
     * @return bool
     */
    public function setSectionStatus($section_id, $status) {

        $is_remove = false;
        $result    = Curl::post($this->config->system->apiserver . "/api/orders/makets/section/status", [
            'section_id' => $section_id,
            'status'     => $status,
        ], [
            'Core2-apikey: ' . $this->config->system->apikey,
            'Core2-auth-token: ' . $this->auth->token,
        ]);


        if ($result->getHttpCode() == '403') {
            header("HTTP/1.1 403 Forbidden");
            $this->logout();

        } else {
            $result_decode = $result->toArray();
            $is_remove = ! empty($result_decode['data']) && ! empty($result_decode['data']['status'])
                ? $result_decode['data']['status'] == 'success'
                : false;
        }


        return $is_remove;
    }


    /**
     * @param int $order_id
     * @return array
     */
    public function getPayments($order_id) {

        $payments = [];
        $result  = Curl::get($this->config->system->apiserver . "/api/orders/payments/list", [
            'order_id' => $order_id
        ], [
            'Core2-apikey: ' . $this->config->system->apikey,
            'Core2-auth-token: ' . $this->auth->token,
        ]);


        if ($result->getHttpCode() == '403') {
            header("HTTP/1.1 403 Forbidden");
            $this->logout();

        } else {
            $result_decode = $result->toArray();
            $payments = ! empty($result_decode['data']) && ! empty($result_decode['data']['payments'])
                ? $result_decode['data']['payments']
                : [];
        }


        return $payments;
    }


    /**
     * @param int $payment_id
     * @return array
     */
    public function getPayment($payment_id) {

        $payment = [];
        $result  = Curl::get($this->config->system->apiserver . "/api/orders/payment", [
            'payment_id' => $payment_id
        ], [
            'Core2-apikey: ' . $this->config->system->apikey,
            'Core2-Private-Key: ' . $this->config->system->private_key,
        ]);

        if ($result->getHttpCode() == '200') {
            $result_decode = $result->toArray();

            $payment = ! empty($result_decode['data']) && ! empty($result_decode['data']['payment'])
                ? $result_decode['data']['payment']
                : [];
        }


        return $payment;
    }


    /**
     * @param int   $payment_id
     * @param int   $client_id
     * @param int   $transaction_id
     * @param array $transaction_data
     * @return array
     */
    public function setPaymentSuccess($payment_id, $client_id, $transaction_id, $transaction_data) {

        $response = Curl::post($this->config->system->apiserver . "/api/orders/payment/success", [
            'id'               => $payment_id,
            'client_id'        => $client_id,
            'transaction_id'   => $transaction_id,
            'transaction_data' => $transaction_data,
        ], [
            'Core2-Apikey: ' . $this->config->system->apikey,
            'Core2-Private-Key: ' . $this->config->system->private_key,
        ]);


        $result_decode = $response->toArray();

        if ($result_decode !== null && ! empty($result_decode['data'])) {
            $result = $result_decode['data'];
        } else {
            $result = [
                'status'        => 'error',
                'error_message' => 'Ошибка. Попробуйте повторить попытку позже.'
            ];
        }


        return $result;
    }
}