<?php
/**
 * Base validator class.
 *
 * @package ow_core
 * @since 1.0
 */
abstract class OW_Validator
{
    /**
     * Error message.
     *
     * @var string
     */
    protected $errorMessage;

    /**
     * Checks if provided value is valid.
     *
     * @param mixed $value
     * @return boolean
     */
    abstract function isValid( $value );

    /**
     * Returns validator error message.
     *
     * @return string
     */
    public function getError()
    {
        return $this->errorMessage;
    }

    /**
     * Sets validator error message.
     *
     * @param string $errorMessage
     * @throws InvalidArgumentException
     */
    public function setErrorMessage( $errorMessage )
    {
        if ( $errorMessage === null || mb_strlen(trim($errorMessage)) === 0 )
        {
            //throw new InvalidArgumentException('Invalid error message!');
            return;
        }

        $this->errorMessage = trim($errorMessage);
    }

    /**
     * Returns validator js object code.
     *
     * @return string
     */
    public function getJsValidator()
    {
        return "{
            validate : function( value ){}
        }";
    }

    /**
     * @see OW_Validator::isValid()
     *
     * @param mixed $value
     */
    protected function isValid1( $value )
    {
        // doesn't check empty values
        if ( (is_array($value) && sizeof($value) === 0) || $value === null || mb_strlen(trim($value)) === 0 )
        {
            return true;
        }

        if ( is_array($value) )
        {
            foreach ( $value as $val )
            {
                if ( !$this->checkValue($val) )
                {
                    return false;
                }
            }
            return true;
        }
        else
        {
            return $this->checkValue($value);
        }
    }

}

/**
 * Required validator.
 *
 * @package ow_core
 * @since 1.0
 */
class RequiredValidator extends OW_Validator
{
    /**
     * Constructor.
     *
     * @param array $params
     */
    public function __construct()
    {
        $errorMessage = OW::getLanguage()->text('base', 'form_validator_required_error_message');

        if ( empty($errorMessage) )
        {
            $errorMessage = 'Required Validator Error!';
        }

        $this->setErrorMessage($errorMessage);
    }

    /**
     * @see OW_Validator::isValid()
     *
     * @param mixed $value
     */
    public function isValid( $value )
    {
        if ( is_array($value) )
        {
            if ( sizeof($value) === 0 )
            {
                return false;
            }
        }
        else if ( $value === null || mb_strlen(trim($value)) === 0 )
        {
            return false;
        }

        return true;
    }

    /**
     * @see OW_Validator::getJsValidator()
     *
     * @return string
     */
    public function getJsValidator()
    {
        return "{
        	validate : function( value ){
                if(  $.isArray(value) ){ if(value.length == 0  ) throw " . json_encode($this->getError()) . "; return;}
                else if( !value || $.trim(value).length == 0 ){ throw " . json_encode($this->getError()) . "; }
        },
        	getErrorMessage : function(){ return " . json_encode($this->getError()) . " }
        }";
    }
}

/**
 * Wyswyg required validator.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_core
 * @since 1.0
 */
class WyswygRequiredValidator extends OW_Validator
{
    /**
     * Constructor.
     *
     * @param array $params
     */
    public function __construct()
    {
        $errorMessage = OW::getLanguage()->text('base', 'form_validator_wyswyg_error_message');

        if ( empty($errorMessage) )
        {
            $errorMessage = 'Required Validator Error!';
        }

        $this->setErrorMessage($errorMessage);
    }

    /**
     * @see OW_Validator::isValid()
     *
     * @param mixed $value
     */
    public function isValid( $value )
    {
        // process value
        $value = strip_tags(str_replace(array('&nbsp;', '&nbsp'), array(' ', ' '), $value));

        return mb_strlen(trim($value));
    }

    /**
     * @see OW_Validator::getJsValidator()
     *
     * @return string
     */
    public function getJsValidator()
    {

        return "{
        	validate : function( value ){
                    // process value
                    value = value.replace(/\&nbsp;|&nbsp/ig,'');
                    value = value.replace(/(<([^>]+)>)/ig,'');

                    /*
                     // ---------- Original code block -------------------
                        if (!$.trim(value).length) {
                            throw " . json_encode($this->getError()) . ";
                        }
                     // ---------- End block ------------------------------
                     //
                     // ---------- Reason for change -------------------------------------------------------------------
                     // original code block is replaced by a new code block to handle paste a text.
                     // Before this change, validator recognize a pasted text as an empty string and throw an exception.
                     // --------------------------------------------------- --------------------------------------------  
                    */
                    if (!$.trim(value).length) { 
                        if(document.getElementsByClassName('owm_suitup-editor').length>0)
                        {              
                            hiddenValue = document.getElementsByClassName('owm_suitup-editor')[0].innerText;
                            hiddenValue = hiddenValue.replace(/\&nbsp;|&nbsp/ig,'');
                            hiddenValue = hiddenValue.replace(/(<([^>]+)>)/ig,'');
                            if (!$.trim(hiddenValue).length) {
                                throw " . json_encode($this->getError()) . ";
                            }else{                            
                                document.getElementsByName('text')[0].value = hiddenValue;
                            }
                        }
                        else{
                            throw " . json_encode($this->getError()) . ";
                        }
                    }
        },
        	getErrorMessage : function(){ return " . json_encode($this->getError()) . " }
        }";
    }

}

/**
 * StringValidator validates String.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_core
 * @since 1.0
 */
class StringValidator extends OW_Validator
{
    /**
     * String min length
     *
     * @var int
     */
    private $min;
    /**
     * String max length
     *
     * @var int
     */
    private $max;

    /**
     * Class constructor.
     *
     * @param int $min
     * @param int $max
     */
    public function __construct( $min = null, $max = null )
    {
        if ( isset($min) )
        {
            $this->setMinLength($min);
        }

        if ( isset($max) )
        {
            $this->setMaxLength($max);
        }

        $errorMessage = OW::getLanguage()->text('base', 'form_validator_string_error_message');

        if ( empty($errorMessage) )
        {
            $errorMessage = 'String Validator Error!';
        }
        
        $this->setErrorMessage($errorMessage);
    }

    /**
     * Sets string max length
     *
     * @param int $max
     */
    public function setMaxLength( $max )
    {
        if ( !isset($max) )
        {
            throw new InvalidArgumentException('Empty max length!');
        }

        $this->max = (int) $max;
    }

    /**
     * Sets string min length
     *
     * @param int $min
     */
    public function setMinLength( $min )
    {
        if ( !isset($min) )
        {
            throw new InvalidArgumentException('Empty min length!');
        }

        $this->min = (int) $min;
    }

    /**
     * @see OW_Validator::isValid()
     *
     * @param mixed $value
     */
    public function isValid( $value )
    {
        return parent::isValid1($value);
    }

    public function checkValue( $value )
    {
        $trimValue = trim($value);

        if ( isset($this->min) && mb_strlen($trimValue) < (int) $this->min )
        {
            return false;
        }

        if ( isset($this->max) && mb_strlen($trimValue) > (int) $this->max )
        {
            return false;
        }

        return true;
    }

    public function getJsValidator()
    {
        $js = "{
            
        	validate : function( value )
        	{
        		var self = this;
        	
        		// doesn't check empty values
        		if( !value || $.trim( value ).length == 0 || ( $.isArray(value) && value.length == 0 ) )
        		{
        			return;
        		}
        		
        		if( $.isArray(value) )
        		{
        			$.each( value,
                        function( i, item )
                        {
                        	self.checkValue( item );
                        } );
        		}
        		else
        		{
        			this.checkValue( value );
        		}
        	},
    		";

        $js .= "
        	getErrorMessage : function()
        	{
        		return " . json_encode($this->getError()) . "
    		},
        ";

        $js .= "
            checkValue : function( value )
            {
        ";

        if ( isset($this->min) )
        {
            $js .= "
            if( $.trim(value).length < " . $this->min . " )
            {
            	throw " . json_encode($this->getError()) . ";
            }
           ";
        }

        if ( isset($this->max) )
        {
            $js .= "
            if( $.trim(value).length > " . $this->max . " )
            {
            	throw " . json_encode($this->getError()) . ";
            }
           ";
        }

        $js .= "}
    		}";

        return $js;
    }
}

/**
 * RegExpValidator validates value by RegExp.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_core
 * @since 1.0
 */
class RegExpValidator extends OW_Validator
{
    /**
     * @var string
     */
    protected $pattern;

    /**
     * Class constructor.
     *
     * @param string pattern
     */
    public function __construct( $pattern = null )
    {
        if ( isset($pattern) )
        {
            $this->setPattern($pattern);
        }

        $errorMessage = OW::getLanguage()->text('base', 'form_validator_regexp_error_message');

        if ( empty($errorMessage) )
        {
            $errorMessage = 'Regexp Validator Error!';
        }
        
        $this->setErrorMessage($errorMessage);
    }

    /**
     * Sets pattern
     *
     * @param string $pattern
     */
    public function setPattern( $pattern )
    {
        if ( !isset($pattern) || mb_strlen(trim($pattern)) === 0 )
        {
            throw new InvalidArgumentException('Empty pattern!');
        }

        $this->pattern = trim($pattern);
    }

    /**
     * @see OW_Validator::isValid()
     *
     * @param mixed $value
     */
    public function isValid( $value )
    {
        return parent::isValid1($value);
    }

    public function checkValue( $value )
    {
        $trimValue = trim($value);

        if ( !preg_match($this->pattern, $trimValue) )
        {
            return false;
        }

        return true;
    }

    public function getJsValidator()
    {
        $js = "{
            
        	validate : function( value )
        	{
        		var self = this;
        	
        		// doesn't check empty values
        		if( !value || $.trim( value ).length == 0 || ( $.isArray(value) && value.length == 0 ) )
        		{
        			return;
        		}
        		
        		if( $.isArray(value) )
        		{
        			$.each( value,
                        function( i, item )
                        {
                        	self.checkValue( item );
                        } );
        		}
        		else
        		{
        			this.checkValue( value );
        		}
        	},
    		";

        $js .= "
        	getErrorMessage : function()
        	{
        		return " . json_encode($this->getError()) . "
    		},
        ";

        $js .= "
            checkValue : function( value )
            {
                var pattern = " . $this->pattern . ";
        		
            	if( !pattern.test( value ) )
            	{
            		throw " . json_encode($this->getError()) . ";
        		}
        	}}
        ";

        return $js;
    }
}

/**
 * EmailValidator validates Email.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_core
 * @since 1.0
 */
class EmailValidator extends RegExpValidator
{

    /**
     * Class constructor
     */
    public function __construct()
    {
        parent::__construct(UTIL_Validator::EMAIL_PATTERN);

        $errorMessage = OW::getLanguage()->text('base', 'form_validator_email_error_message');

        if ( empty($errorMessage) )
        {
            $errorMessage = 'Email Validator Error!';
        }

        $this->setErrorMessage($errorMessage);
    }
}


/**
 * UrlValidator validates Url.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_core
 * @since 1.0
 */
class UrlValidator extends RegExpValidator
{
    /**
     * Class constructor
     */
    public function __construct()
    {
        parent::__construct(UTIL_Validator::URL_PATTERN);

        $errorMessage = OW::getLanguage()->text('base', 'form_validator_url_error_message');

        if ( empty($errorMessage) )
        {
            $errorMessage = 'Url Validator Error!';
        }

        $this->setErrorMessage($errorMessage);
    }
}


/**
 * AlphaNumericValidator
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_core
 * @since 1.0
 */
class AlphaNumericValidator extends RegExpValidator
{

    /**
     * Class constructor
     */
    public function __construct()
    {
        parent::__construct(UTIL_Validator::ALPHA_NUMERIC_PATTERN);

        $errorMessage = OW::getLanguage()->text('base', 'form_validator_url_error_message');

        if ( empty($errorMessage) )
        {
            $errorMessage = 'Alphanumeric Validator Error!';
        }

        $this->setErrorMessage($errorMessage);
    }
}

/**
 * In array validator
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_core
 * @since 8.1
 */
class InArrayValidator extends OW_Validator
{
    /**
     * Predefined values
     *
     * @var array
     */
    protected $predefinedValues = array();

    /**
     * Class constructor
     *
     * @param array $predefinedValues
     */
    public function __construct( array $predefinedValues  = array() )
    {
        $this->predefinedValues = $predefinedValues;
        $this->errorMessage = OW::getLanguage()->text('base', 'form_validate_common_error_message');
    }

    /**
     * Set predefined values
     *
     * @param array $predefinedValues
     * @return void
     */
    public function setPredefinedValues( array $predefinedValues = array() )
    {
        $this->predefinedValues = $predefinedValues;
    }

    /**
     * Is data valid
     *
     * @param mixed $value
     * @return boolean
     */
    public function isValid( $value )
    {
        return is_scalar($value) && in_array($value, $this->predefinedValues);
    }

    /**
     * Get js validator
     *
     * @return string
     */
    public function getJsValidator()
    {
        $values = json_encode($this->predefinedValues);

        $js = "{
            validate : function( value )
        	{
        	    if ( $.inArray(value, {$values}) == -1 )
        	    {
        	        throw this.getErrorMessage();
        	    }
        	},

        	getErrorMessage : function()
        	{
        		return " . json_encode($this->getError()) . "
    		}
        }";

        return $js;
    }
}

/**
 * IntValidator
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_core
 * @since 1.0
 */
class IntValidator extends OW_Validator
{
    /**
     * @var int
     */
    private $min;
    /**
     * @var int
     */
    private $max;
    /**
     * @var string
     */
    private $pattern;

    /**
     * Class constructor
     *
     * @param int $min
     * @param int $max
     */
    public function __construct( $min = null, $max = null )
    {
        $this->pattern = UTIL_Validator::INT_PATTERN;

        if ( isset($min) )
        {
            $this->min = (int) $min;
        }

        if ( isset($max) )
        {
            $this->max = (int) $max;
        }

        $errorMessage = OW::getLanguage()->text('base', 'form_validator_int_error_message');

        if ( empty($errorMessage) )
        {
            $errorMessage = 'Int Validator Error!';
        }

        $this->setErrorMessage($errorMessage);
    }

    public function setMaxValue( $max )
    {
        $value = (int) $max;

        if ( empty($value) )
        {
            throw new InvalidArgumentException('Empty max value!');
        }

        $this->max = (int) $value;
    }

    public function setMinValue( $min )
    {
        $value = (int) $min;

        if ( empty($value) )
        {
            throw new InvalidArgumentException('Empty min value!');
        }

        $this->min = (int) $value;
    }

    public function isValid( $value )
    {
        return parent::isValid1($value);
    }

    public function checkValue( $value )
    {
        $intValue = (int) $value;

        if ( !UTIL_Validator::isIntValid($value) )
        {
            return false;
        }

        if ( isset($this->min) && $intValue < (int) $this->min )
        {
            return false;
        }

        if ( isset($this->max) && $intValue > (int) $this->max )
        {
            return false;
        }

        return true;
    }

    public function getJsValidator()
    {
        $js = "{
            
        	validate : function( value )
        	{
        		var self = this;
        		
        		// doesn't check empty values
        		if( !value || $.trim( value ).length == 0 || ( $.isArray(value) && value.length == 0 ) )
        		{
        			return;
        		}
        		
        		if( $.isArray(value) )
        		{
        			$.each( value,
                        function( i, item )
                        {
                        	self.checkValue( item );
                        } );
        		}
        		else
        		{
        			this.checkValue( value );
        		}
        	},
    		";

        $js .= "
        	getErrorMessage : function()
        	{
        		return " . json_encode($this->getError()) . "
    		},
        ";

        $js .= "
            checkValue : function( value )
            {
            	var pattern = " . $this->pattern . ";
        		
            	if( !pattern.test( value ) )
            	{
            		throw " . json_encode($this->getError()) . ";
        		}
        ";

        if ( isset($this->min) )
        {
            $js .= "
            if( parseInt(value) < " . $this->min . " )
            {
            	throw " . json_encode($this->getError()) . ";
            }
           ";
        }

        if ( isset($this->max) )
        {
            $js .= "
            if( parseInt(value) > " . $this->max . " )
            {
            	throw " . json_encode($this->getError()) . ";
            }
           ";
        }

        $js .= "}
    		}";

        return $js;
    }
}

/**
 * FloatValidator
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_core
 * @since 1.0
 */
class FloatValidator extends OW_Validator
{
    /**
     * @var float
     */
    private $min;
    /**
     * @var float
     */
    private $max;
    /**
     * @var string
     */
    private $pattern;

    /**

      /**
     * Class constructor
     *
     * @param float $min
     * @param float $max
     */
    public function __construct( $min = null, $max = null )
    {
        $this->pattern = UTIL_Validator::FLOAT_PATTERN;

        if ( isset($min) )
        {
            $this->min = (float) $min;
        }

        if ( isset($max) )
        {
            $this->max = (float) $max;
        }

        $errorMessage = OW::getLanguage()->text('base', 'form_validator_float_error_message');

        if ( empty($errorMessage) )
        {
            $errorMessage = 'Float Validator Error!';
        }

        $this->setErrorMessage($errorMessage);
    }

    public function setMaxValue( $max )
    {
        $value = (float) $max;

        if ( empty($value) )
        {
            throw new InvalidArgumentException('Empty max value!');
        }

        $this->max = (float) $value;
    }

    public function setMinValue( $min )
    {
        $value = (float) $min;

        if ( empty($value) )
        {
            throw new InvalidArgumentException('Empty min value!');
        }

        $this->min = (float) $value;
    }

    public function isValid( $value )
    {
        return parent::isValid1($value);
    }

    public function checkValue( $value )
    {
        $floatValue = (float) $value;

        if ( !UTIL_Validator::isFloatValid($value) )
        {
            return false;
        }

        if ( isset($this->min) && $floatValue < (float) $this->min )
        {
            return false;
        }

        if ( isset($this->max) && $floatValue > (float) $this->max )
        {
            return false;
        }

        return true;
    }

    public function getJsValidator()
    {
        $js = "{
            
        	validate : function( value )
        	{
        		var self = this;
        		
        		// doesn't check empty values
        		if( !value || $.trim( value ).length == 0 || ( $.isArray(value) && value.length == 0 ) )
        		{
        			return;
        		}
        		
        		if( $.isArray(value) )
        		{
        			$.each( value,
                        function( i, item )
                        {
                        	self.checkValue( item );
                        } );
        		}
        		else
        		{
        			this.checkValue( value );
        		}
        	},
    		";

        $js .= "
        	getErrorMessage : function()
        	{
        		return " . json_encode($this->getError()) . "
    		},
        ";

        $js .= "
            checkValue : function( value )
            {
                var pattern = " . $this->pattern . ";
        		
            	if( !pattern.test( value ) )
            	{
            		throw " . json_encode($this->getError()) . ";
        		}
        ";

        if ( isset($this->min) )
        {
            $js .= "
            if( parseFloat(value) < " . $this->min . " )
            {
            	throw " . json_encode($this->getError()) . ";
            }
           ";
        }

        if ( isset($this->max) )
        {
            $js .= "
            if( parseFloat(value) > " . $this->max . " )
            {
            	throw " . json_encode($this->getError()) . ";
            }
           ";
        }

        $js .= "}
    		}";

        return $js;
    }
}

/**
 * DateValidator
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_core
 * @since 1.0
 */
class DateValidator extends OW_Validator
{
    /**
     * @var int
     */
    private $minYear;
    /**
     * @var int
     */
    private $maxYear;
    /**
     * @var string
     */
    private $dateFormat = UTIL_DateTime::DEFAULT_DATE_FORMAT;

    /**
     * Class constructor
     *
     * @param int $min
     * @param int $max
     */
    public function __construct( $minYear = null, $maxYear = null )
    {
        if ( isset($minYear) )
        {
            $this->setMinYear($minYear);
        }

        if ( isset($maxYear) )
        {
            $this->setMaxYear($maxYear);
        }

        $errorMessage = OW::getLanguage()->text('base', 'form_validator_date_error_message');

        if ( empty($errorMessage) )
        {
            $errorMessage = 'Date Validator Error!';
        }

        $this->setErrorMessage($errorMessage);
    }

    public function setMaxYear( $maxYear )
    {
        $value = (int) $maxYear;

        if ( empty($value) )
        {
            throw new InvalidArgumentException('Incorrect max year value!');
        }

        $this->maxYear = (int) $value;
    }

    public function setDateFormat( $dateFormat )
    {
        $format = trim($dateFormat);

        if ( empty($format) )
        {
            throw new InvalidArgumentException('Incorrect argument `$format`!');
        }

        $this->dateFormat = trim($format);
    }

    public function setMinYear( $minYear )
    {
        $value = (int) $minYear;

        if ( empty($value) )
        {
            throw new InvalidArgumentException('Incorrect min year value!');
        }

        $this->minYear = (int) $value;
    }

    public function isValid( $value )
    {
        return parent::isValid1($value);
    }

    public function checkValue( $value )
    {
        if ( $value === null || mb_strlen(trim($value)) === 0 )
        {
            return true;
        }

        $date = UTIL_DateTime::parseDate($value, $this->dateFormat);

        if ( $date === null )
        {
            return false;
        }

        if ( !UTIL_Validator::isDateValid($date[UTIL_DateTime::PARSE_DATE_MONTH], $date[UTIL_DateTime::PARSE_DATE_DAY], $date[UTIL_DateTime::PARSE_DATE_YEAR]) )
        {
            return false;
        }

        if ( !empty($this->maxYear) && $date[UTIL_DateTime::PARSE_DATE_YEAR] > $this->maxYear )
        {
            return false;
        }

        if ( !empty($this->minYear) && $date[UTIL_DateTime::PARSE_DATE_YEAR] < $this->minYear )
        {
            return false;
        }

        return true;
    }

    public function getJsValidator()
    {
        return "{
        	validate : function( value ){
        },
        	getErrorMessage : function(){ return " . json_encode($this->getError()) . " }
        }";
    }
}

/**
 * DateValidator
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_core
 * @since 1.0
 */
class CaptchaValidator extends OW_Validator
{
    protected $jsObjectName = null;

    public function __construct()
    {
        $errorMessage = OW::getLanguage()->text('base', 'form_validator_captcha_error_message');

        if ( empty($errorMessage) )
        {
            $errorMessage = 'Captcha Validator Error!';
        }

        $this->setErrorMessage($errorMessage);
    }

    public function isValid( $value )
    {
        return parent::isValid1($value);
    }

    public function setJsObjectName( $name )
    {
        if ( !empty($name) )
        {
            $this->jsObjectName = $name;
        }
    }

    public function checkValue( $value )
    {
        return UTIL_Validator::isCaptchaValid($value);
    }

    public function getJsValidator()
    {
        if ( empty($this->jsObjectName) )
        {
            return "{
                    validate : function( value ){
            },
                    getErrorMessage : function(){ return " . json_encode($this->getError()) . " }
            }";
        }
        else
        {
            return "{
                 
                    validate : function( value )
                    {
                        if( !window." . $this->jsObjectName . ".validateCaptcha() )
                        {
                            throw " . json_encode($this->getError()) . ";
                        }
                    },
                    
                    getErrorMessage : function()
                    {
                        return " . json_encode($this->getError()) . ";
                    }
            }";
        }
    }
}

class RangeValidator extends OW_Validator
{
    /**
     * @var int
     */
    private $min;
    /**
     * @var int
     */
    private $max;
    /**
     * Class constructor.
     *
     */
    public function __construct()
    {
        $errorMessage = OW::getLanguage()->text('base', 'form_validator_range_error_message');

        if ( empty($errorMessage) )
        {
            $errorMessage = 'Range Validator Error!';
        }
        
        $this->setErrorMessage($errorMessage);
    }
    
    public function setMaxValue( $max )
    {
        $value = (int) $max;

        if ( !isset($value) )
        {
            throw new InvalidArgumentException('Empty max value!');
        }

        $this->max = (int) $value;
    }

    public function setMinValue( $min )
    {
        $value = (int) $min;

        if ( !isset($value) )
        {
            throw new InvalidArgumentException('Empty min value!');
        }

        $this->min = (int) $value;
    }

    /**
     * @see OW_Validator::isValid()
     *
     * @param mixed $value
     */
    public function isValid( $value )
    {
        // doesn't check empty values
        if ( $value === null )
        {
            return true;
        }
        
        if ( is_string($value) && mb_strlen(trim($value)) === 0 )
        {
            return true;
        }
        
        if ( is_array($value) )
        {
            $value = implode('-', $value);
        }
        
        return $this->checkValue($value);
    }

    public function checkValue( $value )
    {
        $value = trim($value);
        
        if ( empty($value) )
        {
            return false;
        }
        
        $valArray = explode('-', $value);

        if ( empty($valArray) || !isset($valArray[0]) || !isset($valArray[1]) )
        {
            return false;
        }

        if ($valArray[0] > $valArray[1])
        {
            return false;
        }
        
        if ( isset($this->min) && ($valArray[0] < (int) $this->min || $valArray[1] < (int) $this->min) )
        {
            return false;
        }

        if ( isset($this->max) && ($valArray[0] > (int) $this->max || $valArray[1] > (int) $this->max) )
        {
            return false;
        }
        
        return true;
    }

    public function getJsValidator()
    {
        $js = "{
            
        	validate : function( value )
        	{
        		var self = this;
        	
        		// doesn't check empty values
        		if( !value || $.trim( value ).length == 0 || ( $.isArray(value) && value.length == 0 ) )
        		{
        			return;
        		}
        		
        		if( $.isArray(value) )
        		{
        			$.each( value,
                        function( i, item )
                        {
                        	self.checkValue( item );
                        } );
        		}
        		else
        		{
        			this.checkValue( value );
        		}
        	},
    		";

        $js .= "
        	getErrorMessage : function()
        	{
        		return " . json_encode($this->getError()) . "
    		},
        ";

        $js .= "
            checkValue : function( value )
            {
        ";

        if (isset($this->min) || isset($this->max))
        {
            if ( isset($this->min) )
            {
                $js .= "
                if( $.trim(value) < " . $this->min . " )
                {
                    throw " . json_encode($this->getError()) . ";
                }
               ";
            }

            if ( isset($this->max) )
            {
                $js .= "
                if( $.trim(value) > " . $this->max . " )
                {
                    throw " . json_encode($this->getError()) . ";
                }
               ";
            }
        }
        else
        {
            $js .= "if( $.trim(value).length == 0 )
                {
                    throw " . json_encode($this->getError()) . ";
                }
               ";
        }

        $js .= "}
    		}";

        return $js;
    }
}

class NationalCodeValidator extends OW_Validator
{
    protected $jsObjectName = null;

    public function __construct()
    {
        $errorMessage = OW::getLanguage()->text('base', 'form_validator_national_code_wrong');

        if ( empty($errorMessage) )
        {
            $errorMessage = 'National Code Validator Error!';
        }

        $this->setErrorMessage($errorMessage);
    }

    public function isValid( $value )
    {
        return parent::isValid1($value);
    }

    public function setJsObjectName( $name )
    {
        if ( !empty($name) )
        {
            $this->jsObjectName = $name;
        }
    }

    public function checkValue( $value )
    {
        if (strlen($value) == 10)
        {
            if ($value == '1111111111' ||
                $value == '0000000000' ||
                $value == '2222222222' ||
                $value == '3333333333' ||
                $value == '4444444444' ||
                $value == '5555555555' ||
                $value == '6666666666' ||
                $value == '7777777777' ||
                $value == '8888888888' ||
                $value == '9999999999' ||
                $value == '0123456789'
            )
            {
                return false;
            }

            $c = substr($value,9,1);

            $n = substr($value,0,1) * 10 +
                substr($value,1,1) * 9 +
                substr($value,2,1) * 8 +
                substr($value,3,1) * 7 +
                substr($value,4,1) * 6 +
                substr($value,5,1) * 5 +
                substr($value,6,1) * 4 +
                substr($value,7,1) * 3 +
                substr($value,8,1) * 2;
            $r = $n - (int)($n / 11) * 11;
            if (($r == 0 && $r == $c) || ($r == 1 && $c == 1) || ($r > 1 && $c == 11 - $r))
            {
                return true;
            }
            else
            {
                return false;
            }
        }
        else
        {
            return false;
        }
    }


}

/**
 * File extension Validator
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_core
 * @since 1.8.4
 */
class FileExtensionValidator extends OW_Validator
{
    /**
     * List of disallowed extensions
     *
     * @var array
     */
    protected $disallowedExtensions = array();
    /**
     * List of allowed extensions
     *
     * @var array
     */
    protected $allowedExtensions = array();

    /**
     * @param array $disallowedExtensions
     */
    public function setDisallowedExtensions($disallowedExtensions)
    {
        $this->disallowedExtensions = $disallowedExtensions;
    }

    /**
     * @param array $allowedExtensions
     */
    public function setAllowedExtensions($allowedExtensions)
    {
        $this->allowedExtensions = $allowedExtensions;
    }
    /**
     * Class constructor
     * @param array $disallowedExtensions extensions not allowed
     * @param array $allowedExtensions extensions allowed
     */
    public function __construct($disallowedExtensions, $allowedExtensions)
    {
        $this->setDisallowedExtensions($disallowedExtensions);
        $this->setAllowedExtensions($allowedExtensions);
        $this->errorMessage = OW::getLanguage()->text('base', 'wrong_file_extension');
    }

    public function isValid( $file )
    {
        if($file['name']== "" && $file['size']== 0){
            return true;
        }
        $value = $file['name'];
        $values = explode('.', $value);
        $extension = $values[count($values)-1];
        if($this->allowedExtensions != null){
            foreach ($this->allowedExtensions as $allowedExtension){
                if ( $allowedExtension == $extension )
                {
                    return true;
                }
            }
        }
        else if($this->disallowedExtensions != null){
            foreach ($this->disallowedExtensions as $disallowedExtension){
                if ( $disallowedExtension == $extension )
                {
                    return false;
                }
            }
        }else{
            return true;
        }

        return false;
    }
}

abstract class AbstractPasswordValidator extends OW_Validator
{

    /**
     * AbstractPasswordValidator constructor.
     */
    public function __construct()
    {
    }

    /**
     * @param mixed $value
     * @return bool
     */
    public function isValid( $value )
    {
        $language = OW::getLanguage();

        if ( mb_strlen($value) > 0 && mb_strlen($value) < UTIL_Validator::PASSWORD_MIN_LENGTH )
        {
            $this->setErrorMessage($language->text('base', 'join_error_password_too_short'));
            return false;
        }
        else if ( mb_strlen($value) > UTIL_Validator::PASSWORD_MAX_LENGTH )
        {
            $this->setErrorMessage($language->text('base', 'join_error_password_too_long'));
            return false;
        }
        else if ( isset($_POST['repeatPassword']) && $value !== $_POST['repeatPassword'] )
        {
            $this->setErrorMessage($language->text('base', 'join_error_password_not_valid'));
            return false;
        }

        $resultOfEvenet = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_PASSWORD_VALIDATION_IN_JOIN_FORM, array('value' => $value)));
        if(isset($resultOfEvenet->getData()['error'])){
            $this->setErrorMessage(  $resultOfEvenet->getData()['error']);
            return false;
        }

        return true;
    }
}

class NewPasswordValidator extends AbstractPasswordValidator
{
    /***
     * NewPasswordValidator constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @see Validator::getJsValidator()
     *
     * @return string
     */
    public function getJsValidator()
    {
        return "{
                validate : function( value )
                {
                    if( !window.changePassword.validatePassword() )
                    {
                        throw window.changePassword.errors['password']['error'];
                    }
                },
                getErrorMessage : function()
                {
                       if( window.changePassword.errors['password']['error'] !== undefined ){ return window.changePassword.errors['password']['error'] }
                       else{ return ".json_encode($this->getError())." }
                }
        }";
    }
}

class OldPasswordValidator extends OW_Validator
{
    private $inputName;

    /***
     * OldPasswordValidator constructor.
     * @param string $inputName
     */
    public function __construct($inputName = 'oldPassword')
    {
        $this->inputName = $inputName;
        $language = OW::getLanguage();
        $this->setErrorMessage($language->text('base', 'password_protection_error_message'));
    }

    /***
     * @param mixed $value
     * @return bool
     */
    public function isValid( $value )
    {
        $result = BOL_UserService::getInstance()->isValidPassword( OW::getUser()->getId(), $value );

        return $result;
    }

    /**
     * @see Validator::getJsValidator()
     *
     * @return string
     */
    public function getJsValidator()
    {
        return "{
            validate : function( value )
            {
                if( !window." . $this->inputName . ".validatePassword() )
                {
                    throw window." . $this->inputName . ".errors['password']['error'];
                }
            },
            getErrorMessage : function()
            {
                if( window." . $this->inputName . ".errors['password']['error'] !== undefined ){
                    return window." . $this->inputName . ".errors['password']['error'] 
                }else{ 
                    return " . json_encode($this->getError()) . "
                }
            }
        }";
    }
}

class EditUserNameValidator extends OW_Validator
{
    private $userId = null;

    /**
     * Constructor.
     *
     * @param array $params
     */
    public function __construct( $userId = null )
    {
        $this->userId = $userId;
    }

    /**
     * @see Validator::isValid()
     *
     * @param mixed $value
     */
    public function isValid( $value )
    {
        $language = OW::getLanguage();

        if ( !UTIL_Validator::isUserNameValid($value) )
        {
            $this->setErrorMessage($language->text('base', 'join_error_username_not_valid'));
            return false;
        }

        if ( BOL_UserService::getInstance()->isExistUserName($value) )
        {
            $userId = OW::getUser()->getId();

            if ( !empty($this->userId) )
            {
                $userId = $this->userId;
            }

            $user = BOL_UserService::getInstance()->findUserById($userId);

            if (!$user || $value !== $user->username )
            {
                $this->setErrorMessage($language->text('base', 'join_error_username_already_exist'));
                return false;
            }
        }

        if ( BOL_UserService::getInstance()->isRestrictedUsername($value) && !OW::getUser()->isAdmin() )
        {
            $this->setErrorMessage($language->text('base', 'join_error_username_restricted'));
            return false;
        }

        if ( OW::getConfig()->configExists('base', 'username_chars_min') )
        {
            $config = OW::getConfig();
            $usernameMin = $config->configExists('base', 'username_chars_min')?$config->getValue('base', 'username_chars_min'):1;
            $usernameMax = $config->configExists('base', 'username_chars_max')?$config->getValue('base', 'username_chars_max'):32;
            if (strlen($value)<$usernameMin || strlen($value)>$usernameMax) {
                $this->setErrorMessage($language->text('base', 'join_error_username_length_not_valied', ['min'=>$usernameMin, 'max'=>$usernameMax]));
                return false;
            }
        }


        return true;
    }

    /**
     * @see Validator::getJsValidator()
     *
     * @return string
     */
    public function getJsValidator()
    {
        return "{
                validate : function( value )
                {
                    // window.edit.validateUsername(false);
                    if( window.edit.errors['username']['error'] !== undefined )
                    {
                        throw window.edit.errors['username']['error'];
                    }
                },
                getErrorMessage : function(){
                    if( window.edit.errors['username']['error'] !== undefined ){ return window.edit.errors['username']['error']; }
                    else{ return " . json_encode($this->getError()) . " }
                }
        }";
    }
}

class NewUserUsernameValidator extends OW_Validator
{

    /**
     * Constructor.
     *
     * @param array $params
     */
    public function __construct()
    {

    }

    /**
     * @see Validator::isValid()
     *
     * @param mixed $value
     */
    public function isValid( $value )
    {
        $language = OW::getLanguage();

        if ( !UTIL_Validator::isUserNameValid($value) )
        {
            $this->setErrorMessage($language->text('base', 'join_error_username_not_valid'));
            return false;
        }

        if ( BOL_UserService::getInstance()->isExistUserName($value) )
        {
            $this->setErrorMessage($language->text('base', 'join_error_username_already_exist'));
            return false;
        }

        if ( BOL_UserService::getInstance()->isRestrictedUsername($value) && !OW::getUser()->isAdmin() )
        {
            $this->setErrorMessage($language->text('base', 'join_error_username_restricted'));
            return false;
        }

        if ( OW::getConfig()->configExists('base', 'username_chars_min') )
        {
            $config = OW::getConfig();
            $usernameMin = $config->configExists('base', 'username_chars_min')?$config->getValue('base', 'username_chars_min'):1;
            $usernameMax = $config->configExists('base', 'username_chars_max')?$config->getValue('base', 'username_chars_max'):32;
            if (strlen($value)<$usernameMin || strlen($value)>$usernameMax) {
                $this->setErrorMessage($language->text('base', 'join_error_username_length_not_valied', ['min'=>$usernameMin, 'max'=>$usernameMax]));
                return false;
            }
        }
        return true;
    }
}

class NewUserEmailValidator extends OW_Validator
{

    /**
     * Constructor.
     *
     * @param array $params
     */
    public function __construct()
    {

    }

    /**
     * @see Validator::isValid()
     *
     * @param mixed $value
     */
    public function isValid( $value )
    {
        $language = OW::getLanguage();

        if ( !UTIL_Validator::isEmailValid($value) )
        {
            $this->setErrorMessage($language->text('base', 'join_error_email_not_valid'));
            return false;
        }

        if ( BOL_UserService::getInstance()->isExistEmail($value) )
        {
            $this->setErrorMessage($language->text('base', 'join_error_email_already_exist'));
            return false;
        }
        return true;
    }
}

class EditEmailValidator extends OW_Validator
{
    private $userId = null;

    /**
     * Constructor.
     *
     * @param array $params
     */
    public function __construct( $userId )
    {
        if ( empty($userId) )
        {
            throw new InvalidArgumentException(" Invalid parameter userId ");
        }

        $this->userId = $userId;
    }

    /**
     * @see Validator::isValid()
     *
     * @param mixed $value
     */
    public function isValid( $value )
    {
        $language = OW::getLanguage();

        if ( !UTIL_Validator::isEmailValid($value) )
        {
            $this->setErrorMessage($language->text('base', 'join_error_email_not_valid'));
            return false;
        }

        if ( BOL_UserService::getInstance()->isExistEmail($value) )
        {
            $userId = $this->userId;
            $user = BOL_UserService::getInstance()->findUserById($userId);

            if ( !$user || $value !== $user->email )
            {
                $this->setErrorMessage($language->text('base', 'join_error_email_already_exist'));
                return false;
            }
        }

        return true;
    }

    /**
     * @see Validator::getJsValidator()
     *
     * @return string
     */
    public function getJsValidator()
    {
        return "{
        	validate : function( value )
                {
                    // window.edit.validateEmail(false);
                    if( window.edit.errors['email']['error'] !== undefined )
                    {
                        throw window.edit.errors['email']['error'];
                    }
                },
        	getErrorMessage : function(){
                    if( window.edit.errors['email']['error'] !== undefined ){ return window.edit.errors['email']['error']; }
                    else{ return " . json_encode($this->getError()) . " }
                }
        }";
    }
}
