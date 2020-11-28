<?php

namespace App\Repository;

use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\Where;
use UnexpectedValueException;

class File
{
    private $db;

    /**
     *  Constructor
     */
    public function __construct(AdapterInterface $db)
    {
        $this->db = $db;
    }

    /**
     *  Get file by file ID
     */
    public function getFileByAlias(string $alias): array
    {
        $sql = new Sql($this->db);

        $select = $sql->select()
            ->from('uploads')
            ->limit(1);

        $extensionMarker = strrpos($alias, '.');

        if ($extensionMarker !== false) {
            $select->where(function (Where $where) use ($alias, $extensionMarker) {
                $where->equalTo('alias', substr($alias, 0, $extensionMarker));
                $where->equalTo('is_deleted', 0);
                $where->like('file_name', '%' . substr($alias, $extensionMarker));
            });
        } else {
            $select->where(function (Where $where) use ($alias) {
                $where->equalTo('alias', $alias);
                $where->equalTo('is_deleted', 0);
            });
        }

        $statement = $sql->prepareStatementForSqlObject($select);

        $file = $statement->execute()->current();

        if (empty($file)) {
            throw new UnexpectedValueException();
        }

        // might as well keep the same naming conventions
        $file['file_stream'] = fopen($file['file_location'], 'r');

        if (!is_resource($file['file_stream'])) {
            throw new UnexpectedValueException();
        }

        return $file;
    }
}
