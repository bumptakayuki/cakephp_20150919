<?php
namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;
use Cake\ORM\TableRegistry;

/**
 * Postsのテーブルクラス
 * バリデーション定義などを行う。
 *
 * @access public
 * @author Takayuki_suzuki
 * @package Model
 */
class PostsTable extends Table
{
    /**
     * 初期化処理
     *
     * @access public
     * @param array $config
     */
    public function initialize(array $config)
    {
        // 多対多
        $this->belongsToMany('Tags', [
            'joinTable' => 'relations',
        ]);
        // 一対一
        $this->hasOne('Images', [
            'className' => 'Images',
            'foreignKey' => 'post_id',
        ]);
        // 多対一
        $this->belongsTo('Drafts', [
            'foreignKey' => 'draft_flag',
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

            ->notEmpty('title', 'タイトルを入力してください')
            ->add('title', [
            'maxLength' => [
                'rule' => [ 'maxLength', 30 ],
                'message' => 'タイトルは30文字以内で入力してください',
                'allowEmpty' => false
            ]
        ])
            ->notEmpty('body', '本文を入力してください')
            ->add('body', [
            'maxLength' => [
                'rule' => ['maxLength', 1000 ],
                'message' => '本文は1000文字以内で入力してください',
                'allowEmpty' => false
            ]
        ]);
        return $validator;
    }
    
    /**
     * 記事一覧ページに表示するレコードを取得するメソッド
     *
     * @access public
     * @param $offsetPageId レコード取得開始位置
     * @param $displayLimit ページング表示件数
     * @return $resultPosts 記事情報リスト
     */
    public function index($offsetPageId, $displayLimit)
    {
        // レコード取得処理
        $resultPosts = $this->find('all')
        ->where(['Posts.delete_flag' => 0])
        ->where(['Posts.draft_flag' => 1])
        ->where(['Posts.published_date <' => date('Y-m-d H:i:s')])
        ->limit($displayLimit)
        ->offset($offsetPageId)
        ->contain(['Tags'])
        ->contain(['Drafts'])
        ->order(['Posts.id' => 'DESC']);
    
        return $resultPosts;
    }
    
    /**
     * 下書き記事一覧ページに表示するレコードを取得するメソッド
     *
     * @access public
     * @param $offsetPageId レコード取得開始位置
     * @param $displayLimit ページング表示件数
     * @return $resultPosts 記事情報リスト
     */
    public function indexDraft($offsetPageId, $displayLimit)
    {
        // レコード取得処理
        $resultPosts = $this->find('all')
        ->where(['Posts.delete_flag' => 0])
        ->where(['Posts.draft_flag' => 0])
        ->limit($displayLimit)
        ->offset($offsetPageId)
        ->contain(['Tags'])
        ->contain(['Drafts'])
        ->order(['Posts.id' => 'DESC']);
    
        return $resultPosts;
    }
    
    /**
     * 予約記事一覧ページに表示するレコードを取得するメソッド
     *
     * @access public
     * @param $offsetPageId レコード取得開始位置
     * @param $displayLimit ページング表示件数
     * @return $resultPosts 記事情報リスト
     */
    public function indexReservations($offsetPageId, $displayLimit)
    {
        // レコード取得処理
        $resultPosts = $this->find('all')
        ->where(['Posts.delete_flag' => 0])
        ->where(['Posts.draft_flag' => 1])
        ->where(['Posts.published_date >' => date('Y-m-d H:i:s')])
        ->limit($displayLimit)
        ->offset($offsetPageId)
        ->contain(['Tags'])
        ->contain(['Drafts'])
        ->order(['Posts.id' => 'DESC']);
    
        return $resultPosts;
    }
    /**
     * IDに紐づく記事情報を取得するメソッド
     * (1レコード分のみを取得する)
     *
     * @access public
     * @param int $id ブログの記事ID
     * @return $postEntity 記事情報(1件分)
     */
    public function view($id)
    {
        $postEntity = $this->find()
        ->contain(['Tags'])
        ->contain(['Images'])
        ->where(['Posts.delete_flag' => 0])
        ->where(['Posts.id' => $id])
        ->first();
        
        return $postEntity;
    }
    
    /**
     * 検索メソッド
     * 検索条件に紐づく記事情報を取得する
     *
     * @access public
     * @param string $keyword 検索キーワード
     * @param array $tagIdList タグIDリスト
     * @param int $displayLimit レコード取得件数
     * @param int $offsetPageId レコード取得開始位置
     * @return $resultPosts 記事検索結果情報リスト
     */
    public function search($keyword,$tagIdList,$displayLimit,$offsetPageId)
    {
        // 記事検索結果情報リスト
        $resultPosts = [];
        
        // タグIDに紐づく記事を取得する
        $posts = $this->find('all')
        ->join([
            'table' => 'relations',
            'alias' => 'Relations',
            'type' => 'INNER',
            'conditions' => ['Posts.id = Relations.post_id']
        ])
        ->select(['id' => ' Posts.id'
            , 'title' => 'Posts.title'
            , 'body' => 'Posts.body'
            , 'author_name' => 'Posts.author_name'
            , 'created' => 'Posts.created'
            , 'modified' => 'Posts.modified'
            , 'published_date' => 'Posts.published_date'
            , 'draft_flag' => 'Posts.draft_flag'
            , 'post_id' => 'Relations.post_id'
            , 'tag_id' => 'Relations.tag_id'
        ])
        ->distinct(['Posts.id'])
        ->where(['Posts.delete_flag' => 0])
        ->where(['Posts.draft_flag' => 1])
        ->where(['Posts.published_date <' => date('Y-m-d H:i:s')])
        ->where(['Relations.tag_id IN' => $tagIdList])
        ->andwhere(['Posts.title like' => '%'.$keyword.'%'])
        ->andWhere(['Posts.body like' => '%'.$keyword.'%'])
        ->limit($displayLimit)
        ->offset($offsetPageId)
        ->order(['Posts.id' => 'DESC']);
        
        // 取得した記事の投稿IDのみを設定する
        $postIdList = [];
        foreach ($posts as $post) {
            array_push($postIdList, $post->post_id);
        }
        
        // 投稿IDに紐づくタグ情報を取得する
        $tags = TableRegistry::get('Tags');
        $tags = $tags->find('all')
        ->join([
            'table' => 'relations',
            'alias' => 'Relations',
            'type' => 'INNER',
            'conditions' => ['Tags.tag_id = Relations.tag_id']
        ])
        ->select(['tag_name' => 'Tags.tag_name'
            , 'post_id' => 'Relations.post_id'
            , 'tag_name' => 'Tags.tag_name'
            , 'tag_id' => 'Tags.tag_id'
        ])
        ->where([
            'Relations.post_id IN' => $postIdList
        ]);
        
        // 返却するオブジェクトを初期化
        $resultPost = new \stdClass();
        
        // タグ情報設定処理
        // 取得した記事の数だけ繰り返す
        foreach ($posts as $post) {
            $tagList = [];
            // 取得したタグ情報の数だけ繰り返す
            foreach ($tags as $tag) {
                // 該当するタグ情報があった場合
                if ($post->id == $tag->post_id) {
                    $tagList[$tag->tag_id]=$tag->tag_name;
                    $post->tags = $tagList;
                }
                $resultPost->post = $post;
            }
            // 1記事分を設定する
            array_push($resultPosts, $resultPost->post);
        }
        return $resultPosts;
    }
    
    /**
     * 選択されたタグを含む記事を返却するメソッド
     *
     * @access private
     * @param int $tagId タグID
     * @param int $displayLimit レコード取得件数
     * @param int $offsetPageId レコード取得開始位置
     * @return array $resultPosts 記事情報
     */
    public function getTagBlogList($tagId,$displayLimit,$offsetPageId)
    {
    
        // 初期表示用データの初期化
        $resultTags = [];
        $resultPosts = [];
    
        // タグIDに紐づく記事を取得する
        $posts = $this->find('all')
        ->join([
            'table' => 'relations',
            'alias' => 'Relations',
            'type' => 'INNER',
            'conditions' => ['Posts.id = Relations.post_id']
        ])
        ->select(['id' => ' Posts.id'
            , 'title' => 'Posts.title'
            , 'body' => 'Posts.body'
            , 'author_name' => 'Posts.author_name'
            , 'created' => 'Posts.created'
            , 'modified' => 'Posts.modified'
            , 'post_id' => 'Relations.post_id'
            , 'tag_id' => 'Relations.tag_id'
        ])
        ->distinct(['Posts.id'])
        ->limit($displayLimit)
        ->offset($offsetPageId)
        ->where(['Posts.delete_flag' => 0])
        ->where(['Posts.draft_flag' => 1])
        ->where(['Posts.published_date <' => date('Y-m-d H:i:s')])
        ->where(['Relations.tag_id IN' => $tagId])
        ->order(['Posts.id' => 'DESC']);
    
        // 取得した記事の投稿IDのみを設定する
        $postIdList = [];
        foreach ($posts as $post) {
            array_push($postIdList, $post->post_id);
        }
    
        // 投稿IDに紐づくタグ情報を取得する
        $tags = TableRegistry::get('Tags');
        $tags = $tags->find('all')
        ->join([
            'table' => 'relations',
            'alias' => 'Relations',
            'type' => 'INNER',
            'conditions' => ['Tags.tag_id = Relations.tag_id']
        ])
        ->select(['tag_name' => 'Tags.tag_name'
            , 'post_id' => 'Relations.post_id'
            , 'tag_name' => 'Tags.tag_name'
            , 'tag_id' => 'Tags.tag_id'
        ])
        ->where([
            'Relations.post_id IN' => $postIdList
        ]);
    
        // 返却するオブジェクトを初期化
        $resultPost = new \stdClass();
    
        // タグ情報設定処理
        // 取得した記事の数だけ繰り返す
        foreach ($posts as $post) {
            $tagList = [];
            // 取得したタグ情報の数だけ繰り返す
            foreach ($tags as $tag) {
                // 該当するタグ情報があった場合
                if ($post->id == $tag->post_id) {
                    $tagList[$tag->tag_id]=$tag->tag_name;
                    $post->tags = $tagList;
                }
                $resultPost->post = $post;
            }
            // 1記事分を設定する
            array_push($resultPosts, $resultPost->post);
        }
        return $resultPosts;
    }
    
    /**
     * 選択されたタグを含む記事の総数を取得するメソッド
     *
     * @access public
     * @param int $tagId タグID
     * @return int $total 記事の総数
     */
    public function getTagBlogListRowCount($tagId)
    {

        // タグIDに紐づく記事を取得する
        $total = $this->find('all')
        ->join([
            'table' => 'relations',
            'alias' => 'Relations',
            'type' => 'INNER',
            'conditions' => ['Posts.id = Relations.post_id']
        ])
        ->select(['id' => ' Posts.id'
            , 'title' => 'Posts.title'
            , 'body' => 'Posts.body'
            , 'author_name' => 'Posts.author_name'
            , 'created' => 'Posts.created'
            , 'modified' => 'Posts.modified'
            , 'post_id' => 'Relations.post_id'
            , 'tag_id' => 'Relations.tag_id'
        ])
        ->distinct(['Posts.id'])
        ->where(['Posts.delete_flag' => 0])
        ->where(['Posts.draft_flag' => 1])
        ->where(['Posts.published_date <' => date('Y-m-d H:i:s')])
        ->where(['Relations.tag_id IN' => $tagId])
        ->order(['Posts.id' => 'DESC'])
        ->count();
    
        return $total;
    }

    /**
     * 記事の総数を取得するメソッド
     *
     * @access public
     * @return int $total 記事の総数
     */
    public function getRowIndexCount()
    {
        $total = $this->find()
        ->where(['Posts.delete_flag' => 0])
        ->where(['Posts.draft_flag' => 1])
        ->where(['Posts.published_date <' => date('Y-m-d H:i:s')])
        ->count();
        return $total;
    }
    
    /**
     *  下書き記事の総数を取得するメソッド
     *
     * @access public
     * @return int $total 記事の総数
     */
    public function getRowIndexDraftCount()
    {
        $total = $this->find()
        ->where(['Posts.delete_flag' => 0])
        ->where(['Posts.draft_flag' => 0])
        ->count();
        return $total;
    }
    
    /**
     *  予約投稿記事の総数を取得するメソッド
     *
     * @access public
     * @return int $total 記事の総数
     */
    public function getRowIndexReservationsCount()
    {
        $total = $this->find()
        ->where(['Posts.delete_flag' => 0])
        ->where(['Posts.draft_flag' => 1])
        ->where(['Posts.published_date >' => date('Y-m-d H:i:s')])
        ->count();
        return $total;
    }
    
    /**
     * 検索された記事の総数を取得するメソッド
     *
     * @access public
     * @param array $tagIdList タグIDのリスト
     * @param string $keyword 検索キーワード
     * @return int $total 記事の総数
     */
    public function getSearchRowCount($tagIdList, $keyword)
    {
        $total = $this->find('all')
        ->join([
            'table' => 'relations',
            'alias' => 'Relations',
            'type' => 'INNER',
            'conditions' => ['Posts.id = Relations.post_id']
        ])
        ->select(['id' => ' Posts.id'
            , 'title' => 'Posts.title'
            , 'body' => 'Posts.body'
            , 'author_name' => 'Posts.author_name'
            , 'created' => 'Posts.created'
            , 'published_date' => 'Posts.published_date'
            , 'post_id' => 'Relations.post_id'
            , 'tag_id' => 'Relations.tag_id'
        ])
        ->distinct(['Posts.id'])
        ->where(['Posts.delete_flag' => 0])
        ->where(['Posts.draft_flag' => 1])
        ->where(['Posts.published_date <' => date('Y-m-d H:i:s')])
        ->where(['Relations.tag_id IN' => $tagIdList])
        ->andwhere(['Posts.title like' => '%'.$keyword.'%'])
        ->andWhere(['Posts.body like' => '%'.$keyword.'%'])
        ->order(['Posts.id' => 'DESC'])
        ->count();

        return $total;
    }
    
    /**
     * 記事の指定されたカラムに対して、ソートをかける。
     * 指定されたカラムに対してソートをかけた状態で返却する。
     *
     * @access public
     * @param $offsetPageId レコード取得開始位置
     * @param $displayLimit ページング表示件数
     * @param $sortTarget ソート対象
     * @param $orderSetting ソート設定
     * 
     * @return $resultPosts 記事情報リスト
     */
    public function sort($offsetPageId, $displayLimit, $sortTarget, $orderSetting)
    {
        // ソートの設定がされていない場合は、デフォルト値を設定する
        if(empty($sortTarget) && empty($orderSetting)){
            $sortTarget = 'id';
            $orderSetting = 'DESC';
        }
        
        // レコード取得処理
        $resultPosts = $this->find('all')
        ->where(['Posts.delete_flag' => 0])
        ->where(['Posts.draft_flag' => 1])
        ->where(['Posts.published_date <' => date('Y-m-d H:i:s')])
        ->limit($displayLimit)
        ->offset($offsetPageId)
        ->contain(['Tags'])
        ->contain(['Drafts'])
        ->order([$sortTarget => $orderSetting]);

        return $resultPosts;
    }
}