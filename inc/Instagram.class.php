<?php
use MetzWeb\Instagram\Instagram;

class InstagramHashtagSearch
{

    protected $callBackUrl,
    $apiKey,
    $apiSecret,
    $instagramObj,
        $accessToken;

    public function __construct(string $apiKey, string $apiSecret, string $callBackUrl, string $filename = 'result.csv')
    {
        $this->apiKey = $apiKey;
        $this->apiSecret = $apiSecret;
        $this->callBackUrl = $callBackUrl;
        $this->filename = $filename;
    }

    /**
     * Initial instagram app from another class
     *
     * @return void
     */

    private function init()
    {
        //Define an Instagram class object
        $this->instagramObj = new Instagram(array(
            'apiKey' => $this->apiKey,
            'apiSecret' => $this->apiSecret,
            'apiCallback' => $this->callBackUrl,
        ));
    }

    /**
     * Initial the app and search for hashtag
     * @param string $hashtag
     * @return array $searched_media
     */
    public function searchByHashtag(string $hashtag)
    {

        if (empty($_SESSION['access_token'])) {
            return false;
        }

        $this->init();
        $this->accessToken = $_SESSION['access_token'];
        $this->searchedMedia = $this->instagramObj->getTagMedia($hashtag);
        return $this->searchedMedia;
    }

    public function getLoginUrl()
    {
        $this->init();
        return $this->instagramObj->getLoginUrl(array('basic'));
    }

    public function generateAccessToken(string $code)
    {
        $_SESSION['access_token'] = $this->instagramObj->getOAuthToken($code);
    }

    /**
     * Show message as an alert block
     * 
     * @param string $message
     * @return string MessageBlock
     */
    
    public function ShowMessage($message, $type = 'success')
    {
        return '<div class="my-4 alert alert-block alert-' . $type . '">' . $message . '</div>';
    }

    /**
     * Sort instagram post by likes count, descending order
     * 
     * @param array,object $a
     * @param array,object $b
     * 
     * @return $a
     */
    private static function SortByLikeCount($a, $b)
    {
        //To detect if the data are collected by graphql
        if ($a['node']['edge_liked_by']['count'] > -1) {
            return $b['node']['edge_liked_by']['count'] > $a['node']['edge_liked_by']['count'];
        }

        return $b->likes->count > $a->likes->count;
    }


    /**
     * Parse Object and Array to readbale array
     * 
     * @param object,array $obj
     * @return array $res
     */
    public function parseObject($obj)
    {
        $res = array();
        if (is_array($obj)) {
            $res['id'] = $obj['node']['id'];
            $res['type'] = $obj['node']['is_video'] ? 'video' : 'image';
            $res['created_at'] = $obj['node']['taken_at_timestamp'];
            $res['shortcode'] = $obj['node']['shortcode'];
            $res['likes'] = $obj['node']['edge_liked_by']['count'];
            $res['comments'] = $obj['node']['edge_media_to_comment']['count'];
            $res['thumbnail_src'] = $obj['node']['thumbnail_src'];
            $res['caption'] = !empty($obj['node']['edge_media_to_caption']['edges'][0]['node']['text']) ? $obj['node']['edge_media_to_caption']['edges'][0]['node']['text'] : '';

        } else {

            $a['id'] = $obj->id;
            $a['type'] = $obj->type;
            $a['created_at'] = $obj->created_time;
            $a['shortcode'] = $obj->shortcode;
            $a['likes'] = $obj->likes->count;
            $a['comments'] = $obj->comments->count;
            $a['thumbnail_src'] = $obj->images->thumbnail->url;
            $a['caption'] = $obj->caption;
        }
        return $res;
    }

    /**
     * Write searched media into CSV file
     *
     * @param
     * @return string $filename
     * @return boolean
     */
    public function writeToFile()
    {
        if (count($this->searchedMedia)) {

            //Sort media by likes count desc
            usort($this->searchedMedia, array('InstagramHashtagSearch', 'SortByLikeCount'));

            //Create CVS file
            $file = fopen($this->filename, 'w');

            $sliced_array = array_slice($this->searchedMedia, 0, 100);

            fputcsv($file, array('id', 'type', 'created_at', 'shortcode', 'likes', 'comments', 'thumbnail_src', 'caption'));

            foreach ($sliced_array as $obj) {

                //Convert obj/array it to associative array
                $a = $this->parseObject($obj);

                fputcsv($file, array_values($a));
            }

            fclose($file);
            return $this->filename;
        } else {
            return false;
        }

    }

    /**
     * Get URL content by a safe CURL
     *
     * @param string $url
     * @param array $params
     *
     * @return string $result
     */
    public function getByCurl(string $url, $params = array())
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Accept: application/json'));
        if (!empty($params)) {
            curl_setopt($curl, CURLOPT_POST, count($params));
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($params));
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $result = curl_exec($curl);
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);
        return $result;
    }

    /**
     * Search hashtag by Graphql from Instagram without API key
     * 
     * @param string $tag
     * @return string $searched_media
     */
    public function searchByGraphql($tag)
    {

        $insta_source = $this->getByCurl('https://www.instagram.com/explore/tags/' . $tag . '/');
        $shards = explode('window._sharedData = ', $insta_source);
        $insta_json = explode(';</script>', $shards[1]);
        $insta_array = json_decode($insta_json[0], true);
        $this->searchedMedia = $insta_array['entry_data']['TagPage'][0]['graphql']['hashtag']['edge_hashtag_to_media']['edges'];

        return $this->searchedMedia;

    }

}
