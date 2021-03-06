var App;

/**
 * AppView, the main view/controller of the App
 *
 */
var AppView = Backbone.View.extend({
    el                 : 'body',
    fileManagerTrigger : null,
    imageFancyPreview  : null,
    tinyMceEditorClass : null,
    events: {

    },
    initialize: function() {
        fileManagerTrigger = $('.iframe-btn');
        imageFancyPreview  = $('.image-preview');
        tinyMceEditorClass = '.tinyMce';
        this.initializePlugins();

    },
    initializePlugins: function() {
        fileManagerTrigger.fancybox({
            'width'     : 900,
            'height'    : 600,
            'type'      : 'iframe',
            'autoSize'  : false,
            'fitToView' : true
        });
        imageFancyPreview.fancybox({

        });
        tinymce.init({
            selector: tinyMceEditorClass,
            plugins: [
                "advlist autolink link image lists charmap print preview hr anchor pagebreak",
                "searchreplace wordcount visualblocks visualchars insertdatetime media nonbreaking",
                "table contextmenu directionality paste responsivefilemanager paste code"
            ],
            forced_root_block : "",
            force_br_newlines : true,
            force_p_newlines  : false,
            toolbar1          : "bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist | outdent indent ",
            toolbar2          : "styleselect | responsivefilemanager | link unlink anchor | image media | forecolor backcolor | code",
            image_advtab      : true ,
            theme             : 'modern',
            height            : 400,
            paste_as_text     : true,

            external_filemanager_path : "/filemanager/",
            filemanager_title         : "File manager" ,
            external_plugins          : { "filemanager" : "/filemanager/plugin.min.js" }
        });

        $('.datePicker').datetimepicker({
            format: "yyyy-mm-dd hh:ii:ss",
            autoclose: true,
            todayBtn: true
        });
        $('.datetimePickerTrigger').bind('click',function(e){
            e.preventDefault();
            $(this).closest('.input-group').find('.datePicker').datetimepicker('show');
        });
        $('.datetimePickerClear').bind('click',function(e){
            e.preventDefault();
            $(this).closest('.input-group').find('input').val('');
        });

        $('.datatable').dataTable({
            "bPaginate": true,
            "bLengthChange": true,
            "bFilter": true,
            "bSort": true,
            "bInfo": true,
            "bAutoWidth": false,
            "aoColumnDefs": [{ 'bSortable': false, 'aTargets': [ 0] }]
        });

        $('.save_exit').bind('click', function(){
            $(this).closest('form').find('input[name="redirect_after_save"]').val('1');
        });

        $('.save_stay').bind('click', function(){
            $(this).closest('form').find('input[name="redirect_after_save"]').val('0');
        });

        $('.bootstrapSwitchIndex').bootstrapSwitch({
            size: 'mini',
            onColor: 'success',
            offColor: 'danger',
            onText: '<span class="glyphicon glyphicon-ok"></span>',
            offText: '<span class="glyphicon glyphicon-remove"></span>',
            onSwitchChange: function(event, state) {
                var currentSwitch = $(this);
                var url   = $(this).data('url'),
                    field = $(this).data('field');

                var fieldValue;
                if (state) {
                    fieldValue = '1';
                } else {
                    fieldValue = '0';
                }
                var data = {
                    'field' : field,
                    'value' : fieldValue
                };
                //disable the switch while there is interaction with server
                currentSwitch.bootstrapSwitch('disabled',true);
                $.ajax({
                    url      : url,
                    type     : 'POST',
                    data     : data,
                    dataType :'json',
                    success  : function(response) {
                        if (response.success) {
                            currentSwitch.bootstrapSwitch('disabled',false);
                        } else {
                            currentSwitch.bootstrapSwitch('disabled',false);
                            currentSwitch.bootstrapSwitch('toggleState','skip');
                        }
                    },
                    error: function (xhr, ajaxOptions, thrownError) {
                        console.log(thrownError);
                    }
                });
            }
        });
        $('.bootstrapSwitchEdit').bootstrapSwitch({
            size: 'small',
            onColor: 'success',
            offColor: 'danger',
            onText: '<span class="glyphicon glyphicon-ok"></span>',
            offText: '<span class="glyphicon glyphicon-remove"></span>'
        });
    }
});

//Initialize the view on domready
$(function(){
    App = new AppView();
});
