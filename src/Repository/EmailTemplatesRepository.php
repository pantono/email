<?php

namespace Pantono\Email\Repository;

use Pantono\Database\Repository\MysqlRepository;
use Pantono\Email\Model\EmailTemplateBlockType;
use Pantono\Email\Model\EmailTemplate;
use Pantono\Contracts\Locator\UserInterface;
use Pantono\Email\Model\EmailTemplateBlock;

class EmailTemplatesRepository extends MysqlRepository
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
        $this->getDb()->insert('email_template_blocK_history', [
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
        foreach ($emailTemplate->getBlocks() as $block) {
            $block->setTemplateId($emailTemplate->getId());
            $this->saveTemplateBlock($block);
        }
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
}
