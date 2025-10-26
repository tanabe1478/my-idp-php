<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Scopes Model
 *
 * @property \App\Model\Table\ClientsTable&\Cake\ORM\Association\BelongsToMany $Clients
 *
 * @method \App\Model\Entity\Scope newEmptyEntity()
 * @method \App\Model\Entity\Scope newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\Scope> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Scope get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Scope findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\Scope patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\Scope> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Scope|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Scope saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 */
class ScopesTable extends Table
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

        $this->setTable('scopes');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsToMany('Clients', [
            'foreignKey' => 'scope_id',
            'targetForeignKey' => 'client_id',
            'joinTable' => 'clients_scopes',
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
            ->uuid('id')
            ->allowEmptyString('id', null, 'create');

        $validator
            ->scalar('name')
            ->maxLength('name', 255)
            ->requirePresence('name', 'create')
            ->notEmptyString('name');

        $validator
            ->scalar('description')
            ->allowEmptyString('description');

        $validator
            ->boolean('is_active')
            ->notEmptyString('is_active');

        return $validator;
    }
}
