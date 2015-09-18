<?php
namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Tagsのテーブルクラス
 * バリデーション定義などを行う。
 *
 * @access public
 * @author Takayuki_suzuki
 * @package Model
 */
class TagsTable extends Table
{
    /**
     * 初期化処理
     *
     * @access public
     * @param array $config
     */
    public function initialize(array $config)
    {
        $this->hasOne('Relations', [
            'foreignKey' => 'tag_id',
            'dependent' => true
        ]);
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
            ->notEmpty('tag_id', 'タグを選択してください')
            ->notEmpty('tag_name', 'タグ名を入力してください')
            ->add('tag_name', [
            'maxLength' => [
                'rule' => ['maxLength', 10 ],
                'message' => 'タグ名は10文字以内で入力してください',
                'allowEmpty' => false
            ]
        ]);
        return $validator;
    }
    
    /**
     * タグマスタ情報取得メソッド
     * タグのチェックボックスの情報を取得する際に使用する
     *
     * @access private
     * @return $reasultTags タグマスタ情報リスト
     */
    public function getTagList()
    {
        $reasultTags = $this->find('all')
        ->order(['tag_id' => 'ASC']);
    
        return $reasultTags;
    }
    
    /**
     * ページングするタグリストを取得するメソッド
     *
     * @access public
     * @param int $offsetPageId レコード取得開始位置
     * @param int $displayLimit レコード取得件数
     * @return $reasultTags タグ情報リスト
     */
    public function getPagingList($offsetPageId,$displayLimit)
    {
        $reasultTags = $this->find('all')
        ->order(['tag_id' => 'DESC'])
        ->limit($displayLimit)
        ->offset($offsetPageId);
        return $reasultTags;
    }
    
    /**
     * ページングするタグリストを取得するメソッド
     *
     * @access public
     * @return int $total タグの総数
     */
    public function getRowCount()
    {
        $total = $this->find()->count();
        return $total;
    }
    
    /**
     * タグの詳細情報を取得するメソッド
     * (レコード1件分)
     *
     * @access public
     * @param int $id タグID
     * @return $tagEntity タグ情報
     */
    public function view($id)
    {
        $tagEntity = $this->find()
        ->where(['tag_id' => $id])
        ->first();
    
        return $tagEntity;
    }
    
    /**
     * 削除対象のタグに関連付けされている
     * 記事がないかを検索するメソッド
     *
     * @access public
     * @param int $id タグID
     * @return $tagDeleteCheckEntitys 削除対象のタグ情報
     */
    public function checkTagDelete($id)
    {
        $tagDeleteCheckEntitys = $this->find()
        ->contain(['Relations'])
        ->where(['Relations.tag_id'=>$id])
        ->all();
    
        return $tagDeleteCheckEntitys;
    }
}
