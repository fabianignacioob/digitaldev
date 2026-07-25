<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Service\PlanService;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Utility\Text;
use Cake\Validation\Validator;

class SitesTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('sites');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');
        $this->addBehavior('Timestamp');

        $this->belongsTo('Users');
        $this->belongsTo('Templates');
        $this->belongsTo('Themes');
        $this->hasMany('Domains', ['dependent' => true]);
        $this->hasMany('SiteSections', ['dependent' => true, 'sort' => ['SiteSections.sort_order' => 'ASC']]);
        $this->hasMany('MediaAssets');
        $this->hasMany('Leads');
        $this->hasOne('CatalogSettings', ['dependent' => true]);
        $this->hasMany('CatalogCategories', ['dependent' => true, 'sort' => ['CatalogCategories.sort_order' => 'ASC']]);
        $this->hasMany('CatalogProducts', ['dependent' => true, 'sort' => ['CatalogProducts.sort_order' => 'ASC']]);
    }

    public function beforeMarshal(\Cake\Event\EventInterface $event, \ArrayObject $data, \ArrayObject $options): void
    {
        if (!empty($data['name']) && empty($data['slug'])) {
            $data['slug'] = Text::slug(strtolower((string)$data['name']));
        }
        if (!empty($data['subdomain'])) {
            $data['subdomain'] = Text::slug(strtolower((string)$data['subdomain']));
        }
        if (!empty($data['whatsapp_country_code'])) {
            $data['whatsapp_country_code'] = preg_replace('/\D+/', '', (string)$data['whatsapp_country_code']);
        }
        if (!empty($data['whatsapp_number'])) {
            $data['whatsapp_number'] = preg_replace('/\D+/', '', (string)$data['whatsapp_number']);
            $data['whatsapp'] = ($data['whatsapp_country_code'] ?? '56') . $data['whatsapp_number'];
        }
        if (!empty($data['instagram_username'])) {
            $instagram = trim((string)$data['instagram_username']);
            $instagram = preg_replace('~^https?://(www\.)?instagram\.com/~i', '', $instagram);
            $instagram = trim((string)$instagram, " \t\n\r\0\x0B/@");
            $data['instagram_username'] = $instagram;
            $data['instagram'] = $instagram ? 'https://instagram.com/' . $instagram : null;
        }
        foreach (['business_address', 'business_hours', 'public_phone', 'public_email'] as $field) {
            if ($data->offsetExists($field)) {
                $data[$field] = trim((string)$data[$field]) ?: null;
            }
        }
        if (!empty($data['public_email'])) {
            $data['public_email'] = strtolower((string)$data['public_email']);
        }
    }

    public function validationDefault(Validator $validator): Validator
    {
        return $validator
            ->integer('user_id')->notEmptyString('user_id')
            ->integer('template_id')->notEmptyString('template_id')
            ->integer('theme_id')->notEmptyString('theme_id')
            ->scalar('name')->maxLength('name', 140)->requirePresence('name', 'create')->notEmptyString('name')
            ->scalar('slug')->maxLength('slug', 140)->requirePresence('slug', 'create')->notEmptyString('slug')
            ->scalar('subdomain')->maxLength('subdomain', 80)->requirePresence('subdomain', 'create')->notEmptyString('subdomain')
            ->add('subdomain', 'format', [
                'rule' => ['custom', '/^[a-z0-9](?:[a-z0-9-]{1,61}[a-z0-9])$/'],
                'message' => 'Usa entre 3 y 63 caracteres, solo letras, números y guiones, sin guion al inicio o final.',
            ])
            ->add('subdomain', 'reserved', [
                'rule' => function ($value) {
                    return !(new PlanService())->isReservedSubdomain((string)$value);
                },
                'message' => 'Este subdominio está reservado.',
            ])
            ->scalar('status')->maxLength('status', 30)
            ->scalar('paused_reason')->maxLength('paused_reason', 80)->allowEmptyString('paused_reason')
            ->scalar('whatsapp_country_code')->maxLength('whatsapp_country_code', 8)->notEmptyString('whatsapp_country_code')
            ->scalar('whatsapp_number')->maxLength('whatsapp_number', 30)->allowEmptyString('whatsapp_number')
            ->scalar('whatsapp')->maxLength('whatsapp', 40)->allowEmptyString('whatsapp')
            ->scalar('instagram_username')->maxLength('instagram_username', 80)->allowEmptyString('instagram_username')
            ->scalar('instagram')->maxLength('instagram', 180)->allowEmptyString('instagram')
            ->scalar('business_address')->maxLength('business_address', 220)->allowEmptyString('business_address')
            ->scalar('business_hours')->maxLength('business_hours', 220)->allowEmptyString('business_hours')
            ->scalar('public_phone')->maxLength('public_phone', 60)->allowEmptyString('public_phone')
            ->email('public_email')->maxLength('public_email', 180)->allowEmptyString('public_email')
            ->scalar('seo_title')->maxLength('seo_title', 180)->allowEmptyString('seo_title')
            ->allowEmptyString('seo_description')
            ->dateTime('published_at')->allowEmptyDateTime('published_at');
    }

    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->existsIn(['user_id'], 'Users'), ['errorField' => 'user_id']);
        $rules->add($rules->existsIn(['template_id'], 'Templates'), ['errorField' => 'template_id']);
        $rules->add($rules->existsIn(['theme_id'], 'Themes'), ['errorField' => 'theme_id']);
        $rules->add($rules->isUnique(['slug']), ['errorField' => 'slug']);
        $rules->add($rules->isUnique(['subdomain']), ['errorField' => 'subdomain']);

        return $rules;
    }
}
