<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Service\DomainAdministrationService;
use Cake\Event\EventInterface;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

class DomainsTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('domains');
        $this->setDisplayField('domain');
        $this->setPrimaryKey('id');
        $this->addBehavior('Timestamp');
        $this->belongsTo('Sites');
    }

    public function beforeMarshal(EventInterface $event, \ArrayObject $data, \ArrayObject $options): void
    {
        if (isset($data['domain'])) {
            $data['domain'] = DomainAdministrationService::normalizeHostname((string)$data['domain']);
        }
    }

    public function validationDefault(Validator $validator): Validator
    {
        return $validator
            ->integer('site_id')->notEmptyString('site_id')
            ->scalar('domain')->maxLength('domain', 180)->requirePresence('domain', 'create')->notEmptyString('domain')
            ->add('domain', 'hostname', [
                'rule' => static fn ($value) => DomainAdministrationService::isValidHostname((string)$value),
                'message' => 'Ingresa un hostname válido, sin protocolo ni rutas.',
            ])
            ->scalar('type')->maxLength('type', 30)
            ->boolean('verified')
            ->boolean('active')
            ->scalar('verification_token')->maxLength('verification_token', 120)->allowEmptyString('verification_token')
            ->scalar('verification_method')->maxLength('verification_method', 30)->allowEmptyString('verification_method')
            ->scalar('last_dns_error')->maxLength('last_dns_error', 500)->allowEmptyString('last_dns_error');
    }

    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->existsIn(['site_id'], 'Sites'), ['errorField' => 'site_id']);
        $rules->add($rules->isUnique(['domain']), ['errorField' => 'domain']);

        return $rules;
    }
}
