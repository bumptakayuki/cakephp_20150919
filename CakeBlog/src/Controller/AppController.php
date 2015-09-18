<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link      http://cakephp.org CakePHP(tm) Project
 * @since     0.2.9
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace App\Controller;

use Cake\Controller\Controller;
use Cake\ORM\TableRegistry;
/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @link http://book.cakephp.org/3.0/en/controllers.html#the-app-controller
 */
class AppController extends Controller
{
    public static $checkFlg = false;
    
    /**
     * Initialization hook method.
     *
     * Use this method to add common initialization code like loading components.
     *
     * @return void
     */
    public function initialize()
    {
        parent::initialize();
        $this->loadComponent('Flash');
    }

    /**
     * 共通ログイン認証
     * 各アクション実行前にログインの認証を行う
     *
     * @return redirect 画面遷移先
     */
    public function loginCheck()
    {
        // セッションからユーザ情報を取得
        $user = $this->request->session()->read('Auth.User');

        // ユーザ情報が空の場合
        if (empty($user)) {
            $this->Flash->error(__('ログインしてください。'));
            
            // 初期表示用のデータを設定する
            $this->set('user', TableRegistry::get('Users')->newEntity());
            return $this->redirect([
                'controller' => 'Login',
                'action' => 'login']);
        }
    }
}
