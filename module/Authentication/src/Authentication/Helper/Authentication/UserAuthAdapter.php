<?php

namespace Authentication\Helper\Authentication;

use Zend\Authentication\Adapter\AdapterInterface;
use Zend\Authentication\Adapter\DbTable\AbstractAdapter;
use Zend\Authentication\Result;
use Zend\Crypt\Password\Bcrypt;

class UserAuthAdapter extends AbstractAdapter implements AdapterInterface
{

    /**
     * _authenticateValidateResult() - This method attempts to validate that
     * the record in the resultset is indeed a record that matched the
     * identity provided to this adapter.
     *
     * @param  array $resultIdentity
     * @return AuthenticationResult
     */
    protected function authenticateValidateResult($resultIdentity)
    {
        if ($this->verifyPassword($this->getCredential(), $resultIdentity[$this->credentialColumn])) {
            unset($resultIdentity[$this->credentialColumn]);
            return new Result(Result::SUCCESS, $resultIdentity, array( $this->getIdentity() . ' authentication successful'));
        } else {
            return new Result(Result::FAILURE_CREDENTIAL_INVALID, $this->getIdentity(), array( $this->getIdentity() . ' invalid credentials'));
        }
    }

    /**
     * _authenticateCreateSelect() - This method creates a Zend\Db\Sql\Select object that
     * is completely configured to be queried against the database.
     *
     * @return Sql\Select
     */
    protected function authenticateCreateSelect()
    {
        return $this->getDbSelect()->from($this->tableName)->where(array($this->identityColumn => $this->getIdentity(), 'status' => '1'));
    }

    private function verifyPassword($suppliedPassword, $storedPassword)
    {
        $bcrypt = new Bcrypt();
        return $bcrypt->verify($suppliedPassword, $storedPassword);
    }
}