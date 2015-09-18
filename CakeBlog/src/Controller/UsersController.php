<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\ORM\TableRegistry;
use Cake\Validation\Validator;
use Cake\Event\Event;
use App\Bean\PagingInfoBean;
use App\Util\PagingUtil;

/**
 * ユーザ関連APIのController
 *
 * @access public
 * @author Takayuki_suzuki
 * @package Controller
 */
class UsersController extends AppController
{
    // ページング表示件数
    protected $displayLimit = 5;
    
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
        
        // 権限確認を行う
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
            if (in_array($action, ['add'])) {
                return;
            } else {
                $this->Flash->error(__('権限がありません。'));
                return $this->redirect($this->referer());
            }
        }
    }

    /**
     * 一覧表示メソッド
     *
     * @access public
     */
    public function index()
    {
        // 現在ページ番号を取得する
        if(!empty($this->request->query['pagingId'])){
            $currentPageId = $this->request->query['pagingId'];
        } else {
            $currentPageId = 1;
        }
        // レコード取得開始位置判定
        $offsetPageId = $this->getOffsetPageId($currentPageId);
        
        // DBからユーザ一覧を取得する
        $users = TableRegistry::get('Users')->getPagingList($offsetPageId, $this->displayLimit);
        $this->set('users', $users);
        
        // ユーザの総数を取得する
        $total = TableRegistry::get('Users')->getRowCount();
        
        // ユーザが1件も無い場合
        if(empty($total)){
            $this->Flash->error(__('ユーザデータがありません。'));
        }
        
        $endPageId = round($total / $this->displayLimit);
        
        // ページング処理を実行
        $pagingInfoBean = (new PagingUtil())->paging($currentPageId, $endPageId);
        $this->set('pagingInfoBean', $pagingInfoBean);
    }

    /**
     * 詳細表示メソッド
     *
     * @access public
     * @param string $id　ユーザID
     * @throws NotFoundException
     */
    public function view($id)
    {
        if (! $id) {
            throw new NotFoundException(__('ユーザが見つかりませんでした。'));
        }
        // DBからユーザ情報を取得する
        $userEntity = TableRegistry::get('Users')->view($id);
        
        // ユーザの詳細情報を取得する
        if (! $userEntity) {
            $this->Flash->error(__('ユーザが見つかりませんでした。'));
            return $this->redirect(['action' => 'index']);
        }
        $this->set('user', $userEntity);
    }

    /**
     * ユーザ編集メソッド
     *
     * @access public
     * @param int $id　ブログのユーザID
     * @throws NotFoundException
     */
    public function edit($id)
    {
        if (! $id) {
            throw new NotFoundException(__('ユーザが見つかりませんでした。'));
        }
        // 編集対象レコードを取得する
        $tempEntity = TableRegistry::get('Users')->view($id);
        if (! $tempEntity) {
            $this->Flash->error(__('ユーザが見つかりませんでした。'));
            return $this->redirect(['action' => 'index']);
        }
        // リクエストの種類が不正でない場合
        if ($this->request->is('put')) {
            $userEntity = $this->Users->patchEntity($tempEntity, $this->request->data);
            // バリデーションチェック
            if ($userEntity->errors()) {
                $this->set('user', $userEntity);
                return;
            }
            // 確認処理
            $this->set('user', $userEntity);
            $this->Flash->success(__('下記の内容で編集してよろしいですか？'));
            return $this->render('edit_confirm');
        }
        // 初期表示用のデータを設定
        $this->set('user', $tempEntity);
    }
    
    /**
     * ユーザ編集実行メソッド
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
        
        // セッションからユーザ情報を取得して、エンティティを作成する
        $tempEntity = $this->Users->newEntity();
        $userEntity = $this->Users->patchEntity($tempEntity, 
            $this->request->session()->read('data'));
        
        // 更新日時を設定
        $userEntity->modified = date('Y-m-d H:i:s');
        // 編集処理
        if ($this->Users->save($userEntity)) {
            $this->Flash->success(__('ユーザの編集が完了しました。'));
            return $this->redirect(['action' => 'index']);
        } else {
            $this->Flash->error(__('ユーザの編集に失敗しました。やり直してください。'));
        }
    }

    /**
     * ユーザ削除メソッド
     *
     * @access public
     * @param int $id
     * @throws NotFoundException
     */
    public function delete($id)
    {
        if (! $id) {
            throw new NotFoundException(__('ユーザが見つかりませんでした。'));
        }
        $userEntity = TableRegistry::get('Users')->view($id);
        if (! $userEntity) {
            $this->Flash->error(__('ユーザが見つかりませんでした。'));
            return $this->redirect(['action' => 'index']);
        }
        $this->set('user', $userEntity);
        $this->Flash->success(__('下記の内容で削除してよろしいですか？'));
        
        // 削除フラグを削除済みに設定
        $userEntity-> delete_flag = 1;
        
        return $this->render('delete_confirm');
    }
    
    /**
     * ユーザ削除実行メソッド
     *
     * @access public
     */
    public function deleteFinish()
    {
        //戻るボタンが押された場合
        if(! empty($this->request->data['cancel'])){
            if($this->request->data['cancel']==true){
                return $this->redirect(['action' => 'index']);
            }
        }
        
        // セッションからユーザ情報を取得して、エンティティを作成する
        $userEntity = $this->Users
            ->get($this->request->session()->read('data')['id']);
    
        // 削除フラグを削除済みに設定
        $userEntity-> delete_flag = 1;

        // 削除を実行する
        if ($this->Users->save($userEntity)) {
            $this->Flash->success(__('ユーザの削除が完了しました。'));
            $this->redirect(['action' => 'index']);
        } else {
            $this->Flash->error(__('ユーザの削除に失敗しました。やり直してください。'));
        }
        $this->redirect(['action' => 'index']);
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
