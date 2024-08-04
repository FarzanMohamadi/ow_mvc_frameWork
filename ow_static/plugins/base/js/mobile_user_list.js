var OW_UserList = function( params )
{
    params = params || {};

    var self = this;
    this.node = params.node;
    this.cmp = params.component;
    this.list = params.listType;
    this.showOnline = params.showOnline;
    this.responder = params.responderUrl;
    this.count = params.count;
    this.searchSelector = params.searchSelector;

    if (typeof params.componentWindow !== 'undefined'){
        this.preloader = $(params.componentWindow+' .owm_user_list_preloader');
    } else if(typeof params.preloader !== 'undefined') {
        this.preloader = $(params.preloader);
    } else{
        this.preloader = $('.owm_user_list_preloader');
    }

    if (typeof params.componentWindow !== 'undefined'){
        this.currentWindow = $(params.componentWindow).parent();
        this.currentDocument = $(params.componentWindow);
    } else{
        this.currentWindow = $(window);
        this.currentDocument = $(document);
    }

    this.allowLoadData = true;
    this.process = false;
    this.renderedItems = [];

    if ( $.isArray(params.excludeList) )
    {
        self.addDataToExcludeList(params.excludeList);
    }

    this.currentWindow.scroll(function( event ) {
            self.tryLoadData();
        });

    self.tryLoadData();
};

OW_UserList.prototype = 
{
    addDataToExcludeList: function( data )
    {
        var self = this;

        if(data['excludeList']!=undefined)
        {
            self.renderedItems= data['excludeList'];
            return;
        }

        $.each( data, function( key, val ) {
            self.renderedItems.push(val);
        } )
    },

    getExcludeList: function()
    {
        var self = this;

        var list = []
        $.each( self.renderedItems, function( key, item ) {
            list.push(item);
        } );
        return list;
    },

    resetExcludeList: function()
    {
        var self = this;

        self.allowLoadData = true;
        self.renderedItems = [];
    },

    setProsessStatus: function( value )
    {
        var self = this;

        self.process = value;

        if ( value )
        {
            self.preloader.css("visibility","visible");
        }
        else
        {
            self.preloader.css("visibility","hidden");
            if ( !self.allowLoadData )
            {
                self.preloader.hide();
            }
        }
    },

    loadData: function()
    {
        var self = this;

        if ( self.process )
        {
            return;
        }

        self.setProsessStatus(true);

        var exclude = self.getExcludeList();

        var ajaxOptions = {
            url: self.responder,
            dataType: 'json',
            type: 'POST',
            data: {
                list: self.list,
                showOnline: self.showOnline,
                excludeList: exclude,
                count: self.count
            },            
            success: function(data)
            {
                
                
                if ( !data || data.length == 0 )
                {
                    self.allowLoadData = false;
                    self.setProsessStatus(false);
                }
                else
                {
                    self.allowLoadData = true;
                    self.addDataToExcludeList(data);
                    self.renderList(data);
                }

            }
        };

        if($(self.searchSelector).length != 0) {
            var searchValue = $(self.searchSelector)[0].value;
            ajaxOptions.data.q = searchValue;
        }
        
        $.ajax(ajaxOptions);
    },

    renderList: function( data )
    {
        var self = this;
        OWM.loadComponent( self.cmp, [self.list, data, self.showOnline], function( content ) {  self.append(content); self.setProsessStatus(false);  } );
        
        self.setProsessStatus(false);
    },

    append: function( content )
    {
        var self = this;

        $(self.node).append($(content));
    },

    tryLoadData: function()
    {
        var self = this;

        if ( !self.allowLoadData )
            return;

        if ( self.getExcludeList().length < self.count )
        {
            self.allowLoadData = false;
            self.setProsessStatus(false);
        }

        var diff = this.currentDocument.height() - (this.currentWindow.scrollTop() + this.currentWindow.height());

        if ( diff < 100 )
        {
            self.loadData();
        }
    }
}