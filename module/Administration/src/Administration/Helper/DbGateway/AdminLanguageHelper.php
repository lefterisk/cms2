<?php
namespace Administration\Helper\DbGateway;


use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\TableGateway\Exception;


class AdminLanguageHelper
{
    protected $dbAdapter;
    protected $sql;
    protected $languages = array();

    public function __construct(Adapter $adapter)
    {
        $this->dbAdapter = $adapter;
        $this->sql       = new Sql($this->dbAdapter);
    }

    public function getLanguages()
    {
        if (count($this->languages) <= 0 ) {
            $statement    = $this->sql->select('admin_language')->where(array('status' => '1'))->order(array('is_primary DESC'));
            $selectString = $this->sql->getSqlStringForSqlObject($statement);
            $results      = $this->dbAdapter->query($selectString, Adapter::QUERY_MODE_EXECUTE);
            if (count($results) > 0) {
                foreach ($results as $language) {
                    $this->languages[$language['id']] = array(
                        'name'    => $language['name'],
                        'code'    => $language['code'],
                        'is_primary' => $language['is_primary']
                    );
                }
            } else {
                throw new Exception\InvalidArgumentException('Something is wrong with your site setup. No Admin Languages are detected!');
            }
        }
        return $this->languages;
    }

    public function getPrimaryLanguageId()
    {
        $languages = $this->getLanguages();
        foreach ($languages as $id => $definition) {
            if ($definition['is_primary']) {
                return $id;
            }
        }

        //if no primary has been set just return the first one
        reset($languages);
        $id = key($languages);
        return $id;
    }

    public function getDefaultAdminLocale()
    {
        foreach ($this->getLanguages() as $language) {
            if ($language['is_primary']) {
                return $language['code'];
            }
        }
        //if no default language is detected then throw exception
        throw new Exception\InvalidArgumentException('Something is wrong with your site setup. No Default Admin Language was detected!');
    }

    public function isValidAdminLocale($locale) {
        foreach ($this->getLanguages() as $language) {
            if ($language['code'] == $locale) {
                return true;
            }
        }
        return false;
    }
}