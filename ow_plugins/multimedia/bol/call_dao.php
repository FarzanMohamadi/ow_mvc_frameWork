<?php
/**
 * Data Access Object for `call_call` table.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.call.bol
 * @since 1.0
 */
class MULTIMEDIA_BOL_CallDao extends OW_BaseDao
{

    /**
     * Constructor.
     *
     */
    protected function __construct()
    {
        parent::__construct();
    }
    /**
     * Singleton instance.
     *
     * @var MULTIMEDIA_BOL_CallDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return MULTIMEDIA_BOL_CallDao
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    /**
     * @see OW_BaseDao::getDtoClassName()
     *
     */
    public function getDtoClassName()
    {
        return 'MULTIMEDIA_BOL_Call';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'multimedia_call';
    }

    public function setAnswerToCall($callId, $candidate, $offer)
    {
        $query = 'UPDATE ' . $this->getTableName() . ' SET candidate=:c , offer=:o WHERE id=:id';

        $this->dbo->query($query, array(
            'id' => $callId,
            'c' => $candidate,
            'o' => $offer
        ));
    }
}