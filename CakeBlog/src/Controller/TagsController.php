<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\ORM\TableRegistry;
use Cake\Validation\Validator;
use Cake\Event\Event;
use App\Bean\PagingInfoBean;
use App\Util\PagingUtil;

/**
 * タグ関連APIのController
 *
 * @access public
 * @author Takayuki_suzuki
 * @package Controller
 */
class TagsController extends AppController
{
    
    // ページング表示件数
    protected  $displayLimit = 5;

    /**
     * 初期化処理
     * beforeFilter() メソッドの前に呼び出される。
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
    
        // ログインユーザの権限が管理者の場合
        if (isset($user['permission']) && $user['permission'] === 'admin') {
            return;
            
        // ログインユーザの権限が一般の場合
        } elseif (isset($user['permission'])
                && $user['permission'] === 'author') {
            // 使用できるアクションを制限する
            if (in_array($action, ['index'])) {
                return;
            } else {
                $this->Flash->error(__('権限がありません。'));
                // 前画面に遷移する
                return $this->redirect($this->referer());
            }
        }
    }

    /**
     * タグ一覧表示メソッド
     *
     * @access public
     */
    public function index()
    {
        // リクエストで送られてきた現在ページ番号が空でない場合
        if (! empty($this->request->query['pagingId'])) {
            // ページ数を取得
            $currentPageId = $this->request->query['pagingId'];
        } else {
            $currentPageId = 1;
        }
        
        // レコード取得開始位置判定
        $offsetPageId = $this->getOffsetPageId($currentPageId);
        
        // DBから選択されたタグを含む記事を取得する
        $reasultTags = TableRegistry::get('Tags')->getPagingList($offsetPageId, $this->displayLimit);
        $this->set('tags', $reasultTags);
        
        // 行数カウント
        $total = TableRegistry::get('Tags')->getRowCount();
        
        // タグが1件も無い場合
        if(empty($total)){
            $this->Flash->error(__('タグデータがありません。'));
        }
        $endPageId = round($total / $this->displayLimit);
        
        // ページング処理を実行
        $pagingInfoBean = (new PagingUtil())->paging($currentPageId, $endPageId);
        $this->set('pagingInfoBean', $pagingInfoBean);
    }

    /**
     * タグ追加メソッド
     *
     * @access public
     */
    public function add()
    {
        $tempEntity = $this->Tags->newEntity();
        
        // POSTで送られてきた場合
        if ($this->request->is('post')) {
            
            $tagEntity = $this->Tags->patchEntity($tempEntity, $this->request->data);
            
            // バリデーションチェック
            if ($tagEntity->errors()) {
                $this->set('tag', $tagEntity);
                return;
            }
            // 確認処理
            $this->set('tag', $tagEntity);
            return $this->render('add_confirm');
        }
        // 初期表示用のデータを設定
        $this->set('tag', $tempEntity);
    }
    
    /**
     * タグ追加実行メソッド
     *
     * @access public
     * @return redirect 画面遷移先
     */
    public function addFinish()
    {
        //戻るボタンが押された場合
        if(! empty($this->request->data['cancel'])){
            if($this->request->data['cancel']==true){
                return $this->redirect($this->referer());
            }
        }
        
        // セッションから取得したタグ情報でエンティティを作成
        $tempEntity = $this->Tags->newEntity();
        $tagEntity = $this->Tags->patchEntity($tempEntity,
            $this->request->session()->read('data'));

        // 登録処理
        if ($this->Tags->save($tagEntity)) {
            $this->Flash->success(__('タグが投稿されました。'));
            return $this->redirect(['action' => 'index']);
        } else {
            $this->Flash->error(__('タグの投稿に失敗しました。やり直してください。'));
        }
    }

    /**
     * タグ編集メソッド
     *
     * @access public
     * @param string $id ブログのタグID
     * @return redirect 画面遷移先
     * @throws NotFoundException
     */
    public function edit($id)
    {
        if (! $id) {
            throw new NotFoundException(__('タグが見つかりませんでした。'));
        }
        // 編集対象レコードを取得する
        $tempEntity = TableRegistry::get('Tags')->view($id);
        
        if (! $tempEntity) {
            $this->Flash->error(__('タグが見つかりませんでした。'));
            return $this->redirect(['action' => 'index']);
        }
        if ($this->request->is(['patch','post','put'])) {
            $tagEntity = $this->Tags->patchEntity($tempEntity, $this->request->data);

            // バリデーションチェック
            if ($tagEntity->errors()) {
                $this->set('tag', $tempEntity);
                return;
            }
            // 確認処理
            $this->set('tag', $tempEntity);
            return $this->render('edit_confirm');
        }
        // 初期表示用のデータを設定
        $this->set('tag', $tempEntity);
    }
    
    /**
     * タグ編集実行メソッド
     *
     * @access public
     * @param string $id　ブログのタグID
     * @return redirect 画面遷移先
     */
    public function editFinish($id)
    {
        //戻るボタンが押された場合
        if(! empty($this->request->data['cancel'])){
            if($this->request->data['cancel']==true){
                return $this->redirect($this->referer());
            }
        }
        
        $tempEntity = $this->Tags->newEntity();
        $tagEntity = $this->Tags
            ->patchEntity($tempEntity,  $this->request->session()->read('data'));
        
        // 編集処理
        if ($this->Tags->save($tagEntity)) {
            $this->Flash->success(__('タグの編集が完了しました。'));
            return $this->redirect(['action' => 'index']);
        } else {
            $this->Flash->error(__('タグの編集に失敗しました。やり直してください。'));
        }
    }

    /**
     * タグ削除メソッド
     *
     * @access public
     * @param string $id　ブログのタグID
     * @return redirect 画面遷移先
     * @throws NotFoundException
     */
    public function delete($id)
    {
        if (! $id) {
            throw new NotFoundException(__('タグが取得できませんでした。'));
        }

        // 削除対象のタグに関連付けされている記事がないかを検索する
        $tagDeleteCheckEntitys = TableRegistry::get('Tags')->checkTagDelete($id);
        
        // チェックフラグを初期化
        $checkFlg = false;
        
        // 関連付けされているタグがある数だけ繰り返す
        foreach ($tagDeleteCheckEntitys as $tagDeleteCheckEntity) {
            $checkFlg = true;
        }
        // 該当するレコードがある場合
        if ($checkFlg == true) {
            $this->Flash->error(__('このタグは関連付けされている為、削除できません。'));
            return $this->redirect(['action' => 'index']);
        }
        
        // 削除対象のレコードを取得する
        $tempEntity = TableRegistry::get('Tags')->view($id);
        
        if (! $tempEntity) {
            $this->Flash->error(__('タグが見つかりませんでした。'));
            return $this->redirect(['action' => 'index']);
        }

        // 確認処理
        $this->set('tag', $tempEntity);
        return $this->render('delete_confirm');
    }
    
    /**
     * タグ削除実行メソッド
     *
     * @access public
     * @return redirect 画面遷移先
     */
    public function deleteFinish()
    {
        //戻るボタンが押された場合
        if(! empty($this->request->data['cancel'])){
            if($this->request->data['cancel']==true){
                return $this->redirect(['action' => 'index']);
            }
        }
        
        // セッションから取得したタグ情報でエンティティを作成
        $tagEntity = $this->Tags
            ->get($this->request->session()->read('data')['id']);
 
        // タグ削除を実行
        if ($this->Tags->delete($tagEntity)) {
            $this->Flash->success(__('タグの削除が完了しました。'));
        } else {
            $this->Flash->error(__('タグの削除に失敗しました。やり直してください。'));
        }
        return $this->redirect(['action' => 'index']);
    }
    
    /**
     * レコード取得開始位置判定メソッド
     *
     * @access private
     * @param int $currentPageId 現在ページ番号
     * @return $offsetPageId レコード取得開始位置
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
}
