<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;
use Cake\Auth\DefaultPasswordHasher;

/**
 * Usersのエンティティクラス
 *
 * @access public
 * @author Takayuki_suzuki
 * @package Model
 */
class User extends Entity
{

    protected $_accessible = [
        'id' => true,
        'name' => true,
        'password' => true,
        'permission' => true,
        'mail_address' => true
    ];

    /**
     * パスワードセッター passwordに値をセットするときハッシュ化する。
     * 
     * @param type $value            
     * @return type
     */
    protected function _setPassword($value)
    {
        return (new DefaultPasswordHasher())->hash($value);
    }
    
}