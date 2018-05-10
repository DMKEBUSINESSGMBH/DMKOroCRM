<?php
namespace DMKOroCRM\Bundle\ContactBundle\Migrations\Schema\v1_0;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\EntityExtendBundle\EntityConfig\ExtendScope;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\EntityBundle\EntityConfig\DatagridScope;

class AddBirthdayFlagToContact implements Migration
{

    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('orocrm_contact');
        if ($table->hasColumn('birthdaycal')) {
            return;
        }
        $table->addColumn('birthdaycal', 'integer', [
            'notnull' => false,
            'oro_options' => [
                'extend' => [
                    'is_extend' => true,
                    'owner' => ExtendScope::OWNER_SYSTEM
                ],
                'entity' => array(
                    'label' => 'Birthday flag'
                ),
                'datagrid' => array(
                    'is_visible' => DatagridScope::IS_VISIBLE_FALSE
                )
            ]
        ]);
    }
}
