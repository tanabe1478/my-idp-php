<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * SocialAccounts Model
 *
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 *
 * @method \App\Model\Entity\SocialAccount newEmptyEntity()
 * @method \App\Model\Entity\SocialAccount newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\SocialAccount> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\SocialAccount get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\SocialAccount findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\SocialAccount patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\SocialAccount> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\SocialAccount|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\SocialAccount saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\SocialAccount>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\SocialAccount>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\SocialAccount>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\SocialAccount> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\SocialAccount>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\SocialAccount>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\SocialAccount>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\SocialAccount> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class SocialAccountsTable extends Table
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

        $this->setTable('social_accounts');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER',
        ]);
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
            ->requirePresence('user_id', 'create')
            ->notEmptyString('user_id');

        $validator
            ->scalar('provider')
            ->maxLength('provider', 50)
            ->requirePresence('provider', 'create')
            ->notEmptyString('provider');

        $validator
            ->scalar('provider_user_id')
            ->maxLength('provider_user_id', 255)
            ->requirePresence('provider_user_id', 'create')
            ->notEmptyString('provider_user_id');

        $validator
            ->email('email')
            ->allowEmptyString('email');

        $validator
            ->scalar('name')
            ->maxLength('name', 255)
            ->allowEmptyString('name');

        $validator
            ->scalar('avatar_url')
            ->allowEmptyString('avatar_url');

        $validator
            ->scalar('access_token_encrypted')
            ->allowEmptyString('access_token_encrypted');

        $validator
            ->scalar('refresh_token_encrypted')
            ->allowEmptyString('refresh_token_encrypted');

        $validator
            ->dateTime('expires_at')
            ->allowEmptyDateTime('expires_at');

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
        $rules->add($rules->existsIn(['user_id'], 'Users'), ['errorField' => 'user_id']);
        $rules->add(
            $rules->isUnique(['provider', 'provider_user_id'], 'This social account is already linked.'),
            ['errorField' => 'provider_user_id']
        );

        return $rules;
    }

    /**
     * Find social account by provider and provider user ID
     *
     * @param \Cake\ORM\Query\SelectQuery $query Query
     * @param string $provider Provider name
     * @param string $providerUserId Provider user ID
     * @return \Cake\ORM\Query\SelectQuery
     */
    public function findByProviderUser(SelectQuery $query, string $provider, string $providerUserId): SelectQuery
    {
        return $query->where([
            'provider' => $provider,
            'provider_user_id' => $providerUserId,
        ]);
    }
}
