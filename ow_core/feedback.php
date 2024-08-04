<?php
/**
 * The class works with default feedback system.
 * 
 * @package ow_core
 * @method static OW_Feedback getInstance()
 * @since 1.0
 */
class OW_Feedback
{
    /* feedback messages types */
    const TYPE_ERROR = 'error';
    const TYPE_INFO = 'info';
    const TYPE_WARNING = 'warning';

    use OW_Singleton;
    
    /**
     * @var array
     */
    private $feedback;

    /**
     * Constructor.
     */
    private function __construct()
    {
        $session = OW::getSession();

        if ( $session->isKeySet('ow_messages') )
        {
            $this->feedback = $session->get('ow_messages');
            $session->delete('ow_messages');
        }
        else
        {
            $this->feedback = array(
                'error' => array(),
                'info' => array(),
                'warning' => array()
            );
        }
    }

    /**
     * Adds message to feedback.
     *
     * @param string $message
     * @param string $type
     * @return OW_Feedback
     */
    private function addMessage( $message, $type = 'info' )
    {
        if ( $type !== self::TYPE_ERROR && $type !== self::TYPE_INFO && $type !== self::TYPE_WARNING )
        {
            throw new InvalidArgumentException('Invalid message type `' . $type . '`!');
        }

        $this->feedback[$type][] = $message;

        return $this;
    }

    /**
     * Adds error message to feedback.
     *
     * @param string $message
     */
    public function error( $message )
    {
        $this->addMessage($message, self::TYPE_ERROR);
    }

    /**
     * Adds info message to feedback.
     *
     * @param string $message
     */
    public function info( $message )
    {
        $this->addMessage($message, self::TYPE_INFO);
    }

    /**
     * Adds warning message to feedback.
     *
     * @param string $message
     */
    public function warning( $message )
    {
        $this->addMessage($message, self::TYPE_WARNING);
    }

    /**
     * Returns whole list of registered messages.
     *
     * @return array
     */
    public function getFeedback()
    {
        $feedback = $this->feedback;

        $this->feedback = null;

        return $feedback;
    }

    /**
     * System method. Don't call it.
     */
    public function __destruct()
    {
        if ( $this->feedback !== null )
        {
            OW::getSession()->set('ow_messages', $this->feedback);
        }
    }
}

