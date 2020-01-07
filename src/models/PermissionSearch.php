<?php

namespace mipotech\yii2rest\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * PermissionSearch represents the model behind the search form of `app\models\permissions\Permission`.
 */
class PermissionSearch extends Permission
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['_id', 'entity_type', 'entity_name', 'entity_id', 'allowed_actions', 'scope', 'scope_id', 'conditions', 'fields'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Permission::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere(['like', '_id', $this->_id])
            ->andFilterWhere(['like', 'entity_type', $this->entity_type])
            ->andFilterWhere(['like', 'entity_name', $this->entity_name])
            ->andFilterWhere(['like', 'entity_id', $this->entity_id])
            ->andFilterWhere(['like', 'allowed_actions', $this->allowed_actions])
            ->andFilterWhere(['like', 'scope', $this->scope])
            ->andFilterWhere(['like', 'scope_id', $this->scope_id])
            ->andFilterWhere(['like', 'conditions', $this->conditions])
            ->andFilterWhere(['like', 'fields', $this->fields]);

        return $dataProvider;
    }
}
