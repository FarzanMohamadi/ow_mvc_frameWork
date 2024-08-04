OW_Notification = function( itemKey )
{
    var listLoaded = false;
    let counter = new OW_DataModel();
    let model = OW.Console.getData(itemKey);
    let list = OW.Console.getItem(itemKey);

    counter.addObserver(function()
    {
        let newCount = counter.get('new');
        if(newCount > 0){
            list.setCounter(newCount, true);
        }else{
            list.setCounter(0);
        }
        let counterNumber = newCount > 0 ? newCount : counter.get('all');

        if ( counterNumber > 0 )
        {
            list.showItem();
        }
    });

    model.addObserver(function()
    {
        if ( !list.opened )
        {
            counter.set(model.get('counter'));
        }else{
            list.loadList();
        }
    });

    list.onHide = function()
    {
        list.getItems().removeClass('ow_console_new_message');
        counter.set('new', 0);
        model.set('counter', counter.get());
    };

    list.onShow = function()
    {
        if ( counter.get('all') <= 0 )
        {
            this.showNoContent();
        }

        if ( counter.get('new') > 0 || !listLoaded )
        {
            this.loadList();
            listLoaded = true;
        }
    };
};

OW.Notification = null;

function hideNotification(event, hideUrl, notificationId) {
    event.stopImmediatePropagation();
    event.preventDefault();
    var answer = $.confirm(OW.getLanguageText('base', 'are_you_sure'));
    answer.buttons.ok.action = function() {
        $.ajax({
            type: 'post',
            url: hideUrl,
            context: this,
            dataType: 'json',
            data: {
                id: notificationId
            },
            success: function (resp) {
                if (resp.result) {
                    if (resp.result === 'ok') {
                        $('#notification_' + notificationId).hide();
                    }
                }
            }
        });
    };
    return false;
}
