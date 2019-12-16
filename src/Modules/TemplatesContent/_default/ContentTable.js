$('#' + htmlTarget + ' .draggable.move').draggable({
    revert: 'invalid',
    cursor: 'move',
    containment: '#df_template_fields',
    helper: 'clone',
    snap: true
});

$('#' + htmlTarget + ' .droppable').droppable({
    accept: function(ui) { return ($(this).attr('data-full')!='1'); },
    drop: function(event,ui) {
        if($(ui.draggable).hasClass('ui-modal-dlg')) {
            return false;
        }
        let cell=$(this).attr('data-cell');
        let cellId=$(ui.draggable).attr('data-id');
        let ths=$(this);
        if($(ui.draggable).hasClass('clone')) {
            let acb=function() {
                $(ths).find('span.blank').hide();
                $(ths).append($(ui.draggable).clone());
                $(ths).children('.draggable').each(function() {
                    $(this).removeClass('clone');
                    $(this).attr('data-cell',cell);
                    $(this).draggable({
                        revert: 'invalid',
                        cursor: 'move',
                        containment: '#df_template_fields',
                        helper: 'clone',
                        snap: true
                    });
                });
                $(ths).attr('data-full','1');
            };
            addEditContentAction(cellId,cell);
        } else {
            let acb=function() {
                let oCell=$(ui.draggable).attr('data-cell');
                $(ths).find('span.blank').hide();
                $(ths).append($(ui.draggable).clone());
                $(ths).children('.draggable').each(function() {
                    $(this).attr('data-cell',cell);
                    $(this).draggable({
                        revert: 'invalid',
                        cursor: 'move',
                        containment: '#df_template_fields',
                        helper: 'clone',
                        snap: true
                    });
                });
                $('#cell-' + oCell).find('.draggable').remove();
                $('#cell-' + oCell).attr('data-full','0');
                $('#cell-' + oCell).find('span.blank').show();
                $(ths).attr('data-full','1');
            };
            moveContentElementAction(cellId,cell);
        }
    }
});