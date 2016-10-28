<?php
App::uses('Component', 'Controller');
App::import('Vendor', 'TwitterValidation/Regex');
App::import('Vendor', 'TwitterValidation/Validation');
App::import('Vendor', 'TwitterValidation/Autolink');
App::import('Vendor', 'TwitterValidation/HitHighlighter');
App::import('Vendor','twitteroauth/autoload');
use Abraham\TwitterOAuth\TwitterOAuth;

class TwComponent extends Component{ 

  private $connect;

  private $Tw_image_valid = 22;
  public $Tw_limit = 140;

  private $tw_error_code = array(
    '32' => array('post_status' => 3, 'msg' => 'ユーザー認証失敗' ),
    '34' => array('post_status' => 3, 'msg' => 'Not found Page' ),
    '64' => array('post_status' => 3, 'msg' => 'アカウントが停止されています' ),
    '68' => array('post_status' => 3, 'msg' => 'Api v1.1を使用してください' ),
    '88' => array('post_status' => 4, 'msg' => 'レートの制限を超えました' ),
    '89' => array('post_status' => 3, 'msg' => 'トークンが期限切れです' ),
    '92' => array('post_status' => 3, 'msg' => 'SSL認証が必要です' ),
    '130' => array('post_status' => 3, 'msg' => '容量オーバーです' ),
    '131' => array('post_status' => 4, 'msg' => '内部エラーです' ),
    '135' => array('post_status' => 3, 'msg' => 'ユーザー認証失敗' ),
    '136' => array('post_status' => 3, 'msg' => 'あなたはブロックされています' ),
    '161' => array('post_status' => 3, 'msg' => '幾つかの制限にかかりました' ),
    '179' => array('post_status' => 3, 'msg' => '権限がありません' ),
    '185' => array('post_status' => 4, 'msg' => 'ステータス更新の限度を超えました' ),
    '186' => array('post_status' => 3, 'msg' => '140オーバー' ),
    '187' => array('post_status' => 3, 'msg' => 'ステータスが重複しています' ),
    '215' => array('post_status' => 3, 'msg' => '不良認証データ' ),
    '226' => array('post_status' => 3, 'msg' => 'スパム判定されました' ),
    '231' => array('post_status' => 3, 'msg' => 'ログインする必要があります' ),
    '251' => array('post_status' => 3, 'msg' => 'このエンドポイントは廃止されました。' ),
    '261' => array('post_status' => 3, 'msg' => '書き込み権限がありません' ),
    '271' => array('post_status' => 3, 'msg' => 'ミュートエラー' ),
    '272' => array('post_status' => 3, 'msg' => 'ミュートエラー' ),
    '354' => array('post_status' => 3, 'msg' => '最大文字数を超えています' ),
    );

   public function initialize(Controller $controller) {
    $this->Controller = $controller;
  }

  public function connect(){
    try{
      $this->connect = new TwitterOAuth(
        CONSUMER_KEY, CONSUMER_SECRET,
        TOKEN, TOKEN_SECRET
        );
    }catch(Exception $e){
      $this->log($e->getmessage());
    }
  }

  public function mediaTweet($img, $msg){
    $this->connect();
    $res = $this->connect->upload(
      'media/upload',
      array(
        'media' => $img,
        )
      );

    if(isset($res->errors)){
      $this->setErrors($res);
      return false;
    }

    $params = array(
      'status' => $msg,
      'media_ids' => $res->media_id_string,
      );
    $res = $this->connect->post('statuses/update', $params);
    if(isset($res->errors)){
      $this->setErrors($res);
      return false;
    }

    return true;

  }

  public function mediaTweetAll($post){
    $this->connect();

    foreach($post['Upload'] as $img){
      $res[] = $this->connect->upload(
        'media/upload',
        array(
          'media' => WWW_ROOT. $img['small_path']
          )
        );
    }

    foreach($res as $r){
      if(isset($r->errors)){
        $this->setErrors($r);
        return false;
      }
      $media_ids[] = $r->media_id_string;  
    }

    $media_ids = implode(',', $media_ids);
    $media_ids = rtrim($media_ids, ',');

    $post['Post']['content'] = preg_replace("/\r\n|\r|\n/", '', $post['Post']['content']);
    $params = array(
      'status' => $post['Post']['content'],
      'media_ids' => $media_ids
      );

    $res = $this->connect->post('statuses/update', $params);
    if(isset($res->errors)){
      $this->setErrors($res);
      return false;
    }

    return true;
  }

  public function tweet($msg){
    $this->connect();
    $msg = preg_replace("/\r\n|\r|\n/", '', $msg);
    $params = array(
      'status' => $msg,
      );
    $res = $this->connect->post('statuses/update', $params);
    if(isset($res->errors)){
      $this->setErrors($res);
      return false;
    }

    return true;
  }

  public function TwValidate($str, $upload){
    $valid = Twitter_Validation::create();
    $count = $valid->getTweetLength($str);

    if(isset($upload['tmp_name']) && !empty($upload['tmp_name'])){
      $count = $count + $this->Tw_image_valid;
    }

    return $count;
  }

  public function get_Tw_error($key){
    return $this->tw_error_code[$key];
  }

  /*******PrivateMethod*******/

  private function setErrors($res){
    Configure::write('Tw.error_code', $res->errors[0]->code);
    if(isset($this->tw_error_code[$res->errors[0]->code])){
      Configure::write('Tw.post_status_id', $this->tw_error_code[$res->errors[0]->code]['post_status']);
    }else{
      Configure::write('TW.post_status_id', 3);
    }
  }

  public function tes(){
    debug(TOKEN_SECRET);
  }


}





