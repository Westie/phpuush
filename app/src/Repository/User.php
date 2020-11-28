<?php

namespace App\Repository;

use App\Configuration\Configuration;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\Where;
use UnexpectedValueException;

class User
{
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
     *  Create user
     */
    public function createUser(array $data): array
    {
        $sql = new Sql($this->db);

        if (array_key_exists('password', $data)) {
            $data['password'] = sha1($data['password']);
        }

        $data['api_key'] = sha1(random_bytes(32));

        $insert = $sql->insert()
            ->into('users')
            ->values($data);

        $sql->prepareStatementForSqlObject($insert)->execute();

        return $this->enrich($data);
    }

    /**
     *  Get user by key
     */
    public function getUserByKey(string $key): array
    {
        $sql = new Sql($this->db);

        $select = $sql->select()
            ->from('users')
            ->columns([
                'rowid',
                '*',
            ])
            ->where([
                'api_key' => $key,
                'is_deleted' => false,
            ])
            ->limit(1);

        $data = $sql->prepareStatementForSqlObject($select)->execute()->current();

        if (empty($data)) {
            throw new UnexpectedValueException();
        }

        return $this->enrich($data);
    }

    /**
     *  Enrich things
     */
    private function enrich(array $data): array
    {
        $data['storage_folder'] = $data['email_address'];
        $data['storage_path'] = rtrim($this->config->get('files.upload'), '/') . '/' . $data['storage_folder'];

        return $data;
    }
}
