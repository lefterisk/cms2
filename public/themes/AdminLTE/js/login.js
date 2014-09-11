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
        'resize window': 'onResize'
    },
    initialize: function() {
       this.initializePlugins();
        $(window).on('ready resize', this.onResize)
    },
    initializePlugins: function() {
        $("input[type='checkbox']:not(.simple), input[type='radio']:not(.simple)").iCheck({
            checkboxClass: 'icheckbox_minimal',
            radioClass: 'iradio_minimal'
        });
    },
    onResize: function() {
        var height = $(window).height() - $("body > .header").height() - ($("body > .footer").outerHeight() || 0);
        $(".wrapper").css("min-height", height + "px");
        var topMargin = (($(window).height() - $("#login-box").height())/2);
        if(topMargin > 0) {
            $("#login-box").css({'margin-top': topMargin+'px'});
        }
    }
});

//Initialize the view on domready
$(function(){
    App = new AppView();
});
