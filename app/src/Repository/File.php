<?php

namespace App\Repository;

use App\Configuration\Configuration;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\Where;
use UnexpectedValueException;

class File
{
    private $characterList = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    private $config;
    private $db;

    /**
     *  Constructor
     */
    public function __construct(Configuration $config, AdapterInterface $db)
    {
        $this->config = $config;
        $this->db = $db;
    }

    /**
     *  Create file
     */
    public function createFile(array $data): array
    {
        $sql = new Sql($this->db);

        $data['alias'] = $this->createAlias();

        $insert = $sql->insert()
            ->into('uploads')
            ->values($data);

        $sql->prepareStatementForSqlObject($insert)->execute();

        return $this->enrich([
            'rowid' => $this->db->getDriver()->getLastGeneratedValue(),
        ] + $data);
    }

    /**
     *  Get file by file ID
     */
    public function getFileByAlias(string $alias): array
    {
        $sql = new Sql($this->db);

        $select = $sql->select()
            ->from('uploads')
            ->columns([
                'rowid',
                '*',
            ])
            ->limit(1);

        $extensionMarker = strrpos($alias, '.');

        if ($extensionMarker !== false) {
            $select->where(function (Where $where) use ($alias, $extensionMarker) {
                $where->equalTo('alias', substr($alias, 0, $extensionMarker));
                $where->equalTo('is_deleted', false);
                $where->like('file_name', '%' . substr($alias, $extensionMarker));
            });
        } else {
            $select->where(function (Where $where) use ($alias) {
                $where->equalTo('alias', $alias);
                $where->equalTo('is_deleted', false);
            });
        }

        $data = $sql->prepareStatementForSqlObject($select)->execute()->current();

        if (empty($data)) {
            throw new UnexpectedValueException();
        }

        return $this->enrich($data);
    }

    /**
     *  Get files belonging to a user
     */
    public function getFilesForUser(int $userId, int $limit = 10): iterable
    {
        $sql = new Sql($this->db);

        $select = $sql->select()
            ->from('uploads')
            ->columns([
                'rowid',
                '*',
            ])
            ->where([
                'users_id' => $userId,
                'is_deleted' => false,
            ])
            ->order('rowid DESC')
            ->limit($limit);

        $set = $sql->prepareStatementForSqlObject($select)->execute();

        foreach ($set as $data) {
            yield $this->enrich($data);
        }
    }

    /**
     *  Get file size for user
     */
    public function getFileSizeForUser(int $userId): int
    {
        $sql = new Sql($this->db);

        $select = $sql->select()
            ->from('uploads')
            ->columns([ 'file_size_sum' => new Expression('SUM(file_size)') ])
            ->where([
                'users_id' => $userId,
                'is_deleted' => false,
            ])
            ->limit(1);

        $data = $sql->prepareStatementForSqlObject($select)->execute()->current();

        if (!empty($data['file_size_sum'])) {
            return (int) $data['file_size_sum'];
        }

        return 0;
    }

    /**
     *  Get file size for user
     */
    public function isFileOwnedByUser(int $fileId, int $userId): int
    {
        $sql = new Sql($this->db);

        $select = $sql->select()
            ->from('uploads')
            ->columns([ 'rowid' ])
            ->where([
                'rowid' => $fileId,
                'users_id' => $userId,
                'is_deleted' => false,
            ])
            ->limit(1);

        $data = $sql->prepareStatementForSqlObject($select)->execute();

        return count($data) > 0;
    }

    /**
     *  Delete file
     */
    public function deleteFile(int $fileId)
    {
        $sql = new Sql($this->db);

        $update = $sql->update()
            ->table('uploads')
            ->set([ 'is_deleted' => true ])
            ->where([ 'rowid' => $fileId ]);

        $sql->prepareStatementForSqlObject($update)->execute();
    }

    /**
     *  Enrich things
     */
    private function enrich(array $data): array
    {
        $data['file_url'] = rtrim($this->config->get('files.domain'), '/') . '/' . $data['alias'];
        $data['file_path'] = rtrim($this->config->get('files.upload'), '/') . '/' . $data['file_location'];

        return $data;
    }

    /**
     *  Generate alias
     */
    private function createAlias(): string
    {
        $sql = new Sql($this->db);

        $dictionary = preg_split('//', $this->characterList, -1, PREG_SPLIT_NO_EMPTY);

        do {
            $index = 0;
            $length = $this->config->get('alias.length') ?: 4;

            $alias = '';

            while ($index != $length) {
                $alias .= $dictionary[mt_rand(0, count($dictionary) - 1)];
                $index++;
            }

            $select = $sql->select()
                ->from('uploads')
                ->columns([ 'rowid' ])
                ->where([ 'alias' => $alias ])
                ->limit(1);

            $result = $sql->prepareStatementForSqlObject($select)->execute();

            if (!count($result)) {
                return $alias;
            }
        } while(true);
    }
}
