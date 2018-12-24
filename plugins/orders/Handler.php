<?php
namespace Cabinet\Plugin\Orders;
use Cabinet\Common;

require_once DOC_ROOT . '/cabinet/classes/Common.php';
require_once DOC_ROOT . '/cabinet/classes/Curl.php';

require_once 'classes/Order_Api.php';


/**
 * Class Handler
 * @package Cabinet\Plugin\Orders
 */
class Handler extends Common {


    /**
     * Добавление учеников
     * @return string
     */
    public function addChildrens() {

        try {
            if (empty($_POST['order_id'])) {
                throw new \Exception('Не указан идентификатор заказа');
            }
            if (empty($_POST['group'])) {
                throw new \Exception('Вы выбрана группа для учеников');
            }
            if (empty($_POST['childrens'])) {
                throw new \Exception('Вы не добавили учеников');
            }

        } catch (\Exception $e) {
            return json_encode([
                'status'        => 'error',
                'error_message' => $e->getMessage()
            ]);
        }

        $childrens = [];
        $childrens_raw = explode("\n", $_POST['childrens']);
        foreach ($childrens_raw as $children) {
            $children = preg_replace('~[ ]{2,}~', '', $children);
            $children = str_replace(["\r", "\t"], '', $children);
            $children = $children != ' ' ? $children: false;

            if ($children) {
                $childrens[] = $children;
            }
        }


        $orders_api = new Order_Api();
        $result     =  $orders_api->addChildrens($_POST['order_id'], $_POST['group'], $childrens);

        if (empty($result)) {
            $result = [
                'status'        => 'error',
                'error_message' => 'Попробуйте повторить попытку позже.'
            ];
        }


        return json_encode($result);
    }


    /**
     * Редактирование учеников
     * @return string
     */
    public function editChildren() {

        try {
            if (empty($_POST['order_id'])) {
                throw new \Exception('Не указан идентификатор заказа');
            }
            if (empty($_POST['group'])) {
                throw new \Exception('Вы выбрана группа для учеников');
            }
            if (empty($_POST['children_id'])) {
                throw new \Exception('Не указан идентификатор ученика');
            }
            if (empty($_POST['firstname'])) {
                throw new \Exception('Не указано имя ученика');
            }
            if (empty($_POST['lastname'])) {
                throw new \Exception('Не указана фамилия ученика');
            }

        } catch (\Exception $e) {
            return json_encode([
                'status'        => 'error',
                'error_message' => $e->getMessage()
            ]);
        }

        $orders_api = new Order_Api();
        $result     =  $orders_api->editChildren($_POST['order_id'], $_POST['children_id'], [
            'group'        => $_POST['group'],
            'firstname'    => $_POST['firstname'],
            'lastname'     => $_POST['lastname'],
            'albums_count' => ! empty($_POST['albums_count']) ? $_POST['albums_count'] : '',
            'additional'   => ! empty($_POST['additional']) ? $_POST['additional'] : '',
            'comment'      => ! empty($_POST['comment']) ? $_POST['comment'] : '',
        ]);

        if (empty($result)) {
            $result = [
                'status'        => 'error',
                'error_message' => 'Попробуйте повторить попытку позже.'
            ];
        }


        return json_encode($result);
    }


    /**
     * Добавление учеников
     * @return string
     */
    public function deleteChildrens() {

        try {
            if (empty($_POST['order_id'])) {
                throw new \Exception('Не указан идентификатор заказа');
            }
            if (empty($_POST['children_id'])) {
                throw new \Exception('Не указан идентификатор ученика');
            }

        } catch (\Exception $e) {
            return json_encode([
                'status'        => 'error',
                'error_message' => $e->getMessage()
            ]);
        }

        $orders_api = new Order_Api();
        $result     =  $orders_api->deleteChildrens($_POST['order_id'], [$_POST['children_id']]);

        if (empty($result)) {
            $result = [
                'status'        => 'error',
                'error_message' => 'Попробуйте повторить попытку позже.'
            ];
        }


        return json_encode($result);
    }


    /**
     * Добавление учителей
     * @return string
     */
    public function addTeachers() {

        try {
            if (empty($_POST['order_id'])) {
                throw new \Exception('Не указан идентификатор заказа');
            }
            if (empty($_POST['teachers'])) {
                throw new \Exception('Empty parameter teachers');
            }

        } catch (\Exception $e) {
            return json_encode([
                'status'        => 'error',
                'error_message' => $e->getMessage()
            ]);
        }

        $teachers     = [];
        $teachers_raw = explode("\n", $_POST['teachers']);
        foreach ($teachers_raw as $teacher) {
            $teacher = preg_replace('~[ ]{2,}~', '', $teacher);
            $teacher = str_replace(["\r", "\t"], '', $teacher);
            $teacher = $teacher != ' ' ? $teacher: false;

            if ($teacher) {
                $teachers[] = $teacher;
            }
        }


        $orders_api = new Order_Api();
        $result     =  $orders_api->addTeachers($_POST['order_id'], $teachers);

        if (empty($result)) {
            $result = [
                'status'        => 'error',
                'error_message' => 'Ошибка. Попробуйте повторить попытку позже.'
            ];
        }


        return json_encode($result);
    }


    /**
     * Редактирование учителя
     * @return string
     */
    public function editTeacher() {

        try {
            if (empty($_POST['order_id'])) {
                throw new \Exception('Не указан идентификатор заказа');
            }
            if (empty($_POST['teacher_id'])) {
                throw new \Exception('Не указан идентификатор учителя');
            }
            if (empty($_POST['name'])) {
                throw new \Exception('Не указано ФИО учителя');
            }
            if (empty($_POST['position'])) {
                throw new \Exception('Не указана должность учителя');
            }

        } catch (\Exception $e) {
            return json_encode([
                'status'        => 'error',
                'error_message' => $e->getMessage()
            ]);
        }

        $orders_api = new Order_Api();
        $result     =  $orders_api->editTeacher($_POST['order_id'], $_POST['teacher_id'], [
            'name'         => $_POST['name'],
            'position'     => $_POST['position'],
            'albums_count' => ! empty($_POST['albums_count']) ? $_POST['albums_count'] : '',
        ]);

        if (empty($result)) {
            $result = [
                'status'        => 'error',
                'error_message' => 'Попробуйте повторить попытку позже.'
            ];
        }


        return json_encode($result);
    }


    /**
     * Получение изображения
     * @return false|string
     * @throws \Exception
     */
    public function getSectionImage() {

        try {
            if (empty($_GET['image_id'])) {
                throw new \Exception('Не указан идентификатор картинки');
            }

        } catch (\Exception $e) {
            return json_encode([
                'status'        => 'error',
                'error_message' => $e->getMessage()
            ]);
        }

        $orders_api = new Order_Api();
        $image       =  $orders_api->getSectionsImage($_GET['image_id']);


        if (empty($image)) {
            throw new \Exception('Ошибка получения картинки');
        }

        header("Content-Type: " . $image['type']);
        echo $image['content'];
    }


    /**
     * Получение изображения
     * @return false|string
     * @throws \Exception
     */
    public function uploadClientMaterial() {

        try {
            if (empty($_FILES['client_image'])) {
                throw new \Exception('Файл не загружен');
            }
            if ($_FILES['client_image']['error'] !== UPLOAD_ERR_OK) {
                throw new \Exception('Ошибка загрузки файла');
            }
            if ( ! file_exists($_FILES['client_image']['tmp_name'])) {
                throw new \Exception('Ошибка загрузки файла. Файл не найден');
            }

            if ( ! is_dir($this->config->system->tmp . '/vypusk-cabinet')) {
                mkdir($this->config->system->tmp . '/vypusk-cabinet');
            }

            $ext      = substr($_FILES['client_image']['name'], strrpos($_FILES['client_image']['name'], '.') + 1);
            $tmp_name = 'client_image_' . md5(time() . $_FILES['client_image']['tmp_name']) . '.' . $ext;
            move_uploaded_file($_FILES['client_image']['tmp_name'], $this->config->system->tmp . '/vypusk-cabinet/' . $tmp_name);

            return json_encode([
                'tmp_name' => $tmp_name,
                'name'     => $_FILES['client_image']['name'],
                'type'     => $_FILES['client_image']['type'],
                'size'     => $_FILES['client_image']['size'],
            ]);

        } catch (\Exception $e) {
            header("HTTP/1.1 500 Internal Server Error");
            return json_encode([
                'status'        => 'error',
                'error_message' => $e->getMessage()
            ]);
        };
    }


    /**
     * Получение изображения
     * @return false|string
     * @throws \Exception
     */
    public function uploadSectionComment() {

        try {
            if (empty($_FILES['comment_image'])) {
                throw new \Exception('Файл не загружен');
            }
            if ($_FILES['comment_image']['error'] !== UPLOAD_ERR_OK) {
                throw new \Exception('Ошибка загрузки файла');
            }
            if ( ! file_exists($_FILES['comment_image']['tmp_name'])) {
                throw new \Exception('Ошибка загрузки файла. Файл не найден');
            }

            if ( ! is_dir($this->config->system->tmp . '/vypusk-cabinet')) {
                mkdir($this->config->system->tmp . '/vypusk-cabinet');
            }

            $tmp_name = 'comment_image_' . md5(time() . $_FILES['comment_image']['tmp_name']);
            move_uploaded_file($_FILES['comment_image']['tmp_name'], $this->config->system->tmp . '/vypusk-cabinet/' . $tmp_name);

            return json_encode([
                'tmp_name' => $tmp_name,
                'name'     => $_FILES['comment_image']['name'],
                'type'     => $_FILES['comment_image']['type'],
                'size'     => $_FILES['comment_image']['size'],
            ]);

        } catch (\Exception $e) {
            header("HTTP/1.1 500 Internal Server Error");
            return json_encode([
                'status'        => 'error',
                'error_message' => $e->getMessage()
            ]);
        }
    }


    /**
     * Сохранение клиентских материалов
     * @return false|string
     * @throws \Exception
     */
    public function saveClientMaterials() {

        try {
            if (empty($_POST['order_id'])) {
                throw new \Exception('Не указан идентификатор заказа');
            }
            if (empty($_POST['section'])) {
                throw new \Exception('Нет раздела для сохранения');
            }
            if (empty($_POST['id'])) {
                throw new \Exception('Нет идентификатора страницы');
            }

            $sections     = [];
            $files        = [];
            $files_remove = [];


            $files_remove_raw = ! empty($_POST['files_remove']) ? json_decode($_POST['files_remove'], true) : [];
            if ( ! empty($files_remove_raw)) {
                foreach ($files_remove_raw as $file_remove_id) {
                    $files_remove[] = $file_remove_id;
                }
            }

            $files_raw = ! empty($_POST['files']) ? json_decode($_POST['files'], true) : [];
            if ( ! empty($files_raw)) {
                foreach ($files_raw as $file) {
                    if ( ! empty($file['tmp_name']) &&
                        file_exists($this->config->system->tmp . '/vypusk-cabinet/' . $file['tmp_name'])
                    ) {
                        $tmp_name = $this->config->system->tmp . '/vypusk-cabinet/' . $file['tmp_name'];

                        $files[$_POST['section']][$_POST['id']][] = [
                            'content'  => file_get_contents($tmp_name),
                            'name'     => $file['name'],
                            'type'     => $file['type'],
                            'size'     => $file['size'],
                            'tmp_name' => $tmp_name,
                        ];
                    }
                }
            }

            $sections[$_POST['section']][$_POST['id']] = [
                'text'    => $_POST['text'],
                'comment' => $_POST['comment']
            ];

            $orders_api = new Order_Api();

            $is_save = $orders_api->saveClientMaterials($_POST['order_id'], $sections, ! empty($_POST['send_message']));
            if ( ! $is_save) {
                throw new \Exception('Ошибка сохранения. Попробуйте еще раз');
            }

            if ( ! empty($files)) {
                foreach ($files as $section_name => $files_section) {
                    foreach ($files_section as $page_number => $page_files) {
                        foreach ($page_files as $file) {
                            if ($orders_api->uploadClientImage($_POST['order_id'], $section_name, $page_number, $file)) {
                                unlink($file['tmp_name']);
                            }
                        }
                    }
                }
            }

            if ( ! empty($files_remove)) {
                $orders_api->removeClientImages($files_remove);
            }

        } catch (\Exception $e) {
            return json_encode([
                'status'        => 'error',
                'error_message' => $e->getMessage()
            ]);
        }


        return json_encode([
            'status' => 'success'
        ]);
    }


    /**
     * Проверка клиентских материалов
     * @return false|string
     * @throws \Exception
     */
    public function verificationClientMaterials() {

        try {
            if (empty($_POST['order_id'])) {
                throw new \Exception('Не указан идентификатор заказа');
            }

            $orders_api = new Order_Api();
            $orders_api->verificationClientMaterials($_POST['order_id']);

        } catch (\Exception $e) {
            return json_encode([
                'status'        => 'error',
                'error_message' => $e->getMessage()
            ]);
        }


        return json_encode([
            'status' => 'success'
        ]);
    }


    /**
     * Получение изображения
     * @return false|string
     * @throws \Exception
     */
    public function getPageCommentImage() {

        try {
            if (empty($_GET['image_id'])) {
                throw new \Exception('Не указан идентификатор картинки');
            }

        } catch (\Exception $e) {
            return json_encode([
                'status'        => 'error',
                'error_message' => $e->getMessage()
            ]);
        }

        $orders_api = new Order_Api();
        $image       =  $orders_api->getSectionsImage($_GET['image_id']);


        if (empty($image)) {
            throw new \Exception('Ошибка получения картинки');
        }

        header("Content-Type: " . $image['type']);
        echo $image['content'];
    }


    /**
     * Сохранение клиентских материалов
     * @return false|string
     * @throws \Exception
     */
    public function savePageComment() {

        try {
            if (empty($_POST['page_id'])) {
                throw new \Exception('Не указан идентификатор страницы');
            }

            $images       = ! empty($_POST['images']) ? $_POST['images'] : [];
            $files        = [];
            $files_remove = [];

            if ( ! empty($images)) {
                $images['files_remove'] = ! empty($images['files_remove']) ? json_decode($images['files_remove'], true) : [];
                if ( ! empty($images['files_remove'])) {
                    foreach ($images['files_remove'] as $file_remove_id) {
                        $files_remove[] = $file_remove_id;
                    }
                }

                $images['files'] = ! empty($images['files']) ? json_decode($images['files'], true) : [];
                if ( ! empty($images['files'])) {
                    foreach ($images['files'] as $file) {
                        if ( ! empty($file['id'])) {
                            $files[] = [
                                'id' => $file['id']
                            ];

                        } elseif ( ! empty($file['tmp_name']) &&
                                  file_exists($this->config->system->tmp . '/vypusk-cabinet/' . $file['tmp_name'])
                        ) {
                            $tmp_name = $this->config->system->tmp . '/vypusk-cabinet/' . $file['tmp_name'];

                            $files[] = [
                                'content'  => file_get_contents($tmp_name),
                                'name'     => $file['name'],
                                'type'     => $file['type'],
                                'size'     => $file['size'],
                                'tmp_name' => $tmp_name,
                            ];
                        }
                    }
                }
            }

            $comment = ! empty($_POST['comment']) ? $_POST['comment'] : '';


            $orders_api = new Order_Api();

            $is_save = $orders_api->savePageComment($_POST['page_id'], $comment);
            if ( ! $is_save) {
                throw new \Exception('Ошибка сохранения. Попробуйте еще раз');
            }

            if ( ! empty($files)) {
                foreach ($files as $file) {
                    if (isset($file['content']) && $orders_api->uploadMaketsPageImage($_POST['page_id'], $file)) {
                        unset($file['tmp_name']);
                    }
                }
            }

            if ( ! empty($files_remove)) {
                $orders_api->removeMaketsPageImages($files_remove);
            }

        } catch (\Exception $e) {
            return json_encode([
                'status'        => 'error',
                'error_message' => $e->getMessage()
            ]);
        }


        return json_encode([
            'status' => 'success'
        ]);
    }


    /**
     * Сохранение статуса раздела
     * @return false|string
     * @throws \Exception
     */
    public function setSectionStatus() {

        try {
            if (empty($_POST['section_id'])) {
                throw new \Exception('Не указан идентификатор раздела');
            }
            if (empty($_POST['status'])) {
                throw new \Exception('Не указан статус раздела');
            }

            $orders_api = new Order_Api();
            $is_save = $orders_api->setSectionStatus($_POST['section_id'], $_POST['status']);
            if ( ! $is_save) {
                throw new \Exception('Ошибка сохранения. Попробуйте еще раз');
            }

        } catch (\Exception $e) {
            return json_encode([
                'status'        => 'error',
                'error_message' => $e->getMessage()
            ]);
        }


        return json_encode([
            'status' => 'success'
        ]);
    }


    /**
     * Удаление учителей
     * @return string
     */
    public function deleteTeachers() {

        try {
            if (empty($_POST['order_id'])) {
                throw new \Exception('Не указан идентификатор заказа');
            }
            if (empty($_POST['teacher_id'])) {
                throw new \Exception('Не указан идентификатор учителя');
            }

        } catch (\Exception $e) {
            return json_encode([
                'status'        => 'error',
                'error_message' => $e->getMessage()
            ]);
        }

        $orders_api = new Order_Api();
        $result     =  $orders_api->deleteTeachers($_POST['order_id'], [$_POST['teacher_id']]);

        if (empty($result)) {
            $result = [
                'status'        => 'error',
                'error_message' => 'Попробуйте повторить попытку позже.'
            ];
        }


        return json_encode($result);
    }


    /**
     * Подпись документа
     * @return string
     */
    public function signDocuments() {

        try {
            if (empty($_POST['order_id'])) {
                throw new \Exception('Не указан идентификатор заказа');
            }
            if (empty($_POST['document_id'])) {
                throw new \Exception('Не указан идентификатор документа');
            }
            if (empty($_POST['status'])) {
                throw new \Exception('Не указан статус документа');
            }
            if ( ! in_array($_POST['status'], ['accepted', 'rejected'])) {
                throw new \Exception('Некорректно указан статус документа');
            }

        } catch (\Exception $e) {
            return json_encode([
                'status'        => 'error',
                'error_message' => $e->getMessage()
            ]);
        }

        $status = $_POST['status'] == 'accepted' ? 'Y' : 'N';

        $orders_api = new Order_Api();
        $result     =  $orders_api->signDocuments($_POST['order_id'], $_POST['document_id'], $status);

        if (empty($result)) {
            $result = [
                'status'        => 'error',
                'error_message' => 'Попробуйте повторить попытку позже.'
            ];
        }


        return json_encode($result);
    }
}