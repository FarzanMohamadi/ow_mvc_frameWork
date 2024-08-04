
OW_FriendRequest = function( itemKey, params )
{
    let listLoaded = false;
    let refetch = true;
    let model = OW.Console.getData(itemKey);
    let list = OW.Console.getItem(itemKey);
    let counter = new OW_DataModel();

    counter.addObserver(function()
    {
        let newCount = counter.get('new');
        let counterNumber = newCount > 0 ? newCount : counter.get('all');

        list.setCounter(counterNumber, newCount > 0);

        if ( counterNumber > 0 )
        {
            list.showItem();
        }
    });

    list.onHide = function()
    {
        counter.set('new', 0);
        list.getItems().removeClass('ow_console_new_message');
        model.set('counter', counter.get());
    };

    list.onShow = function()
    {
        if ( counter.get('all') <= 0 )
        {
            if(listLoaded){
                this.showNoContent();
                return;
            }else{
                this.showPreloader();
                return;
            }
        }

        if ( counter.get('new') > 0 || !listLoaded )
        {
            this.loadList();
            listLoaded = true;
        }
    };

    model.addObserver(function()
    {
        if ( !list.opened )
        {
            counter.set(model.get('counter'));
        }else if(refetch){
            list.loadList();
            counter.set('all', model.get('counter.all'));
        }
        refetch = true;
    });

    this.removeItem = function( requestKey, userId )
    {
        var item = list.getItem(requestKey);
        var c = {};

        if ( item.hasClass('ow_console_new_message') )
        {
            c["new"] = counter.get("new") - 1;
        }
        c["all"] = counter.get("all") - 1;
        counter.set(c);

        $('#friend_request_accept_'+userId).addClass( "ow_hidden");
        $('#friend_request_ignore_'+userId).addClass( "ow_hidden");
        list.removeItem(item);
        refetch = false;
        model.set('counter', counter.get());

        return this;
    };

    this.accept = function( requestKey, userId ,code)
    {
        this.send('friends-accept', {id: userId, code:code});
        return this.removeItem(requestKey, userId);
    };

    this.ignore = function( requestKey, userId ,code)
    {
        this.send('friends-ignore', {id: userId, code:code});
        return this.removeItem(requestKey, userId);
    };

    this.send = function( command, data )
    {
        var request = $.ajax({
            url: params.rsp,
            type: "POST",
            data: {
                "command": command,
                "data": JSON.stringify(data)
            },
            dataType: "json"
        });

        request.done(function( res )
        {
            if ( res && res.script )
            {
                OW.addScript(res.script);
            }
        });

        return this;
    };
}

OW.FriendRequest = null;