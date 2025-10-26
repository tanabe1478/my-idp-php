<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * AuthorizationCodes Model
 *
 * @property \App\Model\Table\ClientsTable&\Cake\ORM\Association\BelongsTo $Clients
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 *
 * @method \App\Model\Entity\AuthorizationCode newEmptyEntity()
 * @method \App\Model\Entity\AuthorizationCode newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\AuthorizationCode> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\AuthorizationCode get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\AuthorizationCode findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\AuthorizationCode patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\AuthorizationCode> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\AuthorizationCode|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\AuthorizationCode saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\AuthorizationCode>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\AuthorizationCode> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\AuthorizationCode>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\AuthorizationCode> deleteManyOrFail(iterable $entities, array $options = [])
 */
class AuthorizationCodesTable extends Table
{
    /**
     * Initialize method
     *
     * @param array<string, mixed> $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('authorization_codes');
        $this->setDisplayField('code');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Clients', [
            'foreignKey' => 'client_id',
            'joinType' => 'INNER',
        ]);

        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER',
        ]);

        // Configure JSON type for array columns
        $this->getSchema()->setColumnType('scopes', 'json');
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->uuid('id')
            ->allowEmptyString('id', null, 'create');

        $validator
            ->scalar('code')
            ->maxLength('code', 255)
            ->requirePresence('code', 'create')
            ->notEmptyString('code')
            ->add('code', 'unique', ['rule' => 'validateUnique', 'provider' => 'table']);

        $validator
            ->uuid('client_id')
            ->requirePresence('client_id', 'create')
            ->notEmptyString('client_id');

        $validator
            ->uuid('user_id')
            ->requirePresence('user_id', 'create')
            ->notEmptyString('user_id');

        $validator
            ->scalar('redirect_uri')
            ->maxLength('redirect_uri', 500)
            ->requirePresence('redirect_uri', 'create')
            ->notEmptyString('redirect_uri');

        $validator
            ->requirePresence('scopes', 'create')
            ->notEmptyArray('scopes');

        $validator
            ->dateTime('expires_at')
            ->requirePresence('expires_at', 'create')
            ->notEmptyDateTime('expires_at');

        $validator
            ->boolean('is_used')
            ->notEmptyString('is_used');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->isUnique(['code']), ['errorField' => 'code']);
        $rules->add($rules->existsIn(['client_id'], 'Clients'), ['errorField' => 'client_id']);
        $rules->add($rules->existsIn(['user_id'], 'Users'), ['errorField' => 'user_id']);

        return $rules;
    }

    /**
     * Find authorization code by code with client and user
     *
     * @param string $code Authorization code to search for
     * @return \App\Model\Entity\AuthorizationCode|null
     */
    public function findByCode(string $code): ?\App\Model\Entity\AuthorizationCode
    {
        return $this->find()
            ->where(['code' => $code])
            ->contain(['Clients', 'Users'])
            ->first();
    }

    /**
     * Clean up expired authorization codes
     *
     * @return int Number of deleted codes
     */
    public function cleanupExpired(): int
    {
        return $this->deleteAll(['expires_at <' => new \DateTime()]);
    }
}
