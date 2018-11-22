<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DoctrineSchemaDocCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('doctrine:schema:doc')
            ->setDescription('生成数据库schema文档。')
            ->addArgument('argument', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $doctrine = $this->getContainer()->get("doctrine");
        foreach ($doctrine->getManagers() as $name => $em) {
            $this->currentConnName = $name;
            $this->currentEm = $em;
            $metadatas = $em->getMetadataFactory()->getAllMetadata();
            $db = [];
            foreach ($metadatas as $metadata) {
                $this->buildTable($db, $metadata);
            }
            $this->databases["database"][] = [
                "name" => $name,
                "content" => $db
            ];
        }

        $rootDir = $this->getContainer()->getParameter('kernel.root_dir');
        $handle = fopen("$rootDir/../doc/db/data.js", "w+");
        fwrite($handle, "var data = ");
        fwrite($handle, json_encode($this->databases));
        fwrite($handle, ";");
        fclose($handle);
    }

    protected function buildTable(&$table, $metadata)
    {
        $reflect = $metadata->getReflectionClass();
        $db = [
            "name" => $metadata->table["name"],
            "table" => [
                "thead" => ["列名","类型","长度","唯一","可空","备注"],
                "tbody" => [],
                "comment" => $this->getComment(@$metadata->table["options"]),
                "indexes" => []
            ]
        ];
        $this->buildIndexes($db["table"]["indexes"], $metadata->table);
        foreach (array_merge($metadata->fieldMappings, $metadata->associationMappings) as $mapping) {
            $this->buildColumn($db["table"]["tbody"], $mapping, $metadata->getReflectionProperty($mapping["fieldName"]));
        }
        $table[] = $db;
    }

    protected function buildIndexes(&$indexes, $table)
    {
        if (!isset($table["indexes"])) {
            return;
        }
        foreach ($table["indexes"] as $key => $value) {
            $index = [];
            $index["name"] = $key;
            $index["columns"] = $value["columns"];
            $indexes[] = $index;
            unset($index);
        }
    }

    protected function buildColumn(&$columns, $mapping, $reflect)
    {
        $columnName = $this->getColumnName($mapping);
        if (empty($columnName)) {
            return;
        }
        $column[] = $columnName;
        $column[] = is_numeric($mapping["type"]) ? "integer" : $mapping["type"];
        $column[] = $this->getColumnProperty($mapping, "length");
        $column[] = $this->getColumnProperty($mapping, "unique");
        $column[] = $this->getColumnProperty($mapping, "nullable");
        $column[] = $this->getComment(@$mapping["options"]);
        $columns[] = $column;
    }

    protected function getColumnName($mapping)
    {
        if (isset($mapping["columnName"])) {
            return $mapping["columnName"];
        }
        if (isset($mapping["joinColumnFieldNames"])) {
            $columnName = array_values($mapping["joinColumnFieldNames"])[0];
            $targetMetadata = $this->currentEm->getMetadataFactory()->getMetadataFor($mapping["targetEntity"]);
            return "$columnName#{$this->currentConnName}.{$targetMetadata->table["name"]}";
        }
        return null;
    }

    protected function getColumnProperty($mapping, $key)
    {
        if (isset($mapping[$key])) {
            return (string)$mapping[$key] ?: "";
        }
        return "";
    }

    protected function getComment($options)
    {
        if (empty($options)) {
            return "";
        }
        return $options["comment"];
    }
}
