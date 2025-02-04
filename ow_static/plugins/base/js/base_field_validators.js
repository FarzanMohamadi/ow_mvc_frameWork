var OW_BaseFieldValidators = function($params, $emailPattern, $usernamePattern, userId)
{
        var self = this;

        this.responderUrl = $params.responderUrl;
        this.passwordMaxLength = $params.passwordMaxLength;
        this.passwordMinLength = $params.passwordMinLength;
        this.emailPattern = $emailPattern;
        this.usernamePattern = $usernamePattern;
        this.formName = $params['formName'];
        this.userId = userId;
        
        this.errors = [];
        
        var username = $("form[name='" + this.formName + "'] input[name='username']");
        var password = $("form[name='" + this.formName + "'] input[name='password']");
        var passwordRepeat = $("form[name='" + this.formName + "'] input[name=repeatPassword]");
        var email = $("form[name='" + this.formName + "'] input[name='email']");

        if( username.length == 0 )
        {
            username = $("form[name='" + this.formName + "'] input.ow_username_validator");
        }

        if( email.length == 0 )
        {
            email = $("form[name='" + this.formName + "'] input.ow_email_validator");
        }
        
        if( password )
        {
            this.errors['password'] = [];
        }

        if( username )
        {
            this.errors['username'] = [];
            username.bind('blur',function(){ self.validateUsername(this) });
        }

        if( email )
        {
           this.errors['email'] = [];
           email.bind('blur',function(){ self.validateEmail(this) });
        }

        /***
         *  Issa Annamoradnejad
         * @param $string
         * @returns {*}
         */
        function convertToEnglishNumbers($string){
            $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
            $arabic = ['٩', '٨', '٧', '٦', '٥', '٤', '٣', '٢', '١','٠'];
            for(var i=0;i<=9;i++) {
                $string = $string.replace($persian[i], i.toString());
                $string = $string.replace($arabic[i], i.toString());
            }
            return $string;
        }

        this.validateUsername = function( $element )
        {
            var username = $($element);
            if( username.val().length > 0 )
            {
                username.val(convertToEnglishNumbers(username.val()));
                if( self.errors['username']['value'] !==  username.val() )
                {
                    self.errors['username']['value'] = username.val();
                    self.errors['username']['error'] = undefined;
                    
                    var element = owForms[this.formName].elements[username.attr('name')];
                    element.removeErrors();

                    if( !this.usernamePattern.test( username.val() ) )
                    {
                        self.errors['username']['error']  = OW.getLanguageText('base', 'join_error_username_not_valid');
                        element.showError( OW.getLanguageText('base', 'join_error_username_not_valid') );
                        return false;
                    }
                    else
                    {
                        $.ajax( {
                            url: self.responderUrl,
                            type: 'POST',
                            data: { command: 'isExistUserName', value: username.val(),'userId': self.userId },
                            dataType: 'json',
                            // async: isAsync,
                            success: function( data )
                            {
                                if( data.result == false )
                                {
                                     self.errors['username']['error'] = OW.getLanguageText('base', 'join_error_username_already_exist');
                                     element.showError( OW.getLanguageText('base', 'join_error_username_already_exist') );
                                }
                            }
                        } );
                    }
                }
            }
        }

        this.validateEmail = function( $element )
        {
            var email = $($element);
            
            if( email.val().length > 0 )
            {
                email.val(convertToEnglishNumbers(email.val()));
                if( self.errors['email']['value'] !== email.val() )
                {
                    self.errors['email']['value'] = email.val();
                    self.errors['email']['error'] = undefined;
                    var element = owForms[this.formName].elements[email.attr('name')];
                    element.removeErrors();

                    if( !this.emailPattern.test( email.val() ) )
                    {
                        self.errors['email']['error'] = OW.getLanguageText('base', 'join_error_email_not_valid');
                        element.showError( OW.getLanguageText('base', 'join_error_email_not_valid') );
                        return false;
                    }
                    else
                    {
                        $.ajax( {
                                url: self.responderUrl,
                                type: 'POST',
                                data: { command: 'isExistEmail', value: email.val(), 'userId': self.userId },
                                dataType: 'json',
                                // async: isAsync,
                                success: function( data )
                                {
                                    if( data.result == false )
                                    {
                                         self.errors['email']['error'] = OW.getLanguageText('base', 'join_error_email_already_exist');
                                         element.showError( OW.getLanguageText('base', 'join_error_email_already_exist') );
                                    }
                                }
                        } );

                    }
                }
            }
        }

        this.validatePassword = function()
        {
            var element = owForms[this.formName].elements['password'];
            element.removeErrors();

            self.errors['password']['error'] = undefined;

            if ( password.val().length > 0 && password.val().length < this.passwordMinLength )
            {
                self.errors['password']['error'] = OW.getLanguageText('base', 'join_error_password_too_short');
                return false;
            }
            else if  ( password.val().length > this.passwordMaxLength )
            {
                self.errors['password']['error'] = OW.getLanguageText('base', 'join_error_password_too_long');
                return false;
            }
            else if ( password.val() !== passwordRepeat.val() & passwordRepeat.val().length > 0 )
            {
                self.errors['password']['error'] = OW.getLanguageText('base', 'reset_password_not_equal_error_message');
                return false;
            }

            return true;
        }
};