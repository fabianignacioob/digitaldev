<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class BackfillWhatsappFields extends BaseMigration
{
    public function change(): void
    {
        if ($this->getAdapter()->getAdapterType() !== 'postgres') {
            return;
        }

        $this->execute(
            "UPDATE sites
             SET whatsapp_country_code = '56',
                 whatsapp_number = CASE
                    WHEN regexp_replace(COALESCE(whatsapp, ''), '\\D', '', 'g') LIKE '56%'
                        THEN substring(regexp_replace(COALESCE(whatsapp, ''), '\\D', '', 'g') from 3)
                    ELSE regexp_replace(COALESCE(whatsapp, ''), '\\D', '', 'g')
                 END
             WHERE whatsapp IS NOT NULL
             AND whatsapp <> ''
             AND (whatsapp_number IS NULL OR whatsapp_number = '')"
        );
    }
}
