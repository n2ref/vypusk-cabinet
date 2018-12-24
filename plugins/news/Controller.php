<?php
namespace Cabinet\Plugin\News;
use Cabinet\Common;
use Cabinet\Mtpl;
use Cabinet\Curl;

require_once DOC_ROOT . '/cabinet/classes/Common.php';
require_once DOC_ROOT . '/cabinet/classes/Curl.php';
require_once DOC_ROOT . '/cabinet/classes/Mtpl.php';


/**
 * Class Controller
 * @package Cabinet\Plugin\News
 */
class Controller extends Common {

    /**
     * @return string
     */
    public function pageIndex() {

        $item_id = ! empty($_GET['item']) ? $_GET['item'] : 0;

        if ($item_id) {
            $item = $this->getNewsItem($item_id);
            $tpl  = new Mtpl(__DIR__ . '/html/item.html');

            $tpl->assign('[TITLE]', ! empty($item['title']) ? htmlspecialchars($item['title']) : '');
            $tpl->assign('[DATE]',  ! empty($item['date_created']) ? date('m.d.Y', strtotime($item['date_created'])) : '');
            $tpl->assign('[BODY]',  ! empty($item['body']) ? $item['body'] : '');

            return $tpl->render();

        } else {
            $tpl  = new Mtpl(__DIR__ . '/html/news.html');
            $news = $this->getNews();

            if ( ! empty($news)) {
                foreach ($news as $item) {
                    $tpl->news_item->assign('[DATE]',  ! empty($item['date_created']) ? date('m.d.Y', strtotime($item['date_created'])) : '');
                    $tpl->news_item->assign('[TITLE]', ! empty($item['title']) ? htmlspecialchars($item['title']) : '');
                    $tpl->news_item->assign('[ID]',    ! empty($item['id']) ? htmlspecialchars($item['id']) : '');
                    $tpl->news_item->reassign();
                }
            }

            return $tpl->render();
        }
    }


    /**
     * @return array
     */
    private function getNews() {

        $result = Curl::get($this->config->system->apiserver . "/api/news", [], [
            'Core2-apikey: ' . $this->config->system->apikey
        ]);

        $result_decode = $result->toArray();
        $data          = ! empty($result_decode['data']) && ! empty($result_decode['data']['news'])
            ? $result_decode['data']['news']
            : [];

        return $data;
    }


    /**
     * @param int $item_id
     * @return array
     */
    private function getNewsItem($item_id) {

        $result = Curl::get($this->config->system->apiserver . "/api/news/item", [
            'item_id' => $item_id
        ], [
            'Core2-apikey: ' . $this->config->system->apikey
        ]);

        $result_decode = $result->toArray();
        $data          = ! empty($result_decode['data']) && ! empty($result_decode['data']['item'])
            ? $result_decode['data']['item']
            : [];

        return $data;
    }
}