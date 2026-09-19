<?php

namespace Pantono\Email\Repository;

use Pantono\Database\Repository\DefaultRepository;
use Pantono\Email\Model\EmailTemplateBlockType;
use Pantono\Email\Model\EmailTemplate;
use Pantono\Contracts\Locator\UserInterface;
use Pantono\Email\Model\EmailTemplateBlock;
use Pantono\Email\Filter\EmailTemplateFilter;
use Pantono\Email\Filter\EmailTemplateBlockFilter;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\ParameterType;

class EmailTemplatesRepository extends DefaultRepository
{
    public function getTemplateById(int $id): ?array
    {
        return $this->selectSingleRow('email_template', 'id', $id);
    }

    public function getBlockById(int $id): ?array
    {
        return $this->selectSingleRow('email_template_block', 'id', $id);
    }

    public function getBlockTypeById(int $id): ?array
    {
        return $this->selectSingleRow('email_template_block_type', 'id', $id);
    }

    public function getFieldsForBlockType(EmailTemplateBlockType $blockType): array
    {
        return $this->selectRowsByValues('email_template_block_field', ['block_type_id' => $blockType->getId()]);
    }

    public function addHistoryToTemplate(EmailTemplate $template, UserInterface $user, string $entry): void
    {
        $this->getDb()->insert('email_template_history', [
            'template_id' => $template->getId(),
            'user_id' => $user->getId(),
            'entry' => $entry
        ]);
    }

    public function addHistoryToBlock(EmailTemplateBlockType $block, UserInterface $user, string $entry): void
    {
        $this->getDb()->insert('email_template_block_history', [
            'block_id' => $block->getId(),
            'user_id' => $user->getId(),
            'entry' => $entry
        ]);
    }

    public function saveTemplate(EmailTemplate $emailTemplate): void
    {
        $id = $this->insertOrUpdateCheck('email_template', 'id', $emailTemplate->getId(), $emailTemplate->getAllData());
        if ($id) {
            $emailTemplate->setId($id);
        }
        $ids = [];
        foreach ($emailTemplate->getBlocks() as $block) {
            $block->setTemplateId($emailTemplate->getId());
            $this->saveTemplateBlock($block);
            $ids[] = $block->getId();
        }
        $qbDelete = $this->getDb()->createQueryBuilder()->delete('email_template_block')
            ->andWhere('template_id=:template_id')
            ->setParameter('template_id', $emailTemplate->getId());

        if (!empty($ids)) {
            $qbDelete->andWhere('id not in (:ids)')
                ->setParameter('ids', $ids, ArrayParameterType::INTEGER);
        }
        $qbDelete->executeQuery();
    }

    public function saveTemplateBlock(EmailTemplateBlock $block): void
    {
        $id = $this->insertOrUpdateCheck('email_template_block', 'id', $block->getId(), $block->getAllData());
        if ($id) {
            $block->setId($id);
        }
    }

    public function saveBlockType(EmailTemplateBlockType $type): void
    {
        $id = $this->insertOrUpdateCheck('email_template_block_type', 'id', $type->getId(), $type->getAllData());
        if ($id) {
            $type->setId($id);
        }
        foreach ($type->getFields() as $field) {
            $field->setBlockTypeId($type->getId());
            $fieldId = $this->insertOrUpdateCheck('email_template_block_field', 'id', $field->getId(), $field->getAllData());
            if ($fieldId) {
                $field->setId($fieldId);
            }
        }
    }

    public function getBlocksForTemplate(EmailTemplate $template): array
    {
        return $this->selectRowsByValues('email_template_block', ['template_id' => $template->getId()], 'display_order');
    }

    public function getTemplateForType(string $typeName): ?array
    {
        $select = $this->getDb()->select('t.*')->from('email_template', 't')
            ->innerJoin('t', 'email_mapping', 'm', 'm.template_id=t.id')
            ->andWhere('m.type_name=:type_name')
            ->setParameter('type_name', $typeName);

        return $this->getDb()->fetchRow($select);
    }

    public function saveMappingForType(string $type, EmailTemplate $template): void
    {
        $this->getDb()->delete('email_mapping', ['type_name' => $type]);
        $this->getDb()->insert('email_mapping', ['type_name' => $type, 'template_id' => $template->getId()]);
    }

    public function getEmailTemplatesByFilter(EmailTemplateFilter $filter): array
    {
        $select = $this->getDb()->select('et.*')->from('email_template', 'et')
            ->andWhere('et.deleted=:deleted')
            ->setParameter('deleted', false, ParameterType::BOOLEAN);

        if ($filter->getSearch() !== null) {
            $select->andWhere('et.name like :search or et.description like :search')
                ->setParameter('search', '%' . $filter->getSearch() . '%');
        }

        if ($filter->getCategory() !== null) {
            $select->andWhere('category=:category')
                ->setParameter('category', $filter->getCategory());
        }
        $this->applySort($select, $filter);
        $this->applyCountAndLimit($select, $filter);

        return $this->getDb()->fetchAll($select);
    }

    public function getEmailTemplateBlockTypesByFilter(EmailTemplateBlockFilter $filter): array
    {
        $select = $this->getDb()->select('bt.*')->from('email_template_block_type', 'bt')
            ->andWhere('bt.deleted=:deleted')
            ->setParameter('deleted', false, ParameterType::BOOLEAN);

        if ($filter->getSearch() !== null) {
            $select->andWhere('(bt.name like :search or bt.description like :search)')
                ->setParameter(':search', '%' . $filter->getSearch() . '%');
        }

        if ($filter->getCategory() !== null) {
            $select->andWhere('category=:category')
                ->setParameter(':category', $filter->getCategory());
        }

        if ($filter->getContentSearch() !== null) {
            $select->andWhere('template like :content_search')
                ->setParameter(':content_search', '%' . $filter->getContentSearch() . '%');
        }

        $this->applyCountAndLimit($select, $filter);

        return $this->getDb()->fetchAll($select);
    }

    public function getAllMappings(): array
    {
        return $this->selectAll('email_mapping');
    }

    public function getAllEmailTemplateTypes(): array
    {
        return $this->selectAll('email_template_type');
    }

    public function getActiveEmailTemplateTypes(): array
    {
        return $this->selectRowsByValues('email_template_type', ['enabled' => 1]);
    }
}
