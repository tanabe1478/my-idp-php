<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * RefreshTokens Model
 *
 * @property \App\Model\Table\ClientsTable&\Cake\ORM\Association\BelongsTo $Clients
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 *
 * @method \App\Model\Entity\RefreshToken newEmptyEntity()
 * @method \App\Model\Entity\RefreshToken newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\RefreshToken> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\RefreshToken get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\RefreshToken findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\RefreshToken patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\RefreshToken> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\RefreshToken|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\RefreshToken saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\RefreshToken>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\RefreshToken>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\RefreshToken>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\RefreshToken> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\RefreshToken>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\RefreshToken>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\RefreshToken>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\RefreshToken> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class RefreshTokensTable extends Table
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

        $this->setTable('refresh_tokens');
        $this->setDisplayField('token');
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

        // Configure scopes field as JSON
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
            ->scalar('token')
            ->maxLength('token', 255)
            ->requirePresence('token', 'create')
            ->notEmptyString('token')
            ->add('token', 'unique', ['rule' => 'validateUnique', 'provider' => 'table']);

        $validator
            ->requirePresence('client_id', 'create')
            ->notEmptyString('client_id');

        $validator
            ->requirePresence('user_id', 'create')
            ->notEmptyString('user_id');

        $validator
            ->requirePresence('scopes', 'create')
            ->notEmptyArray('scopes');

        $validator
            ->dateTime('expires_at')
            ->requirePresence('expires_at', 'create')
            ->notEmptyDateTime('expires_at');

        $validator
            ->boolean('is_revoked')
            ->notEmptyString('is_revoked');

        return $validator;
    }

    /**
     * Find refresh token by token string
     *
     * @param string $token The token string
     * @return \App\Model\Entity\RefreshToken|null
     */
    public function findByToken(string $token): ?\App\Model\Entity\RefreshToken
    {
        return $this->find()
            ->where(['token' => $token])
            ->contain(['Clients', 'Users'])
            ->first();
    }

    /**
     * Clean up expired refresh tokens
     *
     * @return int Number of deleted tokens
     */
    public function cleanupExpired(): int
    {
        return $this->deleteAll(['expires_at <' => new \DateTime()]);
    }

    /**
     * Revoke a refresh token by token string
     *
     * @param string $token The token string
     * @return bool Success
     */
    public function revokeByToken(string $token): bool
    {
        $refreshToken = $this->findByToken($token);

        if (!$refreshToken) {
            return false;
        }

        $refreshToken->is_revoked = true;

        return (bool)$this->save($refreshToken);
    }
}
