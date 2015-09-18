<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\ORM\TableRegistry;
use Cake\Validation\Validator;
use Cake\Event\Event;
use App\Model\Table\RelationsTable;
use App\Model\Entity\Relation;
use App\Bean\PagingInfoBean;
use App\Util\PagingUtil;
use App\Util\ImageFileUtil;
use Cake\Auth\DefaultPasswordHasher;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;

/**
 * ブログAPIのController
 * ブログ投稿処理に関するAPIをまとめたコントローラークラス。
 *
 * @access public
 * @author Takayuki_suzuki
 * @package Controller
 */
class PostsController extends AppController
{
    // ページング表示件数
    protected $displayLimit = 5;
    
    // ページング表示件数
    protected $maxPageDisplayLimit = 7;
    
    /**
     * 初期化処理
     * beforeFilter()メソッドの前に呼び出される。
     *
     * @access public
     */
    public function initialize()
    {
        parent::initialize();
        // 共通ログイン認証を行う
        parent::loginCheck();
        // Csrfコンポーネント読み込み
        $this->loadComponent('Csrf');
    }

    /**
     * 共通処理
     *
     * @access public
     * @param Event $event イベント
     */
    public function beforeFilter(Event $event)
    {
        // Csrfトークンの作成をONにする
        $this->eventManager()->on($this->Csrf);
        
        // ユーザの権限を確認する
        return $this->checkPermission();
    }

    /**
     * ログインユーザの権限を確認する
     *
     * @access private
     * @return redirect 画面遷移先
     */
    private function checkPermission()
    {
        // ユーザのログイン情報がセッションに入っているか確認する
        $user = $this->request->session()->read('Auth.User');
        // アクションの情報を取得する
        $action = $this->request->params['action'];
        
        // ログインユーザの権限が「管理者」の場合
        if (isset($user['permission']) 
                && $user['permission'] === 'admin') {
            return true;
            
            // ログインユーザの権限が「一般」の場合
        } elseif (isset($user['permission']) &&
                $user['permission'] === 'author') {
           
            // 使用できるアクションを制限する
            if (in_array($action, [
                'index','add','addFinish','edit','editFinish',
                'view','viewTagBlogList','readImage','readTempImage'])) {
                return true;
            } else {
                $this->Flash->error(__('権限がありません。'));
                
                return $this->redirect($this->referer());
            }
        }
    }

    /**
     * 記事一覧表示メソッド
     *
     * @access public
     */
    public function index()
    {
        // リクエストで送られてきた現在ページ番号が空でない場合
        if (! empty($this->request->query['pagingId'])) {
            // ページ数を取得する
            $currentPageId = $this->request->query['pagingId'];
        } else {
            $currentPageId = 1;
        }
        
        // DBからレコード取得をする開始位置判定
        $offsetPageId = $this->getOffsetPageId($currentPageId);

        // DBから記事一覧を取得する
        $posts = TableRegistry::get('Posts')->index($offsetPageId, $this->displayLimit);
        $this->set('posts', $posts);
        
        // 記事の総数を取得する
        $total = TableRegistry::get('Posts')->getRowIndexCount();
        if(empty($total)){
            $this->Flash->error(__('記事データがありません。'));
        }

        $endPageId = round($total / $this->displayLimit);
        
        // ページング処理を実行
        $pagingInfoBean = (new PagingUtil())->paging($currentPageId, $endPageId);
        $this->set('pagingInfoBean', $pagingInfoBean);
        
        // タグマスタ情報リストを取得
        $resultTags = $this->getTagList();
        $this->set('tags', $resultTags);
        
        // ソートの仕方を設定(デフォルトはtrue=DESC)
        $this->set('sortFlg', true);
    }
    
    /**
     * 下書き記事一覧表示メソッド
     * 下書きステータスが「下書き」の記事を取得する
     *
     * @access public
     */
    public function indexDraft()
    {
        // リクエストで送られてきた現在ページ番号が空でない場合
        if (! empty($this->request->query['pagingId'])) {
            // ページ数を取得する
            $currentPageId = $this->request->query['pagingId'];
        } else {
            $currentPageId = 1;
        }
    
        // DBからレコード取得をする開始位置判定
        $offsetPageId = $this->getOffsetPageId($currentPageId);
    
        // DBから記事一覧を取得する
        $posts = TableRegistry::get('Posts')->indexDraft($offsetPageId, $this->displayLimit);
        $this->set('posts', $posts);
    
        // 記事の総数を取得する
        $total = TableRegistry::get('Posts')->getRowIndexDraftCount();
        if(empty($total)){
            $this->Flash->error(__('記事データがありません。'));
        }
    
        $endPageId = round($total / $this->displayLimit);
    
        // ページング処理を実行
        $pagingInfoBean = (new PagingUtil())->paging($currentPageId, $endPageId);
        $this->set('pagingInfoBean', $pagingInfoBean);
    
        // タグマスタ情報リストを取得
        $resultTags = $this->getTagList();
        $this->set('tags', $resultTags);
    }
    
    /**
     * 予約記事一覧表示メソッド
     * 下書きステータスが「公開中」且つ公開日が本日以降の
     * 記事を取得する
     *
     * @access public
     */
    public function indexReservations()
    {
        // リクエストで送られてきた現在ページ番号が空でない場合
        if (! empty($this->request->query['pagingId'])) {
            // ページ数を取得する
            $currentPageId = $this->request->query['pagingId'];
        } else {
            $currentPageId = 1;
        }
    
        // DBからレコード取得をする開始位置判定
        $offsetPageId = $this->getOffsetPageId($currentPageId);
    
        // DBから記事一覧を取得する
        $posts = TableRegistry::get('Posts')->indexReservations($offsetPageId, $this->displayLimit);
        $this->set('posts', $posts);
    
        // 記事の総数を取得する
        $total = TableRegistry::get('Posts')->getRowIndexReservationsCount();
        if(empty($total)){
            $this->Flash->error(__('記事データがありません。'));
        }
    
        $endPageId = round($total / $this->displayLimit);
    
        // ページング処理を実行
        $pagingInfoBean = (new PagingUtil())->paging($currentPageId, $endPageId);
        $this->set('pagingInfoBean', $pagingInfoBean);
    
        // タグマスタ情報リストを取得
        $resultTags = $this->getTagList();
        $this->set('tags', $resultTags);
    }

    /**
     * 詳細表示メソッド
     *
     * @access public
     * @param int $id　ブログの記事ID
     * @throws NotFoundException
     */
    public function view($id)
    {
        if (! $id) {
            throw new NotFoundException(__('記事が見つかりませんでした。'));
        }
        // 記事の詳細情報を取得する
        $postEntety = TableRegistry::get('Posts')->view($id);
        
        if (! $postEntety) {
            $this->Flash->error(__('記事が見つかりませんでした。'));
            return $this->redirect(['action' => 'index']);
        }
        // 画像情報がある場合
        if (! empty($postEntety->image)) {
            // セッションに画像情報を書き込む
            $session = $this->request->session();
            $session->write('fileInfo.filePath', $postEntety->image->path);
            $session->write('fileInfo.fileType', $postEntety->image->type);
        }
        $this->set('post', $postEntety);
    }

    /**
     * 記事追加メソッド
     * 
     * @access public
     * @return redirect 画面遷移先
     */
    public function add()
    {
        $session = $this->request->session();
        
        // 初期表示用のエンティティを作成
        $tempEntity = $this->Posts->newEntity();

        // タグマスタ情報リストを取得
        $resultTags = $this->getTagList();
        
        // 画像存在フラグをデフォルトはFALSEで設定する
        $this->set('ImageExistsFlg', false);

        // POSTで送られてきた場合
        if ($this->request->is('post')) {
            $session->write('data',$this->request->data);
            
            $postEntity = $this->Posts->patchEntity($tempEntity, $this->request->data);
            
            // バリデーションチェック
            if ($postEntity->errors()) {
                $this->set('post', $postEntity);
                $this->set('tag', $resultTags);
                return;
            }

            $postEntity->tags = [];
            // 入力値のタグIDをタグ名に変換して格納する
            foreach ($postEntity->tag_id as $tagId) {
                // タグIDをタグ名に変換して格納
                array_push($postEntity->tags, $resultTags[$tagId]);
            }

            if(empty($this->request->data['sameFileUploadFlg'])){
                $sameFileUploadFlg = false;
            }else{
                $sameFileUploadFlg = $this->request->data['sameFileUploadFlg'];
            }
            
            // 同じ画像情報をアップする場合
            if($sameFileUploadFlg){
                
                // 一時保存されているファイルを取得する
                $filePath = $session->read('tempFileInfo.tempFilePath');

                // 画像ファイルの各情報をセッションに設定する
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $session->write('sameImageFile.name', basename($filePath));// ファイル名
                $session->write('sameImageFile.type', finfo_file($finfo, $filePath));// MIMEタイプ
                $session->write('sameImageFile.size', filesize($filePath));// ファイルサイズ
                $session->write('fileUploadFlg', true);// ファイルアップロードフラグ
                $session->write('tempFileExistsFlg', true);// 一時ファイル存在フラグ
                
                // 画像存在フラグをTRUEで設定する
                $this->set('ImageExistsFlg', true);

            // 新しく選択した画像情報をアップする場合
            }else{

                $imageData = $this->request->data['imageFile'];
                
                // 画像ファイルがある場合
                if(! empty($imageData['name'])){
                
                    $imageFileUtil = new ImageFileUtil();
                    
                    // 画像情報に問題が無いか確認する
                    $resultFlg = $imageFileUtil->checkImage($imageData);

                    // 結果がFALSEだった場合
                    if($resultFlg){
                        $this->set('post', $tempEntity);
                        $this->set('tag', $resultTags);
                        return $this->render('add');
                    }
                    // 一時的に画像ファイルを格納する
                    $imageFileUtil->insertTempImage($imageData,$session);

                    // 画像存在フラグをTRUEで設定する
                    $this->set('ImageExistsFlg', true);
                }
            }
            $this->set('post', $postEntity);
            $this->Flash->success(__('下記の内容で登録してよろしいですか？'));
            return $this->render('add_confirm');
        }
        $filePath = $session->read('tempFileInfo.tempFilePath');
        
        // 初期表示用のデータを設定
        $this->set('post', $tempEntity);
        $this->set('tag', $resultTags);
    }
    
    /**
     * 記事追加実行メソッド
     *
     * @access public
     * @return redirect 画面遷移先
     */
    public function addFinish()
    {
        $session = $this->request->session();
        
        //戻るボタンが押された場合
        if(! empty($this->request->data['cancel'])){
            if($this->request->data['cancel'] == true){
                $this->set('post', $session->read('data'));
                $session->delete('fileUploadFlg');
                return $this->redirect($this->referer());
            }
        }

        // セッションから取得したタグ情報でエンティティを作成
        $tempEntity = $this->Posts->newEntity();
        $postEntity = $this->Posts->patchEntity($tempEntity,$session->read('data'));
        $postEntity->created = date('Y-m-d H:i:s'); // 作成日時を設定
        $postEntity->modified = date('Y-m-d H:i:s'); // 更新日時を設定
        $postEntity->draft_flag = 1; // 下書きフラグを1(公開中)で設定
        $postEntity->author_name = $session->read('Auth.User.name');// 作成者を設定
        
        // 登録処理
        // Postsテーブルに登録
        if ($this->Posts->save($postEntity)) {
            
            // Relationsテーブルに登録
            $this->saveRelations($postEntity);
            
            // 画像情報を取得する
            if(! $session->read('sameImageFile') == null){
               $image = $this->request->session()->read('sameImageFile');
            }else{
                $image = $session->read('data')['imageFile'];
            }
            
            // ファイルアップロード
            if($session->read('fileUploadFlg')){
                // 画像情報をDBに登録し、画像ファイルを「tmp」フォルダから移動させる
                (new ImageFileUtil())->insertImage($image, $postEntity->id,$session);
                $session->delete('fileUploadFlg');
            }
            // セッションに格納してあるフォーム情報を削除する
            $session->delete('data');
            $session->delete('sameImageFile');
            
            // 記事投稿メールを送信する
            $this->sendMailPost();
            
            $this->Flash->success(__('記事が投稿されました。'));
            return $this->redirect(['action' => 'index']);

        } else {
            $this->Flash->error(__('記事の投稿に失敗しました。やり直してください。'));
        }
    }
    
    /**
     * 下書き保存実行メソッド
     *
     * @access public
     * @return redirect 画面遷移先
     */
    public function addDraftFinish()
    {
        $session = $this->request->session();
            
        // セッションから取得したタグ情報でエンティティを作成
        $tempEntity = $this->Posts->newEntity();
        $postEntity = $this->Posts->patchEntity($tempEntity, $session->read('data'));
        $postEntity->created = date('Y-m-d H:i:s'); // 作成日時を設定
        $postEntity->modified = date('Y-m-d H:i:s'); // 更新日時を設定
        $postEntity->draft_flag = 0; // 下書きフラグを0(下書き)で設定
        $postEntity->author_name = $session->read('Auth.User.name'); // 作成者を設定

        // 記事登録処理
        if ($this->Posts->save($postEntity)) {
            
            // Relationsテーブルに登録
            $this->saveRelations($postEntity);
            $image = $session->read('data')['imageFile'];
            
            // 画像情報を取得する
            if (! $session->read('sameImageFile') == null) {
                $image = $session->read('sameImageFile');
            } else {
                $image = $session->read('data')['imageFile'];
            }
            // ファイルアップロード
            if ($session->read('fileUploadFlg')) {
                // Imagesテーブルに登録
                (new ImageFileUtil())->insertImage($image, $postEntity->id, $session);
                $session->delete('fileUploadFlg');
            }
            // セッションに格納してあるフォーム情報を削除する
            $session->delete('data');
            $session->delete('sameImageFile');
            $this->Flash->success(__('記事が保存されました。'));
            return $this->redirect([
                'action' => 'index'
            ]);
        } else {
            $this->Flash->error(__('記事の投稿に失敗しました。やり直してください。'));
        }
    }

    /**
     * 記事編集メソッド
     *
     * @access public
     * @param int $id　ブログの記事ID
     * @return redirect 画面遷移先
     * @throws NotFoundException
     */
    public function edit($id)
    {
        if (! $id) {
            throw new NotFoundException(__('記事が見つかりませんでした。'));
        }
        // 記事の情報を取得する
        $tempEntity = TableRegistry::get('Posts')->view($id);

        if (! $tempEntity) {
            $this->Flash->error(__('記事が見つかりませんでした。'));
            return $this->redirect(['action' => 'index']);
        }
        // リクエストの種類が不正でない場合
        if ($this->request->is(['put'])) {
            $postEntity = $this->Posts->patchEntity($tempEntity, $this->request->data);
            // バリデーションチェック
            if ($postEntity->errors()) {
                $this->set('post', $postEntity);
                return;
            }
            
            // 下書きマスタ情報リストを取得
            $resultDrafs = $this->getDrafsInfoList();
           
            $postEntity->draft_status = $resultDrafs[$postEntity->draft_flag];
            
            // 確認処理
            $this->set('post', $postEntity);
            $this->Flash->success(__('下記の内容で編集してよろしいですか？'));
            return $this->render('edit_confirm');
        }
        // 初期表示用のデータを設定
        $this->set('post', $tempEntity);
    }
    
    /**
     * 記事編集実行メソッド
     *
     * @access public
     * @return redirect 画面遷移先
     */
    public function editFinish()
    {
        //戻るボタンが押された場合
        if(! empty($this->request->data['cancel'])){
            if($this->request->data['cancel']==true){
                return $this->redirect($this->referer());
            }
        }
        
        $tempEntity = $this->Posts->newEntity();
        $postEntity = $this->Posts
            ->patchEntity($tempEntity,  $this->request->session()->read('data'));
        // 更新日時を設定
        $postEntity->modified = date('Y-m-d H:i:s');
        // 編集処理
        if ($this->Posts->save($postEntity)) {
            $this->Flash->success(__('記事の編集が完了しました。'));
            return $this->redirect(['action' => 'index']);
        } else {
            $this->Flash->error(__('記事の編集に失敗しました。やり直してください。'));
        }
    }

    /**
     * 記事削除メソッド
     *
     * @access public
     * @param int $id　ブログの記事ID
     * @return redirect 画面遷移先
     * @throws NotFoundException
     */
    public function delete($id)
    {
        if (! $id) {
            throw new NotFoundException(__('記事が取得できませんでした。'));
        }
        
        // 記事の情報を取得する
        $postEntity = TableRegistry::get('Posts')->view($id);
        if (! $postEntity) {
            $this->Flash->error(__('記事が見つかりませんでした。'));
            return $this->redirect(['action' => 'index']);
        }
        // 確認処理
        $this->set('post', $postEntity);
        $this->Flash->success(__('下記の内容で削除してよろしいですか？'));
        return $this->render('delete_confirm');
    }
    
    /**
     * 記事削除実行メソッド
     *
     * @access public
     * @return redirect 画面遷移先
     * @throws NotFoundException
     */
    public function deleteFinish()
    {
        //戻るボタンが押された場合
        if(! empty($this->request->data['cancel'])){
            if($this->request->data['cancel']==true){
                return $this->redirect(['action' => 'index']);
            }
        }
        
        // セッションから取得したタグ情報で削除対象レコードを取得
        $postEntity = TableRegistry::get('Posts')
            ->view($this->request->session()->read('data')['id']);
        
        // 削除フラグを削除済みに設定
        $postEntity->delete_flag = 1;
        
        // 削除を実行
        if ($this->Posts->save($postEntity)) {
            $this->Flash->success(__('記事の削除が完了しました。'));
        } else {
            $this->Flash->error(__('記事の削除に失敗しました。やり直してください。'));
        }
        return $this->redirect(['action' => 'index']);
    }

    /**
     * 検索メソッド
     * 
     * @access public
     * @return redirect 画面遷移先
     */
    public function search()
    {
        // 初期表示用データの初期化
        $tempEntity = $this->Posts->newEntity();
        $resultTags = [];
        $resultPosts = [];
        $pagingInfoBean = new PagingInfoBean();
        
        // タグマスタ情報リストを取得
        $resultTags = $this->getTagList();
        
        // 初期表示用のデータを設定
        $this->set('posts', $resultPosts);
        $this->set('post', $tempEntity);
        $this->set('tag', $resultTags);
        $this->set('pagingInfoBean', $pagingInfoBean);
        
        // 検索条件が設定されている場合
        if (! empty($this->request->query)) {
            $postEntity = $this->Posts->patchEntity($tempEntity, $this->request->query);
            // バリデーションチェック
            if ($postEntity->errors()) {
                return;
            }
            // 検索を実行
            $this->executeSearch();
        }
    }
    
    /**
     * 選択されたタグを含む記事を返却するメソッド
     *      
     * @access private
     * @return true or false
     */
    public function viewTagBlogList()
    {
        // 初期表示用データの初期化
        $resultTags = [];
        
        // セッションからタグIDを取得する
        $tagId = 0;
        $session = $this->request->session();
        if (! empty($this->request->query['tag_id'])) {
            $tagId = $this->request->query['tag_id'];
            $session->write('TagBlogList.tagId', $tagId);
        } else {
            $tagId = $session->read('TagBlogList.tagId');
        }
        
        // 選択されたタグを含む記事を取得する
        $this->getTagBlogList($tagId);

        // タグマスタ情報リストを取得
        $resultTags = $this->getTagList();
        $this->set('tags', $resultTags);
        $this->set('currentTagName', $resultTags[$tagId]);
    }
    
    /**
     * 画像情報を読み込み、出力するメソッド
     *
     * @access public
     */
    public function readImage()
    {
        // 画像ファイルのMIMEタイプを取得
        header('Content-type:'.$this->request->session()->read('fileInfo.fileType'));
        // セッションから画像の保存先のパスを取得
        $filePath = $this->request->session()->read('fileInfo.filePath');
        // ファイル情報を読み込み、出力する
        readfile($filePath);
        exit;
    }
    
    /**
     * 一時保存画像情報を読み込み、出力するメソッド
     *
     * @access public
     */
    public function readTempImage()
    {
        // 画像ファイルのMIMEタイプを取得
        header('Content-type:'.$this->request->session()->read('tempFileInfo.fileType'));
        // セッションから画像の保存先のパスを取得
        $filePath = $this->request->session()->read('tempFileInfo.tempFilePath');
        // ファイル情報を読み込み、出力する
        readfile($filePath);
        exit;
    }
    
    /**
     * ソートメソッド
     * 記事の指定されたカラムに対して、ソートをかける。
     *
     * @access public
     * @param string $sortTarget ソート対象
     * @param string $pagingId 現在ページ番号
     * @param string $orderSetting ソート設定(昇順、降順)
     * 
     * @return redirect 画面遷移先
     */
    public function sort($sortTarget, $pagingId, $orderSetting)
    {
        // リクエストで送られてきた現在ページ番号が空でない場合
        if (! empty($pagingId)) {
            // ページ数を取得する
            $currentPageId = $pagingId;
        } else {
            $currentPageId = 1;
        }
    
        // DBからレコード取得をする開始位置判定
        $offsetPageId = $this->getOffsetPageId($currentPageId);
    
        // DBから記事一覧を取得する
        $posts = TableRegistry::get('Posts')
            ->sort($offsetPageId, $this->displayLimit,$sortTarget,$orderSetting);
        $this->set('posts', $posts);
    
        // 記事の総数を取得する
        $total = TableRegistry::get('Posts')->getRowIndexCount();
        if(empty($total)){
            $this->Flash->error(__('記事データがありません。'));
        }
        $endPageId = round($total / $this->displayLimit);
    
        // ページング処理を実行
        $pagingInfoBean = (new PagingUtil())->paging($currentPageId, $endPageId);
        $this->set('pagingInfoBean', $pagingInfoBean);
    
        // タグマスタ情報リストを取得
        $resultTags = $this->getTagList();
        $this->set('tags', $resultTags);
        
        // リクエストで送られてきたソート設定によって、フラグを変更する
        if(strcmp($orderSetting,'ASC')){
            $this->set('sortFlg', false);
        } elseif (strcmp($orderSetting,'DESC')){
            $this->set('sortFlg', true);
        }
        // 今回、指定したソート対象を設定
        $this->set('sortTarget', $sortTarget);
        
        return $this->render('index');
    }

    /**
     * タグマスタ情報取得メソッド
     * タグのチェックボックスの情報を取得する際に使用する
     *
     * @access private
     * @return array tagList タグマスタ情報リスト
     */
    private function getTagList()
    {
        $resultTags = [];
        
        // チェックボックス表示用のデータを取得
        $tagsEntity = TableRegistry::get('Tags')->getTagList();
        
        // 番号がずれないように0番目を空で設定
        array_push($resultTags, '');
        foreach ($tagsEntity as $tag) {
            array_push($resultTags, $tag['tag_name']);
        }
        unset($resultTags[0]);
        
        return $resultTags;
    }
    
    /**
     * 下書きマスタ情報取得メソッド
     * 編集時のステータスを表示するのに使用する
     *
     * @access private
     * @return array tagList タグマスタ情報リスト
     */
    private function getDrafsInfoList()
    {
        $resultDrafs = [];
        $drafsEntity = TableRegistry::get('Drafts')->find('all');
    
        foreach ($drafsEntity as $draft) {
            array_push($resultDrafs, $draft['draft_status']);
        }
    
        return $resultDrafs;
    }
    
    /**
     * レコード取得開始位置判定メソッド
     *
     * @access private
     * @param int $currentPageId 現在ページ番号
     * @return int $offsetPageId レコード取得開始位置
     */
    private function getOffsetPageId($currentPageId)
    {
        // レコード取得開始位置判定
        switch ($currentPageId) {
            case 1:
                $offsetPageId = 0;
                break;
            case 2:
                $offsetPageId = $this->displayLimit;
                break;
            default:
                $offsetPageId = ($currentPageId * $this->displayLimit) - $this->displayLimit;
                break;
        }
        return $offsetPageId;
    }
    
    /**
     * Relationsテーブルへの登録メソッド
     *
     * @access private
     * @return $postEntity 記事情報
     */
    private function saveRelations($postEntity)
    {
        // Relationsテーブルに登録
        $relations = TableRegistry::get('Relations');
        $relationEntity = $relations->newEntity();
        $relationsEntity = $relations->patchEntity(
            $relationEntity, $this->request->data);
        
        // タグの数だけ登録処理を繰り返す
        foreach ( $postEntity->tag_id as $tagId) {
        
            // タグを登録する
            $relationsEntity->tag_id = $tagId;
            $relationsEntity->post_id = $postEntity->id;
            $relations->save($relationsEntity);
        
            // エンティティを初期化する
            $relations = TableRegistry::get('Relations');
            $relationEntity = $relations->newEntity();
            $relationsEntity = $relations->patchEntity(
                $relationEntity, $this->request->data);
        
            // IDを初期化する
            $relationsEntity->id = 0;
        }
    }
    
    /**
     * 記事検索を実行するメソッド
     *
     * @access private
     */
    private function executeSearch()
    {
        $session = $this->request->session();
        
        // 現在ページ番号を取得する
        if (! empty($this->request->query['pagingId'])) {
            $currentPageId = $this->request->query['pagingId'];
        } else {
            $currentPageId = 1;
        }
        
        // セッションから検索条件を取得する
        $keyword = null;
        $tagIdList = [];
        
        // 検索キーワード取得
        if (! empty($this->request->query['keyword'])) {
            $keyword = $this->request->query['keyword'];
            $session->write('Search.keyword', $keyword);
        } else {
            $keyword = $session->read('Search.keyword');
        }
        // タグID取得
        if (! empty($this->request->query['tag_id'])) {
            $tagIdList = $this->request->query['tag_id'];
            $session->write('Search.tagIdList', $tagIdList);
        } else {
            $tagIdList = $session->read('Search.tagIdList');
        }

        // レコード取得開始位置判定
        $offsetPageId = $this->getOffsetPageId($currentPageId);
        
        // 検索条件に一致する記事情報を取得する
        $resultPosts = TableRegistry::get('Posts')
            ->search($keyword, $tagIdList, $this->displayLimit, $offsetPageId);

        // 検索結果を設定
        $this->set('posts', $resultPosts);
        
        // 行数カウント
        $total = TableRegistry::get('Posts')->getSearchRowCount($tagIdList, $keyword);
        $endPageId = round($total / $this->displayLimit);
        
        // ページング処理を実行
        $pagingInfoBean = (new PagingUtil())->paging($currentPageId, $endPageId);
        
        $this->set('pagingInfoBean', $pagingInfoBean);
    }
    
    /**
     * タグ記事検索を実行するメソッド
     *
     * @access private
     * @return $tagId 選択されたタグID
     */
    private function getTagBlogList($tagId)
    {
        // 現在ページ番号を取得する
        if (! empty($this->request->query['pagingId'])) {
            $currentPageId = $this->request->query['pagingId'];
        } else {
            $currentPageId = 1;
        }

        // レコード取得開始位置判定
        $offsetPageId = $this->getOffsetPageId($currentPageId);
        
        // DBから選択されたタグを含む記事を取得する
        $resultPosts = TableRegistry::get('Posts')
        ->getTagBlogList($tagId,$this->displayLimit,$offsetPageId);
        $this->set('posts',$resultPosts);
        
        // 行数カウント
        $total = TableRegistry::get('Posts')->getTagBlogListRowCount($tagId);
        if(empty($total)){
            $this->Flash->error(__('記事データがありません。'));
        }
        $endPageId = round($total / $this->displayLimit);
        
        // ページング処理を実行
        $pagingInfoBean = (new PagingUtil())->paging($currentPageId, $endPageId);
        $this->set('pagingInfoBean', $pagingInfoBean);
    }

    /**
     * 記事投稿メール送信メソッド
     *
     * @access private
     */
    private function sendMailPost()
    {
        $user = $this->request->session()->read('Auth.User');
        
        // 管理者ユーザの情報を取得する
        $users = TableRegistry::get('Users');
        $adminUser = $users->find()
        ->where(['permission' => 'admin'])
        ->first();

        // 言語設定、内部エンコーディングを指定する
        mb_language("japanese");
        mb_internal_encoding("EUC-JP");
        
        // 記事投稿完了メールの値を設定する
        $to = $adminUser->mail_address;
        $from = mb_encode_mimeheader(mb_convert_encoding($adminUser->name,
            "EUC-JP", "UTF-8")) . "<".$adminUser->mail_address.">";
        $subject = mb_convert_encoding($user['name'].'さんが記事を投稿しました。',
            "EUC-JP", "UTF-8");
        $body = mb_convert_encoding('記事の投稿が完了しました。', "EUC-JP", "UTF-8");
        
        // メールを送信する
        mb_send_mail($to, $subject, $body, "From:" . $from);
    }
}
