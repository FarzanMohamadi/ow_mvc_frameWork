function filterRulesChanged() {
    var items = $('tr[name=rule_item]');
    var checkedCategories = $('input[name="filter_category"]:checked');
    var checkedTags = $('input[name="filter_tag"]:checked');
    var without_filter = checkedTags.length==0;//$('input[name="filter__without_tag"]')[0].checked;

    for (i = 0; i < items.length; i++) {
        exist_in_tag = true;
        exist_in_category = false;

        item = items[i];
        category = item.attributes['data-category'].value;
        tag = item.attributes['data-tag'].value;

        for (j = 0; j < checkedCategories.length; j++) {
            if (checkedCategories[j].value == category) {
                exist_in_category = true;
            }
        }

        if (tag != '') {
            tag = tag.split('.');
            for (j = 0; j < checkedTags.length; j++) {
                find = false;
                for (k = 0; k < tag.length; k++) {
                    if (tag[k] == checkedTags[j].value) {
                        find = true;
                    }
                }
                if(!find) {
                    exist_in_tag = false;
                }
            }
        } else if (!without_filter || checkedTags.size()>0) {
            exist_in_tag = false;
        }

        if (!exist_in_tag || !exist_in_category) {
            item.style.display = 'none';
        } else {
            item.style.display = 'table-row';
        }

    }
}

function editRuleItem($item_id, $title) {
    OW.ajaxFloatBox('FRMRULES_CMP_EditItemFloatBox', {id: $item_id}, {iconClass: 'ow_ic_edit', title: $title});
}

function deleteRuleItem($url, $warning) {
    let jc = $.confirm($warning);
    jc.buttons.ok.action = function () {
        location.href = $url;
    };
}

function editRuleCategory($item_id, $title) {
    OW.ajaxFloatBox('FRMRULES_CMP_EditCategoryFloatBox', {id: $item_id}, {iconClass: 'ow_ic_edit', title: $title});
}

function deleteRuleCategory($url, $warning) {
    let jc = $.confirm($warning);
    jc.buttons.ok.action = function () {
        location.href = $url;
    };
}

function redefineOrderItems(){
    numbers = $('.item_number');
    for (i = 0; i < numbers.length; i++) {
        rowNumber = i+1;
        numbers[i].innerHTML = rowNumber;
    }
}