<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

class Site extends Entity
{
    protected array $_accessible = [
        'user_id' => true,
        'template_id' => true,
        'theme_id' => true,
        'name' => true,
        'slug' => true,
        'subdomain' => true,
        'status' => true,
        'paused_reason' => true,
        'published_at' => true,
        'logo_path' => true,
        'whatsapp_country_code' => true,
        'whatsapp_number' => true,
        'whatsapp' => true,
        'instagram_username' => true,
        'instagram' => true,
        'business_address' => true,
        'business_hours' => true,
        'public_phone' => true,
        'public_email' => true,
        'seo_title' => true,
        'seo_description' => true,
        'created' => true,
        'modified' => true,
        'user' => true,
        'template' => true,
        'theme' => true,
        'domains' => true,
        'site_sections' => true,
        'media_assets' => true,
        'leads' => true,
        'catalog_setting' => true,
        'catalog_categories' => true,
        'catalog_products' => true,
    ];
}
