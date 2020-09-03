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

        $this->addMixedCondition($query, 'entity_id');
        $this->addMixedCondition($query, 'entity_type');
        $this->addMixedCondition($query, 'scope');
        $this->addMixedCondition($query, 'scope_id');

        // grid filtering conditions
        $query->andFilterWhere(['like', '_id', $this->_id])
            ->andFilterWhere(['like', 'entity_name', $this->entity_name])
            ->andFilterWhere(['in', 'allowed_actions', $this->allowed_actions])
            ->andFilterWhere(['like', 'conditions', $this->conditions])
            ->andFilterWhere(['in', 'fields', $this->fields]);

        return $dataProvider;
    }

    /**
     * Add a condition for a search parameter that may be
     * either numeric or a string.
     *
     * @param yii\db\Query $query
     * @param string $attribute
     */
    protected function addMixedCondition(&$query, string $attribute)
    {
        if (!empty($this->{$attribute})) {
            if (is_numeric($this->{$attribute})) {
                $query->andWhere([$attribute => (int)$this->{$attribute}]);
            } else {
                $query->andWhere(['like', $attribute, $this->{$attribute}]);
            }
        }
    }
}
