/**
 * Created by kit on 8/21/2017.
 */

var app = app || {};

TaskMessageView = Backbone.View.extend({
    createMode : false,

    messageLable : null,
    messageEditCont : null,
    messageTextArea : null,
    cancelEditIcon : null,

    events : {
        'click .task-message-cont': 'editMessage',
        'keyup .task-message-edit textarea' : 'changeMessageHandler'
    },

    template: _.template( $('#task-card-message-template').html()),

    initialize: function (options) {
        this.createMode = options.createMode;

        this.render();

        _.bindAll(this,
            'changeMessageHandler',
            'prepareForNewTask'
        );
    },

    render: function () {
        var params = {
            message: this.model.get('message'),
        };

        this.$el.html(this.template(params));

        // view elements
        this.messageLable = this.$el.find('.task-message-cont');
        this.messageEditCont = this.$el.find('.task-message-edit');
        this.messageTextArea = this.$el.find('.task-message-edit textarea');

        if (this.createMode) {
            this.prepareForNewTask();
        }
    },

    editMessage: function() {
        this.messageLable.toggle();
        this.messageEditCont.toggle();
        this.messageTextArea.css('background-color', '');
    },

    changeMessageHandler: function() {
        this.messageTextArea.css('background-color', '');
        this.model.set('message', this.messageTextArea.val());
    },

    prepareForNewTask: function() {
        this.messageLable.hide();
        this.messageEditCont.show();
    },

    setRedColorWhenError: function () {
        this.messageTextArea.css('background-color', '#ff80a0');
    }
});



