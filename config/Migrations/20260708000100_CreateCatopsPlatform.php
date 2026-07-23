<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class CreateCatopsPlatform extends BaseMigration
{
    public function change(): void
    {
        $this->table('users')
            ->addColumn('name', 'string', ['limit' => 120])
            ->addColumn('email', 'string', ['limit' => 180])
            ->addColumn('password', 'string', ['limit' => 255])
            ->addColumn('role', 'string', ['default' => 'customer', 'limit' => 30])
            ->addColumn('active', 'boolean', ['default' => true])
            ->addTimestamps('created', 'modified')
            ->addIndex(['email'], ['unique' => true])
            ->create();

        $this->table('templates')
            ->addColumn('name', 'string', ['limit' => 120])
            ->addColumn('slug', 'string', ['limit' => 120])
            ->addColumn('description', 'text', ['null' => true])
            ->addColumn('preview_image', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('active', 'boolean', ['default' => true])
            ->addTimestamps('created', 'modified')
            ->addIndex(['slug'], ['unique' => true])
            ->create();

        $this->table('themes')
            ->addColumn('name', 'string', ['limit' => 120])
            ->addColumn('slug', 'string', ['limit' => 120])
            ->addColumn('primary_color', 'string', ['default' => '#f36b16', 'limit' => 20])
            ->addColumn('secondary_color', 'string', ['default' => '#0a2a66', 'limit' => 20])
            ->addColumn('background_color', 'string', ['default' => '#fbfaf7', 'limit' => 20])
            ->addColumn('font_family', 'string', ['default' => 'Inter, Arial, sans-serif', 'limit' => 120])
            ->addColumn('active', 'boolean', ['default' => true])
            ->addTimestamps('created', 'modified')
            ->addIndex(['slug'], ['unique' => true])
            ->create();

        $this->table('sites')
            ->addColumn('user_id', 'integer')
            ->addColumn('template_id', 'integer')
            ->addColumn('theme_id', 'integer')
            ->addColumn('name', 'string', ['limit' => 140])
            ->addColumn('slug', 'string', ['limit' => 140])
            ->addColumn('subdomain', 'string', ['limit' => 80])
            ->addColumn('status', 'string', ['default' => 'draft', 'limit' => 30])
            ->addColumn('logo_path', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('whatsapp', 'string', ['limit' => 40, 'null' => true])
            ->addColumn('instagram', 'string', ['limit' => 180, 'null' => true])
            ->addColumn('seo_title', 'string', ['limit' => 180, 'null' => true])
            ->addColumn('seo_description', 'text', ['null' => true])
            ->addTimestamps('created', 'modified')
            ->addIndex(['slug'], ['unique' => true])
            ->addIndex(['subdomain'], ['unique' => true])
            ->addForeignKey('user_id', 'users', 'id', ['delete' => 'CASCADE'])
            ->addForeignKey('template_id', 'templates', 'id')
            ->addForeignKey('theme_id', 'themes', 'id')
            ->create();

        $this->table('domains')
            ->addColumn('site_id', 'integer')
            ->addColumn('domain', 'string', ['limit' => 180])
            ->addColumn('type', 'string', ['default' => 'subdomain', 'limit' => 30])
            ->addColumn('verified', 'boolean', ['default' => false])
            ->addColumn('active', 'boolean', ['default' => true])
            ->addTimestamps('created', 'modified')
            ->addIndex(['domain'], ['unique' => true])
            ->addForeignKey('site_id', 'sites', 'id', ['delete' => 'CASCADE'])
            ->create();

        $this->table('site_sections')
            ->addColumn('site_id', 'integer')
            ->addColumn('section_key', 'string', ['limit' => 80])
            ->addColumn('title', 'string', ['limit' => 180, 'null' => true])
            ->addColumn('subtitle', 'string', ['limit' => 220, 'null' => true])
            ->addColumn('content', 'text', ['null' => true])
            ->addColumn('image_path', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('sort_order', 'integer', ['default' => 0])
            ->addColumn('visible', 'boolean', ['default' => true])
            ->addColumn('settings', 'json', ['default' => '{}'])
            ->addTimestamps('created', 'modified')
            ->addIndex(['site_id', 'section_key'])
            ->addForeignKey('site_id', 'sites', 'id', ['delete' => 'CASCADE'])
            ->create();

        $this->table('media_assets')
            ->addColumn('user_id', 'integer')
            ->addColumn('site_id', 'integer', ['null' => true])
            ->addColumn('type', 'string', ['limit' => 40])
            ->addColumn('path', 'string', ['limit' => 255])
            ->addColumn('original_name', 'string', ['limit' => 255])
            ->addColumn('mime_type', 'string', ['limit' => 120, 'null' => true])
            ->addColumn('size', 'integer', ['null' => true])
            ->addTimestamps('created', 'modified')
            ->addForeignKey('user_id', 'users', 'id', ['delete' => 'CASCADE'])
            ->addForeignKey('site_id', 'sites', 'id', ['delete' => 'CASCADE'])
            ->create();

        $this->table('leads')
            ->addColumn('site_id', 'integer')
            ->addColumn('name', 'string', ['limit' => 120])
            ->addColumn('email', 'string', ['limit' => 180, 'null' => true])
            ->addColumn('phone', 'string', ['limit' => 40, 'null' => true])
            ->addColumn('message', 'text', ['null' => true])
            ->addColumn('source', 'string', ['default' => 'site', 'limit' => 80])
            ->addColumn('created', 'datetime')
            ->addForeignKey('site_id', 'sites', 'id', ['delete' => 'CASCADE'])
            ->create();

        $this->table('audit_logs')
            ->addColumn('user_id', 'integer', ['null' => true])
            ->addColumn('action', 'string', ['limit' => 120])
            ->addColumn('entity', 'string', ['limit' => 120, 'null' => true])
            ->addColumn('entity_id', 'integer', ['null' => true])
            ->addColumn('data', 'json', ['default' => '{}'])
            ->addColumn('created', 'datetime')
            ->addForeignKey('user_id', 'users', 'id', ['delete' => 'SET_NULL'])
            ->create();
    }
}
