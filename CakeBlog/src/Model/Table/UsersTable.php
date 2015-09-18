<?php
namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Usersのテーブルクラス
 * バリデーション定義などを行う。
 *
 * @access public
 * @author Takayuki_suzuki
 * @package Model
 */
class UsersTable extends Table
{
    /**
     * 初期化処理
     *
     * @access public
     * @param array $config
     */
    public function initialize(array $config)
    {

    }
    
    /**
     * バリデーション
     *
     * @access public
     * @param Validator $validator バリデーター
     * @return Validator $validator バリデーター
     */
    public function validationDefault(Validator $validator)
    {
        $validator
        ->notEmpty('name', '名前を入力してください')
        ->add('name', [
            'maxLength' => [
                'rule' => [ 'maxLength', 20 ],
                'message' => '名前は20文字以内で入力してください',
                'allowEmpty' => false
            ]
        ])
        ->notEmpty('password', 'パスワードを入力してください')
        ->add('password', [
            'maxLength' => [
                'rule' => [ 'maxLength', 16 ],
                'message' => 'パスワードは16文字以内で入力してください',
                'allowEmpty' => false
            ]
        ])
        ->notEmpty('permission', '権限を入力してください')
        ->add('permission', 'validRole', [
            'rule' => 'isValidRole',
            'message' => __('権限の値が不正です'),
            'provider' => 'table',
        ])
        ->notEmpty('mail_address', '入力してください')
        ->add('mail_address', 'email', [
            'rule' => ['email'],
            'message' => '正しく入力してください',
            'last' => true,
        ]);
        
        return $validator;
    }
    
    public function isValidRole($value, array $context)
    {
        return in_array($value, ['admin', 'author'], true);
    }
    
    /**
     * ページングするユーザリストを取得するメソッド
     *
     * @access public
     * @param int $offsetPageId レコード取得開始位置
     * @param int $displayLimit レコード取得件数
     * @return $usersEntity ユーザ情報
     */
    public function getPagingList($offsetPageId, $displayLimit)
    {
        $usersEntity = $this->find('all')
        ->where(['delete_flag' => 0])
        ->limit($displayLimit)
        ->offset($offsetPageId)
        ->order(['Users.id' => 'DESC']);
    
        return $usersEntity;
    }
    
    /**
     * ユーザの総件数を取得する
     *
     * @access public
     * @return int $total ユーザの総数
     */
    public function getRowCount()
    {
        $total = $this->find('all')
        ->where(['delete_flag' => 0])
        ->count();
    
        return $total;
    }
    
    /**
     * ログイン確認を行うメソッド
     *
     * @access public
     * @param string $name ユーザ名
     * @return $usersEntity ユーザ情報
     */
    public function loginCheck($name)
    {
        $usersEntity = $this->find()
        ->where(['name' => $name])
        ->where(['delete_flag' => 0])
        ->limit(1)
        ->first();
    
        return $usersEntity;
    }
    
    /**
     * 同じユーザ名のユーザが存在するか確認するメソッド
     *
     * @access public
     * @param string $name ユーザ名
     * @return $userEntity ユーザ情報
     */
    public function checkUserExist($name)
    {
        $userEntity = $this->find()
        ->where(['name' => $name])
        ->where(['registration_flag' => 1])
        ->where(['delete_flag' => 0])
        ->limit(1)
        ->first();
    
        return $userEntity;
    }
    
    /**
     * 同じメールアドレスのユーザが存在するか確認するメソッド
     * (登録済み：1)のユーザのみを検索
     *
     * @access public
     * @param string $mailAddress メールアドレス
     * @return $userEntity ユーザ情報
     */
    public function checkMailAdressExist($mailAddress)
    {
        $userEntity = $this->find()
        ->where(['mail_address' => $mailAddress])
        ->where(['registration_flag' => 1])
        ->where(['delete_flag' => 0])
        ->limit(1)
        ->first();

        return $userEntity;
    }
    
    /**
     * 同じメールアドレスのユーザが存在するか確認するメソッド
     * (仮登録：0)のユーザのみを検索
     *
     * @access public
     * @param string $mailAddress メールアドレス
     * @return $userEntity ユーザ情報
     */
    public function checkMailAdressExistUnregistration($mailAddress)
    {
        $userEntity = $this->find()
        ->where(['mail_address' => $mailAddress])
        ->where(['registration_flag' => 0])
        ->where(['delete_flag' => 0])
        ->limit(1)
        ->first();
    
        return $userEntity;
    }
    
    /**
     * IDに紐づくユーザ情報を取得する
     * (1レコード分のみを取得する)
     *
     * @access public
     * @param int $id ユーザID
     * @return $usersEntity ユーザ情報(1件分)
     */
    public function view($id)
    {
        $usersEntity = $this->find()
        ->where(['delete_flag' => 0])
        ->where(['id' => $id])
        ->first();
    
        return $usersEntity;
    }
}
