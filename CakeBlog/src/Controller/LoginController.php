<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\ORM\TableRegistry;
use Cake\Validation\Validator;
use Cake\Event\Event;
use Cake\Auth\DefaultPasswordHasher;
use App\Bean\PagingInfoBean;
use App\Util\PagingUtil;
use Cake\Controller;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
use Cake\Routing\Router;

/**
 * ログイン関連APIのController
 *
 * @access public
 * @author Takayuki_suzuki
 * @package Controller
 */
class LoginController extends AppController
{
    // ページング表示件数
    protected $displayLimit = 5;
    
    // メール情報
    protected $sendMailUrl;
    
    /**
     * 初期化処理
     * beforeFilter()メソッドの前に呼び出される。
     *
     * @access public
     */
    public function initialize()
    {
        parent::initialize();
        // コンポーネント読み込み
        $this->loadComponent('Csrf');
        $this->loadComponent('Cookie');
        
        // 登録確認メールのURLを取得する
        Configure::config('default', new PhpConfig());
        Configure::load('app', 'default', false);
        $this->sendMailUrl = Configure::read('Email.default.addUserSendMailUrl');
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
            
        // ユーザの権限確認を行う
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
            if (in_array($action, [
                'login','logout','add','addConfirm','addFinish'])) {
                return;
            } else {
                $this->Flash->error(__('権限がありません。'));
                return $this->redirect($this->referer());
            }
        }
    }

    /**
     * ログイン認証メソッド
     * セッションが切れている場合、各画面から必ず呼ばれる
     *
     * @access public
     * @return redirect 画面遷移先
     */
    public function login()
    {
        // 自動ログインフラグが存在するか確認する
        if (! empty($this->request->data['autoLoginFlg'])) {
            $autoLoginFlg = $this->request->data['autoLoginFlg'];
        } else {
            $autoLoginFlg = false;
        }
        // 初期表示用のエンティティを作成
        $tempEntity = TableRegistry::get('Users')->newEntity();
        $userEntity = TableRegistry::get('Users')->patchEntity($tempEntity, $this->request->data);
        $this->set('user', $tempEntity);
        
        // 自動ログイン確認処理
        $autoLoginInfo = $this->Cookie->read('auto_login');
        if (! empty($autoLoginInfo)) {
            $this->checkAutoLogin();
            return;
        }
        // ユーザ名、パスワードがリクエストされてきた場合
        if ($this->request->is('post')) {
            
            // バリデーションチェック
            if ($userEntity->errors()) {
                $this->set('post', $userEntity);
                return $this->render('login');
            }
            $userName = $this->request->data['name'];
            // 自動ログインにチェックがついていない場合
            if (! $autoLoginFlg) {
                // 通常のログイン設定を行う
                $this->setUpLogin($userName);
                return;
                
            // 自動ログイン設定がされていた場合
            } else {
                // 通常のログイン設定を行う
                $this->setUpLogin($userName);

                // 自動ログイン設定を行う
                $this->setupAutoLogin($userName);
                return;
            }
        }
        // 初期表示時にクッキーを初期化する
        $this->Cookie->delete('auto_login');
    }
    
    /**
     * ログアウトメソッド
     * 
     * @access public
     * @return render 画面遷移先
     */
    public function logout()
    {
        $autoLoginInfo = $this->Cookie->read('auto_login');
        
        // オートログイン情報が設定されていれば、削除する   
        if (! empty($autoLoginInfo)) {
            $this->deleteAutoLoginInfo($autoLoginInfo);
        }
        // セッションを削除する
        $session = $this->request->session();
        session_destroy();
        // クッキーを削除する
        $this->Cookie->delete('auto_login');
        
        $this->Flash->success(__('ログアウトしました。'));
        
        // 初期表示用のデータを設定する
        $tempEntity = TableRegistry::get('Users')->newEntity();
        $this->set('user', $tempEntity);
        
        return $this->redirect([
            'controller' => 'Login',
            'action' => 'login'
         ]);
    }

    /**
     * ユーザ追加メソッド
     *
     * @access public
     * @return redirect 画面遷移先
     */
    public function add()
    {
        $users = TableRegistry::get('Users');
        
        // エンティティを作成
        $tempEntity = $users->newEntity();
        $userEntity = $users->patchEntity($tempEntity, $this->request->data);
    
        // POSTでリクエストが送られてきた場合
        if ($this->request->is('post')) {

            // バリデーションチェック
            if ($userEntity->errors()) {
                $this->set('user', $userEntity);
                return;
            }
            // 同じユーザ名、メールアドレス且つ登録済みユーザはいないか確認する
            $mailAddress = $this->request->data['mail_address'];
            $userName = $this->request->data['name'];
            $this->checkDuplicateUser($userName, $mailAddress);
            
            // 確認処理
            $this->set('user', $userEntity);
            $this->Flash->success(__('下記の内容で編集してよろしいですか？'));
            return $this->render('add_confirm');
        }
        $this->set('user', $tempEntity);
    }
    
    /**
     * ユーザ追加確認メソッド
     *
     * @access public
     * @return redirect 画面遷移先
     */
    public function addConfirm()
    {
        $sessionData = $this->request->session()->read('data');
        $users = TableRegistry::get('Users');
            
        // 戻るボタンが押された場合
        if (! empty($this->request->data['cancel'])) {
            if ($this->request->data['cancel'] == true) {
                return $this->redirect($this->referer());
            }
        }
        // セッションを確認して、二重登録を防止する
        if(empty($sessionData)){
            $this->Flash->error(__('ユーザの登録に失敗しました。'));
            return $this->redirect($this->referer());
        }
        // エンティティを作成
        $tempEntity = $users->newEntity();
        $userEntity = $users->patchEntity($tempEntity, $sessionData);
        $userEntity->created = date('Y-m-d H:i:s');
        $userEntity->modified = date('Y-m-d H:i:s');
        $userEntity->limit_time = date('Y-m-d H:i:s',strtotime('+24 hour'));
        $registrationToken = sha1(uniqid() . mt_rand(1, 999999999) . $userEntity->mail_address);
        $userEntity->registration_token = $registrationToken;
        $userEntity->registration_flag = 0; // 仮登録(0)で設定
        
        // ユーザ情報を仮登録する
        if ($users->save($userEntity)) {
            // 登録確認メールのURL(トークン付)
            $sendMailUrl = $this->sendMailUrl . $registrationToken;
            
            // 登録確認メールを送信する
            $this->sendMailUserConfirm($sendMailUrl, $userEntity->mail_address, $userEntity->name);

            $this->set('user', $tempEntity);
            return $this->render('add_applied');
        } else {
            $this->Flash->error(__('ユーザの登録に失敗しました。'));
        }
    }

    /**
     * ユーザ追加実行メソッド
     *
     * @access public
     * @return redirect 画面遷移先
     */
    public function addFinish()
    {
        $users = TableRegistry::get('Users');
        
        // 送られてきたトークンが空でない場合
        if (! empty(array_keys($this->request->query)[0])) {
            $registrationToken = array_keys($this->request->query)[0];
        // 空の場合
        }else{
            $this->Flash->error(__('ユーザの登録に失敗しました。'));
            $this->set('user', $users);
            return $this->render('add');
        }
        // トークンに紐づくユーザ情報を取得する
        $userEntity = $users->find('all')
            ->where(['registration_token' => $registrationToken])
            ->first();
        
        // リクエストと同じトークンが無い場合
        if(empty($userEntity)){
            $this->Flash->error(__('ユーザの登録に失敗しました。'));
            $this->set('user', $users);
            return $this->render('add');
        }

        // 24時間以上経っていた場合
        if(date('Y-m-d H:i:s') >= $userEntity->limit_time->format('Y-m-d H:i:s')){
            $this->Flash->error(__('ユーザの登録に失敗しました。URLの期限が過ぎています。'));
            $this->set('user', $users);
            return $this->render('add');
        }
        $userEntity->registration_flag = 1; // 登録(1)で設定

        // ユーザ本登録
        if ($users->save($userEntity)) {
            $this->Flash->success(__('ユーザが登録されました。'));
            
            // 登録したユーザ情報をセッションに書き込み
            $session = $this->request->session();
            $session->write('Auth.User', $userEntity);
            
            // 登録完了メールを送信する
            $this->sendMailUserFinish($userEntity->mail_address, $userEntity->name);
            
            // 仮登録ユーザで同じメールアドレスのユーザがいれば、削除しておく
            $checkMailUnregistrationUser = TableRegistry::get('Users')
            ->checkMailAdressExistUnregistration($userEntity->mail_address);
            if(! empty($checkMailUnregistrationUser)){
                TableRegistry::get('Users')->delete($checkMailUnregistrationUser);
            }
            return $this->render('add_finish');
            
        } else {
            $this->Flash->error(__('ユーザの登録に失敗しました。'));
        }
    }
    
    /**
     * ユーザ情報を設定するメソッド
     *
     * @access private
     * @return redirect 画面遷移先
     */
    private function setUpLogin($userName)
    {
        // 初期表示用のエンティティを作成
        $tempEntity = TableRegistry::get('Users')->newEntity();

        // 入力された名前に紐づくユーザが存在するかチェックする
        $user = TableRegistry::get('Users')->loginCheck($userName);

        // ユーザ存在確認フラグ
        $userExistFlg = false;

        // ユーザが存在する場合
        if (! empty($user)) {
            
            // 入力されたパスワードと一致するか確認
            $password = $this->request->data['password'];
            if ((new DefaultPasswordHasher())->check($password, $user->password)) {
                $userExistFlg = true;
            } else {
                $this->Flash->error(__('ユーザ名もしくは、パスワードが間違っています。'));
                $this->set('user', $tempEntity);
                return $this->render('login');
            }
            
            // ユーザのステータスが「1:登録」になっているか確認する
            if (strcmp($user->registration_flag, 1 )) {
                $this->Flash->error(__('このユーザは使用できません。'));
                $this->set('user', $tempEntity);
                return $this->render('login');
            }
        }

        // ユーザが存在する場合、セッションにユーザ情報を書き込む
        if ($userExistFlg) {
            $session = $this->request->session();
            $session->write('Auth.User.name', $user->name);
            $session->write('Auth.User.permission', $user->permission);
            $this->Flash->success(__('ログインしました。'));
            return $this->redirect([
                'controller' => 'Posts',
                'action' => 'index'
            ]);
        } else {
            $this->Flash->error(__('ユーザ名もしくは、パスワードが間違っています。'));
        }
    }

    /**
     * 自動ログイン情報を設定するメソッド
     * DBとCookieに自動ログイン情報を設定する
     *
     * @param string $userName ユーザ名
     * @access private
     */
    private function setupAutoLogin($userName)
    {
        // クッキーに格納する自動ログイン情報を設定する
        $cookieName = 'auto_login';
        $autoLoginKey = sha1(uniqid() . mt_rand(1, 999999999) . '_auto_login');
        $cookieExpire = time() + 3600 * 24 * 7; // 7日間
        $cookiePath = '/';
        $cookieDomain = $_SERVER['SERVER_NAME'];

        $this->Cookie->write('auto_login',$autoLoginKey);
        $this->Cookie->configKey('auto_login', [
            'expires' => $cookieExpire,
            'path' => $cookiePath,
            'domain' => $cookieDomain,
        ]);

        // DBに格納する自動ログイン情報を設定する
        $autoLoginInfo = [];
        $autoLoginInfo['user_name'] = $userName;
        $autoLoginInfo['auto_login_key'] = $this->Cookie->read('auto_login');
        
        // DBに情報を設定する
        $autoLogin = TableRegistry::get('auto_login_info');
        $autoLoginEntity = $autoLogin->newEntity();
        $autoLoginEntitys = $autoLogin->patchEntity(
            $autoLoginEntity, $autoLoginInfo);
        $autoLogin->save($autoLoginEntitys);
    }

    /**
     * 自動ログイン情報を確認するメソッド
     * CookieのログインキーとDBのログインキーが一致した場合
     * 自動ログイン設定をし直す
     * 
     * @access private
     * @return redirect 画面遷移先
     */
    private function checkAutoLogin()
    {
        $autoLoginKey = $this->Cookie->read('auto_login');
        
        // DBのログインキーと一致するか確認する
        $autoLogin = TableRegistry::get('auto_login_info');
        $autoLoginEntity = $autoLogin->find()
            ->where(['auto_login_key' => $autoLoginKey])
            ->first();
        
        // 自動ログインキーが一致した場合
        if (! empty($autoLoginEntity)) {
        
            // DBとCookieのオートログインキーを削除する
            if (! empty($autoLoginKey)) {
                $userName = $this->deleteAutoLoginInfo($autoLoginKey);
            }
            // 自動ログインキーを設定し直す
            $this->setupAutoLogin($userName);

            // ログイン情報をセッションに設定する
            $user = TableRegistry::get('Users')->loginCheck($userName);
            $session = $this->request->session();
            $session->write('Auth.User.name', $user->name);
            $session->write('Auth.User.permission', $user->permission);

            $this->Flash->success(__('自動ログインしました。'));
            return $this->redirect([
                'controller' => 'Posts',
                'action' => 'index'
            ]);
        }
    }

    /**
     * 自動ログイン情報を削除するメソッド
     *
     * @access private
     * @return string $userName ユーザ名
     */
    private function deleteAutoLoginInfo($auto_login_key)
    {
        // DBの自動ログイン情報を削除する
        $autoLogin = TableRegistry::get('auto_login_info');
        $autoLoginEntity = $autoLogin->find()
            ->where(['auto_login_key' => $auto_login_key])
            ->first();
        $userName = $autoLoginEntity->user_name;
        
        // 削除を実行する
        $autoLogin->delete($autoLoginEntity);
    
        // Cookieのオートログイン情報を削除する
        $this->Cookie->delete('auto_login');
    
        return $userName;
    }
    
    /**
     * ユーザ会員登録確認メール送信メソッド
     *
     * @access private
     * @param string $url 登録確認メールのURL
     * @param string $mailAddress メールアドレス
     * @param string $userName ユーザ名
     */
    private function sendMailUserConfirm($url, $mailAddress, $userName)
    {
        // 管理者ユーザの情報を取得する
        $adminUser = TableRegistry::get('Users')->find()
            ->where(['permission' => 'admin'])
            ->first();
        
        // 言語設定、内部エンコーディングを指定する
        mb_language("japanese");
        mb_internal_encoding("EUC-JP");
    
        // ユーザ登録確認メールの値を設定する
        $to = $mailAddress;
        $from = mb_encode_mimeheader(mb_convert_encoding($adminUser->name,
            "EUC-JP", "UTF-8")) . "<".$adminUser->mail_address.">";

        $subject = mb_convert_encoding('本登録を行ってください。', "EUC-JP", "UTF-8");

        $message = $userName.'さん'
            . PHP_EOL 
            . PHP_EOL .'会員登録をして頂き、ありがとうございます。'
            . PHP_EOL .'下記のURLにアクセスして本登録を完了させてください。'
            . PHP_EOL .'※まだ、会員登録は完了していません。'
            . PHP_EOL . $url;

        $body = mb_convert_encoding($message, "EUC-JP", "UTF-8");

        mb_send_mail($to, $subject, $body, "From:" . $from);// メールを送信する
        mb_internal_encoding("UTF-8");// 内部エンコーディングを元に戻す
    }
    
    /**
     * ユーザ会員登録完了メール送信メソッド
     *
     * @access private
     * @param string $mailAddress メールアドレス
     * @param string $userName ユーザ名
     */
    private function sendMailUserFinish($mailAddress, $userName)
    {
        // 管理者ユーザの情報を取得する
        $users = TableRegistry::get('Users');
        $adminUser = $users->find()
            ->where(['permission' => 'admin'])
            ->first();
        
        // 言語設定、内部エンコーディングを指定する
        mb_language("japanese");
        mb_internal_encoding("EUC-JP");

        // ユーザ登録完了メールの値を設定する
        $to = $mailAddress;
        $from = mb_encode_mimeheader(mb_convert_encoding($adminUser->name, 
            "EUC-JP", "UTF-8")) . "<".$adminUser->mail_address.">";
        
        $subject = mb_convert_encoding('本登録が完了しました。', "EUC-JP", "UTF-8");
        
        $message = $userName.'さん'
            . PHP_EOL 
            . PHP_EOL .'会員登録をして頂き、ありがとうございます。'
            . PHP_EOL .'本登録が完了しました。';
        
        $body = mb_convert_encoding($message, "EUC-JP", "UTF-8");
    
        mb_send_mail($to, $subject, $body, "From:" . $from);// メールを送信する
        mb_internal_encoding("UTF-8");// 内部エンコーディングを元に戻す
    }

    /**
     * ユーザ情報の重複チェックメソッド
     *
     * @access private
     * @param string $userName ユーザ名
     * @param string $mailAddress メールアドレス
     * @return 画面遷移先
     */
    private function checkDuplicateUser($userName, $mailAddress)
    {
        $users = TableRegistry::get('Users');
        $tempEntity = $users->newEntity();
        
        // DBに同じユーザ名のユーザが存在するか確認する
        $checkNameUser = TableRegistry::get('Users')->checkUserExist($userName);
        
        // 入力されたユーザ名と同じか確認
        if (! empty($checkNameUser)) {
            if (! strcmp($userName, $checkNameUser->name)) {
                $this->Flash->error(__('このユーザ名は既に使用されています。'));
                $this->set('user', $tempEntity);
                return $this->render('add');
            }
        }
        // DBに同じメールアドレスのユーザが存在するか確認する
        $checkMailUser = TableRegistry::get('Users')
            ->checkMailAdressExist($mailAddress);
        
        // 入力されたユーザ名と同じか確認
        if (! empty($checkMailUser)) {
            if (! strcmp($mailAddress, $checkMailUser->mail_address)) {
                $this->Flash->error(__('このメールアドレスは既に使用されています。'));
                $this->set('user', $tempEntity);
                return $this->render('add');
            }
        }
    }
}
